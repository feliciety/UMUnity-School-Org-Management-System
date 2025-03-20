<?php
require_once(__DIR__ . "/../../database/config.php");
require_once(__DIR__ . "/../../database/functions.php");
include "../../includes/header.php";

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the session is properly set and user is a leader
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'leader') {
    header("Location: /pages/login.php");
    exit();
}

// Validate Database Connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get Leader ID
$leader_id = $_SESSION['user_id'];
$organization = get_leader_organization($leader_id, $conn);

if (!$organization) {
    die("Error: No organization assigned.");
}

$org_id = $organization['org_id']; // Organization ID

// Fetch all members in the leader's organization
$members = get_org_members($org_id, $conn);
$total_members = count($members);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Management</title>

    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/includes.css">
    <link rel="stylesheet" href="/assets/css/table.css">
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Google Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <!-- Bootstrap 5 -->
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
                <div class="table-responsive">
                    <div class="table-wrapper">
                        <div class="table-title d-flex justify-content-between align-items-center">
                            <div>
                                <h1 style="color: #a83232;"><b> Member Management</b></h1>
                                <p>Oversee and manage members in <strong><?php echo htmlspecialchars($organization['name']); ?></strong>.</p>
                            </div>
                            <div class="col-md-3">
                                <div class="info-card shadow-sm border rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="text-muted mb-1">Total Members</h6>
                                        <div class="icon-circle bg-success text-white">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                    <h2 class="fw-bold mb-1" id="totalMembersCount"><?= $total_members ?></h2>
                                </div>
                            </div>
                        </div>

                        <div class="wrapper-card pagination-container d-flex justify-content-between">
                            <div>
                                <label for="rowsPerPageSelect">Show:</label>
                                <select id="rowsPerPageSelect" class="form-select w-auto">
                                    <option value="5">5 entries</option>
                                    <option value="10" selected>10 entries</option>
                                    <option value="15">15 entries</option>
                                    <option value="20">20 entries</option>
                                </select>
                            </div>
                            <div class="d-flex gap-3">
                                <input type="text" id="searchInput" class="form-control w-auto" placeholder="Search members...">
                            </div>
                        </div>

                        <div class="wrapper-card mt-4">
                            <div class="table-container">
                                <table class="table table-striped table-hover" id="membersTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">ID</th>
                                            <th class="text-center">Name</th>
                                            <th class="text-center">Email</th>
                                            <th class="text-center">Role</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($members as $member): ?>
                                            <tr id="member-<?= $member['membership_id'] ?>">
                                                <td class="text-center"><?= $member['membership_id'] ?></td>
                                                <td><?= htmlspecialchars($member['full_name']) ?></td>
                                                <td><?= htmlspecialchars($member['email']) ?></td>
                                                <td class="text-center">
                                                    <select class="role-select form-select w-auto" data-id="<?= $member['membership_id'] ?>">
                                                        <option value="member" <?= ($member['role'] == 'member') ? 'selected' : '' ?>>Member</option>
                                                        <option value="officer" <?= ($member['role'] == 'officer') ? 'selected' : '' ?>>Officer</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-<?= ($member['status'] == 'approved') ? 'success' : 'warning' ?>">
                                                        <?= ucfirst($member['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($member['status'] == 'pending'): ?>
                                                        <button class="btn btn-m p-2 text-success approve-btn" data-id="<?= $member['membership_id'] ?>">
                                                            <i class="fas fa-check-circle fa-lg"></i>
                                                        </button>
                                                        <button class="btn btn-m p-2 text-danger reject-btn" data-id="<?= $member['membership_id'] ?>">
                                                            <i class="fas fa-times-circle fa-lg"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-m p-2 text-warning remove-btn" data-id="<?= $member['membership_id'] ?>">
                                                        <i class="fas fa-user-slash fa-lg"></i>
                                                    </button>
                                                </td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="text-muted">Showing <b id="currentEntries">0</b> out of <b id="totalEntries">0</b> entries</span>
                            <nav>
                                <ul class="pagination pagination-sm mb-0" id="paginationControls"></ul>
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery & Bootstrap Bundle -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            let rowsPerPage = parseInt($("#rowsPerPageSelect").val());
            let currentPage = 1;
            let rows = $("#membersTable tbody tr");
            let filteredRows = rows; // Holds search results

            function showPage(page, rowsPerPage) {
                rows.hide(); // Hide all rows
                filteredRows.slice((page - 1) * rowsPerPage, page * rowsPerPage).show(); // Show selected page rows
                $(".pagination button").removeClass("active");
                $(`.pagination button[data-page=${page}]`).addClass("active");
            }

            function generatePagination(rowsPerPage) {
                let totalRows = filteredRows.length;
                let totalPages = Math.ceil(totalRows / rowsPerPage);
                $("#paginationControls").html("");

                if (totalPages <= 1) return;

                for (let i = 1; i <= totalPages; i++) {
                    $("#paginationControls").append(
                        `<button class="btn btn-sm btn-outline-primary ${i === 1 ? 'active' : ''}" data-page="${i}">${i}</button>`
                    );
                }
            }

            // Initial Setup
            generatePagination(rowsPerPage);
            showPage(currentPage, rowsPerPage);

            // Change Page on Button Click
            $(document).on("click", ".pagination button", function() {
                currentPage = $(this).data("page");
                showPage(currentPage, rowsPerPage);
            });

            // Change Rows Per Page
            $("#rowsPerPageSelect").change(function() {
                rowsPerPage = parseInt($(this).val());
                currentPage = 1; // Reset to first page
                filteredRows = rows; // Reset filtered data when changing per page count
                generatePagination(rowsPerPage);
                showPage(currentPage, rowsPerPage);
            });

            // Live Search Filter
            $("#searchInput").on("keyup", function() {
                let value = $(this).val().toLowerCase();

                if (value === "") {
                    filteredRows = rows; // Reset filtering when search bar is empty
                    $(".pagination").show();
                } else {
                    filteredRows = rows.filter(function() {
                        return $(this).text().toLowerCase().indexOf(value) > -1;
                    });

                    $(".pagination").hide(); // Hide pagination during search
                }

                generatePagination(rowsPerPage);
                showPage(currentPage, rowsPerPage);
            });

            function updateEntryCount() {
                let totalEntries = $("#membersTable tbody tr").length; // Count all table rows
                let visibleEntries = $("#membersTable tbody tr:visible").length; // Count only visible rows

                $("#currentEntries").text(visibleEntries);
                $("#totalEntries").text(totalEntries);
            }

            // Call function on page load
            $(document).ready(function() {
                updateEntryCount();
            });


            //  Toast Notification Function
            function showToast(message, type = "success") {
                let toastClass = (type === "success") ? "bg-success text-white" : "bg-danger text-white";
                let toastHTML = `
        <div class="toast align-items-center ${toastClass} position-fixed bottom-0 end-0 p-2 m-3" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        `;
                $("body").append(toastHTML);
                let toast = new bootstrap.Toast($(".toast").last()[0]);
                toast.show();
                setTimeout(() => {
                    $(".toast").fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }

            $(".approve-btn").click(function() {
                let member_id = $(this).data("id");
                $.post("process_member.php", {
                    action: "approve",
                    member_id: member_id
                }, function() {
                    $("#member-" + member_id).fadeOut();
                });
            });

            $(".reject-btn").click(function() {
                let member_id = $(this).data("id");
                $.post("process_member.php", {
                    action: "reject",
                    member_id: member_id
                }, function() {
                    $("#member-" + member_id).fadeOut();
                });
            });

            $(".remove-btn").click(function() {
                let member_id = $(this).data("id");
                $.post("process_member.php", {
                    action: "remove",
                    member_id: member_id
                }, function() {
                    $("#member-" + member_id).fadeOut();
                });
            });

            $(".role-select").change(function() {
                let member_id = $(this).data("id");
                let new_role = $(this).val();
                $.post("process_member.php", {
                    action: "update_role",
                    member_id: member_id,
                    role: new_role
                });
            });
        });
    </script>

</body>

</html>