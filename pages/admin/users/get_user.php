<?php
require_once(__DIR__ . "/../../../database/config.php");

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["user_id"])) {
    $user_id = intval($_POST["user_id"]);

    // Fetch user details including assigned organization
    $stmt = $conn->prepare("
        SELECT 
            u.user_id, 
            u.full_name, 
            u.email, 
            u.role_id, 
            COALESCE(m.org_id, '') AS org_id 
        FROM users u
        LEFT JOIN membership m ON u.user_id = m.user_id
        WHERE u.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        echo json_encode($user);
    } else {
        echo json_encode(["error" => "User not found"]);
    }

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid request"]);
}
