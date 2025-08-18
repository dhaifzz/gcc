<?php
require_once '../../database/database.php';
require_once '../../font/font.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff' && $_SESSION['role'] !== 'Director') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Set the base upload directory
$baseUploadDir = $_SERVER['DOCUMENT_ROOT'] . '/gcc/shared/main/uploads/shifting/';

// Create directory if it doesn't exist
if (!file_exists($baseUploadDir)) {
    mkdir($baseUploadDir, 0755, true);
}

// Process file uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['attachments'])) {
    $response = [];
    
    foreach ($_FILES['attachments']['name'] as $key => $name) {
        $tmpName = $_FILES['attachments']['tmp_name'][$key];
        $error = $_FILES['attachments']['error'][$key];
        
        if ($error === UPLOAD_ERR_OK) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $destination = $baseUploadDir . $filename;
            
            if (move_uploaded_file($tmpName, $destination)) {
                $response[] = [
                    'original_name' => $name,
                    'stored_name' => $filename,
                    'path' => '/gcc/shared/main/uploads/shifting/' . $filename
                ];
            }
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request']);
}
?>