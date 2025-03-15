<?php
require_once '../../font/font.php';
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../auth/sign-in.php");
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
    <!-- <a href="../../auth/sign-out.php">Sign Out</a> -->
    <!-- STUDENT -->
</head>
<body>
    <div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="#">Guidance and Counseling Center</a>
       <div class="burger-icon" style="float: right; margin: 10px;">
           <i class="fas fa-bars" style="font-size: 35px;"></i>
       </div>
    </div>    
    <div class="container">
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
          <div class="welcome-text">Welcome to GCC Website!</div>
            </div>
      <div id="motto" class="motto" style="background-color: #F1F1F1; padding: 80px 0 80px;">
       <p style="margin: 0 20px; text-align: center; font-size: 28px; font-weight: 500;">The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> at Western Mindanao State University offers free, 
       confidential counseling, student assessments, and support for the shifting exam, along with workshops for academic and personal growth.</p>
      </div>
      <div class="contents">
       <div class="image-gallery">
            <div class="image-item">
                   <img src="/gcc/img/counseling-img.png" alt="Image 1">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="counseling.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Counseling</a></p>
                   <span class="description"> Counseling services are available for both students and outside clients. Appointments are required for consultations, which include the completion of the Personal Data Form and Counseling Form before sessions.</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/assessment-img.png" alt="Image 2">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="assessment.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Assessment for Students</a></p>
                   <span class="description"> Conducts assessments for students taking the DASS-21 Test (College) and DASS-Y Test (High School). Students must schedule an appointment and complete the required forms before the assessment.</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/shifting-img.png" alt="Image 3">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="shifting.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Shifting Exam</a></p>
                   <span class="description"> Students changing programs. Applicants must schedule an appointment and complete the required forms before taking the exam.</span>
            </div>
        </div>
      </div>
         <div class="gcc-pages">
            <div class="pages-to-go">
                <div class="pages"><a href="sub-pages/about-us.php" style="color: white; text-decoration: none;">About Us</a></div>
                <div class="pages"><a href="sub-pages/our-team.php" style="color: white; text-decoration: none;">Our Team</a></div>
                <div class="pages"><a href="sub-pages/contact-us.php" style="color: white; text-decoration: none;">Contact Us</a></div>
            </div>
         </div>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
    </div>
</body>
</html>

<script src="js/carousel.js"></script>
