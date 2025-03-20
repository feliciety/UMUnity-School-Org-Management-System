<?php
require_once(__DIR__ . "/../../../database/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $leader_id = isset($_POST["leader_id"]) ? intval($_POST["leader_id"]) : 0;
    $org_id = isset($_POST["org_id"]) ? intval($_POST["org_id"]) : 0;

    // Validate inputs
    if ($leader_id === 0 || $org_id === 0) {
        echo json_encode(["success" => false, "message" => "Invalid organization or leader selected."]);
        exit();
    }

    // Check if the leader exists in the users table
    $leaderQuery = "SELECT full_name FROM users WHERE user_id = ?";
    $leaderStmt = $conn->prepare($leaderQuery);
    $leaderStmt->bind_param("i", $leader_id);
    $leaderStmt->execute();
    $leaderStmt->bind_result($leader_name);
    $leaderStmt->fetch();
    $leaderStmt->close();

    if (!$leader_name) {
        echo json_encode(["success" => false, "message" => "Selected leader does not exist."]);
        exit();
    }

    // Update leader_id for the specific organization
    $stmt = $conn->prepare("UPDATE organizations SET leader_id = ? WHERE org_id = ?");
    $stmt->bind_param("ii", $leader_id, $org_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "leader_name" => $leader_name, "org_id" => $org_id]);
    } else {
        echo json_encode(["success" => false, "message" => "Database update failed: " . $conn->error]);
    }

    $stmt->close();
}
