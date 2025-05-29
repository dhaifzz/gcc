<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gcc-2";

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Admin account details
    $adminEmail = "admin@gmail.com";
    $adminPassword = "123";
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    $firstName = "Dhaifz";
    $lastName = "Administrator";
    $role = "Admin";
    $contactNumber = "00000000000";
    $school = "WMSU";
    $courseGrade = "N/A";

    $stmt = $pdo->prepare("DELETE FROM users WHERE email = :email");
    $stmt->bindParam(':email', $adminEmail);
    $stmt->execute();

    $stmt = $pdo->prepare("INSERT INTO users 
                          (email, password, first_name, last_name, role, contact_number, school, course_grade) 
                          VALUES 
                          (:email, :password, :first_name, :last_name, :role, :contact_number, :school, :course_grade)");
    
    $stmt->bindParam(':email', $adminEmail);
    $stmt->bindParam(':password', $hashedPassword);
    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':role', $role);
    $stmt->bindParam(':contact_number', $contactNumber);
    $stmt->bindParam(':school', $school);
    $stmt->bindParam(':course_grade', $courseGrade);
    
    $stmt->execute();
    
    $adminId = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("DELETE FROM profiles WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $adminId);
    $stmt->execute();
    
    $stmt = $pdo->prepare("INSERT INTO profiles (user_id) VALUES (:user_id)");
    $stmt->bindParam(':user_id', $adminId);
    $stmt->execute();
    
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>