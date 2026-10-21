<?php
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

try {
    require_once 'db_connect.php';

    $dean_id = isset($_GET['dean_id']) ? intval($_GET['dean_id']) : (isset($_POST['dean_id']) ? intval($_POST['dean_id']) : 0);

    if ($dean_id > 0) {
        $d_check = $conn->query("SELECT student_id FROM student WHERE dean_id = $dean_id LIMIT 1");
        if (!$d_check || $d_check->num_rows == 0) {
            $dean_id = 0; // Fallback to all logs if no students assigned specifically to this dean
        }
    }

    $sql = "SELECT DISTINCT
                a.attendance_id,
                a.date,
                a.login_time,
                a.logout_time,
                a.status AS attendance_status,
                a.remarks AS attendance_remarks,
                s.student_id,
                s.student_number,
                s.full_name,
                s.dean_id,
                ud.full_name AS dean_name,
                c.course_code,
                'CIT Event' AS site_name,
                COALESCE(s.year_level, cs.year_level, '4') AS year_level
            FROM attendance a
            JOIN student s ON (a.student_id = s.student_id OR a.ojt_id = s.student_id)
            LEFT JOIN users ud ON s.dean_id = ud.user_id
            LEFT JOIN course c ON s.course_id = c.course_id
            LEFT JOIN students cs ON (s.student_number = cs.student_id OR s.id_no = cs.student_id)";

    if ($dean_id > 0) {
        $sql .= " WHERE s.dean_id = " . intval($dean_id);
    }

    $sql .= " ORDER BY a.date DESC, a.attendance_id DESC";

    $result = $conn->query($sql);
    $logs = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $att_id = intval($row['attendance_id']);


            $p_stmt = $conn->prepare("SELECT photo_id, shift_type, image_path, captured_at FROM photo WHERE attendance_id = ? ORDER BY photo_id ASC");
            $p_stmt->bind_param("i", $att_id);
            $p_stmt->execute();
            $p_res = $p_stmt->get_result();

            $photos = [];
            $base_sys_url = function_exists('get_system_base_url') ? get_system_base_url() : '../';
            while ($p = $p_res->fetch_assoc()) {
                $clean_img_path = ltrim(str_replace('\\', '/', $p['image_path']), '/');
                $photos[] = [
                    "photo_id" => $p['photo_id'],
                    "shift_type" => str_replace('_', ' ', $p['shift_type']),
                    "image_path" => $clean_img_path,
                    "full_url" => $base_sys_url . $clean_img_path,
                    "captured_at" => date("h:i A", strtotime($p['captured_at']))
                ];
            }
            $p_stmt->close();

            $raw_att_status = $row['attendance_status'] ?? 'Pending';
            if ($raw_att_status === 'Confirmed') {
                $status = "Confirmed";
            } elseif ($raw_att_status === 'Rejected') {
                $status = "Rejected";
            } elseif ($raw_att_status === 'Partial') {
                $status = "Partial";
            } else {
                $status = "Pending";
            }

            $login_str = !empty($row['login_time']) ? date("h:i A", strtotime($row['login_time'])) : '--:--';
            $logout_str = !empty($row['logout_time']) ? date("h:i A", strtotime($row['logout_time'])) : '--:--';

            $logs[] = [
                "attendance_id" => intval($row['attendance_id']),
                "student_id" => intval($row['student_id'] ?? 0),
                "date" => date("M d, Y", strtotime($row['date'])),
                "raw_date" => $row['date'],
                "full_name" => $row['full_name'] ?? 'Unknown Student',
                "student_number" => $row['student_number'] ?? 'N/A',
                "course_code" => $row['course_code'] ?? 'BSIS',
                "year_level" => $row['year_level'] ?? '4',
                "site_name" => $row['site_name'] ?? 'CIT Campus',
                "login_time" => $login_str,
                "logout_time" => $logout_str,
                // Compatibility aliases
                "time_in" => $login_str,
                "time_out" => $logout_str,
                "time_in_morning" => $login_str,
                "time_out_morning" => '--:--',
                "time_in_afternoon" => '--:--',
                "time_out_afternoon" => $logout_str,
                "status" => $status,
                "remarks" => trim($row['attendance_remarks'] ?? ''),
                "morning_status" => $status,
                "morning_remarks" => trim($row['attendance_remarks'] ?? ''),
                "afternoon_status" => $status,
                "afternoon_remarks" => trim($row['attendance_remarks'] ?? ''),
                "is_confirmed" => ($raw_att_status === 'Confirmed'),
                "is_rejected" => ($raw_att_status === 'Rejected'),
                "photos" => $photos
            ];
        }
    }

    // Overall counts per course for logs
    $counts_sql = "SELECT COALESCE(c.course_code, 'BSIS') as course_code, COUNT(a.attendance_id) as total
                   FROM attendance a
                   JOIN student s ON (a.student_id = s.student_id OR a.ojt_id = s.student_id)
                   LEFT JOIN course c ON s.course_id = c.course_id";
    if ($dean_id > 0) {
        $counts_sql .= " WHERE s.dean_id = " . intval($dean_id);
    }
    $counts_sql .= " GROUP BY COALESCE(c.course_code, 'BSIS')";
    $counts_res = $conn->query($counts_sql);
    $course_counts = [
        "ALL" => 0,
        "BSCS" => 0,
        "BSIS" => 0,
        "BLIS" => 0
    ];
    if ($counts_res && $counts_res->num_rows > 0) {
        while ($c_row = $counts_res->fetch_assoc()) {
            $code = strtoupper($c_row['course_code'] ?? 'BSIS');
            $cnt = intval($c_row['total'] ?? 0);
            $course_counts[$code] = $cnt;
            $course_counts["ALL"] += $cnt;
        }
    }

    echo json_encode([
        "status" => "success",
        "course_counts" => $course_counts,
        "data" => $logs
    ]);

    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>