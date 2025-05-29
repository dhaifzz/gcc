<?php
require_once('../../database/database.php');

session_start();

// Check if user is logged in and is a Staff member
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if client_id is provided
if (!isset($_POST['client_id'])) {
    echo json_encode(['success' => false, 'message' => 'Client ID is required']);
    exit();
}

$client_id = $_POST['client_id'];

try {
    // Prepare the query to get client details and their profile image
    $stmt = $pdo->prepare("
        SELECT 
            u.*,
            COALESCE(p.profile_image, 'default-profile.png') as profile_image
        FROM users u 
        LEFT JOIN profiles p ON u.id = p.user_id 
        WHERE u.id = ?
    ");
    
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($client) {
        // Get the document root path
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        $baseUrl = '/gcc'; // Your base URL path
        
        // Check if profile image exists
        $defaultImagePath = $docRoot . $baseUrl . '/img/profiles/default-profile.png';
        $profileImagePath = $docRoot . $baseUrl . '/img/profiles/' . $client['profile_image'];
        
        // Verify if the image file exists
        if ($client['profile_image'] !== 'default-profile.png' && !file_exists($profileImagePath)) {
            $client['profile_image'] = 'default-profile.png';
        }

        echo json_encode([
            'success' => true,
            'data' => $client
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Client not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$pdo = null; 