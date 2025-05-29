<?php
require_once '../../font/font.php';
require_once('../../database/database.php');

session_start();

$message = '';
$error = '';
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

// Query for client types distribution
$clientTypeQuery = "
    SELECT role as client_type, COUNT(*) as count
    FROM users
    WHERE role IN ('Faculty', 'High School Student', 'Outside Client', 'College Student')
    GROUP BY role
    ORDER BY count DESC";

// Query for shifting course distribution
$shiftingQuery = "
    WITH RankedCourses AS (
        SELECT 
            current_course,
            COUNT(*) as count,
            ROW_NUMBER() OVER (ORDER BY COUNT(*) DESC) as rn
        FROM shifting 
        WHERE current_course != 'None'
        GROUP BY current_course
    )
    SELECT 
        CASE 
            WHEN rn <= 5 THEN current_course
            ELSE 'Others'
        END as current_course,
        SUM(count) as count
    FROM RankedCourses
    GROUP BY 
        CASE 
            WHEN rn <= 5 THEN current_course
            ELSE 'Others'
        END
    ORDER BY 
        CASE 
            WHEN current_course = 'Others' THEN 2
            ELSE 1
        END,
        count DESC";

// Query for appointment types
$appointmentQuery = "
    SELECT appointment_type, COUNT(*) as count 
    FROM appointments 
    WHERE appointment_type IN ('counseling', 'assessment')
    GROUP BY appointment_type";

// Add new query for total shifting requests
$shiftingTotalQuery = "
    SELECT 
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'evaluated' THEN 1 ELSE 0 END) as evaluated
    FROM shifting";

try {
    // Execute queries
    $clientTypeStmt = $pdo->query($clientTypeQuery);
    $clientTypeData = $clientTypeStmt->fetchAll(PDO::FETCH_ASSOC);

    $shiftingStmt = $pdo->query($shiftingQuery);
    $shiftingData = $shiftingStmt->fetchAll(PDO::FETCH_ASSOC);

    $appointmentStmt = $pdo->query($appointmentQuery);
    $appointmentData = $appointmentStmt->fetchAll(PDO::FETCH_ASSOC);

    // Execute new shifting total query
    $shiftingTotalStmt = $pdo->query($shiftingTotalQuery);
    $shiftingTotalData = $shiftingTotalStmt->fetch(PDO::FETCH_ASSOC);

    // Prepare data for JSON encoding
    $clientTypeLabels = [];
    $clientTypeValues = [];
    foreach ($clientTypeData as $row) {
        $clientTypeLabels[] = $row['client_type'];
        $clientTypeValues[] = intval($row['count']);
    }

    $shiftingLabels = [];
    $shiftingValues = [];
    foreach ($shiftingData as $row) {
        $shiftingLabels[] = $row['current_course'];
        $shiftingValues[] = intval($row['count']);
    }

    $appointmentLabels = [];
    $appointmentValues = [];
    foreach ($appointmentData as $row) {
        $appointmentLabels[] = ucfirst($row['appointment_type']);
        $appointmentValues[] = intval($row['count']);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
    <title>GCC Staff Dashboard</title>
    <?php includeGoogleFonts(); ?>
    <link rel="stylesheet" type="text/css" href="css/staff-dashboard.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h3 style="text-decoration: underline; text-decoration-color: red; text-underline-offset: 0.3125rem;">GCC <?php echo $_SESSION['role']; ?></h3>
        </div>
        <div class="menu-items">
            <a href="staff-dashboard.php" style="background-color: rgb(255, 255, 255); color: #236641;"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="staff-counseling.php"><i class="fa-regular fa-calendar-days"></i>Counseling Table</a>
            <a href="staff-assessment.php"><i class="fa-regular fa-calendar"></i> Assessment Table</a>           
            <a href="staff-shifting.php"><i class="fa-solid fa-envelope"></i> Shifting Table</a>
            <a href="staff-history.php"><i class="fa fa-history"></i> History</a>
            <a href="../../auth/sign-out.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="sidebar-footer">
            <small>© 2025 WMSU </small>
            <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
            <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" style="vertical-align: middle; width: 2rem; height: 2rem;">
        </div>
    </div>

    <div class="main-content">
        <div class="dashboard-container">
            <div class="dashboard-header">
                <h1>Dashboard Overview</h1>
            </div>

            <div class="charts-grid">
                <!-- Client Types Distribution -->
                <div class="chart-container">
                    <h2>Client</h2>
                    <div class="chart-wrapper">
                        <canvas id="clientTypeChart"></canvas>
                    </div>
                </div>

                <!-- Shifting Course Distribution -->
                <div class="chart-container">
                    <h2>Top Shifting Courses</h2>
                    <div class="chart-wrapper">
                        <canvas id="shiftingChart"></canvas>
                    </div>
                </div>

                <!-- Appointment Types -->
                <div class="chart-container">
                    <h2>Appointment Types </h2>
                    <div class="chart-wrapper">
                        <canvas id="appointmentChart"></canvas>
                    </div>
                </div>

                <!-- Shifting Requests Metrics -->
                <div class="chart-container">
                    <h2>Shifting Request</h2>
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <h3>Total Requests</h3>
                            <div class="number"><?php echo $shiftingTotalData['total_requests']; ?></div>
                        </div>
                        <div class="metric-card">
                            <h3>Pending</h3>
                            <div class="number"><?php echo $shiftingTotalData['pending']; ?></div>
                        </div>
                        <div class="metric-card">
                            <h3>Approved</h3>
                            <div class="number"><?php echo $shiftingTotalData['approved']; ?></div>
                        </div>
                        <div class="metric-card">
                            <h3>Evaluated</h3>
                            <div class="number"><?php echo $shiftingTotalData['evaluated']; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Updated color schemes with more vibrant colors
        const colors = [
            '#4CAF50', // Green
            '#2196F3', // Blue
            '#FFC107', // Yellow
            '#9C27B0', // Purple
            '#FF5722', // Deep Orange
            '#00BCD4', // Cyan
            '#3F51B5', // Indigo
            '#E91E63', // Pink
            '#FF9800', // Orange
            '#009688'  // Teal
        ];

        // Helper function to calculate percentages
        function calculatePercentages(data) {
            const total = data.reduce((a, b) => a + b, 0);
            return data.map(value => ((value / total) * 100).toFixed(1));
        }

        // Client Types Chart
        new Chart(document.getElementById('clientTypeChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($clientTypeLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($clientTypeValues); ?>,
                    backgroundColor: colors,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                const percentages = calculatePercentages(data.datasets[0].data);
                                return data.labels.map((label, i) => ({
                                    text: `${label} (${percentages[i]}%)`,
                                    fillStyle: colors[i],
                                    hidden: false,
                                    index: i
                                }));
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Distribution of Client Types',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Shifting Courses Chart
        new Chart(document.getElementById('shiftingChart'), {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($shiftingLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($shiftingValues); ?>,
                    backgroundColor: colors,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            generateLabels: function(chart) {
                                const data = chart.data;
                                const percentages = calculatePercentages(data.datasets[0].data);
                                return data.labels.map((label, i) => ({
                                    text: `${label} (${percentages[i]}%)`,
                                    fillStyle: colors[i],
                                    hidden: false,
                                    index: i
                                }));
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Top 5 Shifting Courses',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Appointment Types Chart
        new Chart(document.getElementById('appointmentChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($appointmentLabels); ?>,
                datasets: [{
                    label: 'Number of Appointments',
                    data: <?php echo json_encode($appointmentValues); ?>,
                    backgroundColor: [colors[0], colors[1]],
                    borderColor: [colors[0], colors[1]],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Counseling vs Assessment Appointments',
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Dropdown menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdownBtn = document.querySelector('.dropdown-btn');
            const dropdown = document.querySelector('.dropdown');
            
            dropdownBtn.addEventListener('click', function() {
                dropdown.classList.toggle('active');
            });
        });
    </script>

    <style>
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            padding: 1rem;
        }

        .metric-card {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .metric-card h3 {
            color: #236641;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .metric-card .number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
    </style>
</body>
</html>


