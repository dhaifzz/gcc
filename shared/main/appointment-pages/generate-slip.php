<?php
require_once __DIR__ . '/../../../database/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Authentication required']);
    exit();
}

// Get appointment ID from request
$appointment_id = $_GET['appointment_id'] ?? null;
$format = $_GET['format'] ?? 'html'; // html or pdf

if (!$appointment_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Appointment ID is required']);
    exit();
}

try {
    // Get appointment details with user and staff information
    $stmt = $pdo->prepare("
        SELECT 
            a.appointment_id,
            a.appointment_type,
            a.requested_date,
            a.requested_time,
            a.status,
            u.first_name AS client_fname,
            u.middle_name AS client_mname,
            u.last_name AS client_lname,
            u.role AS client_role,
            s.first_name AS staff_fname,
            s.last_name AS staff_lname
        FROM appointments a
        LEFT JOIN users u ON a.client_id = u.id
        LEFT JOIN users s ON a.Staff_id = s.id
        WHERE a.appointment_id = :appointment_id
    ");
    
    $stmt->execute(['appointment_id' => $appointment_id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        throw new Exception('Appointment not found');
    }

    // Format the date
    $formatted_date = date('F j, Y', strtotime($data['requested_date']));

    // Format client name
    $client_name = $data['client_fname'];
    if (!empty($data['client_mname'])) {
        $client_name .= ' ' . $data['client_mname'];
    }
    $client_name .= ' ' . $data['client_lname'];

    // Format staff name if available
    $approved_by = '';
    if ($data['staff_fname'] && $data['staff_lname']) {
        $approved_by = $data['staff_fname'] . ' ' . $data['staff_lname'];
    }

    // Generate the HTML content
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <link rel="icon" type="image/png" sizes="96x96" href="/gcc/img/favicon.ico">
        <link rel="icon" type="image/x-icon" href="/gcc/img/favicon.ico">
        <title>GCC Website</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                border: 2px solid #16633F;
                border-radius: 10px;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 2px solid #16633F;
            }
            .logo {
                max-width: 100px;
                margin-bottom: 10px;
            }
            .title {
                color:rgb(9, 100, 59);
                font-size: 24px;
                font-weight: bold;
                margin: 10px 0;
            }
            .subtitle {
                color: #666;
                font-size: 16px;
            }
            .content {
                margin: 20px 0;
            }
            .message {
                font-size: 18px;
                color: #333;
                margin: 20px 0;
                padding: 15px;
                background-color: #f9f9f9;
                border-radius: 5px;
            }
            .details {
                margin: 20px 0;
            }
            .detail-row {
                display: flex;
                margin: 10px 0;
            }
            .detail-label {
                font-weight: bold;
                width: 150px;
                color: #16633F;
            }
            .detail-value {
                flex: 1;
            }
            .footer {
                margin-top: 40px;
                text-align: center;
                font-size: 14px;
                color: #666;
            }
            .note {
                margin-top: 20px;
                padding: 15px;
                background-color:rgb(205, 255, 218);
                border: 1px solid rgb(105, 233, 143);
                border-radius: 5px;
                color:rgb(10, 105, 54);
            }
            @media print {
                body {
                    padding: 0;
                }
                .no-print {
                    display: none !important;
                }
            }
        </style>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    </head>
    <body>
        <div class="container">
            <div class="header">
                <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" class="logo">
                <img src="/gcc/img/wmsu-logo.png" alt="WMSU Logo" class="logo">
                <div class="title">Guidance and Counseling Center</div>
                <div class="subtitle">Western Mindanao State University</div>
            </div>
            
            <div class="content">
                <div class="message">
                     Hi <strong>' . htmlspecialchars($client_name) . '</strong>! 
                     Please proceed to the GCC Office at WMSU for your scheduled appointment at 
                     <strong>' . htmlspecialchars($data['requested_time']) . '</strong> on 
                     <strong>' . htmlspecialchars($formatted_date) . '</strong>.
                 </div>
                
                <div class="details">
                    <div class="detail-row">
                        <div class="detail-label">Appointment ID:</div>
                        <div class="detail-value">' . htmlspecialchars($data['appointment_id']) . '</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Appointment Type:</div>
                        <div class="detail-value">' . htmlspecialchars(ucfirst($data['appointment_type'])) . '</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Client Role:</div>
                        <div class="detail-value">' . htmlspecialchars($data['client_role']) . '</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status:</div>
                        <div class="detail-value">' . htmlspecialchars(ucfirst($data['status'])) . '</div>
                    </div>
                    ' . ($approved_by ? '
                    <div class="detail-row">
                        <div class="detail-label">Approved By:</div>
                        <div class="detail-value">' . htmlspecialchars($approved_by) . '</div>
                    </div>
                    ' : '') . '
                </div>

                <div class="note">
                    <i class="fas fa-info-circle"></i> Please arrive 5-10 minutes before your scheduled time. Don\'t forget to bring this slip with you.
                </div>
            </div>
            
            <div class="footer">
                This is an official appointment slip from the WMSU Guidance and Counseling Center.
                <br>Please keep this slip and present it during your appointment.
            </div>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 20px;">
            <button onclick="window.print()" style="
                padding: 10px 20px;
                background-color: #16633F;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            ">
                <i class="fas fa-print"></i> Print Slip
            </button>
        </div>

        <script>
            // Add event listener for print dialog close
            if (window.matchMedia) {
                window.matchMedia("print").addListener(function(media) {
                    if (!media.matches) {
                        // Print dialog was closed
                        document.querySelector(".no-print button").focus();
                    }
                });
            }
        </script>
    </body>
    </html>';

    if ($format === 'pdf') {
        // If you want to implement PDF generation, you can do it here
        header('Content-Type: application/json');
        echo json_encode(['error' => 'PDF format not yet implemented']);
        exit();
    } else {
        // Output as HTML
        echo $html;
    }

} catch (Exception $e) {
    if ($format === 'html') {
        echo '<div style="color: red; padding: 20px;">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?> 