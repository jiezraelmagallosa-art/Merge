<?php
/**
 * SBC Internship Attendance & Verification System
 * Database Migration & Setup Runner
 *
 * Can be run via:
 * 1. Web Browser: http://localhost/SBC_Internship_Attendance_System/backend/migrate.php
 * 2. Command Line (CLI): php backend/migrate.php
 */

// Load centralized config (edit backend/config.php to change settings)
require_once __DIR__ . '/config.php';

date_default_timezone_set(APP_TIMEZONE);

$isCli = (php_sapi_name() === 'cli');
$logs = [];
$status = "success";

function logStep($message, $type = "info")
{
    global $logs, $isCli;
    $logs[] = [
        "timestamp" => date('H:i:s'),
        "type" => $type,
        "message" => $message
    ];
    if ($isCli) {
        $prefix = ($type === 'error') ? '[ERROR]' : (($type === 'success') ? '[OK]' : '[INFO]');
        echo "$prefix $message\n";
    }
}

// 1. Connection Config — from backend/config.php
$host = DB_HOST;
$username = DB_USER;
$password = DB_PASS;
$database = DB_NAME;
$port = DB_PORT;

logStep("Connecting to MySQL at $host:$port (User: $username, Database: $database)...");

// Try connecting directly with database name (standard for InfinityFree / cPanel / production)
$conn = @new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    // Fallback: try connecting without DB name to attempt creation (for local dev setup)
    $conn = @new mysqli($host, $username, $password, "", $port);
    if ($conn->connect_error) {
        $status = "error";
        logStep("MySQL connection failed: " . $conn->connect_error, "error");
        outputJson($status, $logs, $database, []);
        exit();
    }

    // Attempt database creation if permission allows
    @$conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    if (!$conn->select_db($database)) {
        $status = "error";
        logStep("Failed to select database `$database`: " . $conn->error, "error");
        outputJson($status, $logs, $database, []);
        exit();
    }
    logStep("Database `$database` initialized successfully.", "success");
} else {
    logStep("Connected to MySQL server and selected database `$database` successfully.", "success");
}

$conn->query("SET time_zone = '+08:00';");
$conn->set_charset("utf8mb4");

// 3. Define Table Schema
$tables = [
    "course" => "CREATE TABLE IF NOT EXISTS course (
        course_id INT PRIMARY KEY AUTO_INCREMENT,
        course_code VARCHAR(20) NOT NULL UNIQUE,
        course_name VARCHAR(100) NOT NULL,
        required_hours INT DEFAULT 480
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "users" => "CREATE TABLE IF NOT EXISTS users (
        user_id INT PRIMARY KEY AUTO_INCREMENT,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('Dean', 'Admin') DEFAULT 'Dean',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "student" => "CREATE TABLE IF NOT EXISTS student (
        student_id INT PRIMARY KEY AUTO_INCREMENT,
        student_number VARCHAR(50) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        id_no VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        course_id INT NOT NULL,
        dean_id INT NULL,
        FOREIGN KEY (course_id) REFERENCES course(course_id) ON DELETE CASCADE,
        FOREIGN KEY (dean_id) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "attendance" => "CREATE TABLE IF NOT EXISTS attendance (
        attendance_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        date DATE NOT NULL,
        login_time TIME NULL,
        logout_time TIME NULL,
        status VARCHAR(50) DEFAULT 'Pending',
        remarks TEXT NULL,
        ojt_id INT NULL DEFAULT NULL,
        site_id INT NULL DEFAULT NULL,
        FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "photo" => "CREATE TABLE IF NOT EXISTS photo (
        photo_id INT PRIMARY KEY AUTO_INCREMENT,
        attendance_id INT NOT NULL,
        shift_type VARCHAR(50) NOT NULL DEFAULT 'Login',
        image_path VARCHAR(255) NOT NULL,
        captured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (attendance_id) REFERENCES attendance(attendance_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "absence_requests" => "CREATE TABLE IF NOT EXISTS absence_requests (
        absence_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        ojt_id INT NULL DEFAULT NULL,
        date_absent DATE NOT NULL,
        reason TEXT NOT NULL,
        supporting_document VARCHAR(255) NULL,
        status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
        reviewed_by INT NULL,
        remarks TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
        FOREIGN KEY (reviewed_by) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",

    "daily_journal" => "CREATE TABLE IF NOT EXISTS daily_journal (
        journal_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        ojt_id INT NULL DEFAULT NULL,
        attendance_id INT NULL,
        entry_date DATE NOT NULL,
        tasks_completed TEXT NOT NULL,
        learnings_reflection TEXT NULL,
        challenges_encountered TEXT NULL,
        dean_feedback TEXT NULL,
        dean_status ENUM('Pending', 'Reviewed', 'Commended') DEFAULT 'Pending',
        reviewed_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES student(student_id) ON DELETE CASCADE,
        FOREIGN KEY (attendance_id) REFERENCES attendance(attendance_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
];

// Execute Table Creations
foreach ($tables as $tableName => $query) {
    if ($conn->query($query)) {
        logStep("Table `$tableName` initialized / verified.", "success");
    } else {
        logStep("Error creating table `$tableName`: " . $conn->error, "error");
    }
}

// 4. Incremental Alter Migrations (Self-Healing Schema)
// A. student.dean_id
$colDean = $conn->query("SHOW COLUMNS FROM student LIKE 'dean_id'");
if ($colDean && $colDean->num_rows == 0) {
    if ($conn->query("ALTER TABLE student ADD COLUMN dean_id INT NULL AFTER course_id")) {
        @$conn->query("ALTER TABLE student ADD CONSTRAINT fk_student_dean FOREIGN KEY (dean_id) REFERENCES users(user_id) ON DELETE SET NULL");
        logStep("Migrated: Added `dean_id` to `student`.", "success");
    } else {
        logStep("Migration error adding `dean_id`: " . $conn->error, "error");
    }
}

// B. course.required_hours
$colCourseHours = $conn->query("SHOW COLUMNS FROM course LIKE 'required_hours'");
if ($colCourseHours && $colCourseHours->num_rows == 0) {
    if ($conn->query("ALTER TABLE course ADD COLUMN required_hours INT DEFAULT 480 AFTER course_name")) {
        @$conn->query("UPDATE course SET required_hours = 480 WHERE required_hours IS NULL OR required_hours = 0");
        logStep("Migrated: Added `required_hours` to `course`.", "success");
    } else {
        logStep("Migration error adding `required_hours` to `course`: " . $conn->error, "error");
    }
}

// 5. Seed Initial Data — credentials from backend/config.php
$def_hours = DEFAULT_REQUIRED_HOURS;

// A. Courses
$conn->query("INSERT INTO course (course_code, course_name, required_hours) VALUES
    ('BSCS', 'Bachelor of Science in Computer Science', $def_hours),
    ('BSIS', 'Bachelor of Science in Information Systems', $def_hours),
    ('BLIS', 'Bachelor of Library and Information Science', $def_hours)
    ON DUPLICATE KEY UPDATE course_name=VALUES(course_name), required_hours=VALUES(required_hours);");
logStep("Courses seeded (BSCS, BSIS, BLIS).", "success");

// B. Dean Admin
$seed_dean_email = $conn->real_escape_string(SEED_DEAN_EMAIL);
$seed_dean_pass = $conn->real_escape_string(SEED_DEAN_PASSWORD);
$conn->query("INSERT IGNORE INTO users (user_id, full_name, email, password, role)
    VALUES (1, 'Dean Admin', '$seed_dean_email', '$seed_dean_pass', 'Dean');");
logStep("Dean account verified (" . SEED_DEAN_EMAIL . ") — change password after first login!", "success");

// D. Sample Student (Disabled - accounts created via registration or admin)


// 6. Table Counts
$tableCounts = [];
foreach (array_keys($tables) as $tbl) {
    $res = $conn->query("SELECT COUNT(*) as cnt FROM `$tbl`");
    $tableCounts[$tbl] = $res ? intval($res->fetch_assoc()['cnt'] ?? 0) : 'N/A';
}

logStep("Migration completed successfully!", "success");

// Output JSON
outputJson($status, $logs, $database, $tableCounts);

function outputJson($status, $logs, $database, $tableCounts)
{
    global $isCli;
    if (!$isCli) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        "status" => $status,
        "database" => $database,
        "table_counts" => $tableCounts,
        "logs" => $logs
    ], JSON_PRETTY_PRINT);
}
?>