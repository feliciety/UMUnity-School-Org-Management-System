<?php
require_once(__DIR__ . "/../../../database/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['org_id'])) {
    $org_id = $_POST['org_id'];

    // Check if the organization exists before deleting
    $checkStmt = $conn->prepare("SELECT org_id FROM organizations WHERE org_id = ?");
    $checkStmt->bind_param("i", $org_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Perform Hard Delete - Remove the organization from the database
        $stmt = $conn->prepare("DELETE FROM organizations WHERE org_id = ?");
        $stmt->bind_param("i", $org_id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Error: Organization not found.";
    }

    $checkStmt->close();
}

$conn->close();
