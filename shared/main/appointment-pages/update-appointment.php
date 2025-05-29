<?php
require_once '../../../database/database.php';
session_start();

header('Content-Type: application/json');

// Validate session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit();
}

// Get input data
$appointmentId = $_POST['appointment_id'] ?? null;
$requestedDate = $_POST['requested_date'] ?? null;
$requestedTime = $_POST['requested_time'] ?? null;

// Validate inputs
if (!$appointmentId || !$requestedDate || !$requestedTime) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

try {
    // Verify appointment belongs to user
    $stmt = $pdo->prepare("SELECT client_id FROM appointments WHERE appointment_id = ?");
    $stmt->execute([$appointmentId]);
    $appointment = $stmt->fetch();
    
    if (!$appointment || $appointment['client_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'error' => 'Invalid appointment']);
        exit();
    }
    
    // Validate date is not in past
    $today = new DateTime();
    $appointmentDate = new DateTime($requestedDate);
    if ($appointmentDate < $today) {
        echo json_encode(['success' => false, 'error' => 'Cannot set appointment for past date']);
        exit();
    }
    
    // Validate date is not weekend
    if ($appointmentDate->format('N') >= 6) { // 6 or 7 is weekend
        echo json_encode(['success' => false, 'error' => 'Weekend appointments not available']);
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();

    // Update the appointment
    $stmt = $pdo->prepare("UPDATE appointments SET 
        requested_date = :date,
        requested_time = :time,
        status = 'Pending',
        updated_at = NOW()
        WHERE appointment_id = :id");
    
    $success = $stmt->execute([
        ':date' => $requestedDate,
        ':time' => $requestedTime,
        ':id' => $appointmentId
    ]);
    
    if ($success) {
        $pdo->commit();
        echo json_encode(['success' => true]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Database update failed']);
    }
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'A system error occurred']);
}