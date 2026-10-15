<?php
/**
 * SBC Internship Attendance System - Student Login API
 */

ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/db_connect.php';

try {
    $raw_input = file_get_contents("php://input");
    $data = json_decode($raw_input, true);

    if (!$data) {
        $data = $_POST;
    }

    $email = isset($data['email']) ? trim($data['email']) : '';
    $pass = isset($data['password']) ? trim($data['password']) : '';

    if (empty($email) || empty($pass)) {
        echo json_encode(["status" => "error", "message" => "Missing email or password."]);
        exit();
    }

    $stmt = $conn->prepare("SELECT s.*, u.full_name AS dean_name, u.email AS dean_email, s.student_id AS ojt_id, CONCAT('CIT-', s.student_number) AS ojt_no FROM student s LEFT JOIN users u ON s.dean_id = u.user_id WHERE s.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $db_password = $row['password'];

        if (password_verify($pass, $db_password) || $pass === $db_password) {
            echo json_encode([
                "status" => "success",
                "message" => "Login successful",
                "user" => $row
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Incorrect password. Please try again."
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "No registered account found with that email."
        ]);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Authentication error: " . $e->getMessage()
    ]);
}
?>