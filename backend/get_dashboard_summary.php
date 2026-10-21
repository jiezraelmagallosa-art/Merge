<?php
/**
 * CIT Attendance System - Backend API
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
        "message" => "Student ID is required."
    ]);
    exit();
}

// Total distinct days attended
$query = "SELECT COUNT(DISTINCT a.date) as total_days
          FROM attendance a
          WHERE (a.student_id = ? OR a.ojt_id = ?) 
            AND (a.status IS NULL OR a.status != 'Rejected')";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $student_id, $student_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total_days = intval($result['total_days'] ?? 0);

// Today's event
$today = date('Y-m-d');
$today_event = null;
$ev_stmt = $conn->prepare("SELECT event_id, event_name, event_date, login_time, logout_time, login_cutoff_time, logout_cutoff_time, grace_period FROM events WHERE event_date = ? ORDER BY event_id ASC LIMIT 1");
if ($ev_stmt) {
    $ev_stmt->bind_param("s", $today);
    $ev_stmt->execute();
    $today_event = $ev_stmt->get_result()->fetch_assoc();
    $ev_stmt->close();
}

// Today's attendance for student
$att_stmt = $conn->prepare("SELECT attendance_id, login_time, logout_time, status, remarks FROM attendance WHERE (student_id = ? OR ojt_id = ?) AND date = ? LIMIT 1");
$today_att = null;
if ($att_stmt) {
    $att_stmt->bind_param("iis", $student_id, $student_id, $today);
    $att_stmt->execute();
    $today_att = $att_stmt->get_result()->fetch_assoc();
    $att_stmt->close();
}

$has_logged_in = false;
$has_logged_out = false;
$attendance_status_label = "Not Recorded";

if ($today_att) {
    if (!empty($today_att['login_time'])) {
        $has_logged_in = true;
        $attendance_status_label = "Logged In (Present)";
    }
    if (!empty($today_att['logout_time'])) {
        $has_logged_out = true;
        $attendance_status_label = "Logged Out (Completed)";
    }
    if ($today_att['status'] === 'Confirmed') {
        $attendance_status_label = "Verified Present";
    }
    // Backward compatibility aliases
    $today_att['time_in_morning'] = $today_att['login_time'];
    $today_att['time_out_morning'] = '--:--';
    $today_att['time_in_afternoon'] = '--:--';
    $today_att['time_out_afternoon'] = $today_att['logout_time'];
    $today_att['morning_status'] = $today_att['status'];
    $today_att['afternoon_status'] = $today_att['status'];
    $today_att['morning_remarks'] = $today_att['remarks'];
    $today_att['afternoon_remarks'] = $today_att['remarks'];
}

$has_timed_in = $has_logged_in;
$has_timed_out = $has_logged_out;

// Student & Dean details
$st_stmt = $conn->prepare("SELECT s.full_name, s.student_number, u.full_name as dean_name FROM student s LEFT JOIN users u ON s.dean_id = u.user_id WHERE s.student_id = ? LIMIT 1");
$st_info = null;
if ($st_stmt) {
    $st_stmt->bind_param("i", $student_id);
    $st_stmt->execute();
    $st_info = $st_stmt->get_result()->fetch_assoc();
    $st_stmt->close();
}

echo json_encode([
    "status" => "success",
    "data" => [
        "total_days" => $total_days,
        "total_events_attended" => $total_days,
        "formatted_time" => "$total_days Event Days",
        "has_logged_in" => $has_logged_in,
        "has_logged_out" => $has_logged_out,
        "has_timed_in" => $has_timed_in,
        "has_timed_out" => $has_timed_out,
        "today_status_label" => $attendance_status_label,
        "today_attendance" => $today_att,
        "dean_name" => $st_info['dean_name'] ?? 'Assigned Dean / Officer',
        "today_event" => $today_event ? [
            "has_event" => true,
            "event_id" => intval($today_event['event_id']),
            "event_name" => $today_event['event_name'],
            "login_time" => $today_event['login_time'],
            "logout_time" => $today_event['logout_time'],
            "login_cutoff_time" => $today_event['login_cutoff_time'],
            "logout_cutoff_time" => $today_event['logout_cutoff_time']
        ] : [
            "has_event" => false,
            "message" => "No scheduled event for today."
        ],
        "site_breakdown" => []
    ]
]);

$conn->close();
?>