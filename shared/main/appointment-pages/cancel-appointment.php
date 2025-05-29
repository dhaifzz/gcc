<?php
require_once '../../../database/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $appointment_id = $_POST['appointment_id'];
    // Optionally, check appointment type here if needed

    $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE appointment_id = ?");
    $stmt->execute([$appointment_id]);

    echo json_encode(['success' => true]);
    exit();
}
echo json_encode(['error' => 'Invalid request']);
exit();
?>