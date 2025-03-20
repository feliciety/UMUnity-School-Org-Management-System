<?php
require_once(__DIR__ . "/../../../database/config.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date_time = $_POST['date_time'];
    $venue = $_POST['venue'];
    $capacity = $_POST['capacity'];

    // ✅ Update event details
    $stmt = $conn->prepare("
        UPDATE events 
        SET title = ?, description = ?, date_time = ?, venue = ?, capacity = ? 
        WHERE event_id = ?
    ");
    $stmt->bind_param("ssssii", $title, $description, $date_time, $venue, $capacity, $event_id);

    if ($stmt->execute()) {
        $stmt->close();

        // ✅ Check if `status` column exists before querying
        $check_status = $conn->query("SHOW COLUMNS FROM events LIKE 'status'");
        if ($check_status->num_rows > 0) {
            $stmt = $conn->prepare("SELECT status FROM events WHERE event_id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $event = $result->fetch_assoc();
            $status = $event["status"] ?? "pending";
        } else {
            $status = "pending"; // Default value if `status` column does not exist
        }

        // ✅ Return response: success|event_id|title|date_time|venue|status
        echo "success|$event_id|$title|$date_time|$venue|$status";
    } else {
        echo "error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
