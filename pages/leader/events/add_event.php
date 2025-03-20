<?php
require_once(__DIR__ . "/../../database/config.php");
require_once(__DIR__ . "/../../database/functions.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    die(json_encode(["success" => false, "message" => "Unauthorized access!"]));
}

$leader_id = $_SESSION['user_id'];

// Get the leader's organization
$query = "SELECT org_id FROM organizations WHERE leader_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $leader_id);
$stmt->execute();
$result = $stmt->get_result();
$organization = $result->fetch_assoc();

if (!$organization) {
    die(json_encode(["success" => false, "message" => "You are not assigned to any organization."]));
}

$org_id = $organization['org_id'];

// Validate form data with correct column names
$title = isset($_POST['title']) ? trim($_POST['title']) : null;
$date_time = isset($_POST['date_time']) ? $_POST['date_time'] : null;
$venue = isset($_POST['venue']) ? trim($_POST['venue']) : null;
$capacity = isset($_POST['capacity']) ? $_POST['capacity'] : null;
$event_image = isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0 ? $_FILES['event_image'] : null;

// Check for missing values
if (!$title || !$date_time || !$venue || !$capacity) {
    die(json_encode(["success" => false, "message" => "All fields are required!"]));
}

// Upload Image if Provided
$image_name = "default.png";
if ($event_image) {
    $upload_dir = __DIR__ . "/../../uploads/";
    $image_name = time() . "_" . basename($event_image["name"]);
    $target_file = $upload_dir . $image_name;

    if (!move_uploaded_file($event_image["tmp_name"], $target_file)) {
        die(json_encode(["success" => false, "message" => "Failed to upload event image."]));
    }
}

// Insert event into database
$sql = "INSERT INTO events (org_id, title, description, date_time, venue, capacity, status, event_image) 
        VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("issssis", $org_id, $title, $title, $date_time, $venue, $capacity, $image_name);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Event created successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Error creating event: " . $stmt->error]);
}

$stmt->close();
$conn->close();
