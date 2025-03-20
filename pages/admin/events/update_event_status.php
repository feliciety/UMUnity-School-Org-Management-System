<?php
require_once(__DIR__ . "/../../../database/config.php");


$event_id = $_POST["event_id"];
$status = $_POST["status"];
$reason = isset($_POST["reason"]) ? trim($_POST["reason"]) : null;

if ($status === "approved") {
    $stmt = $conn->prepare("INSERT INTO event_status (event_id, status) VALUES (?, ?) 
        ON DUPLICATE KEY UPDATE status = VALUES(status), reason = NULL");
    $stmt->bind_param("is", $event_id, $status);
} elseif ($status === "rejected" && $reason) {
    $stmt = $conn->prepare("INSERT INTO event_status (event_id, status, reason) VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE status = VALUES(status), reason = VALUES(reason)");
    $stmt->bind_param("iss", $event_id, $status, $reason);
} else {
    echo "error";
    exit();
}

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
