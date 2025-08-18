<?php
require_once '../../../database/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';
$appointmentId = $_POST['appointment_id'] ?? 0;

try {
    if ($action === 'update') {
        // Verify the appointment belongs to the user
        $stmt = $pdo->prepare("SELECT client_id FROM appointments WHERE appointment_id = ?");
        $stmt->execute([$appointmentId]);
        $appointment = $stmt->fetch();
        
        if (!$appointment || $appointment['client_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'error' => 'Invalid appointment']);
            exit();
        }
        
        // Update the appointment
        $stmt = $pdo->prepare("UPDATE appointments SET 
            requested_date = ?, 
            requested_time = ?, 
            status = 'Pending' 
            WHERE appointment_id = ?");
        
        $success = $stmt->execute([
            $_POST['requested_date'],
            $_POST['requested_time'],
            $appointmentId
        ]);
        
        echo json_encode(['success' => $success]);
        
    } elseif ($action === 'cancel') {
        // Verify the appointment belongs to the user
        $stmt = $pdo->prepare("SELECT client_id FROM appointments WHERE appointment_id = ?");
        $stmt->execute([$appointmentId]);
        $appointment = $stmt->fetch();
        
        if (!$appointment || $appointment['client_id'] != $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'error' => 'Invalid appointment']);
            exit();
        }
        
        // Cancel the appointment
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'Cancelled' WHERE appointment_id = ?");
        $success = $stmt->execute([$appointmentId]);
        
        echo json_encode(['success' => $success]);
        
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>