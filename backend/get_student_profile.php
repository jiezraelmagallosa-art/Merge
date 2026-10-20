<?php
/**
 * CIT Attendance System - Backend API
 * Endpoint: get_student_profile.php
 */

ini_set('display_errors', 0);
error_reporting(0);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle CORS preflight options request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Connect to MySQL database
require_once 'db_connect.php';

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if ($student_id <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Student ID is required."]);
    exit();
}

$sql = "SELECT
            s.student_id,
            s.student_number,
            s.full_name,
            s.id_no,
            s.email,
            s.dean_id,
            s.course_id,
            s.year_level,
            u.full_name AS dean_name,
            u.email AS dean_email,
            c.course_code,
            c.course_name
        FROM student s
        LEFT JOIN users u ON s.dean_id = u.user_id
        LEFT JOIN course c ON s.course_id = c.course_id
        WHERE s.student_id = ?
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Student profile not found."]);
    exit();
}

$profile = $result->fetch_assoc();
$stmt->close();

// Count total events attended (distinct days recorded in attendance)
$days_stmt = $conn->prepare("
    SELECT COUNT(DISTINCT a.date) as total_event_days 
    FROM attendance a 
    WHERE (a.student_id = ? OR a.ojt_id = ?) 
      AND (a.status IS NULL OR a.status != 'Rejected')
");
$days_stmt->bind_param("ii", $student_id, $student_id);
$days_stmt->execute();
$days_res = $days_stmt->get_result()->fetch_assoc();
$total_event_days = intval($days_res['total_event_days'] ?? 0);
$days_stmt->close();

// Check if student has attendance today
$today = date('Y-m-d');
$today_stmt = $conn->prepare("
    SELECT attendance_id, time_in_morning, time_out_morning, time_in_afternoon, time_out_afternoon, status, morning_status, afternoon_status
    FROM attendance
    WHERE (student_id = ? OR ojt_id = ?) AND date = ?
    LIMIT 1
");
$today_stmt->bind_param("iis", $student_id, $student_id, $today);
$today_stmt->execute();
$today_att = $today_stmt->get_result()->fetch_assoc();
$today_stmt->close();

$has_attended_today = ($today_att !== null && ($today_att['time_in_morning'] || $today_att['time_in_afternoon']));

$profile['total_event_days'] = $total_event_days;
$profile['has_attended_today'] = $has_attended_today;
$profile['attendance_status'] = $has_attended_today ? 'Present Today' : 'No Log Today';
$profile['today_attendance'] = $today_att;
$profile['standing'] = 'Good Standing';

echo json_encode([
    "status" => "success",
    "data" => $profile
]);

$conn->close();
?>