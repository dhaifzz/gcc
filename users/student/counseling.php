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
    <link rel="stylesheet" type="text/css" href="css/counseling.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- <a href="../../auth/sign-out.php">Sign Out</a> -->
    <!-- STUDENT / COUNSELING -->
</head>
<body>
    <div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="../student/student.php">Guidance and Counseling Center</a>
       <div class="burger-icon" style="float: right; margin: 10px;">
           <i class="fas fa-bars" style="font-size: 35px;"></i>
       </div>
    </div>    
       <div class="container">
         <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;"> Appointments </div>
           <div id="motto" class="motto" style="padding: 60px 0 60px;">
            <p style="margin: 0 20px; text-align: center; font-size: 23px; font-weight: 500;">The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> at WMSU offers counseling services for both students and outside clients. Appointments are required for consultations, including the completion of the Personal Data Form and Counseling Form before sessions.</p>
         </div>
       <div style="background-color: #F1F1F1; padding: 70px 80px 100px;">
            <div style="text-align: center; font-size: 40px; font-weight: bold; margin-bottom: 30px; color: #16633F; text-decoration: underline;">Counseling</div>
            <div style="display: flex; justify-content: center; gap: 30px;">
              <div style="width: 30%; height: 300px; background-color: white; border: 1px solid #ccc; border-radius: 7px; display: flex; flex-direction: column; justify-content: center; padding: 0px 20px 0px; text-align: center; gap: 50px;">
                <div style="font-size: 30px; font-weight: 600;">Appointments</div>
                <div style="font-size: 20px;">Book, Check in for Reschedule or change an appointment.</div>
                <button class="btn-re-view" style="background-color: #11AD64; color: white; border: 2px solid rgb(14, 121, 73); padding: 15px 0; margin-bottom: -20px; border-radius: 5px; cursor: pointer; font-size: 22px; font-weight: 500; transition: background-color 0.3s, transform 0.3s;">
                  <i class="fas fa-arrow-right" style="margin-right: 10px;"></i>Reserve
                </button>
              </div>
              <div style="width: 30%; height: 300px; background-color: white; border: 1px solid #ccc; border-radius: 7px; display: flex; flex-direction: column; justify-content: center; padding: 0px 20px 0px; text-align: center; gap: 50px;">
                <div style="font-size: 30px; font-weight: 600;">Meet our Counsellors</div>
                <div style="font-size: 20px;">Meet the GCC's team director, coordinators and staff.</div>
                <button class="btn-re-view" 
                        onclick="window.location.href='../student/sub-pages/our-team.php'"
                        style="background-color: #11AD64; color: white; border: 2px solid rgb(14, 121, 73); 
                               padding: 15px 0; margin-bottom: -20px; border-radius: 5px; cursor: pointer; 
                               font-size: 22px; font-weight: 500; transition: background-color 0.3s, transform 0.3s;">
                    <i class="fas fa-arrow-right" style="margin-right: 10px;"></i>View
                </button>
              </div>
            </div>
          </div>
        <div style="background-color:rgb(255, 255, 255); padding: 60px 0 60px;">
        <span style="margin: 0 30px; color: #095D36; font-size: 28px; font-weight: 600;">Counseling Services</span>
       <p style="margin: 15px 30px; text-align: left; font-size: 25px;"> Counseling at the WMSU Guidance and Counseling Center starts with an initial appointment. Appointments can be booked on the same day or for the next day, available Monday – Friday, 8 AM – 5 PM. During peak times, appointment availability may vary.</p>
      </div>
      <div style="background-color: #F1F1F1; padding: 40px 0 40px;">
            <p style="margin: 0 30px; text-align: start; font-size: 23px; font-weight: 500;">If you cannot find an appointment <a id="contact" class="contact" href="sub-pages/contact-us.php">contact us</a> during business hours. We can arrange for a counselor to provide a brief triage phone call to discuss support options. You can also check out what you can do while you are waiting, or if you need urgent support.</p>
      </div>
      <div style="background-color:rgb(255, 255, 255); padding: 55px 0 55px;">
            <p style="margin: 0 30px; text-align: start; font-size: 25px;">All appointments are up to 50 minutes. For your initial appointment, you'll be asked to arrive 10 minutes before your appointment to complete questionnaires about your health and well being.</p>
            <p style="margin: 25px 30px; text-align: start; font-size: 25px; font-weight: 400;">If you feel more comfortable speaking with a male or female counselor, or have particular preferences of counselor, see our staff page for more details. We will endevour to place you with someone of your choice, although this may not always be possible.</p>
      </div>
      <div style="background-image: url('/gcc/img/counselling-bg.png'); background-size: cover; width: 100%; height: 700px; border-top: solid 1px rgba(124, 124, 124, 0.91)"></div>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
  </div>
</body>
</html>