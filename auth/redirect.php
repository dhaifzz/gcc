<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: ./sign-in.php");
    exit();
}

switch ($_SESSION['role']) {
    case 'student':
        header("Location: ../users/student/student.php");
        break;
    case 'director':
        header("Location: ../users/director/director.php");
        break;
    case 'superadmin':
        header("Location: ../users/staff/staff.php");
        break;
    case 'staff':
        header("Location: ../users/superadmin/superadmin.php");
        break;
    default:
        header("Location: ./sign-in.php");
        break;
}
exit();
?>