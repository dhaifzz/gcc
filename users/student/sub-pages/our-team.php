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
    <link rel="stylesheet" type="text/css" href="../css/our-team.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
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
        <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 45px; font-weight: 500; color: white; display: flex; justify-content: left; align-items: center; padding-left: 70px;"> 
            Meet Our Team 
        </div>

        <!-- Main Campus -->
        <div id="main-campus" class="page active">
        <div style="padding: 40px 0 40px;">
            <p style="color: #16633F; display: flex; justify-content: center; align-items: center; font-size: 35px; font-weight: 600; margin: 0;">
                Guidance, Coordinators, & Support Staff
            </p>     
            <p style="color: #727070; display: flex; justify-content: center; align-items: center; font-size: 25px; font-weight: 600; margin: 0;">
                (Main Campus)
            </p>     
        </div>

        <!-- Director Section -->
        <div style="justify-content: center; align-items: center; display: flex; margin: 50px 0;">
            <div class="profile">
                <p class="role"> Director</p>
                <div class="profile-container">
                    <div class="profile-text">
                        <p class="name">Dr. Fini Joy P. Buenafe</p>
                        <p class="status"> RGC, LPT </p>
                        <p class="title">Director, Guidance and Counseling Center</p>
                    </div> 
                    <img src="/gcc/img/team-gcc/MA'AM.FINI.png" alt="Dr. Fini Joy P. Buenafe" class="profile-img">
                </div>
            </div>
        </div>

        <!-- Guidance Counselors Section -->
        <div style="text-align: center;">
            <p class="role">Guidance Counselor</p>
        </div>
        <div id="guidance-counselors" class="guidance-counselors" style="display: flex; justify-content: center; gap: 100px; margin-bottom: 50px;">
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MS.GLENDA.png" alt="Ms. Glenda P. Acedo" class="profile-img">
                <p class="name">Ms. Glenda P. Acedo</p>
                <p class="status"> RGC, RPm, LPT </p>
                <p class="title">Guidance Counselor III</p>
            </div>

            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.MELTA.png" alt="Melta A. Villarta" class="profile-img">
                <p class="name">Melta A. Villarta</p>
                <p class="status"> RPm, RGC </p>
                <p class="title">Assistant Professor</p>
            </div>
        </div>

        <!-- Staff Section -->
        <div style="text-align: center;">
            <p class="role">Guidance Staff</p>
        </div>
        <div id="staff" class="staff" style="display: flex; justify-content: center; gap: 100px;">
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MS.AKEMI.png" alt="Ms. Glenda P. Acedo" class="profile-img">
                <p class="name">Ms. Akemi Lim</p>
                <p class="status"> MPA </p>
            </div>

            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MR.ROMEL.png" alt="Melta A. Villarta" class="profile-img">
                <p class="name">Mr. Romel L. San Juan</p>
            </div>
        </div>

        <!-- Coordinators -->
        <div style="text-align: center;">
            <p class="role" style="margin-top: 100px"> Guidance Coordinators</p>
        </div>
        <div id="coords" class="coords" style="display: flex; justify-content: center; gap: 50px; margin-bottom: 50px;">
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.LEAN.png" alt="Lean A. Legarde" class="profile-img">
                <p class="name">Lean A. Legarde</p>
                <p class="status"> MPA </p>
                <p class="title" style="max-width: 300px;">College of Public Administration and Development Studies</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.NADINE.png" alt="Nadine Evangelista" class="profile-img">
                <p class="name">Nadine Evangelista</p>
                <p class="status"> CpE </p>
                <p class="title">College of Engineering</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/SIR.JIEMAR.png" alt="Jiemar A. Arabani" class="profile-img">
                <p class="name">Jiemar A. Arabani</p>
                <p class="title" style="max-width: 250px;">College of Criminal Justice Education</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.CLARISSA.png" alt="Clarissa B. Miranda" class="profile-img">
                <p class="name">Clarissa B. Miranda</p>
                <p class="title">College of Liberal Arts</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.CARPIZO.png" alt="Marcelina Carpizo" class="profile-img">
                <p class="name">Marcelina Carpizo</p>
                <p class="status"> Ph.D., RSW </p>
                <p class="title" style="max-width: 300px;">College of Social Work and Community Development</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.UCOL.png" alt="Arlene B. Ucol" class="profile-img">
                <p class="name">Arlene B. Ucol</p>
                <p class="status"> LPT, MSPE </p>
                <p class="title" style="max-width: 300px;">College of Sports Science and Physical Education</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/SIR.PERFECTO.png" alt="Perfecto B. Cimafranca III." class="profile-img">
                <p class="name">Perfecto B. Cimafranca III.</p>
                <p class="status"> Ph.D., RGC, RTsy, LPT </p>
                <p class="title"> College of Teacher Education</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.COROS.png" alt="Aurea T. Coros" class="profile-img">
                <p class="name">Aurea T. Coros</p>
                <p class="title">College of Science and Mathematics</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.CARMEN.png" alt="Carmen Theresa V. Dickina" class="profile-img">
                <p class="name">Carmen Theresa V. Dickina</p>
                <p class="status">  Arch., UAP, PICAM </p>
                <p class="title">College of Architecture</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.SANDRA.png" alt="Sandra M. Covarrubias" class="profile-img">
                <p class="name">Sandra M. Covarrubias</p>
                <p class="status"> RN, MN </p>
                <p class="title">College of Nursing</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.MARJ.png" alt="Marjorie A. Rojas" class="profile-img">
                <p class="name">Marjorie A. Rojas</p>
                <p class="status"> CpE </p>
                <p class="title">College of Computing Studies</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.LIM.png" alt="Ruby M. Lim" class="profile-img">
                <p class="name">Ruby M. Lim</p>
                <p class="status"> RND </p>
                <p class="title">College of Home Economics</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.SITTIRASMA.png" alt="Sittirasma I. Jalilula" class="profile-img">
                <p class="name">Sittirasma I. Jalilula</p>
                <p class="status"> RF </p>
                <p class="title" style="max-width: 300px;">College of Forestry and Environmental Studies</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.NURMIA.png" alt="Nurmia L. Ticao" class="profile-img">
                <p class="name">Nurmia L. Ticao</p>
                <p class="status"> MA </p>
                <p class="title" style="max-width: 200px;">College of Asian and Islamic Studies</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.TOTO.png" alt="Sitti Aisha G. Toto" class="profile-img">
                <p class="name">Sitti Aisha G. Toto</p>
                <p class="status"> LPT </p>
                <p class="title">College of Agriculture</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.SHERYL.png" alt="Sheryl P. Ramirez" class="profile-img">
                <p class="name">Sheryl P. Ramirez</p>
                <p class="status"> Ph.D., LPT </p>
                <p class="title" style="max-width: 250px;">Integrated Laboratory School (Secondary)</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.ALICE.png" alt="Alice A. Calahat" class="profile-img">
                <p class="name">Alice A. Calahat</p>
                <p class="title" style="max-width: 250px;">Integrated Laboratory School (Elementary)</p>
            </div>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.CECILE.png" alt="Cecile G. Miang" class="profile-img">
                <p class="name">Cecile G. Miang</p>
                <p class="title" style="max-width: 250px;">Department of Extension Services and Community Development</p>
            </div>
        </div>
        <!-- BUTTON  TO PAGE 2 -->
        <div class="button-container">
             <button class="go-to-page" onclick="showPage('esu-campus')">
                 <i class="fas fa-arrow-right"></i> 
                 <p class="text-esu"> Meet the Team ESU! </p>
             </button>
         </div>
    </div>

    <!-- ESU Campus -->
         <div id="esu-campus" class="page">
            <div style="padding: 40px 0 40px;">
               <p style="color: #16633F; display: flex; justify-content: center; align-items: center; font-size: 35px; font-weight: 600; margin: 0;">
                   Guidance Coordinators
               </p>     
               <p style="color: #727070; display: flex; justify-content: center; align-items: center; font-size: 25px; font-weight: 600; margin: 0;">
                   (ESU Campus)
               </p>     
            </div>
            <div id="coords" class="coords" style="display: flex; justify-content: center; gap: 100px; margin-bottom: 50px;">
              <div class="profile-card">
                  <img src="/gcc/img/team-gcc/MA'AM.DECENA.png" alt="Rowee Joy S. Decena" class="profile-img">
                  <p class="name">Rowee Joy S. Decena</p>
                  <p class="title"> Ipil/Naga Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.LAGUNA.png" alt="Erjorie A. Laguna" class="profile-img">
                <p class="name">Erjorie A. Laguna</p>
                <p class="title">Diplahan Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/SIR.PACAMALAN.png" alt="Myco Leo B. Pacamalan" class="profile-img">
                <p class="name">Myco Leo B. Pacamalan</p>
                <p class="title">Siay Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.NOSCA.png" alt="Nosca Bonna Ar Taasin" class="profile-img">
                <p class="name">Nosca Bonna Ar Taasin</p>
                <p class="title">Tungawan Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.LUNA.png" alt="Loribel A. Luna" class="profile-img">
                <p class="name">Loribel A. Luna</p>
                <p class="title">Curuan Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/SIR.ANTOLIN.png" alt="Antolin Sialana" class="profile-img">
                <p class="name">Antolin Sialana</p>
                <p class="title">Alicia Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.BIHAG.png" alt="Cristie Bihag" class="profile-img">
                <p class="name">Cristie Bihag</p>
                <p class="title">Imelda Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.DELACRUZ.png" alt="Maria Celeste B. Dela Cruz" class="profile-img">
                <p class="name">Maria Celeste B. Dela Cruz</p>
                <p class="title">Malangas Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.LOLITA.png" alt="Lolita Lacao-Lacao" class="profile-img">
                <p class="name">Lolita Lacao-Lacao</p>
                <p class="title">Olutanga Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/MA'AM.MICHELLE.png" alt="Michelle Paderan" class="profile-img">
                <p class="name">Michelle Paderan</p>
                <p class="title">Mabuhay Campus</p>
              </div>
              <div class="profile-card">
                <img src="/gcc/img/team-gcc/SIR.NOEL.png" alt="Noel Pugosa" class="profile-img">
                <p class="name">Noel Pugosa</p>
                <p class="title">Pagadian Campus</p>
              </div>
            </div>
            <!-- BUTTON  TO PAGE 1 -->
        <div class="button-container">
             <button class="go-to-page" onclick="showPage('main-campus')">
                 <i class="fas fa-arrow-right"></i> 
                 <p class="text-esu"> Return to Main Campus! </p>
             </button>
         </div>
          </div>
        <div style="background-color: rgb(255, 255, 255); padding: 40px 0;"></div>
        <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;">
                <img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;">
            </div>
        </footer>
    </div>
</body>
</html>

<script src="../js/descDropdown.js"></script>
<script src="../js/page2page.js"></script>