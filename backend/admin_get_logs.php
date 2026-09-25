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
                a.time_in_morning,
                a.time_out_morning,
                a.time_in_afternoon,
                a.time_out_afternoon,
                a.status AS attendance_status,
                a.remarks AS attendance_remarks,
                a.morning_status,
                a.morning_remarks,
                a.afternoon_status,
                a.afternoon_remarks,
                s.student_id,
                s.student_number,
                s.full_name,
                s.dean_id,
                ud.full_name AS dean_name,
                c.course_code,
                ts.site_name
            FROM attendance a
            JOIN ojt o ON a.ojt_id = o.ojt_id
            JOIN student s ON o.student_id = s.student_id
            LEFT JOIN users ud ON s.dean_id = ud.user_id
            LEFT JOIN course c ON s.course_id = c.course_id
            LEFT JOIN training_site ts ON o.site_id = ts.site_id";

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

            $m_in = !empty($row['time_in_morning']) ? date("h:i A", strtotime($row['time_in_morning'])) : '--:--';
            $m_out = (!empty($row['time_out_morning']) && (empty($row['time_in_morning']) || strtotime($row['time_out_morning']) > strtotime($row['time_in_morning'])))
                ? date("h:i A", strtotime($row['time_out_morning']))
                : '--:--';

            $a_in = !empty($row['time_in_afternoon']) ? date("h:i A", strtotime($row['time_in_afternoon'])) : '--:--';
            $a_out = (!empty($row['time_out_afternoon']) && (empty($row['time_in_afternoon']) || strtotime($row['time_out_afternoon']) > strtotime($row['time_in_afternoon'])))
                ? date("h:i A", strtotime($row['time_out_afternoon']))
                : '--:--';

                $m_remarks = trim(preg_replace('/\s*\/\s*in car/i', '', $row['morning_remarks'] ?? ''));
                if (!empty($m_in) && !empty($m_out) && $m_in !== '--:--' && $m_out !== '--:--' && ($row['morning_status'] ?? '') === 'Rejected') {
                    if (strcasecmp($m_remarks, 'Morning Time-Out taken off-site') === 0 || strcasecmp($m_remarks, 'Morning Time-In taken off-site') === 0) {
                        $m_remarks = 'Morning Shift taken off-site';
                    }
                }

                $a_remarks = trim(preg_replace('/\s*\/\s*in car/i', '', $row['afternoon_remarks'] ?? ''));
                if (!empty($a_in) && !empty($a_out) && $a_in !== '--:--' && $a_out !== '--:--' && ($row['afternoon_status'] ?? '') === 'Rejected') {
                    if (strcasecmp($a_remarks, 'Afternoon Time-Out taken off-site') === 0 || strcasecmp($a_remarks, 'Afternoon Time-In taken off-site') === 0 || strcasecmp($a_remarks, 'Afternoon photo invalid / off-site') === 0) {
                        $a_remarks = 'Afternoon Shift taken off-site';
                    }
                }

                $logs[] = [
                    "attendance_id" => intval($row['attendance_id']),
                    "student_id" => intval($row['student_id'] ?? 0),
                    "date" => date("M d, Y", strtotime($row['date'])),
                    "raw_date" => $row['date'],
                    "full_name" => $row['full_name'] ?? 'Unknown Intern',
                    "student_number" => $row['student_number'] ?? 'N/A',
                    "course_code" => $row['course_code'] ?? 'BSIS',
                    "site_name" => $row['site_name'] ?? 'SBC IT Department',
                    "time_in_morning" => $m_in,
                    "time_out_morning" => $m_out,
                    "time_in_afternoon" => $a_in,
                    "time_out_afternoon" => $a_out,
                    "status" => $status,
                    "remarks" => trim(preg_replace('/\s*\/\s*in car/i', '', $row['attendance_remarks'] ?? '')),
                    "morning_status" => $row['morning_status'] ?? 'Pending',
                    "morning_remarks" => $m_remarks,
                    "afternoon_status" => $row['afternoon_status'] ?? 'Pending',
                    "afternoon_remarks" => $a_remarks,
                    "is_confirmed" => ($raw_att_status === 'Confirmed'),
                    "is_rejected" => ($raw_att_status === 'Rejected'),
                    "photos" => $photos
                ];
        }
    }

    // Overall counts per course for logs
    $counts_sql = "SELECT COALESCE(c.course_code, 'BSIS') as course_code, COUNT(a.attendance_id) as total
                   FROM attendance a
                   JOIN ojt o ON a.ojt_id = o.ojt_id
                   JOIN student s ON o.student_id = s.student_id
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