<?php
require_once '../font/font.php';
require_once '../database/database.php';
// include('users\admin\content.php');

// $contents = [];
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GCC Website</title>  
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/public-page.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- PUBLIC PAGE -->
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-items">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
       <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
       <a class="website" href="public-page.php">WMSU Guidance and Counseling Center</a>
       </div>
       <div class="navbar-content">
    <a href="public-page.php">Home</a>
    <div class="dropdown">
        <a href="#" class="dropbtn">Appointments <i class="fas fa-caret-down"></i></a>
        <div class="dropdown-content">
        <a class="h" data-section="Counseling"><i class="fas fa-calendar-check"></i> Counseling</a>
        <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
        <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
        </div>
    </div>
    <div class="dropdown">
        <a href="#" class="dropbtn">About <i class="fas fa-caret-down"></i></a>
        <div class="dropdown-content">
            <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
            <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="team.php"><i class="fas fa-users"></i> Our Team</a>
        </div>
    </div>

    <button class="btn-sign-in" onclick="window.location.href='../auth/sign-in.php'"> Sign In</button>

</div>
    <div class="sidebar-overlay"></div>
    <div class="burger-icon" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </div>
    
    <div class="sidebar" id="sidebar">
        <span class="close-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-xmark"></i>
        </span>
    <div class="menu-items">
            <a href="public-page.php"><i class="fas fa-home"></i>Home</a>
            <a class="h" data-section="Appointments"><i class="fas fa-calendar-check"></i> Appointments</a>
            <a class="h" data-section="Assessments"><i class="fas fa-file-alt"></i> Assessments</a>
            <a class="h" data-section="Shifting Exam"><i class="fas fa-edit"></i> Shifting Exam</a>
            <hr>
            <a href="contact.php"><i class="fas fa-envelope"></i> Contact Us</a>
            <a href="about.php"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="team.php"><i class="fas fa-users"></i> Our Team</a>
            <hr>
            <a href="../auth/sign-in.php" class="logout"><i class="fas fa-sign-out-alt"></i>Sign In</a>
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
          <div class="carousel-overlay"></div>
          <div class="welcome-text">
             <span>Welcome to GCC Website!</span>
          </div>
   </div>

    <div class="motto" style="background-color: #F1F1F1; padding: 5rem 0 5rem;">
  <p style="margin: 0 1.25rem; text-align: center; font-size: 1.75rem; font-weight: 500;">
    The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> at Western Mindanao State University offers free, 
    confidential counseling, student assessments, and support for the shifting exam, along with workshops for academic and personal growth.
  </p>
</div>
    <div class="container-sign">
    <div class="content-sign" style="align-items: baseline;">
  <div style="text-align: center; font-size: 2.5rem; font-weight: bold; margin-bottom: 1.875rem; color:rgb(255, 255, 255); text-decoration: underline;">
    To Access Website
  </div>
  <p style="font-size: 1.125rem; color: #ffffff; font-weight: 600; text-align: center; margin: 0 2rem 2rem;">
    In order to access the full features of the Guidance and Counseling Center website, including setting appointments for counseling, assessments, and shifting examinations, users are required to log in to their respective accounts. This ensures that all services are personalized, securely documented, and handled with confidentiality. If you already have an account, please proceed to sign in. Otherwise, kindly register to create one and gain access to our wide range of support services.
</p>
  <div style="display: flex; justify-content: center; gap: 1.875rem; align-items:last baseline;">
    <div class="card">
      <div style="font-size: 1.875rem; font-weight: 600;">Sign In Here.</div>
      <div style="font-size: 1.25rem;">Sign In, If you already have an existing account.</div>
      <button class="btn-hi-col" onclick="location.href='../auth/sign-in.php'" style="background-color: #11AD64; color: white; border: 0.125rem solid rgb(14, 121, 73); padding: 0.9375rem 0; margin-bottom: -1.25rem; border-radius: 0.3125rem; cursor: pointer; font-size: 1.375rem; font-weight: 500; transition: background-color 0.3s, transform 0.3s;">
        <i class="fas fa-arrow-right" style="margin-right: 0.625rem;"></i>Sign In 
      </button>
    </div>

    <div class="card">
      <div style="font-size: 1.875rem; font-weight: 600;">Sign Up Here.</div>
      <div style="font-size: 1.25rem;">Sign Up, If you still don't have an account.</div>
      <button class="btn-hi-col" onclick="location.href='../auth/sign-up.php'" style="background-color: #11AD64; color: white; border: 0.125rem solid rgb(14, 121, 73); padding: 0.9375rem 0; margin-bottom: -1.25rem; border-radius: 0.3125rem; cursor: pointer; font-size: 1.375rem; font-weight: 500; transition: background-color 0.3s, transform 0.3s;">
        <i class="fas fa-arrow-right" style="margin-right: 0.625rem;"></i>Sign Up
      </button>
    </div>
  </div>
</div>
    </div>
    <div class="contents">
       <div class="image-gallery">
            <div class="image-item">
                   <img src="/gcc/img/counseling-img.png" alt="Image 1">
                   <p style="margin: 0.9375rem 0.3125rem 1.25rem; cursor: pointer;"><a href="#" class="h" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 0.3125rem; color:rgb(14, 72, 45);"></i>Counseling</a></p>
                   <span class="description"> Counseling services are available for both students and outside clients. Appointments are required for consultations, which include the completion of the Personal Data Form and Counseling Form before sessions.</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/assessment-img.png" alt="Image 2">
                   <p style="margin: 0.9375rem 0.3125rem 1.25rem; cursor: pointer;"><a href="#" class="h" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 0.3125rem; color:rgb(14, 72, 45);"></i>Assessment for Students</a></p>
                   <span class="description"> Conducts assessments for students taking the DASS-21 Test (College) and DASS-Y Test (High School). Students must schedule an appointment and complete the required forms before the assessment.</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/shifting-img.png" alt="Image 3">
                   <p style="margin: 0.9375rem 0.3125rem 1.25rem; cursor: pointer;"><a href="#" class="h" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 0.3125rem; color:rgb(14, 72, 45);"></i>Shifting Exam</a></p>
                   <span class="description"> Students changing programs. Applicants must schedule an appointment and complete the required forms before taking the exam.</span>
            </div>
        </div>
    </div>
    <div class="gcc-pages">
        <div class="pages-to-go">
            <div class="pages"><a href="about.php" style="color: white; text-decoration: none;">About Us</a></div>
            <div class="pages"><a href="team.php" style="color: white; text-decoration: none;">Our Team</a></div>
            <div class="pages"><a href="contact.php" style="color: white; text-decoration: none;">Contact Us</a></div>
        </div>
    </div>
    <footer style="background-color: #DC143C; color: white; padding-top: 0.3125rem; display: flex; justify-content: space-between; align-items: center;">
  <div style="margin-left: 1.25rem;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
  <div style="margin-right: 1.25rem;">
    <img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 2.5rem;">
  </div>
</footer>
    </div> 
</body>
</html>

<script src="/gcc/js/carousel.js"></script>
<script src="/gcc/js/sidebar.js"></script>
<script src="/gcc/js/card-animation.js"></script>
<script src="/gcc/js/slide-to-sign.js"></script>