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

    $today = date("Y-m-d");
    $dean_id = isset($_GET['dean_id']) ? intval($_GET['dean_id']) : (isset($_POST['dean_id']) ? intval($_POST['dean_id']) : 0);

    if ($dean_id > 0) {
        $d_check = $conn->query("SELECT student_id FROM student WHERE dean_id = $dean_id LIMIT 1");
        if (!$d_check || $d_check->num_rows == 0) {
            $dean_id = 0; // Fallback to all students if no students assigned specifically to this dean
        }
    }


    $sql_interns = "SELECT COUNT(*) AS total FROM student";
    if ($dean_id > 0) $sql_interns .= " WHERE dean_id = $dean_id";
    $res_interns = $conn->query($sql_interns);
    $total_interns = intval($res_interns->fetch_assoc()['total'] ?? 0);


    if ($dean_id > 0) {
        $sql_shifts = "SELECT COUNT(*) AS total FROM attendance a JOIN student s ON (a.student_id = s.student_id OR a.ojt_id = s.student_id) WHERE a.date = '$today' AND s.dean_id = $dean_id";
    } else {
        $sql_shifts = "SELECT COUNT(*) AS total FROM attendance WHERE date = '$today'";
    }
    $res_shifts = $conn->query($sql_shifts);
    $active_shifts_today = intval($res_shifts->fetch_assoc()['total'] ?? 0);


    if ($dean_id > 0) {
        $sql_absences = "SELECT COUNT(*) AS total FROM absence_requests ar JOIN student s ON ar.student_id = s.student_id WHERE ar.status = 'Pending' AND s.dean_id = $dean_id";
    } else {
        $sql_absences = "SELECT COUNT(*) AS total FROM absence_requests WHERE status = 'Pending'";
    }
    $res_absences = $conn->query($sql_absences);
    $pending_absences = intval($res_absences->fetch_assoc()['total'] ?? 0);


    $m_min = "CASE WHEN (a.status IS NULL OR a.status != 'Rejected') AND (a.morning_status IS NULL OR a.morning_status != 'Rejected') AND a.time_in_morning IS NOT NULL AND a.time_out_morning IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, a.time_in_morning, a.time_out_morning) ELSE 0 END";
    $a_min = "CASE WHEN (a.status IS NULL OR a.status != 'Rejected') AND (a.afternoon_status IS NULL OR a.afternoon_status != 'Rejected') AND a.time_in_afternoon IS NOT NULL AND a.time_out_afternoon IS NOT NULL THEN TIMESTAMPDIFF(MINUTE, a.time_in_afternoon, a.time_out_afternoon) ELSE 0 END";

    if ($dean_id > 0) {
        $sql_hours = "SELECT SUM($m_min + $a_min) AS total_min FROM attendance a JOIN student s ON (a.student_id = s.student_id OR a.ojt_id = s.student_id) WHERE s.dean_id = $dean_id";
    } else {
        $sql_hours = "SELECT SUM($m_min + $a_min) AS total_min FROM attendance a";
    }
    $res_hours = $conn->query($sql_hours);
    $total_minutes = intval($res_hours->fetch_assoc()['total_min'] ?? 0);
    $total_rendered_hours = max(0, (int)floor($total_minutes / 60));


    $target_total = max(1, $total_interns);
    $completion_rate = 100.0;
    $total_sites = 0;


    $sql_photos = "SELECT
                    p.photo_id,
                    p.attendance_id,
                    p.shift_type,
                    p.image_path,
                    p.captured_at,
                    a.date,
                    s.full_name,
                    s.student_number,
                    c.course_code
                   FROM photo p
                   JOIN attendance a ON p.attendance_id = a.attendance_id
                   JOIN student s ON (a.student_id = s.student_id OR a.ojt_id = s.student_id)
                   LEFT JOIN course c ON s.course_id = c.course_id";
    $where_p = ["(a.status IS NULL OR a.status != 'Confirmed')"];
    if ($dean_id > 0) {
        $where_p[] = "s.dean_id = $dean_id";
    }
    $sql_photos .= " WHERE " . implode(" AND ", $where_p);
    $sql_photos .= " ORDER BY p.photo_id DESC LIMIT 8";
    $res_photos = $conn->query($sql_photos);

    $live_captures = [];
    if ($res_photos && $res_photos->num_rows > 0) {
        $base_sys_url = function_exists('get_system_base_url') ? get_system_base_url() : '../';
        while ($p = $res_photos->fetch_assoc()) {
            $clean_img_path = ltrim(str_replace('\\', '/', $p['image_path']), '/');
            $live_captures[] = [
                "photo_id" => $p['photo_id'],
                "attendance_id" => intval($p['attendance_id'] ?? 0),
                "full_name" => $p['full_name'],
                "student_number" => $p['student_number'],
                "course_code" => $p['course_code'] ?? 'BSIS',
                "shift_type" => str_replace('_', ' ', $p['shift_type']),
                "image_path" => $clean_img_path,
                "full_url" => $base_sys_url . $clean_img_path,
                "captured_time" => date("h:i A", strtotime($p['captured_at'])),
                "date" => date("M d, Y", strtotime($p['date']))
            ];
        }
    }

    echo json_encode([
        "status" => "success",
        "kpis" => [
            "total_interns" => $total_interns,
            "active_shifts_today" => $active_shifts_today,
            "pending_absences" => $pending_absences,
            "total_rendered_hours" => $total_rendered_hours,
            "completion_rate" => $completion_rate,
            "total_sites" => $total_sites
        ],
        "recent_captures" => $live_captures
    ]);

    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
