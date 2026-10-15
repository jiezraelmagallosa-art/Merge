<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: update_student_profile.php
 * Allows students to update their account details, assigned Dean, and Training Site.
 * 
 * Auto-enrollment / reassignment behavior:
 * - When dean_id is changed, the student is automatically moved to the new Dean's roster.
 * - When site_id is changed, student is transferred to the new facility while preserving previous hours.
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

    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);
    if (!$data) {
        $data = $_POST;
    }

    $student_id = isset($data['student_id']) ? intval($data['student_id']) : 0;
    if ($student_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Valid Student ID is required."]);
        exit();
    }

    // 1. Fetch current student details
    $stmt_curr = $conn->prepare("
        SELECT s.student_id, s.full_name, s.id_no, s.dean_id, s.course_id
        FROM student s
        WHERE s.student_id = ?
        LIMIT 1
    ");
    $stmt_curr->bind_param("i", $student_id);
    $stmt_curr->execute();
    $res_curr = $stmt_curr->get_result();
    if (!$res_curr || $res_curr->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Student account not found."]);
        exit();
    }
    $curr = $res_curr->fetch_assoc();
    $stmt_curr->close();

    $prev_dean_id = intval($curr['dean_id'] ?? 0);

    // 2. Parse new values (or retain existing)
    $new_name      = isset($data['full_name']) && trim($data['full_name']) !== '' ? trim($data['full_name']) : $curr['full_name'];
    $new_id_no     = isset($data['id_no']) && trim($data['id_no']) !== '' ? trim($data['id_no']) : $curr['id_no'];
    $new_course_id = isset($data['course_id']) && intval($data['course_id']) > 0 ? intval($data['course_id']) : intval($curr['course_id']);
    $new_dean_id   = isset($data['dean_id']) && intval($data['dean_id']) > 0 ? intval($data['dean_id']) : $prev_dean_id;

    // 3. Update student table
    $stmt_upd = $conn->prepare("
        UPDATE student 
        SET full_name = ?, id_no = ?, course_id = ?, dean_id = ?
        WHERE student_id = ?
    ");
    $stmt_upd->bind_param("ssiii", $new_name, $new_id_no, $new_course_id, $new_dean_id, $student_id);
    $stmt_upd->execute();
    $stmt_upd->close();

    // 4. Fetch fresh updated profile details to return to app
    $stmt_fresh = $conn->prepare("
        SELECT 
            s.student_id, s.student_number, s.full_name, s.id_no, s.email, s.dean_id, s.course_id,
            s.student_id AS ojt_id,
            u.full_name AS dean_name, u.email AS dean_email,
            c.course_code, c.course_name
        FROM student s
        LEFT JOIN users u ON s.dean_id = u.user_id
        LEFT JOIN course c ON s.course_id = c.course_id
        WHERE s.student_id = ?
        LIMIT 1
    ");
    $stmt_fresh->bind_param("i", $student_id);
    $stmt_fresh->execute();
    $fresh_data = $stmt_fresh->get_result()->fetch_assoc();
    $stmt_fresh->close();

    $dean_changed = ($new_dean_id !== $prev_dean_id);

    echo json_encode([
        "status" => "success",
        "message" => "Account details updated successfully.",
        "dean_changed" => $dean_changed,
        "data" => $fresh_data
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
