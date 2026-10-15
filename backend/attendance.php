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

    // Validate against the scheduled event for today
    $current_time_formatted = date('H:i:s', strtotime($current_time));
    $is_login = in_array($action, ['time_in_morning', 'time_in_afternoon']);
    $is_logout = in_array($action, ['time_out_morning', 'time_out_afternoon']);
    $is_morning = in_array($action, ['time_in_morning', 'time_out_morning']);
    $is_afternoon = in_array($action, ['time_in_afternoon', 'time_out_afternoon']);

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

    // Select the appropriate event if multiple exist for today
    $active_event = $events_today[0];
    if (count($events_today) > 1) {
        if ($is_morning) {
            foreach ($events_today as $ev) {
                if (!empty($ev['login_time']) && $ev['login_time'] < '12:30:00') {
                    $active_event = $ev;
                    break;
                }
            }
        } elseif ($is_afternoon) {
            foreach ($events_today as $ev) {
                if (!empty($ev['login_time']) && $ev['login_time'] >= '12:00:00') {
                    $active_event = $ev;
                    break;
                }
            }
        }
    }

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

    if ($is_login) {
        if (empty($ev_login_time)) {
            echo json_encode([
                "status"  => "error",
                "message" => "Event '{$event_title}' does not have a log-in schedule configured."
            ]);
            exit();
        }
        if ($current_time_formatted < $ev_login_time) {
            echo json_encode([
                "status"  => "error",
                "message" => "Log-in has not started yet. Log-in starts at " . date("g:i A", strtotime($ev_login_time)) . " for event '{$event_title}'."
            ]);
            exit();
        }
        if (!empty($ev_login_cutoff) && $current_time_formatted > $ev_login_cutoff) {
            echo json_encode([
                "status"  => "error",
                "message" => "Log-in cutoff reached (" . date("g:i A", strtotime($ev_login_cutoff)) . "). Log-in is closed for event '{$event_title}'."
            ]);
            exit();
        }
    } elseif ($is_logout) {
        if (empty($ev_logout_time)) {
            echo json_encode([
                "status"  => "error",
                "message" => "Event '{$event_title}' does not have a log-out schedule configured."
            ]);
            exit();
        }
        if ($current_time_formatted < $ev_logout_time) {
            echo json_encode([
                "status"  => "error",
                "message" => "Log-out has not started yet. Log-out starts at " . date("g:i A", strtotime($ev_logout_time)) . " for event '{$event_title}'."
            ]);
            exit();
        }
        if (!empty($ev_logout_cutoff) && $current_time_formatted > $ev_logout_cutoff) {
            echo json_encode([
                "status"  => "error",
                "message" => "Log-out cutoff reached (" . date("g:i A", strtotime($ev_logout_cutoff)) . "). Log-out is closed for event '{$event_title}'."
            ]);
            exit();
        }
    }


    $checkStmt = $conn->prepare("SELECT * FROM attendance WHERE ojt_id = ? AND date = ?");
    $checkStmt->bind_param("is", $ojt_id, $current_date);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $existingRecord = $result->fetch_assoc();

    require_once 'motivational_messages.php';
    $motivational_msg = getRandomMotivationalMessage($action);

    if ($action === 'time_in_morning') {
        if ($existingRecord && !empty($existingRecord['time_in_morning'])) {
            echo json_encode(["status" => "error", "message" => "Already recorded Time-In for Morning today!"]);
            exit();
        }

        if ($existingRecord) {
            $stmt = $conn->prepare("UPDATE attendance SET time_in_morning = ?, status = 'Pending', remarks = NULL WHERE attendance_id = ?");
            $stmt->bind_param("si", $current_time, $existingRecord['attendance_id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO attendance (date, time_in_morning, ojt_id, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param("ssi", $current_date, $current_time, $ojt_id);
        }
        $stmt->execute();
        echo json_encode([
            "status" => "success",
            "message" => $motivational_msg,
            "motivational_message" => $motivational_msg,
            "recorded_time" => date('g:i A')
        ]);

    } elseif ($action === 'time_out_morning') {
        if (!$existingRecord || empty($existingRecord['time_in_morning'])) {
            echo json_encode(["status" => "error", "message" => "Cannot Time-Out. No Morning Time-In record found."]);
            exit();
        }
        if (!empty($existingRecord['time_out_morning'])) {
            echo json_encode(["status" => "error", "message" => "Already recorded Morning Time-Out today! Only 1 Time-Out allowed per shift."]);
            exit();
        }
        if (strtotime($current_time) <= strtotime($existingRecord['time_in_morning'])) {
            echo json_encode(["status" => "error", "message" => "Morning Time-Out must be after Morning Time-In."]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE attendance SET time_out_morning = ?, status = 'Pending', remarks = NULL WHERE attendance_id = ?");
        $stmt->bind_param("si", $current_time, $existingRecord['attendance_id']);
        $stmt->execute();
        echo json_encode([
            "status" => "success",
            "message" => $motivational_msg,
            "motivational_message" => $motivational_msg,
            "recorded_time" => date('g:i A')
        ]);

    } elseif ($action === 'time_in_afternoon') {
        if ($existingRecord && !empty($existingRecord['time_in_afternoon'])) {
            echo json_encode(["status" => "error", "message" => "Already recorded Time-In for Afternoon today! Only 1 Time-In allowed per shift."]);
            exit();
        }

        if ($existingRecord) {
            $stmt = $conn->prepare("UPDATE attendance SET time_in_afternoon = ?, status = 'Pending', remarks = NULL WHERE attendance_id = ?");
            $stmt->bind_param("si", $current_time, $existingRecord['attendance_id']);
        } else {
            $stmt = $conn->prepare("INSERT INTO attendance (date, time_in_afternoon, ojt_id, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param("ssi", $current_date, $current_time, $ojt_id);
        }
        $stmt->execute();
        echo json_encode([
            "status" => "success",
            "message" => $motivational_msg,
            "motivational_message" => $motivational_msg,
            "recorded_time" => date('g:i A')
        ]);

    } elseif ($action === 'time_out_afternoon') {
        if (!$existingRecord || empty($existingRecord['time_in_afternoon'])) {
            echo json_encode(["status" => "error", "message" => "Cannot Time-Out. No Afternoon Time-In record found."]);
            exit();
        }
        if (!empty($existingRecord['time_out_afternoon'])) {
            echo json_encode(["status" => "error", "message" => "Already recorded Afternoon Time-Out today! Only 1 Time-Out allowed per shift."]);
            exit();
        }
        if (strtotime($current_time) <= strtotime($existingRecord['time_in_afternoon'])) {
            echo json_encode(["status" => "error", "message" => "Afternoon Time-Out must be after Afternoon Time-In."]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE attendance SET time_out_afternoon = ?, status = 'Pending', remarks = NULL WHERE attendance_id = ?");
        $stmt->bind_param("si", $current_time, $existingRecord['attendance_id']);
        $stmt->execute();
        echo json_encode([
            "status" => "success",
            "message" => $motivational_msg,
            "motivational_message" => $motivational_msg,
            "recorded_time" => date('g:i A')
        ]);

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid action requested."]);
    }


    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server Error: " . $e->getMessage()
    ]);
}
?>