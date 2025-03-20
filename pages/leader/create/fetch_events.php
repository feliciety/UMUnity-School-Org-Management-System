<?php
require_once(__DIR__ . "/../../database/config.php");
require_once(__DIR__ . "/../../database/functions.php");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and authorized
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'officer') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$events = [];

// Fetch organization IDs where the officer is a member
$sql = "SELECT DISTINCT org_id FROM membership WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$org_ids = [];
while ($row = $result->fetch_assoc()) {
    $org_ids[] = $row['org_id'];
}

// If officer is part of any organization, fetch events
if (!empty($org_ids)) {
    $org_id_list = implode(',', array_map('intval', $org_ids));

    $event_sql = "SELECT e.event_id, e.title, e.date_time, e.venue, e.capacity, e.status, o.name AS org_name
                  FROM events e
                  JOIN organizations o ON e.org_id = o.org_id
                  WHERE e.org_id IN ($org_id_list)
                  ORDER BY e.date_time DESC";

    $event_result = $conn->query($event_sql);

    if ($event_result) {
        $events = $event_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($events);
exit();
