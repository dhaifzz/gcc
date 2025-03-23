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

} catch (PDOException $e) {
    die("Error fetching user details: " . $e->getMessage());
}

$profile_title = '';
switch ($user['role']) {
    case 'Faculty':
        $profile_title = "Faculty's Profile";
        break;
    case 'Student':
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
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="../css/profile.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- STUDENT, OUTSIDE, FACULTY / PROFILE -->
</head>
<body>
    <div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="<?php
    switch ($_SESSION['role']) {
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
            
            <!-- Right Container (User Information) -->
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
        <!-- Appointments Table -->
        <div style="margin-top: 40px;">
            <h3>Appointments</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: white;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px;">Type</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Date</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Counselor</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Time</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Edit</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">Counseling</td>
                        <td style="border: 1px solid #ddd; padding: 8px; color: red;">08-30-2004</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">Kayden Andal</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">10:00 am</td>
                        <td style="border: 1px solid #ddd; padding: 8px;"><button style="background-color: #DC143C; color: white; border: none; padding: 5px 10px; cursor: pointer;">Confirm</button></td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">-</td>
                    </tr>
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