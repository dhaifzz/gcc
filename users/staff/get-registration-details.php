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
        // Define the correct base URL
        $base_url = '/gcc/shared/main/uploads/shifting/';
        
        $file_fields = ['picture', 'grades', 'cor', 'cet_result'];
        
        foreach ($file_fields as $field) {
            if (!empty($row[$field])) {
                // Get just the filename
                $filename = basename($row[$field]);
                
                // Construct full URL
                $row[$field] = $base_url . $filename;
                
                // Add cache-busting parameter
                $row[$field] .= '?t=' . time();
            } else {
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