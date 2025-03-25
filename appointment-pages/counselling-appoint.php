<?php
require_once '../../../font/font.php';
require_once '../../../database/database.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Student') {
    header("Location: ../../../auth/sign-in.php");
    exit();
}

// Handle form submission
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'book') {
    $client_id = $_SESSION['user_id'];
    $appointment_type = 'counseling'; 
    $requested_date = $_POST['requested_date'];
    $requested_time = $_POST['requested_time']; 
    $status = 'pending';

    try {
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
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }

    $stmt = null;
}

// Close the database connection
$pdo = null;
?>
<!DOCTYPE html>
<html>
<head>
    <title>GCC Website</title>
    <link rel="stylesheet" type="text/css" href="../../css/appoint-assess.css">
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
            color: red;
            margin-top: 10px;
        }
        .success {
            color: green;
            margin-top: 10px;
        }
    </style>
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="navbar">
       <img src="../../../../gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="<?php
    switch ($_SESSION['role']) {
        case 'Student':
            echo '../../../../client/inside/student/student.php';
            break;
        default:
           echo '../../../../../auth/sign-in.php';  
    }
    ?>">Guidance and Counseling Center</a>
       <div class="burger-icon" style="float: right; margin: 10px;">
           <i class="fas fa-bars" style="font-size: 35px;"></i>
       </div>
    </div>    
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
                            <div class="time-slot" onclick="selectTimeSlot('8am-9am')">8am - 9am</div>
                            <div class="time-slot" onclick="selectTimeSlot('9am-10am')">9am - 10am</div>
                            <div class="time-slot" onclick="selectTimeSlot('10am-11am')">10am - 11am</div>
                        </div>
                        <div class="time-slot-section">
                            <h3>Schedule for Afternoon</h3>
                            <div class="time-slot" onclick="selectTimeSlot('2pm-3pm')">2pm - 3pm</div>
                            <div class="time-slot" onclick="selectTimeSlot('3pm-4pm')">3pm - 4pm</div>
                            <div class="time-slot" onclick="selectTimeSlot('4pm-5pm')">4pm - 5pm</div>
                        </div>
                    </div>
                </div>
                <button type="submit" style="background-color: #11AD64; color: white; border: none; width: 100%; padding: 15px 20px; border-radius: 5px; cursor: pointer; font-size: 20px; font-weight: 600; transition: background-color 0.3s, transform 0.3s;">
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
            <div style="margin-left: 20px;">Copyright Â© 2025 Western Mindanao State University. All rights reserved.</div>
            <div style="margin-right: 20px;"><img src="../../../../gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
         </footer>
  </div>

<script>
    const calendarDays = document.getElementById('calendarDays');
    const calendarMonth = document.getElementById('calendarMonth');
    let currentMonth = new Date().getMonth();
    let currentYear = new Date().getFullYear();
    const today = new Date();

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
            if (dayOfWeek === 0 || dayOfWeek === 6 || cellDate < today) {
                dayCell.classList.add('disabled');
            } else {
                dayCell.onclick = () => selectDay(i);
            }
            calendarDays.appendChild(dayCell);
        }

        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        calendarMonth.innerHTML = `${monthNames[month]}<br><span style="color:rgb(17, 147, 87); font-size: 22px;">${year}</span>`;
    }

    function selectDay(day) {
        const selectedDay = document.querySelector('.calendar-grid .selected');
        if (selectedDay) {
            selectedDay.classList.remove('selected');
        }
        const dayCells = calendarDays.children;
        for (let cell of dayCells) {
            if (cell.textContent == day && !cell.classList.contains('disabled')) {
                cell.classList.add('selected');
                document.getElementById('requested_date').value = `${currentYear}-${(currentMonth + 1).toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
                break;
            }
        }
    }

    function selectTimeSlot(timeSlot) {
        const selectedTimeSlot = document.querySelector('.time-slot.selected');
        if (selectedTimeSlot) {
            selectedTimeSlot.classList.remove('selected');
        }
        const timeSlots = document.querySelectorAll('.time-slot');
        timeSlots.forEach(slot => {
            if (slot.textContent === timeSlot) {
                slot.classList.add('selected');
            }
        });
        document.getElementById('requested_time').value = timeSlot;
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

    generateCalendar(currentMonth, currentYear);

    // Form submission check
    document.getElementById('appointmentForm').addEventListener('submit', function(event) {
        const dateSelected = document.getElementById('requested_date').value;
        const timeSelected = document.getElementById('requested_time').value;
        if (!dateSelected || !timeSelected) {
            alert('Please select both a date and a time for your appointment.');
            event.preventDefault(); // Prevent form submission
        }
    });
</script>
</body>
</html>