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
                           WHERE a.status = 'pending'");
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
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'approved', Staff_id = ? WHERE appointment_id = ?");
            $stmt->execute([$_SESSION['user_id'], $appointment_id]);
            $message = "Appointment approved successfully.";
        } elseif ($action == 'reschedule') {
            if (empty($_POST['new_date_' . $appointment_id])) {
                $error = "Please select a new date for rescheduling.";
            } elseif (empty($_POST['new_time_' . $appointment_id])) {
                $error = "Please select a new time for rescheduling.";
            } else {
                $new_date = $_POST['new_date_' . $appointment_id];
                $new_time = $_POST['new_time_' . $appointment_id];
                
                $stmt = $pdo->prepare("UPDATE appointments SET status = 'rescheduled', requested_date = ?, requested_time = ?, Staff_id = ? WHERE appointment_id = ?");
                $stmt->execute([$new_date, $new_time, $_SESSION['user_id'], $appointment_id]);
                $message = "Appointment rescheduled successfully.";
            }
        }
        
        $stmt = $pdo->prepare("SELECT * FROM appointments WHERE status = 'pending'");
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
    <title>GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="/gcc/js/action-modal.js"></script>
    <style>
        .success {
            color: green;
            padding: 10px;
            margin: 10px 0;
            background-color: #ddffdd;
            border: 1px solid green;
        }
        .error {
            color: red;
            padding: 10px;
            margin: 10px 0;
            background-color: #ffdddd;
            border: 1px solid red;
        }
        .reschedule-fields {
            display: none;
            margin-top: 5px;
        }
        .show-reschedule .reschedule-fields {
            display: block;
        }
    </style>
</head>
<body>
<div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="staff.php">Guidance and Counseling Center</a>
    </div>    
    <div class="container">
        <div style="background-color: #16633F; width: 100%; height: 150px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;">
            Check Appointment Requests
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div style="padding: 40px;">
            <table id="appointmentsTable" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Client ID</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Username</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Type</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Date</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Time</th>
                        <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                    <tr style="border: 1px solid #ddd;">
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['client_id']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['first_name'] . ' ' . $appointment['middle_name'] . ' ' . $appointment['last_name']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['appointment_type']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['requested_date']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;"><?php echo htmlspecialchars($appointment['requested_time']); ?></td>
                        <td style="padding: 12px; border: 1px solid #ddd;">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                <button type="submit" name="action" value="approve" style="padding: 8px 12px; margin-right: 5px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Approve</button>
                            </form>
                            
                            <button type="button" class="reschedule-btn" style="padding: 8px 12px; background-color: #2196F3; color: white; border: none; cursor: pointer;">Reschedule</button>
                            
                            <div class="reschedule-fields">
                                <form method="post" style="margin-top: 5px;">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                    <input type="date" name="new_date_<?php echo $appointment['appointment_id']; ?>" style="padding: 8px; margin-right: 5px;" required>
                                    <input type="time" name="new_time_<?php echo $appointment['appointment_id']; ?>" style="padding: 8px;" required>
                                    <button type="submit" name="action" value="reschedule" style="padding: 8px 12px; margin-left: 5px; background-color: #2196F3; color: white; border: none; cursor: pointer;">Submit</button>
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
        <div style="margin-right: 20px;"><img src="../../../../gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
    </footer>

    <script>
        $(document).ready(function() {
            $('#appointmentsTable').DataTable();
            
            // Toggle reschedule fields
            $('.reschedule-btn').click(function() {
                $(this).closest('td').find('.reschedule-fields').toggle();
            });
        });
        setTimeout(function() {
            document.getElementById('successMessage').style.display = 'none';
        }, 1000);
    </script>
</body>
</html>