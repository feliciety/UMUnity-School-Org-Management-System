<?php
require_once(__DIR__ . "/../../../database/config.php");
require_once(__DIR__ . "/../../../includes/log_activity.php"); // ✅ Include logging function
session_start();

ob_start(); // ✅ Prevents unwanted output before "success"

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["user_id"])) {
    $user_id = intval($_POST["user_id"]);

    // ✅ Fetch user details before deletion
    $userQuery = $conn->prepare("SELECT full_name, email, role_id FROM users WHERE user_id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userQuery->bind_result($fullName, $email, $roleId);

    if ($userQuery->fetch()) {
        $userQuery->close();

        // ✅ Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // ✅ Log Activity BEFORE closing the connection
            $adminId = $_SESSION["user_id"] ?? null;
            $roleName = ($roleId == 1) ? "Admin" : (($roleId == 2) ? "Officer" : "Student");
            $logMessage = "User '<b>$fullName</b>' ($email) with role <b>$roleName</b> was deleted.";

            logActivity($adminId, "User Deleted", $logMessage);

            $stmt->close();
            $conn->close();

            ob_clean(); // ✅ Clears any unwanted output
            exit("success"); // ✅ Only return "success"
        } else {
            ob_clean();
            exit("Error: " . $conn->error);
        }
    } else {
        ob_clean();
        exit("Error: User not found!");
    }
} else {
    ob_clean();
    exit("Invalid request!");
}
