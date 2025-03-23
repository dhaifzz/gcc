<?php
require_once '../../database/database.php';
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ./sign-in.php");
    exit();
}

$email = $_SESSION['email'];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['wmsu_id'] = $user['wmsu_id'];
        $_SESSION['role'] = $user['role'];
    } else {
        header("Location: ./sign-in.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Redirect based on user role
switch ($_SESSION['role']) {
    case 'Student':
        header("Location: ../../client/inside/student/student.php");
        break;
    case 'Outside Client':
        header("Location: ../../client/outside/outside.php");
        break;
    case "Faculty":
        header("Location: ../../client/inside/faculty/faculty.php"); 
        break;
    case 'Director':
        header("Location: ../../users/director/director.php");
        break;
    case 'Admin':
        header("Location: ../../users/admin/admin.php"); 
        break;
    case 'Staff':
        header("Location: ../../users/staff/staff.php"); 
        break;
    default:
        header("Location: ./sign-in.php");
        break;
}
exit();
