<?php
session_start();
include '../../database/config.php';

// Ensure form submission via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Fetch existing profile data BEFORE making any changes
    $fetch_query = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $fetch_query->bind_param("i", $user_id);
    $fetch_query->execute();
    $fetch_result = $fetch_query->get_result();
    $user_data = $fetch_result->fetch_assoc();

    // Store old values to prevent reset
    $full_name = !empty($_POST["full_name"]) ? trim($_POST["full_name"]) : $user_data["full_name"];
    $email = !empty($_POST["email"]) ? trim($_POST["email"]) : $user_data["email"];
    $phone = !empty($_POST["phone"]) ? trim($_POST["phone"]) : $user_data["phone"];
    $address = !empty($_POST["address"]) ? trim($_POST["address"]) : $user_data["address"];
    $gender = !empty($_POST["gender"]) ? trim($_POST["gender"]) : $user_data["gender"];
    $birthdate = !empty($_POST["birthdate"]) ? trim($_POST["birthdate"]) : $user_data["birthdate"];
    $bio = !empty($_POST["bio"]) ? trim($_POST["bio"]) : $user_data["bio"];

    // Store old profile picture path
    $old_profile_pic = $user_data['profile_pic'];
    $upload_dir = "../../assets/images/profile/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $profile_updated = false;
    $profile_pic_path = $old_profile_pic; // Keep old profile picture if not updating

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $file_name = basename($_FILES["profile_pic"]["name"]);
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_types)) {
            $profile_pic_name = "user_{$user_id}_" . time() . "." . $file_ext;
            $target_file = $upload_dir . $profile_pic_name;
            $profile_pic_path = "assets/images/profile/" . $profile_pic_name;

            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                if ($old_profile_pic && $old_profile_pic !== "assets/images/profile/default.jpg") {
                    $old_file_path = "../../" . $old_profile_pic;
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }
                }
                $profile_updated = true;
            }
        }
    }

    // Update profile in database
    $stmt = $conn->prepare("
        UPDATE users 
        SET full_name = ?, email = ?, phone = ?, address = ?, gender = ?, birthdate = ?, bio = ?, profile_pic = ?
        WHERE user_id = ?
    ");
    $stmt->bind_param("ssssssssi", $full_name, $email, $phone, $address, $gender, $birthdate, $bio, $profile_pic_path, $user_id);
    $profile_details_updated = $stmt->execute();

    $toast_messages = [];
    if ($profile_updated) {
        $toast_messages[] = "Profile picture updated successfully.";
    }
    if ($profile_details_updated) {
        $toast_messages[] = "Profile details updated successfully.";
    }
    if (!$profile_updated && !$profile_details_updated) {
        $toast_messages[] = "No changes were made.";
    }

    $_SESSION['toast_messages'] = $toast_messages;
    header("Location: profile.php");
    exit();
}
