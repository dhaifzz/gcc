<?php
require_once '../../font/font.php';
require_once('../../database/database.php');

session_start();

$message = '';
$error = '';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

$appointments = [];
try {
    $stmt = $pdo->prepare("SELECT a.*, u.first_name, u.middle_name, u.last_name 
                           FROM appointments a
                           JOIN users u ON a.client_id = u.id
                           WHERE (a.status = 'pending' OR a.status = 'approved') AND a.appointment_type = 'assessment'");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];
    
    try {
        if ($action == 'evaluate') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'evaluated', Staff_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment evaluated successfully.";
        } elseif ($action == 'reschedule') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'rescheduled', Staff_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment marked as rescheduled successfully.";
        } elseif ($action == 'complete') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed', Staff_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment marked as completed successfully.";
        } elseif ($action == 'cancel') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled', Staff_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment cancelled successfully.";
        }
        
        // Refresh the appointments list
        $stmt = $pdo->prepare("SELECT a.*, u.first_name, u.middle_name, u.last_name 
                               FROM appointments a
                               JOIN users u ON a.client_id = u.id
                               WHERE (a.status = 'pending' OR a.status = 'approved') AND a.appointment_type = 'assessment'");
        $stmt->execute();
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        $error = "Error processing action: " . $e->getMessage();
    }
}

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC Admin - Assessment Requests</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" type="text/css" href="css/filter-modal.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="staff-dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="staff-counseling.php"><i class="fa-regular fa-calendar-days"></i>Counseling Table</a>
            <a href="staff-assessment.php" style="background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-regular fa-calendar"></i> Assessment Table</a>           
            <a href="staff-shifting.php"><i class="fa-solid fa-envelope"></i> Shifting Table</a>
            
            <a href="staff-history.php"><i class="fa fa-history"></i> History</a>
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="sidebar-footer">
            <small>© 2025 WMSU </small>
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Assessment Requests</h1>
        </div>

        <div class="filters-section">
            <button class="filter-btn" id="filterBtn">
                <i class="fas fa-filter"></i> Filter
            </button>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="tableSearch" placeholder="Search...">
            </div>
        </div>

        <?php if ($error): ?>
            <div class="notification error">
                <div><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <span class="close-notification">&times;</span>
            </div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="notification success">
                <div><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
                <span class="close-notification">&times;</span>
            </div>
        <?php endif; ?>

        <div class="data-table-container">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h3>No Pending Assessment Requests</h3>
                    <p>There are currently no assessment requests awaiting evaluation.</p>
                </div>
            <?php else: ?>
                <table id="appointmentsTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Client Name</th>
                            <th>Date</th>
                            <th>Requested Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($appointment['client_id']); ?></td>
                            <td><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['middle_name'] . ' ' . $appointment['last_name']); ?></td>
                            <td><?php echo htmlspecialchars(date('M j, Y', strtotime($appointment['requested_date']))); ?></td>
                            <td><?php echo htmlspecialchars($appointment['requested_time']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                    <?php echo ucfirst($appointment['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="action-btn view-btn" data-client-id="<?php echo $appointment['client_id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                
                                <?php if ($appointment['status'] == 'pending'): ?>
                                <button type="button" class="action-btn evaluate-btn" 
                                    data-appointment-id="<?php echo $appointment['appointment_id']; ?>"
                                    data-client-name="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>"
                                    data-appointment-date="<?php echo htmlspecialchars(date('M j, Y', strtotime($appointment['requested_date']))); ?>"
                                    data-appointment-time="<?php echo htmlspecialchars($appointment['requested_time']); ?>">
                                    <i class="fas fa-check"></i> Evaluate
                                </button>
                                
                                <button type="button" class="action-btn reschedule-btn"
                                    data-appointment-id="<?php echo $appointment['appointment_id']; ?>"
                                    data-client-name="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>"
                                    data-appointment-date="<?php echo htmlspecialchars(date('M j, Y', strtotime($appointment['requested_date']))); ?>"
                                    data-appointment-time="<?php echo htmlspecialchars($appointment['requested_time']); ?>">
                                    <i class="fas fa-calendar-alt"></i> Reschedule
                                </button>
                                <?php elseif ($appointment['status'] == 'approved'): ?>
                                <button type="button" class="action-btn complete-btn" 
                                    data-appointment-id="<?php echo $appointment['appointment_id']; ?>"
                                    data-client-name="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>"
                                    data-appointment-date="<?php echo htmlspecialchars(date('M j, Y', strtotime($appointment['requested_date']))); ?>"
                                    data-appointment-time="<?php echo htmlspecialchars($appointment['requested_time']); ?>">
                                    <i class="fas fa-check-circle"></i> Complete
                                </button>
                                
                                <button type="button" class="action-btn cancel-btn"
                                    data-appointment-id="<?php echo $appointment['appointment_id']; ?>"
                                    data-client-name="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>"
                                    data-appointment-date="<?php echo htmlspecialchars(date('M j, Y', strtotime($appointment['requested_date']))); ?>"
                                    data-appointment-time="<?php echo htmlspecialchars($appointment['requested_time']); ?>">
                                    <i class="fas fa-times-circle"></i> Cancel
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Modal -->
    <div id="filterModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-filter"></i> Filter Assessments</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="form-group">
                        <label for="clientNameFilter">
                            <i class="fas fa-user"></i> Client Name
                        </label>
                        <input type="text" id="clientNameFilter" class="form-control" placeholder="Search by client name...">
                    </div>

                    <div class="date-filter-group">
                        <h3><i class="fas fa-calendar"></i> Date Range</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="dateFrom">From</label>
                                <input type="date" id="dateFrom" name="dateFrom" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="dateTo">To</label>
                                <input type="date" id="dateTo" name="dateTo" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="time-filter-group">
                        <h3><i class="fas fa-clock"></i> Time</h3>
                        <div class="form-group">
                            <label for="timeFilter">Select Time</label>
                            <input type="time" id="timeFilter" name="timeFilter" class="form-control">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" id="clearFilters">
                            <i class="fas fa-eraser"></i> Clear All
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Apply Filters
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Client Details Modal -->
    <div id="clientDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user"></i> Client Details</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="client-profile-section">
                    <div class="profile-image">
                        <img id="clientProfileImage" src="" alt="Profile Image" onclick="openImageViewer(this.src)">
                        <div class="image-overlay">
                            <i class="fas fa-search-plus"></i> Click to view
                        </div>
                    </div>
                    <div class="client-info">
                        <div class="info-group">
                            <h3>Personal Information</h3>
                            <p><strong>Name:</strong> <span id="clientFullName"></span></p>
                            <p><strong>Age:</strong> <span id="clientAge"></span></p>
                            <p><strong>Sex:</strong> <span id="clientSex"></span></p>
                            <p><strong>Civil Status:</strong> <span id="clientCivilStatus"></span></p>
                        </div>
                        <div class="info-group">
                            <h3>Academic Information</h3>
                            <p><strong>School:</strong> <span id="clientSchool"></span></p>
                            <p><strong>Course/Grade:</strong> <span id="clientCourseGrade"></span></p>
                            <p><strong>WMSU ID:</strong> <span id="clientWmsuId"></span></p>
                        </div>
                        <div class="info-group">
                            <h3>Contact Information</h3>
                            <p><strong>Contact Number:</strong> <span id="clientContactNumber"></span></p>
                            <p><strong>Address:</strong> <span id="clientAddress"></span></p>
                            <p><strong>Occupation:</strong> <span id="clientOccupation"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Viewer Modal -->
    <div id="imageViewerModal" class="modal image-viewer-modal">
        <span class="close">&times;</span>
        <img id="expandedImage" src="">
    </div>

    <!-- Evaluate Confirmation Modal -->
    <div id="evaluateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-check-circle"></i> Confirm Evaluation</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to evaluate the assessment appointment for:</p>
                <div class="confirmation-details">
                    <p><strong>Client:</strong> <span id="evaluateClientName"></span></p>
                    <p><strong>Date:</strong> <span id="evaluateAppointmentDate"></span></p>
                    <p><strong>Time:</strong> <span id="evaluateAppointmentTime"></span></p>
                </div>
                <div class="modal-footer">
                    <form method="post" id="evaluateForm">
                        <input type="hidden" name="appointment_id" id="evaluateAppointmentId">
                        <input type="hidden" name="action" value="evaluate">
                        <button type="button" class="btn-secondary cancel-btn">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Confirm Evaluation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reschedule Confirmation Modal -->
    <div id="rescheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-alt"></i> Confirm Reschedule</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to reschedule the assessment appointment for:</p>
                <div class="confirmation-details">
                    <p><strong>Client:</strong> <span id="rescheduleClientName"></span></p>
                    <p><strong>Date:</strong> <span id="rescheduleAppointmentDate"></span></p>
                    <p><strong>Time:</strong> <span id="rescheduleAppointmentTime"></span></p>
                </div>
                <div class="modal-footer">
                    <form method="post" id="rescheduleForm">
                        <input type="hidden" name="appointment_id" id="rescheduleAppointmentId">
                        <input type="hidden" name="action" value="reschedule">
                        <button type="button" class="btn-secondary cancel-btn">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Confirm Reschedule
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Confirmation Modal -->
    <div id="completeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-check-circle"></i> Confirm Completion</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to mark this assessment appointment as completed for:</p>
                <div class="confirmation-details">
                    <p><strong>Client:</strong> <span id="completeClientName"></span></p>
                    <p><strong>Date:</strong> <span id="completeAppointmentDate"></span></p>
                    <p><strong>Time:</strong> <span id="completeAppointmentTime"></span></p>
                </div>
                <div class="modal-footer">
                    <form method="post" id="completeForm">
                        <input type="hidden" name="appointment_id" id="completeAppointmentId">
                        <input type="hidden" name="action" value="complete">
                        <button type="button" class="btn-secondary cancel-btn">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Confirm Completion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-times-circle"></i> Confirm Cancellation</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this assessment appointment for:</p>
                <div class="confirmation-details">
                    <p><strong>Client:</strong> <span id="cancelClientName"></span></p>
                    <p><strong>Date:</strong> <span id="cancelAppointmentDate"></span></p>
                    <p><strong>Time:</strong> <span id="cancelAppointmentTime"></span></p>
                </div>
                <div class="modal-footer">
                    <form method="post" id="cancelForm">
                        <input type="hidden" name="appointment_id" id="cancelAppointmentId">
                        <input type="hidden" name="action" value="cancel">
                        <button type="button" class="btn-secondary cancel-btn">
                            <i class="fas fa-times"></i> Go Back
                        </button>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-check"></i> Confirm Cancellation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Modal styles */
    #clientDetailsModal .modal-content {
        max-width: 800px;
        width: 90%;
    }

    .client-profile-section {
        display: flex;
        gap: 2rem;
        padding: 1rem;
    }

    .profile-image {
        flex: 0 0 200px;
        position: relative;
        cursor: pointer;
    }

    .profile-image img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 10px;
        border: 2px solid #ddd;
        transition: transform 0.3s ease;
    }

    .profile-image:hover img {
        transform: scale(1.05);
    }

    .image-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 8px;
        text-align: center;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        font-size: 0.9rem;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .profile-image:hover .image-overlay {
        opacity: 1;
    }

    .client-info {
        flex: 1;
    }

    .info-group {
        margin-bottom: 1.5rem;
    }

    .info-group h3 {
        color: #236641;
        margin-bottom: 0.5rem;
        border-bottom: 2px solid #236641;
        padding-bottom: 0.25rem;
    }

    .info-group p {
        margin: 0.5rem 0;
        line-height: 1.6;
    }

    .info-group strong {
        color: #333;
        min-width: 120px;
        display: inline-block;
    }

    .view-btn {
        background-color: #236641;
        color: white;
        margin-right: 5px;
    }

    .view-btn:hover {
        background-color: #1a4d31;
    }

    /* Image Viewer Modal Styles */
    .image-viewer-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        padding: 20px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
    }

    .image-viewer-modal img {
        max-width: 90%;
        max-height: 90vh;
        margin: auto;
        display: block;
        object-fit: contain;
    }

    .image-viewer-modal .close {
        position: absolute;
        right: 35px;
        top: 15px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        cursor: pointer;
    }

    .image-viewer-modal .close:hover {
        color: #bbb;
    }

    /* Updated Modal Footer Styles */
    .modal-footer {
        margin-top: 1.5rem;
        text-align: right;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .modal-footer form {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        width: 100%;
    }

    .modal-footer button {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background-color: #236641;
        color: white;
        min-width: 120px;
        justify-content: center;
    }

    .btn-primary:hover {
        background-color: #1a4d31;
        transform: translateY(-1px);
    }

    .btn-secondary {
        background-color: #6c757d;
        color: white;
        min-width: 100px;
        justify-content: center;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        transform: translateY(-1px);
    }

    /* Add shadow effect on hover */
    .modal-footer button:hover {
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    /* Active state for buttons */
    .modal-footer button:active {
        transform: translateY(0);
    }

    .complete-btn {
        background-color: #28a745;
        color: white;
        margin-right: 5px;
    }

    .complete-btn:hover {
        background-color: #218838;
    }

    .cancel-btn {
        background-color: #dc3545;
        color: white;
    }

    .cancel-btn:hover {
        background-color: #c82333;
    }

    /* Status indicator */
    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .status-pending {
        background-color: #ffeeba;
        color: #856404;
    }

    .status-approved {
        background-color: #d4edda;
        color: #155724;
    }

    .status-completed {
        background-color: #c3e6cb;
        color: #155724;
    }

    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
    }

    .status-evaluated {
        background-color: #cce5ff;
        color: #004085;
    }
    </style>

    <script>
        $(document).ready(function() {
            // Initialize DataTable with more options
            var table = $('#appointmentsTable').DataTable({
                responsive: true,
                dom: '<"top">rt<"bottom"ip><"clear">',
                language: {
                    emptyTable: "No assessment requests available",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'
                    }
                },
                order: [[2, 'asc']], // Sort by date by default
                pageLength: 10,
                lengthChange: false
            });

            // Real-time search functionality
            $('#tableSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Modal functionality
            var modal = document.getElementById("filterModal");
            var filterBtn = document.getElementById("filterBtn");
            var span = modal.getElementsByClassName("close")[0];

            filterBtn.onclick = function() {
                modal.style.display = "block";
            }

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            // Filter form submission
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                
                var clientName = $('#clientNameFilter').val().toLowerCase();
                var dateFrom = $('#dateFrom').val();
                var dateTo = $('#dateTo').val();
                var timeFilter = $('#timeFilter').val();

                // Clear existing custom filters
                $.fn.dataTable.ext.search.pop();

                // Apply all filters
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var rowDate = new Date(data[2]); // Date column index
                    var rowTime = data[3]; // Time column index
                    
                    // Client name filtering
                    if (clientName && !data[1].toLowerCase().includes(clientName)) return false;
                    
                    // Date filtering
                    var fromDate = dateFrom ? new Date(dateFrom) : null;
                    var toDate = dateTo ? new Date(dateTo) : null;
                    
                    if (fromDate && rowDate < fromDate) return false;
                    if (toDate && rowDate > toDate) return false;
                    
                    // Time filtering
                    if (timeFilter && rowTime < timeFilter) return false;
                    
                    return true;
                });

                table.draw();
                modal.style.display = "none";
            });

            // Clear filters
            $('#clearFilters').click(function() {
                $('#filterForm')[0].reset();
                $.fn.dataTable.ext.search.pop();
                table.draw();
            });

            // Close notification
            $('.close-notification').click(function() {
                $(this).parent().fadeOut();
            });
            
            // Auto-hide success message after 5 seconds
            setTimeout(function() {
                $('.notification').fadeOut();
            }, 5000);

            // Client Details Modal
            var clientModal = document.getElementById("clientDetailsModal");
            var clientModalSpan = clientModal.getElementsByClassName("close")[0];

            // Close modal when clicking the X button
            clientModalSpan.onclick = function() {
                clientModal.style.display = "none";
            }

            // Close modal when clicking outside
            window.onclick = function(event) {
                if (event.target == clientModal) {
                    clientModal.style.display = "none";
                }
            }

            // Handle view button click
            $(document).on('click', '.view-btn', function() {
                var clientId = $(this).data('client-id');
                
                // Fetch client details using AJAX
                $.ajax({
                    url: 'get_client_details.php',
                    type: 'POST',
                    data: { client_id: clientId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            var client = response.data;
                            
                            // Determine the correct image path
                            var profileImagePath;
                            if (client.profile_image === 'default-profile.png') {
                                profileImagePath = '../../img/profiles/default-profile.png';
                            } else {
                                profileImagePath = '../../img/profiles/' + client.profile_image;
                            }
                            
                            // Test if image exists
                            var img = new Image();
                            img.onload = function() {
                                $('#clientProfileImage').attr('src', profileImagePath);
                            };
                            img.onerror = function() {
                                console.log('Image failed to load:', profileImagePath);
                                $('#clientProfileImage').attr('src', '../../img/profiles/default-profile.png');
                            };
                            img.src = profileImagePath;
                            
                            // Update other client details
                            $('#clientFullName').text(client.first_name + ' ' + 
                                (client.middle_name ? client.middle_name + ' ' : '') + 
                                client.last_name);
                            $('#clientAge').text(client.age || 'N/A');
                            $('#clientSex').text(client.sex || 'N/A');
                            $('#clientCivilStatus').text(client.civil_status || 'N/A');
                            $('#clientSchool').text(client.school || 'N/A');
                            $('#clientCourseGrade').text(client.course_grade || 'N/A');
                            $('#clientWmsuId').text(client.wmsu_id || 'N/A');
                            $('#clientContactNumber').text(client.contact_number || 'N/A');
                            $('#clientAddress').text(client.address || 'N/A');
                            $('#clientOccupation').text(client.occupation || 'N/A');
                            
                            // Show the modal
                            clientModal.style.display = "block";
                        } else {
                            alert('Error: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        alert('Error fetching client details');
                    }
                });
            });

            // Image viewer functionality
            window.openImageViewer = function(imageSrc) {
                $('#expandedImage').attr('src', imageSrc);
                $('#imageViewerModal').css('display', 'block');
            }

            // Close image viewer
            $('#imageViewerModal .close').click(function() {
                $('#imageViewerModal').css('display', 'none');
            });

            // Close image viewer when clicking outside
            $(window).click(function(event) {
                if (event.target.id === 'imageViewerModal') {
                    $('#imageViewerModal').css('display', 'none');
                }
            });

            // Evaluate button click handler
            $(document).on('click', '.evaluate-btn', function() {
                var appointmentId = $(this).data('appointment-id');
                var clientName = $(this).data('client-name');
                var appointmentDate = $(this).data('appointment-date');
                var appointmentTime = $(this).data('appointment-time');

                $('#evaluateAppointmentId').val(appointmentId);
                $('#evaluateClientName').text(clientName);
                $('#evaluateAppointmentDate').text(appointmentDate);
                $('#evaluateAppointmentTime').text(appointmentTime);
                
                $('#evaluateModal').css('display', 'block');
            });

            // Reschedule button click handler
            $(document).on('click', '.reschedule-btn', function() {
                var appointmentId = $(this).data('appointment-id');
                var clientName = $(this).data('client-name');
                var appointmentDate = $(this).data('appointment-date');
                var appointmentTime = $(this).data('appointment-time');

                $('#rescheduleAppointmentId').val(appointmentId);
                $('#rescheduleClientName').text(clientName);
                $('#rescheduleAppointmentDate').text(appointmentDate);
                $('#rescheduleAppointmentTime').text(appointmentTime);
                
                $('#rescheduleModal').css('display', 'block');
            });

            // Complete button click handler
            $(document).on('click', '.complete-btn', function() {
                var appointmentId = $(this).data('appointment-id');
                var clientName = $(this).data('client-name');
                var appointmentDate = $(this).data('appointment-date');
                var appointmentTime = $(this).data('appointment-time');

                $('#completeAppointmentId').val(appointmentId);
                $('#completeClientName').text(clientName);
                $('#completeAppointmentDate').text(appointmentDate);
                $('#completeAppointmentTime').text(appointmentTime);
                
                $('#completeModal').css('display', 'block');
            });

            // Cancel button click handler
            $(document).on('click', '.cancel-btn', function() {
                var appointmentId = $(this).data('appointment-id');
                var clientName = $(this).data('client-name');
                var appointmentDate = $(this).data('appointment-date');
                var appointmentTime = $(this).data('appointment-time');

                $('#cancelAppointmentId').val(appointmentId);
                $('#cancelClientName').text(clientName);
                $('#cancelAppointmentDate').text(appointmentDate);
                $('#cancelAppointmentTime').text(appointmentTime);
                
                $('#cancelModal').css('display', 'block');
            });

            // Close modals when clicking the X button or cancel
            $('.modal .close, .cancel-btn').click(function() {
                $(this).closest('.modal').css('display', 'none');
            });

            // Close modals when clicking outside
            $(window).click(function(event) {
                if ($(event.target).hasClass('modal')) {
                    $('.modal').css('display', 'none');
                }
            });
        });
    </script>
</body>
</html>