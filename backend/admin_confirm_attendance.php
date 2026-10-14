<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: admin_confirm_attendance.php
 * Dean reviews an attendance log: 'Confirmed' (deletes photos) or 'Rejected' (stores remarks).
 */

ini_set('display_errors', 0);
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once 'db_connect.php';

    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);
    if (!$data) {
        $data = $_POST;
    }

    $attendance_id = isset($data['attendance_id']) ? intval($data['attendance_id']) : 0;
    $dean_id = isset($data['dean_id']) ? intval($data['dean_id']) : 0;
    $action = isset($data['action']) ? trim($data['action']) : 'Confirmed';
    $shift = isset($data['shift']) ? strtolower(trim($data['shift'])) : 'both';
    $remarks = isset($data['remarks']) ? trim($data['remarks']) : '';

    if ($attendance_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid or missing attendance ID."]);
        exit();
    }

    if (!in_array($action, ['Confirmed', 'Rejected'])) {
        echo json_encode(["status" => "error", "message" => "Invalid action. Must be 'Confirmed' or 'Rejected'."]);
        exit();
    }

    if (!in_array($shift, ['morning', 'afternoon', 'both'])) {
        $shift = 'both';
    }

    $deleted_files_count = 0;
    $base_dir = dirname(__DIR__) . "/";

    if ($action === 'Confirmed') {
        // Delete only the photos associated with the confirmed shift(s)
        $photo_types = [];
        if ($shift === 'morning') {
            $photo_types = ["'Morning_In'", "'Morning_Out'"];
        } elseif ($shift === 'afternoon') {
            $photo_types = ["'Afternoon_In'", "'Afternoon_Out'"];
        } else {
            $photo_types = ["'Morning_In'", "'Morning_Out'", "'Afternoon_In'", "'Afternoon_Out'"];
        }

        $type_list = implode(',', $photo_types);
        $res_photos = $conn->query("SELECT photo_id, image_path FROM photo WHERE attendance_id = $attendance_id AND shift_type IN ($type_list)");
        if ($res_photos) {
            while ($p_row = $res_photos->fetch_assoc()) {
                $img_rel_path = $p_row['image_path'];
                if (!empty($img_rel_path)) {
                    $full_file_path = $base_dir . $img_rel_path;
                    if (file_exists($full_file_path)) {
                        @unlink($full_file_path);
                        $deleted_files_count++;
                    }
                }
            }
            $conn->query("DELETE FROM photo WHERE attendance_id = $attendance_id AND shift_type IN ($type_list)");
        }

        if ($shift === 'morning') {
            $final_remarks = $remarks !== '' ? $remarks : 'Morning shift confirmed by Dean';
            $stmt_upd = $conn->prepare("UPDATE attendance SET morning_status = 'Confirmed', morning_remarks = ? WHERE attendance_id = ?");
            $stmt_upd->bind_param("si", $final_remarks, $attendance_id);
            $stmt_upd->execute();
            $stmt_upd->close();
        } elseif ($shift === 'afternoon') {
            $final_remarks = $remarks !== '' ? $remarks : 'Afternoon shift confirmed by Dean';
            $stmt_upd = $conn->prepare("UPDATE attendance SET afternoon_status = 'Confirmed', afternoon_remarks = ? WHERE attendance_id = ?");
            $stmt_upd->bind_param("si", $final_remarks, $attendance_id);
            $stmt_upd->execute();
            $stmt_upd->close();
        } else {
            $final_remarks = $remarks !== '' ? $remarks : 'Attendance log confirmed by Dean of Student Affairs';
            $stmt_upd = $conn->prepare("UPDATE attendance SET status = 'Confirmed', morning_status = 'Confirmed', afternoon_status = 'Confirmed', remarks = ?, morning_remarks = ?, afternoon_remarks = ? WHERE attendance_id = ?");
            $stmt_upd->bind_param("sssi", $final_remarks, $final_remarks, $final_remarks, $attendance_id);
            $stmt_upd->execute();
            $stmt_upd->close();
        }

        // Re-evaluate overall status
        updateAttendanceOverallStatus($conn, $attendance_id);

        // Synchronize Dean confirmation with CIT Department System -> marks as Present
        try {
            require_once __DIR__ . '/cit_sync_helper.php';
            syncDeanConfirmationToCitSystem($conn, $attendance_id, $shift, 'Confirmed');
        } catch (Throwable $eSync) {
            error_log("CIT Dean confirmation sync error: " . $eSync->getMessage());
        }

        echo json_encode([
            "status" => "success",
            "message" => ucfirst($shift) . " shift attendance confirmed. {$deleted_files_count} photo(s) purged.",
            "attendance_id" => $attendance_id,
            "shift" => $shift,
            "action" => "Confirmed",
            "remarks" => $final_remarks
        ]);

    } else {
        // Rejected — save custom Dean remarks, DO NOT delete photos (preserve evidence)
        $att_info = null;
        $q_att = $conn->query("SELECT time_in_morning, time_out_morning, time_in_afternoon, time_out_afternoon FROM attendance WHERE attendance_id = $attendance_id LIMIT 1");
        if ($q_att && $att_row = $q_att->fetch_assoc()) {
            $att_info = $att_row;
        }

        if ($shift === 'morning') {
            $has_both_m = $att_info && !empty($att_info['time_in_morning']) && !empty($att_info['time_out_morning']);
            if ($has_both_m && (empty($remarks) || strcasecmp($remarks, 'Morning Time-Out taken off-site') === 0 || strcasecmp($remarks, 'Morning Time-In taken off-site') === 0)) {
                $final_remarks = 'Morning Shift taken off-site';
            } else {
                $final_remarks = $remarks !== '' ? $remarks : 'Morning shift attendance rejected by Dean';
            }
            $stmt_upd = $conn->prepare("UPDATE attendance SET morning_status = 'Rejected', morning_remarks = ? WHERE attendance_id = ?");
            $stmt_upd->bind_param("si", $final_remarks, $attendance_id);
            $stmt_upd->execute();
            $stmt_upd->close();
        } elseif ($shift === 'afternoon') {
            $has_both_a = $att_info && !empty($att_info['time_in_afternoon']) && !empty($att_info['time_out_afternoon']);
            if ($has_both_a && (empty($remarks) || strcasecmp($remarks, 'Afternoon Time-Out taken off-site') === 0 || strcasecmp($remarks, 'Afternoon Time-In taken off-site') === 0 || strcasecmp($remarks, 'Afternoon photo invalid / off-site') === 0)) {
                $final_remarks = 'Afternoon Shift taken off-site';
            } else {
                $final_remarks = $remarks !== '' ? $remarks : 'Afternoon shift attendance rejected by Dean';
            }
            $stmt_upd = $conn->prepare("UPDATE attendance SET afternoon_status = 'Rejected', afternoon_remarks = ? WHERE attendance_id = ?");
            $stmt_upd->bind_param("si", $final_remarks, $attendance_id);
            $stmt_upd->execute();
            $stmt_upd->close();
        } else {
            $final_remarks = $remarks !== '' ? $remarks : 'Attendance log rejected by Dean of Student Affairs';
            $stmt_upd = $conn->prepare("UPDATE attendance SET status = 'Rejected', morning_status = 'Rejected', afternoon_status = 'Rejected', remarks = ?, morning_remarks = ?, afternoon_remarks = ? WHERE attendance_id = ?");
            $stmt_upd->bind_param("sssi", $final_remarks, $final_remarks, $final_remarks, $attendance_id);
            $stmt_upd->execute();
            $stmt_upd->close();
        }

        // Re-evaluate overall status
        updateAttendanceOverallStatus($conn, $attendance_id);

        // Synchronize Dean rejection with CIT Department System -> marks as Absent / Rejected
        try {
            require_once __DIR__ . '/cit_sync_helper.php';
            syncDeanConfirmationToCitSystem($conn, $attendance_id, $shift, 'Rejected');
        } catch (Throwable $eSync) {
            error_log("CIT Dean rejection sync error: " . $eSync->getMessage());
        }

        echo json_encode([
            "status" => "success",
            "message" => ucfirst($shift) . " shift attendance rejected. Photo evidence retained.",
            "attendance_id" => $attendance_id,
            "shift" => $shift,
            "action" => "Rejected",
            "remarks" => $final_remarks
        ]);
    }

    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}

function updateAttendanceOverallStatus($conn, $attendance_id)
{
    $check = $conn->query("SELECT morning_status, afternoon_status, time_in_morning, time_in_afternoon FROM attendance WHERE attendance_id = $attendance_id");
    if ($check && $row = $check->fetch_assoc()) {
        $ms = $row['morning_status'] ?? 'Pending';
        $as = $row['afternoon_status'] ?? 'Pending';
        $has_m = !empty($row['time_in_morning']);
        $has_a = !empty($row['time_in_afternoon']);

        $overall = 'Pending';
        if ($ms === 'Rejected' && $as === 'Rejected') {
            $overall = 'Rejected';
        } elseif ($ms === 'Confirmed' && $as === 'Confirmed') {
            $overall = 'Confirmed';
        } elseif ($ms === 'Confirmed' && (!$has_a || $as === 'Confirmed')) {
            $overall = 'Confirmed';
        } elseif ($as === 'Confirmed' && (!$has_m || $ms === 'Confirmed')) {
            $overall = 'Confirmed';
        } elseif ($ms === 'Rejected' || $as === 'Rejected') {
            $overall = ($ms === 'Confirmed' || $as === 'Confirmed') ? 'Partial' : 'Rejected';
        }
        $conn->query("UPDATE attendance SET status = '$overall' WHERE attendance_id = $attendance_id");
    }
}
?>