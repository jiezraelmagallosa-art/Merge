<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: admin_get_journals.php
 * Fetches all daily student journals for the Dean's Administrative Suite.
 */

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
            $dean_id = 0; // Fallback to all journals if no students strictly assigned to this dean
        }
    }

    $sql = "
        SELECT 
            j.journal_id,
            j.student_id,
            j.ojt_id,
            j.attendance_id,
            j.entry_date,
            DATE_FORMAT(j.entry_date, '%M %d, %Y') AS formatted_date,
            j.tasks_completed,
            j.learnings_reflection,
            j.challenges_encountered,
            j.dean_feedback,
            j.dean_status,
            j.reviewed_at,
            DATE_FORMAT(j.reviewed_at, '%b %d, %Y %h:%i %p') AS formatted_reviewed_at,
            j.created_at,
            s.student_number,
            s.full_name,
            s.id_no,
            s.email AS student_email,
            c.course_code,
            c.course_name,
            ts.site_name,
            ts.location AS site_location,
            COALESCE(s.year_level, cs.year_level, '4') AS year_level,
            a.time_in_morning,
            a.time_out_morning,
            a.time_in_afternoon,
            a.time_out_afternoon,
            a.status AS attendance_status
        FROM daily_journal j
        JOIN student s ON j.student_id = s.student_id
        LEFT JOIN course c ON s.course_id = c.course_id
        LEFT JOIN ojt o ON j.ojt_id = o.ojt_id
        LEFT JOIN training_site ts ON o.site_id = ts.site_id
        LEFT JOIN attendance a ON j.attendance_id = a.attendance_id
        LEFT JOIN students cs ON (s.student_number = cs.student_id OR s.id_no = cs.student_id)
    ";

    if ($dean_id > 0) {
        $sql .= " WHERE s.dean_id = " . intval($dean_id);
    }

    $sql .= " ORDER BY j.entry_date DESC, j.journal_id DESC";

    $result = $conn->query($sql);
    $journals = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $journals[] = [
                "journal_id"             => intval($row['journal_id']),
                "student_id"             => intval($row['student_id']),
                "student_number"         => $row['student_number'],
                "full_name"              => $row['full_name'],
                "id_no"                  => $row['id_no'] ?? '',
                "student_email"          => $row['student_email'],
                "course_code"            => $row['course_code'] ?? 'BSIS',
                "course_name"            => $row['course_name'] ?? 'Information Systems',
                "year_level"              => $row['year_level'] ?? '4',
                "site_name"              => $row['site_name'] ?? 'Training Facility',
                "site_location"          => $row['site_location'] ?? '',
                "raw_date"               => $row['entry_date'],
                "date"                   => $row['formatted_date'],
                "tasks_completed"        => $row['tasks_completed'],
                "learnings_reflection"   => $row['learnings_reflection'] ?? '',
                "challenges_encountered" => $row['challenges_encountered'] ?? '',
                "dean_feedback"          => $row['dean_feedback'] ?? '',
                "dean_status"            => $row['dean_status'] ?? 'Pending',
                "reviewed_at"            => $row['formatted_reviewed_at'],
                "time_in_morning"        => !empty($row['time_in_morning']) ? date("h:i A", strtotime($row['time_in_morning'])) : '--:--',
                "time_out_morning"       => !empty($row['time_out_morning']) ? date("h:i A", strtotime($row['time_out_morning'])) : '--:--',
                "time_in_afternoon"      => !empty($row['time_in_afternoon']) ? date("h:i A", strtotime($row['time_in_afternoon'])) : '--:--',
                "time_out_afternoon"     => !empty($row['time_out_afternoon']) ? date("h:i A", strtotime($row['time_out_afternoon'])) : '--:--',
                "attendance_status"      => $row['attendance_status'] ?? 'Pending'
            ];
        }
    }

    echo json_encode([
        "status" => "success",
        "count"  => count($journals),
        "data"   => $journals
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
