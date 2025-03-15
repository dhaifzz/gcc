<?php
require_once '../../config.php';

$error = '';
$message = '';

$conn = new mysqli("localhost", "root", "", "guidance_counseling_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, contact_number, civil_status, grade_level_course, wmsu_id, role, address, sex, age, occupation, email,ccreated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                if (!$stmt) {
                    $error = "Error preparing statement: " . $conn->error;
                    break;
                }
                $stmt->bind_param("ssssssssssssss", $_POST['first_name'], $_POST['middle_name'], $_POST['last_name'], $_POST['contact_number'], $_POST['civil_status'], $_POST['grade_level_course'], $_POST['wmsu_id'], $_POST['role'], $_POST['address'], $_POST['sex'], $_POST['age'], $_POST['occupation'], $_POST['email'], );
                if ($stmt->execute()) {
                    $message = "New record created successfully";
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
        
            case 'delete':
                $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $message = "Record deleted successfully";
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
                break;
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin</title>
    <link rel="stylesheet" href="admin1.css">
</head>
<body>
    <div class="navbar">
        <img src="/gcc-master/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
        <a class="website" href="#">Guidance and Counseling Center</a>
        <div class="burger-icon" style="float: right; margin: 10px;">
            <i class="fas fa-bars" style="font-size: 35px;"></i>
        </div>
    </div>

    <div class="container">
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

        <h1>Accounts <a href="create_account.php"> Create Account</a></h1> 
       
        
        <div class="table-container">
            <table class="table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Last Name</th>
                    <th>Contact Number</th>
                    <th>Civil Status</th>
                    <th>WMSU ID</th>
                    <th>Role</th>
                    <th>Address</th>
                    <th>Grade Level/Course</th>
                    <th>Age</th>
                    <th>Sex</th>
                    <th>Occupation</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

        </div>
                <?php
                $conn = new mysqli("localhost", "root", "", "guidance_counseling_db");
                $sql = "SELECT * FROM users";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row["user_id"]. "</td>
                                <td>" . $row["first_name"]. "</td>
                                <td>" . $row["middle_name"]. "</td>
                                <td>" . $row["last_name"]. "</td>
                                <td>" . $row["contact_number"]. "</td>
                                <td>" . $row["civil_status"]. "</td>
                                <td>" . $row["wmsu_id"]. "</td>
                                <td>" . $row["role"]. "</td>
                                <td>" . $row["address"]. "</td>
                                <td>" . $row["grade_level_course"]. "</td>
                                <td>" . $row["age"]. "</td>
                                <td>" . $row["sex"]. "</td>
                                <td>" . $row["occupation"]. "</td>
                                <td>" . $row["email"]. "</td>
                                <td>" . $row["created_at"]. "</td>
                                <td>
                                    <form method='get' action='edit_account.php'>
                                        <input type='hidden' name='user_id' value='" . $row["user_id"] . "'>
                                        <button type='submit' class='btn btn-primary btn-sm'>Edit</button>
                                    </form>
                                    <form method='post' action=''>
                                        <input type='hidden' name='user_id' value='" . $row["user_id"] . "'>
                                        <input type='hidden' name='action' value='delete'>
                                        <button type='submit' class='btn btn-danger btn-sm'>Delete</button>
                                    </form>
                                </td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='17'>No accounts found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
<div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
<div style="margin-right: 20px;"><img src="/gcc-master/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
</footer>
</body>
</html>