<?php
require_once(__DIR__ . "/../../database/config.php");
require_once(__DIR__ . "/../../database/functions.php");
include "../../includes/header.php";

// Ensure user is logged in and is a leader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header("Location: /pages/login.php");
    exit();
}

$leader_id = $_SESSION['user_id'];

// ✅ Fetch the leader's organization details
$query = $conn->prepare("
    SELECT o.org_id, o.name, o.description, 
           COALESCE(o.logo, 'assets/images/orgs/default-org.png') AS logo, 
           COALESCE(o.website, '') AS website, 
           COALESCE(o.facebook, '') AS facebook, 
           COALESCE(o.twitter, '') AS twitter, 
           COALESCE(o.instagram, '') AS instagram,
           u.full_name AS leader_name, u.email AS leader_email
    FROM organizations o
    LEFT JOIN users u ON o.leader_id = u.user_id
    WHERE o.leader_id = ?
");
$query->bind_param("i", $leader_id);
$query->execute();
$result = $query->get_result();
$organization = $result->fetch_assoc();

// ✅ If leader has no assigned organization, set default values
if (!$organization) {
    $organization = [
        "org_id" => null,
        "name" => "Unknown Organization",
        "description" => "No description available",
        "website" => "",
        "facebook" => "",
        "twitter" => "",
        "instagram" => "",
        "logo" => "assets/images/orgs/default-logo.jpg",
        "leader_name" => "N/A",
        "leader_email" => "N/A"
    ];
}

// ✅ Check if `org_resources` table exists
$check_table = $conn->query("SHOW TABLES LIKE 'org_resources'");
$admin_resources = ["support_email" => "", "contact_person" => "", "docs_link" => ""];

if ($check_table->num_rows > 0 && $organization['org_id']) {
    $admin_resources_query = $conn->prepare("
        SELECT support_email, contact_person, docs_link 
        FROM org_resources 
        WHERE org_id = ?
    ");
    $admin_resources_query->bind_param("i", $organization['org_id']);
    $admin_resources_query->execute();
    $admin_resources_result = $admin_resources_query->get_result();

    if ($admin_resources_result->num_rows > 0) {
        $admin_resources = $admin_resources_result->fetch_assoc();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Profile</title>
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="stylesheet" href="/assets/css/includes.css">
    <link rel="stylesheet" href="/assets/css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            <div class="container mt-4">
                <div class="table-wrapper">
                    <div class="table-title d-flex justify-content-between align-items-center">
                        <div>
                            <h1 style="color:  #a83232;"><b> Organization Profile</b></h1>
                            <p>Manage your organization's details, contacts, and resources.</p>
                        </div>
                    </div>

                    <div class="container-fluid py-4">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="wrapper-card text-center">
                                    <!-- Organization Logo with Preview -->
                                    <img id="org_logo_preview"
                                        src="<?= !empty($organization['logo']) ? htmlspecialchars($organization['logo']) : '/assets/images/orgs/default-org.png' ?>"
                                        alt="Org Logo"
                                        class="rounded-circle profile-img"
                                        style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #ddd; padding: 5px;">

                                    <h3 class="mt-3"><?= htmlspecialchars($organization['name']) ?></h3>
                                    <p class="text-muted">Managed by <?= htmlspecialchars($organization['leader_name']) ?></p>

                                    <!-- Logo Upload Form with Preview -->
                                    <form action="orgs/update_organization.php" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="org_id" value="<?= $organization['org_id'] ?>">

                                        <!-- Live Preview File Input -->
                                        <label class="fw-bold mt-2">Update Logo:</label>
                                        <input type="file" name="logo" id="org_logo_input" class="form-control mt-2" accept="image/*">

                                        <!-- Upload Button -->
                                        <button type="submit" class="btn btn-primary btn-m mt-3 w-100">
                                            <i class="fas fa-upload"></i> Upload Logo
                                        </button>
                                    </form>
                                </div>


                                <!-- Social Media Links -->
                                <div class="wrapper-card">
                                    <h5 class="mb-3">Social Links</h5>
                                    <form action="orgs/update_organization.php" method="POST">
                                        <input type="hidden" name="org_id" value="<?= $organization['org_id'] ?>">

                                        <label class="form-label"><i class="fas fa-globe me-2"></i> Website:</label>
                                        <input type="text" class="form-control" name="website"
                                            value="<?= htmlspecialchars($organization['website']) ?>">

                                        <label class="form-label"><i class="fab fa-facebook me-2"></i> Facebook:</label>
                                        <input type="text" class="form-control" name="facebook"
                                            value="<?= htmlspecialchars($organization['facebook']) ?>">

                                        <label class="form-label"><i class="fab fa-twitter me-2"></i> Twitter:</label>
                                        <input type="text" class="form-control" name="twitter"
                                            value="<?= htmlspecialchars($organization['twitter']) ?>">

                                        <label class="form-label"><i class="fab fa-instagram me-2"></i> Instagram:</label>
                                        <input type="text" class="form-control" name="instagram"
                                            value="<?= htmlspecialchars($organization['instagram']) ?>">

                                        <div class="text-center mt-3">
                                            <button type="submit" class="btn btn-success btn-sm">Update Social Links</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Right Section: Organization Info Edit -->
                            <div class="col-md-8">
                                <div class="wrapper-card">
                                    <h4>Organization Details</h4>
                                    <form action="orgs/update_organization.php" method="POST">
                                        <input type="hidden" name="org_id" value="<?= $organization['org_id'] ?>">

                                        <label class="form-label">Description:</label>
                                        <textarea class="form-control" name="description"><?= htmlspecialchars($organization['description']) ?></textarea>

                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-success">Update Organization</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Admin Resources -->
                                <div class="wrapper-card">
                                    <h5 class="mb-3">Admin Resources</h5>
                                    <form action="orgs/update_organization.php" method="POST">
                                        <input type="hidden" name="org_id" value="<?= $organization['org_id'] ?>">

                                        <label class="form-label">Support Email:</label>
                                        <input type="email" class="form-control" name="support_email"
                                            value="<?= htmlspecialchars($admin_resources['support_email']) ?>">

                                        <label class="form-label">Contact Person:</label>
                                        <input type="text" class="form-control" name="contact_person"
                                            value="<?= htmlspecialchars($admin_resources['contact_person']) ?>">

                                        <label class="form-label">Documentation Link:</label>
                                        <input type="text" class="form-control" name="docs_link"
                                            value="<?= htmlspecialchars($admin_resources['docs_link']) ?>">

                                        <div class="text-center mt-4">
                                            <button type="submit" class="btn btn-primary">Update Admin Info</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php if (isset($_SESSION['toast_messages'])): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            <?php foreach ($_SESSION['toast_messages'] as $toast): ?>
                showToast("<?= addslashes($toast['message']) ?>", "<?= $toast['type'] ?>");
            <?php endforeach; ?>
        });

        document.getElementById("org_logo_input").addEventListener("change", function(event) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById("org_logo_preview").src = e.target.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>
    <?php unset($_SESSION['toast_messages']); ?>
<?php endif; ?>