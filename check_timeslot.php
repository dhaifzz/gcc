<?php
require_once 'config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $appointmentType = $_POST['appointment_type'] ?? '';

    try {
        $query = "SELECT appointment_id, status FROM appointments 
                 WHERE requested_date = :date 
                 AND time_slot = :time 
                 AND appointment_type = :appointment_type";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'date' => $date,
            'time' => $time,
            'appointment_type' => $appointmentType
        ]);

        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'appointments' => $appointments
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
} 