<?php
require_once '../../font/font.php';
require_once('../../database/database.php');

session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

// Establish PDO Connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=gcc-2", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Appointment Stats
    $statuses = ['pending', 'approved', 'rescheduled', 'completed', 'cancelled'];
    $appointmentCountsCounseling = array_fill_keys($statuses, 0);
    $appointmentCountsAssessment = array_fill_keys($statuses, 0);
    $shiftingCounts = array_fill_keys($statuses, 0);
    $userRoleCounts = [];

    // Fetch Counseling Appointments
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE appointment_type = 'counseling' GROUP BY status");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $appointmentCountsCounseling[$row['status']] = $row['count'];
    }

    // Fetch Assessment Appointments
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE appointment_type = 'assessment' GROUP BY status");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $appointmentCountsAssessment[$row['status']] = $row['count'];
    }

    // Fetch Shifting Requests
    $stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM shifting GROUP BY status");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $shiftingCounts[$row['status']] = $row['count'];
    }

    // Fetch User Roles (excluding Admin)
    $stmt = $pdo->prepare("SELECT role, COUNT(*) as count FROM users WHERE role IN ('Staff','Director','Faculty','High School Student','Outside Client','College Student') GROUP BY role");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userRoleCounts[$row['role']] = $row['count'];
    }
     // Fetch Total Users (excluding Admin)
     $stmt = $pdo->prepare("SELECT COUNT(*) AS total_users FROM users WHERE role != 'Admin'");
     $stmt->execute();
     $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
 
     // Fetch Transactions for Today
     $stmt = $pdo->prepare("SELECT COUNT(*) AS total_today FROM appointments WHERE DATE(created_at) = CURDATE()");
     $stmt->execute();
     $appointmentsToday = $stmt->fetch(PDO::FETCH_ASSOC)['total_today'];
 
     $stmt = $pdo->prepare("SELECT COUNT(*) AS total_today FROM shifting WHERE DATE(created_at) = CURDATE()");
     $stmt->execute();
     $shiftingToday = $stmt->fetch(PDO::FETCH_ASSOC)['total_today'];
 
     $totalTransactionsToday = $appointmentsToday + $shiftingToday;
 
     // Fetch Transactions for This Week
     $stmt = $pdo->prepare("SELECT COUNT(*) AS total_week FROM appointments WHERE WEEK(created_at) = WEEK(CURDATE())");
     $stmt->execute();
     $appointmentsWeek = $stmt->fetch(PDO::FETCH_ASSOC)['total_week'];
 
     $stmt = $pdo->prepare("SELECT COUNT(*) AS total_week FROM shifting WHERE WEEK(created_at) = WEEK(CURDATE())");
     $stmt->execute();
     $shiftingWeek = $stmt->fetch(PDO::FETCH_ASSOC)['total_week'];
 
     $totalTransactionsWeek = $appointmentsWeek + $shiftingWeek;
 
     // Fetch Transactions for This Month
     $stmt = $pdo->prepare("SELECT COUNT(*) AS total_month FROM appointments WHERE MONTH(created_at) = MONTH(CURDATE())");
     $stmt->execute();
     $appointmentsMonth = $stmt->fetch(PDO::FETCH_ASSOC)['total_month'];
 
     $stmt = $pdo->prepare("SELECT COUNT(*) AS total_month FROM shifting WHERE MONTH(created_at) = MONTH(CURDATE())");
     $stmt->execute();
     $shiftingMonth = $stmt->fetch(PDO::FETCH_ASSOC)['total_month'];
 
     $totalTransactionsMonth = $appointmentsMonth + $shiftingMonth;
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
<link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GCC Admin</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/dashboard.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="smaller-dashboard-wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="dashboard.php" style=" background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-solid fa-home"></i> Dashboard</a>
            <a href="admin.php"><i class="fa-solid fa-users"></i> Manage Users</a>
            <a href="content.php"><i class="fa-solid fa-chart-bar"></i> Content </a>
            <!-- <a href="settings.php"><i class="fa-solid fa-cog"></i> Settings</a> -->
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
        <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
        <div class="sidebar-footer">
        <small>© 2025 WMSU </small>
        <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        <img src="/gcc/img/wmsu-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
    </div>
    </div>
    <div class="main-content">
<!-- Summary Cards with Icons -->
<div class="summary-cards">
    <div class="summary-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
            <h4>Total Users</h4>
        </div>
        <div class="value"><?php echo $totalUsers; ?></div>
        <div class="subtext">
            <i class="fas fa-info-circle"></i> All registered users
        </div>
    </div>
    
    <div class="summary-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
            <h4>Today's Transactions</h4>
        </div>
        <div class="value"><?php echo $totalTransactionsToday; ?></div>
        <div class="subtext">
            <i class="fas fa-clock"></i> New requests today
        </div>
    </div>
    
    <div class="summary-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-calendar-week"></i>
            </div>
            <h4>This Week</h4>
        </div>
        <div class="value"><?php echo $totalTransactionsWeek; ?></div>
        <div class="subtext">
            <i class="fas fa-chart-line"></i> Weekly activity
        </div>
    </div>
    
    <div class="summary-card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h4>This Month</h4>
        </div>
        <div class="value"><?php echo $totalTransactionsMonth; ?></div>
        <div class="subtext">
            <i class="fas fa-chart-bar"></i> Monthly overview
        </div>
    </div>
</div>

<!-- Dashboard Cards -->
<div class="dashboard-container">
            <div class="charts-container">
                <div class="card">
                    <h3>Counseling Appointments</h3>
                    <canvas id="counselingChart"></canvas>
                </div>
                <div class="card">
                    <h3>Assessment Appointments</h3>
                    <canvas id="assessmentChart"></canvas>
                </div>
                <div class="card">
                    <h3>Shifting Requests</h3>
                    <canvas id="shiftingChart"></canvas>
                </div>
                <div class="card">
                    <h3>User Distribution</h3>
                    <canvas id="userRoleChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
        function openModal(modalId) { document.getElementById(modalId).style.display = 'flex'; }
        function closeModal(modalId) { document.getElementById(modalId).style.display = 'none'; }

        var config = (data, label, type = 'bar') => ({
    type: type,
    data: {
        labels: ['Pending', 'Approved', 'Rescheduled', 'Completed', 'Cancelled'],
        datasets: [{
            label: label,
            data: data,
            backgroundColor: ['#ffcc00', '#00cc66', '#ff6600', '#0099cc', '#ff0033'],
            borderColor: '#ffffff',
            borderWidth: 1
        }]
    },options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: label,
                font: {
                    size: 18,
                    family: 'Poppins',
                    weight: 'bold'
                },
                color: '#ffffff',
                padding: {
                    top: 10,
                    bottom: 20
                }
            },
            legend: {
                display: false // Hide legend for bar charts
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                bodyFont: {
                    family: 'Poppins',
                    size: 14
                },
                titleFont: {
                    family: 'Poppins',
                    size: 16,
                    weight: 'bold'
                },
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return `${context.label}: ${context.raw}`;
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#ffffff',
                    font: {
                        family: 'Poppins',
                        size: 12
                    }
                },
                grid: {
                    display: false
                }
            },
            y: {
                ticks: {
                    color: '#ffffff',
                    font: {
                        family: 'Poppins',
                        size: 12
                    }
                },
                grid: {
                    color: 'rgba(255, 255, 255, 0.2)'
                }
            }
        }
    }
});

        
var pieConfig = {
    type: 'pie',
    data: {
        labels: <?php echo json_encode(array_keys($userRoleCounts)); ?>,
        datasets: [{
            label: 'User Roles',
            data: <?php echo json_encode(array_values($userRoleCounts)); ?>,
            backgroundColor: [
                '#FFE5EC', 
                '#ffa726', // Orange
                '#ef5350', // Red
                '#42a5f5',  // Blue
                '#FB6F92', // Pink
                '#66bb6a', // Green
                '#ab47bc'  // Purple
            ],
            borderColor: '#ffffff',
            borderWidth: 2
        }]
    },
    options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: '#ffffff',
                        font: {
                            family: 'Poppins',
                            size: 13,
                            weight: '500'
                        },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    bodyFont: {
                        family: 'Poppins',
                        size: 14,
                        weight: '500'
                    },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}`;
                        }
                    }
                }
            },
            cutout: '55%'
        }
    };

        new Chart(document.getElementById('counselingChart'), config([<?php echo implode(',', $appointmentCountsCounseling); ?>], 'Counseling Appointments'));
        new Chart(document.getElementById('assessmentChart'), config([<?php echo implode(',', $appointmentCountsAssessment); ?>], 'Assessment Appointments'));
        new Chart(document.getElementById('shiftingChart'), config([<?php echo implode(',', $shiftingCounts); ?>], 'Shifting Requests'));
        new Chart(document.getElementById('userRoleChart'), pieConfig);
    </script>

</body>
</html>