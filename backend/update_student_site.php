<?php
/**
 * SBC Attendance System - Backend API
 * Endpoint: update_student_site.php
 * Note: Training site transfers are deprecated in the CIT Student Event Attendance System.
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

require_once 'site_helper.php';
$breakdown = getStudentSiteBreakdown(null, 0);

echo json_encode([
    "status"         => "success",
    "message"        => "Training site transfers are deprecated. The system tracks event presence only.",
    "current_site"   => [
        "site_id"      => 1,
        "site_code"    => "CIT-MAIN",
        "site_name"    => "CIT Department / Campus Hall",
        "location"     => "Main Campus"
    ],
    "site_breakdown" => $breakdown['sites'],
    "total_rendered_hours"   => 0,
    "total_rendered_minutes" => 0,
    "formatted_rendered_time" => "Event Mode"
]);
?>
