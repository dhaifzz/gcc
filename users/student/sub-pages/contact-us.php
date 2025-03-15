<?php
require_once '../../../font/font.php';
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
    <link rel="stylesheet" type="text/css" href="../css/contact-us.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- <a href="../../auth/sign-out.php">Sign Out</a> -->
    <!-- STUDENT / CONTACT US -->
</head>
<body>
    <div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="../../student/student.php">Guidance and Counseling Center</a>
       <div class="burger-icon" style="float: right; margin: 10px;">
           <i class="fas fa-bars" style="font-size: 35px;"></i>
       </div>
    </div>    
       <div class="container">
         <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 45px; font-weight: 500; color: white; display: flex; justify-content: left; align-items: center; padding-left: 70px;"> Contact Us </div>
           <div style="padding: 60px 0 60px;">
            <p style="margin: 0 20px; text-align: center; font-size: 23px; font-weight: 500;">The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> For any concerns, just contact us through our official page or email. Completion of the Personal Data Form and Counseling Form is required before sessions.</p>
         </div>
         <div style="background-color: #F1F1F1; padding: 70px 80px 100px; display: flex; flex-direction: column; align-items: center; text-align: center;">
    <div style="font-size: 40px; font-weight: bold; margin-bottom: 30px; color: #16633F; text-decoration: underline;">
        Contacts
    </div>
    <div style="display: flex; flex-direction: column; align-items: left; gap: 20px;">
        <!-- Facebook Contact -->
        <a href="https://www.facebook.com/WMSUGCC" target="_blank" class="gccpage" 
           style="display: flex; align-items: center; text-decoration: none; color: #16633F; font-size: 20px;">
            <i class="fab fa-facebook" style="font-size: 40px; margin-right: 10px;"></i>
            <span class="gcctext">WMSU Guidance and Counseling Center</span>
        </a>

        <!-- Email Contact -->
        <a href="mailto:gcc@wmsu.edu.ph" class="gccemail" 
           style="display: flex; align-items: center; text-decoration: none; color: #16633F; font-size: 20px;">
            <i class="fas fa-envelope" style="font-size: 40px; margin-right: 10px;"></i>
            <span class="gccetext">gcc@wmsu.edu.ph</span>
        </a>
    </div>
</div>
       
      <div style="background-color:rgb(255, 255, 255); padding: 60px 0 60px;"> </div>
      <div style="background-image: url('/gcc/img/contact-bg.png'); background-size: cover; width: 100%; height: 600px; border-top: solid 1px rgba(124, 124, 124, 0.91)"></div>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
  </div>
</body>
</html>