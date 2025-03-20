<?php
require_once(__DIR__ . "/../../../database/config.php");
require_once(__DIR__ . "/../../../database/functions.php");

session_start();

// Ensure user is logged in and is a leader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    $_SESSION['toast_messages'][] = ["type" => "error", "message" => "Unauthorized access."];
    header("Location: ../my_organization.php");
    exit();
}

$leader_id = $_SESSION['user_id'];

// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['org_id']) || empty($_POST['org_id'])) {
        $_SESSION['toast_messages'][] = ["type" => "error", "message" => "Invalid organization ID."];
        header("Location: ../my_organization.php");
        exit();
    }

    $org_id = intval($_POST['org_id']);
    $updated_fields = [];

    // ✅ Step 1: Fetch Current Organization Details (including existing logo)
    $fetch_logo = $conn->prepare("SELECT logo FROM organizations WHERE org_id = ?");
    $fetch_logo->bind_param("i", $org_id);
    $fetch_logo->execute();
    $fetch_logo_result = $fetch_logo->get_result();
    $org_data = $fetch_logo_result->fetch_assoc();
    $old_logo = $org_data['logo'] ?? "assets/images/orgs/default-org.png"; // Default logo path

    // ✅ Step 2: Process and Update Other Fields
    $fields = ['name', 'description', 'website', 'facebook', 'twitter', 'instagram'];
    foreach ($fields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            $updated_fields[$field] = trim($_POST[$field]);
        }
    }

    // ✅ Step 3: Handle Logo Upload and Replacement
    if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
        $upload_dir = __DIR__ . "/../../../assets/images/orgs/"; // Absolute path

        // Ensure the directory exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_ext = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
        $valid_extensions = ["jpg", "jpeg", "png"];

        if (!in_array($file_ext, $valid_extensions)) {
            $_SESSION['toast_messages'][] = ["type" => "error", "message" => "Invalid file format. Only JPG, JPEG, and PNG allowed."];
            header("Location: ../my_organization.php");
            exit();
        }

        // Generate unique file name
        // Generate unique file name
        $logo_file_name = "org_{$org_id}_" . time() . "." . $file_ext;
        $target_file = $upload_dir . $logo_file_name;

        // ✅ Absolute URL Path Fix (adjust BASE_URL to your domain or project root)
        $BASE_URL = "http://yourwebsite.com/"; // Replace with actual domain
        $logo_path = "/assets/images/orgs/" . $logo_file_name; // Keep the absolute path for web display
        $logo_full_path = $BASE_URL . ltrim($logo_path, "/"); // Full URL

        if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
            // Delete old logo if not default
            if ($old_logo !== "/assets/images/orgs/default-org.png") {
                $old_file_path = __DIR__ . "/../../../" . ltrim($old_logo, "/"); // Remove leading slash when deleting file
                if (file_exists($old_file_path)) {
                    unlink($old_file_path);
                }
            }
            $updated_fields["logo"] = $logo_path;
            $_SESSION['toast_messages'][] = ["type" => "success", "message" => "Logo updated successfully."];
        } else {
            $_SESSION['toast_messages'][] = ["type" => "error", "message" => "Error uploading logo."];
            header("Location: ../my_organization.php");
            exit();
        }
    }

    // ✅ Step 4: Update Organization in Database
    if (!empty($updated_fields)) {
        $sql = "UPDATE organizations SET ";
        $params = [];
        foreach ($updated_fields as $column => $value) {
            $sql .= "$column = ?, ";
            $params[] = $value;
        }
        $sql = rtrim($sql, ", ") . " WHERE org_id = ?";
        $params[] = $org_id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(str_repeat("s", count($params) - 1) . "i", ...$params);

        if ($stmt->execute()) {
            $_SESSION['toast_messages'][] = ["type" => "success", "message" => "Organization details updated successfully."];

            // ✅ Redirect normally for form submissions
            if (!isset($_POST['ajax'])) {
                header("Location: ../my_organization.php");
                exit();
            }

            // ✅ Return Updated Logo Path in AJAX Response
            echo json_encode(["status" => "success", "logo" => $updated_fields["logo"] ?? $old_logo]);
            exit();
        } else {
            $_SESSION['toast_messages'][] = ["type" => "error", "message" => "Failed to update organization."];
            header("Location: ../my_organization.php");
            exit();
        }
    }
}

// Default Error Redirect
$_SESSION['toast_messages'][] = ["type" => "error", "message" => "Invalid request method."];
header("Location: ../my_organization.php");
exit();
