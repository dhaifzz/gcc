<?php
require_once '../../font/font.php';
require_once('../../database/database.php');
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Director') {
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
        'rejected' => 'Rejected',
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

// Initialize variables for filtering and search
$appointmentType = $_GET['appointment_type'] ?? '';
$clientName = $_GET['client_name'] ?? '';
$status = $_GET['status'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Prepare the SQL query with filters and search for appointments
$sql = "
    SELECT 
        'Appointment' AS type, 
        a.appointment_type, 
        u.first_name AS client_first_name, 
        u.last_name AS client_last_name,
        staff.first_name AS staff_first_name,
        staff.last_name AS staff_last_name,
        a.requested_date, 
        a.requested_time, 
        a.status,
        a.created_at
    FROM appointments a 
    JOIN users u ON a.client_id = u.id 
    LEFT JOIN users staff ON a.Staff_id = staff.id
    WHERE a.status IN ('evaluated', 'rescheduled', 'completed', 'declined')
";

// SQL for shifting requests
$shiftingSql = "
    SELECT 
        'Shifting' AS type, 
        s.course_to_shift AS appointment_type, 
        s.first_name AS client_first_name,
        s.last_name AS client_last_name,
        staff.first_name AS staff_first_name,
        staff.last_name AS staff_last_name,
        s.submitted_at AS requested_date, 
        '' AS requested_time, 
        s.status,
        s.created_at
    FROM shifting s 
    LEFT JOIN users staff ON s.approved_by = staff.id 
    WHERE s.status IN ('evaluated', 'declined', 'completed', 'rejected')
";

$parameters = [];
$shiftingParameters = [];

// Add search term condition
if ($searchTerm) {
    $sql .= " AND (u.first_name LIKE :searchTerm OR u.last_name LIKE :searchTerm OR a.appointment_type LIKE :searchTerm)";
    $parameters['searchTerm'] = "%$searchTerm%";
    $shiftingSql .= " AND (s.first_name LIKE :searchTerm OR s.last_name LIKE :searchTerm OR s.course_to_shift LIKE :searchTerm)";
    $shiftingParameters['searchTerm'] = "%$searchTerm%";
}

// Add filtering conditions for appointments
if ($appointmentType) {
    $sql .= " AND a.appointment_type = :appointment_type";
    $parameters['appointment_type'] = $appointmentType;
}
if ($clientName) {
    $sql .= " AND (u.first_name LIKE :client_name OR u.last_name LIKE :client_name)";
    $parameters['client_name'] = "%$clientName%";
}
if ($status) {
    $sql .= " AND a.status = :status";
    $parameters['status'] = $status;
}

// Add filtering conditions for shifting
if ($appointmentType) {
    $shiftingSql .= " AND s.course_to_shift = :appointment_type";
    $shiftingParameters['appointment_type'] = $appointmentType;
}
if ($clientName) {
    $shiftingSql .= " AND (s.first_name LIKE :client_name OR s.last_name LIKE :client_name)";
    $shiftingParameters['client_name'] = "%$clientName%";
}
if ($status) {
    $shiftingSql .= " AND s.status = :status";
    $shiftingParameters['status'] = $status;
}

// Order by creation date
$sql .= " ORDER BY a.created_at DESC";
$shiftingSql .= " ORDER BY s.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($parameters);
    $appointmentResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $shiftingStmt = $pdo->prepare($shiftingSql);
    $shiftingStmt->execute($shiftingParameters);
    $shiftingResults = $shiftingStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $results = array_merge($appointmentResults, $shiftingResults);
    
    // Sort combined results by created_at in descending order
    usort($results, function($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC Director - History</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/director-history.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <style>
        .filter-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            z-index: 1001;
            width: 400px;
            max-width: 90%;
        }
        
        .filter-modal h3 {
            margin-top: 0;
            color: #236641;
        }
        
        .filter-modal-content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .filter-modal-content input,
        .filter-modal-content select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        .filter-modal-content label {
            font-weight: 500;
            margin-bottom: 4px;
            display: block;
        }
        
        .filter-modal-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1rem;
        }

        .filter-modal-buttons button {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }

        .filter-modal-buttons button[type="submit"] {
            background-color: #236641;
            color: white;
        }

        .filter-modal-buttons button[type="button"] {
            background-color: #ddd;
        }
        
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .search-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 1rem;
        }
        
        .filter-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="director-dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="director-counseling.php"><i class="fa-regular fa-calendar-days"></i>Counseling Table</a>
            <a href="director-assessment.php"><i class="fa-regular fa-calendar"></i> Assessment Table</a>           
            <a href="director-shifting.php"><i class="fa-solid fa-envelope"></i> Shifting Table</a>
            <a href="director-history.php" style="background-color: rgb(255, 255, 255); color: #236641;"><i class="fa fa-history"></i> History</a>
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="sidebar-footer">
            <small>© 2025 WMSU </small>
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        </div>
    </div>  

    <div class="main-content">
        <div class="container">
            <div class="typing-container">
                <span class="typing-text">Transaction History</span>
            </div>
            
            <div class="filter-container">
                <button id="filter-button" class="filter-btn">
                    <i class="fas fa-filter"></i> Filter
                </button>
                
                <div class="search-container">
                    <form id="search-form" method="GET">
                        <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button type="submit"><i class="fas fa-search"></i> Search</button>
                    </form>
                </div>
            </div>
            
            <div class="modal-overlay" id="modal-overlay"></div>
            <div class="filter-modal" id="filter-modal">
                <h3>Filter Options</h3>
                <form id="filter-form" method="GET" class="filter-modal-content">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    
                    <div>
                        <label for="appointment-type-filter">Type:</label>
                        <select name="appointment_type" id="appointment-type-filter">
                            <option value="">All Types</option>
                            <option value="counseling" <?php echo $appointmentType == 'counseling' ? 'selected' : ''; ?>>Counseling</option>
                            <option value="assessment" <?php echo $appointmentType == 'assessment' ? 'selected' : ''; ?>>Assessment</option>
                            <option value="shifting" <?php echo $appointmentType == 'shifting' ? 'selected' : ''; ?>>Shifting</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="client-name-filter">Client Name:</label>
                        <input type="text" name="client_name" id="client-name-filter" placeholder="Client Name" value="<?php echo htmlspecialchars($clientName); ?>">
                    </div>
                    
                    <div>
                        <label for="status-filter">Status:</label>
                        <select name="status" id="status-filter">
                            <option value="">All Statuses</option>
                            <option value="evaluated" <?php echo $status == 'evaluated' ? 'selected' : ''; ?>>Evaluated</option>
                            <option value="declined" <?php echo $status == 'declined' ? 'selected' : ''; ?>>Declined</option>
                            <option value="completed" <?php echo $status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="rejected" <?php echo $status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                    </div>
                    
                    <div class="filter-modal-buttons">
                        <button type="button" id="cancel-filter">Cancel</button>
                        <button type="submit">Apply Filters</button>
                    </div>
                </form>
            </div>
            
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Request Type</th>
                        <th>Client</th>
                        <th>Staff</th>
                        <th>Date and Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?php echo capitalizeWords(htmlspecialchars($row['type'])); ?></td>
                            <td><?php echo capitalizeWords(htmlspecialchars($row['appointment_type'])); ?></td>
                            <td><?php echo capitalizeWords(htmlspecialchars($row['client_first_name'] . " " . $row['client_last_name'])); ?></td>
                            <td><?php 
                                if ($row['staff_first_name'] && $row['staff_last_name']) {
                                    echo capitalizeWords(htmlspecialchars($row['staff_first_name'] . " " . $row['staff_last_name']));
                                } else {
                                    echo "Not Assigned";
                                }
                            ?></td>
                            <td><?php echo htmlspecialchars($row['requested_date'] . " " . $row['requested_time']); ?></td>
                            <td><?php echo capitalizeWords(htmlspecialchars($row['status'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Filter modal functionality
        const filterButton = document.getElementById('filter-button');
        const filterModal = document.getElementById('filter-modal');
        const modalOverlay = document.getElementById('modal-overlay');
        const cancelFilter = document.getElementById('cancel-filter');
        
        filterButton.addEventListener('click', () => {
            filterModal.style.display = 'block';
            modalOverlay.style.display = 'block';
        });
        
        cancelFilter.addEventListener('click', () => {
            filterModal.style.display = 'none';
            modalOverlay.style.display = 'none';
        });
        
        modalOverlay.addEventListener('click', () => {
            filterModal.style.display = 'none';
            modalOverlay.style.display = 'none';
        });
    </script>
</body>
</html>
