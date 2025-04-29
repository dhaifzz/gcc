<?php
require_once '../../database/database.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $pdo->prepare("SELECT * FROM shifting WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Define the base URL for attachments
        $base_url = '/uploads/shifting/';
        // Adjust this to your actual path
        
        // List of file fields in your database
        $file_fields = ['picture', 'grades', 'cor', 'cet_result'];
        
        // Process each file field
        foreach ($file_fields as $field) {
            if (!empty($row[$field])) {
                // Construct full URL for the file
                $row[$field] = $base_url . $row[$field];
                
                // Add a timestamp to prevent caching
                $row[$field] .= '?t=' . time();
            } else {
                // Set to empty string if no file
                $row[$field] = '';
            }
        }

        header('Content-Type: application/json');
        echo json_encode($row);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No data found']);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid request']);
}
?>