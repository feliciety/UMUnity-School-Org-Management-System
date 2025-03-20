<?php
require_once(__DIR__ . "/../../../database/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    $stmt = $conn->prepare("UPDATE events SET status = 'approved' WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {
        echo "success|$event_id|approved";
    } else {
        echo "error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
