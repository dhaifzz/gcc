<?php
require_once('../database/database.php');
header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
if (!$email) {
    echo json_encode(['exists' => false]);
    exit;
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$exists = $stmt->fetchColumn() > 0;

echo json_encode(['exists' => $exists]);