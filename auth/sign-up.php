<?php
require_once '../font/font.php';
require_once('../database/database.php');

$error_messages = []; 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $school = trim($_POST['school']);
    $course_grade = trim($_POST['course_grade']);
    $sex = $_POST['sex'];
    $age = trim($_POST['age']);
    $contact_number = trim($_POST['contact_number']);
    $address = trim($_POST['address']);
    $civil_status = trim($_POST['civil_status']);
    $occupation = trim($_POST['occupation']);
    $wmsu_id = trim($_POST['wmsu_id']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $valid_schools = [
        "Western Mindanao State University",
        "Universidad de Zamboanga",
        "Ateneo de Zamboanga University",
        "Southern City Colleges",
        "Zamboanga City State Polytechnic College",
        "Zamboanga State College of Marine Sciences and Technology"
    ];

    if (!in_array($school, $valid_schools)) {
        $error_messages['school'] = "Please select a valid school from the list.";
    }

    if ($password !== $confirm_password) {
        $error_messages['password'] = "Passwords do not match.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($age < 12) {
        $error_messages['age'] = "Must be at least 12+ to sign up.";
    }

    $role = "";

    if (strpos($email, '@wmsu.edu.ph') !== false) {
        if ($school !== 'Western Mindanao State University') {
            $error_messages['email'] = "WMSU email can only be used if the school is Western Mindanao State University.";
        } elseif (!empty($wmsu_id) && is_numeric($wmsu_id)) {
            if (strlen($wmsu_id) == 6) {
                $role = 'Faculty';
            } elseif (strlen($wmsu_id) == 9) {
                $role = 'Student';
            } else {
                $error_messages['wmsu_id'] = "WMSU ID is for be 6 or 9 digits for WMSU email addresses.";
            }
        } else {
            $error_messages['wmsu_id'] = "WMSU ID is required for WMSU email addresses.";
        }
    } else {
        if (!empty($wmsu_id)) {
            $error_messages['wmsu_id'] = "WMSU ID should not be filled for non-WMSU email addresses.";
        }
        $role = 'Outside Client';
        $wmsu_id = "<i>Guest ID</i>";
    }

    if ($school !== 'Western Mindanao State University' && strpos($email, '@wmsu.edu.ph') === false) {
        $role = 'Outside Client';
    }
    
    if ($course_grade === 'None' && $role === 'Outside Client') {
        if (strpos($email, '@wmsu.edu.ph') !== false) {
            $error_messages['email'] = "Outside clients cannot use a WMSU email address.";
        }
        $wmsu_id = "<i>Guest ID</i>";
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

                header("Location: sign-in.php");
                exit();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
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
    <title>Sign Up</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/sign-up.css">
</head>
<body>
<form method="POST" action="sign-up.php">
    <div class="header-container">
        <img src="../img/gcc-logo.png" alt="GCC Logo" id="gcc-logo">
        <p class="text">Sign up with your details to access the GCC portal</p>
    </div>
    <div class="flex-container">
        <div class="first-name">
            <label for="first-name">First Name</label>
            <input type="text" id="first-name" name="first_name" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : ''; ?>" required>
        </div>
        <div class="middle-name">
            <label for="middle-name">Middle Name (optional)</label>
            <input type="text" id="middle-name" name="middle_name" value="<?php echo isset($_POST['middle_name']) ? $_POST['middle_name'] : ''; ?>">
        </div>
    </div>
    <div class="flex-container">
        <div class="last-name">
            <label for="last-name">Last Name</label>
            <input type="text" id="last-name" name="last_name" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : ''; ?>" required>
        </div>
        <div class="school">
            <label for="school">School</label>
            <input 
                type="text" 
                id="school" 
                name="school" 
                placeholder="Search for your school..." 
                list="schoolList" 
                required
                autocomplete="off"
                value="<?php echo isset($_POST['school']) ? htmlspecialchars($_POST['school']) : ''; ?>">
            
            <datalist id="schoolList">
                <option value="Western Mindanao State University">
                <option value="Universidad de Zamboanga">
                <option value="Ateneo de Zamboanga University">
                <option value="Southern City Colleges">
                <option value="Jak Roberto Anti-silos University">
                <option value="Zamboanga City State Polytechnic College">
                <option value="Zamboanga State College of Marine Sciences and Technology">
            </datalist>
            <?php if (isset($error_messages['school'])): ?>
                <small style="color:red; font-weight: 600;"><?php echo $error_messages['school']; ?></small>
            <?php endif; ?>
        </div>
    </div>
    <div class="flex-container">
    <div class="course-grade">
        <label for="course-grade">Course / Grade Level</label>
         <select id="course-grade" name="course_grade" required>
            <option value="None" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'None') ? 'selected' : ''; ?>>None</option>
            <option value="Junior High" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'Junior High') ? 'selected' : ''; ?>>Junior High</option>
            <option value="Senior High" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'Senior High') ? 'selected' : ''; ?>>Senior High</option>
            <option value="BSCS" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'BSCS') ? 'selected' : ''; ?>>Computer Science</option>
            <option value="BSIT" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'BSIT') ? 'selected' : ''; ?>>Information Technology</option>
            <option value="ACT" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'ACT') ? 'selected' : ''; ?>>Associate in Computer Technology</option>
            <option value="BSN" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'BSN') ? 'selected' : ''; ?>>Nursing</option>
         </select>
    </div>
        <div class="sex">
            <label for="sex">Sex</label>
            <select id="sex" name="sex" required>
                <option value="Male" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                <option value="Prefer not to say" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'Prefer not to say') ? 'selected' : ''; ?>>Prefer not to say</option>
            </select>
        </div>
        <div class="age">
            <label for="age">Age</label>
            <input type="number" id="age" name="age" value="<?php echo isset($_POST['age']) ? $_POST['age'] : ''; ?>" required
            <?php echo isset($error_messages['age']) ? 'style="border: 1px solid red;"' : ''; ?>>
              <?php if (isset($error_messages['age'])): ?>
                  <small style="color:red; font-weight: 600;"><?php echo $error_messages['age']; ?></small>
              <?php endif; ?>
        </div>
    </div>
    <div class="flex-container">
        <div class="contact-number">
            <label for="contact-number">Contact Number</label>
            <input type="text" id="contact-number" name="contact_number" value="<?php echo isset($_POST['contact_number']) ? $_POST['contact_number'] : ''; ?>" required
            <?php echo isset($error_messages['contact_number']) ? 'style="border: 1px solid red;"' : ''; ?>>
              <?php if (isset($error_messages['contact_number'])): ?>
                  <small style="color:red; font-weight: 600;"><?php echo $error_messages['contact_number']; ?></small>
              <?php endif; ?>
        </div>
        <div class="address">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" value="<?php echo isset($_POST['address']) ? $_POST['address'] : ''; ?>" required>
        </div>
    </div>
    <div class="flex-container">
        <div class="civil-status">
            <label for="civil-status">Civil Status</label>
            <select id="civil-status" name="civil_status" required>
                <option value="Single" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                <option value="Married" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'Married') ? 'selected' : ''; ?>>Married</option>
                <option value="Widowed" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'Widowed') ? 'selected' : ''; ?>>Widowed</option>
                <option value="Divorced" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                <option value="Separated" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'Separated') ? 'selected' : ''; ?>>Separated</option>
            </select>
        </div>
        <div class="occupation">
            <label for="occupation">Occupation</label>
            <select id="occupation" name="occupation" required>
                <option value="Student" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Student') ? 'selected' : ''; ?>>Student</option>
                <option value="Employee" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Employee') ? 'selected' : ''; ?>>Employee</option>
                <option value="Self-employed" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Self-employed') ? 'selected' : ''; ?>>Self-employed</option>
                <option value="Unemployed" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Unemployed') ? 'selected' : ''; ?>>Unemployed</option>
                <option value="Other" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
    </div>
    <div class="flex-container">
    <div class="wmsu-id">
         <label for="wmsu-id">WMSU ID Number</label>
         <input type="text" id="wmsu-id" name="wmsu_id" placeholder="For WMSU applicants only" style="font-style: italic; 
             <?php echo isset($error_messages['wmsu_id']) ? 'border: 1px solid red;' : ''; ?>"
             value="<?php echo isset($_POST['wmsu_id']) ? htmlspecialchars($_POST['wmsu_id']) : ''; ?>" 
             maxlength="15" 
             title="Invalid WMSU ID format.">
             
         <?php if (isset($error_messages['wmsu_id'])): ?>
             <small style="color:red; font-weight: 600;"><?php echo $error_messages['wmsu_id']; ?></small>
         <?php endif; ?>
     </div>

        <div class="email">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" required
            <?php echo isset($error_messages['email']) ? 'style="border: 1px solid red;"' : ''; ?>>
              <?php if (isset($error_messages['email'])): ?>
                  <small style="color:red; font-weight: 600;"><?php echo $error_messages['email']; ?></small>
              <?php endif; ?>
            </div>
    </div>
    <div class="flex-container">
    <div class="password">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required 
        <?php echo isset($error_messages['password']) ? 'style="border: 1px solid red;"' : ''; ?>>
    <span class="toggle-password" onclick="togglePassword('password', this)">
        <i class="fas fa-eye-slash" style="color: #16633F;"></i>
    </span>
</div>

<div class="confirm-password">
    <label for="confirm-password">Confirm Password</label>
    <input type="password" id="confirm-password" name="confirm_password" required>
    <span class="toggle-password" onclick="togglePassword('confirm-password', this)">
        <i class="fas fa-eye-slash" style="color: #16633F;"></i>
    </span>
</div>

</div>
<?php if (isset($error_messages['password'])): ?>
    <small style="color:red; font-weight: 600;"><?php echo $error_messages['password']; ?></small>
<?php endif; ?>

    <button type="submit" style="font-size: 15px;">Submit</button>
    <div class="signin-text">
            <p>Already have an account? <a href="../auth/sign-in.php" class="signup">Sign In</a></p>
        </div>
</form>

</body>
</html>

<script src="/gcc/js/eye-icon.js"></script>
<script src="/gcc/js/none-course.js"></script>
