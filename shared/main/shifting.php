<?php
require_once '../../font/font.php';
require_once '../../client/navbar.php';
require_once '../../database/database.php';

session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'College Student') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize variables
$email = $_SESSION['email'];
$profile_image = '/gcc/img/profiles/default-profile.png';
$successMessage = '';
$errorMessage = '';

// Check if this is the first page load
if (!isset($_SESSION['page_loaded'])) {
    $_SESSION['page_loaded'] = true;
    // Clear any existing messages on first load
    unset($_SESSION['success']);
    unset($_SESSION['error']);
}

// Get user data
$query = "SELECT * FROM users WHERE email = :email";
$stmt = $pdo->prepare($query);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $user_id = $user['id'];
    $_SESSION['user_id'] = $user_id;
    
    // Get profile image
    $profileQuery = "SELECT profile_image FROM profiles WHERE user_id = :user_id";
    $profileStmt = $pdo->prepare($profileQuery);
    $profileStmt->execute(['user_id' => $user_id]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

    if ($profile && !empty($profile['profile_image'])) {
        $profile_image = '/gcc/img/profiles/' . htmlspecialchars($profile['profile_image']);
    }
}

// Get user details
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

// Retrieve messages from session only if they were set by form submission
if (isset($_SESSION['success']) && isset($_SESSION['form_submitted'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']);
    unset($_SESSION['form_submitted']);
}

if (isset($_SESSION['error']) && isset($_SESSION['form_submitted'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']);
    unset($_SESSION['form_submitted']);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['form_submitted'] = true;  // Mark that a form was submitted
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = "Invalid form submission.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    if (hasPendingRequest($userId)) {
        $_SESSION['error'] = "You already have a pending shifting request. Please wait for it to be approved.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $courseToShift = $_POST['course_to_shift'];
        $reasonToShift = $_POST['reason_to_shift'];

        // Create upload directory if it doesn't exist
        $uploadDir = 'uploads/shifting/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Define file size limit (5MB)
        $maxFileSize = 5 * 1024 * 1024; // 5MB in bytes

        function uploadFile($file, $uploadDir, $allowedTypes, $maxFileSize) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'error' => 'File upload failed: ' . getUploadErrorMessage($file['error'])];
            }

            if ($file['size'] > $maxFileSize) {
                return ['success' => false, 'error' => 'File size exceeds limit of 5MB'];
            }

            $fileName = basename($file['name']);
            $fileType = $file['type'];
            $fileTmpName = $file['tmp_name'];

            // Generate unique filename
            $uniqueName = uniqid() . '_' . $fileName;
            $filePath = $uploadDir . $uniqueName;

            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'error' => 'Invalid file type'];
            }

            if (move_uploaded_file($fileTmpName, $filePath)) {
                return ['success' => true, 'path' => $filePath];
            }

            return ['success' => false, 'error' => 'Failed to move uploaded file'];
        }

        function getUploadErrorMessage($errorCode) {
            switch ($errorCode) {
                case UPLOAD_ERR_INI_SIZE:
                    return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                case UPLOAD_ERR_FORM_SIZE:
                    return 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form';
                case UPLOAD_ERR_PARTIAL:
                    return 'The uploaded file was only partially uploaded';
                case UPLOAD_ERR_NO_FILE:
                    return 'No file was uploaded';
                default:
                    return 'Unknown upload error';
            }
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        
        // Upload files with improved error handling
        $uploadResults = [
            'picture' => uploadFile($_FILES['picture'], $uploadDir, ['image/jpeg', 'image/png'], $maxFileSize),
            'grades' => uploadFile($_FILES['grades'], $uploadDir, ['application/pdf'], $maxFileSize),
            'cor' => uploadFile($_FILES['cor'], $uploadDir, ['application/pdf'], $maxFileSize),
            'cet_result' => uploadFile($_FILES['cet_result'], $uploadDir, ['application/pdf'], $maxFileSize)
        ];

        // Check for upload errors
        $uploadErrors = [];
        $uploadPaths = [];
        foreach ($uploadResults as $fileType => $result) {
            if (!$result['success']) {
                $uploadErrors[] = ucfirst($fileType) . ': ' . $result['error'];
            } else {
                $uploadPaths[$fileType] = $result['path'];
            }
        }

        if (empty($uploadErrors)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO shifting (user_id, first_name, middle_name, last_name, wmsu_id, current_course, course_to_shift, reason_to_shift, picture, grades, cor, cet_result, status, requested_time) VALUES (:userId, :firstName, :middleName, :lastName, :wmsu_id, :currentCourse, :courseToShift, :reasonToShift, :picturePath, :gradesPath, :corPath, :cetResultPath, 'pending', :requestedTime)");
                $stmt->execute([
                    'userId' => $userId,
                    'firstName' => $firstName,
                    'middleName' => $middleName,
                    'lastName' => $lastName,
                    'wmsu_id' => $wmsu_id,
                    'currentCourse' => $currentCourse,
                    'courseToShift' => $courseToShift,
                    'reasonToShift' => $reasonToShift,
                    'picturePath' => $uploadPaths['picture'],
                    'gradesPath' => $uploadPaths['grades'],
                    'corPath' => $uploadPaths['cor'],
                    'cetResultPath' => $uploadPaths['cet_result'],
                    'requestedTime' => $_POST['requested_time']
                ]);
                
                $_SESSION['success'] = "Your shifting request has been submitted successfully.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
                
            } catch (PDOException $e) {
                $_SESSION['error'] = "Database error: " . $e->getMessage();
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $_SESSION['error'] = "Error uploading files. Please try again. Errors: " . implode(', ', $uploadErrors);
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

$pdo = null;
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
    <link rel="stylesheet"href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <!-- COLLEGE STUDENT / SHIFTING EXAM -->
</head>
<body>
    <!-- Navbar -->
    <?php shiftingNavbar($profile_image); ?>
    <div class="main-content">
        <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;"> Request for Shifting Form </div>
        <div class="message-container">
            <?php if (!empty($successMessage)): ?>
                <div class="success"><?= $successMessage ?></div>
            <?php endif; ?>
            <?php if (!empty($errorMessage)): ?>
                <div class="error"><?= $errorMessage ?></div>
            <?php endif; ?>
        </div>
        <div class="shift-form" style="padding: 70px 50px 70px 50px;">
            <form action="" method="post" enctype="multipart/form-data" id="shiftingForm" onsubmit="return showConfirmationModal(event)">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div style="display: flex; width: 100%; gap: 20px;">
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="first_name" style="font-size: 25px; color: white; font-weight: 600;">First Name</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" required style="font-size: 25px; width: 100%; border-radius: 7px; border: 2px solid #0b3822; padding: 7px 0 10px; margin-top: 5px; pointer-events: none; user-select: none; background-color:rgb(212, 212, 212); color:rgb(23, 69, 46);" readonly>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="middle_name" style="font-size: 25px; color: white; font-weight: 600;">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middleName); ?>" style="font-size: 25px; width: 100%; border-radius: 7px; border: 2px solid #0b3822; padding: 7px 0 10px; margin-top: 5px; pointer-events: none; user-select: none; background-color:rgb(212, 212, 212); color:rgb(23, 69, 46);" readonly>                
                    </div>
                </div>
                <div style="display: flex; width: 100%; gap: 20px; margin-top: 20px;">
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="last_name" style="font-size: 25px; color: white; font-weight: 600;">Last Name</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" required style="font-size: 25px; width: 100%; border-radius: 7px; border: 2px solid #0b3822; padding: 7px 0 10px; margin-top: 5px; pointer-events: none; user-select: none; background-color:rgb(212, 212, 212); color:rgb(23, 69, 46);" readonly>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="student_id" style="font-size: 25px; color: white; font-weight: 600;">Student ID</label>
                        <input type="text" id="school_id" name="school_id" value="<?php echo htmlspecialchars($wmsu_id); ?>" required style="font-size: 25px; width: 100%; border-radius: 7px; border: 2px solid #0b3822; padding: 7px 0 10px; margin-top: 5px; pointer-events: none; user-select: none; background-color:rgb(212, 212, 212); color:rgb(23, 69, 46);" readonly>
                    </div>
                </div>
                <div style="display: flex; width: 100%; gap: 20px; margin-top: 20px;">
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="course_to_shift" style="font-size: 25px; color: white; font-weight: 600;">Current Course</label>
                        <input type="text" id="current_course" name="current_course" value="<?php echo htmlspecialchars($currentCourse); ?>" required style="font-size: 25px; width: 100%; border-radius: 7px; border: 2px solid #0b3822; padding: 7px 0 10px; margin-top: 5px; pointer-events: none; user-select: none; background-color:rgb(212, 212, 212); color:rgb(23, 69, 46);" readonly>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                      <label for="course_to_shift" style="font-size: 25px; color: white; font-weight: 600;">Course to Shift</label>
                        <select id="course_to_shift" name="course_to_shift" required style="font-size: 25px; width: 100%; border-radius: 7px; border: 2px solid #0b3822; padding: 7px 0 10px; margin-top: 5px;">
                            <?php
                            $courses = [
                                "College of Agriculture" => [
                                    "BSA" => "Bachelor of Science in Agriculture",
                                    "BSFT" => "Bachelor of Science in Food Technology",
                                    "BSBA" => "Bachelor of Science in Agribusiness",
                                    "BAT" => "Bachelor of Agricultural Technology"
                                ],
                                "College of Liberal Arts" => [
                                    "ACCTANCY" => "Bachelor of Science in Accountancy",
                                    "BAH" => "Bachelor of Arts in History",
                                    "BAELS" => "Bachelor of Arts in English",
                                    "POLSCI" => "Bachelor of Arts in Political Science",
                                    "JOURNALISM" => "BA Mass Communication – Journalism",
                                    "BROADCASTING" => "BA Mass Communication – Broadcasting",
                                    "ECON" => "Bachelor of Science in Economics",
                                    "PSYCH" => "Bachelor of Science in Psychology"
                                ],
                                "College of Architecture" => [
                                    "ARCHI" => "Bachelor of Science in Architecture"
                                ],
                                "College of Nursing" => [
                                    "BSN" => "Bachelor of Science in Nursing"
                                ],
                                "College of Asian & Islamic Studies" => [
                                    "CAIS" => "College of Asian and Islamic Studies"
                                ],
                                "College of Computing Studies" => [
                                    "BSCS" => "Bachelor of Science in Computer Science",
                                    "BSIT" => "Bachelor of Science in Information Technology",
                                    "ACT" => "Associate in Computer Technology"
                                ],
                                "College of Forestry & Environmental Studies" => [
                                    "BSF" => "Bachelor of Science in Forestry",
                                    "BSAF" => "Bachelor of Science in Agroforestry",
                                    "BSES" => "Bachelor of Science in Environmental Science"
                                ],
                                "College of Criminal Justice Education" => [
                                    "CRIM" => "Bachelor of Science in Criminal Justice Education"
                                ],
                                "College of Home Economics" => [
                                    "BSHE" => "Bachelor of Science in Home Economics",
                                    "BSND" => "Bachelor of Science in Nutrition and Dietetics",
                                    "BSHM" => "Bachelor of Science in Home Management"
                                ],
                                "College of Engineering" => [
                                    "BSABE" => "BS Agricultural and Biosystems Engineering",
                                    "CE" => "BS Civil Engineering",
                                    "CPE" => "BS Computer Engineering",
                                    "BSEE" => "BS Electrical Engineering",
                                    "EE" => "BS Electronics Engineering",
                                    "ENVI" => "BS Environmental Engineering",
                                    "GEO" => "BS Geodetic Engineering",
                                    "IE" => "BS Industrial Engineering",
                                    "ME" => "BS Mechanical Engineering",
                                    "SE" => "BS Sanitary Engineering"
                                ],
                                "College of Public Administration & Development Studies" => [
                                    "PUBAD" => "Bachelor of Public Administration"
                                ],
                                "College of Sports Science & Physical Education" => [
                                    "BPED" => "Bachelor of Physical Education",
                                    "BSESS" => "Bachelor of Science in Exercise and Sports Sciences"
                                ],
                                "College of Science and Mathematics" => [
                                    "BIO" => "BS Biology",
                                    "CHEM" => "BS Chemistry",
                                    "MATH" => "BS Mathematics",
                                    "PHY" => "BS Physics",
                                    "STATS" => "BS Statistics"
                                ],
                                "College of Social Work & Community Development" => [
                                    "BSSW" => "Bachelor of Science in Social Work",
                                    "BSCD" => "Bachelor of Science in Community Development"
                                ],
                                "College of Teacher Education" => [
                                    "BCAED" => "Bachelor of Culture and Arts Education",
                                    "BECED" => "Bachelor of Early Childhood Education",
                                    "BEED" => "Bachelor of Elementary Education",
                                    "BSED" => "Bachelor of Secondary Education",
                                    "BSNED" => "Bachelor of Special Needs Education"
                                ]
                            ];
                            
                            foreach ($courses as $college => $collegeCourses) {
                                echo '<optgroup label="' . htmlspecialchars($college) . '">';
                                foreach ($collegeCourses as $code => $name) {
                                    // Skip the current course
                                    if ($code === $currentCourse) continue;
                                    echo '<option value="' . htmlspecialchars($code) . '">' . htmlspecialchars($name) . '</option>';
                                }
                                echo '</optgroup>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div style="width: 100%; margin-top: 20px;">
                    <div style="display: flex; flex-direction: column;">
                        <label for="reason_to_shift" style="font-size: 25px; color: white; font-weight: 600;">Reason to Shift</label>
                        <textarea id="reason_to_shift" name="reason_to_shift" required style="font-size: 25px; width: 100%; border-radius: 7px; border: 3px solid #0b3822; padding: 7px 0 10px; margin-top: 5px; min-height: 100px; resize: none;"></textarea>                    </div>
                   </div>
                <div style="display: flex; width: 100%; gap: 20px; margin-top: 30px;">
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="picture" style="font-size: 25px; color: white; font-weight: 600;">2x2 Picture with Name Tag (not selfie)*</label>
                        <div style="color: rgb(193, 255, 202); font-size: 14px; margin-bottom: 5px;">Accepted formats: JPG, PNG (Max: 5MB)</div>
                        <label class="custom-file-upload" style="margin-top: 5px;">
                            <input type="file" id="picture" name="picture" accept="image/jpeg,image/png" required onchange="showFileName('picture')">
                            <i class="fa-solid fa-upload"></i> Upload Picture
                        </label>
                        <span id="picture-file-name" class="file-name"></span>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="grades" style="font-size: 25px; color: white; font-weight: 600;">All Downloadable Grades*</label>
                        <div style="color: rgb(193, 255, 202); font-size: 14px; margin-bottom: 5px;">Accepted format: PDF (Max: 5MB)</div>
                        <label class="custom-file-upload" style="margin-top: 5px;">
                            <input type="file" id="grades" name="grades" accept="application/pdf" required onchange="showFileName('grades')">
                            <i class="fa-solid fa-upload"></i> Upload Grades
                        </label>
                        <span id="grades-file-name" class="file-name"></span>
                    </div>
                </div>

                <div style="display: flex; width: 100%; gap: 20px; margin-top: 30px;">
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="cor" style="font-size: 25px; color: white; font-weight: 600;">Latest COR*</label>
                        <div style="color: rgb(193, 255, 202); font-size: 14px; margin-bottom: 5px;">Accepted format: PDF (Max: 5MB)</div>
                        <label class="custom-file-upload" style="margin-top: 5px;">
                            <input type="file" id="cor" name="cor" accept="application/pdf" required onchange="showFileName('cor')">
                            <i class="fa-solid fa-upload"></i> Upload COR
                        </label>
                        <span id="cor-file-name" class="file-name"></span>
                    </div>
                    <div style="flex: 1; display: flex; flex-direction: column;">
                        <label for="cet_result" style="font-size: 25px; color: white; font-weight: 600;">College Entrance Test Result*</label>
                        <div style="color: rgb(193, 255, 202); font-size: 14px; margin-bottom: 5px;">Accepted format: PDF (Max: 5MB)</div>
                        <label class="custom-file-upload" style="margin-top: 5px;">
                            <input type="file" id="cet_result" name="cet_result" accept="application/pdf" required onchange="showFileName('cet_result')">
                            <i class="fa-solid fa-upload"></i> Upload CET Result
                        </label>
                        <span id="cet_result-file-name" class="file-name"></span>
                    </div>
                </div>
                 <div style="margin-top: 80px;">
                     <button class="submit" type="submit">
                         <i class="fa-regular fa-note-sticky" aria-hidden="true"></i>
                         Submit
                     </button>
                 </div>
            </form>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Review Your Shifting Request</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="warning-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Remember: You cannot edit or cancel your request once submitted. Please double check if all the requirements are correct and complete.</p>
                </div>
                
                <div class="review-section">
                    <h3>Personal Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>First Name:</label>
                            <span id="preview-firstName"></span>
                        </div>
                        <div class="info-item">
                            <label>Middle Name:</label>
                            <span id="preview-middleName"></span>
                        </div>
                        <div class="info-item">
                            <label>Last Name:</label>
                            <span id="preview-lastName"></span>
                        </div>
                        <div class="info-item">
                            <label>Student ID:</label>
                            <span id="preview-studentId"></span>
                        </div>
                    </div>

                    <h3>Course Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Current Course:</label>
                            <span id="preview-currentCourse"></span>
                        </div>
                        <div class="info-item">
                            <label>Course to Shift:</label>
                            <span id="preview-courseToShift"></span>
                        </div>
                        <div class="info-item">
                            <label>Preferred Time:</label>
                            <span id="preview-requestedTime"></span>
                        </div>
                    </div>

                    <h3>Reason for Shifting</h3>
                    <div class="reason-box">
                        <p id="preview-reason"></p>
                    </div>

                    <h3>Uploaded Documents</h3>
                    <div class="document-previews">
                        <div class="preview-item">
                            <label>2x2 Picture:</label>
                            <div id="picture-preview" class="preview-box"></div>
                        </div>
                        <div class="preview-item">
                            <label>Grades:</label>
                            <div id="grades-preview" class="preview-box pdf-preview"></div>
                        </div>
                        <div class="preview-item">
                            <label>COR:</label>
                            <div id="cor-preview" class="preview-box pdf-preview"></div>
                        </div>
                        <div class="preview-item">
                            <label>CET Result:</label>
                            <div id="cet-preview" class="preview-box pdf-preview"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn" onclick="closeModal()">Back to Edit</button>
                <button type="button" class="confirm-btn" onclick="submitForm()">Confirm Submission</button>
            </div>
        </div>
    </div>

    <script>
    function showConfirmationModal(event) {
        event.preventDefault();
        
        // Get form values
        document.getElementById('preview-firstName').textContent = document.getElementById('first_name').value;
        document.getElementById('preview-middleName').textContent = document.getElementById('middle_name').value;
        document.getElementById('preview-lastName').textContent = document.getElementById('last_name').value;
        document.getElementById('preview-studentId').textContent = document.getElementById('school_id').value;
        document.getElementById('preview-currentCourse').textContent = document.getElementById('current_course').value;
        
        const courseSelect = document.getElementById('course_to_shift');
        document.getElementById('preview-courseToShift').textContent = 
            courseSelect.options[courseSelect.selectedIndex].text;
            
        const timeSelect = document.getElementById('requested_time');
        document.getElementById('preview-requestedTime').textContent = 
            timeSelect.options[timeSelect.selectedIndex].text;
        
        document.getElementById('preview-reason').textContent = document.getElementById('reason_to_shift').value;

        // Preview uploaded files
        previewFile('picture', 'picture-preview', true);
        previewFile('grades', 'grades-preview', false);
        previewFile('cor', 'cor-preview', false);
        previewFile('cet_result', 'cet-preview', false);

        // Show modal with animation
        const modal = document.getElementById('confirmationModal');
        modal.style.display = 'block';
        // Trigger reflow
        modal.offsetHeight;
        modal.classList.add('show');
        
        return false;
    }

    function previewFile(inputId, previewId, isImage) {
        const file = document.getElementById(inputId).files[0];
        const previewElement = document.getElementById(previewId);
        
        if (file) {
            if (isImage) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewElement.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
                }
                reader.readAsDataURL(file);
            } else {
                // For PDFs, show icon and filename
                previewElement.innerHTML = `
                    <div style="text-align: center;">
                        <i class="far fa-file-pdf" style="font-size: 48px; color: #DC143C;"></i>
                        <p style="margin-top: 10px; word-break: break-all;">${file.name}</p>
                    </div>`;
            }
        }
    }

    function closeModal() {
        const modal = document.getElementById('confirmationModal');
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    function submitForm() {
        document.getElementById('shiftingForm').submit();
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('confirmationModal');
        if (event.target == modal) {
            closeModal();
        }
    }

    // Close modal when clicking the X
    document.querySelector('.close').onclick = closeModal;
    </script>

    <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
        <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
        <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
    </footer>

    <script src="/gcc/js/showFileName.js"></script>
    <script src="/gcc/js/sidebar.js"></script>
    <script src="/gcc/js/typing-text.js"></script>

</body>
</html>
