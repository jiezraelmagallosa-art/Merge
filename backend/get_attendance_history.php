<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: get_attendance_history.php
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


$param_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : (isset($_GET['ojt_id']) ? intval($_GET['ojt_id']) : 0);

if ($param_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "OJT ID / Student ID is required."
    ]);
    exit();
}


$query = "SELECT DISTINCT
            a.attendance_id,
            a.date,
            a.login_time,
            a.logout_time,
            a.status,
            a.remarks
          FROM attendance a
          WHERE a.student_id = ? OR a.ojt_id = ?
          ORDER BY a.date DESC, a.attendance_id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $param_id, $param_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];

while ($row = $result->fetch_assoc()) {
    $raw_overall = $row['status'] ?? 'Pending';
    $login_val = $row['login_time'];
    $logout_val = $row['logout_time'];

    $login_formatted = !empty($login_val) ? date("h:i A", strtotime($login_val)) : '--:--';
    $logout_formatted = !empty($logout_val) ? date("h:i A", strtotime($logout_val)) : '--:--';

    $status = "Present";
    if (empty($login_val)) {
        $status = "Absent";
    } elseif ($raw_overall === 'Rejected') {
        $status = "Rejected";
    } elseif ($raw_overall === 'Confirmed') {
        $status = "Confirmed";
    }

    $history[] = [
        "id" => $row['attendance_id'],
        "attendance_id" => $row['attendance_id'],
        "date" => date("M d, Y", strtotime($row['date'])),
        "raw_date" => $row['date'],
        "login_time" => $login_formatted,
        "logout_time" => $logout_formatted,
        // Compatibility aliases
        "time_in" => $login_formatted,
        "time_out" => $logout_formatted,
        "time_in_morning" => $login_formatted,
        "time_out_morning" => '--:--',
        "time_in_afternoon" => '--:--',
        "time_out_afternoon" => $logout_formatted,
        "status" => $status,
        "remarks" => trim($row['remarks'] ?? ''),
        "morning_status" => $raw_overall,
        "morning_remarks" => trim($row['remarks'] ?? ''),
        "afternoon_status" => $raw_overall,
        "afternoon_remarks" => trim($row['remarks'] ?? ''),
        "credited_minutes" => 0,
        "credited_hours" => 0,
        "credited_remaining_minutes" => 0,
        "formatted_credited_time" => "Event Attendance"
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $history
]);

$stmt->close();
$conn->close();
?>