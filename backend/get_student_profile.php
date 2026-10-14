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
            c.required_hours AS course_required_hours,
            o.ojt_id,
            o.ojt_no,
            o.required_hours,
            ts.site_code,
            ts.site_name,
            ts.location AS site_location
        FROM student s
        LEFT JOIN users u ON s.dean_id = u.user_id
        LEFT JOIN course c ON s.course_id = c.course_id
        LEFT JOIN ojt o ON s.student_id = o.student_id
        LEFT JOIN training_site ts ON o.site_id = ts.site_id
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
$ojt_id = $profile['ojt_id'] ?? 0;


require_once 'site_helper.php';
$site_breakdown_data = getStudentSiteBreakdown($conn, $student_id);

$total_minutes = $site_breakdown_data['total_minutes'];
$total_rendered_hours = $site_breakdown_data['total_hours'];
$remaining_minutes = $site_breakdown_data['remaining_minutes'];

// Count total unique attendance days across all sites
$days_stmt = $conn->prepare("SELECT COUNT(DISTINCT a.date) as total_days FROM attendance a JOIN ojt o ON a.ojt_id = o.ojt_id WHERE o.student_id = ? AND (a.status IS NULL OR a.status != 'Rejected') AND (((a.morning_status IS NULL OR a.morning_status != 'Rejected') AND a.time_in_morning IS NOT NULL) OR ((a.afternoon_status IS NULL OR a.afternoon_status != 'Rejected') AND a.time_in_afternoon IS NOT NULL))");
$days_stmt->bind_param("i", $student_id);
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