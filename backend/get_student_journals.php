<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: get_student_journals.php
 * Fetches all daily narrative journal entries for a specific student intern.
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

    $student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : (isset($_POST['student_id']) ? intval($_POST['student_id']) : 0);

    if ($student_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Valid Student ID is required."]);
        exit();
    }

    $sql = "
        SELECT 
            j.journal_id,
            j.student_id,
            j.ojt_id,
            j.attendance_id,
            j.entry_date,
            DATE_FORMAT(j.entry_date, '%M %d, %Y') AS formatted_date,
            j.tasks_completed,
            j.learnings_reflection,
            j.challenges_encountered,
            j.dean_feedback,
            j.dean_status,
            j.reviewed_at,
            DATE_FORMAT(j.reviewed_at, '%M %d, %Y %h:%i %p') AS formatted_reviewed_at,
            j.created_at,
            j.updated_at,
            'CIT Event' AS site_name,
            'CIT Campus' AS site_location,
            a.login_time,
            a.logout_time,
            a.status AS attendance_status
        FROM daily_journal j
        LEFT JOIN attendance a ON j.attendance_id = a.attendance_id
        WHERE j.student_id = ?
        ORDER BY j.entry_date DESC, j.journal_id DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $journals = [];
    while ($row = $result->fetch_assoc()) {
        $journals[] = [
            "journal_id"             => intval($row['journal_id']),
            "entry_date"             => $row['entry_date'],
            "formatted_date"         => $row['formatted_date'],
            "tasks_completed"        => $row['tasks_completed'],
            "learnings_reflection"   => $row['learnings_reflection'] ?? '',
            "challenges_encountered" => $row['challenges_encountered'] ?? '',
            "dean_feedback"          => $row['dean_feedback'] ?? '',
            "dean_status"            => $row['dean_status'] ?? 'Pending',
            "reviewed_at"            => $row['formatted_reviewed_at'],
            "site_name"              => $row['site_name'] ?? 'Assigned Facility',
            "has_feedback"           => !empty($row['dean_feedback']),
            "is_reviewed"            => ($row['dean_status'] === 'Reviewed' || $row['dean_status'] === 'Commended')
        ];
    }
    $stmt->close();

    echo json_encode([
        "status" => "success",
        "count"  => count($journals),
        "data"   => $journals
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
