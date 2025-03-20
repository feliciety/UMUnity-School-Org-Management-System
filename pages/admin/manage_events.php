<?php
require_once(__DIR__ . "/../../database/config.php");
include "../../includes/header.php";

// Ensure only admins can access
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("Location: /pages/login.php");
    exit();
}

// Get Total Events
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM events");
if ($stmt->execute()) {
    $result = $stmt->get_result();
    $total_events = $result->fetch_assoc()['count'] ?? 0;
}

// ✅ Updated SQL Query to ensure all events are included
$sql = "SELECT e.event_id, e.title, e.description, e.date_time, 
               e.venue, e.capacity, 
               COALESCE(es.status, 'Pending') AS status, 
               COALESCE(o.name, 'N/A') AS organization
        FROM events e
        LEFT JOIN event_status es ON e.event_id = es.event_id
        LEFT JOIN organizations o ON e.org_id = o.org_id 
        ORDER BY e.date_time DESC";

$result = $conn->query($sql);

// Debugging: Check if query retrieves data
if (!$result) {
    die("SQL Error: " . $conn->error);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>

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
                                <h1 style="color: #a83232 !important;"><b>Event Management</b></h1>
                                <p>Efficiently oversee event details, scheduling, and approvals.</p>
                            </div>
                            <div class="col-md-3">
                                <div class="info-card shadow-sm border rounded-3 p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="text-muted mb-1">Total Events</h6>
                                        <div class="icon-circle bg-success text-white">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                    </div>
                                    <h2 class="fw-bold mb-1"><?= $total_events ?></h2>
                                    <span class="text-success small">
                                        <i class="fas fa-arrow-up"></i> 2 new this month
                                    </span>
                                </div>
                            </div>

                        </div>

                        <div class=" wrapper-card pagination-container  justify-content-between">
                            <div class=" d-flex gap-3 ">
                                <label for="rowsPerPageSelect" class="mt-2 ">Show:</label>
                                <select id="rowsPerPageSelect" class="form-select w-auto">
                                    <option value="5">5 entries</option>
                                    <option value="10" selected>10 entries</option>
                                    <option value="15">15 entries</option>
                                    <option value="20">20 entries</option>
                                </select>
                            </div>
                            <div class="d-flex gap-3"><input type="text" id="searchInput" class="form-control w-auto" placeholder="Search events...">
                                <button class="btn  d-flex align-items-center px-3" data-bs-toggle="modal" data-bs-target="#addEventModal" id="addEventBtn">
                                    <span class="material-icons-outlined" style="font-size: 20px; line-height: 1;">add</span>
                                    <span class="ms-1">Add Event</span>
                                </button>
                            </div>
                        </div>

                        <div class="wrapper-card mt-4">
                            <div class="table-container">
                                <table class="table table-striped table-hover mt-3" id="eventTable" style="max-height: 500px;">
                                    <colgroup>
                                        <col style="width: 20%;"> <!-- Event Name -->
                                        <col style="width: 10%;"> <!-- Date & Time -->
                                        <col style="width: 15%;"> <!-- Venue -->
                                        <col style="width: 20%;"> <!-- Organization -->
                                        <col style="width: 10%;"> <!-- Status -->
                                        <col style="width: 25%;"> <!-- Actions -->
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th class="text-center">Event</th>
                                            <th class="text-center">Date & Time</th>
                                            <th class="text-center">Venue</th>
                                            <th class="text-center">Organization</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="text-center"><?= htmlspecialchars($row['title']) ?></td>
                                                <td class="text-center"><?= date("M d, Y h:i A", strtotime($row['date_time'])) ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row['venue']) ?></td>
                                                <td class="text-center"><?= htmlspecialchars($row['organization']) ?: 'N/A' ?></td>
                                                <td class="text-center">
                                                    <span class="badge bg-<?= ($row['status'] == 'approved') ? 'success' : 'secondary' ?>">
                                                        <?= ucfirst($row['status']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <!-- View Event -->
                                                    <button class="btn btn-m p-2 text-warning viewEvent" data-id="<?= $row['event_id'] ?>" data-bs-toggle="modal" data-bs-target="#viewEventModal">
                                                        <i class="fas fa-info-circle fa-lg"></i>
                                                    </button>
                                                    <!-- Edit Event -->
                                                    <button class="btn btn-m p-2 text-primary editEvent" data-id="<?= $row['event_id'] ?>" data-bs-toggle="modal" data-bs-target="#editEventModal">
                                                        <i class="fas fa-edit fa-lg"></i>
                                                    </button>

                                                    <button class="btn btn-m p-2 text-success approveEvent" data-id="<?= $row['event_id'] ?>" data-bs-toggle="modal" data-bs-target="#approveEventModal">
                                                        <i class="fas fa-check-circle fa-lg"></i>
                                                    </button>
                                                    <button class="btn btn-m p-2 text-secondary disapproveEvent" data-id="<?= $row['event_id'] ?>" data-bs-toggle="modal" data-bs-target="#disapproveEventModal">
                                                        <i class="fas fa-times-circle fa-lg"></i>
                                                    </button>
                                                    <!-- Delete Event -->
                                                    <button class="btn btn-m p-2 text-danger deleteEvent" data-id="<?= $row['event_id'] ?>">
                                                        <i class="fas fa-trash-alt fa-lg"></i>
                                                    </button>
                                                </td>

                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
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
        </div>
    </div>
    <!-- View Event Modal -->
    <div id="viewEventModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h4 class="modal-title">Event Details</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center"><i class="fas fa-calendar-alt text-info fa-3x mb-3"></i>
                        <h5 id="viewEventTitle" class="fw-bold mb-3"></h5>
                        <p id="viewEventDescription" class="text-muted mb-3"></p>
                    </div>
                    <p class="mb-3"><strong>Date & Time:</strong> <span id="viewEventDate"></span></p>
                    <p class="mb-3"><strong>Venue:</strong> <span id="viewEventVenue"></span></p>
                    <p class="mb-3"><strong>Capacity:</strong> <span id="viewEventCapacity"></span></p>
                    <p class="mb-3"><strong>Organization:</strong> <span id="viewEventOrganization"></span></p>
                    <p class="mb-3"><strong>Status:</strong> <span id="viewEventStatus" class="badge"></span></p>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Event Modal -->
    <div id="editEventModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h4 class="modal-title">Edit Event</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" name="event_id" id="edit_event_id">

                        <label class="mb-1">Title:</label>
                        <input type="text" class="form-control mb-3" name="title" id="edit_title" required>

                        <label class="mb-1">Description:</label>
                        <textarea class="form-control mb-3" name="description" id="edit_description" required></textarea>

                        <label class="mb-1">Date & Time:</label>
                        <input type="datetime-local" class="form-control mb-3" name="date_time" id="edit_date_time" required>

                        <label class="mb-1">Venue:</label>
                        <input type="text" class="form-control mb-3" name="venue" id="edit_venue" required>

                        <label class="mb-1">Capacity:</label>
                        <input type="number" class="form-control mb-3" name="capacity" id="edit_capacity" min="1" required>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Event Modal -->
    <div id="deleteEventModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title">Delete Event</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-trash-alt text-danger fa-3x mb-3"></i>
                    <p>Are you sure you want to <b>delete</b> this event?</p>
                    <input type="hidden" id="delete_event_id">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteEvent">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Event Modal -->
    <div id="addEventModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h4 class="modal-title">Add Event</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center">
                        <i class="fas fa-calendar    text-success fa-3x mb-3"></i>
                        <p>Fill in the details below to <b>add a new event</b>.</p>
                    </div>
                    <form id="addEventForm">
                        <label class="mb-1">Title:</label>
                        <input type="text" class="form-control mb-3" name="title" required>

                        <label class="mb-1">Description:</label>
                        <textarea class="form-control mb-3" name="description" required></textarea>

                        <label class="mb-1">Date & Time:</label>
                        <input type="datetime-local" class="form-control mb-3" name="date_time" required>

                        <label class="mb-1">Venue:</label>
                        <input type="text" class="form-control mb-3" name="venue" required>

                        <label class="mb-1">Capacity:</label>
                        <input type="number" class="form-control mb-3" name="capacity">

                        <label class="mb-1">Organization:</label>
                        <select class="form-control mb-3" name="org_id">
                            <option value="">Select Organization</option>
                            <?php
                            $orgQuery = $conn->query("SELECT org_id, name FROM organizations WHERE status='active'");
                            while ($org = $orgQuery->fetch_assoc()) {
                                echo "<option value='{$org['org_id']}'>{$org['name']}</option>";
                            }
                            ?>
                        </select>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Event Modal -->
    <div id="approveEventModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h4 class="modal-title">Approve Event</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <p>Are you sure you want to <b>approve</b> this event?</p>
                    <input type="hidden" id="approve_event_id">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmApproveEvent">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Event Modal -->
    <div id="rejectEventModal" class="modal fade" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h4 class="modal-title">Reject Event</h4>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-times-circle text-danger fa-3x mb-3"></i>
                    <p>Are you sure you want to <b>reject</b> this event?</p>
                    <input type="hidden" id="reject_event_id">
                    <label class="mb-1">Reason for Rejection:</label>
                    <textarea class="form-control mb-3" id="reject_reason" rows="3" required></textarea>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRejectEvent">
                        <i class="fas fa-times"></i> Reject
                    </button>
                </div>
            </div>
        </div>
    </div>



    <?php include "../../includes/footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            let rowsPerPage = parseInt($("#rowsPerPageSelect").val());
            let currentPage = 1;
            let rows = $("#eventTable tbody tr");
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
                let totalEntries = $("#eventTable tbody tr").length; // Count all table rows
                let visibleEntries = $("#eventTable tbody tr:visible").length; // Count only visible rows

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

            /** 🔹 Add Event AJAX Request */
            $("#addEventForm").submit(function(e) {
                e.preventDefault();

                $.post("events/add_event.php", $(this).serialize(), function(response) {
                    if (response.startsWith("success|")) {
                        let eventData = response.split("|"); // Split response to get event data
                        let newEventId = eventData[1]; // Get the new event's ID
                        let title = eventData[2];
                        let description = eventData[3];
                        let dateTime = eventData[4];
                        let venue = eventData[5];
                        let capacity = eventData[6] || "No limit";
                        let organization = eventData[7] || "N/A";

                        // Create new row HTML
                        let newRow = `
                <tr id="event_${newEventId}">
                    <td class="text-center">${title}</td>
                    <td class="text-center">${dateTime}</td>
                    <td class="text-center">${venue}</td>
                    <td class="text-center">${organization}</td>
                    <td class="text-center"><span class="badge bg-secondary">Pending</span></td>
                    <td class="text-center">
                        <button class="btn btn-m p-2 text-warning viewEvent" data-id="${newEventId}" data-bs-toggle="modal" data-bs-target="#viewEventModal">
                            <i class="fas fa-info-circle fa-lg"></i>
                        </button>
                        <button class="btn btn-m p-2 text-primary editEvent" data-id="${newEventId}" data-bs-toggle="modal" data-bs-target="#editEventModal">
                            <i class="fas fa-edit fa-lg"></i>
                        </button>
                        <button class="btn btn-m p-2 text-success toggleEventStatus" data-id="${newEventId}" data-status="pending" data-bs-toggle="modal" data-bs-target="#approveEventModal">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </button>
                        <button class="btn btn-m p-2 text-secondary toggleEventStatus" data-id="${newEventId}" data-status="rejected" data-bs-toggle="modal" data-bs-target="#rejectEventModal">
                            <i class="fas fa-times-circle fa-lg"></i>
                        </button>

                        <button class="btn btn-m p-2 text-secondary deleteEvent" data-id="${newEventId}">
                            <i class="fas fa-trash-alt fa-lg"></i>
                        </button>
                    </td>
                </tr>
            `;

                        // Hide modal, reset form, prepend row to table, and show success toast
                        $("#addEventModal").modal("hide");
                        $("#eventTable tbody").prepend(newRow);
                        $("#addEventForm")[0].reset();
                        showToast("Event added successfully!");
                    } else {
                        showToast(response, "error");
                    }
                }).fail(function(xhr, status, error) {
                    showToast("AJAX Error: " + xhr.status + " - " + error, "error");
                });
            });

            $(document).ready(function() {
                /** ✅ Event delegation for Approve & Reject buttons */
                $(document).on("click", ".approveEvent, .disapproveEvent", function() {
                    let eventId = $(this).data("id");

                    // Show the correct modal based on the button clicked
                    if ($(this).hasClass("approveEvent")) {
                        $("#approve_event_id").val(eventId);
                        $("#approveEventModal").modal("show");
                    } else {
                        $("#reject_event_id").val(eventId);
                        $("#rejectEventModal").modal("show");
                    }
                });

                /** ✅ Confirm Approve Event */
                $(document).on("click", "#confirmApproveEvent", function() {
                    let eventId = $("#approve_event_id").val();
                    updateEventStatus(eventId, "approved");
                });

                /** ✅ Confirm Reject Event */
                $(document).on("click", "#confirmRejectEvent", function() {
                    let eventId = $("#reject_event_id").val();
                    let reason = $("#reject_reason").val().trim();

                    if (reason === "") {
                        showToast("Please provide a reason for rejection.", "danger");
                        return;
                    }

                    updateEventStatus(eventId, "rejected", reason);
                });

                /** ✅ Function to Update Event Status */
                function updateEventStatus(eventId, status, reason = "") {
                    $.post("events/update_event_status.php", {
                        event_id: eventId,
                        status: status,
                        reason: reason
                    }, function(response) {
                        if (response.trim() === "success") {
                            let row = $("tr").filter(function() {
                                return $(this).find(".approveEvent, .disapproveEvent").data("id") == eventId;
                            });

                            // ✅ Update the Status Badge
                            let statusBadge = row.find(".badge");
                            if (status === "approved") {
                                statusBadge.text("Approved").removeClass("bg-secondary bg-danger").addClass("bg-success");
                            } else {
                                statusBadge.text("Rejected").removeClass("bg-secondary bg-success").addClass("bg-danger");
                            }

                            // ✅ Hide the Modal
                            $("#" + (status === "approved" ? "approveEventModal" : "rejectEventModal")).modal("hide");

                            // ✅ Show Success Toast
                            showToast(`Event ${status === "approved" ? "approved" : "rejected"} successfully!`, "success");
                        } else {
                            showToast("Error updating event status: " + response, "danger");
                        }
                    }).fail(function(xhr, status, error) {
                        showToast("AJAX Error: " + xhr.status + " - " + error, "danger");
                    });
                }

                /** ✅ Bootstrap Toast Notification */
                function showToast(message, type = "success") {
                    let toastClass = type === "success" ? "bg-success text-white" : "bg-danger text-white";
                    let toastHTML = `
            <div class="toast align-items-center ${toastClass} position-fixed bottom-0 end-0 p-2 m-3" role="alert">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;

                    let toastElement = document.createElement("div");
                    toastElement.innerHTML = toastHTML;
                    document.body.appendChild(toastElement);

                    let toast = new bootstrap.Toast(toastElement.querySelector(".toast"));
                    toast.show();

                    setTimeout(() => {
                        toastElement.querySelector(".toast").remove();
                    }, 3000);
                }
            });



            $(document).ready(function() {
                let deleteEventId = null;
                let deleteButton = null;

                // Open Delete Modal & Store Event ID
                $(document).on("click", ".deleteEvent", function() {
                    deleteEventId = $(this).data("id");
                    deleteButton = $(this);
                    $("#delete_event_id").val(deleteEventId);
                    $("#deleteEventModal").modal("show");
                });

                // Confirm Delete Event
                $("#confirmDeleteEvent").click(function() {
                    $.ajax({
                        url: "events/delete_event.php",
                        type: "POST",
                        data: {
                            event_id: deleteEventId
                        },
                        success: function(response) {
                            response = response.trim(); // Trim spaces & newlines

                            if (response === "success") {
                                // ✅ Remove row instantly after deletion success
                                deleteButton.closest("tr").fadeOut(300, function() {
                                    $(this).remove();
                                    showToast("Event deleted successfully!", "success");
                                });

                                $("#deleteEventModal").modal("hide");
                            } else {
                                showToast("Error deleting event: " + response, "error");
                            }
                        },
                        error: function(xhr, status, error) {
                            showToast("AJAX Error: " + xhr.responseText, "error");
                        }
                    });
                });
            });


            // View Event (Load details into modal)
            $(document).on("click", ".viewEvent", function() {
                let eventId = $(this).data("id");

                $.ajax({
                    url: "events/get_event.php",
                    type: "POST",
                    data: {
                        event_id: eventId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response && response.event_id) {
                            // ✅ Populate modal fields with event data
                            $("#viewEventTitle").text(response.title);
                            $("#viewEventDescription").text(response.description);
                            $("#viewEventDate").text(formatDateTime(response.date_time));
                            $("#viewEventVenue").text(response.venue);
                            $("#viewEventCapacity").text(response.capacity ? response.capacity : "No limit");
                            $("#viewEventOrganization").text(response.organization ? response.organization : "N/A");

                            // ✅ Set Status Badge (Dynamically change color like Edit Modal)
                            let statusBadge = $("#viewEventStatus");
                            statusBadge.text(response.status);
                            statusBadge.removeClass().addClass("badge bg-" +
                                (response.status === "approved" ? "success" :
                                    response.status === "pending" ? "warning" :
                                    response.status === "rejected" ? "danger" : "secondary"));

                            // ✅ Open the modal
                            $("#viewEventModal").modal("show");
                        } else {
                            showToast("Error: Event data not found.", "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast("AJAX Error: " + xhr.responseText, "error");
                    }
                });
            });

            // ✅ Helper Function to Format Date & Time
            function formatDateTime(dateTimeStr) {
                let dateObj = new Date(dateTimeStr);
                if (isNaN(dateObj)) return dateTimeStr; // If invalid, return original

                let options = {
                    year: "numeric",
                    month: "short",
                    day: "numeric",
                    hour: "2-digit",
                    minute: "2-digit",
                    hour12: true
                };
                return dateObj.toLocaleString("en-US", options);
            }


            // ✅ Open Edit Event Modal and Populate Fields
            $(document).on("click", ".editEvent", function() {
                let eventId = $(this).data("id");

                $.ajax({
                    url: "events/get_event.php", // Fetch event data
                    type: "POST",
                    data: {
                        event_id: eventId
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response && response.event_id) {
                            // Populate the modal fields with event data
                            $("#edit_event_id").val(response.event_id);
                            $("#edit_title").val(response.title);
                            $("#edit_description").val(response.description);
                            $("#edit_date_time").val(response.date_time);
                            $("#edit_venue").val(response.venue);
                            $("#edit_capacity").val(response.capacity || 0);

                            // Store the status in a hidden field (useful if needed later)
                            $("#edit_status").val(response.status);

                            // Open the modal
                            $("#editEventModal").modal("show");
                        } else {
                            showToast("Error: Event data not found.", "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast("AJAX Error: " + xhr.responseText, "error");
                    }
                });
            });

            // ✅ Edit Event Submission (Dynamically Update Table Row, Keep Status Unchanged)
            $("#editEventForm").submit(function(e) {
                e.preventDefault(); // Prevent default form submission

                $.ajax({
                    url: "events/edit_event.php", // Update event data
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        let eventData = response.split("|"); // Expecting "success|event_id|title|date_time|venue"
                        let eventId = eventData[1];

                        if (response.startsWith("success|")) {
                            // Find the row corresponding to the updated event
                            let row = $("#eventTable tbody tr").filter(function() {
                                return $(this).find(".editEvent").data("id") == eventId;
                            });

                            // ✅ Update table row dynamically (EXCLUDING STATUS BADGE)
                            row.find("td:nth-child(1)").text(eventData[2]); // Title
                            row.find("td:nth-child(2)").text(eventData[3]); // Date & Time
                            row.find("td:nth-child(3)").text(eventData[4]); // Venue

                            // ✅ Hide modal and show success toast
                            $("#editEventModal").modal("hide");
                            showToast("Event updated successfully!");
                        } else {
                            showToast("Error updating event: " + response, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        showToast("AJAX Error: " + xhr.responseText, "error");
                    }
                });
            });

        });
    </script>

</body>

</html>