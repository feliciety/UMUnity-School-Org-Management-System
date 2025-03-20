<?php
session_start(); // Ensure session starts only once
require_once("../database/config.php"); // Ensure database connection
require_once("../includes/log_activity.php"); // Include activity logger

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("
        SELECT users.user_id, users.full_name, users.password, roles.name AS role 
        FROM users 
        LEFT JOIN roles ON users.role_id = roles.role_id 
        WHERE users.email = ?
    ");

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ✅ Plain text password comparison (Use password_verify() if passwords are hashed)
        if ($password === $row['password']) {
            $_SESSION["user_id"] = $row["user_id"];
            $_SESSION["role"] = $row["role"];
            $_SESSION["full_name"] = $row["full_name"];

            // ✅ Log successful login ONLY if user is an admin
            if (strtolower($row["role"]) === "admin") {
                logActivity($row["user_id"], "Admin Login", "{$row['full_name']} logged into the system");
            }

            // ✅ Redirect based on role
            if (strtolower($row["role"]) === "admin") {
                header("Location: /pages/admin/dashboard.php");
            } elseif (strtolower($row["role"]) === "leader") {
                header("Location: /pages/leader/dashboard.php");
            } else {
                header("Location: /pages/student/dashboard.php");
            }
            exit();
        } else {
            // ❌ Log failed login attempt (Admin Only)
            if (strtolower($row["role"]) === "admin") {
                logActivity(null, "Failed Admin Login", "Failed admin login attempt for email: $email");
            }

            $_SESSION["error"] = "Invalid password. Please try again.";
            header("Location: /pages/login.php");
            exit();
        }
    } else {
        // ❌ Log failed login due to user not found (Admin Only)
        logActivity(null, "Failed Admin Login", "Admin email not found: $email");

        $_SESSION["error"] = "User not found. Please check your email.";
        header("Location: /pages/login.php");
        exit();
    }

    // ✅ Close statement and connection
    $stmt->close();
    $conn->close();
}
