<?php
require_once '../../font/font.php';
require_once('../../database/database.php');

session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

$user_email = $_SESSION['email'];

try {
    $stmt = $pdo->prepare("SELECT first_name FROM users WHERE email = :email");
    $stmt->bindParam(':email', $user_email, PDO::PARAM_STR);
    $stmt->execute();
    $first_name = $stmt->fetchColumn(); // Get the first_name value directly

    if (!$first_name) {
        $first_name = "User"; 
    }

    $text = "Welcome to GCC Admin, $first_name!";
    $text_length = strlen($text);
    $name_length = strlen($first_name) + 17; 

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$validation_errors = [];

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
    $role = $_POST['role'];

    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $validation_errors[] = 'Please fill in all required fields.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validation_errors[] = 'Invalid email format.';
    }

    if ($password !== $confirm_password) {
        $validation_errors[] = 'Passwords do not match.';
    }

    if (empty($validation_errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (first_name, middle_name, last_name, school, course_grade, sex, age, contact_number, address, civil_status, occupation, wmsu_id, email, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$first_name, $middle_name, $last_name, $school, $course_grade, $sex, $age, $contact_number, $address, $civil_status, $occupation, $wmsu_id, $email, $hashed_password, $role])) {
            echo '<div style="color: green;">Account created successfully!</div>';
        } else {
            echo '<div style="color: red;">Error: ' . $stmt->errorInfo()[2] . '</div>';
        }
    }
}

$stmt = $pdo->query("SELECT id, wmsu_id, first_name, middle_name, last_name, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/bootstrap-modal.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- ADMIN -->
</head>
<body>

    <style>
    .typing-text {
         display: inline-block;
         white-space: nowrap;
         overflow: hidden;
         border-right: 3px solid rgb(29, 215, 129);
         padding-right: 3px;
         animation: typing 2s steps(<?php echo $text_length; ?>) forwards, 
                    blink-caret 0.75s step-end infinite;
     }
     .vertical-border {
         position: absolute;
         top: 0;
         right: 0;
         width: 2px;
         background-color: black;
         animation: move-border 2s steps(<?php echo $text_length; ?>) forwards;
     }
    </style>

    <!-- Navbar -->
    <div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="#">Guidance and Counseling Center</a>
       <!-- Sidebar -->
       <div class="burger-icon" onclick="toggleSidebar()">
         <i class="fas fa-bars"></i>
       </div>
       <div class="sidebar" id="sidebar">
        <span class="close-modal-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars-staggered"></i>
        </span>
        <div class="menu-items">
            <a href="admin.php"><i class="fas fa-home"></i>Home</a>
            <a href="#"><i class="fas fa-user"></i>Profile</a>
            <a href="counseling.php"><i class="fas fa-calendar-check"></i>Appointments</a>
            <hr>
            <a href="../../shared/sub-pages/contact-us.php"><i class="fas fa-envelope"></i>Contact Us</a>
            <a href="../../shared/sub-pages/about-us.php"><i class="fas fa-info-circle"></i>About Us</a>
            <a href="../../shared/sub-pages/our-team.php"><i class="fas fa-users"></i>Our Team</a>
            <hr>
            <a href="../../auth/sign-out.php" class="logout"><i class="fas fa-sign-out-alt"></i>Log Out</a>

            </div>
       </div>
    </div>   

    <div class="container">
    <div class="typing-container">
     <span class="typing-text">Welcome to GCC Admin, <span style="color:rgb(11, 178, 100);"><?php echo $first_name; ?></span>.</span>
     <span class="vertical-border"></span>
     </div>

    <div class="table-container">
    <div style="display: flex; justify-content: flex-start; align-items: center; margin-bottom: 35px;">
    <h2 style="font-weight: 600; margin: 0; margin-right: 15px;"> 
        <i class="fa-solid fa-users"></i>
        GCC's User Accounts
    </h2>
    <button class="btn btn-primary" data-toggle="modal" data-target="#confirmationModal">
        <i class="fa-solid fa-user-plus"></i> Add Account
    </button>
</div>

    <table id="usersTable" class="display">
    <thead>
        <tr>
            <th>ID</th>
            <th>WMSU ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Account Role</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $row) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['wmsu_id'] ?></td>
                <td><?= $row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['role'] ?></td>
                <td style="align-items: center; justify-content: center; display: flex; gap: 6px;">
                   <button class="edit-btn" data-id="<?= $row['id'] ?>">
                       <i class="fa-solid fa-pen-to-square"></i> Edit
                   </button>
                   <button class="delete-btn" data-id="<?= $row['id'] ?>">
                       <i class="fa-solid fa-trash"></i> Delete
                   </button>
               </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
     <?php include 'components/modals.php'; ?>  
    </div>
    <div>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
         </div>
    </div>

    <script src="/gcc/js/sidebar.js"></script>
    <script src="/gcc/js/dataTable.js"></script>
    <script src="/gcc/js/action-modal.js"></script>

    <script>
    document.getElementById('confirmAddAccountBtn').addEventListener('click', function() {
    window.location.href = 'add-account.php';
});

document.querySelectorAll('.close-btn, .cancel-btn').forEach(function(element) {
    element.addEventListener('click', function() {
        document.getElementById('confirmationModal').classList.remove('show');
    });
});
    </script>
    
    <script>
       function hideModal() {
    modal.setAttribute('inert', ''); // Prevent focus and interaction
    modal.style.display = 'none'; // Hide the modal (or use Bootstrap's hide method if you're using it)
}

function showModal() {
    modal.removeAttribute('inert'); // Allow focus and interaction
    modal.style.display = 'flex'; // Show the modal (or use Bootstrap's show method if you're using it)
}
    </script>
</body>
</html>