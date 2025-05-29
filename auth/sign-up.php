<?php
require_once '../font/font.php';
require_once('../database/database.php');

$error_messages = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $school = trim($_POST['school'] ?? '');
    $course_grade = trim($_POST['course_grade'] ?? '');
    $sex = $_POST['sex'] ?? '';
    $birth_date = trim($_POST['birth_date'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $wmsu_id = trim($_POST['wmsu_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // WMSU-specific validations
    if ($school === 'Western Mindanao State University') {
        if (!empty($email) && !str_ends_with(strtolower($email), '@wmsu.edu.ph')) {
            $error_messages['email'] = "Non-WMSU email addresses cannot select Western Mindanao State University";
            // Reset school to force user to select another school
            $school = '';
        }
    } else {
        if (!empty($wmsu_id) && $wmsu_id !== 'Guest ID') {
            $error_messages['wmsu_id'] = "Only WMSU students/staff can input a School ID";
        }
        
        if (!empty($email) && str_ends_with(strtolower($email), '@wmsu.edu.ph')) {
            $error_messages['email'] = "WMSU email can only be used if the school is Western Mindanao State University";
        }
    }

    // Basic validations
    if (empty($first_name)) {
        $error_messages['first_name'] = "First name is required.";
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $first_name)) {
        $error_messages['first_name'] = "Only letters and white space allowed";
    }

    if (empty($last_name)) {
        $error_messages['last_name'] = "Last name is required.";
    } elseif (!preg_match("/^[a-zA-Z-' ]*$/", $last_name)) {
        $error_messages['last_name'] = "Only letters and white space allowed";
    }

    $valid_schools = [
        "Western Mindanao State University",
        "Universidad de Zamboanga",
        "Ateneo de Zamboanga University",
        "Southern City Colleges",
        "Zamboanga City State Polytechnic College",
        "Zamboanga State College of Marine Sciences and Technology"
    ];

    if (!empty($school) && !in_array($school, $valid_schools)) {
        $error_messages['school'] = "Please select a valid school from the list.";
    }

    if ($password !== $confirm_password) {
        $error_messages['password'] = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error_messages['password'] = "Password must be at least 8 characters long.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }

    if (empty($birth_date)) {
        $error_messages['birth_date'] = "Birth date is required.";
    } else {
        $min_age = 10;
        $birth_date = DateTime::createFromFormat('Y-m-d', $birth_date);
        $current_date = new DateTime();
        $interval = $current_date->diff($birth_date);

        $age = $interval->y;

        if ($age < $min_age) {
            $error_messages['birth_date'] = "You must be at least 10 years old to sign up.";
        }
    }

    if (empty($course_grade) || $course_grade === 'None') {
        $course_grade = '';
    }

    $role = "";
    if (empty($course_grade)) {
        $role = "Outside Client";
    } elseif ($course_grade === "Junior High" || $course_grade === "Senior High") {
        $role = "High School Student";
    } else {
        $role = "College Student";
    }

    if (strpos($email, '@wmsu.edu.ph') !== false) {
        if (!empty($wmsu_id)) {
            if ($school !== 'Western Mindanao State University') {
                $error_messages['email'] = "WMSU email can only be used if the school is Western Mindanao State University.";
            } else {
                if (strlen($wmsu_id) == 6) {
                    $role = 'Faculty';
                } elseif (strlen($wmsu_id) == 9) {
                    if (empty($course_grade)) {
                        $role = 'College Student';
                    } elseif ($course_grade === "Junior High" || $course_grade === "Senior High") {
                        $role = 'High School Student';
                    } else {
                        $role = 'College Student';
                    }
                } else {
                    $error_messages['wmsu_id'] = "WMSU ID must be 6 digits for faculty or 9 digits for students.";
                }
            }
        }
    } else {
        if (!empty($wmsu_id)) {
            $error_messages['wmsu_id'] = "WMSU ID should not be filled for non-WMSU email addresses.";
        }
        $role = 'Outside Client';
        $wmsu_id = "Guest ID";
    }

    if ($school !== 'Western Mindanao State University' && strpos($email, '@wmsu.edu.ph') === false) {
        $role = 'Outside Client';
    }

    if (empty($course_grade) && $role === 'Outside Client') {
        if (strpos($email, '@wmsu.edu.ph') !== false) {
            $error_messages['email'] = "Outside clients cannot use a WMSU email address.";
        }
        $wmsu_id = "Guest ID";
    }

    if (empty($error_messages)) {
        $email_check_query = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($email_check_query);
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            $error_messages['email'] = "This email is already registered. Please use another email.";
        }

        if (strpos($email, '@wmsu.edu.ph') !== false && !empty($wmsu_id)) {
            $wmsu_id_check_query = "SELECT * FROM users WHERE wmsu_id = :wmsu_id";
            $stmt = $pdo->prepare($wmsu_id_check_query);
            $stmt->execute([':wmsu_id' => $wmsu_id]);

            if ($stmt->rowCount() > 0) {
                $error_messages['wmsu_id'] = "This WMSU ID is already registered. Please use another ID.";
            }
        }

        $contact_number_check_query = "SELECT * FROM users WHERE contact_number = :contact_number";
        $stmt = $pdo->prepare($contact_number_check_query);
        $stmt->execute([':contact_number' => $contact_number]);

        if ($stmt->rowCount() > 0) {
            $error_messages['contact_number'] = "This contact number is already registered. Please use another contact number.";
        }

        if (empty($error_messages)) {
            $sql = "INSERT INTO users (first_name, middle_name, last_name, school, course_grade, sex, age, contact_number, address, civil_status, occupation, wmsu_id, email, password, role) 
                    VALUES (:first_name, :middle_name, :last_name, :school, :course_grade, :sex, :age, :contact_number, :address, :civil_status, :occupation, :wmsu_id, :email, :password, :role)";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':first_name' => $first_name,
                    ':middle_name' => $middle_name,
                    ':last_name' => $last_name,
                    ':school' => $school ?: 'None',
                    ':course_grade' => $course_grade ?: 'None',
                    ':sex' => $sex,
                    ':age' => $age, 
                    ':contact_number' => $contact_number,
                    ':address' => $address,
                    ':civil_status' => $civil_status,
                    ':occupation' => $occupation,
                    ':wmsu_id' => $wmsu_id ?: 'Guest ID',
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':role' => $role,
                ]);

                header("Location: sign-in.php");
                exit();
            } catch (PDOException $e) {
                $error_messages['database'] = "Error: " . $e->getMessage();
            }
        }
    }
}

function getFormValue($field) {
    return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '';
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>Sign Up</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="css/sign-up.css">
</head>
<body>
    <?php if (!empty($error_messages)): ?>
        <div class="error-popup" id="errorPopup">
            <?php 
            // Display all error messages
            foreach($error_messages as $error) {
                echo htmlspecialchars($error) . "<br>";
            }
            ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="sign-up.php" id="multiStepForm">
        <div class="header-container">
            <img id="gcc-logo" src="/gcc/img/gcc-logo.png" alt="GCC Logo">
            <img id="wmsu-logo" src="/gcc/img/wmsu-logo.png" alt="GCC Logo">
            <div class="text">
                <h1>Guidance and Counseling Center</h1>
                <p>Western Mindanao State University</p>
            </div>
        </div>
        
        <!-- Progress Stepper -->
        <div class="progress-stepper">
            <div class="step active">
                <div class="circle">1</div>
                <p class="step-text">Security</p>
            </div>
            <div class="step">
                <div class="circle">2</div>
                <p class="step-text">General Info</p>
            </div>
            <div class="step">
                <div class="circle">3</div>
                <p class="step-text">Additional Info</p>
            </div>
            <div class="step">
                <div class="circle">4</div>
                <p class="step-text">School Info</p>
                <p class="step-text-2">(Optional)</p>
            </div>
            <div class="step">
                <div class="circle">5</div>
                <p class="step-text">Contact Info</p>
            </div>
        </div>

        <!-- Step 1: Security -->
        <div class="form-step active">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required value="<?= getFormValue('email') ?>">
                <?php if (isset($error_messages['email'])): ?>
                    <span class="error-message"><?= $error_messages['email'] ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($error_messages['password'])): ?>
                    <span class="error-message"><?= $error_messages['password'] ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="confirm-password">Re-enter Password</label>
                <input type="password" id="confirm-password" name="confirm_password" required>
            </div>
            <button type="button" class="next-btn">
                <span>Next</span>
                <i class="fa-solid fa-circle-chevron-right"></i>
            </button>
        </div>

        <!-- Step 2: General Info -->
        <div class="form-step">
            <div class="form-group">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first_name" required value="<?= getFormValue('first_name') ?>" class="upper-input">
                <?php if (isset($error_messages['first_name'])): ?>
                    <span class="error-message"><?= $error_messages['first_name'] ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="middle-name">Middle Name (optional)</label>
                <input type="text" id="middle-name" name="middle_name" value="<?= getFormValue('middle_name') ?>" class="upper-input">
            </div>
            <div class="form-group">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last_name" required value="<?= getFormValue('last_name') ?>" class="upper-input">
                <?php if (isset($error_messages['last_name'])): ?>
                    <span class="error-message"><?= $error_messages['last_name'] ?></span>
                <?php endif; ?>
            </div>
            <div class="button-group">
                <button type="button" class="prev-btn">
                    <i class="fa-solid fa-circle-chevron-left"></i> Previous
                </button>
                <button type="button" class="next-btn">
                    <span>Next</span>
                    <i class="fa-solid fa-circle-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Step 3: Additional Info -->
        <div class="form-step">
            <div class="form-group">
                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="Male" <?= (getFormValue('sex') === 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= (getFormValue('sex') === 'Female') ? 'selected' : '' ?>>Female</option>
                    <option value="Prefer not to say" <?= (getFormValue('sex') === 'Prefer not to say') ? 'selected' : '' ?>>Prefer not to say</option>
                </select>
                <?php if (isset($error_messages['sex'])): ?>
                    <span class="error-message"><?= $error_messages['sex'] ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="civil-status">Civil Status</label>
                <select id="civil-status" name="civil_status" required>
                    <option value="Single" <?= (getFormValue('civil_status') === 'Single') ? 'selected' : '' ?>>Single</option>
                    <option value="Married" <?= (getFormValue('civil_status') === 'Married') ? 'selected' : '' ?>>Married</option>
                    <option value="Widowed" <?= (getFormValue('civil_status') === 'Widowed') ? 'selected' : '' ?>>Widowed</option>
                    <option value="Divorced" <?= (getFormValue('civil_status') === 'Divorced') ? 'selected' : '' ?>>Divorced</option>
                    <option value="Separated" <?= (getFormValue('civil_status') === 'Separated') ? 'selected' : '' ?>>Separated</option>
                </select>
                <?php if (isset($error_messages['civil_status'])): ?>
                    <span class="error-message"><?= $error_messages['civil_status'] ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="birth-date">Birthdate</label>
                <input type="date" id="birth-date" name="birth_date" required value="<?= getFormValue('birth_date') ?>"
                max="<?= date('Y-m-d', strtotime('-10 years')) ?>">
                <?php if (isset($error_messages['birth_date'])): ?>
                    <span class="error-message"><?= $error_messages['birth_date'] ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="occupation">Occupation</label>
                <select id="occupation" name="occupation" required>
                    <option value="Student" <?= (getFormValue('occupation') === 'Student') ? 'selected' : '' ?>>Student</option>
                    <option value="Employee" <?= (getFormValue('occupation') === 'Employee') ? 'selected' : '' ?>>Employee</option>
                    <option value="Self-employed" <?= (getFormValue('occupation') === 'Self-employed') ? 'selected' : '' ?>>Self-employed</option>
                    <option value="Unemployed" <?= (getFormValue('occupation') === 'Unemployed') ? 'selected' : '' ?>>Unemployed</option>
                    <option value="Other" <?= (getFormValue('occupation') === 'Other') ? 'selected' : '' ?>>Other</option>
                </select>
                <?php if (isset($error_messages['occupation'])): ?>
                    <span class="error-message"><?= $error_messages['occupation'] ?></span>
                <?php endif; ?>
            </div>
            <div class="button-group">
                <button type="button" class="prev-btn">
                    <i class="fa-solid fa-circle-chevron-left"></i> Previous
                </button>
                <button type="button" class="next-btn">
                    <span>Next</span>
                    <i class="fa-solid fa-circle-chevron-right"></i>
                </button>
            </div>
        </div>

       <!-- Step 4: School Info -->
        <div class="form-step">
            <div class="form-group">
                <label for="school">School (optional)</label>
                <input type="text" id="school" name="school" list="schoolList" value="<?= getFormValue('school') ?>">
                <?php if (isset($error_messages['school'])): ?>
                    <span class="error-message"><?= $error_messages['school'] ?></span>
                <?php endif; ?>
                <datalist id="schoolList">
                    <option value="Western Mindanao State University">
                    <option value="Universidad de Zamboanga">
                    <option value="Ateneo de Zamboanga University">
                    <option value="Southern City Colleges">
                    <option value="Zamboanga City State Polytechnic College">
                    <option value="Zamboanga State College of Marine Sciences and Technology">
                </datalist>
            </div>
            <div class="form-group">
                <label for="wmsu-id">School ID (optional)</label>
                <input type="text" id="wmsu-id" name="wmsu_id" value="<?= getFormValue('wmsu_id') ?>">
                <?php if (isset($error_messages['wmsu_id'])): ?>
                    <span class="error-message"><?= $error_messages['wmsu_id'] ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
              <label for="course-grade">Course / Grade Level (optional)</label>
              <select id="course-grade" name="course_grade">
              <optgroup label="Select Course / Grade Level" style="color:rgb(61, 61, 61);">
                <option value="" <?= (empty(getFormValue('course_grade'))) ? 'selected' : '' ?>>None</option>
                </optgroup>
                <optgroup label="High School">
                <option value="Junior High" <?= (getFormValue('course_grade') === 'Junior High') ? 'selected' : '' ?>>Junior High</option>
                <option value="Senior High" <?= (getFormValue('course_grade') === 'Senior High') ? 'selected' : '' ?>>Senior High</option>
                </optgroup>
            
                <optgroup label="College of Agriculture">
                  <option value="BSA" <?= (getFormValue('course_grade') === 'BSA') ? 'selected' : '' ?>>Bachelor of Science in Agriculture</option>
                  <option value="BSFT" <?= (getFormValue('course_grade') === 'BSFT') ? 'selected' : '' ?>>Bachelor of Science in Food Technology</option>
                  <option value="BSBA" <?= (getFormValue('course_grade') === 'BSBA') ? 'selected' : '' ?>>Bachelor of Science in Agribusiness</option>
                  <option value="BAT" <?= (getFormValue('course_grade') === 'BAT') ? 'selected' : '' ?>>Bachelor of Agricultural Technology</option>
                </optgroup>
            
                <optgroup label="College of Liberal Arts">
                  <option value="ACCTANCY" <?= (getFormValue('course_grade') === 'ACCTANCY') ? 'selected' : '' ?>>Bachelor of Science in Accountancy</option>
                  <option value="BAH" <?= (getFormValue('course_grade') === 'BAH') ? 'selected' : '' ?>>Bachelor of Arts in History</option>
                  <option value="BAELS" <?= (getFormValue('course_grade') === 'BAELS') ? 'selected' : '' ?>>Bachelor of Arts in English</option>
                  <option value="POLSCI" <?= (getFormValue('course  _grade') === 'POLSCI') ? 'selected' : '' ?>>Bachelor of Arts in Political Science</option>
                  <option value="JOURNALISM" <?= (getFormValue('course_grade') === 'JOURNALISM') ? 'selected' : '' ?>>BA Mass Communication – Journalism</option>
                  <option value="BROADCASTING" <?= (getFormValue('course_grade') === 'BROADCASTING') ? 'selected' : '' ?>>BA Mass Communication – Broadcasting</option>
                  <option value="ECON" <?= (getFormValue('course_grade') === 'ECON') ? 'selected' : '' ?>>Bachelor of Science in Economics</option>
                  <option value="PSYCH" <?= (getFormValue('course_grade') === 'PSYCH') ? 'selected' : '' ?>>Bachelor of Science in Psychology</option>
                </optgroup>
            
                <optgroup label="College of Architecture">
                  <option value="ARCHI" <?= (getFormValue('course_grade') === 'ARCHI') ? 'selected' : '' ?>>Bachelor of Science in Architecture</option>
                </optgroup>
            
                <optgroup label="College of Nursing">
                  <option value="BSN" <?= (getFormValue('course_grade') === 'BSN') ? 'selected' : '' ?>>Bachelor of Science in Nursing</option>
                </optgroup>
            
                <optgroup label="College of Asian & Islamic Studies">
                  <option value="CAIS" <?= (getFormValue('course_grade') === 'CAIS') ? 'selected' : '' ?>>College of Asian and Islamic Studies</option>
                </optgroup>
            
                <optgroup label="College of Computing Studies">
                  <option value="BSCS" <?= (getFormValue('course_grade') === 'BSCS') ? 'selected' : '' ?>>Bachelor of Science in Computer Science</option>
                  <option value="BSIT" <?= (getFormValue('course_grade') === 'BSIT') ? 'selected' : '' ?>>Bachelor of Science in Information Technology</option>
                  <option value="ACT" <?= (getFormValue('course_grade') === 'ACT') ? 'selected' : '' ?>>Associate in Computer Technology</option>
                </optgroup>
            
                <optgroup label="College of Forestry & Environmental Studies">
                  <option value="BSF" <?= (getFormValue('course_grade') === 'BSF') ? 'selected' : '' ?>>Bachelor of Science in Forestry</option>
                  <option value="BSAF" <?= (getFormValue('course_grade') === 'BSAF') ? 'selected' : '' ?>>Bachelor of Science in Agroforestry</option>
                  <option value="BSES" <?= (getFormValue('course_grade') === 'BSES') ? 'selected' : '' ?>>Bachelor of Science in Environmental Science</option>
                </optgroup>
            
                <optgroup label="College of Criminal Justice Education">
                  <option value="CRIM" <?= (getFormValue('course_grade') === 'CRIM') ? 'selected' : '' ?>>Bachelor of Science in Criminal Justice Education</option>
                </optgroup>
            
                <optgroup label="College of Home Economics">
                  <option value="BSHE" <?= (getFormValue('course_grade') === 'BSHE') ? 'selected' : '' ?>>Bachelor of Science in Home Economics</option>
                  <option value="BSND" <?= (getFormValue('course_grade') === 'BSND') ? 'selected' : '' ?>>Bachelor of Science in Nutrition and Dietetics</option>
                  <option value="BSHM" <?= (getFormValue('course_grade') === 'BSHM') ? 'selected' : '' ?>>Bachelor of Science in Home Management</option>
                </optgroup>
            
                <optgroup label="College of Engineering">
                  <option value="BSABE" <?= (getFormValue('course_grade') === 'BSABE') ? 'selected' : '' ?>>BS Agricultural and Biosystems Engineering</option>
                  <option value="CE" <?= (getFormValue('course_grade') === 'CE') ? 'selected' : '' ?>>BS Civil Engineering</option>
                  <option value="CPE" <?= (getFormValue('course_grade') === 'CPE') ? 'selected' : '' ?>>BS Computer Engineering</option>
                  <option value="BSEE" <?= (getFormValue('course_grade') === 'BSEE') ? 'selected' : '' ?>>BS Electrical Engineering</option>
                  <option value="EE" <?= (getFormValue('course_grade') === 'EE') ? 'selected' : '' ?>>BS Electronics Engineering</option>
                  <option value="ENVI" <?= (getFormValue('course_grade') === 'ENVI') ? 'selected' : '' ?>>BS Environmental Engineering</option>
                  <option value="GEO" <?= (getFormValue('course_grade') === 'GEO') ? 'selected' : '' ?>>BS Geodetic Engineering</option>
                  <option value="IE" <?= (getFormValue('course_grade') === 'IE') ? 'selected' : '' ?>>BS Industrial Engineering</option>
                  <option value="ME" <?= (getFormValue('course_grade') === 'ME') ? 'selected' : '' ?>>BS Mechanical Engineering</option>
                  <option value="SE" <?= (getFormValue('course_grade') === 'SE') ? 'selected' : '' ?>>BS Sanitary Engineering</option>
                </optgroup>
            
                <optgroup label="College of Public Administration & Development Studies">
                  <option value="PUBAD" <?= (getFormValue('course_grade') === 'PUBAD') ? 'selected' : '' ?>>Bachelor of Public Administration</option>
                </optgroup>
            
                <optgroup label="College of Sports Science & Physical Education">
                  <option value="BPED" <?= (getFormValue('course_grade') === 'BPED') ? 'selected' : '' ?>>Bachelor of Physical Education</option>
                  <option value="BSESS" <?= (getFormValue('course_grade') === 'BSESS') ? 'selected' : '' ?>>Bachelor of Science in Exercise and Sports Sciences</option>
                </optgroup>
            
                <optgroup label="College of Science and Mathematics">
                  <option value="BIO" <?= (getFormValue('course_grade') === 'BIO') ? 'selected' : '' ?>>BS Biology</option>
                  <option value="CHEM" <?= (getFormValue('course_grade') === 'CHEM') ? 'selected' : '' ?>>BS Chemistry</option>
                  <option value="MATH" <?= (getFormValue('course_grade') === 'MATH') ? 'selected' : '' ?>>BS Mathematics</option>
                  <option value="PHY" <?= (getFormValue('course_grade') === 'PHY') ? 'selected' : '' ?>>BS Physics</option>
                  <option value="STATS" <?= (getFormValue('course_grade') === 'STATS') ? 'selected' : '' ?>>BS Statistics</option>
                </optgroup>
            
                <optgroup label="College of Social Work & Community Development">
                  <option value="BSSW" <?= (getFormValue('course_grade') === 'BSSW') ? 'selected' : '' ?>>Bachelor of Science in Social Work</option>
                  <option value="BSCD" <?= (getFormValue('course_grade') === 'BSCD') ? 'selected' : '' ?>>Bachelor of Science in Community Development</option>
                </optgroup>
            
                <optgroup label="College of Teacher Education">
                  <option value="BCAED" <?= (getFormValue('course_grade') === 'BCAED') ? 'selected' : '' ?>>Bachelor of Culture and Arts Education</option>
                  <option value="BECED" <?= (getFormValue('course_grade') === 'BECED') ? 'selected' : '' ?>>Bachelor of Early Childhood Education</option>
                  <option value="BEED" <?= (getFormValue('course_grade') === 'BEED') ? 'selected' : '' ?>>Bachelor of Elementary Education</option>
                  <option value="BSED" <?= (getFormValue('course_grade') === 'BSED') ? 'selected' : '' ?>>Bachelor of Secondary Education</option>
                  <option value="BSNED" <?= (getFormValue('course_grade') === 'BSNED') ? 'selected' : '' ?>>Bachelor of Special Needs Education</option>
                </optgroup>
              </select>
            </div>
            <div class="button-group">
                <button type="button" class="prev-btn">
                    <i class="fa-solid fa-circle-chevron-left"></i> Previous
                </button>
                <button type="button" class="next-btn">
                    <span>Next</span>
                    <i class="fa-solid fa-circle-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Step 5: Contact Info -->
        <div class="form-step">
            <div class="form-group">
                <label for="contact-number">Contact Number</label>
                <input type="text" id="contact-number" name="contact_number" required value="<?= getFormValue('contact_number') ?>">
                <?php if (isset($error_messages['contact_number'])): ?>
                    <span class="error-message"><?= $error_messages['contact_number'] ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group" style="position: relative;">
              <label for="address">Address</label>
              <input type="text" id="address" name="address" required value="<?= getFormValue('address') ?>" placeholder="Enter your address" autocomplete="off">
              <ul id="suggestions"></ul>
              <?php if (isset($error_messages['address'])): ?>
                  <span class="error-message"><?= $error_messages['address'] ?></span>
              <?php endif; ?>
            </div>
            
            <!-- Hidden fields if you want to store parts -->
            <input type="hidden" id="city" name="city" value="<?= getFormValue('city') ?>">
            <input type="hidden" id="province" name="province" value="<?= getFormValue('province') ?>">
            <input type="hidden" id="country" name="country" value="<?= getFormValue('country') ?>">

            <div class="button-group">
                <button type="button" class="prev-btn">
                    <i class="fa-solid fa-circle-chevron-left"></i> Previous
                </button>
                <button type="submit" class="sub-btn">Sign Up</button>
            </div>
        </div>
        
        <div class="signin-text">
            <p>Already have an account? <a href="../auth/sign-in.php" class="signup">Sign In</a></p>
        </div>
    </form>

    <!-- JS -->
    <script>
    <?php if (!empty($error_messages)): ?>
        var errorMessages = <?= json_encode($error_messages) ?>;
    <?php endif; ?>
    </script>
    <script src="/gcc/js/stepper-form.js"></script>
    <script src="/gcc/js/error-message.js"></script>
    <script src="/gcc/js/validation-signup.js"></script>
    <script src="/gcc/js/auto-capslock.js"></script>
    <script src="/gcc/js/address-autocomplete.js"></script>
    <script src="/gcc/js/school-validation.js"></script>
</body>
</html>