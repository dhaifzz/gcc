<?php
require_once '../../font/font.php';
require_once '../../database/database.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Director') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

// Handle approve/reject actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve']) || isset($_POST['reject'])) {
        $id = $_POST['id'];
        $status = isset($_POST['approve']) ? 'approved' : 'rejected';
        $director_id = $_SESSION['user_id'];
        
        // Update shifting request with director's decision
        $stmt = $pdo->prepare("UPDATE shifting SET 
            status = :status, 
            approved_by = :director_id
            WHERE id = :id AND status = 'evaluated'");
            
        $result = $stmt->execute([
            'status' => $status,
            'director_id' => $director_id,
            'id' => $id
        ]);

        if ($result) {
            // Get student details for the message
            $studentStmt = $pdo->prepare("SELECT first_name, last_name FROM shifting WHERE id = ?");
            $studentStmt->execute([$id]);
            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
            $studentName = $student['first_name'] . ' ' . $student['last_name'];
            
            $message = $status === 'approved' ? 
                "Shifting request for $studentName has been approved. Staff will now process the final completion." :
                "Shifting request for $studentName has been rejected.";
                
            echo "<script>alert('$message');</script>";
        } else {
            echo "<script>alert('Error updating request status.');</script>";
        }
    }
}

// Fetch shifting requests with user info - only evaluated status
$stmt = $pdo->query("SELECT 
    s.id, s.user_id, s.first_name, s.middle_name, s.last_name, 
    s.current_course, s.course_to_shift, s.created_at, s.picture, 
    s.grades, s.cor, s.cet_result, s.status, s.approved_by, 
    s.submitted_at,
    u.email, u.contact_number, u.wmsu_id,
    staff.first_name as staff_first_name, staff.last_name as staff_last_name
                     FROM shifting s
    LEFT JOIN users u ON s.user_id = u.id
    LEFT JOIN users staff ON s.approved_by = staff.id
    WHERE s.status = 'evaluated'
    ORDER BY s.created_at DESC");
$shifting_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC Director - Shifting Requests</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/director-shifting.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="director-dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="director-counseling.php"><i class="fa-regular fa-calendar-days"></i>Counseling Table</a>
            <a href="director-assessment.php"><i class="fa-regular fa-calendar"></i> Assessment Table</a>           
            <a href="director-shifting.php" style="background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-solid fa-envelope"></i> Shifting Table</a>
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
            <h1>Evaluated Shifting Requests</h1>
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

        <!-- Add Filter Modal -->
        <div class="modal-overlay" id="modal-overlay"></div>
        <div class="filter-modal" id="filter-modal">
            <h3>Filter Options</h3>
            <form id="filter-form" class="filter-modal-content">
                <div>
                    <label for="course-filter">Current Course:</label>
                    <input type="text" name="current_course" id="course-filter" placeholder="Current Course">
                </div>
                
                <div>
                    <label for="desired-course-filter">Desired Course:</label>
                    <input type="text" name="desired_course" id="desired-course-filter" placeholder="Desired Course">
                </div>
                
                <div class="filter-modal-buttons">
                    <button type="button" id="cancel-filter">Cancel</button>
                    <button type="submit">Apply Filters</button>
                </div>
            </form>
        </div>

        <div class="data-table-container">
            <?php if (empty($shifting_requests)): ?>
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h3>No Evaluated Shifting Requests</h3>
                    <p>There are currently no shifting requests that have been evaluated.</p>
                </div>
            <?php else: ?>
                <table id="shiftingTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>School ID</th>
                <th>Name</th>
                <th>Current Course</th>
                <th>Desired Course</th>
                            <th>Evaluated By</th>
                            <th>Evaluation Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
                    <?php foreach ($shifting_requests as $row): 
                        $middleInitial = !empty($row['middle_name']) ? ' ' . strtoupper($row['middle_name'][0]) . '. ' : ' ';
                        $fullName = htmlspecialchars($row['first_name'] . $middleInitial . $row['last_name']);
                        $evaluator = $row['staff_first_name'] . ' ' . $row['staff_last_name'];
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['wmsu_id']); ?></td>
                            <td><?php echo $fullName; ?></td>
                            <td><?php echo htmlspecialchars($row['current_course']); ?></td>
                            <td><?php echo htmlspecialchars($row['course_to_shift']); ?></td>
                            <td><?php echo htmlspecialchars($evaluator); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class='action-btn view-btn' onclick='viewRegistration(<?php echo json_encode([
                                        'id' => $row['id'],
                                        'picture' => $row['picture'],
                                        'grades' => $row['grades'],
                                        'cor' => $row['cor'],
                                        'cet_result' => $row['cet_result'],
                                        'fullName' => $fullName,
                                        'wmsu_id' => $row['wmsu_id'],
                                        'current_course' => $row['current_course'],
                                        'course_to_shift' => $row['course_to_shift'],
                                        'submitted_at' => date('M j, Y', strtotime($row['submitted_at'])),
                                        'evaluator' => $evaluator,
                                        'evaluated_at' => date('M j, Y', strtotime($row['created_at']))
                                    ]); ?>)'>
                                        <i class='fas fa-eye'></i> View Details
                                    </button>
                                    <button type="button" class="action-btn approve-btn" 
                                        data-id="<?php echo $row['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($fullName); ?>"
                                        data-current-course="<?php echo htmlspecialchars($row['current_course']); ?>"
                                        data-desired-course="<?php echo htmlspecialchars($row['course_to_shift']); ?>">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject this shifting request?');">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="reject" class="action-btn reject-btn">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
        </tbody>
    </table>
            <?php endif; ?>
    </div>
</div>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
    <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2>Shifting Request Details</h2>
            </div>
            <div id="modalDetails" class="modal-body">
            </div>
        </div>
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
                <p class="confirmation-message">Are you sure you want to approve this shifting request for:</p>
                <div class="confirmation-details">
                    <div class="detail-row">
                        <strong>Student:</strong>
                        <span id="approveStudentName"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Current Course:</strong>
                        <span id="approveCurrentCourse"></span>
                    </div>
                    <div class="detail-row">
                        <strong>Desired Course:</strong>
                        <span id="approveDesiredCourse"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <form method="post" id="approveForm">
                        <input type="hidden" name="id" id="approveShiftingId">
                        <input type="hidden" name="approve" value="1">
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

<script>
$(document).ready(function() {
            // Initialize DataTable with more options
            var table = $('#shiftingTable').DataTable({
        responsive: true,
                dom: '<"top">rt<"bottom"ip><"clear">',
        language: {
                    emptyTable: "No evaluated shifting requests available",
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
                order: [[5, 'desc']], // Sort by evaluation date by default
                pageLength: 10,
                lengthChange: false
            });

            // Real-time search functionality
            $('#tableSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Filter modal functionality
            const filterBtn = document.getElementById('filterBtn');
            const filterModal = document.getElementById('filter-modal');
            const modalOverlay = document.getElementById('modal-overlay');
            const cancelFilter = document.getElementById('cancel-filter');
            const filterForm = document.getElementById('filter-form');
            
            // Show modal
            filterBtn.addEventListener('click', () => {
                filterModal.style.display = 'block';
                modalOverlay.style.display = 'block';
            });
            
            // Hide modal
            function hideModal() {
                filterModal.style.display = 'none';
                modalOverlay.style.display = 'none';
            }
            
            cancelFilter.addEventListener('click', hideModal);
            modalOverlay.addEventListener('click', hideModal);
            
            // Apply filters
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const currentCourse = $('#course-filter').val();
                const desiredCourse = $('#desired-course-filter').val();
                
                // Clear existing filters
                table.columns().search('').draw();
                
                // Apply new filters
                if (currentCourse) {
                    table.column(2).search(currentCourse);
                }
                if (desiredCourse) {
                    table.column(3).search(desiredCourse);
                }
                
                table.draw();
                hideModal();
            });

            // Modal functionality for viewing details
            $('.close').click(function() {
                $('#viewModal').css('display', 'none');
            });

            $(window).click(function(event) {
                if ($(event.target).hasClass('modal')) {
                    $('.modal').css('display', 'none');
                }
            });

            // Approve button click handler
            $(document).on('click', '.approve-btn', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var currentCourse = $(this).data('current-course');
                var desiredCourse = $(this).data('desired-course');

                $('#approveShiftingId').val(id);
                $('#approveStudentName').text(name);
                $('#approveCurrentCourse').text(currentCourse);
                $('#approveDesiredCourse').text(desiredCourse);
                
                $('#approveModal').css('display', 'block');
            });

            // Close modal when clicking the X button or cancel button
            $('.modal .close, .btn-cancel').click(function() {
                $(this).closest('.modal').css('display', 'none');
            });

            // Close modal when clicking outside
            $(window).click(function(event) {
                if ($(event.target).hasClass('modal')) {
                    $('.modal').css('display', 'none');
                }
            });
        });

        function viewRegistration(data) {
            let modalContent = `
                <div class="detail-section">
                    <div class="detail-group">
                        <div class="detail-label">
                            <i class="fas fa-user"></i> Student Name
                        </div>
                        <div class="detail-value">${data.fullName}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">
                            <i class="fas fa-id-card"></i> School ID
                        </div>
                        <div class="detail-value">${data.wmsu_id}</div>
                    </div>
                    <div class="detail-group">
                        <div class="detail-label">
                            <i class="fas fa-graduation-cap"></i> Current Course
                        </div>
                        <div class="detail-value">${data.current_course}</div>
                </div>
                <div class="detail-group">
                        <div class="detail-label">
                            <i class="fas fa-exchange-alt"></i> Desired Course
                        </div>
                        <div class="detail-value">${data.course_to_shift}</div>
                </div>
                <div class="detail-group">
                        <div class="detail-label">
                            <i class="fas fa-user-check"></i> Evaluated By
                        </div>
                        <div class="detail-value">${data.evaluator}</div>
                </div>
                <div class="detail-group">
                        <div class="detail-label">
                            <i class="fas fa-calendar-check"></i> Evaluation Date
                        </div>
                        <div class="detail-value">${data.evaluated_at}</div>
                    </div>
                </div>

                <div class="files-section">
                    <div class="detail-label">
                        <i class="fas fa-file-upload"></i> Submitted Documents
                    </div>
                    <div class="file-grid">
                        ${data.picture ? `
                            <div class="file-preview-card" onclick="viewFile('/gcc/shared/main/${data.picture}', 'Student Photo')">
                                <div class="preview-container">
                                    <img src="/gcc/shared/main/${data.picture}" alt="Student Photo" class="preview-thumbnail">
                                    <div class="preview-overlay">
                                        <button class="preview-button">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </div>
                                <div class="file-info">
                                    <i class="fas fa-image"></i>
                                    <span>Student Photo</span>
                                </div>
                            </div>
                        ` : ''}
                        
                        ${data.grades ? `
                            <div class="file-preview-card" onclick="openPDF('/gcc/shared/main/${data.grades}', 'Student Grades')">
                                <div class="preview-container pdf-preview">
                                    <i class="fas fa-file-pdf"></i>
                                    <div class="preview-overlay">
                                        <button class="preview-button">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </div>
                                <div class="file-info">
                                    <i class="fas fa-file-alt"></i>
                                    <span>Grades</span>
                                </div>
                            </div>
                        ` : ''}
                        
                        ${data.cor ? `
                            <div class="file-preview-card" onclick="openPDF('/gcc/shared/main/${data.cor}', 'Certificate of Registration')">
                                <div class="preview-container pdf-preview">
                                    <i class="fas fa-file-pdf"></i>
                                    <div class="preview-overlay">
                                        <button class="preview-button">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </div>
                                <div class="file-info">
                                    <i class="fas fa-file-invoice"></i>
                                    <span>COR</span>
                                </div>
                            </div>
                        ` : ''}
                        
                        ${data.cet_result ? `
                            <div class="file-preview-card" onclick="openPDF('/gcc/shared/main/${data.cet_result}', 'CET Result')">
                                <div class="preview-container pdf-preview">
                                    <i class="fas fa-file-pdf"></i>
                                    <div class="preview-overlay">
                                        <button class="preview-button">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </div>
                                </div>
                                <div class="file-info">
                                    <i class="fas fa-file-contract"></i>
                                    <span>CET</span>
                                </div>
                            </div>
                        ` : ''}
                </div>
                </div>
            `;
            
            $('#modalDetails').html(modalContent);
            $('#viewModal').css('display', 'block');
        }

        function openPDF(url, title) {
            const absoluteUrl = new URL(url, window.location.origin).href;
            window.open(absoluteUrl, '_blank');
        }

        function viewFile(url, title) {
            if (url.match(/\.(jpg|jpeg|png|gif)$/i)) {
                const absoluteUrl = new URL(url, window.location.origin).href;
                window.open(absoluteUrl, '_blank');
            } else {
                openPDF(url, title);
            }
}
</script>

<style>
/* Modal Styles */
.confirmation-modal {
    max-width: 500px;
    width: 90%;
}

.modal-header .header-title {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #236641;
}

.modal-header .header-title i {
    font-size: 1.5rem;
}

.confirmation-message {
    font-size: 1.1rem;
    margin-bottom: 20px;
    color: #333;
}

.confirmation-details {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.detail-row {
    display: flex;
    margin-bottom: 10px;
    padding: 5px 0;
}

.detail-row:last-child {
    margin-bottom: 0;
}

.detail-row strong {
    width: 140px;
    color: #236641;
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.btn-cancel, .btn-confirm {
    padding: 8px 16px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.btn-cancel {
    background-color: #6c757d;
    color: white;
}

.btn-confirm {
    background-color: #236641;
    color: white;
}

.btn-cancel:hover, .btn-confirm:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
</style>
</body>
</html>