<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: submit_journal.php
 * Allows student interns to create or update their daily narrative journal entry.
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

    $student_id = isset($data['student_id']) ? intval($data['student_id']) : 0;
    $entry_date = isset($data['entry_date']) ? trim($data['entry_date']) : date('Y-m-d');
    $tasks      = isset($data['tasks_completed']) ? trim($data['tasks_completed']) : '';
    $learnings  = isset($data['learnings_reflection']) ? trim($data['learnings_reflection']) : '';
    $challenges = isset($data['challenges_encountered']) ? trim($data['challenges_encountered']) : '';

    if ($student_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Valid Student ID is required."]);
        exit();
    }

    if (empty($learnings) && empty($tasks)) {
        echo json_encode(["status" => "error", "message" => "Please provide your key learnings & reflections for today."]);
        exit();
    }

    if (empty($learnings) && !empty($tasks)) {
        $learnings = $tasks;
    }
    if (empty($tasks) && !empty($learnings)) {
        $tasks = $learnings;
    }

    // 1. Verify student exists
    $stmt_st = $conn->prepare("SELECT student_id FROM student WHERE student_id = ? LIMIT 1");
    $stmt_st->bind_param("i", $student_id);
    $stmt_st->execute();
    $res_st = $stmt_st->get_result();
    if (!$res_st || $res_st->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Student record not found."]);
        exit();
    }
    $stmt_st->close();
    $ojt_id = $student_id;

    // 2. Find attendance_id on this date if one exists
    $attendance_id = null;
    $stmt_att = $conn->prepare("SELECT attendance_id FROM attendance WHERE student_id = ? AND date = ? LIMIT 1");
    $stmt_att->bind_param("is", $student_id, $entry_date);
    $stmt_att->execute();
    $res_att = $stmt_att->get_result();
    if ($res_att && $row_att = $res_att->fetch_assoc()) {
        $attendance_id = intval($row_att['attendance_id']);
    }
    $stmt_att->close();

    // 3. Check if journal entry exists for this student and date
    $stmt_check = $conn->prepare("SELECT journal_id FROM daily_journal WHERE student_id = ? AND entry_date = ? LIMIT 1");
    $stmt_check->bind_param("is", $student_id, $entry_date);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();

    if ($res_check && $row_check = $res_check->fetch_assoc()) {
        // UPDATE existing entry
        $journal_id = intval($row_check['journal_id']);
        $stmt_check->close();

        $stmt_upd = $conn->prepare("
            UPDATE daily_journal 
            SET tasks_completed = ?, learnings_reflection = ?, challenges_encountered = ?, attendance_id = ?, updated_at = CURRENT_TIMESTAMP
            WHERE journal_id = ?
        ");
        $stmt_upd->bind_param("sssii", $tasks, $learnings, $challenges, $attendance_id, $journal_id);
        $stmt_upd->execute();
        $stmt_upd->close();

        echo json_encode([
            "status" => "success",
            "message" => "Daily journal entry updated successfully.",
            "journal_id" => $journal_id,
            "entry_date" => $entry_date
        ]);
    } else {
        // INSERT new entry
        $stmt_check->close();

        $stmt_ins = $conn->prepare("
            INSERT INTO daily_journal (student_id, ojt_id, attendance_id, entry_date, tasks_completed, learnings_reflection, challenges_encountered, dean_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");
        $stmt_ins->bind_param("iiissss", $student_id, $ojt_id, $attendance_id, $entry_date, $tasks, $learnings, $challenges);
        $stmt_ins->execute();
        $new_id = $conn->insert_id;
        $stmt_ins->close();

        echo json_encode([
            "status" => "success",
            "message" => "Daily journal entry submitted successfully.",
            "journal_id" => $new_id,
            "entry_date" => $entry_date
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
