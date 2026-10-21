<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: admin_tag_group_attendance.php
 * Handles Dean tagging students in a group photo and crediting attendance with matching shift timestamps.
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

    $attendance_id      = isset($data['attendance_id']) ? intval($data['attendance_id']) : 0;
    $shift              = isset($data['shift']) ? strtolower(trim($data['shift'])) : 'morning';
    $tagged_student_ids = isset($data['tagged_student_ids']) && is_array($data['tagged_student_ids']) ? $data['tagged_student_ids'] : [];
    $dean_id            = isset($data['dean_id']) ? intval($data['dean_id']) : 0;
    $custom_remarks     = isset($data['remarks']) ? trim($data['remarks']) : '';

    if ($attendance_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid or missing reference attendance ID."]);
        exit();
    }

    $valid_shifts = ['morning_in', 'morning_out', 'morning', 'afternoon_in', 'afternoon_out', 'afternoon', 'both'];
    if (!in_array($shift, $valid_shifts)) {
        $shift = 'morning';
    }

    // 1. Fetch reference attendance record and submitter details
    $stmt_ref = $conn->prepare("
        SELECT 
            a.attendance_id,
            a.date,
            a.login_time,
            a.logout_time,
            a.status,
            1 AS site_id,
            s.student_id AS submitter_student_id,
            s.full_name AS submitter_name
        FROM attendance a
        JOIN student s ON (a.student_id = s.student_id OR a.ojt_id = s.student_id)
        WHERE a.attendance_id = ?
        LIMIT 1
    ");
    $stmt_ref->bind_param("i", $attendance_id);
    $stmt_ref->execute();
    $res_ref = $stmt_ref->get_result();
    $ref = $res_ref->fetch_assoc();
    $stmt_ref->close();

    if (!$ref) {
        echo json_encode(["status" => "error", "message" => "Reference attendance log not found."]);
        exit();
    }

    $ref_date            = $ref['date'];
    $login_time          = $ref['login_time'];
    $logout_time         = $ref['logout_time'];
    $submitter_student_id = intval($ref['submitter_student_id']);
    $submitter_name      = $ref['submitter_name'];

    // 2. Format default credit remark
    $default_tag_remark = !empty($custom_remarks) ? $custom_remarks : ("Present in group photo submitted by " . $submitter_name);

    // 3. Confirm submitter's attendance
    $stmt_sub = $conn->prepare("UPDATE attendance SET status = 'Confirmed', remarks = COALESCE(NULLIF(remarks, ''), ?) WHERE attendance_id = ?");
    $stmt_sub->bind_param("si", $default_tag_remark, $attendance_id);
    $stmt_sub->execute();
    $stmt_sub->close();

    // 4. Process each tagged student
    $credited_count = 0;
    $processed_students = [];

    $clean_student_ids = array_unique(array_filter(array_map('intval', $tagged_student_ids)));

    foreach ($clean_student_ids as $st_id) {
        if ($st_id <= 0) continue;

        if ($st_id === $submitter_student_id) {
            $credited_count++;
            $processed_students[] = $st_id;
            continue;
        }

        $ojt_id = $st_id;

        // Check if student already has an attendance record on this date
        $existing_att_id = 0;
        $stmt_check = $conn->prepare("SELECT attendance_id, login_time, logout_time FROM attendance WHERE (student_id = ? OR ojt_id = ?) AND date = ? LIMIT 1");
        $stmt_check->bind_param("iis", $st_id, $st_id, $ref_date);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();
        if ($row_check = $res_check->fetch_assoc()) {
            $existing_att_id = intval($row_check['attendance_id']);
        }
        $stmt_check->close();

        if ($existing_att_id > 0) {
            $stmt_upd = $conn->prepare("
                UPDATE attendance 
                SET login_time = COALESCE(login_time, ?),
                    logout_time = COALESCE(logout_time, ?),
                    status = 'Confirmed',
                    remarks = ?
                WHERE attendance_id = ?
            ");
            $stmt_upd->bind_param("sssi", $login_time, $logout_time, $default_tag_remark, $existing_att_id);
            $stmt_upd->execute();
            $stmt_upd->close();
        } else {
            $stmt_ins = $conn->prepare("
                INSERT INTO attendance 
                    (date, student_id, ojt_id, login_time, logout_time, status, remarks)
                VALUES 
                    (?, ?, ?, ?, ?, 'Confirmed', ?)
            ");
            $stmt_ins->bind_param("siisss", $ref_date, $st_id, $ojt_id, $login_time, $logout_time, $default_tag_remark);
            $stmt_ins->execute();
            $stmt_ins->close();
        }

        $credited_count++;
        $processed_students[] = $st_id;

        // Synchronize credited student attendance confirmation to CIT
        try {
            require_once __DIR__ . '/cit_sync_helper.php';
            syncStudentOjtAttendanceConfirmationToCit($conn, $st_id, $ref_date, 'both');
        } catch (Throwable $eSync) {}
    }

    $login_formatted = !empty($login_time) ? date("h:i A", strtotime($login_time)) : '--:--';
    $logout_formatted = !empty($logout_time) ? date("h:i A", strtotime($logout_time)) : '--:--';
    $shift_label = "Login ($login_formatted) & Logout ($logout_formatted)";

    echo json_encode([
        "status"         => "success",
        "message"        => "Attendance successfully credited to {$credited_count} student(s) for {$shift_label}.",
        "attendance_id"  => $attendance_id,
        "shift"          => $shift,
        "credited_count" => $credited_count,
        "credited_ids"   => $processed_students
    ]);

    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "status"  => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}

function updateAttendanceOverallStatus($conn, $attendance_id) {
    $att_id = intval($attendance_id);
    if ($att_id <= 0) return;
    $check = $conn->query("SELECT morning_status, afternoon_status, time_in_morning, time_in_afternoon FROM attendance WHERE attendance_id = $att_id");
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
        $conn->query("UPDATE attendance SET status = '$overall' WHERE attendance_id = $att_id");
    }
}
?>
