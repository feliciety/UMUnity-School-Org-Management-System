<?php
require_once(__DIR__ . "/../../../database/config.php");

header("Content-Type: application/json");

if (!isset($_POST['event_id'])) {
    echo json_encode(["error" => "No event ID provided"]);
    exit();
}

$event_id = $_POST['event_id'];

// ✅ Fetch the event based on ID
$sql = "SELECT e.event_id, e.title, e.description, e.date_time, 
               e.venue, e.capacity, 
               COALESCE(es.status, 'Pending') AS status, 
               COALESCE(o.name, 'N/A') AS organization
        FROM events e
        LEFT JOIN event_status es ON e.event_id = es.event_id
        LEFT JOIN organizations o ON e.org_id = o.org_id 
        WHERE e.event_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
$event = $result->fetch_assoc();

if (!$event) {
    echo json_encode(["error" => "Event not found"]);
    exit();
}

// ✅ Send JSON Response
echo json_encode($event, JSON_PRETTY_PRINT);
exit();
