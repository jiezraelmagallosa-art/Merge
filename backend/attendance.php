<?php
date_default_timezone_set('Asia/Manila');
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

require_once __DIR__ . '/db_connect.php';

try {

    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);

    if (!$data) {
        $data = $_POST;
    }

    $ojt_id = isset($data['ojt_id']) ? intval($data['ojt_id']) : (isset($data['student_id']) ? intval($data['student_id']) : 0);
    $action = isset($data['action']) ? trim($data['action']) : '';


    if ($ojt_id <= 0 || empty($action)) {
        echo json_encode(["status" => "error", "message" => "Missing OJT ID or action"]);
        exit();
    }

    $current_date = date('Y-m-d');
    $current_time = date('H:i:s');

    // Normalize action
    $action_lower = strtolower($action);
    $is_logout = (strpos($action_lower, 'out') !== false || strpos($action_lower, 'logout') !== false);
    $is_login = !$is_logout;
    $target_col = $is_login ? 'login_time' : 'logout_time';

    $stmt_ev = $conn->prepare("
        SELECT event_id, event_name, event_date, login_time, logout_time, login_cutoff_time, logout_cutoff_time, grace_period
        FROM events
        WHERE event_date = ?
        ORDER BY event_id ASC
    ");
    $events_today = [];
    if ($stmt_ev) {
        $stmt_ev->bind_param("s", $current_date);
        $stmt_ev->execute();
        $res_ev = $stmt_ev->get_result();
        while ($row_ev = $res_ev->fetch_assoc()) {
            $events_today[] = $row_ev;
        }
        $stmt_ev->close();
    }

    if (empty($events_today)) {
        echo json_encode([
            "status"  => "error",
            "message" => "No scheduled event found for today (" . date("F j, Y", strtotime($current_date)) . "). Attendance log-in and log-out are unavailable."
        ]);
        exit();
    }

    $active_event = $events_today[0];
    $event_title = $active_event['event_name'] ?? 'Department Event';
    $ev_grace = intval($active_event['grace_period'] ?? 0);

    $ev_login_time = $active_event['login_time'] ?? null;
    $ev_login_cutoff = !empty($active_event['login_cutoff_time'])
        ? $active_event['login_cutoff_time']
        : (!empty($ev_login_time) && $ev_grace > 0 ? date('H:i:s', strtotime($ev_login_time . " +$ev_grace minutes")) : $ev_login_time);

    $ev_logout_time = $active_event['logout_time'] ?? null;
    $ev_logout_cutoff = !empty($active_event['logout_cutoff_time'])
        ? $active_event['logout_cutoff_time']
        : (!empty($ev_logout_time) && $ev_grace > 0 ? date('H:i:s', strtotime($ev_logout_time . " +$ev_grace minutes")) : $ev_logout_time);

    $current_time_formatted = date('H:i:s', strtotime($current_time));

    if ($is_login) {
        if (empty($ev_login_time)) {
            echo json_encode(["status" => "error", "message" => "Event '{$event_title}' does not have a log-in schedule configured."]);
            exit();
        }
        if ($current_time_formatted < $ev_login_time) {
            echo json_encode(["status" => "error", "message" => "Log-in has not started yet. Starts at " . date("g:i A", strtotime($ev_login_time)) . "."]);
            exit();
        }
        if (!empty($ev_login_cutoff) && $current_time_formatted > $ev_login_cutoff) {
            echo json_encode(["status" => "error", "message" => "Log-in cutoff reached (" . date("g:i A", strtotime($ev_login_cutoff)) . "). Log-in is closed."]);
            exit();
        }
    } else {
        if (empty($ev_logout_time)) {
            echo json_encode(["status" => "error", "message" => "Event '{$event_title}' does not have a log-out schedule configured."]);
            exit();
        }
        if ($current_time_formatted < $ev_logout_time) {
            echo json_encode(["status" => "error", "message" => "Log-out has not started yet. Starts at " . date("g:i A", strtotime($ev_logout_time)) . "."]);
            exit();
        }
        if (!empty($ev_logout_cutoff) && $current_time_formatted > $ev_logout_cutoff) {
            echo json_encode(["status" => "error", "message" => "Log-out cutoff reached (" . date("g:i A", strtotime($ev_logout_cutoff)) . "). Log-out is closed."]);
            exit();
        }
    }

    $checkStmt = $conn->prepare("SELECT attendance_id, login_time, logout_time FROM attendance WHERE (ojt_id = ? OR student_id = ?) AND date = ?");
    $checkStmt->bind_param("iis", $ojt_id, $ojt_id, $current_date);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $existingRecord = $result->fetch_assoc();
    $checkStmt->close();

    require_once 'motivational_messages.php';
    $motivational_msg = getRandomMotivationalMessage($is_login ? 'login' : 'logout');

    if ($is_login) {
        if ($existingRecord && !empty($existingRecord['login_time'])) {
            echo json_encode(["status" => "error", "message" => "Already recorded Login today!"]);
            exit();
        }

        if ($existingRecord) {
            $stmt = $conn->prepare("UPDATE attendance SET login_time = ?, status = 'Pending', remarks = NULL WHERE attendance_id = ?");
            $stmt->bind_param("si", $current_time, $existingRecord['attendance_id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO attendance (date, login_time, ojt_id, student_id, status) VALUES (?, ?, ?, ?, 'Pending')");
            $stmt->bind_param("ssii", $current_date, $current_time, $ojt_id, $ojt_id);
        }
        $stmt->execute();
        $stmt->close();
        echo json_encode([
            "status" => "success",
            "message" => $motivational_msg,
            "motivational_message" => $motivational_msg,
            "recorded_time" => date('g:i A')
        ]);
    } else {
        if (!$existingRecord || empty($existingRecord['login_time'])) {
            echo json_encode(["status" => "error", "message" => "Cannot Logout. No Login record found for today."]);
            exit();
        }
        if (!empty($existingRecord['logout_time'])) {
            echo json_encode(["status" => "error", "message" => "Already recorded Logout today!"]);
            exit();
        }
        if (strtotime($current_time) <= strtotime($existingRecord['login_time'])) {
            echo json_encode(["status" => "error", "message" => "Logout must be after Login."]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE attendance SET logout_time = ?, status = 'Pending', remarks = NULL WHERE attendance_id = ?");
        $stmt->bind_param("si", $current_time, $existingRecord['attendance_id']);
        $stmt->execute();
        $stmt->close();
        echo json_encode([
            "status" => "success",
            "message" => $motivational_msg,
            "motivational_message" => $motivational_msg,
            "recorded_time" => date('g:i A')
        ]);
    }


    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server Error: " . $e->getMessage()
    ]);
}
?>