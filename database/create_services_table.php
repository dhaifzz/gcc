<?php
require_once 'database.php';

try {
    // Create services table
    $sql = "CREATE TABLE IF NOT EXISTS services (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        display_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    // Insert default services
    $services = [
        [
            'title' => 'Counseling',
            'description' => 'Counseling services are available for both students and outside clients. Appointments are required for consultations, which include the completion of the Personal Data Form and Counseling Form before sessions.',
            'image_path' => '/gcc/img/counseling-img.png',
            'display_order' => 1
        ],
        [
            'title' => 'Assessment for Students',
            'description' => 'Conducts assessments for students taking the DASS-21 Test (College) and DASS-Y Test (High School). Students must schedule an appointment and complete the required forms before the assessment.',
            'image_path' => '/gcc/img/assessment-img.png',
            'display_order' => 2
        ],
        [
            'title' => 'Shifting Exam',
            'description' => 'Students changing programs. Applicants must schedule an appointment and complete the required forms before taking the exam.',
            'image_path' => '/gcc/img/shifting-img.png',
            'display_order' => 3
        ]
    ];
    
    // Clear existing services
    $pdo->exec("TRUNCATE TABLE services");
    
    // Insert new services
    $stmt = $pdo->prepare("INSERT INTO services (title, description, image_path, display_order) VALUES (:title, :description, :image_path, :display_order)");
    
    foreach ($services as $service) {
        $stmt->execute($service);
    }
    
    echo "Services table created and populated successfully!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
} 