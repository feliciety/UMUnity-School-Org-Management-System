<?php
require_once(__DIR__ . "/../../database/config.php");
session_start();

// Ensure the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Unauthorized");
}

// Get input values
$event_id = $_POST['event_id'] ?? 0;
$action = $_POST['action'] ?? '';

if (!$event_id || !in_array($action, ['approve', 'delete', 'reject'])) {
    exit("Invalid request");
}

// Approve Event
if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE events SET status = 'approved' WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    if ($stmt->execute()) {
        exit("Event Approved");
    } else {
        exit("Error approving event");
    }
}

// Reject Event
if ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE events SET status = 'rejected' WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    if ($stmt->execute()) {
        exit("Event Rejected");
    } else {
        exit("Error rejecting event");
    }
}

// Soft Delete Event (Instead of permanent deletion)
if ($action === 'delete') {
    $stmt = $conn->prepare("UPDATE events SET deleted_at = NOW() WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    if ($stmt->execute()) {
        exit("Event Deleted");
    } else {
        exit("Error deleting event");
    }
}

// If no valid action, return error
exit("Invalid action");
