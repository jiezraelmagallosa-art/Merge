<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: admin_get_students.php
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

try {
    // Connect to MySQL database
require_once 'db_connect.php';

    $dean_id = isset($_GET['dean_id']) ? intval($_GET['dean_id']) : (isset($_POST['dean_id']) ? intval($_POST['dean_id']) : 0);
    $course_code_filter = isset($_GET['course_code']) ? trim($_GET['course_code']) : (isset($_POST['course_code']) ? trim($_POST['course_code']) : '');
    $course_id_filter = isset($_GET['course_id']) ? intval($_GET['course_id']) : (isset($_POST['course_id']) ? intval($_POST['course_id']) : 0);
    $show_all = isset($_GET['all']) ? intval($_GET['all']) : (isset($_POST['all']) ? intval($_POST['all']) : 0);

    $sql = "SELECT
                s.student_id,
                s.student_number,
                s.full_name,
                s.id_no,
                s.rfid_uid,
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
                ts.site_id,
                ts.site_code,
                ts.site_name,
                ts.location AS site_location
            FROM student s
            LEFT JOIN users u ON s.dean_id = u.user_id
            LEFT JOIN course c ON s.course_id = c.course_id
            LEFT JOIN ojt o ON s.student_id = o.student_id
            LEFT JOIN training_site ts ON o.site_id = ts.site_id";

    $where = [];
    $has_specific_students = false;
    if ($dean_id > 0 && !$show_all) {
        $d_check = $conn->query("SELECT student_id FROM student WHERE dean_id = " . intval($dean_id) . " LIMIT 1");
        if ($d_check && $d_check->num_rows > 0) {
            $has_specific_students = true;
            $where[] = "s.dean_id = " . intval($dean_id);
        }
    }

    if (!empty($course_code_filter) && $course_code_filter !== 'ALL') {
        $where[] = "c.course_code = '" . $conn->real_escape_string($course_code_filter) . "'";
    } elseif ($course_id_filter > 0) {
        $where[] = "s.course_id = " . intval($course_id_filter);
    }

    if (count($where) > 0) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }

    $sql .= " ORDER BY s.full_name ASC";

    $result = $conn->query($sql);
    $students = [];

    // Overall counts per course for summary badges
    $course_counts = ["ALL" => 0];
    $all_courses_res = $conn->query("SELECT course_code FROM course ORDER BY course_code ASC");
    if ($all_courses_res && $all_courses_res->num_rows > 0) {
        while ($ac_row = $all_courses_res->fetch_assoc()) {
            $code = strtoupper($ac_row['course_code'] ?? '');
            if (!empty($code)) {
                $course_counts[$code] = 0;
            }
        }
    }

    $counts_sql = "SELECT c.course_code, COUNT(s.student_id) as total FROM course c LEFT JOIN student s ON c.course_id = s.course_id";
    if ($has_specific_students && $dean_id > 0 && !$show_all) {
        $counts_sql .= " WHERE s.dean_id = " . intval($dean_id);
    }
    $counts_sql .= " GROUP BY c.course_code";
    $counts_res = $conn->query($counts_sql);
    if ($counts_res && $counts_res->num_rows > 0) {
        while ($c_row = $counts_res->fetch_assoc()) {
            $code = strtoupper($c_row['course_code'] ?? '');
            $cnt = intval($c_row['total'] ?? 0);
            if (!empty($code)) {
                $course_counts[$code] = $cnt;
            }
            $course_counts["ALL"] += $cnt;
        }
    }

    $m_min = "CASE WHEN time_in_morning IS NOT NULL AND time_out_morning IS NOT NULL AND time_out_morning > time_in_morning THEN TIMESTAMPDIFF(MINUTE, time_in_morning, time_out_morning) ELSE 0 END";
    $a_min = "CASE WHEN time_in_afternoon IS NOT NULL AND time_out_afternoon IS NOT NULL AND time_out_afternoon > time_in_afternoon THEN TIMESTAMPDIFF(MINUTE, time_in_afternoon, time_out_afternoon) ELSE 0 END";

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $ojt_id = intval($row['ojt_id'] ?? 0);
            $req_h = intval($row['required_hours'] ?? ($row['course_required_hours'] ?? 480));
            if ($req_h <= 0) {
                $req_h = intval($row['course_required_hours'] ?? 480);
                if ($req_h <= 0) $req_h = 480;
            }

            require_once 'site_helper.php';
            $s_breakdown = getStudentSiteBreakdown($conn, intval($row['student_id']));
            $total_min = $s_breakdown['total_minutes'];

            // Total days across all sites
            $total_days = 0;
            $days_stmt = $conn->prepare("SELECT COUNT(DISTINCT a.date) as total_days FROM attendance a JOIN ojt o ON a.ojt_id = o.ojt_id WHERE o.student_id = ? AND (a.status IS NULL OR a.status != 'Rejected') AND (((a.morning_status IS NULL OR a.morning_status != 'Rejected') AND a.time_in_morning IS NOT NULL) OR ((a.afternoon_status IS NULL OR a.afternoon_status != 'Rejected') AND a.time_in_afternoon IS NOT NULL))");
            if ($days_stmt) {
                $days_stmt->bind_param("i", $row['student_id']);
                $days_stmt->execute();
                $total_days = intval($days_stmt->get_result()->fetch_assoc()['total_days'] ?? 0);
                $days_stmt->close();
            }

            $rendered_hours = floor($total_min / 60);
            $rem_mins = $total_min % 60;
            $progress = min(100.0, round(($total_min / ($req_h * 60)) * 100, 1));

            $status = "In Progress";
            if ($progress >= 100.0) {
                $status = "Completed";
            } elseif ($total_min == 0) {
                $status = "Not Started";
            }

            $students[] = [
                "student_id" => $row['student_id'],
                "student_number" => $row['student_number'],
                "full_name" => $row['full_name'],
                "id_no" => $row['id_no'],
                "rfid_uid" => $row['rfid_uid'] ?? '',
                "email" => $row['email'],
                "dean_id" => $row['dean_id'],
                "dean_name" => $row['dean_name'] ?? 'Unassigned Dean',
                "dean_email" => $row['dean_email'] ?? '',
                "course_id" => $row['course_id'],
                "course_code" => $row['course_code'] ?? 'BSIS',
                "course_name" => $row['course_name'] ?? 'BS Information Systems',
                "course_required_hours" => intval($row['course_required_hours'] ?? 480),
                "site_id" => $row['site_id'] ?? 1,
                "site_name" => $row['site_name'] ?? 'SBC IT Department',
                "site_location" => $row['site_location'] ?? 'M\'lang, Cotabato',
                "ojt_no" => $row['ojt_no'] ?? 'OJT-2026-01',
                "required_hours" => $req_h,
                "rendered_hours" => $rendered_hours,
                "rendered_minutes" => $rem_mins,
                "formatted_time" => "{$rendered_hours}h {$rem_mins}m",
                "progress_percentage" => $progress,
                "total_days" => $total_days,
                "status" => $status,
                "site_breakdown" => $s_breakdown['sites']
            ];
        }
    }

    echo json_encode([
        "status" => "success",
        "course_counts" => $course_counts,
        "data" => $students
    ]);

    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
