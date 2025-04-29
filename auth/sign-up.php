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
    $age = trim($_POST['age'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $occupation = trim($_POST['occupation'] ?? '');
    $wmsu_id = trim($_POST['wmsu_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

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

    // if ($age < 12) {
    //     $error_messages['age'] = "Must be at least 12+ to sign up.";
    // }

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
                <input type="text" id="first-name" name="first_name" required value="<?= getFormValue('first_name') ?>">
                <?php if (isset($error_messages['first_name'])): ?>
                    <span class="error-message"><?= $error_messages['first_name'] ?></span>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="middle-name">Middle Name (optional)</label>
                <input type="text" id="middle-name" name="middle_name" value="<?= getFormValue('middle_name') ?>">
            </div>
            <div class="form-group">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last_name" required value="<?= getFormValue('last_name') ?>">
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
                <label for="sex">Gender</label>
                <select id="sex" name="sex" required>
                    <option value="Male" <?= (getFormValue('sex') === 'Male') ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= (getFormValue('sex') === 'Female') ? 'selected' : '' ?>>Female</option>
                    <option value="Prefer not to say" <?= (getFormValue('sex') === 'Prefer not to say') ? 'selected' : '' ?>>Prefer not to say</option>
                </select>
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
            </div>
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" required value="<?= getFormValue('age') ?>">
                <?php if (isset($error_messages['age'])): ?>
                    <span class="error-message"><?= $error_messages['age'] ?></span>
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
                    <option value="" <?= (empty(getFormValue('course_grade'))) ? 'selected' : '' ?>>None</option>
                    <option value="Junior High" <?= (getFormValue('course_grade') === 'Junior High') ? 'selected' : '' ?>>Junior High</option>
                    <option value="Senior High" <?= (getFormValue('course_grade') === 'Senior High') ? 'selected' : '' ?>>Senior High</option>
                    <option value="BSCS" <?= (getFormValue('course_grade') === 'BSCS') ? 'selected' : '' ?>>Computer Science</option>
                    <option value="BSIT" <?= (getFormValue('course_grade') === 'BSIT') ? 'selected' : '' ?>>Information Technology</option>
                    <option value="ACT" <?= (getFormValue('course_grade') === 'ACT') ? 'selected' : '' ?>>Associate in Computer Technology</option>
                    <option value="BSN" <?= (getFormValue('course_grade') === 'BSN') ? 'selected' : '' ?>>Nursing</option>
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
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" required value="<?= getFormValue('address') ?>">
            </div>
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

    <script>
        <?php if (!empty($error_messages)): ?>
            var errorMessages = <?= json_encode($error_messages) ?>;
        <?php endif; ?>
    </script>
    <script src="/gcc/js/stepper-form.js"></script>
    <script src="/gcc/js/validation-signup"></script>
</body>
</html>