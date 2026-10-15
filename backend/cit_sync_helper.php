<?php
/**
 * SBC Internship Attendance System & CIT Department System Bridge
 * Automatically synchronizes student attendance records into CIT Department System.
 * Ensures that whenever a student records attendance via Live Selfie Capture or ID Tap,
 * they are immediately marked and visible as "Present" in CIT Department System.
 */

if (!function_exists('ensureCitDepartmentTables')) {
    function ensureCitDepartmentTables($conn) {
        static $tables_checked = false;
        if ($tables_checked) return;

        try {
            // 1. CIT Students Table
            @$conn->query("
                CREATE TABLE IF NOT EXISTS students (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_id VARCHAR(50) NULL UNIQUE,
                    firstname VARCHAR(100) NOT NULL,
                    lastname VARCHAR(100) NOT NULL,
                    course VARCHAR(50) NOT NULL,
                    year_level VARCHAR(20) NOT NULL,
                    rfid_uid VARCHAR(100) NULL,
                    status VARCHAR(20) NOT NULL DEFAULT 'Active',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_students_name (lastname, firstname),
                    INDEX idx_students_status (status),
                    INDEX idx_students_rfid (rfid_uid)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // 2. CIT Events Table
            @$conn->query("
                CREATE TABLE IF NOT EXISTS events (
                    event_id INT AUTO_INCREMENT PRIMARY KEY,
                    event_name VARCHAR(150) NOT NULL,
                    event_date DATE NOT NULL,
                    login_time TIME NULL,
                    logout_time TIME NULL,
                    login_cutoff_time TIME NULL,
                    logout_cutoff_time TIME NULL,
                    grace_period INT NULL DEFAULT 0,
                    absent_fine DECIMAL(10, 2) NOT NULL DEFAULT 0,
                    late_fine DECIMAL(10, 2) NOT NULL DEFAULT 0,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_events_date (event_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // 3. CIT Event Attendance Table
            @$conn->query("
                CREATE TABLE IF NOT EXISTS cit_attendance (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    event_id INT NOT NULL,
                    student_id INT NOT NULL,
                    status VARCHAR(32) NOT NULL,
                    timestamp DATETIME NOT NULL,
                    INDEX idx_cit_att_event (event_id),
                    INDEX idx_cit_att_student (student_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // 4. CIT Paper / Direct Attendance Overrides
            @$conn->query("
                CREATE TABLE IF NOT EXISTS paper_attendance (
                    event_id INT NOT NULL,
                    student_id INT NOT NULL,
                    status VARCHAR(16) NOT NULL,
                    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (event_id, student_id),
                    INDEX idx_paper_student (student_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // 5. CIT Fines
            @$conn->query("
                CREATE TABLE IF NOT EXISTS fines (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_id VARCHAR(50) NOT NULL,
                    event_id INT NOT NULL,
                    fine_type VARCHAR(50) NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
                    assessed_amount DECIMAL(10, 2) NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_fine (student_id, event_id, fine_type),
                    INDEX idx_fines_student (student_id),
                    INDEX idx_fines_event (event_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // 6. CIT Transactions
            @$conn->query("
                CREATE TABLE IF NOT EXISTS transactions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    student_id VARCHAR(50) NOT NULL,
                    transaction_type VARCHAR(30) NOT NULL,
                    payment_type VARCHAR(30) NOT NULL,
                    amount DECIMAL(10, 2) NOT NULL,
                    academic_year VARCHAR(20) NOT NULL,
                    semester VARCHAR(20) NOT NULL,
                    created_by VARCHAR(80) NOT NULL,
                    transaction_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_transactions_student (student_id),
                    INDEX idx_transactions_date (transaction_date)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // 7. CIT Admins
            @$conn->query("
                CREATE TABLE IF NOT EXISTS admins (
                    admin_id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(80) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            $tables_checked = true;
        } catch (Throwable $t) {
            // Suppress errors during auto-creation
        }
    }
}

if (!function_exists('syncAttendanceToCitSystem')) {
    /**
     * Synchronizes an attendance action into CIT Department System tables.
     * If $is_confirmed is false, records status as 'pending' (Awaiting Dean Review),
     * and ensures the student DOES NOT display as Present in CIT Department System.
     * Only when $is_confirmed is true is the student marked as Present.
     */
    function syncAttendanceToCitSystem($conn, $ojt_id, $shift_column, $date = null, $time = null, $is_confirmed = false) {
        if (!$conn || $ojt_id <= 0) return false;

        $date = !empty($date) ? $date : date("Y-m-d");
        $time = !empty($time) ? $time : date("H:i:s");
        $full_timestamp = $date . " " . $time;

        try {
            ensureCitDepartmentTables($conn);

            // 1. Fetch Student Info
            $stmt_s = $conn->prepare("
                SELECT s.student_id, s.student_number, s.full_name, s.id_no, s.rfid_uid, c.course_code
                FROM student s
                LEFT JOIN course c ON s.course_id = c.course_id
                WHERE s.student_id = ?
                LIMIT 1
            ");
            if (!$stmt_s) return false;

            $stmt_s->bind_param("i", $ojt_id);
            $stmt_s->execute();
            $s_res = $stmt_s->get_result();
            if (!$s_res || $s_res->num_rows === 0) {
                $stmt_s->close();
                return false;
            }
            $student = $s_res->fetch_assoc();
            $stmt_s->close();

            $full_name = trim($student['full_name']);
            $student_number = trim($student['student_number'] ?? '');
            $id_no = trim($student['id_no'] ?? '');
            $rfid_uid = trim($student['rfid_uid'] ?? '');
            $course_code = !empty($student['course_code']) ? trim($student['course_code']) : 'BSCS';

            // Split name into First and Last
            $parts = explode(' ', $full_name);
            $firstname = count($parts) > 1 ? array_shift($parts) : $full_name;
            $lastname = count($parts) > 0 ? implode(' ', $parts) : 'Student';

            // 2. Find or Create Student in CIT's `students` table
            $cit_student_id = 0;
            $stmt_find_cit = $conn->prepare("
                SELECT id 
                FROM students 
                WHERE student_id = ? OR student_id = ? OR (rfid_uid IS NOT NULL AND rfid_uid != '' AND rfid_uid = ?)
                LIMIT 1
            ");
            if ($stmt_find_cit) {
                $stmt_find_cit->bind_param("sss", $student_number, $id_no, $rfid_uid);
                $stmt_find_cit->execute();
                $res_find = $stmt_find_cit->get_result();
                if ($row_find = $res_find->fetch_assoc()) {
                    $cit_student_id = intval($row_find['id']);
                }
                $stmt_find_cit->close();
            }

            if ($cit_student_id <= 0) {
                $s_id_val = !empty($student_number) ? $student_number : (!empty($id_no) ? $id_no : ('CIT-' . $student['student_id']));
                $stmt_ins_cit = $conn->prepare("
                    INSERT INTO students (student_id, firstname, lastname, course, year_level, rfid_uid, status)
                    VALUES (?, ?, ?, ?, '4', ?, 'Active')
                ");
                if ($stmt_ins_cit) {
                    $stmt_ins_cit->bind_param("sssss", $s_id_val, $firstname, $lastname, $course_code, $rfid_uid);
                    $stmt_ins_cit->execute();
                    $cit_student_id = $conn->insert_id;
                    $stmt_ins_cit->close();
                }
            }

            if ($cit_student_id <= 0) return false;

            // 3. Find or Create Today's Event in CIT's `events` table
            $cit_event_id = 0;
            $stmt_ev = $conn->prepare("
                SELECT event_id 
                FROM events 
                WHERE event_date = ? 
                ORDER BY event_id DESC 
                LIMIT 1
            ");
            if ($stmt_ev) {
                $stmt_ev->bind_param("s", $date);
                $stmt_ev->execute();
                $res_ev = $stmt_ev->get_result();
                if ($row_ev = $res_ev->fetch_assoc()) {
                    $cit_event_id = intval($row_ev['event_id']);
                }
                $stmt_ev->close();
            }

            if ($cit_event_id <= 0) {
                $event_name = "Daily Department Attendance - " . date("M j, Y", strtotime($date));
                $stmt_ins_ev = $conn->prepare("
                    INSERT INTO events (event_name, event_date, login_time, logout_time, login_cutoff_time, logout_cutoff_time, grace_period, absent_fine, late_fine)
                    VALUES (?, ?, '05:00:00', '19:00:00', '12:30:00', '19:00:00', 30, 0, 0)
                ");
                if ($stmt_ins_ev) {
                    $stmt_ins_ev->bind_param("ss", $event_name, $date);
                    $stmt_ins_ev->execute();
                    $cit_event_id = $conn->insert_id;
                    $stmt_ins_ev->close();
                }
            }

            if ($cit_event_id <= 0) return false;

            if ($is_confirmed) {
                // CONFIRMED by Dean of SBC: mark as login/logout and set paper Present
                $cit_status = (strpos($shift_column, 'out') !== false) ? 'logout' : 'login';

                // Update any existing pending record
                $conn->query("UPDATE cit_attendance SET status = '$cit_status', timestamp = '$full_timestamp' WHERE event_id = $cit_event_id AND student_id = $cit_student_id AND status = 'pending'");

                $stmt_check_att = $conn->prepare("
                    SELECT id 
                    FROM cit_attendance 
                    WHERE event_id = ? AND student_id = ? AND status = ?
                    LIMIT 1
                ");
                $att_exists = false;
                if ($stmt_check_att) {
                    $stmt_check_att->bind_param("iis", $cit_event_id, $cit_student_id, $cit_status);
                    $stmt_check_att->execute();
                    $att_exists = ($stmt_check_att->get_result()->num_rows > 0);
                    $stmt_check_att->close();
                }

                if (!$att_exists) {
                    $stmt_ins_att = $conn->prepare("
                        INSERT INTO cit_attendance (event_id, student_id, status, timestamp)
                        VALUES (?, ?, ?, ?)
                    ");
                    if ($stmt_ins_att) {
                        $stmt_ins_att->bind_param("iiss", $cit_event_id, $cit_student_id, $cit_status, $full_timestamp);
                        $stmt_ins_att->execute();
                        $stmt_ins_att->close();
                    }
                }

                $stmt_paper = $conn->prepare("
                    INSERT INTO paper_attendance (event_id, student_id, status)
                    VALUES (?, ?, 'present')
                    ON DUPLICATE KEY UPDATE status = 'present'
                ");
                if ($stmt_paper) {
                    $stmt_paper->bind_param("ii", $cit_event_id, $cit_student_id);
                    $stmt_paper->execute();
                    $stmt_paper->close();
                }
            } else {
                // NOT YET CONFIRMED by Dean (Student took Live Capture / Tap):
                // Record in cit_attendance with status = 'pending' so it is NOT counted as 'Present'.
                // Clear any paper_attendance present mark so CIT DOES NOT DISPLAY PRESENT!
                $stmt_check_p = $conn->prepare("
                    SELECT id 
                    FROM cit_attendance 
                    WHERE event_id = ? AND student_id = ? AND status IN ('pending', 'login')
                    LIMIT 1
                ");
                $p_exists = false;
                if ($stmt_check_p) {
                    $stmt_check_p->bind_param("ii", $cit_event_id, $cit_student_id);
                    $stmt_check_p->execute();
                    $p_exists = ($stmt_check_p->get_result()->num_rows > 0);
                    $stmt_check_p->close();
                }

                if (!$p_exists) {
                    $stmt_ins_att = $conn->prepare("
                        INSERT INTO cit_attendance (event_id, student_id, status, timestamp)
                        VALUES (?, ?, ?, ?)
                    ");
                    if ($stmt_ins_att) {
                        $cit_p = 'pending';
                        $stmt_ins_att->bind_param("iiss", $cit_event_id, $cit_student_id, $cit_p, $full_timestamp);
                        $stmt_ins_att->execute();
                        $stmt_ins_att->close();
                    }
                }

                // CRITICAL REQUIREMENT: DO NOT DISPLAY PRESENT IF NOT CONFIRMED IN DEAN
                // Remove present override from paper_attendance until Dean confirms
                $conn->query("DELETE FROM paper_attendance WHERE event_id = $cit_event_id AND student_id = $cit_student_id");
            }

            return true;
        } catch (Throwable $ex) {
            error_log("CIT Sync Warning: " . $ex->getMessage());
            return false;
        }
    }
}

if (!function_exists('syncDeanConfirmationToCitSystem')) {
    /**
     * Called when the Dean reviews and confirms or rejects an attendance record in SBC System.
     * When Confirmed: Marks student as Present in CIT Department System.
     * When Rejected: Removes Present mark in CIT Department System.
     */
    function syncDeanConfirmationToCitSystem($conn, $attendance_id, $shift = 'both', $action = 'Confirmed') {
        if (!$conn || $attendance_id <= 0) return false;

        try {
            ensureCitDepartmentTables($conn);

            // 1. Fetch attendance log and student details
            $stmt = $conn->prepare("
                SELECT 
                    a.attendance_id, a.date, a.time_in_morning, a.time_out_morning,
                    a.time_in_afternoon, a.time_out_afternoon, a.status,
                    a.morning_status, a.afternoon_status, a.student_id,
                    s.student_id AS sbc_student_id, s.student_number, s.id_no, s.rfid_uid, s.full_name,
                    c.course_code
                FROM attendance a
                JOIN student s ON (a.student_id = s.student_id OR a.ojt_id = s.student_id)
                LEFT JOIN course c ON s.course_id = c.course_id
                WHERE a.attendance_id = ?
                LIMIT 1
            ");
            if (!$stmt) return false;
            $stmt->bind_param("i", $attendance_id);
            $stmt->execute();
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $stmt->close();

            if (!$row) return false;

            $date = $row['date'];
            $student_number = trim($row['student_number'] ?? '');
            $id_no = trim($row['id_no'] ?? '');
            $rfid_uid = trim($row['rfid_uid'] ?? '');
            $full_name = trim($row['full_name']);
            $course_code = !empty($row['course_code']) ? trim($row['course_code']) : 'BSCS';

            $parts = explode(' ', $full_name);
            $firstname = count($parts) > 1 ? array_shift($parts) : $full_name;
            $lastname = count($parts) > 0 ? implode(' ', $parts) : 'Student';

            // 2. Find or Create Student in CIT's `students` table
            $cit_student_id = 0;
            $stmt_find = $conn->prepare("
                SELECT id FROM students 
                WHERE student_id = ? OR student_id = ? OR (rfid_uid IS NOT NULL AND rfid_uid != '' AND rfid_uid = ?)
                LIMIT 1
            ");
            if ($stmt_find) {
                $stmt_find->bind_param("sss", $student_number, $id_no, $rfid_uid);
                $stmt_find->execute();
                $find_res = $stmt_find->get_result();
                if ($find_row = $find_res->fetch_assoc()) {
                    $cit_student_id = intval($find_row['id']);
                }
                $stmt_find->close();
            }

            if ($cit_student_id <= 0) {
                $s_id_val = !empty($student_number) ? $student_number : (!empty($id_no) ? $id_no : ('CIT-' . $row['sbc_student_id']));
                $stmt_ins_cit = $conn->prepare("
                    INSERT INTO students (student_id, firstname, lastname, course, year_level, rfid_uid, status)
                    VALUES (?, ?, ?, ?, '4', ?, 'Active')
                ");
                if ($stmt_ins_cit) {
                    $stmt_ins_cit->bind_param("sssss", $s_id_val, $firstname, $lastname, $course_code, $rfid_uid);
                    $stmt_ins_cit->execute();
                    $cit_student_id = $conn->insert_id;
                    $stmt_ins_cit->close();
                }
            }

            if ($cit_student_id <= 0) return false;

            // 3. Find or Create Today's Event in CIT's `events` table
            $cit_event_id = 0;
            $stmt_ev = $conn->prepare("SELECT event_id FROM events WHERE event_date = ? ORDER BY event_id DESC LIMIT 1");
            if ($stmt_ev) {
                $stmt_ev->bind_param("s", $date);
                $stmt_ev->execute();
                $ev_res = $stmt_ev->get_result();
                if ($ev_row = $ev_res->fetch_assoc()) {
                    $cit_event_id = intval($ev_row['event_id']);
                }
                $stmt_ev->close();
            }

            if ($cit_event_id <= 0) {
                $event_name = "Daily Department Attendance - " . date("M j, Y", strtotime($date));
                $stmt_ins_ev = $conn->prepare("
                    INSERT INTO events (event_name, event_date, login_time, logout_time, login_cutoff_time, logout_cutoff_time, grace_period, absent_fine, late_fine)
                    VALUES (?, ?, '05:00:00', '19:00:00', '12:30:00', '19:00:00', 30, 0, 0)
                ");
                if ($stmt_ins_ev) {
                    $stmt_ins_ev->bind_param("ss", $event_name, $date);
                    $stmt_ins_ev->execute();
                    $cit_event_id = $conn->insert_id;
                    $stmt_ins_ev->close();
                }
            }

            if ($cit_event_id <= 0) return false;

            // 4. Action evaluation
            if ($action === 'Confirmed') {
                $login_time_val = !empty($row['time_in_morning']) ? $row['time_in_morning'] : (!empty($row['time_in_afternoon']) ? $row['time_in_afternoon'] : date("H:i:s"));
                $login_dt = $date . " " . $login_time_val;

                // Update any pending records to 'login'
                $conn->query("UPDATE cit_attendance SET status = 'login', timestamp = '$login_dt' WHERE event_id = $cit_event_id AND student_id = $cit_student_id AND status IN ('pending', 'pending_review', 'pending_dean')");

                // Ensure a login record exists
                $chk_l = $conn->query("SELECT id FROM cit_attendance WHERE event_id = $cit_event_id AND student_id = $cit_student_id AND status = 'login' LIMIT 1");
                if (!$chk_l || $chk_l->num_rows == 0) {
                    $stmt_ins_l = $conn->prepare("INSERT INTO cit_attendance (event_id, student_id, status, timestamp) VALUES (?, ?, 'login', ?)");
                    if ($stmt_ins_l) {
                        $stmt_ins_l->bind_param("iis", $cit_event_id, $cit_student_id, $login_dt);
                        $stmt_ins_l->execute();
                        $stmt_ins_l->close();
                    }
                }

                // If logout is present, record logout as well
                $logout_time_val = !empty($row['time_out_afternoon']) ? $row['time_out_afternoon'] : (!empty($row['time_out_morning']) ? $row['time_out_morning'] : null);
                if ($logout_time_val) {
                    $logout_dt = $date . " " . $logout_time_val;
                    $chk_lo = $conn->query("SELECT id FROM cit_attendance WHERE event_id = $cit_event_id AND student_id = $cit_student_id AND status = 'logout' LIMIT 1");
                    if (!$chk_lo || $chk_lo->num_rows == 0) {
                        $stmt_ins_lo = $conn->prepare("INSERT INTO cit_attendance (event_id, student_id, status, timestamp) VALUES (?, ?, 'logout', ?)");
                        if ($stmt_ins_lo) {
                            $stmt_ins_lo->bind_param("iis", $cit_event_id, $cit_student_id, $logout_dt);
                            $stmt_ins_lo->execute();
                            $stmt_ins_lo->close();
                        }
                    }
                }

                // NOW CONFIRMED: Mark as Present in paper_attendance
                $stmt_paper = $conn->prepare("
                    INSERT INTO paper_attendance (event_id, student_id, status)
                    VALUES (?, ?, 'present')
                    ON DUPLICATE KEY UPDATE status = 'present'
                ");
                if ($stmt_paper) {
                    $stmt_paper->bind_param("ii", $cit_event_id, $cit_student_id);
                    $stmt_paper->execute();
                    $stmt_paper->close();
                }

                return true;
            } else {
                // Rejected by Dean:
                // Update cit_attendance to 'rejected'
                $conn->query("UPDATE cit_attendance SET status = 'rejected' WHERE event_id = $cit_event_id AND student_id = $cit_student_id");
                // Remove present status so CIT DOES NOT DISPLAY PRESENT
                $conn->query("DELETE FROM paper_attendance WHERE event_id = $cit_event_id AND student_id = $cit_student_id");

                return true;
            }

        } catch (Throwable $e) {
            error_log("syncDeanConfirmationToCitSystem error: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('syncStudentOjtAttendanceConfirmationToCit')) {
    /**
     * Synchronizes a student's OJT attendance confirmation (such as group photo tagging) to CIT.
     */
    function syncStudentOjtAttendanceConfirmationToCit($conn, $student_id, $date, $shift = 'both') {
        if (!$conn || $student_id <= 0) return false;

        $student_id = intval($student_id);
        $date_esc = $conn->real_escape_string($date);
        $check = $conn->query("
            SELECT a.attendance_id 
            FROM attendance a 
            WHERE a.student_id = $student_id AND a.date = '$date_esc' 
            ORDER BY a.attendance_id DESC 
            LIMIT 1
        ");
        if ($check && $row = $check->fetch_assoc()) {
            return syncDeanConfirmationToCitSystem($conn, intval($row['attendance_id']), $shift, 'Confirmed');
        }
        return false;
    }
}
