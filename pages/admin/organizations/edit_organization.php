<?php
require_once(__DIR__ . "/../../../database/config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Fetching the posted data securely
    $org_id = intval($_POST['org_id']);
    $org_name = trim($_POST['name']);
    $org_description = trim($_POST['description']);
    $category_id = intval($_POST['category_id']);
    $leader_id = !empty($_POST['leader_id']) ? intval($_POST['leader_id']) : null;

    // ✅ Fetch current logo to handle updates
    $fetch_logo = $conn->prepare("SELECT logo FROM organizations WHERE org_id = ?");
    $fetch_logo->bind_param("i", $org_id);
    $fetch_logo->execute();
    $result = $fetch_logo->get_result();
    $org_data = $result->fetch_assoc();
    $old_logo = $org_data['logo'] ?? '';

    $fetch_logo->close();

    // ✅ Handle Logo Upload
    $updated_logo = $old_logo; // Default to old logo if no new upload
    if (!empty($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
        $upload_dir = __DIR__ . "/../../../assets/images/orgs/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Validate file type
        $file_ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $valid_extensions = ["jpg", "jpeg", "png"];

        if (!in_array($file_ext, $valid_extensions)) {
            echo "error|Invalid file format. Only JPG, JPEG, and PNG allowed.";
            exit();
        }

        // Generate unique filename
        $logo_file_name = "org_{$org_id}_" . time() . "." . $file_ext;
        $target_file = $upload_dir . $logo_file_name;
        $db_logo_path = "/assets/images/orgs/" . $logo_file_name;

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            // Delete old logo if it exists and isn't the default one
            if (!empty($old_logo) && $old_logo !== "assets/images/default-org.png") {
                $old_file_path = __DIR__ . "/../../../" . $old_logo;
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $updated_logo = $db_logo_path;
        } else {
            echo "error|Error uploading logo.";
            exit();
        }
    }

    // ✅ Update organization details
    $updateOrgQuery = "UPDATE organizations SET 
                        name = ?, 
                        description = ?, 
                        category_id = ?, 
                        logo = ?
                        WHERE org_id = ?";

    $stmt = $conn->prepare($updateOrgQuery);
    $stmt->bind_param("ssisi", $org_name, $org_description, $category_id, $updated_logo, $org_id);

    if ($stmt->execute()) {
        // ✅ Fetch category name
        $categoryQuery = "SELECT category_name FROM org_categories WHERE category_id = ?";
        $categoryStmt = $conn->prepare($categoryQuery);
        $categoryStmt->bind_param("i", $category_id);
        $categoryStmt->execute();
        $categoryStmt->bind_result($category_name);
        $categoryStmt->fetch();
        $categoryStmt->close();

        // ✅ If a leader is provided, update the membership table
        if (!empty($leader_id)) {
            // Remove old leader
            $removeOldLeaderQuery = "DELETE FROM membership WHERE org_id = ? AND role = 'officer'";
            $removeOldLeaderStmt = $conn->prepare($removeOldLeaderQuery);
            $removeOldLeaderStmt->bind_param("i", $org_id);
            $removeOldLeaderStmt->execute();
            $removeOldLeaderStmt->close();

            // Assign the new leader
            $assignLeaderQuery = "INSERT INTO membership (org_id, user_id, role) VALUES (?, ?, 'officer')";
            $assignLeaderStmt = $conn->prepare($assignLeaderQuery);
            $assignLeaderStmt->bind_param("ii", $org_id, $leader_id);
            $assignLeaderStmt->execute();
            $assignLeaderStmt->close();

            // ✅ Fetch leader name
            $leaderQuery = "SELECT full_name FROM users WHERE user_id = ?";
            $leaderStmt = $conn->prepare($leaderQuery);
            $leaderStmt->bind_param("i", $leader_id);
            $leaderStmt->execute();
            $leaderStmt->bind_result($leader_name);
            $leaderStmt->fetch();
            $leaderStmt->close();

            echo "success|$org_id|$org_name|$org_description|$leader_name|$category_name|$updated_logo"; // ✅ Includes logo path
        } else {
            // Remove leader if not provided
            $removeLeaderQuery = "DELETE FROM membership WHERE org_id = ? AND role = 'officer'";
            $removeLeaderStmt = $conn->prepare($removeLeaderQuery);
            $removeLeaderStmt->bind_param("i", $org_id);
            $removeLeaderStmt->execute();
            $removeLeaderStmt->close();

            echo "success|$org_id|$org_name|$org_description|N/A|$category_name|$updated_logo"; // ✅ Includes logo path
        }
    } else {
        echo "error|Error updating organization: " . $stmt->error;
    }

    $stmt->close();
}
