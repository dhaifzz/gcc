<?php
require_once '../../font/font.php';
require_once '../../database/database.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['evaluate'])) {
        $id = $_POST['id'];
        $status = 'evaluated';
    } elseif (isset($_POST['decline'])) {
        $id = $_POST['id'];
        $status = 'declined';
    } elseif (isset($_POST['complete'])) {
        $id = $_POST['id'];
        $status = 'completed';
    } elseif (isset($_POST['cancel'])) {
        $id = $_POST['id'];
        $status = 'cancelled';
    }

    if (isset($id) && isset($status)) {
        $stmt = $pdo->prepare("UPDATE shifting SET status = :status, approved_by = :staff_id WHERE id = :id");
        $stmt->execute([
            'status' => $status, 
            'id' => $id,
            'staff_id' => $_SESSION['user_id']
        ]);

        if ($stmt->rowCount() > 0) {
            // Get student details for the message
            $studentStmt = $pdo->prepare("SELECT first_name, last_name FROM shifting WHERE id = ?");
            $studentStmt->execute([$id]);
            $student = $studentStmt->fetch(PDO::FETCH_ASSOC);
            $studentName = $student['first_name'] . ' ' . $student['last_name'];
            
            $message = '';
            switch($status) {
                case 'evaluated':
                    $message = "Request for $studentName has been evaluated and sent to director.";
                    break;
                case 'declined':
                    $message = "Request for $studentName has been declined.";
                    break;
                case 'completed':
                    $message = "Request for $studentName has been completed successfully.";
                    break;
                case 'cancelled':
                    $message = "Request for $studentName has been cancelled.";
                    break;
            }
            
            echo "<script>alert('$message');</script>";
        } else {
            echo '<script>alert("Error processing request.");</script>';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC Staff - Shifting Requests</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" type="text/css" href="css/filter-modal.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
            border: 1px solid #FFEEBA;
        }
        
        .status-evaluated {
            background-color: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }
        
        .status-declined {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-approved {
            background-color: #cce5ff;
            color: #004085;
            border: 1px solid #b8daff;
        }

        .table-actions {
            display: flex;
            flex-direction: row;
            gap: 5px;
            align-items: center;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .view-btn {
            background-color: #0056b3;
            color: white;
        }

        .view-btn:hover {
            background-color: #004494;
        }

        .evaluate-btn {
            background-color: #2B6B48;
            color: white;
        }

        .evaluate-btn:hover {
            background-color: #235c3d;
        }

        .decline-btn {
            background-color: #dc3545;
            color: white;
        }

        .decline-btn:hover {
            background-color: #c82333;
        }

        /* Add styles for the new buttons */
        .complete-btn {
            background-color: #28a745;
            color: white;
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

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #fff;
            margin: 2% auto;
            padding: 0;
            border-radius: 8px;
            width: 95%;
            max-width: 1400px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background-color: #2B6B48;
            color: white;
            padding: 20px 30px;
            border-radius: 12px 12px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .close:hover {
            opacity: 0.7;
        }

        .modal-body {
            padding: 30px;
            max-height: 80vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .detail-section, .files-section {
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            width: 100%;
            box-sizing: border-box;
        }

        .detail-section {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }

        .detail-group {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .detail-label {
            font-weight: 600;
            color: #2B6B48;
            margin-bottom: 8px;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            color: #333;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            font-size: 1.1rem;
            line-height: 1.5;
        }

        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .file-preview-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
        }

        .file-preview-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .preview-container {
            position: relative;
            width: 100%;
            height: 150px;
            background: #f8f9fa;
            overflow: hidden;
        }

        .preview-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .file-preview-card:hover .preview-overlay {
            opacity: 1;
        }

        .preview-button {
            background: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s;
        }

        .preview-button:hover {
            transform: scale(1.05);
        }

        .file-info {
            padding: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-top: 1px solid #e9ecef;
        }

        .file-info i {
            color: #2B6B48;
        }

        .file-info span {
            font-size: 0.9rem;
            color: #495057;
        }

        /* Confirmation Modal Styles */
        .confirmation-modal {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            width: 500px;
            margin: auto;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .confirmation-modal .modal-content {
            width: 100%;
            margin: 0;
            border-radius: 8px;
        }

        .confirmation-modal .modal-header {
            background: #236641;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: none;
            border-radius: 8px 8px 0 0;
        }

        .confirmation-modal .modal-header h2 {
            margin: 0;
            font-size: 1.2rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .confirmation-modal .modal-header .close {
            color: white;
            opacity: 0.8;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            margin: 0;
            transition: opacity 0.2s;
        }

        .confirmation-modal .modal-header .close:hover {
            opacity: 1;
        }

        .confirmation-modal .modal-body {
            padding: 20px;
            max-height: none;
        }

        .confirmation-message {
            color: #333;
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .confirmation-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            margin-bottom: 20px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            align-items: center;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-row strong {
            width: 80px;
            color: #236641;
            font-weight: 600;
        }

        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 20px;
        }

        .modal-actions form {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }

        /* Specific modal styles */
        #evaluateModal, #declineModal {
            padding: 20px;
        }

        #evaluateModal .confirmation-modal,
        #declineModal .confirmation-modal {
            width: 400px;
            max-width: 90%;
        }

        /* Loading animation */
        .loading {
            display: inline-block;
            width: 50px;
            height: 50px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Loading Container Styles */
        .loading-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #2B6B48;
        }

        .loading-container p {
            margin-top: 15px;
            font-size: 1.1rem;
            color: #495057;
        }

        /* Error Message Styles */
        .error-message {
            text-align: center;
            color: #dc3545;
            padding: 20px;
        }

        .error-message i {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .error-message p {
            font-size: 1.1rem;
            margin-bottom: 15px;
        }

        /* Button Styles */
        .btn-cancel, .btn-confirm {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
            border: none;
            min-width: 100px;
            justify-content: center;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-confirm {
            background-color: #236641;
            color: white;
        }

        /* Evaluate Modal Specific Styles */
        #evaluateModal .btn-confirm {
            background: linear-gradient(145deg, #236641, #1a4d31);
        }

        #evaluateModal .btn-confirm:hover {
            background: linear-gradient(145deg, #1a4d31, #236641);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(35, 102, 65, 0.3);
        }

        /* Decline Modal Specific Styles */
        #declineModal .btn-confirm {
            background: linear-gradient(145deg, #dc3545, #c82333);
        }

        #declineModal .btn-confirm:hover {
            background: linear-gradient(145deg, #c82333, #dc3545);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
        }

        #declineModal .modal-header {
            background: linear-gradient(145deg, #dc3545, #c82333);
        }

        .btn-cancel:hover {
            background: linear-gradient(145deg, #5a6268, #6c757d);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }

        /* Active state for buttons */
        .btn-cancel:active, .btn-confirm:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
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
            <a href="staff-shifting.php" style="background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-solid fa-envelope"></i> Shifting Table</a>
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
            <h1>Shifting Requests</h1>
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

        <div class="data-table-container">
            <?php
            // Fetch shifting requests - both pending and approved status
            $stmt = $pdo->query("SELECT s.id, s.user_id, s.first_name, s.middle_name, s.last_name, s.current_course, s.course_to_shift, s.created_at, s.picture, s.grades, s.cor, s.cet_result, s.status 
                                FROM shifting s 
                               WHERE s.status IN ('pending', 'approved')
                               ORDER BY s.created_at DESC");
            $shifting_requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($shifting_requests)): ?>
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h3>No Pending Shifting Requests</h3>
                    <p>There are currently no shifting requests awaiting evaluation.</p>
                </div>
            <?php else: ?>
                <table id="shiftingTable" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>School ID</th>
                            <th>Name</th>
                            <th>Current Course</th>
                            <th>Desired Course</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($shifting_requests as $row): 
                        // Properly handle middle name initial
                        $middleInitial = !empty($row['middle_name']) ? ' ' . strtoupper($row['middle_name'][0]) . '. ' : ' ';
                        $fullName = htmlspecialchars($row['first_name'] . $middleInitial . $row['last_name']);
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                            <td><?php echo $fullName; ?></td>
                            <td><?php echo htmlspecialchars($row['current_course']); ?></td>
                            <td><?php echo htmlspecialchars($row['course_to_shift']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                    <?php echo ucfirst(htmlspecialchars($row['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button class='action-btn view-btn' onclick='viewRegistration(<?php echo json_encode([
                                        'id' => $row['id'],
                                        'picture' => $row['picture'],
                                        'grades' => $row['grades'],
                                        'cor' => $row['cor'],
                                        'cet_result' => $row['cet_result'],
                                        'fullName' => $fullName,
                                        'user_id' => $row['user_id'],
                                        'current_course' => $row['current_course'],
                                        'course_to_shift' => $row['course_to_shift'],
                                        'created_at' => date('M j, Y', strtotime($row['created_at']))
                                    ]); ?>)'>
                                        <i class='fas fa-eye'></i> View
                                    </button>
                                    <?php if ($row['status'] === 'pending'): ?>
                                    <button class='action-btn evaluate-btn' type='button'
                                        data-id='<?php echo $row['id']; ?>'
                                        data-name='<?php echo $fullName; ?>'
                                        data-current='<?php echo htmlspecialchars($row['current_course']); ?>'
                                        data-desired='<?php echo htmlspecialchars($row['course_to_shift']); ?>'>
                                        <i class='fas fa-check'></i> Evaluate
                                    </button>
                                    <button class='action-btn decline-btn' type='button'
                                        data-id='<?php echo $row['id']; ?>'
                                        data-name='<?php echo $fullName; ?>'
                                        data-current='<?php echo htmlspecialchars($row['current_course']); ?>'
                                        data-desired='<?php echo htmlspecialchars($row['course_to_shift']); ?>'>
                                        <i class='fas fa-times'></i> Decline
                                    </button>
                                    <?php elseif ($row['status'] === 'approved'): ?>
                                        <button class='action-btn complete-btn' type='button'
                                            data-id='<?php echo $row['id']; ?>'
                                            data-name='<?php echo $fullName; ?>'
                                            data-current='<?php echo htmlspecialchars($row['current_course']); ?>'
                                            data-desired='<?php echo htmlspecialchars($row['course_to_shift']); ?>'>
                                            <i class='fas fa-check-double'></i> Complete
                                        </button>
                                        <button class='action-btn cancel-btn' type='button'
                                            data-id='<?php echo $row['id']; ?>'
                                            data-name='<?php echo $fullName; ?>'
                                            data-current='<?php echo htmlspecialchars($row['current_course']); ?>'
                                            data-desired='<?php echo htmlspecialchars($row['course_to_shift']); ?>'>
                                            <i class='fas fa-ban'></i> Cancel
                                        </button>
                                    <?php endif; ?>
                                </div>
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
                <h2><i class="fas fa-filter"></i> Filter Shifting Requests</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="filterForm">
                    <div class="form-group">
                        <label for="studentNameFilter">
                            <i class="fas fa-user"></i> Student Name
                        </label>
                        <input type="text" id="studentNameFilter" class="form-control" placeholder="Search by student name...">
                    </div>

                    <div class="form-group">
                        <label for="statusFilter">
                            <i class="fas fa-tag"></i> Status
                        </label>
                        <select id="statusFilter" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                        </select>
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

    <!-- Evaluate Confirmation Modal -->
    <div id="evaluateModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <div class="header-title">
                    <i class="fas fa-check-circle"></i>
                    <span>Confirm Evaluation</span>
                </div>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="confirmation-message">Are you sure you want to evaluate the shifting request for:</p>
                <div class="confirmation-details">
                    <div class="detail-row">
                        <strong>Client:</strong>
                        <span id="evaluateClientName"></span>
                    </div>
                    <div class="detail-row">
                        <strong>From:</strong>
                        <span id="evaluateCurrentCourse"></span>
                    </div>
                    <div class="detail-row">
                        <strong>To:</strong>
                        <span id="evaluateDesiredCourse"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <form method="post" id="evaluateForm">
                        <input type="hidden" name="id" id="evaluateRequestId">
                        <input type="hidden" name="evaluate" value="1">
                        <button type="button" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-confirm">
                            <i class="fas fa-check"></i> Confirm Evaluation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Decline Confirmation Modal -->
    <div id="declineModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <div class="header-title">
                    <i class="fas fa-times-circle"></i>
                    <span>Confirm Decline</span>
                </div>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="confirmation-message">Are you sure you want to decline the shifting request for:</p>
                <div class="confirmation-details">
                    <div class="detail-row">
                        <strong>Client:</strong>
                        <span id="declineClientName"></span>
                    </div>
                    <div class="detail-row">
                        <strong>From:</strong>
                        <span id="declineCurrentCourse"></span>
                    </div>
                    <div class="detail-row">
                        <strong>To:</strong>
                        <span id="declineDesiredCourse"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <form method="post" id="declineForm">
                        <input type="hidden" name="id" id="declineRequestId">
                        <input type="hidden" name="decline" value="1">
                        <button type="button" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-confirm">
                            <i class="fas fa-check"></i> Confirm Decline
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Confirmation Modal -->
    <div id="completeModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <div class="header-title">
                    <i class="fas fa-check-double"></i>
                    <span>Confirm Completion</span>
                </div>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="confirmation-message">Are you sure you want to mark this shifting request as completed for:</p>
                <div class="confirmation-details">
                    <div class="detail-row">
                        <strong>Client:</strong>
                        <span id="completeClientName"></span>
                    </div>
                    <div class="detail-row">
                        <strong>From:</strong>
                        <span id="completeCurrentCourse"></span>
                    </div>
                    <div class="detail-row">
                        <strong>To:</strong>
                        <span id="completeDesiredCourse"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <form method="post" id="completeForm">
                        <input type="hidden" name="id" id="completeRequestId">
                        <input type="hidden" name="complete" value="1">
                        <button type="button" class="btn-cancel">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn-confirm">
                            <i class="fas fa-check"></i> Confirm Completion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancel Confirmation Modal -->
    <div id="cancelModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <div class="header-title">
                    <i class="fas fa-ban"></i>
                    <span>Confirm Cancellation</span>
                </div>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body">
                <p class="confirmation-message">Are you sure you want to cancel this shifting request for:</p>
                <div class="confirmation-details">
                    <div class="detail-row">
                        <strong>Client:</strong>
                        <span id="cancelClientName"></span>
                    </div>
                    <div class="detail-row">
                        <strong>From:</strong>
                        <span id="cancelCurrentCourse"></span>
                    </div>
                    <div class="detail-row">
                        <strong>To:</strong>
                        <span id="cancelDesiredCourse"></span>
                    </div>
                </div>
                <div class="modal-actions">
                    <form method="post" id="cancelForm">
                        <input type="hidden" name="id" id="cancelRequestId">
                        <input type="hidden" name="cancel" value="1">
                        <button type="button" class="btn-cancel">
                            <i class="fas fa-times"></i> Back
                        </button>
                        <button type="submit" class="btn-confirm">
                            <i class="fas fa-check"></i> Confirm Cancellation
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Keep your existing modals -->
    <?php include('modals/view-shifting-modal.php'); ?>
    <?php include('modals/file-viewer-modal.php'); ?>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#shiftingTable').DataTable({
                responsive: true,
                dom: '<"top">rt<"bottom"ip><"clear">',
                language: {
                    emptyTable: "No shifting requests available",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    paginate: {
                        first: '<i class="fas fa-angle-double-left"></i>',
                        last: '<i class="fas fa-angle-double-right"></i>',
                        next: '<i class="fas fa-angle-right"></i>',
                        previous: '<i class="fas fa-angle-left"></i>'
                    }
                },
                order: [[4, 'desc']], // Sort by date by default
                pageLength: 10,
                lengthChange: false
            });

            // Real-time search functionality
            $('#tableSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            // Filter modal functionality
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
                
                var studentName = $('#studentNameFilter').val().toLowerCase();
                var status = $('#statusFilter').val().toLowerCase();
                var dateFrom = $('#dateFrom').val();
                var dateTo = $('#dateTo').val();

                // Clear existing custom filters
                $.fn.dataTable.ext.search.pop();

                // Apply all filters
                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var rowName = data[1].toLowerCase();
                    var rowStatus = data[5].toLowerCase();
                    var rowDate = new Date(data[4]);
                    
                    if (studentName && !rowName.includes(studentName)) return false;
                    if (status && !rowStatus.includes(status)) return false;
                    
                    if (dateFrom && new Date(dateFrom) > rowDate) return false;
                    if (dateTo && new Date(dateTo) < rowDate) return false;
                    
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

            // Evaluate button click handler
            $(document).on('click', '.evaluate-btn', function() {
                var id = $(this).data('id');
                var clientName = $(this).data('name');
                var currentCourse = $(this).data('current');
                var desiredCourse = $(this).data('desired');

                $('#evaluateRequestId').val(id);
                $('#evaluateClientName').text(clientName);
                $('#evaluateCurrentCourse').text(currentCourse);
                $('#evaluateDesiredCourse').text(desiredCourse);
                
                $('#evaluateModal').css('display', 'block');
            });

            // Decline button click handler
            $(document).on('click', '.decline-btn', function() {
                var id = $(this).data('id');
                var clientName = $(this).data('name');
                var currentCourse = $(this).data('current');
                var desiredCourse = $(this).data('desired');

                $('#declineRequestId').val(id);
                $('#declineClientName').text(clientName);
                $('#declineCurrentCourse').text(currentCourse);
                $('#declineDesiredCourse').text(desiredCourse);
                
                $('#declineModal').css('display', 'block');
            });

            // Complete button click handler
            $(document).on('click', '.complete-btn', function() {
                var id = $(this).data('id');
                var clientName = $(this).data('name');
                var currentCourse = $(this).data('current');
                var desiredCourse = $(this).data('desired');

                $('#completeRequestId').val(id);
                $('#completeClientName').text(clientName);
                $('#completeCurrentCourse').text(currentCourse);
                $('#completeDesiredCourse').text(desiredCourse);
                
                $('#completeModal').css('display', 'block');
            });

            // Cancel button click handler
            $(document).on('click', '.cancel-btn', function() {
                var id = $(this).data('id');
                var clientName = $(this).data('name');
                var currentCourse = $(this).data('current');
                var desiredCourse = $(this).data('desired');

                $('#cancelRequestId').val(id);
                $('#cancelClientName').text(clientName);
                $('#cancelCurrentCourse').text(currentCourse);
                $('#cancelDesiredCourse').text(desiredCourse);
                
                $('#cancelModal').css('display', 'block');
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
                        <div class="detail-value">${data.user_id}</div>
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
                </div>

                <div class="files-section">
                    <div class="detail-label">
                        <i class="fas fa-file-upload"></i> Uploaded Documents
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
            // Get the absolute URL
            const absoluteUrl = new URL(url, window.location.origin).href;
            
            // Open PDF in a new tab using browser's PDF viewer
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
</body>
</html>
