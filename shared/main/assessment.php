<?php
require_once '../../font/font.php';
require_once '../../client/navbar.php';
require_once '../../database/database.php';

session_start();
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['College Student', 'High School Student'])) {
  header("Location: ../../auth/sign-in.php");
  exit();
}

$role = $_SESSION['role'];
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
    <link rel="stylesheet" type="text/css" href="../css/assessment.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- COLLEGE AND HIGH SCHOOL STUDENT / ASSESSMENT -->
</head>
<body>
    <!-- Navbar -->
    <?php assessNavbar($profile_image); ?>

    <div class="main-content">
       <div class="container">
         <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;"> Assessment for Students </div>
           <div id="motto" class="motto" style="padding: 60px 0 60px;">
            <p style="margin: 0 20px; text-align: center; font-size: 23px; font-weight: 500;">The <span style="color: #095D36; font-weight: 600;">Guidance and Counseling Center</span> at WMSU offers Assessment for both high school students and College students. Appointments are required for consultations, before taking the assessments.</p>
         </div>
        <div style="background-color: #F1F1F1; padding: 70px 80px 100px;">
          <div style="text-align: center; font-size: 40px; font-weight: bold; margin-bottom: 30px; color: #16633F; text-decoration: underline;">Make an Appointment</div>
          <div style="display: flex; justify-content: center; gap: 30px;">
            
            <!-- High School Card -->
            <div style="width: 30%; height: 300px; background-color: white; border: 1px solid #ccc; border-radius: 7px; display: flex; flex-direction: column; justify-content: center; padding: 0px 20px 0px; text-align: center; gap: 50px; position: relative;">
              <div style="font-size: 30px; font-weight: 600;">For High School Students</div>
              <div style="font-size: 20px;">Book, Check in for Reschedule or change an appointment.</div>
        
              <div class="text-link" style="position: relative; display: inline-block;">
                <button id="btn-highschool" class="btn-hi-col <?php if ($role === 'College Student') echo 'disabled-btn'; ?>" 
                  <?php if ($role !== 'College Student') echo "onclick=\"location.href='./appointment-pages/assessment-appoint.php'\""; ?>>
                  <i class="fas fa-arrow-right" style="margin-right: 10px;"></i>Reserve
                </button>
        
                <?php if ($role === 'College Student'): ?>
                <span class="tooltip-custom">
                  <i class="fa-solid fa-circle-exclamation mr-1" style="margin-right: 3px;"></i>
                  This reservation is for high school students.
                  <span class="arrow"></span>
                </span>
                <?php endif; ?>
              </div>
        
            </div>
        
            <!-- College Card -->
            <div style="width: 30%; height: 300px; background-color: white; border: 1px solid #ccc; border-radius: 7px; display: flex; flex-direction: column; justify-content: center; padding: 0px 20px 0px; text-align: center; gap: 50px; position: relative;">
              <div style="font-size: 30px; font-weight: 600;">For College Students</div>
              <div style="font-size: 20px;">Book, Check in for Reschedule or change an appointment.</div>
        
              <div class="text-link" style="position: relative; display: inline-block;">
                <button id="btn-college" class="btn-hi-col <?php if ($role === 'High School Student') echo 'disabled-btn'; ?>" 
                  <?php if ($role !== 'High School Student') echo "onclick=\"location.href='./appointment-pages/assessment-appoint.php'\""; ?>>
                  <i class="fas fa-arrow-right" style="margin-right: 10px;"></i>Reserve
                </button>
        
                <?php if ($role === 'High School Student'): ?>
                <span class="tooltip-custom">
                  <i class="fa-solid fa-circle-exclamation mr-1" style="margin-right: 3px;"></i>
                  This reservation is for college students in WMSU.
                  <span class="arrow"></span>
                </span>
                <?php endif; ?>
              </div>
        
            </div>
        
          </div>
        </div>

        <div style="background-color:rgb(255, 255, 255); padding: 70px 0 70px;">
        <span style="margin: 0 30px; color: #095D36; font-size: 28px; font-weight: 600;">Student Assessments </span>
       <p style="margin: 15px 30px; text-align: left; font-size: 25px;"> Student Assesments at the WMSU Guidance and Counseling Center starts with an initial appointment. Appointments can be booked on the same day or for the next day, available Monday – Friday, 8 AM – 5 PM. During peak times, appointment availability may vary.</p>
      </div>
      <div style="background-color: #F1F1F1; padding: 50px 0 50px;">
            <p style="margin: 0 30px; text-align: start; font-size: 23px; font-weight: 500;">If you cannot find an appointment <a id="contact" class="contact" href="../../shared/sub-pages/contact-us.php">contact us</a> during business hours. We can arrange for a counselor to provide a brief triage phone call to discuss support options. You can also check out what you can do while you are waiting, or if you need urgent support.</p>
      </div>
      
      <div style="background-image: url('/gcc/img/gcc-bg.png'); background-size: cover; width: 100%; height: 1000px; border-top: solid 1px rgba(124, 124, 124, 0.91)"></div>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
       </div>
    </div>
</body>
</html>

<script src="/gcc/js/sidebar.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
  const userRole = '<?php echo $role; ?>'; // PHP injects the role here

  const btnHighschool = document.getElementById('btn-highschool');
  const btnCollege = document.getElementById('btn-college');
  const tooltipHighschool = document.getElementById('tooltip-highschool');
  const tooltipCollege = document.getElementById('tooltip-college');

  if (userRole === 'College Student') {
    // Disable High School button
    btnHighschool.disabled = true;
    btnHighschool.style.backgroundColor = '#ccc';
    btnHighschool.style.cursor = 'not-allowed';
    tooltipHighschool.style.display = 'inline-block';

    // Enable College button
    btnCollege.disabled = false;
  }
  else if (userRole === 'High School Student') {
    // Disable College button
    btnCollege.disabled = true;
    btnCollege.style.backgroundColor = '#ccc';
    btnCollege.style.cursor = 'not-allowed';
    tooltipCollege.style.display = 'inline-block';

    // Enable High School button
    btnHighschool.disabled = false;
  }
});

</script>