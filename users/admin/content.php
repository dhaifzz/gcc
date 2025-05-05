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

// Handle Upload
// if (isset($_POST['upload'])) {
//     if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {

//         $target_dir = "uploads/";
//         $filename = basename($_FILES["image"]["name"]);
//         $target_file = $target_dir . time() . "_" . $filename; // unique filename

//         if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
//             // Insert into database
//             $stmt = $pdo->prepare("INSERT INTO content (type, image_path) VALUES (?, ?)");
//             $stmt->execute([$_POST['type'], $target_file]);

//             echo "<p style='color:green;'>Upload successful!</p>";
//         } else {
//             echo "<p style='color:red;'>Error uploading file!</p>";
//         }

//     } else {
//         echo "<p style='color:red;'>No file selected or upload error!</p>";
//     }
// }
?>
<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/content.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
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
            <a href="content.php" style=" background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-solid fa-chart-bar"></i> Content </a>
            <!-- <a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a> -->
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="sidebar-footer">
        <small>© 2025 WMSU </small>
        <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
    </div>
    </div>

    <div class="container">
    <h1 style="color: #236641"> CMS Page</h1>
    <h2> Coming Soon...</h2>
    </div>

</body>
</html>
