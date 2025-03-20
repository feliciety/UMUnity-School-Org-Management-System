<?php
session_start();
include '../../database/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Fetch existing admin resource data before updating
    $fetch_query = $conn->prepare("SELECT * FROM admin_resources WHERE user_id = ?");
    $fetch_query->bind_param("i", $user_id);
    $fetch_query->execute();
    $fetch_result = $fetch_query->get_result();
    $admin_data = $fetch_result->fetch_assoc();

    $emergency_contact = !empty($_POST["emergency_contact"]) ? trim($_POST["emergency_contact"]) : $admin_data["emergency_contact"];
    $support_email = !empty($_POST["support_email"]) ? trim($_POST["support_email"]) : $admin_data["support_email"];
    $communication_channel = !empty($_POST["communication_channel"]) ? trim($_POST["communication_channel"]) : $admin_data["communication_channel"];
    $docs_link = !empty($_POST["docs_link"]) ? trim($_POST["docs_link"]) : $admin_data["docs_link"];

    // Check if the admin resource entry exists
    $check_query = $conn->prepare("SELECT * FROM admin_resources WHERE user_id = ?");
    $check_query->bind_param("i", $user_id);
    $check_query->execute();
    $check_result = $check_query->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing record
        $update_query = $conn->prepare("
            UPDATE admin_resources 
            SET emergency_contact = ?, support_email = ?, communication_channel = ?, docs_link = ? 
            WHERE user_id = ?
        ");
        $update_query->bind_param("ssssi", $emergency_contact, $support_email, $communication_channel, $docs_link, $user_id);
        $update_status = $update_query->execute();
    } else {
        // Insert new record
        $insert_query = $conn->prepare("
            INSERT INTO admin_resources (user_id, emergency_contact, support_email, communication_channel, docs_link) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert_query->bind_param("issss", $user_id, $emergency_contact, $support_email, $communication_channel, $docs_link);
        $update_status = $insert_query->execute();
    }

    $_SESSION['toast_messages'] = $update_status ? ["Admin Resources updated successfully."] : ["Error updating admin resources."];
    header("Location: profile.php");
    exit();
}
