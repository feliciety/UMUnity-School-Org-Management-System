<?php
require_once(__DIR__ . "/../../../database/config.php");

if (isset($_POST['org_name']) && isset($_POST['category_id'])) {
    $org_name = trim($_POST['org_name']);
    $org_description = trim($_POST['org_description']);
    $category_id = intval($_POST['category_id']);
    $leader_id = isset($_POST['leader_id']) && !empty($_POST['leader_id']) ? intval($_POST['leader_id']) : null;

    // ✅ Step 1: Insert Organization Without leader_id
    $sql = "INSERT INTO organizations (name, description, category_id, status) 
            VALUES (?, ?, ?, 'active')";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssi", $org_name, $org_description, $category_id);
        if (!$stmt->execute()) {
            error_log("SQL Error: " . $stmt->error);
            echo "Error: Unable to insert organization.";
            exit();
        }

        // ✅ Get the newly created organization ID
        $newOrgId = $conn->insert_id;

        // ✅ Step 2: If a leader is provided, update the organization & add to membership
        if ($leader_id) {
            // Update organization with leader_id
            $updateOrgSql = "UPDATE organizations SET leader_id = ? WHERE org_id = ?";
            $stmt = $conn->prepare($updateOrgSql);
            $stmt->bind_param("ii", $leader_id, $newOrgId);
            if (!$stmt->execute()) {
                error_log("Leader Assignment Error: " . $stmt->error);
            }

            // Insert into membership table
            $membershipSql = "INSERT INTO membership (org_id, user_id, role) VALUES (?, ?, 'officer')";
            $stmt = $conn->prepare($membershipSql);
            $stmt->bind_param("ii", $newOrgId, $leader_id);
            if (!$stmt->execute()) {
                error_log("Membership Insert Error: " . $stmt->error);
            }
        }

        // ✅ Fetch category name
        $categoryName = "N/A";
        $categoryQuery = "SELECT category_name FROM org_categories WHERE category_id = ?";
        if ($categoryStmt = $conn->prepare($categoryQuery)) {
            $categoryStmt->bind_param("i", $category_id);
            $categoryStmt->execute();
            $categoryStmt->bind_result($categoryName);
            $categoryStmt->fetch();
            $categoryStmt->close();
        }

        // ✅ Fetch leader name
        $leaderName = "N/A";
        if ($leader_id) {
            $leaderQuery = "SELECT full_name FROM users WHERE user_id = ?";
            if ($leaderStmt = $conn->prepare($leaderQuery)) {
                $leaderStmt->bind_param("i", $leader_id);
                $leaderStmt->execute();
                $leaderStmt->bind_result($leaderName);
                $leaderStmt->fetch();
                $leaderStmt->close();
            }
        }

        // ✅ Return success response
        echo "success|$newOrgId|$org_name|$org_description|$leaderName|$categoryName";
        exit();
    } else {
        error_log("SQL Prepare Error: " . $conn->error);
        echo "Error: SQL Statement Failed.";
        exit();
    }
} else {
    echo "Error: Missing required fields.";
    exit();
}
