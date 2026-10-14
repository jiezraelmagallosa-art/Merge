<?php
/**
 * ============================================================
 * SBC Internship Attendance & Verification System
 * FILE: backend/config.php
 *
 * ⚠️  BEFORE DEPLOYMENT — Edit the values marked [CHANGE ME]
 * This is the SINGLE file you need to edit for your server.
 * All other backend files automatically read from here.
 * ============================================================
 */

// ------------------------------------------------------------
// DATABASE CONFIGURATION
// [CHANGE ME] Update these for your production MySQL server.
// ------------------------------------------------------------
$is_local_env = (php_sapi_name() === 'cli' && empty(getenv('DB_HOST')))
    || (!empty($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false));

define('DB_HOST', getenv('DB_HOST') ?: ($is_local_env ? 'localhost' : 'sql110.infinityfree.com'));
define('DB_USER', getenv('DB_USER') ?: ($is_local_env ? 'root' : 'if0_42771510'));
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($is_local_env ? '' : '2tG0ijV6kq8'));
define('DB_NAME', getenv('DB_NAME') ?: ($is_local_env ? 'sbc_internship_db' : 'if0_42771510_sbc_internship_db'));
define('DB_PORT', getenv('DB_PORT') ? intval(getenv('DB_PORT')) : 3306);

// ------------------------------------------------------------
// APPLICATION SETTINGS
// ------------------------------------------------------------
define('APP_NAME', 'SBC Internship Attendance & Verification System');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'Asia/Manila');

// ------------------------------------------------------------
// UPLOAD SETTINGS
// Max file size for selfie captures and absence documents (in bytes)
// Default: 8MB. Ensure your server's php.ini also allows this.
// ------------------------------------------------------------
define('MAX_UPLOAD_BYTES', 8 * 1024 * 1024); // 8 MB

// ------------------------------------------------------------
// SHIFT TIME WINDOWS (24-hour format)
// Adjust these if the school's shift schedule changes.
// ------------------------------------------------------------
define('MORNING_IN_START',   '05:00:00');
define('MORNING_IN_END',     '12:30:00');
define('AFTERNOON_IN_START', '12:30:00');
define('AFTERNOON_IN_END',   '19:00:00');

// ------------------------------------------------------------
// CORS SETTINGS
// [CHANGE ME] Set this to your frontend domain in production.
// Use '*' only for local/capstone demo. In production, use
// your actual domain: e.g. 'https://sbc-internship.example.com'
// ------------------------------------------------------------
define('CORS_ORIGIN', '*');

// ------------------------------------------------------------
// DEFAULT OJT REQUIRED HOURS (used when no course data found)
// ------------------------------------------------------------
define('DEFAULT_REQUIRED_HOURS', 480);

// ------------------------------------------------------------
// SEED DATA — These accounts are created on first migration.
// [CHANGE ME] Change passwords immediately after first login!
// Default password for both: 'password' (bcrypt hashed)
// Hash below = bcrypt of 'password'
// ------------------------------------------------------------
define('SEED_DEAN_EMAIL',    'dean@sbc.edu.ph');
define('SEED_DEAN_PASSWORD', '$2y$10$BSN6xQYncAnuuwhOkMpCZO.1ysPcCRNcy2EOZhSYh7ePh62na.5U2'); // bcrypt of 'password'
define('SEED_STUDENT_EMAIL', 'student@sbc.edu.ph');
define('SEED_STUDENT_PASS',  '$2y$10$BSN6xQYncAnuuwhOkMpCZO.1ysPcCRNcy2EOZhSYh7ePh62na.5U2'); // bcrypt of 'password'
