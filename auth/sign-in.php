<?php
require_once '../font/font.php';
require_once '../database/database.php';
session_start();

$error = isset($_SESSION['error']) ? $_SESSION['error'] : ''; 
unset($_SESSION['error']); 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if ($user['status'] === 'Inactive') { 
            $_SESSION['error'] = 'Your account is currently inactive. Please contact the admin.';
            header("Location: sign-in.php");
            exit();
        }
        
        if (password_verify($password, $user['password'])) { 
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $user['role'];
            header("Location: backend/redirect.php");
            exit();
        } else {
            $_SESSION['error'] = 'Incorrect password / email or Account does not exist.'; 
            header("Location: sign-in.php"); 
            exit();
        }
    } else {
        $_SESSION['error'] = 'Incorrect password / email or Account does not exist.'; 
        header("Location: sign-in.php"); 
        exit();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/sign-in.css">
</head>
<body>
    <form method="POST" action="">
        <div>
            <img src="../img/gcc-logo.png" alt="GCC Logo" id="gcc-logo" style="display: block; margin: 0 auto;">
            <p class="text">Log in with your WMSU / Gmail account to access GCC appointment portal</p>
        </div>
        <div class="email">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="password">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <span class="toggle-password" onclick="togglePassword('password', this)">
             <i class="fas fa-eye-slash" style="color: #16633F;"></i>
            </span>
        </div>
        <div>
            <button type="submit">Sign In</button>
        </div>
        <div class="signup-text">
            <p>Don't have an account? <a href="../auth/sign-up.php" class="signup">Sign Up</a></p>
        </div>
        <?php if ($error): ?>
            <p style="color: red; font-weight: 600;"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
    <script src="/gcc/js/eye-icon.js"></script>
</body>
</html>
