<?php
require_once '../../database/database.php';

// Set proper headers
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Check if contact number was provided
if (!isset($_POST['contact_number']) || empty($_POST['contact_number'])) {
    echo json_encode(['error' => 'Contact number is required']);
    exit;
}

$contact_number = trim($_POST['contact_number']);

try {
    // Check if contact number exists in the database
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE contact_number = :contact_number");
    $stmt->execute([':contact_number' => $contact_number]);
    $count = $stmt->fetchColumn();
    
    // Return result
    echo json_encode(['exists' => ($count > 0)]);
} catch (PDOException $e) {
    // In case of database error
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
}
?> 