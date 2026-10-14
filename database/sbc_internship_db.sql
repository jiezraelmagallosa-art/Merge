-- SBC Internship Attendance & Verification System Database Schema
-- Optimized for Universal Shared Hosting & InfinityFree MySQL
-- Database: if0_42771510_sbc_internship_db

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

-- 1. Academic Program / Course Table
CREATE TABLE IF NOT EXISTS course (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL,
    required_hours INT DEFAULT 480
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Partner Training Facilities Table
CREATE TABLE IF NOT EXISTS training_site (
    site_id INT PRIMARY KEY AUTO_INCREMENT,
    site_code VARCHAR(20) NOT NULL UNIQUE,
    site_name VARCHAR(100) NOT NULL,
    location VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Dean & Admin Users Table
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Dean', 'Admin') DEFAULT 'Dean',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Student Roster Table
CREATE TABLE IF NOT EXISTS student (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    student_number VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    id_no VARCHAR(50) NOT NULL,
    rfid_uid VARCHAR(100) NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    course_id INT NOT NULL,
    dean_id INT NULL,
    INDEX idx_student_course (course_id),
    INDEX idx_student_dean (dean_id),
    INDEX idx_student_rfid (rfid_uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. OJT Assignment Table
CREATE TABLE IF NOT EXISTS ojt (
    ojt_id INT PRIMARY KEY AUTO_INCREMENT,
    ojt_no VARCHAR(50) UNIQUE NOT NULL,
    site_id INT NOT NULL,
    student_id INT NOT NULL,
    required_hours INT DEFAULT 480,
    INDEX idx_ojt_site (site_id),
    INDEX idx_ojt_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Attendance Clock-In / Clock-Out Records
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    time_in_morning TIME NULL,
    time_out_morning TIME NULL,
    time_in_afternoon TIME NULL,
    time_out_afternoon TIME NULL,
    status VARCHAR(50) DEFAULT 'Pending',
    morning_status VARCHAR(50) DEFAULT 'Pending',
    morning_remarks TEXT NULL,
    afternoon_status VARCHAR(50) DEFAULT 'Pending',
    afternoon_remarks TEXT NULL,
    remarks TEXT NULL,
    ojt_id INT NOT NULL,
    site_id INT NULL,
    INDEX idx_att_ojt (ojt_id),
    INDEX idx_att_site (site_id),
    INDEX idx_att_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Student Training Site Transfer History Table
CREATE TABLE IF NOT EXISTS student_site_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    site_id INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    pulled_out_at TIMESTAMP NULL,
    is_current TINYINT(1) DEFAULT 1,
    remarks VARCHAR(255) NULL,
    INDEX idx_ssh_student (student_id),
    INDEX idx_ssh_site (site_id),
    INDEX idx_ssh_current (is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Facial Verification Photo Logs Table
CREATE TABLE IF NOT EXISTS photo (
    photo_id INT PRIMARY KEY AUTO_INCREMENT,
    attendance_id INT NOT NULL,
    shift_type ENUM('Morning_In', 'Morning_Out', 'Afternoon_In', 'Afternoon_Out') NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    captured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_photo_att (attendance_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Absence Requests Table
CREATE TABLE IF NOT EXISTS absence_requests (
    absence_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    ojt_id INT NOT NULL,
    date_absent DATE NOT NULL,
    reason TEXT NOT NULL,
    supporting_document VARCHAR(255) NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    reviewed_by INT NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_abs_student (student_id),
    INDEX idx_abs_ojt (ojt_id),
    INDEX idx_abs_reviewed (reviewed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Student Daily Narrative Journal Table
CREATE TABLE IF NOT EXISTS daily_journal (
    journal_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    ojt_id INT NOT NULL,
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
    INDEX idx_dj_student (student_id),
    INDEX idx_dj_ojt (ojt_id),
    INDEX idx_dj_attendance (attendance_id),
    INDEX idx_dj_date (entry_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Academic Courses
INSERT INTO course (course_code, course_name, required_hours) VALUES
('BSCS', 'Bachelor of Science in Computer Science', 480),
('BSIS', 'Bachelor of Science in Information Systems', 480),
('BLIS', 'Bachelor of Library and Information Science', 480)
ON DUPLICATE KEY UPDATE course_name=VALUES(course_name), required_hours=VALUES(required_hours);

-- Seed Initial Default Training Facility
INSERT IGNORE INTO training_site (site_code, site_name, location)
VALUES ('SBC-IT', 'SBC IT Department', 'M\'lang, Cotabato');

-- Seed Default Dean Administrator Account (Password: password)
INSERT IGNORE INTO users (user_id, full_name, email, password, role)
VALUES (1, 'Dean Admin', 'dean@sbc.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.X2g14m.6C', 'Dean');


-- ========================================================
-- CIT DEPARTMENT SYSTEM TABLES (Unified Shared Database)
-- ========================================================

-- 11. CIT Administrators
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. CIT Department Students Roster
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NULL UNIQUE,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    course VARCHAR(50) NOT NULL,
    year_level VARCHAR(20) NOT NULL,
    rfid_uid VARCHAR(100) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_students_name (lastname, firstname),
    INDEX idx_students_status (status),
    INDEX idx_students_rfid (rfid_uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. Academic School Terms
CREATE TABLE IF NOT EXISTS school_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year VARCHAR(20) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_active BOOLEAN NOT NULL DEFAULT FALSE,
    UNIQUE KEY uq_school_term (academic_year, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. CIT Department Events
CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    event_name VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    login_time TIME NULL,
    logout_time TIME NULL,
    login_cutoff_time TIME NULL,
    logout_cutoff_time TIME NULL,
    grace_period INT NULL DEFAULT 0,
    absent_fine DECIMAL(10, 2) NOT NULL DEFAULT 0,
    late_fine DECIMAL(10, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_events_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. CIT Event Attendance Records
CREATE TABLE IF NOT EXISTS cit_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    student_id INT NOT NULL,
    status VARCHAR(32) NOT NULL,
    timestamp DATETIME NOT NULL,
    INDEX idx_cit_att_event (event_id),
    INDEX idx_cit_att_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. Paper / Instant Attendance Overrides
CREATE TABLE IF NOT EXISTS paper_attendance (
    event_id INT NOT NULL,
    student_id INT NOT NULL,
    status VARCHAR(16) NOT NULL,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, student_id),
    INDEX idx_paper_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. Department Fines
CREATE TABLE IF NOT EXISTS fines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    event_id INT NOT NULL,
    fine_type VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    assessed_amount DECIMAL(10, 2) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fine (student_id, event_id, fine_type),
    INDEX idx_fines_student (student_id),
    INDEX idx_fines_event (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 18. Financial Transactions
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    transaction_type VARCHAR(30) NOT NULL,
    payment_type VARCHAR(30) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    created_by VARCHAR(80) NOT NULL,
    transaction_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_transactions_student (student_id),
    INDEX idx_transactions_date (transaction_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 19. Exemption Lists & Mappings
CREATE TABLE IF NOT EXISTS exemption_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    year_level VARCHAR(20) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exemption_list_students (
    list_id INT NOT NULL,
    student_id INT NOT NULL,
    PRIMARY KEY (list_id, student_id),
    INDEX idx_ex_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS event_exemptions (
    event_id INT NOT NULL,
    student_id INT NOT NULL,
    PRIMARY KEY (event_id, student_id),
    INDEX idx_eve_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 20. Student Funds
CREATE TABLE IF NOT EXISTS student_funds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    fund_id INT NULL,
    amount_due DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sf_student (student_id),
    INDEX idx_sf_fund (fund_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 21. System Settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year VARCHAR(20) NULL,
    semester VARCHAR(20) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 22. Transaction Balance Snapshots
CREATE TABLE IF NOT EXISTS transaction_balance_snapshots (
    transaction_id INT PRIMARY KEY,
    transaction_type VARCHAR(16) NOT NULL,
    snapshot JSON NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 23. Fine Payment Allocations
CREATE TABLE IF NOT EXISTS fine_payment_allocations (
    allocation_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    source_fine_id INT NOT NULL,
    student_id VARCHAR(32) NOT NULL,
    event_id INT NOT NULL,
    fine_type VARCHAR(50) NOT NULL,
    amount_paid DECIMAL(10, 2) NOT NULL,
    UNIQUE KEY uq_fine_payment_source (transaction_id, source_fine_id),
    INDEX idx_fine_payment_identity (student_id, event_id, fine_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Default Admins
INSERT IGNORE INTO admins (admin_id, username, password) VALUES
(1, 'admin', 'scrypt:32768:8:1$rxSAesHbHvuQCqPb$7cc48849edb275e26d9cf1392fce31ee0fa23ec60204a48af4dca16df7063acefa818f013dd291c5be103c3bf2bd473907b34fa42e79a09954257454535ca082'),
(2, 'dean', 'scrypt:32768:8:1$1793740263$password_hash'),
(3, 'dean@sbc.edu.ph', 'scrypt:32768:8:1$1793740263$password_hash');

SET FOREIGN_KEY_CHECKS = 1;