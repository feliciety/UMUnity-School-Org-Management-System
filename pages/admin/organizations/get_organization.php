<?php
require_once(__DIR__ . "/../../../database/config.php");

if (isset($_POST['org_id'])) {
    $org_id = $_POST['org_id'];

    // Fetch the organization's details along with the leader's name, leader's user ID, category name, and logo
    $sql = "
    SELECT 
        o.org_id, 
        o.name, 
        o.description, 
        COALESCE(o.logo, 'assets/images/default-org.png') AS logo,  -- Include logo with a default value
        COALESCE(u.full_name, 'N/A') AS leader_name, 
        COALESCE(u.user_id, 0) AS leader_id,  -- Fetch the leader's user ID
        COALESCE(c.category_name, 'N/A') AS category_name,
        o.category_id
    FROM organizations o
    LEFT JOIN membership m ON o.org_id = m.org_id AND m.role = 'officer' 
    LEFT JOIN users u ON m.user_id = u.user_id
    LEFT JOIN org_categories c ON o.category_id = c.category_id
    WHERE o.org_id = ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $org_id);  // Bind the org_id parameter
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);  // Return the organization data with the logo
    } else {
        echo json_encode(["error" => "Organization not found."]);
    }

    $stmt->close();
}
