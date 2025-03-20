<?php
require_once(__DIR__ . "/../../../database/config.php");

if (isset($_POST['org_id'])) {
    $org_id = intval($_POST['org_id']);
    $org_name = trim($_POST['name']);
    $org_description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $leader_id = $_POST['leader_id'] ?? null;

    // Fetch the current organization logo
    $fetch_logo = $conn->prepare("SELECT logo FROM organizations WHERE org_id = ?");
    $fetch_logo->bind_param("i", $org_id);
    $fetch_logo->execute();
    $fetch_logo_result = $fetch_logo->get_result();
    $org_data = $fetch_logo_result->fetch_assoc();
    $org_logo = $org_data['logo'] ?? "assets/images/orgs/default-org.png"; // Default logo if none exists

    // Update the organization data
    $sql = "UPDATE organizations SET 
            name = ?, 
            description = ?, 
            category_id = ? 
            WHERE org_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssii", $org_name, $org_description, $category_id, $org_id);
        if ($stmt->execute()) {
            // If a leader is selected, update the membership table
            if ($leader_id) {
                // Remove existing officer
                $removeLeader = "DELETE FROM membership WHERE org_id = ? AND role = 'officer'";
                $removeStmt = $conn->prepare($removeLeader);
                $removeStmt->bind_param("i", $org_id);
                $removeStmt->execute();

                // Add the new leader (officer)
                $assignLeader = "INSERT INTO membership (org_id, user_id, role) VALUES (?, ?, 'officer')";
                $leaderStmt = $conn->prepare($assignLeader);
                $leaderStmt->bind_param("ii", $org_id, $leader_id);
                $leaderStmt->execute();
            }

            // Get the updated category name
            $categoryQuery = "SELECT category_name FROM org_categories WHERE category_id = ?";
            $categoryStmt = $conn->prepare($categoryQuery);
            $categoryStmt->bind_param("i", $category_id);
            $categoryStmt->execute();
            $categoryResult = $categoryStmt->get_result();
            $categoryName = $categoryResult->num_rows > 0 ? $categoryResult->fetch_assoc()['category_name'] : 'N/A';

            // Get the updated leader name
            $leaderName = "N/A";
            if ($leader_id) {
                $leaderQuery = "SELECT full_name FROM users WHERE user_id = ?";
                $leaderStmt = $conn->prepare($leaderQuery);
                $leaderStmt->bind_param("i", $leader_id);
                $leaderStmt->execute();
                $leaderResult = $leaderStmt->get_result();
                $leaderName = $leaderResult->num_rows > 0 ? $leaderResult->fetch_assoc()['full_name'] : 'N/A';
            }

            // ✅ Return success with updated data, including logo
            echo "success|$org_id|$org_name|$org_description|$leaderName|$categoryName|$org_logo";
        } else {
            echo "Error updating organization: " . $stmt->error;
        }
    }
}
