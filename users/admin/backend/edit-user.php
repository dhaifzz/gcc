<?php
require_once '../../../database/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['id']) || empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['email']) || empty($_POST['wmsu_id'])) {
        echo "Error: Missing required fields.";
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        if (!$stmt->fetch()) {
            echo "Error: User not found.";
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, email=?, wmsu_id=? WHERE id=?");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['middle_name'] ?: null,
            $_POST['last_name'],
            $_POST['email'],
            $_POST['wmsu_id'],
            $_POST['id']
        ]);

        if ($stmt->rowCount() > 0) {
            echo "User updated successfully!";
        } else {
            echo "No changes made.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>