<?php
/**
 * SBC Attendance System - Backend API
 * Endpoint: admin_sites.php
 * Note: Training site management is removed in the CIT Student Event Attendance System.
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo json_encode([
        "status" => "success",
        "message" => "Training site operations are deprecated. CIT Attendance tracks event presence only."
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "data" => []
    ]);
}
?>