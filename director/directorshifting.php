<!DOCTYPE html>
<html>
<head>
    <title>GCC Admin - Shifting Exam</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://kit.fontawesome.com/3c9d5fece1.js" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        * {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .navbar {
            background-color: #11AD64;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .navbar img {
            width: 56px;
            height: 56px;
            margin-right: 15px;
        }
        
        .website {
            font-size: 20px;
            font-weight: 600;
            color: white;
            text-decoration: none;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-top: 20px;
            margin-bottom: 20px;
        }
        
        .header {
            background-color: #16633F;
            color: white;
            padding: 30px;
            text-align: center;
            font-size: 28px;
            font-weight: 500;
        }
        
        .content {
            padding: 30px;
        }
        
        .success {
            color: #2e7d32;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 4px;
        }
        
        .error {
            color: #c62828;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #ffebee;
            border: 1px solid #ffcdd2;
            border-radius: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .view-btn {
            background-color: #2196F3;
        }
        
        .approve-btn {
            background-color: #4CAF50;
        }
        
        .reject-btn {
            background-color: #f44336;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 25px;
            border-radius: 8px;
            width: 70%;
            max-width: 800px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            font-size: 24px;
            color: #aaa;
            cursor: pointer;
        }
        
        .modal-header {
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        
        .modal-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .detail-group {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 500;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }
        
        .detail-value {
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        
        .attachments {
            grid-column: span 2;
            margin-top: 15px;
        }
        
        .attachment-link {
            display: inline-block;
            margin-right: 15px;
            color: #2196F3;
            text-decoration: none;
        }
        
        .attachment-link:hover {
            text-decoration: underline;
        }
        
        footer {
            background-color: #DC143C;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="navbar">
       <img src="/gcc/img/gcc-logo.png" alt="GCC Logo" style="vertical-align: middle; width: 56px; height: 56px; margin-left: 10px;">
       <a class="website" href="#">Guidance and Counseling Center</a>
    </div>    
    <div class="container">
        <div class="header">
            Shifting Exam Registrations
        </div>
        
        <div class="content">
            <div class="success" id="successMessage">
                <i class="fas fa-check-circle"></i> Registration approved successfully.
            </div>

            <div class="error" id="errorMessage" style="display: none;">
                <i class="fas fa-exclamation-circle"></i> Error processing request.
            </div>
            
            <table id="registrationsTable">
                <thead>
                    <tr>
                        <th>School ID</th>
                        <th>Student Name</th>
                        <th>Current Course</th>
                        <th>Desired Course</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample data - replace with dynamic PHP code -->
                    <tr>
                        <td>2020-1234</td>
                        <td>Juan Dela Cruz</td>
                        <td>BS Computer Science</td>
                        <td>BS Information Technology</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn view-btn" onclick="viewRegistration(1)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn approve-btn">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn reject-btn">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>2021-5678</td>
                        <td>Maria Santos</td>
                        <td>BS Biology</td>
                        <td>BS Nursing</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn view-btn" onclick="viewRegistration(2)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn approve-btn">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn reject-btn">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>2019-9012</td>
                        <td>Pedro Reyes</td>
                        <td>BS Mathematics</td>
                        <td>BS Computer Engineering</td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn view-btn" onclick="viewRegistration(3)">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button class="btn approve-btn">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button class="btn reject-btn">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Registration Details Modal -->
    <div id="registrationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="modal-header">
                <h2>Registration Details</h2>
            </div>
            <div class="modal-body">
                <div class="detail-group">
                    <span class="detail-label">School ID</span>
                    <div class="detail-value">2020-1234</div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Student Name</span>
                    <div class="detail-value">Juan Dela Cruz</div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Sex</span>
                    <div class="detail-value">Male</div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">College</span>
                    <div class="detail-value">College of Science and Mathematics</div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Current Course</span>
                    <div class="detail-value">BS Computer Science</div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Desired Course</span>
                    <div class="detail-value">BS Information Technology</div>
                </div>
                <div class="detail-group" style="grid-column: span 2;">
                    <span class="detail-label">Reason for Shifting</span>
                    <div class="detail-value">I have discovered a stronger passion for the practical applications of IT in business environments and believe this program better aligns with my career goals.</div>
                </div>
                <div class="attachments">
                    <h3>Attachments</h3>
                    <a href="#" class="attachment-link"><i class="fas fa-image"></i> Photo</a>
                    <a href="#" class="attachment-link"><i class="fas fa-file-alt"></i> Grades</a>
                    <a href="#" class="attachment-link"><i class="fas fa-file-contract"></i> CET Result</a>
                    <a href="#" class="attachment-link"><i class="fas fa-id-card"></i> School ID</a>
                </div>
            </div>
        </div>
    </div>
    
    <footer>
        <div>Copyright © 2025 Western Mindanao State University. All rights reserved.</div>
        <div><img src="/gcc/img/wmsu-logo.png" alt="Logo" style="height: 40px;"></div>
    </footer>

    <script>
        $(document).ready(function() {
            $('#registrationsTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search registrations...",
                }
            });

            setTimeout(function() {
                $('#successMessage, #errorMessage').fadeOut('slow');
            }, 5000);
        });
        
        function viewRegistration(id) {

            document.getElementById('registrationModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('registrationModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('registrationModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>