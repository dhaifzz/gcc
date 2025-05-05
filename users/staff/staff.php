<?php
require_once '../../font/font.php';
require_once('../../database/database.php');

session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

$user_email = $_SESSION['email'];

try {
    $stmt = $pdo->prepare("SELECT first_name FROM users WHERE email = :email");
    $stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
    $stmt->execute();
    $first_name = $stmt->fetchColumn(); // Get the first_name value directly

    if (!$first_name) {
        $first_name = "User"; 
    }

    $text = "Welcome to GCC Staff Page, $first_name!";
    $text_length = strlen($text);
    $name_length = strlen($first_name) + 17; 

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/staff.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- STUDENT -->
    <style>
    .typing-text {
         display: inline-block;
         white-space: nowrap;
         overflow: hidden;
         border-right: 0.1875rem solid rgb(29, 215, 129); /* 3px */
         padding-right: 0.1875rem; /* 3px */
         animation: typing 2s steps(<?php echo $text_length; ?>) forwards, 
                    blink-caret 0.75s step-end infinite;
     }
     .vertical-border {
         position: absolute;
         top: 0;
         right: 0;
         width: 0.125rem; /* 2px */
         background-color: black;
         animation: move-border 2s steps(<?php echo $text_length; ?>) forwards;
     }
    </style>
</head>
<body>
        <!-- Sidebar -->
        <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="staff.php" style="background-color: rgb(255, 255, 255); color: #236641;">
                <i class="fa-solid fa-home"></i> Home
            </a>
        
            <div class="dropdown">
                <button class="dropdown-btn"><i class="fa-regular fa-calendar-days"></i> Appointments</button>
                <div class="dropdown-content">
                    <a href="staff-counseling.php"><i class="fa-regular fa-calendar-days"></i> Counseling Table</a>
                    <a href="staff-assessment.php"><i class="fa-regular fa-calendar"></i> Assessment Table</a>
                </div>
            </div>
        
            <a href="staff-shifting.php"><i class="fa-solid fa-envelope"></i> Shifting Table</a>
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>

        <div class="sidebar-footer">
        <small>© 2025 WMSU </small>
        <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
    </div>
    </div>
    <div class="container">
    <div class="main-content">
    <div class="typing-container">
            <span class="typing-text">Welcome to WMSU GCC Staff, <span style="color:rgb(11, 178, 100);"><?php echo $first_name; ?></span>.</span>
            <span class="vertical-border"></span>
        </div>
    <div class="contents">
       <div class="image-gallery">
            <div class="image-item">
                   <img src="/gcc/img/counseling-img.png" alt="Image 1">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../staff/staff-counseling.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Counseling</a></p>
                   <span class="description"> Pending Counseling request</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/assessment-img.png" alt="Image 2">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../staff/staff-assessment.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Assessment for Students</a></p>
                   <span class="description"> Pending assessments request for DASS-21 Test (College) and DASS-Y Test (High School).</span>
            </div>
            <div class="image-item">
                   <img src="/gcc/img/shifting-img.png" alt="Image 3">
                   <p style="margin: 15px 5px 20px; cursor: pointer;"><a href="../staff/staff-shifting.php" style="text-decoration: none; color: inherit;"><i class="fas fa-angle-right" style="margin-right: 5px; color:rgb(14, 72, 45);"></i>Shifting Exam</a></p>
                   <span class="description"> Pending request for Shifting Exam</span>
            </div>
        </div>
    </div>
    </div>
</body>
</html>

<script src="/gcc/js/sidebar.js"></script>
