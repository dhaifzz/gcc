<?php
require_once '../font/font.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="sign-up.css">
</head>
<body>
    <form method="POST" action="sign-up.php">
        <div class="header-container">
            <img src="../img/gcc-logo.png" alt="GCC Logo" id="gcc-logo">
            <p class="text">Sign up with your details to access the GCC portal</p>
        </div>
        <div class="flex-container">
            <div class="first-name">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first_name" required>
            </div>
            <div class="middle-name">
                <label for="middle-name">Middle Name (optional)</label>
                <input type="text" id="middle-name" name="middle_name">
            </div>
        </div>
        <div class="flex-container">
            <div class="last-name">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last_name" required>
            </div>
            <div class="school">
                <label for="school">School</label>
                <select id="school" name="school" required>
                    <option value="wmsu">Western Mindanao State University</option>
                    <option value="uz">Universidad de Zamboanga</option>
                    <option value="adzu">Ateneo de Zamboanga University</option>
                </select>
            </div>
        </div>
        <div class="flex-container">
            <div class="course-grade">
                <label for="course-grade">Course / Grade Level</label>
                <select id="course-grade" name="course_grade" required>
                    <option value="js">Junior High</option>
                    <option value="sh">Senior High</option>
                    <option value="cs">Computer Science</option>
                    <option value="it">Information Technology</option>
                </select>
            </div>
            <div class="sex">
                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="prefer_not_to_say">Prefer not to say</option>
                </select>
            </div>
            <div class="age">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" required>
            </div>
        </div>
        <div class="flex-container">
            <div class="contact-number">
                <label for="contact-number">Contact Number</label>
                <input type="text" id="contact-number" name="contact_number" required>
            </div>
            <div class="address">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" required>
            </div>
        </div>
        <div class="flex-container">
            <div class="civil-status">
                <label for="civil-status">Civil Status</label>
                <select id="civil-status" name="civil_status" required>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="widowed">Widowed</option>
                    <option value="divorced">Divorced</option>
                    <option value="separated">Separated</option>
                </select>
            </div>
            <div class="occupation">
                <label for="occupation">Occupation</label>
                <input type="text" id="occupation" name="occupation" required>
            </div>
        </div>
        <div class="flex-container">
            <div class="email">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="password">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <span class="toggle-password" onclick="togglePassword('password', 'toggle-password')"><i class="fas fa-eye-slash" style="color: #16633F;"></i></span>
            </div>
        </div>
        <div>
            <button type="submit">Sign Up</button>
        </div>
        <div class="signin-text">
            <p>Already have an account? <a href="../auth/sign-in.php" class="signup">Sign In</a></p>
        </div>
    </form>
</body>
</html>

<script src="js/eye-icon.js"></script>
