<?php
require_once(__DIR__ . "/../../../database/config.php");

if (isset($_POST['org_name']) && isset($_POST['category_id'])) {
    $org_name = $_POST['org_name'];
    $org_description = $_POST['org_description'];
    $category_id = $_POST['category_id'];
    $leader_id = $_POST['leader_id'] ?? null;  // Get leader_id if assigned

    // Insert the new organization without the leader_id field
    $sql = "INSERT INTO organizations (name, description, category_id, status) 
            VALUES (?, ?, ?, 'active')";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssi", $org_name, $org_description, $category_id);
        $stmt->execute();

        // Get the new organization's ID
        $newOrgId = $conn->insert_id;

        // If a leader is provided, assign the leader in the membership table
        if ($leader_id) {
            $membershipSql = "INSERT INTO membership (org_id, user_id, role) VALUES (?, ?, 'officer')";
            $stmt = $conn->prepare($membershipSql);
            $stmt->bind_param("ii", $newOrgId, $leader_id);
            $stmt->execute();
        }

        // Return success with relevant organization data (including leader name and category name)
        $categoryQuery = "SELECT category_name FROM org_categories WHERE category_id = ?";
        $categoryStmt = $conn->prepare($categoryQuery);
        $categoryStmt->bind_param("i", $category_id);
        $categoryStmt->execute();
        $categoryResult = $categoryStmt->get_result();
        $categoryName = $categoryResult->num_rows > 0 ? $categoryResult->fetch_assoc()['category_name'] : 'N/A';

        $leaderName = "N/A";
        if ($leader_id) {
            $leaderQuery = "SELECT full_name FROM users WHERE user_id = ?";
            $leaderStmt = $conn->prepare($leaderQuery);
            $leaderStmt->bind_param("i", $leader_id);
            $leaderStmt->execute();
            $leaderResult = $leaderStmt->get_result();
            $leaderName = $leaderResult->num_rows > 0 ? $leaderResult->fetch_assoc()['full_name'] : 'N/A';
        }

        echo "success|$newOrgId|$org_name|$org_description|$leaderName|$categoryName";
    } else {
        echo "Error in organization insertion.";
    }
}
