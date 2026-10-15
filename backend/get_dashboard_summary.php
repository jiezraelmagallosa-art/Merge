<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: get_dashboard_summary.php
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


$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : (isset($_GET['ojt_id']) ? intval($_GET['ojt_id']) : 0);

if ($student_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Student ID / OJT ID is required."
    ]);
    exit();
}


$ojt_id = $student_id;
$target_hours = 480;

$query = "SELECT
            COUNT(DISTINCT a.date) as total_days
          FROM attendance a
          WHERE (a.student_id = ? OR a.ojt_id = ?) AND (a.status IS NULL OR a.status != 'Rejected')";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total_days = intval($result['total_days'] ?? 0);
$total_minutes = 0;
$total_hours = 0;
$consumed_mins = 0;

$target_total_minutes = $target_hours * 60;
$remaining_total_minutes = max(0, $target_total_minutes - $total_minutes);
$remaining_hours = floor($remaining_total_minutes / 60);
$remaining_mins = $remaining_total_minutes % 60;
$progress_percentage = $target_total_minutes > 0 ? min(100.0, round(($total_minutes / $target_total_minutes) * 100, 1)) : 0;
$is_completed = ($progress_percentage >= 100.0);
$completion_message = $is_completed ? "🎉 Congratulations! You have successfully completed your {$target_hours} goal hours! SBC is proud of your hard work and dedication." : "";

require_once 'site_helper.php';
$site_breakdown_data = getStudentSiteBreakdown($conn, $student_id);

echo json_encode([
    "status" => "success",
    "data" => [
        "target_hours" => $target_hours,
        "total_hours" => $total_hours,
        "consumed_minutes" => $total_minutes,
        "remaining_minutes" => $consumed_mins,
        "formatted_time" => "{$total_hours} hrs {$consumed_mins} mins",
        "formatted_consumed_of_target" => "{$total_hours} hrs {$consumed_mins} mins / {$target_hours} hrs",
        "remaining_hours" => $remaining_hours,
        "remaining_mins" => $remaining_mins,
        "formatted_remaining" => "{$remaining_hours} hrs {$remaining_mins} mins left",
        "progress_percentage" => $progress_percentage,
        "total_days" => intval($result['total_days'] ?? 0),
        "is_completed" => $is_completed,
        "completion_message" => $completion_message,
        "site_breakdown" => $site_breakdown_data['sites']
    ]
]);


$stmt->close();
$conn->close();
?>