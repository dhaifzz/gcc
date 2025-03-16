<?php
require '../config.php';
$error = '';

try {
    $pdo = new PDO("mysql:host=localhost;dbname=guidance_counseling_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'] ?? null;
    $last_name = $_POST['last_name'];
    $contact_number = $_POST['contact_number'];
    $civil_status = $_POST['civil_status'];
    $course_grade = $_POST['course_grade'];
    $school = $_POST['school'];
    $sex = $_POST['sex'];
    $age = $_POST['age'];
    $address = $_POST['address'];
    $occupation = $_POST['occupation'] ?? null;
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $wmsu_id = $_POST['wmsu_id'] ?? null;

    if ($wmsu_id) {
        if (strlen($wmsu_id) == 6) {
            $role = 'employee';
        } elseif (strlen($wmsu_id) == 9) {
            if (in_array($course_grade, ['junior high school', 'senior high school'])) {
                $role = 'high_school_student';
            } else {
                $role = 'college_student';
            }
        } else {
            $role = 'outside_client';
        }
    } else {
        $role = 'outside_client';
    }
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($existing_user) {
        $error = 'Email already exists';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE wmsu_id = :wmsu_id");
        $stmt->execute(['wmsu_id' => $wmsu_id]);
        $existing_wmsu_id = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing_wmsu_id) {
            $error = 'WMSU ID already exists';
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (first_name, middle_name, last_name, contact_number, civil_status, grade_level_course, wmsu_id, role, address, sex, age, occupation, email, password) VALUES (:first_name, :middle_name, :last_name, :contact_number, :civil_status, :course_grade, :wmsu_id, :role, :address, :sex, :age, :occupation, :email, :password)");
                $stmt->execute([
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'last_name' => $last_name,
                    'contact_number' => $contact_number,
                    'civil_status' => $civil_status,
                    'course_grade' => $course_grade,
                    'wmsu_id' => $wmsu_id,
                    'role' => $role,
                    'address' => $address,
                    'sex' => $sex,
                    'age' => $age,
                    'occupation' => $occupation,
                    'email' => $email,
                    'password' => $password
                ]);
    
                header("Location: sign-in.php");
                exit();
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="sign-up.css">
</head>
<body>
    <form method="POST" action="">
        <div class="header-container">
            <img src="../img/gcc-logo.png" alt="GCC Logo" id="gcc-logo">
            <p class="text">Sign up</p>
        </div>
        <div class="flex-container">
            <div class="form-group">
                <label for="first-name">First Name</label>
                <input type="text" id="first-name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="middle-name">Middle Name (optional)</label>
                <input type="text" id="middle-name" name="middle_name">
            </div>
        </div>
        <div class="flex-container">
            <div class="form-group">
                <label for="last-name">Last Name</label>
                <input type="text" id="last-name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="school">School</label>
                <select id="school" name="school" required>
                    <option value="wmsu">Western Mindanao State University</option>
                    <option value="uz">Universidad de Zamboanga</option>
                    <option value="adzu">Ateneo de Zamboanga University</option>
                </select>
            </div>
        </div>
        <div class="flex-container">
            <div class="form-group">
                <label for="course-grade">Course / Grade Level</label>
                <select id="course-grade" name="course_grade" required>
                    <option value="junior high school">Grade 7</option>
                    <option value="junior high school">Grade 8</option>
                    <option value="junior high school">Grade 9</option>
                    <option value="junior high school">Grade 10</option>
                    <option value="senior high school">Grade 11</option>
                    <option value="senior high school">Grade 12</option>
                    <option value="Computer Science">Computer Science</option>
                    <option value="Information Technology">Information Technology</option>
                </select>
            </div>
            <div class="form-group">
                <label for="wmsu-id">WMSU ID NUMBER (optional)</label>
                <input type="text" id="wmsu-id" name="wmsu_id">
            </div>
        </div>
        <div class="flex-container">
            <div class="form-group">
                <label for="sex">Sex</label>
                <select id="sex" name="sex" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="prefer_not_to_say">Prefer not to say</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" required>
            </div>
        </div>
        <div class="flex-container">
            <div class="form-group">
                <label for="contact-number">Contact Number</label>
                <input type="text" id="contact-number" name="contact_number" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" required>
            </div>
        </div>
        <div class="flex-container">
            <div class="form-group">
                <label for="civil-status">Civil Status</label>
                <select id="civil-status" name="civil_status" required>
                    <option value="single">Single</option>
                    <option value="married">Married</option>
                    <option value="widowed">Widowed</option>
                    <option value="divorced">Divorced</option>
                    <option value="separated">Separated</option>
                </select>
            </div>
            <div class="form-group">
                <label for="occupation">Occupation</label>
                <input type="text" id="occupation" name="occupation">
            </div>
        </div>
        <div class="flex-container">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group password">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="toggle-password" onclick="togglePassword('password', 'toggle-password')"><i class="fas fa-eye-slash" style="color: #16633F;"></i></span>
            </div>
        </div>
        <div class="form-group">
            <button type="submit">Sign Up</button>
        </div>
        <div class="signin-text">
            <p>Already have an account? <a href="../auth/sign-in.php" class="signup">Sign In</a></p>
        </div>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
    <script src="js/eye-icon.js"></script>
</body>
</html>