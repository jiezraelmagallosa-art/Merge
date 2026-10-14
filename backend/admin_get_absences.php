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
            $dean_id = 0; // Fallback to all absence requests if no students assigned specifically to this dean
        }
    }

    $sql = "SELECT
                ar.absence_id,
                ar.date_absent,
                ar.reason,
                ar.supporting_document,
                ar.status,
                ar.remarks,
                ar.created_at,
                s.student_id,
                s.student_number,
                s.full_name,
                s.dean_id,
                ud.full_name AS dean_name,
                c.course_code,
                COALESCE(s.year_level, cs.year_level, '4') AS year_level,
                u.full_name AS reviewed_by_name
            FROM absence_requests ar
            JOIN student s ON ar.student_id = s.student_id
            LEFT JOIN users ud ON s.dean_id = ud.user_id
            LEFT JOIN course c ON s.course_id = c.course_id
            LEFT JOIN users u ON ar.reviewed_by = u.user_id
            LEFT JOIN students cs ON (s.student_number = cs.student_id OR s.id_no = cs.student_id)";

    if ($dean_id > 0) {
        $sql .= " WHERE s.dean_id = " . intval($dean_id);
    }

    $sql .= " ORDER BY ar.absence_id DESC";

    $result = $conn->query($sql);
    $absences = [];

    if ($result && $result->num_rows > 0) {
        $base_sys_url = function_exists('get_system_base_url') ? get_system_base_url() : '../';
        while ($row = $result->fetch_assoc()) {
            $doc_url = null;
            if (!empty($row['supporting_document'])) {
                $doc_url = $base_sys_url . $row['supporting_document'];
            }

            $absences[] = [
                "absence_id" => $row['absence_id'],
                "date_absent" => date("M d, Y", strtotime($row['date_absent'])),
                "raw_date" => $row['date_absent'],
                "reason" => $row['reason'],
                "supporting_document" => $row['supporting_document'],
                "doc_url" => $doc_url,
                "status" => $row['status'],
                "remarks" => $row['remarks'] ?? '',
                "created_at" => date("M d, Y h:i A", strtotime($row['created_at'])),
                "student_id" => $row['student_id'],
                "student_number" => $row['student_number'],
                "full_name" => $row['full_name'],
                "dean_name" => $row['dean_name'] ?? 'Unassigned Dean',
                "course_code" => $row['course_code'] ?? 'BSIS',
                "year_level" => $row['year_level'] ?? '4',
                "reviewed_by_name" => $row['reviewed_by_name'] ?? 'Pending Evaluation'
            ];
        }
    }

    echo json_encode([
        "status" => "success",
        "data" => $absences
    ]);

    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
