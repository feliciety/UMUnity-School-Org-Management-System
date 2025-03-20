<?php
require_once(__DIR__ . "/../database/config.php");

if (!function_exists("logActivity")) {
    function logActivity($userId, $action, $details)
    {
        global $conn;
        $ip_address = $_SERVER['REMOTE_ADDR']; // Get user IP

        // ✅ Debugging Messages (Force Print)
        echo "🔹 Function logActivity() called!<br>";
        echo "🔹 Attempting to insert log: $action - $details - IP: $ip_address<br>";

        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address, created_at) 
                                VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isss", $userId, $action, $details, $ip_address);

        if ($stmt->execute()) {
            echo "✅ Activity logged successfully!<br>";
        } else {
            echo "❌ Error logging activity: " . $stmt->error . "<br>";
        }

        $stmt->close();
    }
}
