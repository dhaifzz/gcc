<?php
require_once '../font/font.php';
require_once '../database/database.php';
session_start();

$error = isset($_SESSION['error']) ? $_SESSION['error'] : ''; 
unset($_SESSION['error']); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $user['role'];

        if ($remember_me) {
            $token = bin2hex(random_bytes(16));
            $stmt = $pdo->prepare("UPDATE users SET remember_token = :token WHERE id = :id");
            $stmt->execute([':token' => $token, ':id' => $user['id']]);
            setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
        }

        header("Location: backend/redirect.php");
        exit();
    } else {
        $_SESSION['error'] = 'Incorrect password / Email or Account does not exist.'; 
        header("Location: sign-in.php"); 
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>Sign In</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/sign-in.css">
</head>
<body>
    <?php if ($error): ?>
        <div class="error-popup" id="errorPopup">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div>
        <div style="display: flex; justify-content: center; align-items: center; gap: 7px;">
            <img src="../img/gcc-logo.png" alt="GCC Logo" id="gcc-logo" style="height: 100px;">
            <img src="../img/wmsu-logo.png" alt="WMSU Logo" id="wmsu-logo" style="height: 100px;">
        </div>
            <p class="text">Log in with your WMSU / Gmail account to access GCC appointment portal</p>
        </div>
        <div class="email">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="input-fade" required>
        </div>
        <div class="password">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="input-fade" required>
            <span class="toggle-password" onclick="togglePassword('password', this)">
                <i class="fas fa-eye-slash" style="color: #16633F;"></i>
            </span>
        </div>
        <div>
            <button type="submit" class="input-fade">Sign In</button>
        </div>
        <div class="signup-text">
            <p>Don't have an account? <a href="../auth/sign-up.php" class="signup">Sign Up</a></p>
        </div>
    </form>
    <script src="/gcc/js/eye-icon.js"></script>
    <script src="/gcc/js/error-message.js"></script>
</body>
</html>