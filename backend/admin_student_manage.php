<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: admin_student_manage.php
 * Handles student program reassignments and required hours adjustments.
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

    $action = isset($data['action']) ? trim($data['action']) : '';
    $student_id = isset($data['student_id']) ? intval($data['student_id']) : 0;

    if ($student_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Valid Student ID is required."]);
        exit();
    }

    // ACTION 1: UPDATE REQUIRED INTERNSHIP HOURS (DEPRECATED IN CIT EVENT SYSTEM)
    if ($action === 'update_required_hours') {
        echo json_encode([
            "status" => "success",
            "message" => "CIT Attendance System tracks event presence; hour computation is disabled."
        ]);
        exit();
    }

    // ACTION 2: UPDATE / REASSIGN STUDENT COURSE
    if ($action === 'update_course') {
        $course_id = isset($data['course_id']) ? intval($data['course_id']) : 0;

        if ($course_id <= 0) {
            echo json_encode(["status" => "error", "message" => "Valid Academic Course ID is required."]);
            exit();
        }

        // Get course details
        $c_stmt = $conn->prepare("SELECT course_id, course_code, course_name FROM course WHERE course_id = ? LIMIT 1");
        $c_stmt->bind_param("i", $course_id);
        $c_stmt->execute();
        $c_res = $c_stmt->get_result();

        if (!$c_res || $c_res->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Selected Academic Course not found."]);
            $c_stmt->close();
            exit();
        }

        $course_info = $c_res->fetch_assoc();
        $c_stmt->close();

        // Update student course
        $s_stmt = $conn->prepare("UPDATE student SET course_id = ? WHERE student_id = ?");
        $s_stmt->bind_param("ii", $course_id, $student_id);
        $s_stmt->execute();
        $s_stmt->close();

        echo json_encode([
            "status" => "success",
            "message" => "Student academic program updated to {$course_info['course_code']} ({$course_info['course_name']}).",
            "course_id" => $course_id,
            "course_code" => $course_info['course_code'],
            "course_name" => $course_info['course_name']
        ]);
        exit();
    }

    // ACTION: UPDATE / MAP STUDENT RFID UID
    if ($action === 'update_rfid' || $action === 'map_rfid') {
        $rfid_uid = isset($data['rfid_uid']) ? trim($data['rfid_uid']) : '';

        if (!empty($rfid_uid)) {
            $stmt_check = $conn->prepare("SELECT student_id, full_name, student_number FROM student WHERE rfid_uid = ? AND student_id != ? LIMIT 1");
            $stmt_check->bind_param("si", $rfid_uid, $student_id);
            $stmt_check->execute();
            $dup_res = $stmt_check->get_result();
            if ($dup_res && $dup_row = $dup_res->fetch_assoc()) {
                $stmt_check->close();
                echo json_encode([
                    "status" => "error",
                    "message" => "This RFID UID (" . htmlspecialchars($rfid_uid) . ") is already mapped to " . $dup_row['full_name'] . " (" . $dup_row['student_number'] . ")."
                ]);
                exit();
            }
            $stmt_check->close();
        }

        $rfid_val = !empty($rfid_uid) ? $rfid_uid : null;
        $s_stmt = $conn->prepare("UPDATE student SET rfid_uid = ? WHERE student_id = ?");
        $s_stmt->bind_param("si", $rfid_val, $student_id);
        $s_stmt->execute();
        $s_stmt->close();

        echo json_encode([
            "status" => "success",
            "message" => !empty($rfid_uid) ? "RFID card UID mapped successfully!" : "RFID card association cleared.",
            "student_id" => $student_id,
            "rfid_uid" => $rfid_uid
        ]);
        exit();
    }

    // ACTION 3: DELETE STUDENT ACCOUNT AND ALL LINKED RECORDS
    if ($action === 'delete_student' || $action === 'delete') {
        // 1. Verify student exists and retrieve full name
        $stmt_check = $conn->prepare("SELECT student_id, full_name FROM student WHERE student_id = ? LIMIT 1");
        $stmt_check->bind_param("i", $student_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if (!$res_check || $res_check->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Student record not found."]);
            $stmt_check->close();
            exit();
        }

        $student_info = $res_check->fetch_assoc();
        $student_name = $student_info['full_name'] ?? 'Student';
        $stmt_check->close();

        // 2. Locate and remove physical image captures and absence files from disk
        $project_root = dirname(__DIR__) . DIRECTORY_SEPARATOR;

        // Unlink facial verification photos
        $photo_stmt = $conn->prepare("
            SELECT p.image_path 
            FROM photo p
            JOIN attendance a ON p.attendance_id = a.attendance_id
            WHERE a.student_id = ? OR a.ojt_id = ?
        ");
        if ($photo_stmt) {
            $photo_stmt->bind_param("ii", $student_id, $student_id);
            $photo_stmt->execute();
            $p_res = $photo_stmt->get_result();
            while ($p_row = $p_res->fetch_assoc()) {
                if (!empty($p_row['image_path'])) {
                    $rel_path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $p_row['image_path']);
                    $full_path = $project_root . ltrim($rel_path, DIRECTORY_SEPARATOR);
                    if (file_exists($full_path) && is_file($full_path)) {
                        @unlink($full_path);
                    }
                }
            }
            $photo_stmt->close();
        }

        // Unlink absence supporting documents
        $doc_stmt = $conn->prepare("SELECT supporting_document FROM absence_requests WHERE student_id = ? AND supporting_document IS NOT NULL");
        if ($doc_stmt) {
            $doc_stmt->bind_param("i", $student_id);
            $doc_stmt->execute();
            $d_res = $doc_stmt->get_result();
            while ($d_row = $d_res->fetch_assoc()) {
                if (!empty($d_row['supporting_document'])) {
                    $rel_doc = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $d_row['supporting_document']);
                    $full_doc = $project_root . ltrim($rel_doc, DIRECTORY_SEPARATOR);
                    if (file_exists($full_doc) && is_file($full_doc)) {
                        @unlink($full_doc);
                    }
                }
            }
            $doc_stmt->close();
        }

        // 3. Clean up database records across all child tables
        // Photos
        $conn->query("DELETE p FROM photo p 
                      JOIN attendance a ON p.attendance_id = a.attendance_id 
                      WHERE a.student_id = " . intval($student_id) . " OR a.ojt_id = " . intval($student_id));

        // Attendance
        $conn->query("DELETE FROM attendance WHERE student_id = " . intval($student_id) . " OR ojt_id = " . intval($student_id));

        // Daily Journals
        $conn->query("DELETE FROM daily_journal WHERE student_id = " . intval($student_id));

        // Absence Requests
        $conn->query("DELETE FROM absence_requests WHERE student_id = " . intval($student_id));

        // Finally, delete the student account
        $del_stmt = $conn->prepare("DELETE FROM student WHERE student_id = ?");
        $del_stmt->bind_param("i", $student_id);

        if ($del_stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Student account for {$student_name} has been permanently deleted from the database."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Database operation failed: " . $del_stmt->error
            ]);
        }
        $del_stmt->close();
        exit();
    }

    echo json_encode(["status" => "error", "message" => "Invalid or unspecified action."]);
    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
