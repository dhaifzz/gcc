<?php
require_once '../../database/database.php';

// Set proper headers
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Check if WMSU ID was provided
if (!isset($_POST['wmsu_id']) || empty($_POST['wmsu_id'])) {
    echo json_encode(['error' => 'WMSU ID is required']);
    exit;
}

$wmsu_id = trim($_POST['wmsu_id']);

// Don't check Guest IDs
if ($wmsu_id === 'Guest ID') {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    // Check if WMSU ID exists in the database
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE wmsu_id = :wmsu_id AND wmsu_id != 'Guest ID'");
    $stmt->execute([':wmsu_id' => $wmsu_id]);
    $count = $stmt->fetchColumn();
    
    // Return result
    echo json_encode(['exists' => ($count > 0)]);
} catch (PDOException $e) {
    // In case of database error
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
}
?> 