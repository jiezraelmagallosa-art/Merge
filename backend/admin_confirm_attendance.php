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
        // Purge photos for confirmed attendance
        $res_photos = $conn->query("SELECT photo_id, image_path FROM photo WHERE attendance_id = $attendance_id");
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
            $conn->query("DELETE FROM photo WHERE attendance_id = $attendance_id");
        }

        $final_remarks = $remarks !== '' ? $remarks : 'Attendance verified and confirmed by Dean';
        $stmt_upd = $conn->prepare("UPDATE attendance SET status = 'Confirmed', remarks = ? WHERE attendance_id = ?");
        $stmt_upd->bind_param("si", $final_remarks, $attendance_id);
        $stmt_upd->execute();
        $stmt_upd->close();

        // Synchronize Dean confirmation with CIT Department System -> marks as Present
        try {
            require_once __DIR__ . '/cit_sync_helper.php';
            syncDeanConfirmationToCitSystem($conn, $attendance_id, 'both', 'Confirmed');
        } catch (Throwable $eSync) {
            error_log("CIT Dean confirmation sync error: " . $eSync->getMessage());
        }

        echo json_encode([
            "status" => "success",
            "message" => "Event attendance confirmed. {$deleted_files_count} photo(s) purged.",
            "attendance_id" => $attendance_id,
            "action" => "Confirmed",
            "remarks" => $final_remarks
        ]);

    } else {
        // Rejected — save custom Dean remarks, keep photo evidence
        $final_remarks = $remarks !== '' ? $remarks : 'Attendance log rejected by Dean';
        $stmt_upd = $conn->prepare("UPDATE attendance SET status = 'Rejected', remarks = ? WHERE attendance_id = ?");
        $stmt_upd->bind_param("si", $final_remarks, $attendance_id);
        $stmt_upd->execute();
        $stmt_upd->close();

        // Synchronize Dean rejection with CIT Department System -> marks as Absent / Rejected
        try {
            require_once __DIR__ . '/cit_sync_helper.php';
            syncDeanConfirmationToCitSystem($conn, $attendance_id, 'both', 'Rejected');
        } catch (Throwable $eSync) {
            error_log("CIT Dean rejection sync error: " . $eSync->getMessage());
        }

        echo json_encode([
            "status" => "success",
            "message" => "Event attendance rejected. Photo evidence retained.",
            "attendance_id" => $attendance_id,
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
?>