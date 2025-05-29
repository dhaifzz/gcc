<?php
session_start();

require_once '../../../font/font.php';
require_once '../../../client/navbar.php';
require_once '../../../database/database.php';

// Redirect if not logged in or invalid role
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['College Student', 'High School Student', 'Outside Client', 'Faculty'])) {
    header("Location: ../../../../auth/sign-in.php");
    exit();
}

// Initialize variables
$email = $_SESSION['email'];
$profile_image = '/gcc/img/profiles/default-profile.png';
$message = '';
$error = '';

// Get user data
$query = "SELECT * FROM users WHERE email = :email";
$stmt = $pdo->prepare($query);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $user_id = $user['id'];
    $_SESSION['user_id'] = $user_id;
    
    // Get profile image
    $profileQuery = "SELECT profile_image FROM profiles WHERE user_id = :user_id";
    $profileStmt = $pdo->prepare($profileQuery);
    $profileStmt->execute(['user_id' => $user_id]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

    if ($profile && !empty($profile['profile_image'])) {
        $profile_image = '/gcc/img/profiles/' . htmlspecialchars($profile['profile_image']);
    }
}

// Check for active appointments
$activeAppointment = false;
$appointmentCheckQuery = "SELECT status FROM appointments WHERE client_id = :user_id AND appointment_type = 'counseling' ORDER BY appointment_id DESC LIMIT 1";
$appointmentCheckStmt = $pdo->prepare($appointmentCheckQuery);
$appointmentCheckStmt->execute(['user_id' => $user_id]);
$latestAppointment = $appointmentCheckStmt->fetch(PDO::FETCH_ASSOC);

if ($latestAppointment) {
    $status = strtolower($latestAppointment['status']);
    if (!in_array($status, ['cancelled', 'completed', 'rescheduled', 'declined'])) {
        $activeAppointment = true;
    }
}

// Get booked appointments
$bookedAppointments = [];
$appointment_type = $_POST['appointment_type'] ?? 'counseling';
$appointmentQuery = "SELECT requested_date, requested_time FROM appointments WHERE status != 'Cancelled' AND appointment_type = :appointment_type";
$appointmentStmt = $pdo->prepare($appointmentQuery);
$appointmentStmt->execute(['appointment_type' => $appointment_type]);

while ($row = $appointmentStmt->fetch(PDO::FETCH_ASSOC)) {
    $bookedAppointments[$row['requested_date']][] = $row['requested_time'];
}

// Time slots
$allTimeSlots = ['8am - 9am', '9am - 10am', '10am - 11am', '2pm - 3pm', '3pm - 4pm', '4pm - 5pm'];

// Calculate fully booked dates
$fullyBookedDates = [];
foreach ($bookedAppointments as $date => $times) {
    if (count($times) >= count($allTimeSlots)) {
        $fullyBookedDates[] = $date;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'book') {
    if ($activeAppointment) {
        $_SESSION['error'] = "You already have an active appointment. Please wait until it's completed or cancelled before booking another one.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $client_id = $_SESSION['user_id'];
        $appointment_type = $_POST['appointment_type'] ?? 'counseling';
        $requested_date = $_POST['requested_date'];
        $requested_time = $_POST['requested_time'];
        $status = 'pending';

        try {
            // Check for existing rescheduled appointment
            $stmt = $pdo->prepare("SELECT appointment_id FROM appointments WHERE client_id = ? AND appointment_type = ? AND status = 'rescheduled' ORDER BY appointment_id DESC LIMIT 1");
            $stmt->execute([$client_id, $appointment_type]);
            $rescheduled = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check slot availability
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE requested_date = ? AND requested_time = ? AND status NOT IN ('cancelled', 'declined') AND appointment_type = ?");
            $stmt->execute([$requested_date, $requested_time, $appointment_type]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $_SESSION['error'] = "The selected time slot is no longer available.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }

            if ($rescheduled) {
                // Update existing appointment
                $stmt = $pdo->prepare("UPDATE appointments SET requested_date = ?, requested_time = ?, status = 'pending' WHERE appointment_id = ?");
                $stmt->execute([$requested_date, $requested_time, $rescheduled['appointment_id']]);
                $_SESSION['success'] = 2;
            } else {
                // Create new appointment
                $stmt = $pdo->prepare("INSERT INTO appointments (client_id, appointment_type, requested_date, requested_time, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$client_id, $appointment_type, $requested_date, $requested_time, $status]);
                $_SESSION['success'] = 1;
            }
            
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
            
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Retrieve messages from session
if (isset($_SESSION['success'])) {
    if ($_SESSION['success'] == 1) {
        $message = "Counceling appointment booked successfully";
    } elseif ($_SESSION['success'] == 2) {
        $message = "Counseling appointment rescheduled successfully";
    }
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Close database connection
$pdo = null;
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GCC Website</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="../../css/appoint-counsel.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    <!-- Navbar -->
    <?php appointPageNavbar($profile_image); ?> 

    <div class="container">
        <div style="background-color: #16633F; width: 100%; height: 120px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;">
            <i class="fa-solid fa-calendar-days" style="margin-right: 15px;"></i>
            Book your Counseling Session!
        </div>         
        <div style="padding: 40px; display: flex; justify-content: center; gap: 20px;">
                <?php if ($activeAppointment): ?>
                    <div class="appointment-carding">
                         <div style="padding: 50px; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center;">
                           <i class="fa-solid fa-calendar" style="font-size: 5rem; color: #16633F;"></i>
                         </div>
                        <h2 style="color: #dc3545;">You already have an active appointment for Counseling</h2>
                        <p>You can only book one appointment at a time. Please wait until your current appointment is completed or cancelled before booking another one.</p>
                        <p>However, you may book again if your current appointment has been rescheduled..</p>
                        <button onclick="loadAppointmentDetails('counseling')" class="appointment-button">
                            <i class="fas fa-eye"></i> View My Appointment
                        </button>
                        <div style="padding: 50px; background-color: #f8f9fa;"></div>
                    </div>
                <?php else: ?>
                <form id="appointmentForm" style="display: flex; flex-direction: column; align-items: center; gap: 20px;" method="post">
                    <input type="hidden" name="action" value="book">
                    <input type="hidden" id="requested_date" name="requested_date" required>
                    <input type="hidden" id="requested_time" name="requested_time" required>
                    <input type="hidden" id="appointment_type" name="appointment_type" value="counseling" required>
                    <div style="display: flex; gap: 20px;">
                        <div class="calendar-container">
                            <div class="calendar-header">
                                <button class="btn-sched" type="button" onclick="prevMonth()">&#8249;</button>
                                <h2 id="calendarMonth">January 2025</h2>
                                <button class="btn-sched" type="button" onclick="nextMonth()">&#8250;</button>
                            </div>
                            <div class="calendar-grid" id="calendarDays">
                                <!-- Calendar days will be generated here -->
                            </div>
                        </div>
                        <div class="time-slot-container">
                         <h2 style="text-align: center; margin-bottom: 20px;">Time</h2>
                         <div class="time-slot-section">
                             <h3>Schedule for Morning</h3>
                             <div class="time-slot" data-time="8am - 9am" onclick="selectTimeSlot('8am - 9am')">8am - 9am</div>
                             <div class="time-slot" data-time="9am - 10am" onclick="selectTimeSlot('9am - 10am')">9am - 10am</div>
                             <div class="time-slot" data-time="10am - 11am" onclick="selectTimeSlot('10am - 11am')">10am - 11am</div>
                         </div>
                         <div class="time-slot-section">
                             <h3>Schedule for Afternoon</h3>
                             <div class="time-slot" data-time="2pm - 3pm" onclick="selectTimeSlot('2pm - 3pm')">2pm - 3pm</div>
                             <div class="time-slot" data-time="3pm - 4pm" onclick="selectTimeSlot('3pm - 4pm')">3pm - 4pm</div>
                             <div class="time-slot" data-time="4pm - 5pm" onclick="selectTimeSlot('4pm - 5pm')">4pm - 5pm</div>
                         </div>
                         <button type="submit" class="save-record">
                        Save Record
                    </button>
                    </div>
                </form>
            <?php endif; ?>
         </div>
         </div>
         <div class="message-container">
             <?php if ($message): ?>
                 <div class="success"><?php echo htmlspecialchars($message); ?></div>
             <?php endif; ?>
             <?php if ($error): ?>
                 <div class="error"><?php echo htmlspecialchars($error); ?></div>
             <?php endif; ?>
         </div>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
  </div>

  <!-- View/Edit Appointment Modal -->
<div id="appointmentModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <span class="closing-btn" onclick="closeModal('appointmentModal')">&times;</span>
        <h2>Active Counseling Appointment</h2>
        
        <div id="appointmentDetails" style="margin-bottom: 20px;">
            <!-- Appointment details will be loaded here -->
        </div>

        <div id="slipButton" style="display: none; text-align: center; margin-bottom: 20px;">
            <button onclick="viewAppointmentSlip()" class="slip-btn">
                <i class="fas fa-file-alt"></i> View Appointment Slip
            </button>
        </div>
        
        <div id="editForm" style="display: none;">
            <form id="updateAppointmentForm">
                <input type="hidden" id="edit_appointment_id">
                <div class="form-group">
                    <label for="edit_date">Requested Date:</label>
                    <input type="text" id="edit_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_time">Time:</label>
                    <select id="edit_time" class="form-control" required>
                        <option value="" disabled selected>Select a date first</option>
                    </select>
                    <div id="timeSlotError" style="color: #dc3545; display: none; margin-top: 5px;"></div>
                </div>
                <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">
                    <button type="submit" class="save-btn">
                        <span class="btn-text"><i class="fas fa-save"></i> Save Changes</span>
                        <span class="loading-spinner" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i> Saving...
                        </span>
                    </button>
                    <button type="button" class="cancel-btn" onclick="toggleEditForm(false)">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
        
        <div id="actionButtons" style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">
            <button onclick="toggleEditForm(true)" class="edit-btn">
                <i class="fas fa-edit"></i> Edit Appointment
            </button>
            <button onclick="cancelAppointment('counseling')" class="cancel-btn">
                <i class="fas fa-ban"></i> Cancel Appointment 
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Appointment -->
<div id="confirmationModal" class="modal">
    <div class="modal-box">
        <h2>Confirm Cancellation?</h2>
        <div class="modal-actions">
            <button id="confirmCancelBtn" class="cancel-btn">Yes, Cancel</button>
            <button onclick="closeModal('confirmationModal')" class="keep-btn">No, Keep It</button>
        </div>
    </div>
</div>

    <script src="/gcc/js/sidebar.js"></script>
    <script> const bookedAppointments = <?php echo json_encode($bookedAppointments); ?>; const fullyBookedDates = <?php echo json_encode($fullyBookedDates); ?>; const allTimeSlots = <?php echo json_encode($allTimeSlots); ?>;</script>
    <script src="/gcc/js/calendar.js"></script>
    <!-- <script src="/gcc/js/edit-appointments.js"></script> -->
    <script src="/gcc/js/edit-view-appointments.js"></script>
    <script>
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Store scroll position
        const scrollY = window.scrollY;
        document.body.style.top = `-${scrollY}px`;
        
        // Add modal-open class
        document.body.classList.add('modal-open');
        
        // Show modal
        modal.style.display = 'flex';
        
        // Trigger reflow for animation
        void modal.offsetWidth;
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('closing');
        
        setTimeout(() => {
            modal.classList.remove('show', 'closing');
            modal.style.display = 'none';
            
            // Restore scroll position
            const scrollY = Math.abs(parseInt(document.body.style.top || '0'));
            document.body.classList.remove('modal-open');
            document.body.style.top = '';
            window.scrollTo(0, scrollY);
        }, 300);
    }
}

</script>
</body>
</html>