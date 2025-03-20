<?php
$role = $_SESSION["role"] ?? null;
$currentPage = basename($_SERVER['PHP_SELF']); // Get current page name
?>

<!-- Sidebar -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');

    * {
        font-family: 'Poppins', sans-serif;
    }

    .sidebar {
        position: fixed;
        background-color: #f3f3f3;
        /* Light gray background */
        width: 250px;
        min-height: 100vh;
        padding: 10px;
        color: black;
    }

    .sidebar-header {
        text-align: center;
        padding: 15px 10px;
    }

    .sidebar img {
        width: 180px;
        margin: auto;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-item {
        margin-bottom: 5px;
        font-weight: 300 !important;
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: black;
        /* Default text color */
        text-decoration: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 500;
        transition: background-color 0.3s, color 0.3s;
    }

    .sidebar-item i {
        margin-right: 10px;
        font-size: 16px;
        color: #a83232;
        /* Default icon color (red) */
        transition: color 0.3s;
    }

    /* Hover effect */
    .sidebar-item:hover {
        background-color: #E0E0E0;
        /* Light gray on hover */
        color: black;
        /* Keep text black */
    }

    .sidebar-item:hover i {
        color: #a83232;
        /* Keep icon red on hover */
    }

    /* Active effect */
    .sidebar-item.active {
        background-color: #a83232 !important;
        /* Red background for active state */
        color: white !important;
        /* White text for active state */
    }

    .sidebar-item.active i {
        color: white !important;
        /* White icon for active state */
    }
</style>


<?php if ($role == 'admin'): ?>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php">
                <img src="/assets/images/logo/adminlogo.png" alt="umunitylogo">
            </a>
        </div>

        <ul>
            <li>
                <a href="/pages/admin/dashboard.php" class="sidebar-item <?= ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>

            <li><a href="/pages/admin/manage_users.php" class="sidebar-item <?= ($currentPage == 'manage_users.php') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Manage Users</a></li>
            <li><a href="/pages/admin/manage_organizations.php" class="sidebar-item <?= ($currentPage == 'manage_organizations.php') ? 'active' : ''; ?>">
                    <i class="fas fa-home-alt"></i> Manage Organizations</a></li>
            <li><a href="/pages/admin/manage_events.php" class="sidebar-item <?= ($currentPage == 'manage_events.php') ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> Manage Events</a></li>
            <li><a href="/pages/admin/manage_reports.php" class="sidebar-item <?= ($currentPage == 'reports.php') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="admin/settings.php" class="sidebar-item <?= ($currentPage == 'settings.php') ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings</a></li>


        <?php elseif ($role == 'leader'): ?>
            <div class="sidebar">
                <div class="sidebar-header">
                    <a href="/pages/leader/dashboard.php">
                        <img src="/assets/images/logo/officerlogo.png" alt="umunitylogo">
                    </a>
                </div>

                <ul>
                    <li>
                        <a href="/pages/leader/dashboard.php" class="sidebar-item <?= ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>

                    <li><a href="/pages/leader/manage_members.php" class="sidebar-item <?= ($currentPage == 'manage_members.php') ? 'active' : ''; ?>">
                            <i class="fas fa-user-friends"></i>Manage Members</a></li>
                    <li><a href="/pages/leader/create_event.php" class="sidebar-item <?= ($currentPage == 'create_event.php') ? 'active' : ''; ?>">
                            <i class="fas fa-plus-circle"></i>Manage Events</a></li>
                    <li><a href="/pages/leader/my_organization.php" class="sidebar-item <?= ($currentPage == 'my_organization.php') ? 'active' : ''; ?>">
                            <i class="fas fa-home-alt"></i>My Organization</a></li>


                <?php elseif ($role == 'student'): ?>
                    <div class="sidebar">
                        <div class="sidebar-header">
                            <a href="/pages/student/dashboard.php">
                                <img src="/assets/images/logo/studentlogo.png" alt="umunitylogo">
                            </a>
                        </div>

                        <ul>
                            <li>
                                <a href="/pages/student/dashboard.php" class="sidebar-item <?= ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard</a>
                            </li>
                            <li><a href="/pages/student/student/browse_organizations.php" class="sidebar-item <?= ($currentPage == 'browse_orgs.php') ? 'active' : ''; ?>">
                                    <i class="fas fa-search"></i> Browse Organizations</a></li>
                            <li><a href="/pages/student/student/my_memberships.php" class="sidebar-item <?= ($currentPage == 'rsvp_events.php') ? 'active' : ''; ?>">
                                    <i class="fas fa-calendar-check"></i>My Memberships</a></li>
                            <li><a href="/pages/student/student/register_organization.php" class="sidebar-item <?= ($currentPage == 'rsvp_events.php') ? 'active' : ''; ?>">
                                    <i class="fas fa-calendar-check"></i>Register Organization</a></li>

                        <?php endif; ?>

                        <li>
                            <a href="../logout.php" class="sidebar-item">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                        </ul>

                    </div>