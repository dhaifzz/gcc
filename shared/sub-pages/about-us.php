<?php
require_once '../../font/font.php';
session_start();
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['Student', 'Outside Client', 'Faculty', 'Director', 'Staff', 'Admin'])) {
    header("Location: ../../auth/sign-in.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="../css/about-us.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- STUDENT / ABOUT US -->
</head>
<body>
    <div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="<?php
    switch ($_SESSION['role']) {
        case 'Director':
            echo '../../users/director/director.php';
            break;
        case 'Staff':
            echo '../../users/staff/staff.php';
            break;
        case 'Admin':
            echo '../../users/admin/admin.php';
            break;
        case 'Faculty':
            echo '../../../client/inside/faculty/faculty.php';
            break;
        case 'Student':
            echo '../../client/inside/student/student.php';
            break;
        case 'Outside Client':
            echo '../../client/outside/outside.php';
            break;
        default:
           echo '../../../auth/sign-in.php';  
    }
    ?>">Guidance and Counseling Center</a>
       <div class="burger-icon" style="float: right; margin: 10px;">
           <i class="fas fa-bars" style="font-size: 35px;"></i>
       </div>
    </div>    
       <div class="container">
         <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 45px; font-weight: 500; color: white; display: flex; justify-content: left; align-items: center; padding-left: 70px;"> About Us </div>
         <div id="motto" class="motto" style="padding: 70px 0 70px;">
            <p style="margin: 0 20px; text-align: center; font-size: 26px; font-weight: 500;">The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> at Western Mindanao State University is a vital support unit dedicated to addressing the psychological, emotional, and personal development needs of students and staff. It is one of the key services that contribute to the overall health and well-being of the WMSU community.</p>
         </div>
         <div class="dropdown">
        <button class="dropdown-btn" onclick="toggleDropdown(0)">
            Our vision 
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </button>
        <div class="dropdown-content">
            <p>By 2040, WMSU is a Smart Research University generating competent professionals and global citizens engendered by the knowledge from sciences and liberal education, empowering communities, promoting peace, harmony, and cultural diversity.</p>
        </div>
    </div>

    <div class="dropdown">
        <button class="dropdown-btn" onclick="toggleDropdown(1)">
            Our mission 
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </button>
        <div class="dropdown-content">
            <p>WMSU commits to create a vibrant atmosphere of learning where science, technology, innovation, research, the arts and humanities, and community engagement flourish, and produce world-class professionals committed to sustainable development and peace.</p>
        </div>
    </div>

    <div class="dropdown">
        <button class="dropdown-btn" onclick="toggleDropdown(2)">
            Quality Policy 
            <i class="fas fa-chevron-down dropdown-icon"></i>
        </button>
        <div class="dropdown-content">
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
