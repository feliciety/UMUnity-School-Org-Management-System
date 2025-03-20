<?php
require_once(__DIR__ . "/../../../database/config.php");

if (isset($_POST['full_name']) && isset($_POST['email']) && isset($_POST['password']) && isset($_POST['role'])) {
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
    $role_id = intval($_POST["role"]);
    $org_id = isset($_POST["org_id"]) && !empty($_POST["org_id"]) ? intval($_POST["org_id"]) : NULL;

    // Check if email already exists
    $checkStmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "Error: Email already exists!";
        exit();
    }
    $checkStmt->close();

    // Insert the new user into the users table
    $sql = "INSERT INTO users (full_name, email, password, role_id, org_id, status) 
            VALUES (?, ?, ?, ?, ?, 'active')";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssii", $full_name, $email, $password, $role_id, $org_id);
        $stmt->execute();

        // Get the new user's ID
        $newUserId = $conn->insert_id;

        // If a user is assigned an organization, add them to the membership table
        if ($org_id) {
            $membershipSql = "INSERT INTO membership (org_id, user_id, role) VALUES (?, ?, 'member')";
            $stmt = $conn->prepare($membershipSql);
            $stmt->bind_param("ii", $org_id, $newUserId);
            $stmt->execute();
        }

        // Retrieve role name
        $roleQuery = "SELECT name FROM roles WHERE role_id = ?";
        $roleStmt = $conn->prepare($roleQuery);
        $roleStmt->bind_param("i", $role_id);
        $roleStmt->execute();
        $roleResult = $roleStmt->get_result();
        $role_name = $roleResult->num_rows > 0 ? $roleResult->fetch_assoc()['name'] : 'N/A';

        // Retrieve organization name if assigned
        $org_name = "N/A";
        if ($org_id) {
            $orgQuery = "SELECT name FROM organizations WHERE org_id = ?";
            $orgStmt = $conn->prepare($orgQuery);
            $orgStmt->bind_param("i", $org_id);
            $orgStmt->execute();
            $orgResult = $orgStmt->get_result();
            $org_name = $orgResult->num_rows > 0 ? $orgResult->fetch_assoc()['name'] : 'N/A';
        }

        // Return success with relevant user data
        echo "success|$newUserId|$full_name|$email|$role_name|$org_name";
    } else {
        echo "Error inserting user.";
    }
} else {
    echo "Error: Missing required fields.";
}
