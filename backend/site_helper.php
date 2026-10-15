<?php
/**
 * CIT Attendance System - Campus Event Location Helper
 * (OJT and Partner Training Sites removed per system requirements)
 */

if (!function_exists('getStudentSiteBreakdown')) {
    function getStudentSiteBreakdown($conn, $student_id) {
        return [
            "sites" => [
                [
                    "site_id" => 1,
                    "site_code" => "CIT",
                    "site_name" => "CIT Campus",
                    "location" => "CIT Campus",
                    "is_current" => true,
                    "status_label" => "Event Attendance",
                    "rendered_hours" => 0,
                    "rendered_minutes" => 0,
                    "total_minutes" => 0,
                    "formatted_rendered_time" => "0 hrs 0 mins"
                ]
            ],
            "total_minutes" => 0,
            "total_hours" => 0,
            "remaining_minutes" => 0,
            "formatted_rendered_time" => "0 hrs 0 mins"
        ];
    }
}
