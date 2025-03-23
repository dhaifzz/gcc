<?php
require_once '../../../database/database.php';

$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$_POST['id']]);
echo "User deleted successfully!";
?>
