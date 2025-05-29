<?php
require_once '../../database/database.php';

// Set proper headers
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Check if email was provided
if (!isset($_POST['email']) || empty($_POST['email'])) {
    echo json_encode(['error' => 'Email is required']);
    exit;
}

$email = trim($_POST['email']);

try {
    // Check if email exists in the database
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $count = $stmt->fetchColumn();
    
    // Return result
    echo json_encode(['exists' => ($count > 0)]);
} catch (PDOException $e) {
    // In case of database error
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
}
?> 