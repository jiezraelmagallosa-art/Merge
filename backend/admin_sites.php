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

try {
    require_once 'db_connect.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw_input = file_get_contents("php://input");
        $data = json_decode($raw_input, true);
        if (!$data)
            $data = $_POST;

        $action = isset($data['action']) ? trim($data['action']) : '';
        $site_id = isset($data['site_id']) ? intval($data['site_id']) : 0;

        if ($action === 'delete') {
            if ($site_id <= 0) {
                echo json_encode(["status" => "error", "message" => "Invalid facility ID."]);
                exit();
            }

            // Check if active interns assigned (only count real existing students)
            $check_stmt = $conn->prepare("SELECT COUNT(DISTINCT s.student_id) AS total FROM ojt o INNER JOIN student s ON o.student_id = s.student_id WHERE o.site_id = ?");
            $check_stmt->bind_param("i", $site_id);
            $check_stmt->execute();
            $assigned_count = intval($check_stmt->get_result()->fetch_assoc()['total'] ?? 0);
            $check_stmt->close();

            if ($assigned_count > 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Cannot delete facility because it currently has {$assigned_count} active assigned student(s)."
                ]);
                exit();
            }

            $del_stmt = $conn->prepare("DELETE FROM training_site WHERE site_id = ?");
            $del_stmt->bind_param("i", $site_id);
            if ($del_stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "Partner facility deleted successfully."]);
            } else {
                echo json_encode(["status" => "error", "message" => "Deletion failed: " . $del_stmt->error]);
            }
            $del_stmt->close();
            exit();
        }

        $site_code = isset($data['site_code']) ? trim($data['site_code']) : '';
        $site_name = isset($data['site_name']) ? trim($data['site_name']) : '';
        $location = isset($data['location']) ? trim($data['location']) : '';

        if (empty($site_code) || empty($site_name)) {
            echo json_encode(["status" => "error", "message" => "Partner code and facility name are required."]);
            exit();
        }

        if ($site_id > 0) {
            $stmt = $conn->prepare("UPDATE training_site SET site_code = ?, site_name = ?, location = ? WHERE site_id = ?");
            $stmt->bind_param("sssi", $site_code, $site_name, $location, $site_id);
            $msg = "Training partner updated successfully.";
        } else {
            $stmt = $conn->prepare("INSERT INTO training_site (site_code, site_name, location) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $site_code, $site_name, $location);
            $msg = "New training partner added successfully.";
        }

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => $msg]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database operation failed: " . $stmt->error]);
        }
        $stmt->close();

    } else {

        $dean_id = isset($_GET['dean_id']) ? intval($_GET['dean_id']) : (isset($_POST['dean_id']) ? intval($_POST['dean_id']) : 0);

        if ($dean_id > 0) {
            // Count existing students assigned under this specific Dean
            $sql = "SELECT
                        ts.site_id,
                        ts.site_code,
                        ts.site_name,
                        ts.location,
                        COUNT(DISTINCT s.student_id) AS assigned_interns
                    FROM training_site ts
                    LEFT JOIN ojt o ON ts.site_id = o.site_id
                    LEFT JOIN student s ON o.student_id = s.student_id AND s.dean_id = " . intval($dean_id) . "
                    GROUP BY ts.site_id
                    ORDER BY ts.site_name ASC";
        } else {
            $sql = "SELECT
                        ts.site_id,
                        ts.site_code,
                        ts.site_name,
                        ts.location,
                        COUNT(DISTINCT s.student_id) AS assigned_interns
                    FROM training_site ts
                    LEFT JOIN ojt o ON ts.site_id = o.site_id
                    LEFT JOIN student s ON o.student_id = s.student_id
                    GROUP BY ts.site_id
                    ORDER BY ts.site_name ASC";
        }

        $result = $conn->query($sql);
        $sites = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sites[] = [
                    "site_id" => intval($row['site_id']),
                    "site_code" => $row['site_code'],
                    "site_name" => $row['site_name'],
                    "location" => $row['location'] ?? 'M\'lang, Cotabato',
                    "assigned_interns" => intval($row['assigned_interns'] ?? 0)
                ];
            }
        }

        echo json_encode([
            "status" => "success",
            "data" => $sites
        ]);
    }

    $conn->close();

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
}
?>