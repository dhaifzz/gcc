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

// Fetch appointments approved by staff
$appointments = [];
try {
    $stmt = $pdo->prepare("SELECT a.*, u.first_name, u.last_name 
                          FROM appointments a
                          JOIN users u ON a.Staff_id = u.id
                          WHERE a.status = 'approved'");
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission for director approval/rejection
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $appointment_id = $_POST['appointment_id'];
    $action = $_POST['action'];
    
    try {
        if ($action == 'final_approve') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'final_approved', Director_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment finally approved successfully.";
        } elseif ($action == 'reject') {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'rejected', Director_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment rejected successfully.";
        }
        
        // Refresh appointments after update
        $stmt = $pdo->prepare("SELECT a.*, u.first_name, u.last_name 
                              FROM appointments a
                              JOIN users u ON a.Staff_id = u.id
                              WHERE a.status = 'approved'");
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
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC- Director</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/table-director.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        .success {
            color: green;
            padding: 10px;
            margin: 10px 0;
            background-color: #ddffdd;
            border: 1px solid green;
            transition: opacity 0.5s ease-out;
        }
        .error {
            color: red;
            padding: 10px;
            margin: 10px 0;
            background-color: #ffdddd;
            border: 1px solid red;
            transition: opacity 0.5s ease-out;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .approve-btn {
            background-color: #11AD64 !important;
        }
        .reject-btn {
            background-color: #f44336 !important;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
        <a class="website" href="director.php">Guidance and Counseling Center - Director</a>
    </div>    
    <div class="container">
        <div style="background-color: #16633F; width: 100%; height: 150px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;">
            Staff-Approved Appointments
        </div>
        
        <?php if ($error): ?>
            <div class="error" id="errorMessage"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="success" id="successMessage"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div style="padding: 40px;">
            <table id="appointmentsTable" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Client ID</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Type</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Date</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Time</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Approved By Staff</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Director Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                    <tr style="border: 1px solid #ddd;">
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['client_id']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['appointment_type']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['requested_date']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['requested_time']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;">
                            <?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>
                        </td>
                        <td style="padding: 12px; border: 1px solid #ddd;">
                            <div class="action-buttons">
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                    <button type="submit" name="action" value="final_approve" class="approve-btn" style="padding: 8px 12px; color: white; border: none; cursor: pointer;">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                    <button type="submit" name="action" value="reject" class="reject-btn" style="padding: 8px 12px; color: white; border: none; cursor: pointer;">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
        <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
        <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
    </footer>

    <script>
        $(document).ready(function() {
            $('#appointmentsTable').DataTable();
            
            setTimeout(function() {
                $('#successMessage, #errorMessage').fadeOut('slow');
            }, 3000);
        });
    </script>
</body>
</html> 