<?php
date_default_timezone_set('Asia/Manila');
ini_set('display_errors', 0);
ini_set('memory_limit', '256M');
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

    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);

    if (!$data) {
        $data = $_POST;
    }

    $raw_ojt_id = isset($data['ojt_id']) ? intval($data['ojt_id']) : (isset($data['student_id']) ? intval($data['student_id']) : 0);
    $raw_shift = isset($data['shift_type']) ? trim($data['shift_type']) : (isset($data['shift']) ? trim($data['shift']) : '');
    $image_base64 = isset($data['image_base64']) ? $data['image_base64'] : (isset($data['image']) ? $data['image'] : '');
    $custom_date = isset($data['custom_date']) ? trim($data['custom_date']) : (isset($data['date']) ? trim($data['date']) : '');
    $custom_time = isset($data['custom_time']) ? trim($data['custom_time']) : (isset($data['time']) ? trim($data['time']) : '');

    if ($raw_ojt_id <= 0 || empty($raw_shift)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields (OJT/Student ID or shift action)."]);
        exit();
    }

    $current_date = !empty($custom_date) ? $custom_date : date("Y-m-d");
    $current_time = !empty($custom_time) ? $custom_time : date("H:i:s");


    $resolved_ojt_id = $raw_ojt_id;
    $active_site_id = 1;

    // Normalize action: Login or Logout (no morning or afternoon shifts)
    $raw_lower = strtolower($raw_shift);
    $is_logout = (strpos($raw_lower, 'out') !== false || strpos($raw_lower, 'logout') !== false);
    $is_login = !$is_logout;

    $column_to_update = $is_login ? 'login_time' : 'logout_time';
    $action_label = $is_login ? 'Login' : 'Logout';

    // Validate against the scheduled event for today
    $time_formatted = date('H:i:s', strtotime($current_time));

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
        http_response_code(400);
        echo json_encode([
            "status"  => "error",
            "message" => "No scheduled event found for today (" . date("F j, Y", strtotime($current_date)) . "). Attendance log-in and log-out are unavailable."
        ]);
        exit();
    }

    // Active event for today
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

    if ($is_login) {
        if (empty($ev_login_time)) {
            http_response_code(400);
            echo json_encode([
                "status"  => "error",
                "message" => "Event '{$event_title}' does not have a log-in schedule configured."
            ]);
            exit();
        }
        if ($time_formatted < $ev_login_time) {
            http_response_code(400);
            echo json_encode([
                "status"  => "error",
                "message" => "Log-in has not started yet. Log-in starts at " . date("g:i A", strtotime($ev_login_time)) . " for event '{$event_title}'."
            ]);
            exit();
        }
        if (!empty($ev_login_cutoff) && $time_formatted > $ev_login_cutoff) {
            http_response_code(400);
            echo json_encode([
                "status"  => "error",
                "message" => "Log-in cutoff reached (" . date("g:i A", strtotime($ev_login_cutoff)) . "). Log-in is closed for event '{$event_title}'."
            ]);
            exit();
        }
    } elseif ($is_logout) {
        if (empty($ev_logout_time)) {
            http_response_code(400);
            echo json_encode([
                "status"  => "error",
                "message" => "Event '{$event_title}' does not have a log-out schedule configured."
            ]);
            exit();
        }
        if ($time_formatted < $ev_logout_time) {
            http_response_code(400);
            echo json_encode([
                "status"  => "error",
                "message" => "Log-out has not started yet. Log-out starts at " . date("g:i A", strtotime($ev_logout_time)) . " for event '{$event_title}'."
            ]);
            exit();
        }
        if (!empty($ev_logout_cutoff) && $time_formatted > $ev_logout_cutoff) {
            http_response_code(400);
            echo json_encode([
                "status"  => "error",
                "message" => "Log-out cutoff reached (" . date("g:i A", strtotime($ev_logout_cutoff)) . "). Log-out is closed for event '{$event_title}'."
            ]);
            exit();
        }
    }

    // Fetch existing attendance record for today
    $check_stmt = $conn->prepare("SELECT attendance_id, login_time, logout_time FROM attendance WHERE (ojt_id = ? OR student_id = ?) AND date = ?");
    $check_stmt->bind_param("iis", $resolved_ojt_id, $resolved_ojt_id, $current_date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    $attendance_id = 0;
    $existing = null;
    if ($check_result && $check_result->num_rows > 0) {
        $existing = $check_result->fetch_assoc();
        $attendance_id = intval($existing['attendance_id']);
    }
    $check_stmt->close();

    $hasTime = function($val) {
        return !empty($val) && $val !== '--:--' && $val !== '00:00:00';
    };

    // STRICT VALIDATION: Exactly 1 Login and 1 Logout per event
    if ($is_login) {
        if ($existing && $hasTime($existing['login_time'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Already recorded Login today (" . date("h:i A", strtotime($existing['login_time'])) . ")! Only 1 Login is allowed per event."
            ]);
            exit();
        }
    } else {
        // Logout
        if (!$existing || !$hasTime($existing['login_time'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Cannot record Logout. No Login record found for today."
            ]);
            exit();
        }
        if ($hasTime($existing['logout_time'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Already recorded Logout today (" . date("h:i A", strtotime($existing['logout_time'])) . ")! Only 1 Logout is allowed per event."
            ]);
            exit();
        }
        if (strtotime($current_time) <= strtotime($existing['login_time'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Logout must be after Login (" . date("h:i A", strtotime($existing['login_time'])) . ")."
            ]);
            exit();
        }
    }

    $db_image_path = "";
    if (!empty($image_base64) && strlen($image_base64) > 50) {
        try {
            $upload_dir = "../uploads/live_captures/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            if (strpos($image_base64, 'base64,') !== false) {
                $image_parts = explode(";base64,", $image_base64);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = isset($image_type_aux[1]) ? $image_type_aux[1] : 'jpg';
                $image_base64_decoded = base64_decode($image_parts[1]);
            } else {
                $image_type = 'jpg';
                $image_base64_decoded = base64_decode($image_base64);
            }

            if ($image_base64_decoded !== false && strlen($image_base64_decoded) > 0) {
                // Ensure received photo is normalized to portrait orientation (height >= width)
                if (function_exists('imagecreatefromstring')) {
                    $img = @imagecreatefromstring($image_base64_decoded);
                    if ($img !== false) {
                        $orientation = 1;
                        if (function_exists('exif_read_data')) {
                            $stream = fopen('php://memory', 'r+');
                            fwrite($stream, $image_base64_decoded);
                            rewind($stream);
                            $exif = @exif_read_data($stream);
                            if (!empty($exif['Orientation'])) {
                                $orientation = intval($exif['Orientation']);
                            }
                            fclose($stream);
                        }

                        $rotated = false;
                        if ($orientation == 6) {
                            $rotated = imagerotate($img, 270, 0);
                        } elseif ($orientation == 8) {
                            $rotated = imagerotate($img, 90, 0);
                        } elseif ($orientation == 3) {
                            $rotated = imagerotate($img, 180, 0);
                        } else {
                            // If dimensions are landscape (width > height), rotate 270 degrees to portrait
                            $w = imagesx($img);
                            $h = imagesy($img);
                            if ($w > $h) {
                                $rotated = imagerotate($img, 270, 0);
                            }
                        }

                        // Always compress and output as a clean, standardized JPEG
                        $target_img = ($rotated !== false) ? $rotated : $img;
                        ob_start();
                        imagejpeg($target_img, null, 85);
                        $image_base64_decoded = ob_get_clean();
                        if ($rotated !== false) {
                            imagedestroy($rotated);
                        }
                        imagedestroy($img);
                        $image_type = 'jpg';
                    }
                }

                $file_name = "live_" . $resolved_ojt_id . "_" . time() . "." . $image_type;
                $file_path = $upload_dir . $file_name;
                $db_image_path = "uploads/live_captures/" . $file_name;
                file_put_contents($file_path, $image_base64_decoded);
                @chmod($file_path, 0644);
            }
        } catch (Exception $img_err) {
            error_log("Image upload exception: " . $img_err->getMessage());
        }
    }

    $enum_shift = $is_login ? 'Login' : 'Logout';

    if ($existing) {
        $update_stmt = $conn->prepare("UPDATE attendance SET $column_to_update = ?, student_id = COALESCE(student_id, ?), status = 'Pending', remarks = NULL WHERE attendance_id = ?");
        $update_stmt->bind_param("sii", $current_time, $resolved_ojt_id, $attendance_id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $insert_stmt = $conn->prepare("INSERT INTO attendance (date, student_id, ojt_id, $column_to_update, status) VALUES (?, ?, ?, ?, 'Pending')");
        $insert_stmt->bind_param("siis", $current_date, $resolved_ojt_id, $resolved_ojt_id, $current_time);
        $insert_stmt->execute();
        $attendance_id = $conn->insert_id;
        $insert_stmt->close();
    }


    if (!empty($db_image_path) && $attendance_id > 0) {
        $photo_stmt = $conn->prepare("INSERT INTO photo (attendance_id, shift_type, image_path) VALUES (?, ?, ?)");
        if ($photo_stmt) {
            $photo_stmt->bind_param("iss", $attendance_id, $enum_shift, $db_image_path);
            $photo_stmt->execute();
            $photo_stmt->close();
        }
    }

    // Automatically synchronize Live Capture attendance to CIT Department System (Pending Dean Review - NOT Present)
    try {
        require_once __DIR__ . '/cit_sync_helper.php';
        syncAttendanceToCitSystem($conn, $resolved_ojt_id, $column_to_update, $current_date, $current_time, false);
    } catch (Throwable $eSync) {
        // Suppress sync error to avoid breaking student mobile response
    }

    require_once 'motivational_messages.php';
    $motivational_msg = getRandomMotivationalMessage($column_to_update);

    echo json_encode([
        "status" => "success",
        "message" => $motivational_msg,
        "motivational_message" => $motivational_msg,
        "recorded_time" => date("g:i A", strtotime($current_time)),
        "shift_used" => $column_to_update
    ]);

    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>