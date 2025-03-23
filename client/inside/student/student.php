<?php
require_once '../../../font/font.php';
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../auth/sign-in.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/student.css">
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
        <div class="menu-items">
            <a href="student.php"><i class="fas fa-home"></i>Home</a>
            <a href="../../../shared/sub-pages/profile.php"><i class="fas fa-user"></i>Profile</a>
            <a href="../../../shared/main/counseling.php"><i class="fas fa-calendar-check"></i>Appointments</a>
            <a href="../../../shared/main/assessment.php"><i class="fas fa-file-alt"></i>Assessments</a>
            <a href="../../../shared/main/shifting.php"><i class="fas fa-edit"></i>Shifting Exam</a>
            <hr>
            <a href="../../../shared/sub-pages/contact-us.php"><i class="fas fa-envelope"></i>Contact Us</a>
            <a href="../../../shared/sub-pages/about-us.php"><i class="fas fa-info-circle"></i>About Us</a>
            <a href="../../../shared/sub-pages/our-team.php"><i class="fas fa-users"></i>Our Team</a>
            <hr>
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
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../../../shared/main/counseling.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Counseling</a></p>
                   <span class="description"> Counseling services are available for both students and outside clients. Appointments are required for consultations, which include the completion of the Personal Data Form and Counseling Form before sessions.</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/assessment-img.png" alt="Image 2">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../../../shared/main/assessment.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Assessment for Students</a></p>
                   <span class="description"> Conducts assessments for students taking the DASS-21 Test (College) and DASS-Y Test (High School). Students must schedule an appointment and complete the required forms before the assessment.</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/shifting-img.png" alt="Image 3">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../../../shared/main/shifting.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Shifting Exam</a></p>
                   <span class="description"> Students changing programs. Applicants must schedule an appointment and complete the required forms before taking the exam.</span>
            </div>
        </div>
    </div>
    <div class="gcc-pages">
        <div class="pages-to-go">
            <div class="pages"><a href="../../../shared/sub-pages/about-us.php" style="color: white; text-decoration: none;">About Us</a></div>
            <div class="pages"><a href="../../../shared/sub-pages/our-team.php" style="color: white; text-decoration: none;">Our Team</a></div>
            <div class="pages"><a href="../../../shared/sub-pages/contact-us.php" style="color: white; text-decoration: none;">Contact Us</a></div>
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
