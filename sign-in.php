<?php
require '../config.php';
session_start();
$error = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=guidance_counseling_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        header("Location: redirect.php");
        exit();
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="sign-in.css">
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
            <span class="toggle-password" onclick="togglePassword('password', 'toggle-password')"><i class="fas fa-eye-slash" style="color: #16633F;"></i></span>
        </div>
        <div>
            <button type="submit">Sign In</button>
        </div>
        <div class="signup-text">
            <p>Don't have an account? <a href="../auth/sign-up.php" class="signup">Sign Up</a></p>
        </div>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
    <script src="js/eye-icon.js"></script>
</body>
</html>