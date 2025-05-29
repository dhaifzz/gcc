<?php
require_once 'database.php';

try {
    // Create about_content table
    $sql = "CREATE TABLE IF NOT EXISTS about_content (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        content TEXT NOT NULL,
        display_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    // Insert default about content
    $aboutContents = [
        [
            'title' => 'Description',
            'content' => 'The Guidance and Counseling Center at Western Mindanao State University is a vital support unit dedicated to addressing the psychological, emotional, and personal development needs of students and staff. It is one of the key services that contribute to the overall health and well-being of the WMSU community.',
            'display_order' => 1
        ],
        [
            'title' => 'Vision',
            'content' => 'By 2040, WMSU is a Smart Research University generating competent professionals and global citizens engendered by the knowledge from sciences and liberal education, empowering communities, promoting peace, harmony, and cultural diversity.',
            'display_order' => 2
        ],
        [
            'title' => 'Mission',
            'content' => 'WMSU commits to create a vibrant atmosphere of learning where science, technology, innovation, research, the arts and humanities, and community engagement flourish, and produce world-class professionals committed to sustainable development and peace.',
            'display_order' => 3
        ],
        [
            'title' => 'Quality Policy',
            'content' => "The Western Mindanao State University is committed to deliver academic excellence, to produce globally competitive human resources, and to conduct innovative research for sustainable development beyond the ASEAN region. It is defined as a Smart Research University, that adapts to the changing landscape of the stakeholders' needs.\n\nWMSU also commits to continually enhance its Quality Management System by integrating risk-based thinking into all processes to achieve intended results and guarantee customer satisfaction in compliance with applicable quality assurance standards.",
            'display_order' => 4
        ]
    ];
    
    // Check if table is empty before inserting default data
    $stmt = $pdo->query("SELECT COUNT(*) FROM about_content");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO about_content (title, content, display_order) VALUES (:title, :content, :display_order)");
        
        foreach ($aboutContents as $content) {
            $stmt->execute($content);
        }
        
        echo "Default about content created successfully!<br>";
    }
    
    // Create contact_info table
    $sql = "CREATE TABLE IF NOT EXISTS contact_info (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        type VARCHAR(50) NOT NULL,
        value TEXT NOT NULL,
        icon VARCHAR(100) DEFAULT NULL,
        display_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    // Insert default contact information
    $contactInfo = [
        [
            'title' => 'Description',
            'type' => 'description',
            'value' => 'The Guidance and Counseling Center For any concerns, just contact us through our official page or email. Completion of the Personal Data Form and Counseling Form is required before sessions.',
            'icon' => '',
            'display_order' => 1
        ],
        [
            'title' => 'Facebook Page',
            'type' => 'facebook',
            'value' => 'WMSU Guidance and Counseling Center',
            'icon' => 'fab fa-facebook',
            'display_order' => 2
        ],
        [
            'title' => 'Facebook Link',
            'type' => 'facebook_link',
            'value' => 'https://www.facebook.com/WMSUGCC',
            'icon' => 'fab fa-facebook',
            'display_order' => 3
        ],
        [
            'title' => 'Email Address',
            'type' => 'email',
            'value' => 'gcc@wmsu.edu.ph',
            'icon' => 'fas fa-envelope',
            'display_order' => 4
        ]
    ];
    
    // Check if table is empty before inserting default data
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_info");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO contact_info (title, type, value, icon, display_order) VALUES (:title, :type, :value, :icon, :display_order)");
        
        foreach ($contactInfo as $info) {
            $stmt->execute($info);
        }
        
        echo "Default contact information created successfully!<br>";
    }
    
    // Create team_members table if it doesn't exist
    $sql = "CREATE TABLE IF NOT EXISTS team_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        image_path VARCHAR(255) NOT NULL,
        role VARCHAR(100) DEFAULT NULL,
        status VARCHAR(100) DEFAULT NULL,
        title VARCHAR(255) DEFAULT NULL,
        campus ENUM('main', 'esu') NOT NULL DEFAULT 'main',
        category ENUM('director', 'counselor', 'staff', 'coordinator') NOT NULL,
        display_order INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sql);
    
    echo "All CMS tables created successfully!";
    
} catch(PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
} 