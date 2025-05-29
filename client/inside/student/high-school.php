<?php
require_once '../../../client/navbar.php';
require_once '../../../font/font.php';
require_once '../../../database/database.php';

// Get carousel images
$carousel_images = array_diff(scandir("../../../img/carousel-img"), array('.', '..'));

session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'High School Student') {
    header("Location: ../../../auth/sign-in.php");
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
    <link rel="stylesheet" type="text/css" href="css/high-school.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- HIGH SCHOOL STUDENT -->
</head>
<body>
    <!-- Navbar -->
    <?php renderNavbar($profile_image); ?>

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
              <span class="typing-container">Welcome to GCC Website, <span class="first-name"><?php echo htmlspecialchars($user['first_name']); ?></span>!</span>
          </div>
    </div>
    <div id="motto" class="motto" style="background-color: #F1F1F1; padding: 5rem 0 5rem;">
       <p style="margin: 0 1.25rem; text-align: center; font-size: 1.75rem; font-weight: 500;">The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> at Western Mindanao State University offers free, 
       confidential counseling, student assessments, and support for the shifting exam, along with workshops for academic and personal growth.</p>
    </div>
    <div class="contents">
       <div class="image-gallery">
            <div class="image-item">
                   <img src="/gcc/img/counseling-img.png" alt="Image 1">
                   <p style="margin: 0.9375rem 0.3125rem 1.25rem; cursor: pointer;"><a href="../../../shared/main/counseling.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 0.3125rem; color:rgb(14, 72, 45);"></i>Counseling</a></p>
                   <span class="description"> Counseling services are available for both students and outside clients. Appointments are required for consultations, which include the completion of the Personal Data Form and Counseling Form before sessions.</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/assessment-img.png" alt="Image 2">
                   <p style="margin: 0.9375rem 0.3125rem 1.25rem; cursor: pointer;"><a href="../../../shared/main/assessment.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 0.3125rem; color:rgb(14, 72, 45);"></i>Assessment for Students</a></p>
                   <span class="description"> Conducts assessments for students taking the DASS-21 Test (College) and DASS-Y Test (High School). Students must schedule an appointment and complete the required forms before the assessment.</span>
            </div>
            <div class="image-item">
               <img src="/gcc/img/shifting-img.png" alt="Image 1" style="filter: grayscale(90%);">
               <p class="clickable-text" style="margin: 15px 5px 20px; cursor: pointer;">
                   <a class="text-link" style="text-decoration: none; color: inherit; user-select: none; color:rgba(0, 0, 0, 0.59);">
                       <i class="fas fa-angle-right" style="margin-right: 5px; color:rgba(0, 0, 0, 0.59);"></i>
                       Shifting Exam
                       <span class="tooltip-custom">
                       <i class="fa-solid fa-circle-exclamation mr-1" style="margin-right: 3px;"></i>
                           Only applicable for college students in WMSU.
                           <span class="arrow"></span>
                       </span>
                   </a>
               </p>
               <span class="description" style="color:rgba(0, 0, 0, 0.59);">
                   Students changing programs. Applicants must schedule an appointment and complete the required forms before taking the exam.
               </span>
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
    <footer style="background-color: #DC143C; color: white; padding-top: 0.3125rem; display: flex; justify-content: space-between; align-items: center;">
        <div style="margin-left: 1.25rem;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
        <div style="margin-right: 1.25rem;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 2.5rem;"></div>
    </footer>
    </div> 
</body>
</html>

<script src="/gcc/js/carousel.js"></script>
<script src="/gcc/js/sidebar.js"></script>
