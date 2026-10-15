<?php
/**
 * SBC Internship Attendance System - Backend API
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
            u.full_name AS dean_name,
            u.email AS dean_email,
            c.course_code,
            c.course_name,
            0 AS course_required_hours,
            s.student_id AS ojt_id,
            CONCAT('CIT-', s.student_number) AS ojt_no,
            0 AS required_hours,
            '' AS site_code,
            'CIT Campus' AS site_name,
            'CIT Campus' AS site_location
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
$ojt_id = $profile['student_id'] ?? 0;

$total_minutes = 0;
$total_rendered_hours = 0;
$remaining_minutes = 0;

// Count total unique attendance days
$days_stmt = $conn->prepare("SELECT COUNT(DISTINCT a.date) as total_days FROM attendance a WHERE (a.student_id = ? OR a.ojt_id = ?) AND (a.status IS NULL OR a.status != 'Rejected')");
$days_stmt->bind_param("ii", $student_id, $student_id);
$days_stmt->execute();
$total_days = intval($days_stmt->get_result()->fetch_assoc()['total_days'] ?? 0);
$days_stmt->close();

$req_h = intval($profile['required_hours'] ?? ($profile['course_required_hours'] ?? 480));
$final_required_hours = ($req_h > 0) ? $req_h : 480;

$is_completed = ($total_rendered_hours >= $final_required_hours);

$profile['rendered_hours'] = $total_rendered_hours;
$profile['rendered_minutes'] = $remaining_minutes;
$profile['formatted_rendered_time'] = "{$total_rendered_hours} hrs {$remaining_minutes} mins";
$profile['total_days'] = $total_days;
$profile['required_hours'] = $final_required_hours;
$profile['is_completed'] = $is_completed;
$profile['completion_message'] = $is_completed ? "🎉 Congratulations! You have successfully completed your {$final_required_hours} goal hours!" : "";
$profile['site_breakdown'] = $site_breakdown_data['sites'];

echo json_encode([
    "status" => "success",
    "data" => $profile
]);

$stmt->close();
$conn->close();
?>