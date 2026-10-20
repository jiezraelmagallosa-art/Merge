<?php
/**
 * SBC Internship Attendance System - Database Connection
 * All backend files require this. Edit config.php to change settings.
 */

// Load centralized configuration
require_once __DIR__ . '/config.php';

date_default_timezone_set(APP_TIMEZONE);

$host     = DB_HOST;
$username = DB_USER;
$password = DB_PASS;
$database = DB_NAME;
$port     = DB_PORT;

if (!headers_sent()) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, Origin, Accept, User-Agent, Cookie");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
}

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = @new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status"  => "error",
        "message" => "Database connection failed. Please check server configuration."
    ]);
    exit();
}

$conn->query("SET time_zone = '+08:00';");
$conn->set_charset("utf8mb4");

// Auto-migrate schema updates safely if tables exist
try {
    $col_check = @$conn->query("SHOW COLUMNS FROM student LIKE 'dean_id'");
    if ($col_check && $col_check->num_rows == 0) {
        @$conn->query("ALTER TABLE student ADD COLUMN dean_id INT NULL AFTER course_id");
        @$conn->query("ALTER TABLE student ADD CONSTRAINT fk_student_dean FOREIGN KEY (dean_id) REFERENCES users(user_id) ON DELETE SET NULL");
        $dean_res = @$conn->query("SELECT user_id FROM users WHERE role IN ('Dean', 'Admin') ORDER BY user_id ASC LIMIT 1");
        if ($dean_res && $dean_res->num_rows > 0) {
            $def_dean_id = intval($dean_res->fetch_assoc()['user_id']);
            @$conn->query("UPDATE student SET dean_id = $def_dean_id WHERE dean_id IS NULL");
        }
    }

    $col_rfid = @$conn->query("SHOW COLUMNS FROM student LIKE 'rfid_uid'");
    if ($col_rfid && $col_rfid->num_rows == 0) {
        @$conn->query("ALTER TABLE student ADD COLUMN rfid_uid VARCHAR(100) NULL AFTER id_no");
        @$conn->query("ALTER TABLE student ADD INDEX idx_student_rfid (rfid_uid)");
    }

    $course_check = @$conn->query("SELECT COUNT(*) as cnt FROM course");
    if ($course_check) {
        $c_cnt = intval($course_check->fetch_assoc()['cnt'] ?? 0);
        if ($c_cnt < 3) {
            @$conn->query("INSERT IGNORE INTO course (course_code, course_name) VALUES
                ('BSCS', 'Bachelor of Science in Computer Science'),
                ('BSIS', 'Bachelor of Science in Information Systems'),
                ('BLIS', 'Bachelor of Library and Information Science')");
        }
    }

    // Add student_id column to attendance table if not present
    $col_att_student = @$conn->query("SHOW COLUMNS FROM attendance LIKE 'student_id'");
    if ($col_att_student && $col_att_student->num_rows == 0) {
        @$conn->query("ALTER TABLE attendance ADD COLUMN student_id INT NOT NULL DEFAULT 1 AFTER attendance_id");
    }

    // Add shift-level status and remarks to attendance table if not present
    $col_m_status = @$conn->query("SHOW COLUMNS FROM attendance LIKE 'morning_status'");
    if ($col_m_status && $col_m_status->num_rows == 0) {
        @$conn->query("ALTER TABLE attendance ADD COLUMN morning_status VARCHAR(50) DEFAULT 'Pending' AFTER status");
        @$conn->query("ALTER TABLE attendance ADD COLUMN morning_remarks TEXT NULL AFTER morning_status");
        @$conn->query("ALTER TABLE attendance ADD COLUMN afternoon_status VARCHAR(50) DEFAULT 'Pending' AFTER morning_remarks");
        @$conn->query("ALTER TABLE attendance ADD COLUMN afternoon_remarks TEXT NULL AFTER afternoon_status");
    }

    // Create daily_journal table if not exists
    @$conn->query("CREATE TABLE IF NOT EXISTS daily_journal (
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
        INDEX (student_id),
        INDEX (entry_date),
        INDEX (ojt_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Ensure CIT Department System tables and mirror students in unified database
    require_once __DIR__ . '/cit_sync_helper.php';
    ensureCitDepartmentTables($conn);
    @$conn->query("INSERT IGNORE INTO students (student_id, firstname, lastname, course, year_level, rfid_uid, status)
        SELECT 
            s.student_number,
            SUBSTRING_INDEX(s.full_name, ' ', 1),
            CASE WHEN INSTR(s.full_name, ' ') > 0 THEN SUBSTRING(s.full_name, INSTR(s.full_name, ' ') + 1) ELSE 'Student' END,
            COALESCE(c.course_code, 'BSCS'),
            '4',
            s.rfid_uid,
            'Active'
        FROM student s
        LEFT JOIN course c ON s.course_id = c.course_id");
} catch (Throwable $e) {
    // Database tables not created yet or query suppressed
}

// Dynamic base URL helper — resolves correct host/domain automatically on any server
if (!function_exists('get_system_base_url')) {
    function get_system_base_url() {
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
            || (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], '"scheme":"https"') !== false);
        $protocol = $is_https ? "https://" : "http://";
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script_dir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $app_dir  = preg_replace('#/backend$#', '', str_replace('\\', '/', $script_dir));
        return rtrim($protocol . $host . $app_dir, '/') . '/';
    }
}

// CORS — reads from config.php (CORS_ORIGIN constant)
$cors_origin = defined('CORS_ORIGIN') ? CORS_ORIGIN : '*';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . $cors_origin);
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>