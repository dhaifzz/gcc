<?php
require_once '../../font/font.php';
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'student') {
    header("Location: ../../auth/sign-in.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/shifting.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="../student/student.php">Guidance and Counseling Center</a>
       <div class="burger-icon" style="float: right; margin: 10px;">
           <i class="fas fa-bars" style="font-size: 35px;"></i>
       </div>
    </div>    
      <div style="background-color: #16633F; width: 100%; height: 200px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;"> Shifting Exams </div>
    <div class="shift-form" style="padding: 70px 50px 70px 50px;">
        <!-- <form action="submit_shift_form.php" method="post" enctype="multipart/form-data"> -->
            <div style="display: flex; width: 100%; gap: 20px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="first_name" style="font-size: 25px; color: white;">First Name</label>
                    <input type="text" id="first_name" name="first_name" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;">
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="middle_name" style="font-size: 25px; color: white;">Middle Name (optional)</label>
                    <input type="text" id="middle_name" name="middle_name" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;">
                </div>
            </div>
            <div style="display: flex; width: 100%; gap: 20px; margin-top: 20px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="last_name" style="font-size: 25px; color: white;">Last Name</label>
                    <input type="text" id="last_name" name="last_name" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;">
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="student_id" style="font-size: 25px; color: white;">Student ID</label>
                    <input type="text" id="student_id" name="student_id" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;">
                </div>
            </div>
            <div style="display: flex; width: 100%; gap: 20px; margin-top: 20px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="course_to_shift" style="font-size: 25px; color: white;">Course to Shift</label>
                    <select id="course_to_shift" name="course_to_shift" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;">
                        <option value="course1">Information Technology</option>
                        <option value="course2">Nursing</option>
                        <option value="course3">Law</option>
                    </select>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="reason_to_shift" style="font-size: 25px; color: white;">Reason to Shift</label>
                    <select id="reason_to_shift" name="reason_to_shift" style="font-size: 25px; width: 100%; border-radius: 4.41px; border: 3px solid #0b3822; padding: 5px 0 5px 0; margin-top: 5px;">
                        <option value="academic">Academic</option>
                        <option value="personal">Personal</option>
                        <option value="career">Career</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; width: 100%; gap: 20px; margin-top: 30px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="picture" style="font-size: 25px; color: white;">2x2 Picture with Name Tag (not selfie)</label>
                    <label class="custom-file-upload" style="margin-top: 5px;">
                        <input type="file" id="picture" name="picture" onchange="showFileName('picture')">
                        <i class="fa-solid fa-upload"></i> Upload Picture
                    </label>
                    <span id="picture-file-name" class="file-name" style="color:rgb(193, 255, 202); font-size: 18px;"></span>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="grades" style="font-size: 25px; color: white;">All Downloadable Grades:</label>
                    <label class="custom-file-upload" style="margin-top: 5px;">
                        <input type="file" id="grades" name="grades" onchange="showFileName('grades')">
                        <i class="fa-solid fa-upload"></i> Upload Grades
                    </label>
                    <span id="grades-file-name" class="file-name"></span>
                </div>
            </div>
            <div style="display: flex; width: 100%; gap: 20px; margin-top: 40px;">
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="cor" style="font-size: 25px; color: white;">Latest COR</label>
                    <label class="custom-file-upload" style="margin-top: 5px;">
                        <input type="file" id="cor" name="cor" onchange="showFileName('cor')">
                        <i class="fa-solid fa-upload"></i> Upload COR
                    </label>
                    <span id="cor-file-name" class="file-name"></span>
                </div>
                <div style="flex: 1; display: flex; flex-direction: column;">
                    <label for="cet_result" style="font-size: 25px; color: white;">College Entrance Test Result</label>
                    <label class="custom-file-upload" style="margin-top: 5px;">
                        <input type="file" id="cet_result" name="cet_result" onchange="showFileName('cet_result')">
                        <i class="fa-solid fa-upload"></i> Upload CET Result
                    </label>
                    <span id="cet_result-file-name" class="file-name"></span>
                </div>
            </div>
            <div style="margin-top: 80px;">
                <button class="submit" type="submit">Submit</button>
            </div>
        </form>
    </div>
    <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
        <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
        <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
    </footer>
</body>
</html>

<script src="js/showFileName.js"></script>
