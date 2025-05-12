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
        $first_name = "404 User"; 
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
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/admin.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

    <style>
    .typing-text {
         display: inline-block;
         white-space: nowrap;
         overflow: hidden;
         border-right: 0.1875rem solid rgb(29, 215, 129); /* 3px */
         padding-right: 0.1875rem; /* 3px */
         animation: typing 2s steps(<?php echo $text_length; ?>) forwards, 
                    blink-caret 0.75s step-end infinite;
     }
     .vertical-border {
         position: absolute;
         top: 0;
         right: 0;
         width: 0.125rem; /* 2px */
         background-color: black;
         animation: move-border 2s steps(<?php echo $text_length; ?>) forwards;
     }
    </style>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="dashboard.php"><i class="fa-solid fa-home"></i> Dashboard</a>
            <a href="admin.php" style=" background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-solid fa-users"></i> Manage Users</a>
            <a href="content.php"><i class="fa-solid fa-chart-bar"></i> Content </a>
            <!-- <a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a> -->
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="sidebar-footer">
        <small>© 2025 WMSU </small>
        <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
    </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="typing-container">
            <span class="typing-text">Welcome to WMSU GCC Admin, <span style="color:rgb(11, 178, 100);"><?php echo $first_name; ?></span>.</span>
            <span class="vertical-border"></span>
        </div>

        <div class="table-container">
            <div style="display: flex; justify-content: flex-start; align-items: center; margin-bottom: 2.1875rem;"> <!-- 35px -->
                <h2 style="font-weight: 600; margin: 0; margin-right: 0.9375rem;"> <!-- 15px -->
                    <i class="fa-solid fa-users"></i>
                    GCC's User Accounts
                </h2>
                <button class="add-btn">
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
                            <td style="align-items: center; justify-content: center; display: flex; gap: 0.375rem;"> <!-- 6px -->
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
    </div>
    <script src="/gcc/js/dataTable.js"></script>
    <script src="/gcc/js/action-modal.js"></script>
</body>
</html>
