<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: get_courses.php
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

// Connect to MySQL database
require_once 'db_connect.php';

$query = "SELECT course_id, course_code, course_name FROM course ORDER BY course_code ASC";
$result = $conn->query($query);

$courses = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = [
            "course_id" => intval($row['course_id']),
            "course_code" => $row['course_code'],
            "course_name" => $row['course_name'],
            "display_name" => $row['course_code'] . " - " . $row['course_name']
        ];
    }
}

echo json_encode([
    "status" => "success",
    "data" => $courses
]);

$conn->close();
?>