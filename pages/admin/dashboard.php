<?php
require_once(__DIR__ . "/../../database/config.php");
require_once(__DIR__ . "/../../database/functions.php");
include "../../includes/header.php";


// Ensure the session is properly set
if (!isset($_SESSION['user_id'])) {
    header("Location: /pages/login.php");
    exit();
}

$admin_id = $_SESSION['user_id'];
$admin = get_user_by_id($admin_id);

if (!$admin) {
    die("Error: User not found in database.");
}

// Validate Database Connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Initialize Counts
$pending_organizations = 0;
$total_organizations = 0;
$total_users = 0;
$active_users = 0;

// Get Pending Organizations Count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM organizations WHERE status = ?");
$status_pending = 'pending';
$stmt->bind_param("s", $status_pending);
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $pending_organizations = $result->fetch_assoc()['count'] ?? 0;
}
$stmt->close();

// Get Total Approved Organizations
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM organizations WHERE status = 'active'");
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $total_organizations = $result->fetch_assoc()['count'] ?? 0;
}
$stmt->close();

// Get Total Users
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users");
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $total_users = $result->fetch_assoc()['count'] ?? 0;
}
$stmt->close();

// Get Active Users Count
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $active_users = $result->fetch_assoc()['count'] ?? 0;
}
$stmt->close();

// Fetch Pending Organizations Data
$pending_orgs = get_organizations_by_status('pending');

// Fetch Recent Activities
$recent_activities = [];
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY sent_at DESC LIMIT 5");
$stmt->bind_param("i", $admin_id);
if ($stmt->execute()) {
    $recent_activities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();

// Count Unread Notifications
$unread_notifications = count_unread_notifications($admin_id);

// Fetch Organization Category Counts for Pie Chart
$category_query = "SELECT c.category_name, COUNT(*) as count 
                   FROM organizations o
                   LEFT JOIN org_categories c ON o.category_id = c.category_id
                   GROUP BY c.category_id";
$category_result = $conn->query($category_query);

$categories = [];
$category_counts = [];

while ($row = $category_result->fetch_assoc()) {
    $categories[] = $row['category_name'];
    $category_counts[] = $row['count'];
}

// Fetch User Activity (Active Users Per Month)
$user_activity_query = "SELECT DATE_FORMAT(created_at, '%b') AS month, COUNT(*) as count 
                        FROM users GROUP BY month ORDER BY created_at ASC";
$user_activity_result = $conn->query($user_activity_query);

$months = [];
$user_counts = [];

while ($row = $user_activity_result->fetch_assoc()) {
    $months[] = $row['month'];
    $user_counts[] = $row['count'];
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/includes.css">
    <link rel="stylesheet" href="/assets/css/table.css">

    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body>

<div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar">
            <?php include "../../includes/sidebar.php"; ?>
        </div>

        <!-- Main Content -->
        <div class="main-content">

            <div class="container ">
                <div class="table-responsive">
                    <div class="table-wrapper">
                        <div class="table-title d-flex justify-content-between align-items-center">
                            <div>
                                <h1 style="color:  #a83232 !important;"><b> Admin Dashboard</b></h1>
                                <p>Oversee and coordinate organizations effectively.</p>
                            </div>
                        </div>
                        <!-- Dashboard Content -->
                        <div class="container-fluid px-0">

                            <!-- Stats Cards -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-6 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="text-muted mb-1">Total Organizations</h6>
                                                    <h3 class="mb-0"><?php echo $total_organizations; ?></h3>
                                                </div>
                                                <div class="stats-card-icon bg-primary bg-opacity-10 text-primary">
                                                    <i class="fas fa-sitemap"></i>
                                                </div>
                                            </div>
                                            <div class="stats-card-trend up mt-3">
                                                <i class="fas fa-arrow-up"></i>
                                                <span>2 new this month</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm border-warning">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="text-muted mb-1">Pending Approvals</h6>
                                                    <h3 class="mb-0"><?php echo $pending_organizations; ?></h3>
                                                </div>
                                                <div class="stats-card-icon bg-warning bg-opacity-10 text-warning">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            </div>
                                            <div class="stats-card-trend up mt-3">
                                                <i class="fas fa-arrow-up"></i>
                                                <span>3 new this week</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="text-muted mb-1">Total Users</h6>
                                                    <h3 class="mb-0"><?php echo $total_users; ?></h3>
                                                </div>
                                                <div class="stats-card-icon bg-success bg-opacity-10 text-success">
                                                    <i class="fas fa-users"></i>
                                                </div>
                                            </div>
                                            <div class="stats-card-trend up mt-3">
                                                <i class="fas fa-arrow-up"></i>
                                                <span>12 new this month</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6 col-lg-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="text-muted mb-1">Active Users</h6>
                                                    <h3 class="mb-0"><?php echo $active_users; ?></h3>
                                                </div>
                                                <div class="stats-card-icon bg-info bg-opacity-10 text-info">
                                                    <i class="fas fa-user-check"></i>
                                                </div>
                                            </div>
                                            <div class="stats-card-trend down mt-3">
                                                <i class="fas fa-arrow-down"></i>
                                                <span>3% from last month</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Charts & Tables -->
                            <div class="row g-4 mb-4">
                                <!-- Organization Categories Chart -->
                                <div class="col-lg-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-transparent border-0">
                                            <h5 class="card-title">Organization Categories</h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="org-categories-chart" height="100"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <!-- User Activity Chart -->
                                <div class="col-lg-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-transparent border-0">
                                            <h5 class="card-title">User Activity</h5>
                                        </div>
                                        <div class="card-body">
                                            <canvas id="user-activity-chart" height="300"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pending Organizations & Recent Activity -->
                            <div class="row g-4">
                                <!-- Pending Organizations -->
                                <div class="col-lg-8">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
                                            <h5 class="card-title">Pending Organizations</h5>
                                            <a href="?page=organizations&filter=pending" class="btn btn-sm btn-primary">View All</a>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-hover align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Category</th>
                                                            <th>Submitted By</th>
                                                            <th>Date</th>
                                                            <th class="text-end">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php if (empty($pending_orgs)): ?>
                                                            <tr>
                                                                <td colspan="5" class="text-center py-4 text-muted">No pending organizations</td>
                                                            </tr>
                                                        <?php else: ?>
                                                            <?php foreach (array_slice($pending_orgs, 0, 5) as $org): ?>
                                                                <?php $creator = get_user_by_id($org['created_by']); ?>
                                                                <tr>
                                                                    <td>
                                                                        <div class="fw-semibold"><?php echo $org['name']; ?></div>
                                                                    </td>
                                                                    <td><span class="badge bg-secondary"><?php echo $org_categories[$org['category']]; ?></span></td>
                                                                    <td>
                                                                        <?php
                                                                        $leader = get_user_by_id($row['leader_id']);
                                                                        echo $leader ? $leader['full_name'] : 'N/A';
                                                                        ?>
                                                                    </td>

                                                                    <td><?php echo format_date($org['created_at']); ?></td>
                                                                    <td class="text-end">
                                                                        <a href="?page=organization-details&id=<?php echo $org['id']; ?>" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-eye"></i></a>
                                                                        <button type="button" class="btn btn-sm btn-success me-1"><i class="fas fa-check"></i></button>
                                                                        <button type="button" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Activity -->
                                <div class="col-lg-4">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-transparent border-0">
                                            <h5 class="card-title">Recent Activity</h5>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="list-group list-group-flush">
                                                <?php if (empty($recent_activities)): ?>
                                                    <div class="text-center py-4 text-muted">
                                                        <i class="fas fa-history fa-2x mb-3"></i>
                                                        <p>No recent activities</p>
                                                    </div>
                                                <?php else: ?>
                                                    <?php foreach ($recent_activities as $activity): ?>
                                                        <div class="list-group-item border-0 py-3">
                                                            <div class="d-flex">
                                                                <div class="flex-shrink-0">
                                                                    <div class="notification-icon <?php echo $activity['type']; ?>">
                                                                        <i class="fas fa-<?php echo $activity['type'] === 'success' ? 'check' : ($activity['type'] === 'warning' ? 'exclamation' : ($activity['type'] === 'error' ? 'times' : 'info-circle')); ?>"></i>
                                                                    </div>
                                                                </div>
                                                                <div class="flex-grow-1 ms-3">
                                                                    <h6 class="mb-1"><?php echo $activity['title']; ?></h6>
                                                                    <p class="text-muted small mb-0"><?php echo $activity['message']; ?></p>
                                                                    <small class="text-muted"><?php echo format_date($activity['created_at'], 'M d, h:i A'); ?></small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include "../../includes/footer.php"; ?>


</body>

</html>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const orgCategories = <?php echo json_encode($categories); ?>;
    const orgCategoryCounts = <?php echo json_encode($category_counts); ?>;

    // Check if the element exists before rendering
    const orgCategoriesChart = document.getElementById('org-categories-chart');
    if (orgCategoriesChart) {
        new Chart(orgCategoriesChart, {
            type: 'pie',
            data: {
                labels: orgCategories,
                datasets: [{
                    data: orgCategoryCounts,
                    backgroundColor: [
                        '#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6', '#6b7280',
                        '#14b8a6', '#db2777', '#facc15', '#6366f1'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // Convert PHP user activity data to JavaScript
    const userMonths = <?php echo json_encode($months); ?>;
    const activeUsers = <?php echo json_encode($user_counts); ?>;

    // Ensure the chart only runs if the element exists
    const userActivityChart = document.getElementById('user-activity-chart');
    if (userActivityChart) {
        new Chart(userActivityChart, {
            type: 'line',
            data: {
                labels: userMonths,
                datasets: [{
                    label: 'Active Users',
                    data: activeUsers,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
</script>