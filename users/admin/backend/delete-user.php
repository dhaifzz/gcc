<?php
require_once '../../../database/database.php';
session_start();

// Verify admin privileges
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    die("Unauthorized access!");
}

// Validate input
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Invalid user ID");
}

try {
    // First delete from profiles table if exists
    $pdo->beginTransaction();
    
    $stmt = $pdo->prepare("DELETE FROM profiles WHERE user_id = ?");
    $stmt->execute([$_POST['id']]);
    
    // Then delete from users table
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    
    $pdo->commit();
    echo "User deleted successfully!";
} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo "Error deleting user: " . $e->getMessage();
}
?>