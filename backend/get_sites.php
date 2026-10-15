<?php
/**
 * SBC Attendance System - Backend API
 * Endpoint: get_sites.php
 * In CIT Event Attendance System, training sites are replaced with standard event venues.
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

$sites = [
    [
        "site_id" => 1,
        "site_code" => "CIT-MAIN",
        "site_name" => "CIT Department / Campus Hall",
        "location" => "Main Campus",
        "display_name" => "CIT Department / Campus Hall (Main Campus)"
    ]
];

echo json_encode([
    "status" => "success",
    "data" => $sites
]);
?>
