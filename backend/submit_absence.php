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

$student_id = isset($data['student_id']) ? intval($data['student_id']) : 0;
$date_absent = isset($data['date_absent']) ? trim($data['date_absent']) : '';
$reason = isset($data['reason']) ? trim($data['reason']) : '';
$image_base64 = isset($data['image_base64']) ? $data['image_base64'] : '';

if ($student_id <= 0 || empty($date_absent) || empty($reason)) {
    echo json_encode(["status" => "error", "message" => "Please fill in all required fields (date and reason)."]);
    exit();
}


$ojt_id = $student_id;


$db_doc_path = null;
if (!empty($image_base64)) {
    $upload_dir = "../uploads/absence_docs/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (strpos($image_base64, 'base64,') !== false) {
        $image_parts = explode(";base64,", $image_base64);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = isset($image_type_aux[1]) ? $image_type_aux[1] : 'jpg';
        $image_base64_decoded = base64_decode($image_parts[1]);
    } else {
        $image_type = 'jpg';
        $image_base64_decoded = base64_decode($image_base64);
    }

    if ($image_base64_decoded !== false && strlen($image_base64_decoded) > 0) {
        if (function_exists('imagecreatefromstring')) {
            $img = @imagecreatefromstring($image_base64_decoded);
            if ($img !== false) {
                $orientation = 1;
                if (function_exists('exif_read_data')) {
                    $stream = fopen('php://memory', 'r+');
                    fwrite($stream, $image_base64_decoded);
                    rewind($stream);
                    $exif = @exif_read_data($stream);
                    if (!empty($exif['Orientation'])) {
                        $orientation = intval($exif['Orientation']);
                    }
                    fclose($stream);
                }

                $rotated = false;
                if ($orientation == 6) {
                    $rotated = imagerotate($img, 270, 0);
                } elseif ($orientation == 8) {
                    $rotated = imagerotate($img, 90, 0);
                } elseif ($orientation == 3) {
                    $rotated = imagerotate($img, 180, 0);
                } else {
                    $w = imagesx($img);
                    $h = imagesy($img);
                    if ($w > $h) {
                        $rotated = imagerotate($img, 270, 0);
                    }
                }

                if ($rotated !== false) {
                    ob_start();
                    imagejpeg($rotated, null, 92);
                    $image_base64_decoded = ob_get_clean();
                    imagedestroy($rotated);
                    $image_type = 'jpg';
                }
                imagedestroy($img);
            }
        }
    }

    $file_name = "abs_" . $student_id . "_" . time() . "." . $image_type;
    $file_path = $upload_dir . $file_name;
    $db_doc_path = "uploads/absence_docs/" . $file_name;

    file_put_contents($file_path, $image_base64_decoded);
}


$stmt = $conn->prepare("INSERT INTO absence_requests (student_id, ojt_id, date_absent, reason, supporting_document, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
$stmt->bind_param("iisss", $student_id, $ojt_id, $date_absent, $reason, $db_doc_path);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Absence request submitted successfully!"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to submit absence request: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
