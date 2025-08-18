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
    $stmt = $pdo->prepare("SELECT 
        appointment_id,
        requested_date,
        requested_time,
        status
        FROM appointments 
        WHERE client_id = :user_id
        ORDER BY requested_date DESC, appointment_id DESC 
        LIMIT 1");
    
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$appointment) {
        echo json_encode([
            'error' => 'No appointments found',
            'user_id' => $_SESSION['user_id']
        ]);
        exit();
    }
    
    echo json_encode([
        'success' => true,
        'appointment_id' => $appointment['appointment_id'],
        'requested_date' => $appointment['requested_date'],
        'requested_time' => $appointment['requested_time'],
        'status' => $appointment['status']
    ]);
    
} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage()
    ]);
}
?>