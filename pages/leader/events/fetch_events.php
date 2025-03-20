<?php
require_once(__DIR__ . "/../../database/config.php");
require_once(__DIR__ . "/../../database/functions.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is authenticated and is a leader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    echo json_encode(["error" => "Unauthorized access"]);
    exit();
}

$leader_id = $_SESSION['user_id'];
$events = get_all_org_events($leader_id, $conn);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($events);
