<?php
require_once '../../font/font.php';
require_once '../../client/navbar.php';
require_once '../../database/database.php';

session_start();

$profile_image = '/gcc/img/profiles/default-profile.png';

$isLoggedIn = isset($_SESSION['email']) && in_array($_SESSION['role'], ['College Student', 'High School Student', 'Outside Client', 'Faculty']);

if ($isLoggedIn) {
    $email = $_SESSION['email'];
    $query = "SELECT * FROM users WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

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
}

// Fetch contact information from the database
try {
    $stmt = $pdo->query("SELECT * FROM contact_info ORDER BY display_order ASC");
    $contactInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Initialize variables with default values
    $description = "The Guidance and Counseling Center For any concerns, just contact us through our official page or email. Completion of the Personal Data Form and Counseling Form is required before sessions.";
    $facebook = "WMSU Guidance and Counseling Center";
    $facebook_link = "https://www.facebook.com/WMSUGCC";
    $email = "gcc@wmsu.edu.ph";
    
    // Map database content to variables
    foreach ($contactInfo as $info) {
        switch ($info['type']) {
            case 'description':
                $description = $info['value'];
                break;
            case 'facebook':
                $facebook = $info['value'];
                break;
            case 'facebook_link':
                $facebook_link = $info['value'];
                break;
            case 'email':
                $email = $info['value'];
                break;
        }
    }
} catch (PDOException $e) {
    // If error, default values will be used
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
    <link rel="stylesheet" type="text/css" href="../css/contact-us.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- CONTACT US -->
</head>
<body>
  <!-- Navbar -->
    <?php 
    if ($isLoggedIn) {
        contactNavbar($profile_image);
    } else {
        contactPublicNavbar();
    }
    ?>   
    
       <div class="container">
         <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 45px; font-weight: 500; color: white; display: flex; justify-content: left; align-items: center; padding-left: 70px;"> Contact Us </div>
           <div style="padding: 60px 0 60px;">
            <p style="margin: 0 20px; text-align: center; font-size: 23px; font-weight: 500;"><?php echo nl2br(htmlspecialchars($description)); ?></p>
         </div>
         <div style="background-color: #F1F1F1; padding: 70px 80px 100px; display: flex; flex-direction: column; align-items: center; text-align: center;">
    <div style="font-size: 40px; font-weight: bold; margin-bottom: 30px; color: #16633F; text-decoration: underline;">
        Contacts
    </div>
    <div style="display: flex; flex-direction: column; align-items: left; gap: 20px;">
        <!-- Facebook Contact -->
        <a href="<?php echo htmlspecialchars($facebook_link); ?>" target="_blank" class="gccpage" 
           style="display: flex; align-items: center; text-decoration: none; color: #16633F; font-size: 20px;">
            <i class="fab fa-facebook" style="font-size: 40px; margin-right: 10px;"></i>
            <span class="gcctext"><?php echo htmlspecialchars($facebook); ?></span>
        </a>

        <!-- Email Contact -->
        <a href="mailto:<?php echo htmlspecialchars($email); ?>" class="gccemail" 
           style="display: flex; align-items: center; text-decoration: none; color: #16633F; font-size: 20px;">
            <i class="fas fa-envelope" style="font-size: 40px; margin-right: 10px;"></i>
            <span class="gccetext"><?php echo htmlspecialchars($email); ?></span>
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

<script src="/gcc/js/sidebar.js"></script>
