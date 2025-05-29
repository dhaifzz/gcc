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
    case 'College Student':
        header("Location: ../../client/inside/student/college.php");
        break;
    case 'High School Student':
        header("Location: ../../client/inside/student/high-school.php");
        break; 
    case 'Outside Client':
        header("Location: ../../client/outside/outside.php");
        break;
    case "Faculty":
        header("Location: ../../client/inside/faculty/faculty.php"); 
        break;
    case 'Director':
        header("Location: ../../users/director/director-dashboard.php");
        break;
    case 'Admin':
        header("Location: ../../users/admin/dashboard.php"); 
        break;
    case 'Staff':
        header("Location: ../../users/staff/staff-dashboard.php"); 
        break;
    default:
        header("Location: ./sign-in.php");
        break;
}
exit();
