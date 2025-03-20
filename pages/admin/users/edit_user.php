<?php
require_once(__DIR__ . "/../../../database/config.php");
require_once(__DIR__ . "/../../../includes/log_activity.php"); // Include logging function
session_start(); // ✅ Ensure session is started

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = intval($_POST['user_id']);
    $fullName = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $roleId = intval($_POST['role_id']);
    $orgId = (isset($_POST['org_id']) && $_POST['org_id'] !== "") ? intval($_POST['org_id']) : null;

    // ✅ Fetch old user details before update
    $oldQuery = $conn->prepare("SELECT full_name, email, role_id, 
                                (SELECT name FROM organizations WHERE org_id = 
                                (SELECT org_id FROM membership WHERE user_id = ? LIMIT 1)) AS organization 
                                FROM users WHERE user_id = ?");
    $oldQuery->bind_param("ii", $userId, $userId);
    $oldQuery->execute();
    $oldQuery->bind_result($oldFullName, $oldEmail, $oldRoleId, $oldOrgName);
    $oldQuery->fetch();
    $oldQuery->close();

    // ✅ Fetch new organization name
    $orgName = "N/A";
    if ($orgId) {
        $orgQuery = $conn->prepare("SELECT name FROM organizations WHERE org_id = ?");
        $orgQuery->bind_param("i", $orgId);
        $orgQuery->execute();
        $orgQuery->bind_result($orgName);
        $orgQuery->fetch();
        $orgQuery->close();
    }

    // ✅ Check if email already exists for another user
    $checkQuery = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
    $checkQuery->bind_param("si", $email, $userId);
    $checkQuery->execute();
    $checkQuery->store_result();
    if ($checkQuery->num_rows > 0) {
        echo "error|Email already exists!";
        exit;
    }
    $checkQuery->close();

    // ✅ Update user details
    $updateQuery = $conn->prepare("UPDATE users SET full_name = ?, email = ?, role_id = ? WHERE user_id = ?");
    $updateQuery->bind_param("ssii", $fullName, $email, $roleId, $userId);

    if ($updateQuery->execute()) {
        $updateQuery->close();

        // ✅ Update or insert organization assignment
        if ($orgId) {
            $orgCheck = $conn->prepare("SELECT membership_id FROM membership WHERE user_id = ?");
            $orgCheck->bind_param("i", $userId);
            $orgCheck->execute();
            $orgCheck->store_result();

            if ($orgCheck->num_rows > 0) {
                $orgUpdate = $conn->prepare("UPDATE membership SET org_id = ? WHERE user_id = ?");
                $orgUpdate->bind_param("ii", $orgId, $userId);
                $orgUpdate->execute();
                $orgUpdate->close();
            } else {
                $orgInsert = $conn->prepare("INSERT INTO membership (user_id, org_id, status, role) VALUES (?, ?, 'approved', 'member')");
                $orgInsert->bind_param("ii", $userId, $orgId);
                $orgInsert->execute();
                $orgInsert->close();
            }
            $orgCheck->close();
        }

        echo "success|$userId|$fullName|$email|$roleId|$orgId|$orgName";
    } else {
        echo "error|Failed to update user!";
    }

    // ✅ Debugging: Check if session exists
    if (!isset($_SESSION["user_id"])) {
        echo "error|Session not found!";
        exit;
    }

    // ✅ Log Activity
    $adminId = $_SESSION["user_id"];
    $roleName = ($roleId == 1) ? "<b>Admin</b>" : (($roleId == 2) ? "<b>Officer</b>" : "<b>Student</b>");

    // ✅ Detect changes and format log message
    $changes = [];
    if ($oldFullName !== $fullName) $changes[] = "Name: <b>$oldFullName</b> ➝ <b>$fullName</b>";
    if ($oldEmail !== $email) $changes[] = "Email: <b>$oldEmail</b> ➝ <b>$email</b>";
    if ($oldRoleId !== $roleId) {
        $oldRoleName = ($oldRoleId == 1) ? "<b>Admin</b>" : (($oldRoleId == 2) ? "<b>Officer</b>" : "<b>Student</b>");
        $changes[] = "Role: $oldRoleName ➝ $roleName";
    }
    if ($oldOrgName !== $orgName) {
        $changes[] = "Organization: <b>$oldOrgName</b> ➝ <b>$orgName</b>";
    }

    if (!empty($changes)) {
        $logMessage = "<b>$fullName</b> updated details: " . implode(", ", $changes);
        logActivity($adminId, "User Updated", $logMessage);

        // ✅ Debugging: Check if logActivity() was called
        echo "Logging Activity: $logMessage";
    } else {
        echo "No changes detected.";
    }
}
