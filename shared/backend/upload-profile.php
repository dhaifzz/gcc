<?php
require_once __DIR__ . '/../../database/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profileImage'])) {
    $targetDir = __DIR__ . '/../../img/profiles/';
    $fileName = basename($_FILES['profileImage']['name']);
    $targetFilePath = $targetDir . $fileName;
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);

    // Allow certain file formats
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif');
    if (in_array($fileType, $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetFilePath)) {
            // Check if the user already has a profile entry
            $stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = :user_id");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($profile) {
                // Update existing profile image
                $stmt = $pdo->prepare("UPDATE profiles SET profile_image = :profile_image WHERE user_id = :user_id");
            } else {
                // Insert new profile image
                $stmt = $pdo->prepare("INSERT INTO profiles (user_id, profile_image) VALUES (:user_id, :profile_image)");
            }

            $stmt->bindParam(':profile_image', $fileName, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                echo "Profile image updated successfully|$fileName";
            } else {
                echo "Error updating profile image in database.";
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Sorry, only JPG, JPEG, PNG, & GIF files are allowed to upload.";
    }
} else {
    echo "No file uploaded.";
}
?>