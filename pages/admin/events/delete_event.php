<?php
require_once(__DIR__ . "/../../../database/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // ✅ Check if event exists before deleting
    $checkStmt = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
    $checkStmt->bind_param("i", $event_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // ✅ Perform Hard Delete - Remove the event from the database
        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);

        if ($stmt->execute()) {
            echo "success"; // ✅ Return only "success"
        } else {
            echo "error: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "error: Event not found.";
    }

    $checkStmt->close();
}

$conn->close();
