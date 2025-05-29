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

// Fetch team members from the database
try {
    // Get main campus team members
    $mainStmt = $pdo->prepare("SELECT * FROM team_members WHERE campus = 'main' ORDER BY category, display_order");
    $mainStmt->execute();
    $mainTeamMembers = $mainStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group main campus members by category
    $mainDirectors = [];
    $mainCounselors = [];
    $mainStaff = [];
    $mainCoordinators = [];
    
    foreach ($mainTeamMembers as $member) {
        switch ($member['category']) {
            case 'director':
                $mainDirectors[] = $member;
                break;
            case 'counselor':
                $mainCounselors[] = $member;
                break;
            case 'staff':
                $mainStaff[] = $member;
                break;
            case 'coordinator':
                $mainCoordinators[] = $member;
                break;
        }
    }
    
    // Get ESU campus team members
    $esuStmt = $pdo->prepare("SELECT * FROM team_members WHERE campus = 'esu' ORDER BY display_order");
    $esuStmt->execute();
    $esuTeamMembers = $esuStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Handle error silently - empty arrays will show "No team members found" messages
    $mainDirectors = $mainCounselors = $mainStaff = $mainCoordinators = $esuTeamMembers = [];
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
    <link rel="stylesheet" type="text/css" href="../css/our-team.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
</head>
<body>
  <!-- Navbar -->
    <?php 
    if ($isLoggedIn) {
        ourTeamNavbar($profile_image);
    } else {
        teamPublicNavbar();
    }
    ?>          

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
        <?php if (!empty($mainDirectors)): ?>
        <div style="justify-content: center; align-items: center; display: flex; margin: 50px 0;">
            <div class="profile">
                <p class="role"> Director</p>
                <div class="profile-container">
                    <div class="profile-text">
                        <p class="name"><?php echo htmlspecialchars($mainDirectors[0]['name']); ?></p>
                        <?php if (!empty($mainDirectors[0]['status'])): ?>
                        <p class="status"> <?php echo htmlspecialchars($mainDirectors[0]['status']); ?> </p>
                        <?php endif; ?>
                        <?php if (!empty($mainDirectors[0]['title'])): ?>
                        <p class="title"><?php echo htmlspecialchars($mainDirectors[0]['title']); ?></p>
                        <?php endif; ?>
                    </div> 
                    <img src="/gcc/img/team-gcc/<?php echo htmlspecialchars($mainDirectors[0]['image_path']); ?>" alt="<?php echo htmlspecialchars($mainDirectors[0]['name']); ?>" class="profile-img-role" style="border: 1px solid black; border-radius: 50%;">
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Guidance Counselors Section -->
        <?php if (!empty($mainCounselors)): ?>
        <div style="text-align: center;">
            <p class="role">Guidance Counselor</p>
        </div>
        <div id="guidance-counselors" class="guidance-counselors" style="display: flex; justify-content: center; gap: 100px; margin-bottom: 50px;">
            <?php foreach ($mainCounselors as $counselor): ?>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/<?php echo htmlspecialchars($counselor['image_path']); ?>" alt="<?php echo htmlspecialchars($counselor['name']); ?>" class="profile-img-role" style="border: 1px solid #ccc; border-radius: 50%;">
                <p class="name"><?php echo htmlspecialchars($counselor['name']); ?></p>
                <?php if (!empty($counselor['status'])): ?>
                <p class="status"> <?php echo htmlspecialchars($counselor['status']); ?> </p>
                <?php endif; ?>
                <?php if (!empty($counselor['title'])): ?>
                <p class="title"><?php echo htmlspecialchars($counselor['title']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Staff Section -->
        <?php if (!empty($mainStaff)): ?>
        <div style="text-align: center;">
            <p class="role">Guidance Staff</p>
        </div>
        <div id="staff" class="staff" style="display: flex; justify-content: center; gap: 100px;">
            <?php foreach ($mainStaff as $staff): ?>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/<?php echo htmlspecialchars($staff['image_path']); ?>" alt="<?php echo htmlspecialchars($staff['name']); ?>" class="profile-img-role" style="border: 1px solid #ccc; border-radius: 50%;">
                <p class="name"><?php echo htmlspecialchars($staff['name']); ?></p>
                <?php if (!empty($staff['status'])): ?>
                <p class="status"> <?php echo htmlspecialchars($staff['status']); ?> </p>
                <?php endif; ?>
                <?php if (!empty($staff['title'])): ?>
                <p class="title"><?php echo htmlspecialchars($staff['title']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Coordinators -->
        <?php if (!empty($mainCoordinators)): ?>
        <div style="text-align: center;">
            <p class="role" style="margin-top: 100px"> Guidance Coordinators</p>
        </div>
        <div id="coords" class="coords" style="display: flex; justify-content: center; gap: 50px; margin-bottom: 50px;">
            <?php foreach ($mainCoordinators as $coordinator): ?>
            <div class="profile-card">
                <img src="/gcc/img/team-gcc/<?php echo htmlspecialchars($coordinator['image_path']); ?>" alt="<?php echo htmlspecialchars($coordinator['name']); ?>" class="profile-img-role" style="border: 1px solid #ccc; border-radius: 50%;">
                <p class="name"><?php echo htmlspecialchars($coordinator['name']); ?></p>
                <?php if (!empty($coordinator['status'])): ?>
                <p class="status"> <?php echo htmlspecialchars($coordinator['status']); ?> </p>
                <?php endif; ?>
                <?php if (!empty($coordinator['title'])): ?>
                <p class="title"<?php if (strlen($coordinator['title']) > 20): ?> style="max-width: 300px;"<?php endif; ?>><?php echo htmlspecialchars($coordinator['title']); ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- BUTTON TO PAGE 2 -->
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
            <?php if (!empty($esuTeamMembers)): ?>
                <?php foreach ($esuTeamMembers as $member): ?>
                <div class="profile-card">
                    <img src="/gcc/img/team-gcc/<?php echo htmlspecialchars($member['image_path']); ?>" alt="<?php echo htmlspecialchars($member['name']); ?>" class="profile-img-role" style="border: 1px solid #ccc; border-radius: 50%;">
                    <p class="name"><?php echo htmlspecialchars($member['name']); ?></p>
                    <?php if (!empty($member['status'])): ?>
                    <p class="status"> <?php echo htmlspecialchars($member['status']); ?> </p>
                    <?php endif; ?>
                    <?php if (!empty($member['title'])): ?>
                    <p class="title"><?php echo htmlspecialchars($member['title']); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No team members found for ESU campus.</p>
            <?php endif; ?>
        </div>
        <!-- BUTTON TO PAGE 1 -->
        <div class="button-container">
             <button class="go-to-page" onclick="showPage('main-campus')">
                 <i class="fas fa-arrow-left"></i> 
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

<script src="/gcc/js/page2page.js"></script>
<script src="/gcc/js/sidebar.js"></script>
