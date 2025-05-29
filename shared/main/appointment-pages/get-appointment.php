<?php
require_once __DIR__ . '/../../../database/database.php';
session_start();

header('Content-Type: application/json');

// Debugging output
error_log('Session data: ' . print_r($_SESSION, true));

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'error' => 'Authentication required',
        'session_status' => session_status(),
        'session_data' => $_SESSION
    ]);
    exit();
}

try {
    $appointment_type = isset($_GET['type']) ? $_GET['type'] : 'counseling';

    // Get current appointment
    $stmt = $pdo->prepare("SELECT 
        appointment_id,
        requested_date,
        requested_time,
        status,
        appointment_type,
        client_id
        FROM appointments 
        WHERE client_id = :user_id 
        AND appointment_type = :appointment_type
        AND status NOT IN ('cancelled', 'completed')
        ORDER BY requested_date DESC, appointment_id DESC 
        LIMIT 1");

    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->bindParam(':appointment_type', $appointment_type, PDO::PARAM_STR);
    $stmt->execute();

    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        echo json_encode([
            'error' => 'No active appointments found',
            'user_id' => $_SESSION['user_id']
        ]);
        exit();
    }

    // Define available time slots
    $available_slots = ['8am - 9am', '9am - 10am', '10am - 11am', '2pm - 3pm', '3pm - 4pm', '4pm - 5pm'];

    echo json_encode([
        'success' => true,
        'appointment_id' => $appointment['appointment_id'],
        'requested_date' => $appointment['requested_date'],
        'requested_time' => $appointment['requested_time'],
        'status' => $appointment['status'],
        'appointment_type' => $appointment['appointment_type'],
        'client_id' => $appointment['client_id'],
        'available_slots' => $available_slots
    ]);
    
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
}
?>