<?php
require_once __DIR__ . '/../../../database/database.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Authentication required']);
    exit();
}

try {
    $date = $_POST['date'] ?? null;
    $appointment_type = $_POST['appointment_type'] ?? null;
    $appointment_id = $_POST['appointment_id'] ?? null;
    $check_time_slots = isset($_POST['check_time_slots']) && $_POST['check_time_slots'] === 'true';

    if (!$date) {
        echo json_encode(['error' => 'Date is required']);
        exit();
    }

    // Define working hours
    $working_hours = ['8am - 9am', '9am - 10am', '10am - 11am', '2pm - 3pm', '3pm - 4pm', '4pm - 5pm'];

    // Check if it's a weekend
    $dayOfWeek = date('w', strtotime($date));
    if ($dayOfWeek == 0 || $dayOfWeek == 6) {
        echo json_encode([
            'error' => 'Weekend dates are not available',
            'available_slots' => 0
        ]);
        exit();
    }

    // Check if date is in the past
    if (strtotime($date) < strtotime(date('Y-m-d'))) {
        echo json_encode([
            'error' => 'Past dates are not available',
            'available_slots' => 0
        ]);
        exit();
    }

    if ($check_time_slots) {
        // Get booked slots for the date
        $stmt = $pdo->prepare("
            SELECT a.requested_time, a.appointment_type, a.client_id, a.appointment_id 
            FROM appointments a 
            WHERE a.requested_date = :date 
            AND a.status NOT IN ('cancelled', 'completed')
        ");
        $stmt->execute(['date' => $date]);
        $booked_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format booked slots for response
        $formatted_booked_slots = array_map(function($slot) {
            return [
                'time' => $slot['requested_time'],
                'type' => $slot['appointment_type'],
                'client_id' => $slot['client_id'],
                'appointment_id' => $slot['appointment_id']
            ];
        }, $booked_slots);

        // Calculate available slots
        $available_count = count($working_hours) - count($booked_slots);

        echo json_encode([
            'success' => true,
            'available_slots' => $available_count,
            'booked_slots' => $formatted_booked_slots,
            'working_hours' => $working_hours
        ]);
    } else {
        // Just check if the day is available
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as booked_count 
            FROM appointments 
            WHERE requested_date = :date 
            AND status NOT IN ('cancelled', 'completed')
        ");
        $stmt->execute(['date' => $date]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $available_slots = count($working_hours) - $result['booked_count'];

        echo json_encode([
            'success' => true,
            'available_slots' => $available_slots
        ]);
    }

} catch (PDOException $e) {
    error_log('Database Error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'Database error occurred',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log('General Error: ' . $e->getMessage());
    echo json_encode([
        'error' => 'An error occurred',
        'details' => $e->getMessage()
    ]);
}
?>