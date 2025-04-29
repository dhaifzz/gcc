<?php
require_once '../../font/font.php';
require_once __DIR__ . '/../../database/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch the user details from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch profile image
    $stmt = $pdo->prepare("SELECT profile_image FROM profiles WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $profileRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profileRow && !empty($profileRow['profile_image'])) {
        $profile_image = $profileRow['profile_image'];
    } else {
        $profile_image = 'default-profile.png';
    }

    $_SESSION['profile_image'] = $profile_image;

    // Fetch 5 most recent appointments
    $stmt = $pdo->prepare("SELECT * FROM appointments WHERE client_id = :user_id ORDER BY appointment_id DESC LIMIT 5");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch shifting requests
    $stmt = $pdo->prepare("SELECT * FROM shifting WHERE user_id = :user_id ORDER BY submitted_at DESC LIMIT 5");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $shiftingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching user details: " . $e->getMessage());
}

$profile_title = '';
switch ($user['role']) {
    case 'Faculty':
        $profile_title = "Faculty's Profile";
        break;
    case 'College Student':
    case 'High School Student':
        $profile_title = "Student's Profile";
        break;
    case 'Outside Client':
        $profile_title = "Outside Client's Profile";
        break;
    default:
        $profile_title = "User's Profile";
        break;
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
    <link rel="stylesheet" type="text/css" href="../css/profile.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- STUDENT, OUTSIDE, FACULTY / PROFILE -->
</head>
<body>
<div class="navbar">
    <div class="navbar-items">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
       <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
       <a class="website" href="<?php
    switch ($_SESSION['role']) {
        case 'Faculty':
            echo '../../client/inside/faculty/faculty.php';
            break;
        case 'College Student':
            echo '../../client/inside/student/college.php';
            break;
        case 'High School Student':
            echo '../../client/inside/student/high-school.php';
            break;
        case 'Outside Client':
            echo '../../client/outside/outside.php';
            break;
        default:
           echo '../../../auth/sign-in.php';  
    }
    ?>">WMSU Guidance and Counseling Center</a>
    </div>
       <div class="navbar-content">
    <button class="btn-sign-out" onclick="window.location.href='../../auth/sign-out.php'">Sign Out</button>
    </div>
  </div>

    <div class="content">
    <div class="container">
    <div style="background-color: #F1F1F1; padding: 70px 80px 100px; border-radius: 15px;">
        <div style="display: flex; justify-content: center; gap: 20px;">
            <!-- Profile Title -->
            <div style="width: 100%; text-align: center; margin-bottom: 20px; font-size: 20px;">
                <h2><?php echo $profile_title; ?></h2>
            </div>
        </div>
        <div style="display: flex; justify-content: center; gap: 20px;">
            <!-- Left Container (Profile Image & Name) -->
            <div style="width: 20%; background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; position: relative;">
                <!-- Profile Image Display -->
                <div style="position: relative; display: inline-block;">
                    <img id="profileImage" src="/gcc/img/profiles/<?php echo $profile_image; ?>?v=<?php echo time(); ?>" alt="Profile"
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                    <!-- Edit Icon -->
                    <div onclick="document.getElementById('fileInput').click()" 
                         style="position: absolute; top: 0; right: 0; background-color: white; border-radius: 50%; 
                         width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; cursor: pointer; 
                         box-shadow: 0px 4px 8px 0px rgba(0, 0, 0, 0.1); transition: background-color 0.3s, transform 0.3s;" 
                         onmouseover="this.style.backgroundColor='rgba(17, 173, 100, 1)'; this.style.color='white'; this.style.transform='scale(1.1)';" 
                         onmouseout="this.style.backgroundColor='white'; this.style.color='black'; this.style.transform='scale(1)';">
                        <i class="fas fa-pen"></i>
                    </div>
                </div>

                <!-- Hidden File Input -->
                <form id="uploadForm" action="../backend/upload-profile.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="profileImage" id="fileInput" style="display: none;" accept="image/jpeg, image/png, image/gif, image/jpg">
                </form>

                <div id="message" style="margin-top: 10px; color: green;"></div>

                <!-- Displayed Name -->
                <div style="margin-top: 15px; font-size: 20px; font-weight: bold;">
                    <?php
                    $middleInitial = isset($user['middle_name'][0]) && !empty($user['middle_name']) ? $user['middle_name'][0] . '. ' : '';
                    echo $user['first_name'] . " " . $middleInitial . $user['last_name'];
                    ?>
                </div>
            </div>
            
            <div style="width: 60%; background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                <div style="font-weight: bold; margin-bottom: 10px; font-size: 18px;">Information:</div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div class="info-box">Role: <span><?php echo htmlspecialchars($user['role']); ?></span></div>
                    <div class="info-box">Contact Number: <span><?php echo htmlspecialchars($user['contact_number']); ?></span></div>
                    <div class="info-box">Gender: <span><?php echo htmlspecialchars($user['sex']); ?></span></div>
                    <div class="info-box">Email: <span><?php echo htmlspecialchars($user['email']); ?></span></div>
                    <div class="info-box">School: <span><?php echo htmlspecialchars($user['school']); ?></span></div>
                    <div class="info-box">Course/Grade Level: <span><?php echo htmlspecialchars($user['course_grade']); ?></span></div>
                </div>
            </div>  
        </div>

        <div style="margin-top: 40px;">
            <h3>Recent Appointments</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: white; border-radius: 9px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px;">Type</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Date</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Time</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($appointment['appointment_type'])); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($appointment['requested_date'])); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($appointment['requested_time'])); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($appointment['status'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-top: 40px;">
            <h3>Recent Shifting Requests</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: white; border-radius: 9px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px;">Current Course</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Course to Shift</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Date/Time</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shiftingRequests as $request): ?>
                        <tr>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($request['current_course'])); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($request['course_to_shift'])); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($request['submitted_at'])); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($request['status'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
  </div>
</body>
</html>

<script src="/gcc/js/upload-profile.js"></script>
<script src="/gcc/js/sidebar.js"></script>