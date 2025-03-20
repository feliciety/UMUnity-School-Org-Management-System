<?php
require_once(__DIR__ . "/../../../database/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate input
    if (!isset($_POST["org_id"]) || !isset($_POST["status"])) {
        echo "Error: Missing required parameters!";
        exit();
    }

    $org_id = intval($_POST["org_id"]);
    $new_status = ($_POST["status"] === "active") ? "active" : "disbanded"; // Validate status input

    // Check if the organization exists
    $checkStmt = $conn->prepare("SELECT org_id FROM organizations WHERE org_id = ?");
    $checkStmt->bind_param("i", $org_id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 0) {
        echo "Error: Organization not found!";
        exit();
    }
    $checkStmt->close();

    // Update organization status
    $stmt = $conn->prepare("UPDATE organizations SET status = ? WHERE org_id = ?");
    $stmt->bind_param("si", $new_status, $org_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request!";
}
