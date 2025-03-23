<?php
require_once '../../../../../config.php';

$user_role = $_SESSION['role'];
if ($user_role !== 'client' && $user_role !== 'college_student' && $user_role !== 'high_school_student' && $user_role !== 'faculty' && $user_role !== 'employee' && $user_role !== 'outside_client') {
    header("Location: ../counseling.php");
    exit();
}

$error = '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'book') {
    $client_id = $_SESSION['user_id'];
    $appointment_type = 'counseling';
    $requested_date = $_POST['requested_date'];
    $requested_time = $_POST['requested_time'];
    $status = 'pending'; 

    $stmt = $pdo->prepare("INSERT INTO appointments (client_id, appointment_type, requested_date, requested_time, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bindParam(1, $client_id);
    $stmt->bindParam(2, $appointment_type);
    $stmt->bindParam(3, $requested_date);
    $stmt->bindParam(4, $requested_time);
    $stmt->bindParam(5, $status);

    if ($stmt->execute()) {
        $message = "Counseling appointment booked successfully";
    } else {
        $error = "Error booking appointment: " . $stmt->errorInfo()[2];
    }
    $stmt = null;
}

$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Counseling Appointment</title>
    <link rel="stylesheet" href="admin1.css">
</head>
<body>
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

    <h2>Book Counseling Appointment</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="book">
        <div class="form-group">
            <label for="requested_date">Requested Date:</label>
            <input type="date" class="form-control" id="requested_date" name="requested_date" required>
        </div>
        <div class="form-group">
            <label for="requested_time">Requested Time:</label>
            <input type="time" class="form-control" id="requested_time" name="requested_time" required>
        </div>
        <button type="submit" class="btn btn-success">Book Appointment</button>
    </form>
</div>
</body>
</html>