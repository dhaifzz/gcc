<?php
require_once '../../font/font.php';
require_once '../../client/navbar.php';
require_once '../../database/database.php';

session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'College Student') {
    header("Location: ../../auth/sign-in.php");
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
function getUserDetails($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT first_name, middle_name, last_name, course_grade FROM users WHERE id = :userId");
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function hasPendingRequest($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM shifting WHERE user_id = :userId AND status = 'pending'");
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetchColumn() > 0;
}

$userId = $_SESSION['user_id'] ?? null;

$userDetails = $userId ? getUserDetails($userId) : [];
$firstName = $userDetails['first_name'] ?? '';
$middleName = $userDetails['middle_name'] ?? '';
$lastName = $userDetails['last_name'] ?? '';
$wmsu_id = $_SESSION['wmsu_id'] ?? '';
$currentCourse = $userDetails['course_grade'] ?? '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (hasPendingRequest($userId)) {
        $errorMessage = "You already have a pending shifting request. Please wait for it to be approved.";
    } else {
        $courseToShift = $_POST['course_to_shift'];
        $reasonToShift = $_POST['reason_to_shift'];

        $picture = $_FILES['picture']['name'];
        $grades = $_FILES['grades']['name'];
        $cor = $_FILES['cor']['name'];
        $cetResult = $_FILES['cet_result']['name'];

        $uploadDir = 'uploads/shifting';
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

        function uploadFile($file, $uploadDir, $allowedTypes) {
            $fileName = basename($file['name']);
            $fileType = $file['type'];
            $fileTmpName = $file['tmp_name'];

            if (in_array($fileType, $allowedTypes)) {
                $filePath = $uploadDir . $fileName;
                if (move_uploaded_file($fileTmpName, $filePath)) {
                    return $filePath;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }

        $picturePath = uploadFile($_FILES['picture'], $uploadDir, $allowedTypes);
        $gradesPath = uploadFile($_FILES['grades'], $uploadDir, $allowedTypes);
        $corPath = uploadFile($_FILES['cor'], $uploadDir, $allowedTypes);
        $cetResultPath = uploadFile($_FILES['cet_result'], $uploadDir, $allowedTypes);

        if ($picturePath && $gradesPath && $corPath && $cetResultPath) {
            $stmt = $pdo->prepare("INSERT INTO shifting (user_id, first_name, middle_name, last_name, wmsu_id, current_course, course_to_shift, reason_to_shift, picture, grades, cor, cet_result, status) VALUES (:userId, :firstName, :middleName, :lastName, :wmsu_id, :currentCourse, :courseToShift, :reasonToShift, :picturePath, :gradesPath, :corPath, :cetResultPath, 'pending')");
            $stmt->execute([
                'userId' => $userId,
                'firstName' => $firstName,
                'middleName' => $middleName,
                'lastName' => $lastName,
                'wmsu_id' => $wmsu_id,
                'currentCourse' => $currentCourse,
                'courseToShift' => $courseToShift,
                'reasonToShift' => $reasonToShift,
                'picturePath' => $picturePath,
                'gradesPath' => $gradesPath,
                'corPath' => $corPath,
                'cetResultPath' => $cetResultPath
            ]);

            $successMessage = "Your shifting request has been submitted successfully.";
        } else {
            $errorMessage = "Error uploading files. Please try again.";
        }
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
    <link rel="stylesheet" type="text/css" href="../css/shifting.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- COLLEGE STUDENT / SHIFTING EXAM -->
</head>
<body>
     <!-- Navbar -->
     <?php shiftingNavbar($profile_image); ?>

      <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;"> Shifting Exam Form </div>
    <div class="shift-form" style="padding: 70px 50px 70px 50px;">
    <?php if (isset($successMessage)): ?>
            <div style="background-color: #c8f7c5; padding: 15px; color: #2c662d; margin-bottom: 20px; border-radius: 5px;">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php elseif (isset($errorMessage)): ?>
            <div style="background-color: #f8d7da; padding: 15px; color: #842029; margin-bottom: 20px; border-radius: 5px;">
                <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>
        <!-- <form action="submit_shift_form.php" method="post" enctype="multipart/form-data"> -->

            <div style="display: flex; width: 100%; gap: 20px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="first_name" style="font-size: 25px; color: white;">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;" readonly>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="middle_name" style="font-size: 25px; color: white;">Middle Name (optional)</label>
                    <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middleName); ?>" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;" readonly>                </div>
            </div>
            <div style="display: flex; width: 100%; gap: 20px; margin-top: 20px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="last_name" style="font-size: 25px; color: white;">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" required style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;" readonly>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="student_id" style="font-size: 25px; color: white;">Student ID</label>
                    <input type="text" id="school_id" name="school_id" value="<?php echo htmlspecialchars($wmsu_id); ?>" required style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;" readonly>
                </div>
            </div>
            <div style="display: flex; width: 100%; gap: 20px; margin-top: 20px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="course_to_shift" style="font-size: 25px; color: white;">Course to Shift</label>
                    <select id="course_to_shift" name="course_to_shift" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;">
                        <option value="course1">Information Technology</option>
                        <option value="course2">Nursing</option>
                        <option value="course3">Law</option>
                    </select>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="reason_to_shift" style="font-size: 25px; color: white;">Reason to Shift</label>
                    <select id="reason_to_shift" name="reason_to_shift" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;">
                        <option value="academic">Academic</option>
                        <option value="personal">Personal</option>
                        <option value="career">Career</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; width: 100%; gap: 20px; margin-top: 30px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="picture" style="font-size: 25px; color: white;">2x2 Picture with Name Tag (not selfie)</label>
                    <label class="custom-file-upload" style="margin-top: 5px;">
                        <input type="file" id="picture" name="picture" onchange="showFileName('picture')">
                        <i class="fa-solid fa-upload"></i> Upload Picture
                    </label>
                    <span id="picture-file-name" class="file-name" style="color:rgb(193, 255, 202); font-size: 18px;"></span>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="grades" style="font-size: 25px; color: white;">All Downloadable Grades:</label>
                    <label class="custom-file-upload" style="margin-top: 5px;">
                        <input type="file" id="grades" name="grades" onchange="showFileName('grades')">
                        <i class="fa-solid fa-upload"></i> Upload Grades
                    </label>
                    <span id="grades-file-name" class="file-name"></span>
                </div>
            </div>
            <div style="display: flex; width: 100%; gap: 20px; margin-top: 40px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="cor" style="font-size: 25px; color: white;">Latest COR</label>
                    <label class="custom-file-upload" style="margin-top: 5px;">
                        <input type="file" id="cor" name="cor" onchange="showFileName('cor')">
                        <i class="fa-solid fa-upload"></i> Upload COR
                    </label>
                    <span id="cor-file-name" class="file-name"></span>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="cet_result" style="font-size: 25px; color: white;">College Entrance Test Result</label>
                    <label class="custom-file-upload" style="margin-top: 5px;">
                        <input type="file" id="cet_result" name="cet_result" onchange="showFileName('cet_result')">
                        <i class="fa-solid fa-upload"></i> Upload CET Result
                    </label>
                    <span id="cet_result-file-name" class="file-name"></span>
                </div>
            </div>
            <div style="margin-top: 80px;">
                <button class="submit" type="submit">Submit</button>
            </div>
        </form>
    </div>
    <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
        <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
        <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
    </footer>
</body>
</html>

<script src="/gcc/js/showFileName.js"></script>
<script src="/gcc/js/sidebar.js"></script>
