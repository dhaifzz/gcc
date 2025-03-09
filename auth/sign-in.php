<?php
require_once '../font/font.php';
require_once 'database.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="sign-in.css">
</head>
<body>
    <?php
    session_start();
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT role FROM users WHERE email = :email AND password = :password");
        $stmt->execute(['email' => $email, 'password' => $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $user['role'];

            header("Location: redirect.php");
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    }
    ?>
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