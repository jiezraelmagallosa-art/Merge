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

require_once 'db_connect.php';

$raw_input = file_get_contents("php://input");
$data = json_decode($raw_input, true);

if (!$data) {
    $data = $_POST;
}

$full_name = isset($data['full_name']) ? trim($data['full_name']) : '';
$student_number = isset($data['student_number']) ? trim($data['student_number']) : '';
$id_no = isset($data['id_no']) ? trim($data['id_no']) : '';
$email = isset($data['email']) ? trim($data['email']) : '';
$pass = isset($data['password']) ? trim($data['password']) : '';
$course_id = isset($data['course_id']) ? intval($data['course_id']) : 1;
$dean_id = isset($data['dean_id']) ? intval($data['dean_id']) : 0;

// Prioritize user's typed Student ID No.
if (!empty($id_no) && empty($student_number)) {
    $student_number = $id_no;
}
if (!empty($student_number) && empty($id_no)) {
    $id_no = $student_number;
}

// Auto-generate student number (e.g. 2026-0010) only if not provided
if (empty($student_number)) {
    $max_res = $conn->query("SELECT MAX(student_id) as max_id FROM student");
    $next_id = 1;
    if ($max_res && $row = $max_res->fetch_assoc()) {
        $next_id = intval($row['max_id']) + 1;
    }
    $student_number = date("Y") . "-" . str_pad($next_id, 4, "0", STR_PAD_LEFT);
    $id_no = $student_number;
}

if (empty($full_name) || empty($email) || empty($pass)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all required registration fields."]);
    exit();
}

if ($dean_id <= 0) {

    $d_check = $conn->query("SELECT user_id FROM users WHERE role IN ('Dean', 'Admin') ORDER BY user_id ASC LIMIT 1");
    if ($d_check && $d_check->num_rows > 0) {
        $dean_id = intval($d_check->fetch_assoc()['user_id']);
    } else {
        echo json_encode(["status" => "error", "message" => "Please select your specific Dean for account categorization."]);
        exit();
    }
} else {

    $d_verify = $conn->prepare("SELECT user_id FROM users WHERE user_id = ? LIMIT 1");
    $d_verify->bind_param("i", $dean_id);
    $d_verify->execute();
    if ($d_verify->get_result()->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Selected Dean is invalid or not found."]);
        $d_verify->close();
        $conn->close();
        exit();
    }
    $d_verify->close();
}

if (empty($id_no)) {
    $id_no = "ID-" . rand(100, 999);
}


$check_email = $conn->prepare("SELECT student_id FROM student WHERE email = ?");
$check_email->bind_param("s", $email);
$check_email->execute();
if ($check_email->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This email is already registered."]);
    $check_email->close();
    $conn->close();
    exit();
}
$check_email->close();


$check_num = $conn->prepare("SELECT student_id FROM student WHERE student_number = ?");
$check_num->bind_param("s", $student_number);
$check_num->execute();
if ($check_num->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This student number is already registered."]);
    $check_num->close();
    $conn->close();
    exit();
}
$check_num->close();


$course_id = isset($data['course_id']) ? intval($data['course_id']) : 1;
$dean_id = isset($data['dean_id']) ? intval($data['dean_id']) : 0;
$year_level = isset($data['year_level']) ? trim($data['year_level']) : '4';
if (empty($year_level)) {
    $year_level = '4';
}

$site_id = isset($data['site_id']) ? intval($data['site_id']) : (isset($data['site_id_selected']) ? intval($data['site_id_selected']) : 1);
if ($site_id <= 0) {
    $site_id = 1;
}

$clean_year_level = preg_replace('/[^0-9]/', '', $year_level);
if (empty($clean_year_level)) {
    $clean_year_level = !empty($year_level) ? $year_level : '4';
}

$hashed_password = password_hash($pass, PASSWORD_DEFAULT);

try {
    $stmt = $conn->prepare("INSERT INTO student (student_number, full_name, id_no, email, password, course_id, dean_id, year_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        $conn->close();
        exit();
    }
    $stmt->bind_param("sssssiis", $student_number, $full_name, $id_no, $email, $hashed_password, $course_id, $dean_id, $clean_year_level);

    if ($stmt->execute()) {
        $student_id = $conn->insert_id;
        $stmt->close();

    // Student registered successfully - ojt assignment omitted as system is for pure event attendance

    // ========================================================
    // Synchronize Student Registration to CIT Department System
    // ========================================================
    try {
        require_once 'cit_sync_helper.php';
        ensureCitDepartmentTables($conn);

        // 1. Determine Student ID in CIT Department System:
        // Use the exact typed Student ID No. ($id_no or $student_number)
        $cit_student_id_str = !empty($id_no) ? $id_no : $student_number;

        // 2. Parse First Name and Last Name from full_name:
        $name_trimmed = trim($full_name);
        $name_parts = preg_split('/\s+/', $name_trimmed);
        if (count($name_parts) > 1) {
            $firstname = array_shift($name_parts);
            $lastname = implode(' ', $name_parts);
        } else {
            $firstname = $name_trimmed;
            $lastname = 'Student';
        }

        // 3. Determine course code:
        $course_code = 'BSIS';
        $c_stmt = $conn->prepare("SELECT course_code FROM course WHERE course_id = ? LIMIT 1");
        if ($c_stmt) {
            $c_stmt->bind_param("i", $course_id);
            $c_stmt->execute();
            $c_res = $c_stmt->get_result();
            if ($c_row = $c_res->fetch_assoc()) {
                if (!empty($c_row['course_code'])) {
                    $course_code = trim($c_row['course_code']);
                }
            }
            $c_stmt->close();
        }

        // 4. Clean and normalize year_level:
        $clean_year_level = preg_replace('/[^0-9]/', '', $year_level);
        if (empty($clean_year_level)) {
            $clean_year_level = !empty($year_level) ? $year_level : '4';
        }

        // 5. Insert or Update in CIT Department System `students` table:
        $check_cit = $conn->prepare("SELECT id FROM students WHERE student_id = ? OR (LOWER(firstname) = LOWER(?) AND LOWER(lastname) = LOWER(?)) LIMIT 1");
        if ($check_cit) {
            $check_cit->bind_param("sss", $cit_student_id_str, $firstname, $lastname);
            $check_cit->execute();
            $cit_match = $check_cit->get_result();
            if ($cit_row = $cit_match->fetch_assoc()) {
                $cit_db_id = intval($cit_row['id']);
                $upd_cit = $conn->prepare("UPDATE students SET student_id = ?, firstname = ?, lastname = ?, course = ?, year_level = ?, status = 'Active' WHERE id = ?");
                if ($upd_cit) {
                    $upd_cit->bind_param("sssssi", $cit_student_id_str, $firstname, $lastname, $course_code, $clean_year_level, $cit_db_id);
                    $upd_cit->execute();
                    $upd_cit->close();
                }
            } else {
                $ins_cit = $conn->prepare("INSERT INTO students (student_id, firstname, lastname, course, year_level, status) VALUES (?, ?, ?, ?, ?, 'Active')");
                if ($ins_cit) {
                    $ins_cit->bind_param("sssss", $cit_student_id_str, $firstname, $lastname, $course_code, $clean_year_level);
                    $ins_cit->execute();
                    $ins_cit->close();
                }
            }
            $check_cit->close();
        }
    } catch (Exception $sync_ex) {
        error_log("CIT Student Sync Error on Register: " . $sync_ex->getMessage());
    }

    echo json_encode([
        "status" => "success",
        "message" => "Account created successfully! You can now log in.",
        "student_id" => $student_id
    ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Registration failed: " . $stmt->error
        ]);
        $stmt->close();
    }
} catch (Throwable $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Registration failed: " . $e->getMessage()
    ]);
}

$conn->close();
?>
