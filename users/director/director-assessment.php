<?php
require_once '../../font/font.php';
require_once('../../database/database.php');

session_start();

$message = '';
$error = '';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Director') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

$appointments = [];
try {
    $stmt = $pdo->prepare("SELECT a.*, u.first_name, u.middle_name, u.last_name,
                           CONCAT(s.first_name, ' ', s.last_name) as staff_name
                           FROM appointments a
                           JOIN users u ON a.client_id = u.id
                           LEFT JOIN users s ON a.Staff_id = s.id
                           WHERE a.status = 'evaluated' AND a.appointment_type = 'assessment'");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];
    
    try {
        if ($action == 'approve') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'approved', Director_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment approved successfully.";
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'rejected', Director_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment rejected successfully.";
        }
        
        // Refresh the appointments list
        $stmt = $pdo->prepare("SELECT a.*, u.first_name, u.middle_name, u.last_name,
                               CONCAT(s.first_name, ' ', s.last_name) as staff_name
                               FROM appointments a
                               JOIN users u ON a.client_id = u.id
                               LEFT JOIN users s ON a.Staff_id = s.id
                               WHERE a.status = 'evaluated' AND a.appointment_type = 'assessment'");
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
    <title>GCC Director - Assessment Requests</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/director-assessment.css">
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
            <a href="director-dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="director-counseling.php"><i class="fa-regular fa-calendar-days"></i>Counseling Table</a>
            <a href="director-assessment.php" style="background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-regular fa-calendar"></i> Assessment Table</a>           
            <a href="director-shifting.php"><i class="fa-solid fa-envelope"></i> Shifting Table</a>
            <a href="director-history.php"><i class="fa fa-history"></i> History</a>
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
                    <h3>No Evaluated Assessment Requests</h3>
                    <p>There are currently no assessment requests that have been evaluated by staff.</p>
                </div>
            <?php else: ?>
                <table id="appointmentsTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Client ID</th>
                            <th>Client Name</th>
                            <th>Date</th>
                            <th>Requested Time</th>
                            <th>Approved By</th>
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
                            <td><?php echo htmlspecialchars($appointment['staff_name']); ?></td>
                            <td>
                                <button type="button" class="action-btn view-btn" data-client-id="<?php echo $appointment['client_id']; ?>">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                
                                <button type="button" class="action-btn approve-btn" 
                                    data-appointment-id="<?php echo $appointment['appointment_id']; ?>"
                                    data-client-name="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>"
                                    data-appointment-date="<?php echo htmlspecialchars(date('M j, Y', strtotime($appointment['requested_date']))); ?>"
                                    data-appointment-time="<?php echo htmlspecialchars($appointment['requested_time']); ?>">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                
                                <button type="button" class="action-btn reject-btn"
                                    data-appointment-id="<?php echo $appointment['appointment_id']; ?>"
                                    data-client-name="<?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>"
                                    data-appointment-date="<?php echo htmlspecialchars(date('M j, Y', strtotime($appointment['requested_date']))); ?>"
                                    data-appointment-time="<?php echo htmlspecialchars($appointment['requested_time']); ?>">
                                    <i class="fas fa-times"></i> Reject
                                </button>
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

    <!-- Approve Confirmation Modal -->
    <div id="approveModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <div class="header-title">
                    <i class="fas fa-check-circle"></i>
                    <span>Confirm Approval</span>
                </div>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="confirmation-message">Are you sure you want to approve this assessment request for:</p>
                <div class="confirmation-details">
                    <div class="detail-row">
                        <strong>Client:</strong>
                        <span id="approveClientName"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Date:</strong>
                        <span id="approveAppointmentDate"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Time:</strong>
                        <span id="approveAppointmentTime"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <form method="post" id="approveForm">
                        <input type="hidden" name="appointment_id" id="approveAppointmentId">
                        <input type="hidden" name="action" value="approve">
                        <button type="button" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-confirm">
                            <i class="fas fa-check"></i> Confirm Approval
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <div class="header-title">
                    <i class="fas fa-times-circle"></i>
                    <span>Confirm Rejection</span>
                </div>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="confirmation-message">Are you sure you want to reject this assessment request for:</p>
                <div class="confirmation-details">
                    <div class="detail-row">
                        <strong>Client:</strong>
                        <span id="rejectClientName"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Date:</strong>
                        <span id="rejectAppointmentDate"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Time:</strong>
                        <span id="rejectAppointmentTime"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <form method="post" id="rejectForm">
                        <input type="hidden" name="appointment_id" id="rejectAppointmentId">
                        <input type="hidden" name="action" value="reject">
                        <button type="button" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-confirm">
                            <i class="fas fa-check"></i> Confirm Rejection
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Check if DataTable is already initialized
            if ($.fn.DataTable.isDataTable('#appointmentsTable')) {
                // Destroy the existing instance
                $('#appointmentsTable').DataTable().destroy();
            }
            
            // Initialize DataTable with options
            var table = $('#appointmentsTable').DataTable({
                responsive: true,
                dom: '<"top">rt<"bottom"ip><"clear">', // Remove 'f' to hide built-in search
                searching: true, // Enable searching but hide the default search box
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
                    },
                    search: "" // Remove search label
                },
                order: [[2, 'asc']], // Sort by date by default
                pageLength: 10,
                lengthChange: false
            });

            // Real-time search functionality using our custom search input
            $('#tableSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Filter Modal functionality
            var filterModal = document.getElementById("filterModal");
            var filterBtn = document.getElementById("filterBtn");
            var span = filterModal.getElementsByClassName("close")[0];

            filterBtn.onclick = function() {
                filterModal.style.display = "block";
            }

            span.onclick = function() {
                filterModal.style.display = "none";
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
                filterModal.style.display = "none";
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
                            if (!client.profile_image || client.profile_image === 'default-profile.png') {
                                profileImagePath = '/gcc/img/profiles/default-profile.png';
                            } else {
                                profileImagePath = '/gcc/img/profiles/' + client.profile_image;
                            }
                            
                            // Test if image exists
                            var img = new Image();
                            img.onload = function() {
                                $('#clientProfileImage').attr('src', profileImagePath);
                            };
                            img.onerror = function() {
                                console.log('Image failed to load:', profileImagePath);
                                $('#clientProfileImage').attr('src', '/gcc/img/default-profile.png');
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
                            $('#clientDetailsModal').css('display', 'block');
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

            // Approve button click handler
            $(document).on('click', '.approve-btn', function() {
                var appointmentId = $(this).data('appointment-id');
                var clientName = $(this).data('client-name');
                var appointmentDate = $(this).data('appointment-date');
                var appointmentTime = $(this).data('appointment-time');

                $('#approveAppointmentId').val(appointmentId);
                $('#approveClientName').text(clientName);
                $('#approveAppointmentDate').text(appointmentDate);
                $('#approveAppointmentTime').text(appointmentTime);
                
                $('#approveModal').css('display', 'block');
            });

            // Reject button click handler
            $(document).on('click', '.reject-btn', function() {
                var appointmentId = $(this).data('appointment-id');
                var clientName = $(this).data('client-name');
                var appointmentDate = $(this).data('appointment-date');
                var appointmentTime = $(this).data('appointment-time');

                $('#rejectAppointmentId').val(appointmentId);
                $('#rejectClientName').text(clientName);
                $('#rejectAppointmentDate').text(appointmentDate);
                $('#rejectAppointmentTime').text(appointmentTime);
                
                $('#rejectModal').css('display', 'block');
            });

            // Close modals when clicking the X button or cancel button
            $('.modal .close, .btn-cancel').click(function() {
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

        /* Image Viewer Modal Styles */
        .image-viewer-modal {
            display: none;
            position: fixed;
            z-index: 1100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            overflow: hidden;
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
    </style>
</body>
</html> 