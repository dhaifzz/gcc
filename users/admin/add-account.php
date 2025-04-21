<?php
require_once '../../font/font.php';
require_once('../../database/database.php');

session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

// Initialize all variables
$first_name = $middle_name = $last_name = $school = $course_grade = $sex = $age = '';
$contact_number = $address = $civil_status = $occupation = $wmsu_id = $email = '';
$password = $confirm_password = '';
$role = 'Admin';
$hashed_password = '';
$validation_errors = [];
$success_message = '';

// Check for success message in session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Assign POST data with null coalescing
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $school = trim($_POST['school'] ?? '');
    $course_grade = trim($_POST['course_grade'] ?? '');
    $sex = $_POST['sex'] ?? '';
    $age = trim($_POST['age'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $civil_status = $_POST['civil_status'] ?? '';
    $occupation = $_POST['occupation'] ?? '';
    $wmsu_id = trim($_POST['wmsu_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'Admin');

    // Validation checks
    $valid_schools = [
        "Western Mindanao State University",
        "Universidad de Zamboanga",
        "Ateneo de Zamboanga University",
        "Southern City Colleges",
        "Zamboanga City State Polytechnic College",
        "Zamboanga State College of Marine Sciences and Technology"
    ];

    $valid_roles = ['Student', 'Outside Client', 'Faculty', 'Director', 'Admin', 'Staff'];
    if (!in_array($role, $valid_roles)) {
        $validation_errors['role'] = "Please select a valid role.";
    }

    if (!in_array($school, $valid_schools)) {
        $validation_errors['school'] = "Please select a valid school from the list.";
    }

    if ($password !== $confirm_password) {
        $validation_errors['password'] = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($age < 12) {
        $validation_errors['age'] = "Must be at least 12+ to sign up.";
    }

    $is_wmsu_email = strpos($email, '@wmsu.edu.ph') !== false;
    $wmsu_id_required = $is_wmsu_email || ($course_grade !== 'None' && $role !== 'Outside Client');

    // Validate WMSU Email
    if ($role === 'Outside Client' && $is_wmsu_email) {
        $validation_errors['email'] = "Outside Clients cannot use a WMSU email (@wmsu.edu.ph).";
    }

    // Validate WMSU ID
    if ($wmsu_id_required) {
        if (empty($wmsu_id) || !is_numeric($wmsu_id)) {
            $validation_errors['wmsu_id'] = "WMSU ID is required and must be numeric.";
        } elseif (strlen($wmsu_id) !== 6 && strlen($wmsu_id) !== 9) {
            $validation_errors['wmsu_id'] = "WMSU ID must be 6 or 9 digits.";
        }
    } else {
        if (!empty($wmsu_id)) {
            $validation_errors['wmsu_id'] = "Only WMSU accounts should have a WMSU ID.";
        }
        $wmsu_id = "<i>Guest ID</i>";
    }

    // Additional validation for Staff and Director roles
    if (($role === 'Staff' || $role === 'Director') && (!$is_wmsu_email || $school !== 'Western Mindanao State University')) {
        $validation_errors['role'] = "Staff and Director roles require a WMSU email and the school must be Western Mindanao State University.";
    }

    if (empty($validation_errors)) {
        // Check for existing email
        $email_check_query = "SELECT * FROM users WHERE email = :email";
        $stmt = $pdo->prepare($email_check_query);
        $stmt->execute([':email' => $email]);

        if ($stmt->rowCount() > 0) {
            $validation_errors['email'] = "This email is already registered. Please use another email.";
        }

        // Check for existing WMSU ID if applicable
        if ($is_wmsu_email && !empty($wmsu_id)) {
            $wmsu_id_check_query = "SELECT * FROM users WHERE wmsu_id = :wmsu_id";
            $stmt = $pdo->prepare($wmsu_id_check_query);
            $stmt->execute([':wmsu_id' => $wmsu_id]);

            if ($stmt->rowCount() > 0) {
                $validation_errors['wmsu_id'] = "This WMSU ID is already registered. Please use another ID.";
            }
        }

        // Check for existing contact number
        $contact_number_check_query = "SELECT * FROM users WHERE contact_number = :contact_number";
        $stmt = $pdo->prepare($contact_number_check_query);
        $stmt->execute([':contact_number' => $contact_number]);

        if ($stmt->rowCount() > 0) {
            $validation_errors['contact_number'] = "This contact number is already registered. Please use another contact number.";
        }

        if (empty($validation_errors)) {
            // Define SQL query here after all validations pass
            $sql = "INSERT INTO users (first_name, middle_name, last_name, school, course_grade, sex, age, contact_number, address, civil_status, occupation, wmsu_id, email, password, role) 
                    VALUES (:first_name, :middle_name, :last_name, :school, :course_grade, :sex, :age, :contact_number, :address, :civil_status, :occupation, :wmsu_id, :email, :password, :role)";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':first_name' => $first_name,
                    ':middle_name' => $middle_name,
                    ':last_name' => $last_name,
                    ':school' => $school,
                    ':course_grade' => $course_grade,
                    ':sex' => $sex,
                    ':age' => $age,
                    ':contact_number' => $contact_number,
                    ':address' => $address,
                    ':civil_status' => $civil_status,
                    ':occupation' => $occupation,
                    ':wmsu_id' => $wmsu_id,
                    ':email' => $email,
                    ':password' => $hashed_password,
                    ':role' => $role,
                ]);

                $_SESSION['success_message'] = "Account created successfully!";
                header("Location: admin.php");
                exit();
            } catch (PDOException $e) {
                $validation_errors['database'] = "Error: " . $e->getMessage();
            }
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
    <title>GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/add-account.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body>
        <!-- Sidebar -->
        <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
            <a href="admin.php"><i class="fa-solid fa-users"></i> Manage Users</a>
            <a href="add-account.php"style=" background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-solid fa-chart-bar"></i> Create Account</a>
            <a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a>
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="sidebar-footer">
        <small>© 2025 WMSU </small>
        <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
    </div>
    </div>
    
    <div class="container">
    <div style="display: flex; justify-content: flex-start; align-items: center; margin-bottom: 50px;">
    <h2 style="font-weight: 600; margin: 0;"> 
        <i class="fa-solid fa-users"></i>
        GCC's User Accounts
    </h2>
    </div>
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success_message; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <?php if (!empty($validation_errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    <?php foreach ($validation_errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <form method="POST" action="add-account.php">
            <div class="row form-row">
                <div class="col-md-6">
                    <label for="first-name">First Name</label>
                    <input type="text" id="first-name" name="first_name" class="form-control" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : ''; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="middle-name">Middle Name (optional)</label>
                    <input type="text" id="middle-name" name="middle_name" class="form-control" value="<?php echo isset($_POST['middle_name']) ? $_POST['middle_name'] : ''; ?>">
                </div>
            </div>
            <div class="row form-row">
                <div class="col-md-6">
                    <label for="last-name">Last Name</label>
                    <input type="text" id="last-name" name="last_name" class="form-control" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : ''; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="school">School</label>
                    <input type="text" id="school" name="school" class="form-control" placeholder="Search school..." list="schoolList" required autocomplete="off" value="<?php echo isset($_POST['school']) ? htmlspecialchars($_POST['school']) : ''; ?>">
                    <datalist id="schoolList">
                        <option value="Western Mindanao State University">
                        <option value="Universidad de Zamboanga">
                        <option value="Ateneo de Zamboanga University">
                        <option value="Southern City Colleges">
                        <option value="Zamboanga City State Polytechnic College">
                        <option value="Zamboanga State College of Marine Sciences and Technology">
                    </datalist>
                </div>
            </div>
            <div class="row form-row">
                <div class="col-md-6">
                    <label for="course-grade">Course / Grade Level</label>
                    <select id="course-grade" name="course_grade" class="form-control" required>
                        <option value="None" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'None') ? 'selected' : ''; ?>>None</option>
                        <option value="Junior High" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'Junior High') ? 'selected' : ''; ?>>Junior High</option>
                        <option value="Senior High" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'Senior High') ? 'selected' : ''; ?>>Senior High</option>
                        <option value="BSCS" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'BSCS') ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="BSIT" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'BSIT') ? 'selected' : ''; ?>>Information Technology</option>
                        <option value="ACT" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'ACT') ? 'selected' : ''; ?>>Associate in Computer Technology</option>
                        <option value="BSN" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'BSN') ? 'selected' : ''; ?>>Nursing</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="sex">Sex</label>
                    <select id="sex" name="sex" class="form-control" required>
                        <option value="Male" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Prefer not to say" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
                    </select>
                </div>
            </div>
            <div class="row form-row">
                <div class="col-md-6">
                    <label for="age">Age</label>
                    <input type="number" id="age" name="age" class="form-control" value="<?php echo isset($_POST['age']) ? $_POST['age'] : ''; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="contact-number">Contact Number</label>
                    <input type="text" id="contact-number" name="contact_number" class="form-control" value="<?php echo isset($_POST['contact_number']) ? $_POST['contact_number'] : ''; ?>" required>
                </div>
            </div>
            <div class="row form-row">
                <div class="col-md-6">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" class="form-control" value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="occupation">Occupation</label>
                    <select id="occupation" name="occupation" class="form-control" required>
                        <option value="Student" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                        <option value="Employee" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Employee') ? 'selected' : ''; ?>>Employee</option>
                        <option value="Self-employed" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Self-employed') ? 'selected' : ''; ?>>Self-employed</option>
                        <option value="Unemployed" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Unemployed') ? 'selected' : ''; ?>>Unemployed</option>
                        <option value="Other" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>
            <div class="row form-row">
                <div class="col-md-6">
                    <label for="wmsu-id">WMSU ID Number</label>
                    <input type="text" id="wmsu-id" name="wmsu_id" class="form-control" placeholder="For WMSU applicants only" style="font-style: italic;" value="<?php echo isset($_POST['wmsu_id']) ? htmlspecialchars($_POST['wmsu_id']) : ''; ?>" maxlength="15" title="WMSU ID must be 6 or 9 digits">
                </div>
                <div class="col-md-6">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required>
                </div>
            </div>
            <div class="row form-row">
                <div class="col-md-6">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <span class="toggle-password" onclick="togglePassword('password', this)"></span>
                </div>
                <div class="col-md-6">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" class="form-control" required>
                    <span class="toggle-password" onclick="togglePassword('confirm-password', this)"></span>
                </div>
            </div>
            <div class="row form-row">
                <div class="col-md-12">
                    <label for="role">Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="Student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                        <option value="Outside Client" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Outside Client') ? 'selected' : ''; ?>>Outside Client</option>
                        <option value="Faculty" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Faculty') ? 'selected' : ''; ?>>Faculty</option>
                        <option value="Director" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Director') ? 'selected' : ''; ?>>Director</option>
                        <option value="Admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="Staff" <?php echo (isset($_POST['role']) && $_POST['role'] == 'Staff') ? 'selected' : ''; ?>>Staff</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Create Account</button>
        </form>
      </div>
</body>

<script src="/gcc/js/none-course.js"></script>
</html>