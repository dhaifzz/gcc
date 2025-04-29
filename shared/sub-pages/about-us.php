<?php
require_once '../../font/font.php';
require_once '../../client/navbar.php';
require_once '../../database/database.php';

session_start();
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['College Student', 'High School Student', 'Outside Client', 'Faculty'])) {
    header("Location: ../../auth/sign-in.php");
    exit();
}

$email = $_SESSION['email'];
$query = "SELECT * FROM users WHERE email = :email";
$stmt = $pdo->prepare($query);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_image = '/gcc/img/profiles/default-profile.png'; 

if ($user) {
    $user_id = $user['id'];
    $profileQuery = "SELECT profile_image FROM profiles WHERE user_id = :user_id";
    $profileStmt = $pdo->prepare($profileQuery);
    $profileStmt->execute(['user_id' => $user_id]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

    if ($profile && !empty($profile['profile_image'])) {
        $profile_image = '/gcc/img/profiles/' . htmlspecialchars($profile['profile_image']);
    }
}

?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="../css/about-us.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- ABOUT US -->
</head>
<body>
    <!-- Navbar -->
    <?php aboutNavbar($profile_image); ?>    

       <div class="container">
         <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 45px; font-weight: 500; color: white; display: flex; justify-content: left; align-items: center; padding-left: 70px;"> About Us </div>
         <div id="motto" class="motto" style="padding: 70px 0 70px;">
            <p style="margin: 0 20px; text-align: center; font-size: 26px; font-weight: 500;">The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> at Western Mindanao State University is a vital support unit dedicated to addressing the psychological, emotional, and personal development needs of students and staff. It is one of the key services that contribute to the overall health and well-being of the WMSU community.</p>
         </div>
         <div class="dropdown-form">
        <button class="dropdown-btn-form" onclick="toggleDropdown(0)">
            Our vision 
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </button>
        <div class="dropdown-content-form">
            <p>By 2040, WMSU is a Smart Research University generating competent professionals and global citizens engendered by the knowledge from sciences and liberal education, empowering communities, promoting peace, harmony, and cultural diversity.</p>
        </div>
    </div>

    <div class="dropdown-form">
        <button class="dropdown-btn-form" onclick="toggleDropdown(1)">
            Our mission 
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </button>
        <div class="dropdown-content-form">
            <p>WMSU commits to create a vibrant atmosphere of learning where science, technology, innovation, research, the arts and humanities, and community engagement flourish, and produce world-class professionals committed to sustainable development and peace.</p>
        </div>
    </div>

    <div class="dropdown-form">
        <button class="dropdown-btn-form" onclick="toggleDropdown(2)">
            Quality Policy 
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </button>
        <div class="dropdown-content-form">
            <p> The Western Mindanao State University is committed to deliver academic excellence, to produce globally competitive
            human resources, and to conduct innovative research for sustainable development beyond the ASEAN region. It is defined as a Smart Research University, that adapts to the changing landscape of the stakeholders' needs.</p>
            <p>WMSU also commits to continually enhance its Quality Management System by integrating risk-based thinking into all processes to achieve intended results and guarantee customer satisfaction in compliance with applicable quality assurance standards.</p>
        </div>
    </div>
    <div style="background-color:rgb(255, 255, 255); padding: 60px 0 60px;"> </div>
    <div style="background-image: url('/gcc/img/about-bg.png'); background-size: cover; width: 100%; height: 600px; border-top: solid 1px rgba(124, 124, 124, 0.91)"></div>

         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
  </div>
</body>
</html>

<script src="/gcc/js/descDropdown.js"></script>
<script src="/gcc/js/sidebar.js"></script>
