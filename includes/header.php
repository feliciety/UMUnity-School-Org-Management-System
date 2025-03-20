<?php
session_start();
require_once(__DIR__ . "/../database/config.php");
require_once(__DIR__ . "/../database/functions.php");

// Redirect to login if not authenticated
if (!isset($_SESSION["user_id"])) {
    header("Location: /pages/login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user details from the database
$query = $conn->prepare("SELECT full_name, role_id, profile_pic FROM users WHERE user_id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Check if user data is retrieved
if (!$user) {
    die("Error: User data not found. Please check the database.");
}

// Set user details or fallback values
$user_name = !empty($user['full_name']) ? htmlspecialchars($user['full_name']) : 'Unknown User';
$role_id = $user['role_id'] ?? null;

// Profile Image Handling
$default_image = "/assets/images/profile/default-user.png";
$profile_image = (!empty($user['profile_pic']) && file_exists(__DIR__ . "/../" . $user['profile_pic']))
    ? "/" . htmlspecialchars($user['profile_pic'])
    : $default_image;

// Assign role names based on `role_id`
$role_names = [
    1 => 'Admin',
    2 => 'Leader',
    3 => 'Student'
];
$role = $role_names[$role_id] ?? 'Guest';

// Fetch unread notifications count
$unread_notifications = count_unread_notifications($user_id);
$recent_notifications = get_recent_notifications($user_id, 5);
?>




<div class="main-header">
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">

            <!-- Universal Search Bar -->
            <div class="nav-search">
                <input type="text" class="search-input" placeholder="Search...">
            </div>

            <!-- Right Side Elements -->
            <div class="right-nav">
                <!-- Notifications Dropdown -->
                <div class="dropdown me-3">
                    <button class="btn btn-light position-relative notification-btn" type="button" data-bs-toggle="dropdown">
                        <span class="material-icons-outlined">notifications</span> <!-- Google Icon -->
                        <?php if ($unread_notifications > 0): ?>
                            <span class="badge rounded-pill bg-danger">
                                <?php echo $unread_notifications; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0">
                        <div class="p-3 border-bottom d-flex justify-content-between">
                            <h6 class="mb-0 text-white">Notifications</h6>
                            <?php if ($unread_notifications > 0): ?>
                                <a href="?page=notifications" class="small text-white">Mark all as read</a>
                            <?php endif; ?>
                        </div>
                        <div class="notification-list">
                            <?php if (empty($recent_notifications)): ?>
                                <div class="p-4 text-center text-white">
                                    <span class="material-icons-outlined" style="font-size: 36px;">notifications_off</span>
                                    <p>No new notifications</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_notifications as $notif): ?>
                                    <div class="notification-item <?php echo $notif['is_read'] ? '' : 'unread'; ?>">
                                        <div class="d-flex">
                                            <div class="ms-3">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                                <p class="small mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                                                <small><?php echo format_date($notif['created_at'], 'M d, h:i A'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                        <div class="p-3 border-top text-center">
                            <a href="?page=notifications" class="btn view-all-btn">View All Notifications</a>
                        </div>
                    </div>
                </div>

                <!-- User Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light profile-dropdown dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <?php
                        // Fetch user's latest profile picture or fallback to default
                        $profile_pic = (!empty($user['profile_pic']) && file_exists(__DIR__ . "/../" . $user['profile_pic']))
                            ? "/" . htmlspecialchars($user['profile_pic'])
                            : "/assets/images/profile/default-user.png";

                        // Ensure the user's name is always displayed
                        $user_name = !empty($user['full_name']) ? htmlspecialchars($user['full_name']) : "Unknown User";
                        ?>

                        <img src="<?= $profile_pic ?>" alt="Profile Picture" class="profile-dropdown-img me-2">
                        <span class="profile-text"><?= $user_name; ?></span>
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="../profile/profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>

            </div>
        </div>
    </nav>
</div>


<style>
    @import url('https://fonts.googleapis.com/css2?family=Material+Icons+Outlined');

    /* Make navbar fixed at the top */
    .main-header {
        background: #f3f3f3 !important;
        height: 70px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        padding: 15px 20px;
        position: fixed;
        top: 0;
        left: 250px;
        /* Adjust based on sidebar width */
        width: calc(100% - 250px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .profile-dropdown-img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }


    /* Universal Search Bar */
    .nav-search {
        flex-grow: 1;
        margin: 0 20px;
        position: relative;
        max-width: 800px;
    }

    .search-input {
        width: 100%;
        padding: 10px 15px;
        border-radius: 30px;
        border: 2px solid #fff;
        outline: none;
        color: #111;
        transition: all 0.3s ease-in-out;
    }

    .search-input::placeholder {
        color: rgba(17, 17, 17, 0.8);
    }

    .search-input:focus {
        border-color: #a83232;
    }

    /* Right side notification & profile */
    .right-nav {
        display: flex;
        align-items: center;
        gap: 25px;
        margin-left: auto;
    }

    /* Bigger Notification Icon */
    .notification-btn {
        font-size: 30px;
        /* Increase icon size */
        padding: 12px 18px;
        position: relative;
        background: transparent !important;
        border: none !important;
        color: #a83232 !important;
        transition: all 0.2s ease-in-out;
    }

    .notification-btn:hover {
        background: rgba(168, 50, 50, 0.1) !important;
        border-radius: 10px;
    }

    /* Notification Badge */
    .notification-btn .badge {
        font-size: 16px;
        padding: 8px 12px;
        position: absolute;
        top: 5px;
        right: 5px;
    }

    /* Profile Dropdown */
    .profile-dropdown {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        background: transparent !important;
        border: none !important;
        color: #a83232 !important;
    }

    .profile-dropdown .fas {
        margin-top: 10px;
        font-size: 30px;
        color: #a83232;
        transition: all 0.3s ease-in-out;
    }

    .profile-text {
        color: #a83232 !important;

    }

    .profile-dropdown:hover .fas {
        color: #d9534f;
        /* Slight hover color change */
    }

    /* Dropdown menu */
    .dropdown-menu {
        background: #a83232 !important;
        border-radius: 8px;
        box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.3);
        border: none;
    }

    .dropdown-menu a {
        color: #fff !important;
        padding: 10px 15px;
        transition: all 0.3s ease-in-out;
    }

    .dropdown-menu a:hover {
        background: rgba(255, 255, 255, 0.2) !important;
    }


    /* Notification Dropdown */
    .notification-dropdown {
        min-width: 350px;
        background: #a83232 !important;
        border-radius: 8px;
        box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.3);
        border: none;
        animation: fadeIn 0.3s ease-in-out;
    }

    .notification-dropdown h6 {
        color: #fff;
    }

    .notification-item {
        padding: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: #fff;
    }

    .notification-item.unread {
        background: rgba(255, 255, 255, 0.1);
    }

    /* Make "View All Notifications" Button Bigger */
    .view-all-btn {
        font-size: 30px !important;
        /* Increase font size */
        padding: 12px 20px !important;
        /* Increase padding */
        width: 100% !important;
        /* Make button full width */
        text-align: center !important;
        /* Center text */
        font-weight: bold !important;
        /* Make text bold */
        background-color: #fff !important;
        /* Ensure visibility */
        color: #a83232 !important;
        /* Match theme color */
        border-radius: 8px !important;
        /* Rounded edges */
        transition: all 0.3s ease-in-out;
    }

    .view-all-btn:hover {
        background: rgba(255, 255, 255, 0.2) !important;
        /* Slight hover effect */
        color: white !important;
    }

    /* Fade-in animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>