<?php
require_once('../database/database.php');
session_start();

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE remember_token = :token AND token_expiry > NOW()");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
    } else {
        setcookie('remember_token', '', time() - 3600, "/", "", false, true);
    }
}
?>
