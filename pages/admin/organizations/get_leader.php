<?php
require_once("/../../../database/config.php");

// Get the role_id for 'org_lead'
$roleQuery = "SELECT id FROM roles WHERE name = 'org_lead'";
$roleResult = $conn->query($roleQuery);

if ($roleResult->num_rows > 0) {
    $roleRow = $roleResult->fetch_assoc();
    $orgLeadRoleId = $roleRow['id'];

    // Fetch users with 'org_lead' role
    $sql = "SELECT user_id, full_name FROM users WHERE role_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orgLeadRoleId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['user_id'] . '">' . $row['full_name'] . '</option>';
        }
    } else {
        echo '<option value="">No leaders available</option>';
    }
} else {
    echo '<option value="">Error: Role not found</option>';
}
