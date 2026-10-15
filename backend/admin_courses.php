<?php
/**
 * SBC Internship Attendance System - Backend API
 * Endpoint: admin_courses.php
 * Handles Academic Program / Course management and required hours configuration.
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw_input = file_get_contents("php://input");
        $data = json_decode($raw_input, true);
        if (!$data) {
            $data = $_POST;
        }

        $action = isset($data['action']) ? trim($data['action']) : 'save';
        $course_id = isset($data['course_id']) ? intval($data['course_id']) : 0;

        // ACTION: DELETE COURSE
        if ($action === 'delete') {
            if ($course_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid academic course ID."]);
                exit();
            }

            // Check if active students enrolled in this course
            $check_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM student WHERE course_id = ?");
            $check_stmt->bind_param("i", $course_id);
            $check_stmt->execute();
            $enrolled_count = intval($check_stmt->get_result()->fetch_assoc()['total'] ?? 0);
            $check_stmt->close();

            if ($enrolled_count > 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Cannot delete this course because it currently has {$enrolled_count} active enrolled student(s). Please reassign them to another course first."
                ]);
                exit();
            }

            $del_stmt = $conn->prepare("DELETE FROM course WHERE course_id = ?");
            $del_stmt->bind_param("i", $course_id);
            if ($del_stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Academic course deleted successfully."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Deletion failed: " . $del_stmt->error]);
            }
            $del_stmt->close();
            exit();
        }

        // ACTION: SAVE / UPDATE COURSE
        $course_code = isset($data['course_code']) ? strtoupper(trim($data['course_code'])) : '';
        $course_name = isset($data['course_name']) ? trim($data['course_name']) : '';
        $required_hours = isset($data['required_hours']) ? intval($data['required_hours']) : 480;

        if (empty($course_code) || empty($course_name)) {
            echo json_encode(["status" => "error", "message" => "Course code and course name are required."]);
            exit();
        }

        if ($required_hours <= 0) {
            $required_hours = 480;
        }

        if ($course_id > 0) {
            // Update course details & required hours
            $stmt = $conn->prepare("UPDATE course SET course_code = ?, course_name = ?, required_hours = ? WHERE course_id = ?");
            $stmt->bind_param("ssii", $course_code, $course_name, $required_hours, $course_id);
            
            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Academic program updated successfully."
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database update failed: " . $stmt->error]);
            }
            $stmt->close();
        } else {
            // Check unique course_code
            $check_c = $conn->prepare("SELECT course_id FROM course WHERE course_code = ? LIMIT 1");
            $check_c->bind_param("s", $course_code);
            $check_c->execute();
            if ($check_c->get_result()->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "A course with code '{$course_code}' already exists."]);
                $check_c->close();
                exit();
            }
            $check_c->close();

            // Insert new course
            $stmt = $conn->prepare("INSERT INTO course (course_code, course_name, required_hours) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $course_code, $course_name, $required_hours);

            if ($stmt->execute()) {
                echo json_encode([
                    "status" => "success",
                    "message" => "New academic program '{$course_code}' added successfully with {$required_hours} required curriculum hours.",
                    "course_id" => $stmt->insert_id
                ]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database insert failed: " . $stmt->error]);
            }
            $stmt->close();
        }

    } else {
        // GET: Fetch all courses with enrolled interns count
        $sql = "SELECT
                    c.course_id,
                    c.course_code,
                    c.course_name,
                    COALESCE(c.required_hours, 480) AS required_hours,
                    COUNT(s.student_id) AS enrolled_students
                FROM course c
                LEFT JOIN student s ON c.course_id = s.course_id
                GROUP BY c.course_id, c.course_code, c.course_name, c.required_hours
                ORDER BY c.course_code ASC";

        $result = $conn->query($sql);
        $courses = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $courses[] = [
                    "course_id" => intval($row['course_id']),
                    "course_code" => $row['course_code'],
                    "course_name" => $row['course_name'],
                    "required_hours" => intval($row['required_hours']),
                    "enrolled_students" => intval($row['enrolled_students'] ?? 0),
                    "display_name" => $row['course_code'] . " - " . $row['course_name'] . " (" . $row['required_hours'] . " hrs)"
                ];
            }
        }

        echo json_encode([
            "status" => "success",
            "data" => $courses
        ]);
    }

    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>
