-- ==============================================================================
-- CIT DEPARTMENT ATTENDANCE SYSTEM - UNIFIED DATABASE SCHEMA
-- Designed for: Student Event Attendance & Mobile Live Capture Verification
-- (No OJT/Internship, Partner Sites, or Required Hours)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS cit_attendance_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE cit_attendance_db;

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

-- ==============================================================================
-- PART 1: CIT DEPARTMENT SYSTEM CORE
-- ==============================================================================

-- 1. CIT Administrators
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. CIT Students Roster
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
) ENGINE=InnoDB;

-- 3. Academic School Terms
CREATE TABLE IF NOT EXISTS school_terms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year VARCHAR(20) NOT NULL,
    semester VARCHAR(20) NOT NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    is_active BOOLEAN NOT NULL DEFAULT FALSE,
    UNIQUE KEY uq_school_term (academic_year, semester)
) ENGINE=InnoDB;

-- 4. CIT System Settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year VARCHAR(20) NULL,
    semester VARCHAR(20) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 5. Department Events (Assemblies, Activities, Meetings)
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
) ENGINE=InnoDB;

-- 6. Student Funds
CREATE TABLE IF NOT EXISTS student_funds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    fund_id INT NULL,
    amount_due DECIMAL(10, 2) NOT NULL DEFAULT 0,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_student_funds_student (student_id)
) ENGINE=InnoDB;

-- 7. Department Fines
CREATE TABLE IF NOT EXISTS fines (
    fine_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) NOT NULL,
    event_id INT NULL,
    fine_type VARCHAR(50) NULL,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 0,
    assessed_amount DECIMAL(10, 2) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fine (student_id, event_id, fine_type),
    INDEX idx_fines_student (student_id),
    INDEX idx_fines_event (event_id)
) ENGINE=InnoDB;

-- 8. CIT Event Attendance Records
CREATE TABLE IF NOT EXISTS cit_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    student_id INT NOT NULL,
    status VARCHAR(32) NOT NULL,
    timestamp DATETIME NOT NULL,
    INDEX idx_cit_attendance_event (event_id),
    INDEX idx_cit_attendance_student (student_id)
) ENGINE=InnoDB;

-- 9. Financial Transactions
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
) ENGINE=InnoDB;

-- 10. Transaction Balance Snapshots
CREATE TABLE IF NOT EXISTS transaction_balance_snapshots (
    transaction_id INT PRIMARY KEY,
    transaction_type VARCHAR(16) NOT NULL,
    snapshot JSON NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. Fine Payment Allocations
CREATE TABLE IF NOT EXISTS fine_payment_allocations (
    allocation_id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    source_fine_id INT NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    event_id INT NOT NULL,
    fine_type VARCHAR(50) NOT NULL,
    amount_paid DECIMAL(10, 2) NOT NULL,
    UNIQUE KEY uq_fine_payment_source (transaction_id, source_fine_id),
    INDEX idx_fine_payment_identity (student_id, event_id, fine_type)
) ENGINE=InnoDB;

-- 12. Exemption Lists
CREATE TABLE IF NOT EXISTS exemption_lists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    year_level VARCHAR(32) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 13. Exemption List Students
CREATE TABLE IF NOT EXISTS exemption_list_students (
    list_id INT NOT NULL,
    student_id INT NOT NULL,
    PRIMARY KEY (list_id, student_id),
    INDEX idx_exemption_list_students_student (student_id)
) ENGINE=InnoDB;

-- 14. Event Exemptions
CREATE TABLE IF NOT EXISTS event_exemptions (
    event_id INT NOT NULL,
    student_id INT NOT NULL,
    PRIMARY KEY (event_id, student_id),
    INDEX idx_event_exemptions_student (student_id)
) ENGINE=InnoDB;

-- 15. Paper / Direct Attendance Overrides
CREATE TABLE IF NOT EXISTS paper_attendance (
    event_id INT NOT NULL,
    student_id INT NOT NULL,
    status VARCHAR(16) NOT NULL,
    recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (event_id, student_id),
    INDEX idx_paper_attendance_student (student_id)
) ENGINE=InnoDB;


-- ==============================================================================
-- PART 2: MOBILE APP AUTHENTICATION & LIVE CAPTURE BRIDGE
-- ==============================================================================

-- 16. Academic Programs / Courses
CREATE TABLE IF NOT EXISTS course (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- 17. Dean / Administrative Users
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('Dean', 'Admin') DEFAULT 'Dean',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 18. Mobile App Student Authentication & Profiles
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
    year_level VARCHAR(20) DEFAULT '4',
    INDEX idx_student_course (course_id),
    INDEX idx_student_dean (dean_id),
    INDEX idx_student_rfid (rfid_uid)
) ENGINE=InnoDB;

-- 19. Student Daily Attendance Logs (Clock In/Out for Event)
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
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
    ojt_id INT NULL DEFAULT NULL,
    site_id INT NULL DEFAULT NULL,
    INDEX idx_att_student (student_id),
    INDEX idx_att_date (date)
) ENGINE=InnoDB;

-- 20. Mobile App Live Capture Selfie Photos
CREATE TABLE IF NOT EXISTS photo (
    photo_id INT PRIMARY KEY AUTO_INCREMENT,
    attendance_id INT NOT NULL,
    shift_type ENUM('Morning_In', 'Morning_Out', 'Afternoon_In', 'Afternoon_Out') NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    captured_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_photo_att (attendance_id)
) ENGINE=InnoDB;

-- 21. Absence Requests
CREATE TABLE IF NOT EXISTS absence_requests (
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
    INDEX idx_abs_student (student_id),
    INDEX idx_abs_reviewed (reviewed_by)
) ENGINE=InnoDB;

-- 22. Daily Journal / Event Notes
CREATE TABLE IF NOT EXISTS daily_journal (
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
    INDEX idx_dj_student (student_id),
    INDEX idx_dj_attendance (attendance_id),
    INDEX idx_dj_date (entry_date)
) ENGINE=InnoDB;

-- Seed Default Admin Accounts
INSERT IGNORE INTO admins (admin_id, username, password) VALUES
(1, 'admin', 'scrypt:32768:8:1$rxSAesHbHvuQCqPb$7cc48849edb275e26d9cf1392fce31ee0fa23ec60204a48af4dca16df7063acefa818f013dd291c5be103c3bf2bd473907b34fa42e79a09954257454535ca082');

-- Seed Default Dean Account (Password: password)
INSERT IGNORE INTO users (user_id, full_name, email, password, role)
VALUES (1, 'Dean Admin', 'dean@cit.edu.ph', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.X2g14m.6C', 'Dean');

-- Seed Default Courses
INSERT INTO course (course_code, course_name) VALUES
('BSCS', 'Bachelor of Science in Computer Science'),
('BSIS', 'Bachelor of Science in Information Systems'),
('BLIS', 'Bachelor of Library and Information Science')
ON DUPLICATE KEY UPDATE course_name=VALUES(course_name);

SET FOREIGN_KEY_CHECKS = 1;
