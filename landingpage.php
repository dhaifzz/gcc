<?php
require_once '../../font/font.php';
require_once('../../database/database.php');

session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../../../auth/sign-in.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/landingpage.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- STUDENT -->
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="#">Guidance and Counseling Center</a>
       <!-- Sidebar -->
       <div class="burger-icon" onclick="toggleSidebar()">
         <i class="fas fa-bars"></i>
       </div>
       <div class="sidebar" id="sidebar">
        <span class="close-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars-staggered"></i>
        </span>
            <a href="../../../auth/sign-out.php" class="logout"><i class="fas fa-sign-out-alt"></i>Log Out</a>
        </div>
       </div>
    </div>   

    <div class="main-content">
    <div id="carousel" class="carousel">
          <div class="carousel-inner"> 
             <div class="carousel-item active"> 
                 <div style="position: relative; text-align: center;">
                     <img src="/gcc/img/carousel-img/test.png" alt="Slide 1">
                 </div>
             </div>
             <div class="carousel-item active">
                 <div style="position: relative; text-align: center;">
                     <img src="/gcc/img/carousel-img/test2.png" alt="Slide 2">
                 </div>
             </div>
             <div class="carousel-item active">
                 <img src="/gcc/img/carousel-img/test3.png" alt="Slide 3">
             </div>
          </div>
          <div class="welcome-text">
             <span>Welcome to GCC Website!</span>
          </div>
    </div>
    <div id="motto" class="motto" style="background-color: #F1F1F1; padding: 80px 0 80px;">
       <p style="margin: 0 20px; text-align: center; font-size: 28px; font-weight: 500;">The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> at Western Mindanao State University offers free, 
       confidential counseling, student assessments, and support for the shifting exam, along with workshops for academic and personal growth.</p>
    </div>
    <div class="contents">
       <div class="image-gallery">
            <div class="image-item">
                   <img src="/gcc/img/counseling-img.png" alt="Image 1">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../staff/staff.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Counseling</a></p>
                   <span class="description"> Pending Counseling request</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/assessment-img.png" alt="Image 2">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../staff/staff.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Assessment for Students</a></p>
                   <span class="description"> Pending assessments request for DASS-21 Test (College) and DASS-Y Test (High School).</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/shifting-img.png" alt="Image 3">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../staff/staffshifting.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Shifting Exam</a></p>
                   <span class="description"> Pending request for Shifting Exam</span>
            </div>
        </div>
    </div>
    </div>
    <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
        <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
        <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
    </footer>
    </div> 
</body>
</html>

<script src="/gcc/js/carousel.js"></script>
<script src="/gcc/js/sidebar.js"></script>
