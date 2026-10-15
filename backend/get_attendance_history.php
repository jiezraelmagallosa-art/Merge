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
            a.time_in_morning,
            a.time_out_morning,
            a.time_in_afternoon,
            a.time_out_afternoon,
            a.status,
            a.remarks,
            a.morning_status,
            a.morning_remarks,
            a.afternoon_status,
            a.afternoon_remarks
          FROM attendance a
          WHERE a.student_id = ? OR a.ojt_id = ?
          ORDER BY a.date DESC, a.attendance_id DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $param_id, $param_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];

while ($row = $result->fetch_assoc()) {
    $raw_m_status = $row['morning_status'] ?? 'Pending';
    $raw_a_status = $row['afternoon_status'] ?? 'Pending';
    $raw_overall  = $row['status'] ?? 'Pending';

    // Calculate credited minutes for this date excluding rejected shifts
    $m_mins = 0;
    if (($raw_overall === null || $raw_overall !== 'Rejected') && 
        ($raw_m_status === null || $raw_m_status !== 'Rejected') && 
        !empty($row['time_in_morning']) && !empty($row['time_out_morning'])) {
        $m_start = strtotime($row['time_in_morning']);
        $m_end = strtotime($row['time_out_morning']);
        if ($m_end > $m_start) {
            $m_mins = round(($m_end - $m_start) / 60);
        }
    }

    $a_mins = 0;
    if (($raw_overall === null || $raw_overall !== 'Rejected') && 
        ($raw_a_status === null || $raw_a_status !== 'Rejected') && 
        !empty($row['time_in_afternoon']) && !empty($row['time_out_afternoon'])) {
        $a_start = strtotime($row['time_in_afternoon']);
        $a_end = strtotime($row['time_out_afternoon']);
        if ($a_end > $a_start) {
            $a_mins = round(($a_end - $a_start) / 60);
        }
    }

    $day_total_mins = $m_mins + $a_mins;
    $day_hours = floor($day_total_mins / 60);
    $day_rem_mins = $day_total_mins % 60;

    $m_rem = trim(preg_replace('/\s*\/\s*in car/i', '', $row['morning_remarks'] ?? ''));
    $has_both_m = !empty($row['time_in_morning']) && !empty($row['time_out_morning']);
    if ($has_both_m && $raw_m_status === 'Rejected') {
        if (strcasecmp($m_rem, 'Morning Time-Out taken off-site') === 0 || strcasecmp($m_rem, 'Morning Time-In taken off-site') === 0) {
            $m_rem = 'Morning Shift taken off-site';
        }
    }

    $a_rem = trim(preg_replace('/\s*\/\s*in car/i', '', $row['afternoon_remarks'] ?? ''));
    $has_both_a = !empty($row['time_in_afternoon']) && !empty($row['time_out_afternoon']);
    if ($has_both_a && $raw_a_status === 'Rejected') {
        if (strcasecmp($a_rem, 'Afternoon Time-Out taken off-site') === 0 || strcasecmp($a_rem, 'Afternoon Time-In taken off-site') === 0 || strcasecmp($a_rem, 'Afternoon photo invalid / off-site') === 0) {
            $a_rem = 'Afternoon Shift taken off-site';
        }
    }

    $status = "Present";
    if (empty($row['time_in_morning']) && empty($row['time_in_afternoon'])) {
        $status = "Absent";
    } elseif ($raw_overall === 'Rejected' || ($raw_m_status === 'Rejected' && $raw_a_status === 'Rejected')) {
        $status = "Rejected";
    } elseif ($raw_overall === 'Confirmed' || ($raw_m_status === 'Confirmed' && $raw_a_status === 'Confirmed')) {
        $status = "Confirmed";
    } elseif ($raw_m_status === 'Rejected' || $raw_a_status === 'Rejected') {
        $status = "Partial";
    }

    $history[] = [
        "id" => $row['attendance_id'],
        "date" => date("M d, Y", strtotime($row['date'])),
        "raw_date" => $row['date'],
        "time_in_morning" => !empty($row['time_in_morning']) ? date("h:i A", strtotime($row['time_in_morning'])) : '--:--',
        "time_out_morning" => !empty($row['time_out_morning']) ? date("h:i A", strtotime($row['time_out_morning'])) : '--:--',
        "time_in_afternoon" => !empty($row['time_in_afternoon']) ? date("h:i A", strtotime($row['time_in_afternoon'])) : '--:--',
        "time_out_afternoon" => !empty($row['time_out_afternoon']) ? date("h:i A", strtotime($row['time_out_afternoon'])) : '--:--',
        "status" => $status,
        "remarks" => trim(preg_replace('/\s*\/\s*in car/i', '', $row['remarks'] ?? '')),
        "morning_status" => $raw_m_status,
        "morning_remarks" => $m_rem,
        "afternoon_status" => $raw_a_status,
        "afternoon_remarks" => $a_rem,
        "credited_minutes" => $day_total_mins,
        "credited_hours" => $day_hours,
        "credited_remaining_minutes" => $day_rem_mins,
        "formatted_credited_time" => "{$day_hours} hrs {$day_rem_mins} mins"
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $history
]);

$stmt->close();
$conn->close();
?>