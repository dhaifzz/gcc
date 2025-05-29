<?php
require_once('../../database/database.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id'])) {
    $client_id = $_POST['client_id'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$client_id]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($client) {
            // Handle profile image path
            $profile_image = 'default-profile.png';
            if (!empty($client['profile_image'])) {
                $profile_path = $_SERVER['DOCUMENT_ROOT'] . '/gcc/img/profiles/' . $client['profile_image'];
                if (file_exists($profile_path)) {
                    $profile_image = $client['profile_image'];
                }
            }

            echo json_encode([
                'success' => true,
                'data' => [
                    'first_name' => $client['first_name'],
                    'middle_name' => $client['middle_name'],
                    'last_name' => $client['last_name'],
                    'age' => $client['age'],
                    'sex' => $client['sex'],
                    'civil_status' => $client['civil_status'],
                    'school' => $client['school'],
                    'course_grade' => $client['course_grade'],
                    'wmsu_id' => $client['wmsu_id'],
                    'contact_number' => $client['contact_number'],
                    'address' => $client['address'],
                    'occupation' => $client['occupation'],
                    'profile_image' => $profile_image
                ]
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
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}

$pdo = null;
?> 