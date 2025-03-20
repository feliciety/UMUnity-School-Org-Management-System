<?php
require_once(__DIR__ . "/../../../database/config.php");
require_once(__DIR__ . "/../../../includes/log_activity.php");

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']); // ⚠️ Plain text password (NO HASHING)
    $roleId = intval($_POST['role']);
    $orgId = !empty($_POST['org_id']) ? intval($_POST['org_id']) : null;

    // Check if email already exists
    $checkQuery = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $checkQuery->bind_param("s", $email);
    $checkQuery->execute();
    $checkQuery->store_result();

    if ($checkQuery->num_rows > 0) {
        echo "error|Email already exists!";
        exit;
    }
    $checkQuery->close();

    // Insert user (NO HASHING)
    $insertQuery = $conn->prepare("INSERT INTO users (full_name, email, password, role_id, status) VALUES (?, ?, ?, ?, 'active')");
    $insertQuery->bind_param("sssi", $fullName, $email, $password, $roleId);

    if ($insertQuery->execute()) {
        $newUserId = $insertQuery->insert_id;
        $insertQuery->close();

        $orgName = "N/A";
        // Assign user to an organization if selected and fetch org name
        if ($orgId) {
            $membershipQuery = $conn->prepare("INSERT INTO membership (user_id, org_id, status, role) VALUES (?, ?, 'approved', 'member')");
            $membershipQuery->bind_param("ii", $newUserId, $orgId);
            $membershipQuery->execute();
            $membershipQuery->close();

            // Fetch organization name
            $orgQuery = $conn->prepare("SELECT name FROM organizations WHERE org_id = ?");
            $orgQuery->bind_param("i", $orgId);
            $orgQuery->execute();
            $orgResult = $orgQuery->get_result();
            if ($orgRow = $orgResult->fetch_assoc()) {
                $orgName = $orgRow['name'];
            }
            $orgQuery->close();
        }


        echo "success|$newUserId|$fullName|$email|$roleId|$orgName";
    } else {
        echo "error|Failed to add user!";
    }

    // ✅ Log Message for adding User
    $adminId = $_SESSION["user_id"] ?? null;
    $roleName = ($roleId == 1) ? "<b>Admin</b>" : (($roleId == 2) ? "<b>Officer</b>" : "<b>Student</b>");
    $logMessage = "<b>$fullName</b> (<b>$email</b>) added as $roleName" . ($orgId ? " in <b>$orgName</b>" : "");

    logActivity($adminId, "User Added", $logMessage);
}
