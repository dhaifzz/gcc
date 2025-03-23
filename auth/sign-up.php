<?php
require_once '../font/font.php';
require_once('../database/database.php');

$error_messages = []; 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $school = $_POST['school'];
    $course_grade = $_POST['course_grade'];
    $sex = $_POST['sex'];
    $age = $_POST['age'];
    $contact_number = $_POST['contact_number'];
    $address = $_POST['address'];
    $civil_status = $_POST['civil_status'];
    $occupation = $_POST['occupation'];
    $wmsu_id = $_POST['wmsu_id'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

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
        if (!empty($wmsu_id) && is_numeric($wmsu_id)) {
            if (strlen($wmsu_id) == 6) {
                $role = 'Faculty';
            } elseif (strlen($wmsu_id) == 9) {
                $role = 'Student';
            } else {
                $error_messages['wmsu_id'] = "WMSU ID must be 6 or 9 digits for WMSU email addresses.";
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

        // Check if contact number is already in the database
        $contact_number_check_query = "SELECT * FROM users WHERE contact_number = :contact_number";
        $stmt = $pdo->prepare($contact_number_check_query);
        $stmt->execute([':contact_number' => $contact_number]);

        if ($stmt->rowCount() > 0) {
            $error_messages['contact_number'] = "This contact number is already registered. Please use another contact number.";
        }
    
        if (empty($error_messages)) {
            $sql = "INSERT INTO users (first_name, middle_name, last_name, school, course_grade, sex, age, contact_number, address, civil_status, occupation, wmsu_id, email, password, role, status) 
                    VALUES (:first_name, :middle_name, :last_name, :school, :course_grade, :sex, :age, :contact_number, :address, :civil_status, :occupation, :wmsu_id, :email, :password, :role, :status)";
    
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
                    ':status' => 'ACTIVE'
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
            <select id="school" name="school" required>
                <option value="wmsu" <?php echo (isset($_POST['school']) && $_POST['school'] == 'wmsu') ? 'selected' : ''; ?>>Western Mindanao State University</option>
                <option value="uz" <?php echo (isset($_POST['school']) && $_POST['school'] == 'uz') ? 'selected' : ''; ?>>Universidad de Zamboanga</option>
                <option value="adzu" <?php echo (isset($_POST['school']) && $_POST['school'] == 'adzu') ? 'selected' : ''; ?>>Ateneo de Zamboanga University</option>
            </select>
        </div>
    </div>
    <div class="flex-container">
        <div class="course-grade">
            <label for="course-grade">Course / Grade Level</label>
            <select id="course-grade" name="course_grade" required>
                <option value="js" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'js') ? 'selected' : ''; ?>>Junior High</option>
                <option value="sh" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'sh') ? 'selected' : ''; ?>>Senior High</option>
                <option value="cs" <?php echo (isset($_POST['course_grade']) && $_POST['course_grade'] == 'cs') ? 'selected' : ''; ?>>Computer Science</option>
               </select>
        </div>
        <div class="sex">
            <label for="sex">Sex</label>
            <select id="sex" name="sex" required>
                <option value="male" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'male') ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'female') ? 'selected' : ''; ?>>Female</option>
                <option value="prefer_not_to_say" <?php echo (isset($_POST['sex']) && $_POST['sex'] == 'prefer_not_to_say') ? 'selected' : ''; ?>>Prefer not to say</option>
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
                <option value="single" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'single') ? 'selected' : ''; ?>>Single</option>
                <option value="married" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'married') ? 'selected' : ''; ?>>Married</option>
                <option value="widowed" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'widowed') ? 'selected' : ''; ?>>Widowed</option>
                <option value="divorced" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'divorced') ? 'selected' : ''; ?>>Divorced</option>
                <option value="separated" <?php echo (isset($_POST['civil_status']) && $_POST['civil_status'] == 'separated') ? 'selected' : ''; ?>>Separated</option>
            </select>
        </div>
        <div class="occupation">
            <label for="occupation">Occupation</label>
            <select id="occupation" name="occupation" required>
                <option value="student" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'student') ? 'selected' : ''; ?>>Student</option>
                <option value="employee" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'employee') ? 'selected' : ''; ?>>Employee</option>
                <option value="self_employed" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'self_employed') ? 'selected' : ''; ?>>Self-employed</option>
                <option value="unemployed" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'unemployed') ? 'selected' : ''; ?>>Unemployed</option>
                <option value="other" <?php echo (isset($_POST['occupation']) && $_POST['occupation'] == 'other') ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>
    </div>
    <div class="flex-container">
    <div class="wmsu-id">
         <label for="wmsu-id">WMSU ID Number</label>
         <input type="text" id="wmsu-id" name="wmsu_id" placeholder="For WMSU applicants only" style="font-style: italic; 
             <?php echo isset($error_messages['wmsu_id']) ? 'border: 1px solid red;' : ''; ?>"
             value="<?php echo isset($_POST['wmsu_id']) ? htmlspecialchars($_POST['wmsu_id']) : ''; ?>" 
             maxlength="9" 
             title="WMSU ID must be 6 or 9 digits">
             
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