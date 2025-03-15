    <?php
    require_once '../../config.php'; 
    $conn = new mysqli("localhost", "root", "", "guidance_counseling_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $error = '';
    $message = '';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['action']) && $_POST['action'] == 'create') {
            $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, contact_number, civil_status, grade_level_course, wmsu_id, role, address, sex, age, occupation, email, password, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

            if (!$stmt) {
                $error = "Error preparing statement: " . $conn->error;
            } else {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $age = (int) $_POST['age']; 

                $stmt->bind_param("sssssssssssis", 
                    $_POST['first_name'], 
                    $_POST['middle_name'], 
                    $_POST['last_name'], 
                    $_POST['contact_number'], 
                    $_POST['civil_status'], 
                    $_POST['grade_level_course'], 
                    $_POST['wmsu_id'], 
                    $_POST['role'], 
                    $_POST['address'], 
                    $_POST['sex'], 
                    $age, 
                    $_POST['occupation'], 
                    $_POST['email'], 
                    $hashed_password);

                if ($stmt->execute()) {
                    $message = "New record created successfully";
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    $conn->close();
    ?>

    <!DOCTYPE html>
    <html>
    <head>
        <title>Create Account</title>
        <link rel="stylesheet" href="create_account.css">
    </head>
    <body>
    <div class="navbar">
        <img src="/gcc-master/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
        <a class="website" href="#">Guidance and Counseling Center</a>
        <div class="burger-icon" style="float: right; margin: 10px;">
            <i class="fas fa-bars" style="font-size: 35px;"></i>
        </div>
        <div class="container mt-5">
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <?php if ($message): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <h1>Create Account <a href="admin.php">Back to Accounts</a></h1>
        <form method="POST" action="">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" class="form-control" id="first_name" name="first_name" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name:</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" class="form-control" id="last_name" name="last_name" required>
            </div>
            <div class="form-group">
                <label for="contact_number">Contact Number:</label>
                <input type="text" class="form-control" id="contact_number" name="contact_number" required>
            </div>
            <div class="form-group">
                <label for="civil_status">Civil Status:</label>
                <input type="text" class="form-control" id="civil_status" name="civil_status" required>
            </div>
            <div class="form-group">
                <label for="grade_level_course">Grade Level/Course:</label>
                <input type="text" class="form-control" id="grade_level_course" name="grade_level_course" required>
            </div>
            <div class="form-group">
                <label for="wmsu_id">WMSU ID:</label>
                <input type="text" class="form-control" id="wmsu_id" name="wmsu_id">
            </div>
            <div class="form-group">
                <label for="role">Role:</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="admin">Admin</option>
                    <option value="staff">Staff</option>
                    <option value="director">Director</option>
                    <option value="faculty">Faculty</option>
                    <option value="college_student">College Student</option>
                    <option value="high_school_student">High School Student</option>
                    <option value="outside_client">Outside Client</option>
                    <option value="employee">Employee</option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea class="form-control" id="address" name="address" required></textarea>
            </div>
            <div class="form-group">
                <label for="sex">Sex:</label>
                <select class="form-control" id="sex" name="sex" required>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" class="form-control" id="age" name="age" required>
            </div>
            <div class="form-group">
                <label for="occupation">Occupation:</label>
                <input type="text" class="form-control" id="occupation" name="occupation">
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-success">Create Account</button>
        </form>
            </div>
        </div>
    </div>
        </div>
    <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
        <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
        <div style="margin-right: 20px;"><img src="/gcc-master/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
    </footer>
    </body>
    </html>