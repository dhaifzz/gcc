<?php
require_once '../../../font/font.php';
require_once '../../../client/navbar.php';
require_once '../../../database/database.php';

session_start();
if (!isset($_SESSION['email']) || !in_array($_SESSION['role'], ['College Student', 'High School Student', 'Outside Client', 'Faculty',])) {
    header("Location: ../../../auth/sign-in.php");
    exit();
}

$email = $_SESSION['email'];
$query = "SELECT * FROM users WHERE email = :email";
$stmt = $pdo->prepare($query);
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$profile_image = '/gcc/img/profiles/default-profile.png'; 

if ($user) {
    $user_id = $user['id'];
    $profileQuery = "SELECT profile_image FROM profiles WHERE user_id = :user_id";
    $profileStmt = $pdo->prepare($profileQuery);
    $profileStmt->execute(['user_id' => $user_id]);
    $profile = $profileStmt->fetch(PDO::FETCH_ASSOC);

    if ($profile && !empty($profile['profile_image'])) {
        $profile_image = '/gcc/img/profiles/' . htmlspecialchars($profile['profile_image']);
    }
}

// Handle form submission
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'book') {
    $client_id = $_SESSION['user_id'];
    $appointment_type = 'counseling'; 
    $requested_date = $_POST['requested_date'];
    $requested_time = $_POST['requested_time']; 
    $status = 'Pending';

    try {
        // Check if user has an existing counseling appointment that's not rescheduled, completed, or cancelled
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments 
                              WHERE client_id = ? 
                              AND appointment_type = 'counseling'
                              AND status NOT IN ('Completed', 'Cancelled', 'Rescheduled')");
        $stmt->execute([$client_id]);
        $existingAppointments = $stmt->fetchColumn();

        if ($existingAppointments > 0) {
            $error = "You already have an active counseling appointment. Please complete, cancel, or reschedule it before booking a new one.";
        } else {
            // Check if the selected time and date are already reserved
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE requested_date = ? AND requested_time = ?");
            $stmt->execute([$requested_date, $requested_time]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $error = "The selected time and date are already reserved. Please choose another.";
            } else {
                // Prepare the SQL statement
                $stmt = $pdo->prepare("INSERT INTO appointments (client_id, appointment_type, requested_date, requested_time, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bindParam(1, $client_id);
                $stmt->bindParam(2, $appointment_type);
                $stmt->bindParam(3, $requested_date);
                $stmt->bindParam(4, $requested_time);
                $stmt->bindParam(5, $status);

                // Execute the statement
                if ($stmt->execute()) {
                    $message = "Counseling appointment booked successfully";
                } else {
                    $error = "Error booking appointment: " . implode(" ", $stmt->errorInfo());
                }
            }
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }

    $stmt = null;
}

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
    <style>
        .time-slot {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .time-slot:hover {
            background-color: #f0f0f0;
        }
        .time-slot.selected {
            background-color: #11AD64;
            color: white;
        }
        .error {
            color: #DC143C;
            margin-top: 10px;
            text-align: center;
            font-size: 16px;
            padding: 10px;
            background-color: #FFE5E5;
            border-radius: 5px;
            border: 1px solid #DC143C;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .success {
            color: #11AD64;
            margin-top: 10px;
            text-align: center;
            font-size: 16px;
            padding: 10px;
            background-color: #E8F5E9;
            border-radius: 5px;
            border: 1px solid #11AD64;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
</head>
<body>
     <!-- Navbar -->
     <?php appointPageNavbar($profile_image); ?> 

       <div class="container">
         <div style="background-color: #16633F; width: 100%; height: 150px; font-size: 40px; font-weight: 500; color: white; display: flex; justify-content: center; align-items: center;"> Schedule your Counseling Appointment </div>
         <div style="padding: 40px; display: flex; justify-content: center; gap: 20px;">
            <form id="appointmentForm" style="display: flex; flex-direction: column; align-items: center; gap: 20px;" method="post">
                <input type="hidden" name="action" value="book">
                <input type="hidden" id="requested_date" name="requested_date" required>
                <input type="hidden" id="requested_time" name="requested_time" required>
                <div style="display: flex; gap: 20px;">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <button type="button" onclick="prevMonth()">&#8249;</button>
                            <h2 id="calendarMonth">January 2025</h2>
                            <button type="button" onclick="nextMonth()">&#8250;</button>
                        </div>
                        <div class="calendar-grid" id="calendarDays">
                            <!-- Calendar days will be generated here -->
                        </div>
                    </div>
                    <div class="time-slot-container">
                        <h2 style="text-align: center; margin-bottom: 20px;">Time</h2>
                        <div class="time-slot-section">
                            <h3>Schedule for Morning</h3>
                            <div class="time-slot" onclick="selectTimeSlot('8am - 9am')">8am - 9am</div>
                            <div class="time-slot" onclick="selectTimeSlot('9am - 10am')">9am - 10am</div>
                            <div class="time-slot" onclick="selectTimeSlot('10am - 11am')">10am - 11am</div>
                        </div>
                        <div class="time-slot-section">
                            <h3>Schedule for Afternoon</h3>
                            <div class="time-slot" onclick="selectTimeSlot('2pm-3pm')">2pm - 3pm</div>
                            <div class="time-slot" onclick="selectTimeSlot('3pm-4pm')">3pm - 4pm</div>
                            <div class="time-slot" onclick="selectTimeSlot('4pm-5pm')">4pm - 5pm</div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="save-record">
                    Save Record
                </button>
            </form>
         </div>
         <?php if ($error): ?>
             <div class="error"><?php echo $error; ?></div>
         <?php endif; ?>
         <?php if ($message): ?>
             <div class="success"><?php echo $message; ?></div>
         <?php endif; ?>
         <footer style="background-color: #DC143C; color: white; padding-top: 5px; display: flex; justify-content: space-between; align-items: center;">
            <div style="margin-left: 20px;">Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="../../../../gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
  </div>

<script src="/gcc/js/sidebar.js"></script>
<script>
const calendarDays = document.getElementById('calendarDays');
const calendarMonth = document.getElementById('calendarMonth');
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();
const today = new Date();

let isDaySelected = false;
let selectedTimeSlot = null;
let selectedDate = null;

// Convert PHP data to JS
const bookedAppointments = <?php echo json_encode($bookedAppointments ?? []); ?>;
const fullyBookedDates = <?php echo json_encode($fullyBookedDates ?? []); ?>;
const allTimeSlots = ['8am - 9am', '9am - 10am', '10am - 11am', '2pm - 3pm', '3pm - 4pm', '4pm - 5pm'];

function generateCalendar(month, year) {
    const date = new Date(year, month, 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const firstDay = date.getDay();
    calendarDays.innerHTML = '';

    const weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    weekdays.forEach(day => {
        const dayCell = document.createElement('div');
        dayCell.textContent = day;
        dayCell.classList.add('weekdays');
        calendarDays.appendChild(dayCell);
    });

    for (let i = 0; i < firstDay; i++) {
        const emptyCell = document.createElement('div');
        calendarDays.appendChild(emptyCell);
    }

    for (let i = 1; i <= daysInMonth; i++) {
        const dayCell = document.createElement('div');
        dayCell.textContent = i;
        const dayOfWeek = new Date(year, month, i).getDay();
        const cellDate = new Date(year, month, i);
        const formattedDate = `${year}-${(month + 1).toString().padStart(2, '0')}-${i.toString().padStart(2, '0')}`;
        
        if (dayOfWeek === 0 || dayOfWeek === 6 || cellDate < today) {
            dayCell.classList.add('disabled');
        } else if (fullyBookedDates.includes(formattedDate)) {
            dayCell.classList.add('fully-booked');
            dayCell.onmouseenter = (e) => showDayTooltip('Day is fully booked, please choose another day.', e.target);
            dayCell.onmouseleave = hideDayTooltip;
        } else {
            dayCell.onclick = () => selectDay(i);
        }
        calendarDays.appendChild(dayCell);
    }

    const monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"];
    calendarMonth.innerHTML = `${monthNames[month]}<br><span style="color:rgb(17, 147, 87); font-size: 22px;">${year}</span>`;
}

function selectDay(day) {
    clearTimeSelection();
    
    const selectedDay = document.querySelector('.calendar-grid .selected');
    if (selectedDay) {
        selectedDay.classList.remove('selected');
    }
    
    const dayCells = calendarDays.children;
    for (let cell of dayCells) {
        if (cell.textContent == day && !cell.classList.contains('disabled') && !cell.classList.contains('fully-booked')) {
            cell.classList.add('selected');
            selectedDate = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            document.getElementById('requested_date').value = selectedDate;
            isDaySelected = true;
            hideTooltip();
            
            // Update time slots availability for the selected date
            updateTimeSlotsAvailability(selectedDate);
            break;
        }
    }
}

function updateTimeSlotsAvailability(date) {
    // Reset all time slots
    const timeSlots = document.querySelectorAll('.time-slot');
    timeSlots.forEach(slot => {
        slot.classList.remove('booked');
        slot.style.pointerEvents = 'auto';
        slot.style.cursor = 'pointer';
    });

    // If there are booked appointments for this date, disable those time slots
    if (bookedAppointments[date]) {
        const bookedTimes = bookedAppointments[date];
        timeSlots.forEach(slot => {
            const slotTime = slot.textContent.trim();
            if (bookedTimes.includes(slotTime)) {
                slot.classList.add('booked');
                slot.style.pointerEvents = 'none';
                slot.style.cursor = 'not-allowed';
            }
        });
    }
}

function selectTimeSlot(time) {
    if (!isDaySelected) {
        const timeSlots = document.querySelectorAll('.time-slot');
        let clickedElement = null;
        timeSlots.forEach(slot => {
            if (slot.textContent.trim() === time) {
                clickedElement = slot;
            }
        });
        
        if (clickedElement) {
            showTooltip('Please select a date first!', clickedElement);
        }
        return;
    }

    // Don't allow selection if the slot is booked
    const timeSlots = document.querySelectorAll('.time-slot');
    let clickedSlot = null;
    timeSlots.forEach(slot => {
        if (slot.textContent.trim() === time) {
            clickedSlot = slot;
        }
    });
    
    if (clickedSlot && clickedSlot.classList.contains('booked')) {
        showTooltip('This time slot is already booked!', clickedSlot);
        return;
    }

    // Remove selection from previously selected time slot
    if (selectedTimeSlot) {
        selectedTimeSlot.classList.remove('selected');
        selectedTimeSlot.style.backgroundColor = '';
        selectedTimeSlot.style.color = '';
    }

    // Select the new time slot
    if (clickedSlot) {
        clickedSlot.classList.add('selected');
        clickedSlot.style.backgroundColor = '#11AD64';
        clickedSlot.style.color = 'white';
        selectedTimeSlot = clickedSlot;

        document.getElementById('requested_time').value = time;
        hideTooltip();
    }
}

function clearTimeSelection() {
    if (selectedTimeSlot) {
        selectedTimeSlot.classList.remove('selected');
        selectedTimeSlot.style.backgroundColor = '';
        selectedTimeSlot.style.color = '';
        selectedTimeSlot = null;
    }
    document.getElementById('requested_time').value = '';
}

function showTooltip(message, element) {
    let tooltip = document.getElementById('timeSlotTooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'timeSlotTooltip';
        document.body.appendChild(tooltip);
    }

    tooltip.textContent = message;
    tooltip.style.display = 'block';

    const rect = element.getBoundingClientRect();
    tooltip.style.top = `${rect.top - 40}px`;
    tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
}

function showDayTooltip(message, element) {
    let tooltip = document.getElementById('dayTooltip');
    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.id = 'dayTooltip';
        document.body.appendChild(tooltip);
    }

    tooltip.textContent = message;
    tooltip.style.display = 'block';

    const rect = element.getBoundingClientRect();
    tooltip.style.top = `${rect.top - 40}px`;
    tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;
}

function hideTooltip() {
    const tooltip = document.getElementById('timeSlotTooltip');
    if (tooltip) {
        tooltip.style.display = 'none';
    }
}

function hideDayTooltip() {
    const tooltip = document.getElementById('dayTooltip');
    if (tooltip) {
        tooltip.style.display = 'none';
    }
}

function prevMonth() {
    if (currentMonth > 0) {
        currentMonth--;
    } else {
        currentMonth = 11;
        currentYear--;
    }
    if (currentYear >= today.getFullYear()) {
        generateCalendar(currentMonth, currentYear);
    }
}

function nextMonth() {
    if (currentMonth < 11) {
        currentMonth++;
    } else {
        currentMonth = 0;
        currentYear++;
    }
    generateCalendar(currentMonth, currentYear);
}

document.getElementById('appointmentForm').addEventListener('submit', function(event) {
    const dateSelected = document.getElementById('requested_date').value;
    const timeSelected = document.getElementById('requested_time').value;
    
    if (!dateSelected || !timeSelected) {
        alert('Please select both a date and a time for your appointment.');
        event.preventDefault(); 
    }
});

generateCalendar(currentMonth, currentYear);
</script>
</body>
</html>