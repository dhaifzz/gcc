<?php
require_once '../../font/font.php';
require_once __DIR__ . '/../../database/database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

try {
    // Fetch the user details from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch profile image
    $stmt = $pdo->prepare("SELECT profile_image FROM profiles WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $profileRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profileRow && !empty($profileRow['profile_image'])) {
        $profile_image = $profileRow['profile_image'];
    } else {
        $profile_image = 'default-profile.png';
    }

    $_SESSION['profile_image'] = $profile_image;

    // Fetch 5 most recent appointments
    $stmt = $pdo->prepare("SELECT appointment_id, appointment_type, requested_date, requested_time, COALESCE(status, 'Pending') as status FROM appointments WHERE client_id = :user_id ORDER BY appointment_id DESC LIMIT 5");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch shifting requests with complete details including documents
    $stmt = $pdo->prepare("
        SELECT 
            s.*,
            CONCAT('/gcc/shared/main/', s.picture) as picture,
            CONCAT('/gcc/shared/main/', s.grades) as grades,
            CONCAT('/gcc/shared/main/', s.cor) as cor,
            CONCAT('/gcc/shared/main/', s.cet_result) as cet_result,
            d.first_name as director_first_name,
            d.last_name as director_last_name
        FROM shifting s
        LEFT JOIN users d ON s.approved_by = d.id 
        WHERE s.user_id = :user_id 
        ORDER BY s.submitted_at DESC 
        LIMIT 5
    "); 
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $shiftingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error fetching user details: " . $e->getMessage());
}

$icon = '';
$profile_title = '';
switch ($user['role']) {
    case 'Faculty':
        $profile_title = "Faculty's Profile";
        break;
    case 'College Student':
    case 'High School Student':
        $profile_title = "Student's Profile";
        break;
    case 'Outside Client':
        $profile_title = "Outside Client's Profile";
        break;
    default:
        $profile_title = "User's Profile";
        break;
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="../css/profile.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="navbar">
    <div class="navbar-items">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
       <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 3.25rem; height: 3.25rem;">
       <a class="website" href="<?php
    switch ($_SESSION['role']) {
        case 'Faculty':
            echo '../../client/inside/faculty/faculty.php';
            break;
        case 'College Student':
            echo '../../client/inside/student/college.php';
            break;
        case 'High School Student':
            echo '../../client/inside/student/high-school.php';
            break;
        case 'Outside Client':
            echo '../../client/outside/outside.php';
            break;
        default:
           echo '../../../auth/sign-in.php';  
    }
    ?>">WMSU Guidance and Counseling Center</a>
    </div>
       <div class="navbar-content">
    <button class="btn-sign-out" onclick="window.location.href='../../auth/sign-out.php'">Sign Out</button>
    </div>
  </div>

    <div class="content">
    <div class="container">
    <div style="background-color: #F1F1F1; padding: 70px 80px 100px; border-radius: 15px;">
        <div style="display: flex; justify-content: center; gap: 20px;">
            <!-- Profile Title -->
            <div style="width: 100%; text-align: center; margin-bottom: 20px; font-size: 20px;">
                <h2><?php echo $profile_title; ?></h2>
            </div>
        </div>
        <div style="display: flex; justify-content: center; gap: 20px;">
            <!-- Left Container (Profile Image & Name) -->
            <div style="width: 20%; background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 0 10px rgba(0,0,0,0.1); text-align: center; position: relative;">
                <!-- Profile Image Display -->
                <div style="position: relative; display: inline-block;">
                    <img id="profileImage" src="/gcc/img/profiles/<?php echo $profile_image; ?>?v=<?php echo time(); ?>" alt="Profile"
                         style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover;">
                    <!-- Edit Icon -->
                    <div onclick="document.getElementById('fileInput').click()" 
                         style="position: absolute; top: 0; right: 0; background-color: white; border-radius: 50%; 
                         width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; cursor: pointer; 
                         box-shadow: 0px 4px 8px 0px rgba(0, 0, 0, 0.1); transition: background-color 0.3s, transform 0.3s;" 
                         onmouseover="this.style.backgroundColor='rgba(17, 173, 100, 1)'; this.style.color='white'; this.style.transform='scale(1.1)';" 
                         onmouseout="this.style.backgroundColor='white'; this.style.color='black'; this.style.transform='scale(1)';">
                        <i class="fas fa-pen"></i>
                    </div>
                </div>

                <!-- Hidden File Input -->
                <form id="uploadForm" action="../backend/upload-profile.php" method="POST" enctype="multipart/form-data">
                    <input type="file" name="profileImage" id="fileInput" style="display: none;" accept="image/jpeg, image/png, image/gif, image/jpg">
                </form>

                <div id="message" style="margin-top: 10px; color: green;"></div>

                <!-- Displayed Name -->
                <div style="margin-top: 15px; font-size: 20px; font-weight: bold;">
                    <?php
                    $middleInitial = isset($user['middle_name'][0]) && !empty($user['middle_name']) ? $user['middle_name'][0] . '. ' : '';
                    echo $user['first_name'] . " " . $middleInitial . $user['last_name'];
                    ?>
                </div>  
            </div>
            
            <div style="width: 60%; background-color: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                <div style="font-weight: bold; margin-bottom: 10px; font-size: 18px;">Information:</div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                    <div class="info-box">Role: <span><?php echo htmlspecialchars($user['role']); ?></span></div>
                    <div class="info-box">Contact Number: <span><?php echo htmlspecialchars($user['contact_number']); ?></span></div>
                    <div class="info-box">Gender: <span><?php echo htmlspecialchars($user['sex']); ?></span></div>
                    <div class="info-box">Email: <span><?php echo htmlspecialchars($user['email']); ?></span></div>
                    <div class="info-box">School: <span><?php echo htmlspecialchars($user['school']); ?></span></div>
                    <div class="info-box">Course/Grade Level: <span><?php echo htmlspecialchars($user['course_grade']); ?></span></div>
                </div>
            </div>  
        </div>

        <div style="margin-top: 40px;">
            <h3>Recent Appointments</h3>
            <table style="width: 100%; border-collapse: collapse; background-color: white; border-radius: 9px;">
                <thead>
                    <tr>
                        <th style="border: 1px solid #ddd; padding: 8px;">Type</th>
                        <th style="border: 1px solid #ddd; padding: 8px;"> Requested Date</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Time</th>
                        <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <?php
                                $type = strtolower($appointment['appointment_type']);
                                switch ($type) {
                                    case 'counseling':
                                        $typeBg = '#e6f9ed';
                                        $typeColor = '#17b968';
                                        break;
                                    case 'assessment':
                                        $typeBg = '#e3f7f0';
                                        $typeColor = '#117864';
                                        break;
                                    default:
                                        $typeBg = '#f8f9fa';
                                        $typeColor = '#333';
                                        break;
                                }
                            ?>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <span style="
                                    display: inline-block;
                                    padding: 4px 16px;
                                    border-radius: 999px;
                                    background: <?php echo $typeBg; ?>;
                                    color: <?php echo $typeColor; ?>;
                                    font-weight: bold;
                                    font-size: 1em;
                                    ">
                                    <?php echo ucfirst(htmlspecialchars($appointment['appointment_type'])); ?>
                                </span>
                            </td>
                            <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($appointment['requested_date'])); ?></td>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <?php
                                    $time = trim($appointment['requested_time']);
                                    $status = strtolower($appointment['status']);
                                    if ($status === 'rescheduled' && (empty($time) || is_null($time))) {
                                        echo '<span style="color:#888;">Unselected</span>';
                                    } elseif ($status === 'declined') {
                                        echo '<span style="text-decoration: line-through; color: #721c24;">
                                                <i class="fas fa-times" style="color: #721c24; margin-right: 5px;"></i>' . 
                                                ucfirst(htmlspecialchars($time)) . 
                                              '</span>';
                                    } else {
                                        echo ucfirst(htmlspecialchars($time));
                                    }
                                ?>
                            </td>
                                <?php
                                $status = strtolower($appointment['status']);
                                switch ($status) {
                                    case 'pending':
                                        $bgColor = '#fff3cd';
                                        $color = '#856404';
                                        $border = '1px solid rgb(255, 223, 126)';
                                        $icon = '<i class="fas fa-spinner fa-spin" style="color: #856404; margin-right: 5px;"></i>';
                                        break;
                                    case 'approved':
                                        $bgColor = '#d4edda';
                                        $color = '#155724';
                                        $border = '1px solid rgb(112, 221, 137)';
                                        $icon = '<i class="fa-solid fa-check-double" style="color: #155724; margin-right: 5px;"></i>';
                                        break;
                                    case 'completed':
                                        $bgColor = '#a5b1fd';
                                        $color = '#1743b9';
                                        $border = '1px solid rgba(137, 169, 255, 0.75)';
                                        $icon = '<i class="fa-solid fa-circle-check" style="color: #1743b9; margin-right: 5px;"></i>';
                                        break;
                                    case 'cancelled':
                                        $bgColor = '#fb6363';
                                        $color = '#800000';
                                        $border = '1px solid rgba(175, 49, 49, 0.51)';
                                        $icon = '<i class="fas fa-times-circle" style="color: #800000; margin-right: 5px;"></i>';
                                        break;
                                    case 'declined':
                                        $bgColor = '#f8d7da'; 
                                        $color = '#721c24';   
                                        $border = '1px solid rgb(241, 166, 174)';
                                        $icon = '<i class="fa-solid fa-ban" style="color: #721c24; margin-right: 5px;"></i>';
                                        break;
                                    case 'rescheduled':
                                        $bgColor = '#f8d7da';
                                        $color = '#721c24';
                                        $border = '1px solid rgb(241, 166, 174)';
                                        $icon = '<i class="fa-solid fa-calendar-xmark" style="color: #721c24; margin-right: 5px;"></i>';
                                        break;
                                    case 'evaluated':
                                        $bgColor = '#e2f7e2';
                                        $color = '#218838';
                                        $border = '1px solid rgba(156, 255, 177, 0.7)';
                                        $icon = '<i class="fa-solid fa-check" style="color: #218838; margin-right: 5px;"></i>';
                                        break;
                                    default:
                                        $bgColor = '#e2e3e5';
                                        $color = '#383d41';
                                        $border = '1px solid #d6d8db';
                                        $icon = '';
                                        break;
                                }
                            ?>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <div style="position: relative; display: inline-block;" class="status-container">
                                    <span style="display: inline-block; padding: 4px 16px; border-radius: 999px; background: <?php echo $bgColor; ?>; color: <?php echo $color; ?>; border: <?php echo $border; ?>; font-weight: bold;">
                                        <?php echo isset($icon) ? $icon : ''; ?>
                                        <?php echo ucfirst(htmlspecialchars($appointment['status'])); ?>
                                    </span>
                                    <?php if (strtolower($appointment['status']) === 'approved'): ?>
                                        <div class="tooltip">Your request has been approved. You may view it on your active appointment.</div>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($user['role'] === 'College Student' && !empty($shiftingRequests)): ?>
            <div style="margin-top: 40px;">
                <h3>Recent Shifting Requests</h3>
                <table style="width: 100%; border-collapse: collapse; background-color: white; border-radius: 9px;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #ddd; padding: 8px;">Current Course</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Course to Shift</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Date/Time</th>
                            <th style="border: 1px solid #ddd; padding: 8px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shiftingRequests as $request): ?>
                            <tr>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($request['current_course'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($request['course_to_shift'])); ?></td>
                                <td style="border: 1px solid #ddd; padding: 8px;"><?php echo ucfirst(htmlspecialchars($request['submitted_at'])); ?></td>
                            <?php
                                $status = strtolower($request['status']);
                                switch ($status) {
                                    case 'pending':
                                        $bgColor = '#fff3cd';
                                        $color = '#856404';
                                        $border = '1px solid rgb(255, 223, 126)';
                                        $icon = '<i class="fas fa-spinner fa-spin" style="color: #856404; margin-right: 5px;"></i>';
                                        break;
                                    case 'approved':
                                        $bgColor = '#d4edda';
                                        $color = '#155724';
                                        $border = '1px solid rgb(112, 221, 137)';
                                        $icon = '<i class="fa-solid fa-check-double" style="color: #155724; margin-right: 5px;"></i>';
                                        break;
                                    case 'completed':
                                        $bgColor = '#a5b1fd';
                                        $color = '#1743b9';
                                        $border = '1px solid rgba(137, 169, 255, 0.75)';
                                        $icon = '<i class="fa-solid fa-circle-check" style="color: #1743b9; margin-right: 5px;"></i>';
                                        break;
                                    case 'cancelled':
                                        $bgColor = '#fb6363';
                                        $color = '#800000';
                                        $border = '1px solid rgba(175, 49, 49, 0.51)';
                                        $icon = '<i class="fas fa-times-circle" style="color: #800000; margin-right: 5px;"></i>';
                                        break;
                                    case 'declined':
                                    case 'rejected':
                                        $bgColor = '#f8d7da';
                                        $color = '#721c24';
                                        $border = '1px solid rgb(241, 166, 174)';
                                        $icon = '<i class="fa-solid fa-ban" style="color: #721c24; margin-right: 5px;"></i>';
                                        break;
                                    case 'evaluated':
                                        $bgColor = '#e2f7e2';
                                        $color = '#218838';
                                        $border = '1px solid rgba(156, 255, 177, 0.7)';
                                        $icon = '<i class="fa-solid fa-check" style="color: #218838; margin-right: 5px;"></i>';
                                        break;
                                    default:
                                        $bgColor = '#e2e3e5';
                                        $color = '#383d41';
                                        $border = '1px solid #d6d8db';
                                        $icon = '';
                                        break;
                                }
                            ?>
                            <td style="border: 1px solid #ddd; padding: 8px;">
                                <span style="display: inline-block; padding: 4px 16px; border-radius: 999px; background: <?php echo $bgColor; ?>; color: <?php echo $color; ?>; border: <?php echo $border; ?>; font-weight: bold;">
                                    <?php echo isset($icon) ? $icon : ''; ?>
                                    <?php echo ucfirst(htmlspecialchars($request['status'])); ?>
                                </span>
                                <?php if (!in_array(strtolower($request['status']), ['rejected', 'completed'])): ?>
                                    <button onclick="viewShiftingDetails(<?php echo htmlspecialchars(json_encode($request)); ?>)" class="preview-btn">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                <?php endif; ?>
                            </td>                          
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Shifting Details Modal -->
<div id="shiftingDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Shifting Request Details</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <!-- Declined Status Message -->
            <div id="declinedMessage" style="display: none; background-color: #f8d7da; color: #721c24; padding: 20px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <i class="fa-solid fa-circle-exclamation" style="font-size: 48px; margin-bottom: 15px;"></i>
                <h3 style="margin: 10px 0;">Request Declined</h3>
                <p style="font-size: 16px;">We regret to inform you that your shifting request has been declined as you did not meet the requirements for the shifting form. However, you may request the form again once all requirements are complete.</p>
                <a href="../main/shifting.php" class="request-form-btn">
                  <i class="fas fa-file-alt"></i>
                   Request Form Again
                </a>
            </div>

            <!-- Approved Status Message -->
            <div id="approvedMessage" style="display: none;">
                <div style="background-color: #d4edda; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <div id="appointmentSlip" style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div style="text-align: center; margin-bottom: 20px;">
                            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="height: 60px; margin-right: 20px;">
                            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="height: 60px;">
                            <div style="margin-top: 10px;">
                                <div style="font-size: 18px; color: #16633F; font-weight: 600;">Guidance and Counseling Center</div>
                                <div style="font-size: 16px; color:rgba(0, 0, 0, 0.77);">Western Mindanao State University</div>
                            </div>
                            <h2 style="margin: 30px 0 0 0; color: #16633F;">Shifting Form Request Slip</h2>
                        </div>
                        <div style="margin-bottom: 20px; font-size: 16px; line-height: 1.6;">
                            <p id="appointmentMessage" style="margin-bottom: 20px;"></p>
                        </div>
                        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <h3 style="color: #16633F; margin-bottom: 15px;">Receipt Details</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 8px 0; color: #495057; font-weight: 600;">Shifting ID:</td>
                                    <td id="shiftingId" style="padding: 8px 0;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #495057; font-weight: 600;">Course Change:</td>
                                    <td id="courseChange" style="padding: 8px 0;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #495057; font-weight: 600;">Client:</td>
                                    <td id="clientName" style="padding: 8px 0;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #495057; font-weight: 600;">Documents Submitted:</td>
                                    <td id="documentsSubmitted" style="padding: 8px 0;"></td>
                                </tr>
                                <tr>
                                    <td style="padding: 8px 0; color: #495057; font-weight: 600;">Approved By:</td>
                                    <td id="approvedBy" style="padding: 8px 0;"></td>
                                </tr>
                            </table>
                        </div>
                        <div style="text-align: center;">
                            <button onclick="printAppointmentSlip()" style="
                                background-color: #16633F;
                                color: white;
                                border: none;
                                padding: 12px 25px;
                                border-radius: 6px;
                                font-weight: 600;
                                font-size: 16px;
                                cursor: pointer;
                                transition: all 0.3s ease;
                            ">
                                <i class="fas fa-print" style="margin-right: 8px;"></i>
                                Print Appointment Slip
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Regular Content -->
            <div id="regularContent">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Current Course</label>
                        <span id="currentCourse"></span>
                    </div>
                    <div class="info-item">
                        <label>Course to Shift</label>
                        <span id="courseToShift"></span>
                    </div>
                    <div class="info-item">
                        <label>Submission Date</label>
                        <span id="submissionDate"></span>
                    </div>
                    <div class="info-item">
                        <label>Status</label>
                        <span id="shiftingStatus"></span>
                    </div>
                </div>

                <div class="reason-section">
                    <h3>Reason for Shifting</h3>
                    <div class="reason-box">
                        <p id="shiftingReason"></p>
                    </div>
                </div>

                <div class="document-section">
                    <h3>Submitted Documents</h3>
                    <div class="document-grid">
                        <div class="document-item">
                            <label>2x2 Picture</label>
                            <div class="document-preview" id="picturePreview">
                                <img src="" alt="2x2 Picture" style="max-width: 100%; max-height: 100%; display: none;">
                                <div class="preview-placeholder">
                                    <i class="fas fa-image"></i>
                                    <p>2x2 Picture</p>
                                </div>
                            </div>
                        </div>
                        <div class="document-item">
                            <label>Grades</label>
                            <div class="document-preview pdf-preview" id="gradesPreview">
                                <div class="preview-placeholder">
                                    <i class="fas fa-file-pdf"></i>
                                    <p>Grades PDF</p>
                                </div>
                            </div>
                        </div>
                        <div class="document-item">
                            <label>COR</label>
                            <div class="document-preview pdf-preview" id="corPreview">
                                <div class="preview-placeholder">
                                    <i class="fas fa-file-pdf"></i>
                                    <p>COR PDF</p>
                                </div>
                            </div>
                        </div>
                        <div class="document-item">
                            <label>CET Result</label>
                            <div class="document-preview pdf-preview" id="cetPreview">
                                <div class="preview-placeholder">
                                    <i class="fas fa-file-pdf"></i>
                                    <p>CET Result PDF</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div id="appointmentDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Appointment Details</h2>
            <span class="close" onclick="closeAppointmentModal()">&times;</span>
        </div>
        <div id="appointmentSlip">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<script>
function viewShiftingDetails(request) {
    // Get references to modal sections
    const declinedMessage = document.getElementById('declinedMessage');
    const approvedMessage = document.getElementById('approvedMessage');
    const regularContent = document.getElementById('regularContent');

    // Check status and show appropriate content
    if (request.status.toLowerCase() === 'declined') {
        declinedMessage.style.display = 'block';
        approvedMessage.style.display = 'none';
        regularContent.style.display = 'none';
    } else if (request.status.toLowerCase() === 'approved') {
        declinedMessage.style.display = 'none';
        approvedMessage.style.display = 'block';
        regularContent.style.display = 'none';

        // Update appointment slip content
        const fullName = `${request.first_name} ${request.middle_name ? request.middle_name + ' ' : ''}${request.last_name}`;
        document.getElementById('appointmentMessage').innerHTML = 
          `Hi <strong>${fullName}</strong>! Please proceed to the GCC Office at WMSU to request your shifting form. You may come anytime from Monday to Friday during office hours: 8:00–11:00 AM or 2:00–5:00 PM. Kindly bring your appointment slip and any required documents. For inquiries, feel free to contact us ahead of time. Thank you!`;
        
        document.getElementById('shiftingId').textContent = request.id;
        document.getElementById('courseChange').textContent = `${request.current_course} to ${request.course_to_shift}`;
        document.getElementById('clientName').textContent = fullName;
        
        // Create checkmarks for submitted documents
        const documents = [
            { name: 'Grades', status: request.grades ? '✓' : '✗' },
            { name: 'COR', status: request.cor ? '✓' : '✗' },
            { name: 'CET Result', status: request.cet_result ? '✓' : '✗' }
        ];
        document.getElementById('documentsSubmitted').innerHTML = documents
            .map(doc => `${doc.status} ${doc.name}`)
            .join('<br>');

        // Show director's name if available
        const directorName = request.director_first_name && request.director_last_name 
            ? `${request.director_first_name} ${request.director_last_name}`
            : 'Pending';
        document.getElementById('approvedBy').textContent = directorName;
    } else {
        declinedMessage.style.display = 'none';
        approvedMessage.style.display = 'none';
        regularContent.style.display = 'block';
        
        // Update regular content
        document.getElementById('currentCourse').textContent = request.current_course;
        document.getElementById('courseToShift').textContent = request.course_to_shift;
        document.getElementById('submissionDate').textContent = new Date(request.submitted_at).toLocaleString();
        document.getElementById('shiftingReason').textContent = request.reason_to_shift || 'No reason provided';
        
        // Format status with icon
        const statusSpan = document.getElementById('shiftingStatus');
        let icon = '';
        let color = '';
        let bgColor = '';
        
        switch(request.status.toLowerCase()) {
            case 'pending':
                icon = '<i class="fas fa-spinner fa-spin"></i>';
                color = '#856404';
                bgColor = '#fff3cd';
                break;
            case 'approved':
                icon = '<i class="fa-solid fa-check-double"></i>';
                color = '#155724';
                bgColor = '#d4edda';
                break;
            case 'rejected':
            case 'declined':
                icon = '<i class="fa-solid fa-ban"></i>';
                color = '#721c24';
                bgColor = '#f8d7da';
                break;
            case 'evaluated':
                icon = '<i class="fa-solid fa-check"></i>';
                color = '#218838';
                bgColor = '#e2f7e2';
                break;
        }
        
        statusSpan.innerHTML = `<span style="display: inline-block; padding: 4px 16px; border-radius: 999px; background: ${bgColor}; color: ${color}; font-weight: bold;">
            ${icon} ${request.status.charAt(0).toUpperCase() + request.status.slice(1)}
        </span>`;

        // Handle document previews
        const pictureImg = document.querySelector('#picturePreview img');
        const picturePlaceholder = document.querySelector('#picturePreview .preview-placeholder');
        
        if (request.picture) {
            pictureImg.src = request.picture;
            pictureImg.style.display = 'block';
            picturePlaceholder.style.display = 'none';
            
            // Handle image load error
            pictureImg.onerror = function() {
                pictureImg.style.display = 'none';
                picturePlaceholder.style.display = 'block';
            };
        } else {
            pictureImg.style.display = 'none';
            picturePlaceholder.style.display = 'block';
        }

        // Add click handlers for PDF documents
        const pdfPreviews = [
            { id: 'grades', path: request.grades },
            { id: 'cor', path: request.cor },
            { id: 'cet_result', path: request.cet_result }
        ];

        pdfPreviews.forEach(doc => {
            const preview = document.getElementById(`${doc.id}Preview`);
            if (preview && doc.path) {
                preview.onclick = () => window.open(doc.path, '_blank');
                preview.style.cursor = 'pointer';
                preview.title = 'Click to view PDF';
            } else if (preview) {
                preview.onclick = null;
                preview.style.cursor = 'default';
                preview.title = 'No document available';
            }
        });
    }

    // Show modal
    const modal = document.getElementById('shiftingDetailsModal');
    modal.style.display = 'block';

    // Close modal when clicking on X or outside
    const closeBtn = modal.querySelector('.close');
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
}

function printAppointmentSlip() {
    const printContent = document.getElementById('appointmentSlip');
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContent.innerHTML;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload(); // Reload the page after printing
}

function viewAppointmentDetails(appointmentId) {
    const modal = document.getElementById('appointmentDetailsModal');
    const appointmentSlip = document.getElementById('appointmentSlip');
    
    // Show loading state
    appointmentSlip.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    modal.style.display = 'block';

    // Fetch appointment details
    fetch(`/gcc/shared/main/appointment-pages/generate-slip.php?appointment_id=${appointmentId}`)
        .then(response => response.text())
        .then(html => {
            appointmentSlip.innerHTML = html;
        })
        .catch(error => {
            console.error('Error:', error);
            appointmentSlip.innerHTML = '<div style="color: red; padding: 20px;">Error loading appointment details. Please try again.</div>';
        });
}

function closeAppointmentModal() {
    const modal = document.getElementById('appointmentDetailsModal');
    modal.style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('appointmentDetailsModal');
    if (event.target == modal) {
        closeAppointmentModal();
    }
}
</script>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
  </div>
</body>
</html>

<script src="/gcc/js/upload-profile.js"></script>
<script src="/gcc/js/sidebar.js"></script>