<?php
require_once(__DIR__ . "/../../database/config.php");
require_once(__DIR__ . "/../../database/functions.php");
include "../../includes/header.php";

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /pages/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$query = $conn->prepare("
    SELECT u.*, r.name AS role_name, o.name AS org_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.role_id
    LEFT JOIN organizations o ON u.org_id = o.org_id
    WHERE u.user_id = ?
");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Ensure user data is always set with fallback values
if (!$user) {
    $user = [
        "user_id" => $user_id,
        "full_name" => "Unknown User",
        "email" => "",
        "phone" => "",
        "address" => "",
        "gender" => "",
        "birthdate" => "",
        "bio" => "",
        "profile_pic" => "assets/images/profile/default.jpg",
        "role_name" => "Guest",
        "org_name" => "No Organization"
    ];
}

// Fetch admin resource details (Ensure the admin_resources table is checked)
$admin_resources_query = $conn->prepare("SELECT * FROM admin_resources WHERE user_id = ?");
$admin_resources_query->bind_param("i", $user_id);
$admin_resources_query->execute();
$admin_resources_result = $admin_resources_query->get_result();
$admin_resources = $admin_resources_result->fetch_assoc() ?? [
    "emergency_contact" => "",
    "support_email" => "",
    "communication_channel" => "",
    "docs_link" => ""
];

// Fetch user's social links
$social_links_query = $conn->prepare("SELECT platform, link FROM user_social_links WHERE user_id = ?");
$social_links_query->bind_param("i", $user_id);
$social_links_query->execute();
$social_links_result = $social_links_query->get_result();

$social_links = [];
while ($row = $social_links_result->fetch_assoc()) {
    $social_links[$row['platform']] = $row['link'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="stylesheet" href="/assets/css/includes.css">
    <link rel="stylesheet" href="/assets/css/table.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>

    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <?php include "../../includes/sidebar.php"; ?>
        </div>

        <!-- Main Content -->
        <div class="main-content">

            <div class="container mt-4">
                <div class="table-responsive">
                    <div class="table-wrapper">
                        <div class="table-title d-flex justify-content-between align-items-center">
                            <div>
                                <h1 style="color:  #a83232 !important;"><b> User Management</b></h1>
                                <p>Efficiently oversee user accounts, roles, and statuses.</p>
                            </div>
                        </div>

                        <div class="container-fluid py-4">
                            <div class="row">
                                <!-- Left Section: Profile Info -->
                                <div class="col-md-4">
                                    <div class="wrapper-card text-center">
                                        <img src="/<?= htmlspecialchars($user['profile_pic']) ?>"
                                            alt="Profile Picture" class="rounded-circle profile-img" style="width: 120px; height: 120px; object-fit: cover;">
                                        <h3 class="mt-3"><?= htmlspecialchars($user['full_name']) ?></h3>
                                        <p class="text-muted"><?= htmlspecialchars($user['role_name']) ?></p>

                                        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                                            <input type="file" name="profile_pic" class="form-control mt-2" accept="image/*" required>
                                            <button type="submit" class="btn btn-primary btn-m mt-2">Upload</button>
                                        </form>
                                    </div>

                                    <!-- Social Links -->
                                    <?php if ($role === 'student' || $role === 'leader'): ?>
                                        <div class="wrapper-card">
                                            <h5 class="mb-3">Social Links</h5>
                                            <form action="/profile/update_profile.php" method="POST">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

                                                <?php
                                                $platforms = ['website', 'github', 'twitter', 'instagram', 'facebook'];
                                                foreach ($platforms as $platform):
                                                ?>
                                                    <label class="form-label"><i class="fab fa-<?= $platform ?> me-2"></i> <?= ucfirst($platform) ?>:</label>
                                                    <input type="text" class="form-control" name="<?= $platform ?>"
                                                        value="<?= htmlspecialchars($social_links[$platform] ?? '') ?>"
                                                        placeholder="Enter <?= ucfirst($platform) ?> link">
                                                <?php endforeach; ?>

                                                <div class="text-center mt-3">
                                                    <button type="submit" class="btn btn-success btn-sm">Update Social Links</button>
                                                </div>
                                            </form>
                                        </div>
                                    <?php endif; ?>


                                    <!-- Admin Contact and Resources -->
                                    <?php if ($role === 'admin'): ?>
                                        <div class="wrapper-card">
                                            <h5 class="mb-3">Admin Contact & Resources</h5>
                                            <form action="update_resource_admin.php" method="POST">
                                                <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">

                                                <!-- Emergency Contact -->
                                                <label class="form-label mt-4"><i class="fas fa-phone me-2"></i> Emergency Contact:</label>
                                                <input type="text" class="form-control" name="emergency_contact"
                                                    value="<?= isset($admin_resources['emergency_contact']) ? htmlspecialchars($admin_resources['emergency_contact']) : '' ?>"
                                                    placeholder="Enter Emergency Contact" required>

                                                <!-- IT Support Email -->
                                                <label class="form-label mt-4"><i class="fas fa-envelope me-2"></i> IT Support Email:</label>
                                                <input type="email" class="form-control" name="support_email"
                                                    value="<?= isset($admin_resources['support_email']) ? htmlspecialchars($admin_resources['support_email']) : '' ?>"
                                                    placeholder="Enter IT Support Email" required>

                                                <!-- Team Communication Channel -->
                                                <label class="form-label mt-4"><i class="fas fa-comments me-2"></i> Internal Communication:</label>
                                                <input type="text" class="form-control" name="communication_channel"
                                                    value="<?= isset($admin_resources['communication_channel']) ? htmlspecialchars($admin_resources['communication_channel']) : '' ?>"
                                                    placeholder="Slack, Teams, or Discord Link">

                                                <!-- Documentation Link -->
                                                <label class="form-label mt-4"><i class="fas fa-book me-2"></i> Admin Documentation:</label>
                                                <input type="text" class="form-control" name="docs_link"
                                                    value="<?= isset($admin_resources['docs_link']) ? htmlspecialchars($admin_resources['docs_link']) : '' ?>"
                                                    placeholder="Enter Documentation Link">

                                                <div class="text-center mt-4">
                                                    <button type="submit" class="btn btn-primary">Update Admin Info</button>
                                                </div>
                                            </form>
                                        </div>


                                    <?php endif; ?>


                                </div>


                                <!--  Profile Info Edit -->
                                <div class="col-md-8">
                                    <div class="wrapper-card">
                                        <h4>Profile Details</h4>
                                        <form action="update_profile.php" method="POST">
                                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Full Name:</label>
                                                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Email:</label>
                                                    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                                </div>
                                            </div>

                                            <div class="row mt-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Phone:</label>
                                                    <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Address:</label>
                                                    <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user['address']) ?>">
                                                </div>
                                            </div>

                                            <div class="row mt-2">
                                                <div class="col-md-6">
                                                    <label class="form-label">Gender:</label>
                                                    <select name="gender" class="form-select">
                                                        <option value="male" <?= ($user['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                                                        <option value="female" <?= ($user['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Birthdate:</label>
                                                    <input type="date" class="form-control" name="birthdate" value="<?= htmlspecialchars($user['birthdate']) ?>">
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <label class="form-label">Bio:</label>
                                                <textarea class="form-control" name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>
                                            </div>

                                            <div class="text-center mt-4">
                                                <button type="submit" class="btn btn-success">Update Profile</button>
                                            </div>
                                        </form>
                                    </div>


                                    <?php if ($user['role_name'] === 'admin'): ?>

                                        <!-- change password -->
                                        <div class="wrapper-card">
                                            <h5 class="mb-3">Change Password</h5>
                                            <form action="update_password.php" method="POST">
                                                <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

                                                <div class="mb-4">
                                                    <label class="form-label">Current Password:</label>
                                                    <input type="password" name="current_password" class="form-control" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">New Password:</label>
                                                    <input type="password" name="new_password" class="form-control" required>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Confirm New Password:</label>
                                                    <input type="password" name="confirm_password" class="form-control" required>
                                                </div>

                                                <button type="submit" class="btn btn-primary">Update Password</button>
                                            </form>

                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Toast Notification Container -->
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;"></div>

    <script>
        function showToast(message, type = "success") {
            let toastClass = (type === "success") ? "bg-success text-white" : "bg-danger text-white";
            let toastHTML = `
            <div class="toast align-items-center ${toastClass} position-fixed bottom-0 end-0 p-2 m-3" 
                role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" 
                        data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
            document.getElementById("toast-container").innerHTML = toastHTML;

            let toastElement = document.querySelector(".toast");
            let toast = new bootstrap.Toast(toastElement);
            toast.show();
        }

        // Show Toast Messages from Session
        document.addEventListener("DOMContentLoaded", function() {
            console.log("DOMContentLoaded: Checking session messages...");

            <?php if (isset($_SESSION['toast_messages'])): ?>
                console.log("Session messages found.");
                setTimeout(() => {
                    <?php foreach ($_SESSION['toast_messages'] as $message): ?>
                        console.log("Showing toast: <?= addslashes($message) ?>");
                        showToast("<?= addslashes($message) ?>", "success");
                    <?php endforeach; ?>
                }, 500); // Delay to allow DOM to load
                <?php unset($_SESSION['toast_messages']); ?>
            <?php else: ?>
                console.log("No session messages found.");
            <?php endif; ?>
        });
    </script>

</body>

</html>