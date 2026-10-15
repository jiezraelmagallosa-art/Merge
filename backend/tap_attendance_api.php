<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: backend/tap_attendance_api.php
 * Handles RFID / Student ID card tap attendance and RFID card mapping.
 */

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
    require_once __DIR__ . '/db_connect.php';
    require_once __DIR__ . '/motivational_messages.php';

    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);
    if (!$data) {
        $data = !empty($_POST) ? $_POST : $_GET;
    }

    $action = isset($data['action']) ? trim($data['action']) : 'tap';
    $today = date("Y-m-d");
    $now_time = date("H:i:s");

    // -------------------------------------------------------------
    // ACTION: TAP ATTENDANCE (Student taps ID card in line)
    // -------------------------------------------------------------
    if ($action === 'tap') {
        $tap_id = isset($data['tap_id']) ? trim($data['tap_id']) : (isset($data['rfid_uid']) ? trim($data['rfid_uid']) : '');
        $shift_mode = isset($data['shift_mode']) ? trim($data['shift_mode']) : (isset($data['shift_type']) ? trim($data['shift_type']) : 'auto');

        if (empty($tap_id)) {
            echo json_encode([
                "status" => "error",
                "code" => "EMPTY_ID",
                "message" => "Please tap your School ID / RFID card or enter your Student ID."
            ]);
            exit();
        }

        // 1. Look up student by rfid_uid, id_no, or student_number
        $stmt_student = $conn->prepare("
            SELECT s.student_id, s.student_number, s.full_name, s.id_no, s.rfid_uid, s.course_id, s.dean_id,
                   c.course_code, c.course_name, 0 AS course_required_hours,
                   s.student_id AS ojt_id, 1 AS site_id, 0 AS ojt_required_hours,
                   'CIT Campus' AS site_name, 'CIT Campus' AS site_location
            FROM student s
            LEFT JOIN course c ON s.course_id = c.course_id
            WHERE s.rfid_uid = ? OR s.id_no = ? OR s.student_number = ?
            LIMIT 1
        ");

        if (!$stmt_student) {
            throw new Exception("Database prepare error: " . $conn->error);
        }

        $stmt_student->bind_param("sss", $tap_id, $tap_id, $tap_id);
        $stmt_student->execute();
        $student_res = $stmt_student->get_result();

        if (!$student_res || $student_res->num_rows === 0) {
            $stmt_student->close();
            echo json_encode([
                "status" => "error",
                "code" => "STUDENT_NOT_FOUND",
                "message" => "Unrecognized ID: \"" . htmlspecialchars($tap_id) . "\". Card is not yet registered in the system."
            ]);
            exit();
        }

        $student = $student_res->fetch_assoc();
        $stmt_student->close();

        $student_id = intval($student['student_id']);
        $ojt_id = $student_id;
        $active_site_id = 1;

        // 2. Fetch today's existing attendance record
        $stmt_att = $conn->prepare("
            SELECT attendance_id, time_in_morning, time_out_morning, time_in_afternoon, time_out_afternoon, remarks, morning_status, afternoon_status
            FROM attendance
            WHERE (student_id = ? OR ojt_id = ?) AND date = ?
            LIMIT 1
        ");
        $stmt_att->bind_param("iis", $student_id, $student_id, $today);
        $stmt_att->execute();
        $att_res = $stmt_att->get_result();
        $existing_att = ($att_res && $att_res->num_rows > 0) ? $att_res->fetch_assoc() : null;
        $stmt_att->close();

        $hasTime = function($val) {
            return !empty($val) && $val !== '--:--' && $val !== '00:00:00';
        };

        // Shift definitions & labels
        $shift_labels = [
            'time_in_morning'   => 'Morning Time-In',
            'time_out_morning'  => 'Morning Time-Out',
            'time_in_afternoon' => 'Afternoon Time-In',
            'time_out_afternoon'=> 'Afternoon Time-Out'
        ];

        $target_column = '';
        $target_label = '';

        // 3. Resolve target shift column - Event MUST exist for today
        $stmt_ev = $conn->prepare("
            SELECT event_id, event_name, event_date, login_time, logout_time, login_cutoff_time, logout_cutoff_time, grace_period
            FROM events
            WHERE event_date = ?
            ORDER BY event_id ASC
        ");
        $events_today = [];
        if ($stmt_ev) {
            $stmt_ev->bind_param("s", $today);
            $stmt_ev->execute();
            $res_ev = $stmt_ev->get_result();
            while ($row_ev = $res_ev->fetch_assoc()) {
                $events_today[] = $row_ev;
            }
            $stmt_ev->close();
        }

        if (empty($events_today)) {
            echo json_encode([
                "status" => "error",
                "code" => "NO_EVENT",
                "message" => "Cannot take attendance. No event has been added for today (" . date("F j, Y", strtotime($today)) . "). Attendance log-in and log-out are unavailable."
            ]);
            exit();
        }

        $active_event = $events_today[0];
        $ev_name = $active_event['event_name'] ?? 'Department Event';
        $ev_grace = intval($active_event['grace_period'] ?? 0);

        $ev_login = $active_event['login_time'] ?? null;
        $ev_login_cutoff = !empty($active_event['login_cutoff_time'])
            ? $active_event['login_cutoff_time']
            : (!empty($ev_login) && $ev_grace > 0 ? date('H:i:s', strtotime($ev_login . " +$ev_grace minutes")) : $ev_login);

        $ev_logout = $active_event['logout_time'] ?? null;
        $ev_logout_cutoff = !empty($active_event['logout_cutoff_time'])
            ? $active_event['logout_cutoff_time']
            : (!empty($ev_logout) && $ev_grace > 0 ? date('H:i:s', strtotime($ev_logout . " +$ev_grace minutes")) : $ev_logout);

        if ($shift_mode === 'auto') {
            // Determine if current time falls in login or logout window
            $in_login_window = ($ev_login && $ev_login_cutoff && $now_time >= $ev_login && $now_time <= $ev_login_cutoff);
            $in_logout_window = ($ev_logout && $ev_logout_cutoff && $now_time >= $ev_logout && $now_time <= $ev_logout_cutoff);

            // Choose appropriate column (morning vs afternoon depending on event time)
            $col_shift = ($ev_login && $ev_login >= '12:00:00') ? 'afternoon' : 'morning';

            if ($in_login_window) {
                $target_column = 'time_in_' . $col_shift;
                $target_label = ($col_shift === 'afternoon' ? 'Afternoon' : 'Morning') . ' Time-In';
                if ($existing_att && $hasTime($existing_att[$target_column])) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "ALREADY_COMPLETED_SHIFT",
                        "student" => [
                            "full_name" => $student['full_name'],
                            "student_number" => $student['student_number'],
                            "course_code" => $student['course_code'] ?? 'OJT'
                        ],
                        "message" => "Already recorded {$target_label} today (" . date("h:i A", strtotime($existing_att[$target_column])) . ")."
                    ]);
                    exit();
                }
            } elseif ($in_logout_window) {
                $target_column = 'time_out_' . $col_shift;
                $target_label = ($col_shift === 'afternoon' ? 'Afternoon' : 'Morning') . ' Time-Out';
                if ($existing_att && $hasTime($existing_att[$target_column])) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "ALREADY_COMPLETED_SHIFT",
                        "student" => [
                            "full_name" => $student['full_name'],
                            "student_number" => $student['student_number'],
                            "course_code" => $student['course_code'] ?? 'OJT'
                        ],
                        "message" => "Already recorded {$target_label} today (" . date("h:i A", strtotime($existing_att[$target_column])) . ")."
                    ]);
                    exit();
                }
            } else {
                // Determine specific cutoff or out-of-window message
                if ($ev_login && $now_time < $ev_login) {
                    $msg = "Log-in has not started yet. Log-in starts at " . date("g:i A", strtotime($ev_login)) . " for event '{$ev_name}'.";
                } elseif ($ev_login_cutoff && $ev_logout && $now_time > $ev_login_cutoff && $now_time < $ev_logout) {
                    $msg = "Log-in cutoff reached (" . date("g:i A", strtotime($ev_login_cutoff)) . "). Log-in is closed. Log-out starts at " . date("g:i A", strtotime($ev_logout)) . ".";
                } elseif ($ev_logout_cutoff && $now_time > $ev_logout_cutoff) {
                    $msg = "Log-out cutoff reached (" . date("g:i A", strtotime($ev_logout_cutoff)) . "). Event '{$ev_name}' is closed.";
                } elseif ($ev_login_cutoff && !$ev_logout && $now_time > $ev_login_cutoff) {
                    $msg = "Log-in cutoff reached (" . date("g:i A", strtotime($ev_login_cutoff)) . "). Log-in is closed.";
                } else {
                    $msg = "Attendance window is closed for event '{$ev_name}'.";
                }

                echo json_encode([
                    "status" => "error",
                    "code" => "CUTOFF",
                    "student" => [
                        "full_name" => $student['full_name'],
                        "student_number" => $student['student_number']
                    ],
                    "message" => $msg
                ]);
                exit();
            }
        } else {
            // Explicit Shift Mode requested by kiosk operator
            $alias_map = [
                'morning_in' => 'time_in_morning',
                'time_in_morning' => 'time_in_morning',
                'morning_out' => 'time_out_morning',
                'time_out_morning' => 'time_out_morning',
                'afternoon_in' => 'time_in_afternoon',
                'time_in_afternoon' => 'time_in_afternoon',
                'afternoon_out' => 'time_out_afternoon',
                'time_out_afternoon' => 'time_out_afternoon'
            ];

            $shift_key = strtolower($shift_mode);
            if (!isset($alias_map[$shift_key])) {
                echo json_encode([
                    "status" => "error",
                    "code" => "INVALID_SHIFT",
                    "message" => "Invalid shift mode specified: " . htmlspecialchars($shift_mode)
                ]);
                exit();
            }

            $target_column = $alias_map[$shift_key];
            $target_label = $shift_labels[$target_column];

            // Validate cutoff in explicit mode
            $is_expl_login = (strpos($target_column, 'in') !== false);
            $is_expl_logout = (strpos($target_column, 'out') !== false);

            if ($is_expl_login) {
                if ($ev_login && $now_time < $ev_login) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "EARLY",
                        "message" => "Log-in has not started yet. Log-in starts at " . date("g:i A", strtotime($ev_login)) . " for event '{$ev_name}'."
                    ]);
                    exit();
                }
                if ($ev_login_cutoff && $now_time > $ev_login_cutoff) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "CUTOFF",
                        "message" => "Log-in cutoff reached (" . date("g:i A", strtotime($ev_login_cutoff)) . "). Log-in is closed for event '{$ev_name}'."
                    ]);
                    exit();
                }
            } elseif ($is_expl_logout) {
                if ($ev_logout && $now_time < $ev_logout) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "EARLY",
                        "message" => "Log-out has not started yet. Log-out starts at " . date("g:i A", strtotime($ev_logout)) . " for event '{$ev_name}'."
                    ]);
                    exit();
                }
                if ($ev_logout_cutoff && $now_time > $ev_logout_cutoff) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "CUTOFF",
                        "message" => "Log-out cutoff reached (" . date("g:i A", strtotime($ev_logout_cutoff)) . "). Log-out is closed for event '{$ev_name}'."
                    ]);
                    exit();
                }
            }

            // Validation for explicit shift
            if ($target_column === 'time_in_morning') {
                if ($existing_att && $hasTime($existing_att['time_in_morning'])) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "DUPLICATE_TIME_IN",
                        "message" => "Already recorded Morning Time-In today (" . date("h:i A", strtotime($existing_att['time_in_morning'])) . ")."
                    ]);
                    exit();
                }
            } elseif ($target_column === 'time_out_morning') {
                if (!$existing_att || !$hasTime($existing_att['time_in_morning'])) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "MISSING_TIME_IN",
                        "message" => "Cannot record Morning Time-Out without a Morning Time-In record."
                    ]);
                    exit();
                }
                if ($hasTime($existing_att['time_out_morning'])) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "DUPLICATE_TIME_OUT",
                        "message" => "Already recorded Morning Time-Out today (" . date("h:i A", strtotime($existing_att['time_out_morning'])) . ")."
                    ]);
                    exit();
                }
            } elseif ($target_column === 'time_in_afternoon') {
                if ($existing_att && $hasTime($existing_att['time_in_afternoon'])) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "DUPLICATE_TIME_IN",
                        "message" => "Already recorded Afternoon Time-In today (" . date("h:i A", strtotime($existing_att['time_in_afternoon'])) . ")."
                    ]);
                    exit();
                }
            } elseif ($target_column === 'time_out_afternoon') {
                if (!$existing_att || !$hasTime($existing_att['time_in_afternoon'])) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "MISSING_TIME_IN",
                        "message" => "Cannot record Afternoon Time-Out without an Afternoon Time-In record."
                    ]);
                    exit();
                }
                if ($hasTime($existing_att['time_out_afternoon'])) {
                    echo json_encode([
                        "status" => "error",
                        "code" => "DUPLICATE_TIME_OUT",
                        "message" => "Already recorded Afternoon Time-Out today (" . date("h:i A", strtotime($existing_att['time_out_afternoon'])) . ")."
                    ]);
                    exit();
                }
            }
        }

        // 4. Save Attendance Record
        $is_morning = (strpos($target_column, 'morning') !== false);
        $tap_remark = "ID Tap Kiosk ($target_label @ " . date("h:i A", strtotime($now_time)) . ")";

        if ($existing_att) {
            $att_id = intval($existing_att['attendance_id']);
            $prev_remarks = trim($existing_att['remarks'] ?? '');
            $new_remarks = !empty($prev_remarks) ? ($prev_remarks . " | " . $tap_remark) : $tap_remark;

            if ($is_morning) {
                $stmt_upd = $conn->prepare("
                    UPDATE attendance
                    SET $target_column = ?, site_id = COALESCE(site_id, ?), status = 'Pending', morning_status = 'Pending', remarks = ?
                    WHERE attendance_id = ?
                ");
                $stmt_upd->bind_param("sisi", $now_time, $active_site_id, $new_remarks, $att_id);
            } else {
                $stmt_upd = $conn->prepare("
                    UPDATE attendance
                    SET $target_column = ?, site_id = COALESCE(site_id, ?), status = 'Pending', afternoon_status = 'Pending', remarks = ?
                    WHERE attendance_id = ?
                ");
                $stmt_upd->bind_param("sisi", $now_time, $active_site_id, $new_remarks, $att_id);
            }
            $stmt_upd->execute();
            $stmt_upd->close();
        } else {
            $stmt_ins = $conn->prepare("
                INSERT INTO attendance (date, student_id, ojt_id, site_id, $target_column, status, morning_status, afternoon_status, remarks)
                VALUES (?, ?, ?, ?, ?, 'Pending', 'Pending', 'Pending', ?)
            ");
            $stmt_ins->bind_param("siiiss", $today, $student_id, $student_id, $active_site_id, $now_time, $tap_remark);
            $stmt_ins->execute();
            $att_id = $conn->insert_id;
            $stmt_ins->close();
        }

        // Synchronize ID Tap attendance to CIT Department System (Pending Dean Review - NOT Present)
        try {
            require_once __DIR__ . '/cit_sync_helper.php';
            syncAttendanceToCitSystem($conn, $ojt_id, $target_column, $today, $now_time, false);
        } catch (Throwable $eSync) {
            // Suppress sync error to avoid breaking kiosk response
        }

        // 5. Calculate cumulative completed hours for the student
        $rendered_hours = 0.0;
        $stmt_hours = $conn->prepare("
            SELECT time_in_morning, time_out_morning, time_in_afternoon, time_out_afternoon, morning_status, afternoon_status
            FROM attendance
            WHERE ojt_id = ? AND (morning_status != 'Rejected' OR afternoon_status != 'Rejected')
        ");
        if ($stmt_hours) {
            $stmt_hours->bind_param("i", $ojt_id);
            $stmt_hours->execute();
            $res_hours = $stmt_hours->get_result();
            while ($row_h = $res_hours->fetch_assoc()) {
                if ($hasTime($row_h['time_in_morning']) && $hasTime($row_h['time_out_morning']) && $row_h['morning_status'] !== 'Rejected') {
                    $diff = strtotime($row_h['time_out_morning']) - strtotime($row_h['time_in_morning']);
                    if ($diff > 0) $rendered_hours += ($diff / 3600);
                }
                if ($hasTime($row_h['time_in_afternoon']) && $hasTime($row_h['time_out_afternoon']) && $row_h['afternoon_status'] !== 'Rejected') {
                    $diff = strtotime($row_h['time_out_afternoon']) - strtotime($row_h['time_in_afternoon']);
                    if ($diff > 0) $rendered_hours += ($diff / 3600);
                }
            }
            $stmt_hours->close();
        }

        $required_hours = floatval($student['ojt_required_hours'] ?? $student['course_required_hours'] ?? 480);
        $motivational_msg = function_exists('getRandomMotivationalMessage') ? getRandomMotivationalMessage($target_column) : "Keep up the great work! Every hour counts toward your degree.";

        echo json_encode([
            "status" => "success",
            "message" => $motivational_msg,
            "motivational_message" => $motivational_msg,
            "recorded_time" => date("h:i:s A", strtotime($now_time)),
            "recorded_time_formatted" => date("g:i A", strtotime($now_time)),
            "shift_column" => $target_column,
            "shift_label" => $target_label,
            "student" => [
                "student_id" => $student['student_id'],
                "student_number" => $student['student_number'],
                "full_name" => $student['full_name'],
                "id_no" => $student['id_no'],
                "rfid_uid" => $student['rfid_uid'],
                "course_code" => $student['course_code'] ?? 'N/A',
                "course_name" => $student['course_name'] ?? 'College Program',
                "site_name" => $student['site_name'] ?? 'Main Campus / Assigned Facility',
                "site_location" => $student['site_location'] ?? 'SBC Campus',
                "rendered_hours" => round($rendered_hours, 1),
                "required_hours" => $required_hours,
                "progress_percent" => ($required_hours > 0) ? min(100, round(($rendered_hours / $required_hours) * 100, 1)) : 0
            ]
        ]);
        exit();
    }

    // -------------------------------------------------------------
    // ACTION: MAP RFID (Associates an RFID card UID to a student)
    // -------------------------------------------------------------
    elseif ($action === 'map_rfid') {
        $student_id = isset($data['student_id']) ? intval($data['student_id']) : 0;
        $rfid_uid = isset($data['rfid_uid']) ? trim($data['rfid_uid']) : '';

        if ($student_id <= 0 || empty($rfid_uid)) {
            echo json_encode([
                "status" => "error",
                "message" => "Please select a student and scan or enter an RFID card UID."
            ]);
            exit();
        }

        // Check if RFID card is already assigned to a different student
        $stmt_check = $conn->prepare("SELECT student_id, full_name, student_number FROM student WHERE rfid_uid = ? AND student_id != ? LIMIT 1");
        $stmt_check->bind_param("si", $rfid_uid, $student_id);
        $stmt_check->execute();
        $dup_res = $stmt_check->get_result();
        if ($dup_res && $dup_row = $dup_res->fetch_assoc()) {
            $stmt_check->close();
            echo json_encode([
                "status" => "error",
                "message" => "This RFID card UID (" . htmlspecialchars($rfid_uid) . ") is already mapped to " . $dup_row['full_name'] . " (" . $dup_row['student_number'] . ")."
            ]);
            exit();
        }
        $stmt_check->close();

        // Update student record
        $stmt_map = $conn->prepare("UPDATE student SET rfid_uid = ? WHERE student_id = ?");
        $stmt_map->bind_param("si", $rfid_uid, $student_id);
        if ($stmt_map->execute()) {
            $stmt_map->close();

            // Fetch student name for response
            $s_res = $conn->query("SELECT full_name, student_number FROM student WHERE student_id = $student_id");
            $s_info = ($s_res && $row = $s_res->fetch_assoc()) ? $row : null;

            echo json_encode([
                "status" => "success",
                "message" => "RFID card successfully mapped to " . ($s_info['full_name'] ?? 'student') . "!",
                "student_id" => $student_id,
                "rfid_uid" => $rfid_uid
            ]);
        } else {
            throw new Exception("Failed to save RFID card: " . $conn->error);
        }
        exit();
    }

    // -------------------------------------------------------------
    // ACTION: RECENT TAPS (Feed of latest check-ins today)
    // -------------------------------------------------------------
    elseif ($action === 'recent_taps') {
        $limit = isset($data['limit']) ? max(5, min(50, intval($data['limit']))) : 15;

        $sql = "
            SELECT a.attendance_id, a.date, a.time_in_morning, a.time_out_morning, a.time_in_afternoon, a.time_out_afternoon,
                   a.status, a.remarks, a.morning_status, a.afternoon_status,
                   s.student_id, s.student_number, s.full_name, s.id_no, s.rfid_uid,
                   c.course_code, 'CIT Campus' AS site_name
            FROM attendance a
            JOIN student s ON (a.student_id = s.student_id OR a.ojt_id = s.student_id)
            LEFT JOIN course c ON s.course_id = c.course_id
            WHERE a.date = ?
            ORDER BY a.attendance_id DESC
            LIMIT ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $today, $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $taps = [];
        while ($row = $res->fetch_assoc()) {
            // Determine latest punch
            $latest_shift = 'Morning In';
            $latest_time = $row['time_in_morning'];

            if (!empty($row['time_out_afternoon']) && $row['time_out_afternoon'] !== '00:00:00') {
                $latest_shift = 'Afternoon Out';
                $latest_time = $row['time_out_afternoon'];
            } elseif (!empty($row['time_in_afternoon']) && $row['time_in_afternoon'] !== '00:00:00') {
                $latest_shift = 'Afternoon In';
                $latest_time = $row['time_in_afternoon'];
            } elseif (!empty($row['time_out_morning']) && $row['time_out_morning'] !== '00:00:00') {
                $latest_shift = 'Morning Out';
                $latest_time = $row['time_out_morning'];
            }

            $is_id_tap = (strpos($row['remarks'] ?? '', 'ID Tap') !== false);

            $taps[] = [
                "attendance_id" => $row['attendance_id'],
                "student_id" => $row['student_id'],
                "full_name" => $row['full_name'],
                "student_number" => $row['student_number'],
                "course_code" => $row['course_code'] ?? 'OJT',
                "site_name" => $row['site_name'] ?? 'Main Campus',
                "latest_shift" => $latest_shift,
                "latest_time" => !empty($latest_time) ? date("g:i A", strtotime($latest_time)) : '--',
                "method" => $is_id_tap ? "ID Tap Station" : "Live Capture",
                "method_type" => $is_id_tap ? "tap" : "selfie"
            ];
        }
        $stmt->close();

        echo json_encode([
            "status" => "success",
            "date" => $today,
            "total_today" => count($taps),
            "taps" => $taps
        ]);
        exit();
    }

    // -------------------------------------------------------------
    // ACTION: KIOSK SUMMARY
    // -------------------------------------------------------------
    elseif ($action === 'kiosk_summary') {
        $stats = [
            "morning_in_count" => 0,
            "morning_out_count" => 0,
            "afternoon_in_count" => 0,
            "afternoon_out_count" => 0,
            "total_students_today" => 0,
            "server_time" => date("h:i:s A"),
            "active_window" => (function() use ($conn, $today, $now_time) {
                $ev_stmt = $conn->prepare("SELECT event_name, login_time, logout_time, login_cutoff_time, logout_cutoff_time, grace_period FROM events WHERE event_date = ? ORDER BY event_id ASC LIMIT 1");
                if (!$ev_stmt) return 'Closed';
                $ev_stmt->bind_param("s", $today);
                $ev_stmt->execute();
                $ev_info = $ev_stmt->get_result()->fetch_assoc();
                $ev_stmt->close();
                if (!$ev_info) return 'No Event Scheduled';
                $ev_login = $ev_info['login_time'];
                $ev_login_cutoff = $ev_info['login_cutoff_time'] ?: $ev_login;
                $ev_logout = $ev_info['logout_time'];
                $ev_logout_cutoff = $ev_info['logout_cutoff_time'] ?: $ev_logout;
                if ($ev_login && $ev_login_cutoff && $now_time >= $ev_login && $now_time <= $ev_login_cutoff) {
                    return 'Log-In Open (' . date("g:i A", strtotime($ev_login)) . ' - ' . date("g:i A", strtotime($ev_login_cutoff)) . ')';
                } elseif ($ev_logout && $ev_logout_cutoff && $now_time >= $ev_logout && $now_time <= $ev_logout_cutoff) {
                    return 'Log-Out Open (' . date("g:i A", strtotime($ev_logout)) . ' - ' . date("g:i A", strtotime($ev_logout_cutoff)) . ')';
                } elseif ($ev_login_cutoff && $now_time > $ev_login_cutoff && (!$ev_logout || $now_time < $ev_logout)) {
                    return 'Log-In Cutoff Reached';
                } elseif ($ev_logout_cutoff && $now_time > $ev_logout_cutoff) {
                    return 'Log-Out Cutoff Reached';
                }
                return 'Closed';
            })()
        ];

        $q = $conn->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN time_in_morning IS NOT NULL AND time_in_morning != '00:00:00' THEN 1 ELSE 0 END) as m_in,
                SUM(CASE WHEN time_out_morning IS NOT NULL AND time_out_morning != '00:00:00' THEN 1 ELSE 0 END) as m_out,
                SUM(CASE WHEN time_in_afternoon IS NOT NULL AND time_in_afternoon != '00:00:00' THEN 1 ELSE 0 END) as a_in,
                SUM(CASE WHEN time_out_afternoon IS NOT NULL AND time_out_afternoon != '00:00:00' THEN 1 ELSE 0 END) as a_out
            FROM attendance
            WHERE date = '$today'
        ");

        if ($q && $r = $q->fetch_assoc()) {
            $stats["total_students_today"] = intval($r['total'] ?? 0);
            $stats["morning_in_count"] = intval($r['m_in'] ?? 0);
            $stats["morning_out_count"] = intval($r['m_out'] ?? 0);
            $stats["afternoon_in_count"] = intval($r['a_in'] ?? 0);
            $stats["afternoon_out_count"] = intval($r['a_out'] ?? 0);
        }

        echo json_encode([
            "status" => "success",
            "summary" => $stats
        ]);
        exit();
    }

    else {
        echo json_encode([
            "status" => "error",
            "message" => "Unknown action requested."
        ]);
        exit();
    }

} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
}
?>
