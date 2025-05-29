<?php
require_once '../../font/font.php';
require_once('../../database/database.php');
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

function capitalizeWords($str) {
    if (empty($str)) return $str;
    $str = ucwords(strtolower($str));
    $specialCases = [
        'rescheduled' => 'Rescheduled',
        'evaluated' => 'Evaluated',
        'completed' => 'Completed',
        'approved' => 'Approved',
        'declined' => 'Declined',
        'cancelled' => 'Cancelled',
        'counseling' => 'Counseling',
        'assessment' => 'Assessment',
        'shifting' => 'Shifting'
    ];
    foreach ($specialCases as $lower => $proper) {
        if (strtolower($str) === strtolower($lower)) {
            return $proper;
        }
    }
    return $str;
}

// Get appointments history
$appointmentsSql = "
    SELECT a.*, u.first_name, u.last_name 
    FROM appointments a 
    JOIN users u ON a.client_id = u.id 
    WHERE a.Staff_id = :staff_id 
    AND a.status IN ('evaluated', 'rescheduled', 'completed')
    ORDER BY a.requested_date DESC";

// Get shifting history
$shiftingSql = "
    SELECT s.*, u.first_name, u.last_name, u.wmsu_id
    FROM shifting s 
    JOIN users u ON s.user_id = u.id 
    WHERE s.status IN ('evaluated', 'declined', 'completed')
    ORDER BY s.submitted_at DESC";

try {
    // Get appointments
    $stmt = $pdo->prepare($appointmentsSql);
    $stmt->execute(['staff_id' => $_SESSION['user_id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get shifting requests
    $shiftingStmt = $pdo->prepare($shiftingSql);
    $shiftingStmt->execute();
    $shiftingRequests = $shiftingStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC Staff - History</title>
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
            <a href="staff-assessment.php"><i class="fa-regular fa-calendar"></i> Assessment Table</a>           
            <a href="staff-shifting.php"><i class="fa-solid fa-envelope"></i> Shifting Table</a>
            <a href="staff-history.php" style="background-color: rgb(255, 255, 255); color: #236641;"><i class="fa fa-history"></i> History</a>
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
            <h1>History</h1>
        </div>

        <!-- Appointments History Section -->
        <div class="section-header">
            <h2><i class="fa-regular fa-calendar"></i> Appointments History</h2>
        </div>
        <div class="filters-section">
            <button class="filter-btn" id="appointmentsFilterBtn">
                <i class="fas fa-filter"></i> Filter
            </button>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="appointmentsSearch" placeholder="Search appointments...">
            </div>
        </div>
        <div class="data-table-container">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h3>No Appointment History Found</h3>
                    <p>There are currently no completed appointments in your history.</p>
                </div>
            <?php else: ?>
                <table id="appointmentsTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Appointment Type</th>
                            <th>Client Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo capitalizeWords(htmlspecialchars($appointment['appointment_type'])); ?></td>
                                <td><?php echo capitalizeWords(htmlspecialchars($appointment['first_name'] . " " . $appointment['last_name'])); ?></td>
                                <td><?php echo htmlspecialchars(date('M j, Y', strtotime($appointment['requested_date']))); ?></td>
                                <td><?php echo htmlspecialchars($appointment['requested_time']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                                        <?php echo capitalizeWords(htmlspecialchars($appointment['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Shifting History Section -->
        <div class="section-header">
            <h2><i class="fa-solid fa-right-left"></i> Shifting History</h2>
        </div>
        <div class="filters-section">
            <button class="filter-btn" id="shiftingFilterBtn">
                <i class="fas fa-filter"></i> Filter
            </button>
            <div class="search-container">
                <i class="fas fa-search"></i>
                <input type="text" id="shiftingSearch" placeholder="Search shifting requests...">
            </div>
        </div>
        <div class="data-table-container">
            <?php if (empty($shiftingRequests)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-right-left"></i>
                    <h3>No Shifting History Found</h3>
                    <p>There are currently no completed shifting requests in your history.</p>
                </div>
            <?php else: ?>
                <table id="shiftingTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>From Course</th>
                            <th>To Course</th>
                            <th>Date Submitted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shiftingRequests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['wmsu_id']); ?></td>
                                <td><?php echo capitalizeWords(htmlspecialchars($request['first_name'] . " " . $request['last_name'])); ?></td>
                                <td><?php echo htmlspecialchars($request['current_course']); ?></td>
                                <td><?php echo htmlspecialchars($request['course_to_shift']); ?></td>
                                <td><?php echo htmlspecialchars(date('M j, Y', strtotime($request['submitted_at']))); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo strtolower($request['status']); ?>">
                                        <?php echo capitalizeWords(htmlspecialchars($request['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Appointments Filter Modal -->
    <div id="appointmentsFilterModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-filter"></i> Filter Appointments</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="appointmentsFilterForm">
                    <div class="form-group">
                        <label for="appointmentTypeFilter">
                            <i class="fas fa-list"></i> Appointment Type
                        </label>
                        <select id="appointmentTypeFilter" class="form-control">
                            <option value="">All Types</option>
                            <option value="counseling">Counseling</option>
                            <option value="assessment">Assessment</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="appointmentStatusFilter">
                            <i class="fas fa-tag"></i> Status
                        </label>
                        <select id="appointmentStatusFilter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="evaluated">Evaluated</option>
                            <option value="completed">Completed</option>
                            <option value="rescheduled">Rescheduled</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="date-filter-group">
                        <h3><i class="fas fa-calendar"></i> Date Range</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="appointmentDateFrom">From</label>
                                <input type="date" id="appointmentDateFrom" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="appointmentDateTo">To</label>
                                <input type="date" id="appointmentDateTo" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" id="clearAppointmentsFilters">
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

    <!-- Shifting Filter Modal -->
    <div id="shiftingFilterModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-filter"></i> Filter Shifting Requests</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="shiftingFilterForm">
                    <div class="form-group">
                        <label for="shiftingStatusFilter">
                            <i class="fas fa-tag"></i> Status
                        </label>
                        <select id="shiftingStatusFilter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="evaluated">Evaluated</option>
                            <option value="completed">Completed</option>
                            <option value="declined">Declined</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>

                    <div class="date-filter-group">
                        <h3><i class="fas fa-calendar"></i> Date Range</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="shiftingDateFrom">From</label>
                                <input type="date" id="shiftingDateFrom" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="shiftingDateTo">To</label>
                                <input type="date" id="shiftingDateTo" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" id="clearShiftingFilters">
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

    <style>
    .section-header {
        background-color: #f8f9fa;
        padding: 1rem;
        margin: 2rem 0 1rem 0;
        border-radius: 5px;
        border-left: 4px solid #236641;
    }

    .section-header h2 {
        margin: 0;
        color: #236641;
        font-size: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-badge {
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.9em;
        font-weight: 500;
        text-transform: capitalize;
    }
    .status-evaluated {
        background-color: #D4EDDA;
        color: #155724;
        border: 1px solid #C3E6CB;
    }
    .status-completed {
        background-color: #CCE5FF;
        color: #004085;
        border: 1px solid #B8DAFF;
    }
    .status-rescheduled {
        background-color: #FFF3CD;
        color: #856404;
        border: 1px solid #FFEEBA;
    }
    .status-declined {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin: 1rem 0;
    }

    .empty-state i {
        font-size: 3rem;
        color: #236641;
        margin-bottom: 1rem;
    }

    .empty-state h3 {
        color: #333;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #666;
    }

    .filters-section {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1rem 0;
        padding: 0.5rem;
        background-color: #f8f9fa;
        border-radius: 5px;
    }

    .search-container {
        display: flex;
        align-items: center;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 0.5rem;
        flex: 1;
        max-width: 300px;
        margin-left: 1rem;
    }

    .search-container i {
        color: #666;
        margin-right: 0.5rem;
    }

    .search-container input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 0.9rem;
    }

    .filter-btn {
        background-color: #236641;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.3s;
    }

    .filter-btn:hover {
        background-color: #1a4d31;
    }

    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        animation: fadeIn 0.3s;
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        position: relative;
        animation: slideIn 0.3s;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from { transform: translateY(-100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #333;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 0.9rem;
    }

    .form-row {
        display: flex;
        gap: 1rem;
    }

    .form-row .form-group {
        flex: 1;
    }
    </style>

    <script>
        $(document).ready(function() {
            // Initialize DataTables
            var appointmentsTable = $('#appointmentsTable').DataTable({
                responsive: true,
                dom: '<"top">rt<"bottom"ip><"clear">',
                pageLength: 10,
                order: [[2, 'desc']], // Sort by date by default
                language: {
                    emptyTable: "No appointment history available",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'
                    }
                }
            });

            var shiftingTable = $('#shiftingTable').DataTable({
                responsive: true,
                dom: '<"top">rt<"bottom"ip><"clear">',
                pageLength: 10,
                order: [[4, 'desc']], // Sort by date submitted by default
                language: {
                    emptyTable: "No shifting history available",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'
                    }
                }
            });

            // Search functionality
            $('#appointmentsSearch').on('keyup', function() {
                appointmentsTable.search(this.value).draw();
            });

            $('#shiftingSearch').on('keyup', function() {
                shiftingTable.search(this.value).draw();
            });

            // Appointments Filter Modal
            var appointmentsModal = document.getElementById("appointmentsFilterModal");
            var appointmentsBtn = document.getElementById("appointmentsFilterBtn");
            var appointmentsSpan = appointmentsModal.getElementsByClassName("close")[0];

            appointmentsBtn.onclick = function() {
                appointmentsModal.style.display = "block";
            }

            appointmentsSpan.onclick = function() {
                appointmentsModal.style.display = "none";
            }

            // Shifting Filter Modal
            var shiftingModal = document.getElementById("shiftingFilterModal");
            var shiftingBtn = document.getElementById("shiftingFilterBtn");
            var shiftingSpan = shiftingModal.getElementsByClassName("close")[0];

            shiftingBtn.onclick = function() {
                shiftingModal.style.display = "block";
            }

            shiftingSpan.onclick = function() {
                shiftingModal.style.display = "none";
            }

            // Close modals when clicking outside
            window.onclick = function(event) {
                if (event.target == appointmentsModal) {
                    appointmentsModal.style.display = "none";
                }
                if (event.target == shiftingModal) {
                    shiftingModal.style.display = "none";
                }
            }

            // Appointments Filter Form
            $('#appointmentsFilterForm').on('submit', function(e) {
                e.preventDefault();
                
                var type = $('#appointmentTypeFilter').val().toLowerCase();
                var status = $('#appointmentStatusFilter').val().toLowerCase();
                var dateFrom = $('#appointmentDateFrom').val();
                var dateTo = $('#appointmentDateTo').val();

                $.fn.dataTable.ext.search.pop();

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'appointmentsTable') return true;

                    var rowType = data[0].toLowerCase();
                    var rowStatus = data[4].toLowerCase();
                    var rowDate = new Date(data[2]);
                    
                    if (type && !rowType.includes(type)) return false;
                    if (status && !rowStatus.includes(status)) return false;
                    
                    if (dateFrom && new Date(dateFrom) > rowDate) return false;
                    if (dateTo && new Date(dateTo) < rowDate) return false;
                    
                    return true;
                });

                appointmentsTable.draw();
                appointmentsModal.style.display = "none";
            });

            // Shifting Filter Form
            $('#shiftingFilterForm').on('submit', function(e) {
                e.preventDefault();
                
                var status = $('#shiftingStatusFilter').val().toLowerCase();
                var dateFrom = $('#shiftingDateFrom').val();
                var dateTo = $('#shiftingDateTo').val();

                $.fn.dataTable.ext.search.pop();

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'shiftingTable') return true;

                    var rowStatus = data[5].toLowerCase();
                    var rowDate = new Date(data[4]);
                    
                    if (status && !rowStatus.includes(status)) return false;
                    
                    if (dateFrom && new Date(dateFrom) > rowDate) return false;
                    if (dateTo && new Date(dateTo) < rowDate) return false;
                    
                    return true;
                });

                shiftingTable.draw();
                shiftingModal.style.display = "none";
            });

            // Clear Filters
            $('#clearAppointmentsFilters').click(function() {
                $('#appointmentsFilterForm')[0].reset();
                $.fn.dataTable.ext.search.pop();
                appointmentsTable.draw();
            });

            $('#clearShiftingFilters').click(function() {
                $('#shiftingFilterForm')[0].reset();
                $.fn.dataTable.ext.search.pop();
                shiftingTable.draw();
            });
        });
    </script>
</body>
</html>