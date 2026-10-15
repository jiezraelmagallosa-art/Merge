<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: admin_year_end_reset.php
 * Handles year-end batch purging and storage cleanup for graduated cohorts.
 * 
 * Safety:
 * - Requires strict confirmation code 'RESET'.
 * - Preserves system configuration: course, training_site, users (Deans).
 * - Removes student records, cascading to ojt, attendance, photos, journals, and unlinks files from disk.
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

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["status" => "error", "message" => "POST method required."]);
        exit();
    }

    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);
    if (!$data) {
        $data = $_POST;
    }

    $confirmation = isset($data['confirmation_code']) ? trim($data['confirmation_code']) : '';
    $dean_id      = isset($data['dean_id']) ? intval($data['dean_id']) : 0;
    $scope        = isset($data['scope']) ? trim($data['scope']) : 'all_students';

    if ($confirmation !== 'RESET') {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid confirmation code. You must type RESET exactly to execute the year-end batch cleanup."
        ]);
        exit();
    }

    // 1. Identify target students to delete
    $query = "SELECT s.student_id FROM student s WHERE 1=1";
    $types = "";
    $params = [];

    if ($dean_id > 0) {
        $query .= " AND s.dean_id = ?";
        $types .= "i";
        $params[] = $dean_id;
    }

    $stmt = $conn->prepare($query);
    if (!empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $student_ids = [];
    while ($row = $res->fetch_assoc()) {
        $student_ids[] = intval($row['student_id']);
    }
    $stmt->close();

    if (empty($student_ids)) {
        echo json_encode([
            "status" => "success",
            "message" => "No student records found matching the reset criteria. Database is already clean.",
            "students_deleted" => 0,
            "photos_unlinked" => 0
        ]);
        exit();
    }

    $in_placeholders = implode(',', array_fill(0, count($student_ids), '?'));
    $in_types = str_repeat('i', count($student_ids));

    // 2. Locate and unlink image files from disk before deleting database records
    $project_root = dirname(__DIR__) . DIRECTORY_SEPARATOR;
    $unlinked_files = 0;

    // A. Facial Verification Photos
    $photo_query = "
        SELECT p.image_path 
        FROM photo p
        JOIN attendance a ON p.attendance_id = a.attendance_id
        WHERE a.student_id IN ($in_placeholders)
    ";
    $stmt_p = $conn->prepare($photo_query);
    $stmt_p->bind_param($in_types, ...$student_ids);
    $stmt_p->execute();
    $p_res = $stmt_p->get_result();

    while ($p_row = $p_res->fetch_assoc()) {
        $rel_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p_row['image_path']);
        $full_path = $project_root . ltrim($rel_path, DIRECTORY_SEPARATOR);
        if (file_exists($full_path) && is_file($full_path)) {
            if (@unlink($full_path)) {
                $unlinked_files++;
            }
        }
    }
    $stmt_p->close();

    // B. Absence Supporting Documents
    $doc_query = "SELECT supporting_document FROM absence_requests WHERE student_id IN ($in_placeholders) AND supporting_document IS NOT NULL";
    $stmt_d = $conn->prepare($doc_query);
    $stmt_d->bind_param($in_types, ...$student_ids);
    $stmt_d->execute();
    $d_res = $stmt_d->get_result();

    while ($d_row = $d_res->fetch_assoc()) {
        if (!empty($d_row['supporting_document'])) {
            $rel_doc = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $d_row['supporting_document']);
            $full_doc = $project_root . ltrim($rel_doc, DIRECTORY_SEPARATOR);
            if (file_exists($full_doc) && is_file($full_doc)) {
                if (@unlink($full_doc)) {
                    $unlinked_files++;
                }
            }
        }
    }
    $stmt_d->close();

    // 3. Delete student records (Triggers ON DELETE CASCADE for ojt, attendance, photo, daily_journal, absence_requests, student_site_history)
    $del_query = "DELETE FROM student WHERE student_id IN ($in_placeholders)";
    $stmt_del = $conn->prepare($del_query);
    $stmt_del->bind_param($in_types, ...$student_ids);
    $stmt_del->execute();
    $students_deleted = $stmt_del->affected_rows;
    $stmt_del->close();

    // 4. Clean up any remaining orphaned files in uploads directories
    $folders_to_clean = [
        $project_root . 'uploads' . DIRECTORY_SEPARATOR . 'live_captures' . DIRECTORY_SEPARATOR,
        $project_root . 'uploads' . DIRECTORY_SEPARATOR . 'absence_docs' . DIRECTORY_SEPARATOR
    ];

    foreach ($folders_to_clean as $folder) {
        if (is_dir($folder)) {
            $files = glob($folder . '*');
            if ($files) {
                foreach ($files as $f) {
                    if (is_file($f) && basename($f) !== '.gitkeep' && basename($f) !== '.htaccess') {
                        if (@unlink($f)) {
                            $unlinked_files++;
                        }
                    }
                }
            }
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "Annual batch reset complete. Successfully deleted $students_deleted student account(s) and freed storage by unlinking $unlinked_files file(s).",
        "students_deleted" => $students_deleted,
        "files_unlinked" => $unlinked_files
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
