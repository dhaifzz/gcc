<?php
require_once '../../font/font.php';
require_once '../../database/database.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Director') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve']) || isset($_POST['reschedule'])) {
        $id = $_POST['id'];
        $status = isset($_POST['approve']) ? 'approved' : 'rescheduled';
        
        // Get director's info from session
        $director_id = $_SESSION['user_id'];
        $director_stmt = $pdo->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $director_stmt->execute([$director_id]);
        $director = $director_stmt->fetch(PDO::FETCH_ASSOC);
        $director_name = $director['first_name'] . ' ' . $director['last_name'];

        // Update shifting request
        $stmt = $pdo->prepare("UPDATE shifting SET status = :status, approved_by = :director_id WHERE id = :id");
        $stmt->execute([
            'status' => $status,
            'director_id' => $director_id,
            'id' => $id
        ]);

        if ($stmt->rowCount() > 0) {
            echo '<script>alert("Request ' . $status . ' by ' . htmlspecialchars($director_name) . '");</script>';
        } else {
            echo '<script>alert("Error processing request.");</script>';
        }
    }
}

// Fetch shifting requests with user info
$stmt = $pdo->query("SELECT s.id, s.user_id, s.first_name, s.middle_name, s.last_name, 
                     s.current_course, s.course_to_shift, s.status, s.approved_by,
                     u.email, u.contact_number as phone_number, u.wmsu_id,
                     d.first_name as approver_first, d.last_name as approver_last
                     FROM shifting s
                     JOIN users u ON s.user_id = u.id
                     LEFT JOIN users d ON s.approved_by = d.id
                     WHERE s.status = 'approved'");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shifting Exam Registration</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/director-shifting.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .nav {
            background-color: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .container {
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px;
        }
        .view-btn, .approve-btn, .reschedule-btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            color: white;
        }
        .view-btn {
            background-color: #3498db;
        }
        .approve-btn {
            background-color: #2ecc71;
        }
        .reschedule-btn {
            background-color: #e74c3c;
        }
        /* Add modal and other styles as needed */
    </style>
</head>
<body>
<div class="nav">
    <img src="/gcc/img/gcc-logo.png" alt="Logo" width="56" height="56">
    <a href="#" class="website">Guidance and Counseling Center</a>
</div>
<div class="container">
    <h2>Shifting Exam Registration</h2>
    <table id="examTable" class="display responsive nowrap" style="width:100%;">
        <thead>
            <tr>
                <th>School ID</th>
                <th>Name</th>
                <th>Current Course</th>
                <th>Desired Course</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['user_id']) ?></td>
                <td><?= htmlspecialchars($row['first_name'] . ' ' . 
                    (!empty($row['middle_name']) ? strtoupper(substr($row['middle_name'], 0, 1)) . '. ' : '') . 
                    $row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['current_course']) ?></td>
                <td><?= htmlspecialchars($row['course_to_shift']) ?></td>
                <td>
                    <button class='view-btn' onclick='viewRegistration(<?= $row['id'] ?>)'>
                        <i class='fas fa-eye'></i> View
                    </button>
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='id' value='<?= $row['id'] ?>'>
                        <button type='submit' name='approve' class='approve-btn'>
                            <i class='fas fa-check'></i> Approve
                        </button>
                    </form>
                    <form method='POST' style='display:inline;'>
                        <input type='hidden' name='id' value='<?= $row['id'] ?>'>
                        <button type='submit' name='reschedule' class='reschedule-btn'>
                            <i class='fas fa-times'></i> Reschedule
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="viewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('viewModal')">&times;</span>
        <div class="modal-header">Registration Details</div>
        <div id="modalDetails" class="modal-body">
        </div>
    </div>
</div>

<div id="fileViewerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal('fileViewerModal')">&times;</span>
        <div class="viewer-header">
            <h3 id="viewer-title">Document Viewer</h3>
            <div class="header-controls">
                <a id="download-btn" href="#" class="download-link">
                    <i class="fas fa-download"></i> Download
                </a>
            </div>
        </div>
        <div class="viewer-container">
            <img id="imageViewer">
            <iframe id="pdfViewer"></iframe>
            <pre id="textViewer"></pre>
            <div class="loading-spinner"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
// Define previewable file types
const PREVIEWABLE_TYPES = {
    images: ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'],
    pdf: ['pdf'],
    text: ['txt', 'csv', 'json', 'xml', 'html', 'css', 'js']
};

$(document).ready(function() {
    $('#examTable').DataTable({
        responsive: true,
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        }
    });
    initModals();
});

function initModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAllModals();
            }
        });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
}

function showModal(modalId) {
    closeAllModals();
    const modal = document.getElementById(modalId);
    modal.classList.add('show');
    document.body.classList.add('modal-open');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
    if (modalId === 'fileViewerModal') {
        clearFileViewer();
    }
}

function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('show');
    });
    document.body.classList.remove('modal-open');
    clearFileViewer();
}

function clearFileViewer() {
    const elements = {
        pdfViewer: document.getElementById('pdfViewer'),
        imageViewer: document.getElementById('imageViewer'),
        textViewer: document.getElementById('textViewer')
    };

    for (const [key, element] of Object.entries(elements)) {
        if (element) {
            if (key === 'textViewer') {
                element.textContent = '';
            } else {
                element.src = '';
            }
            element.style.display = 'none';
        }
    }
}

function showLoading(show) {
    const spinner = document.querySelector('.loading-spinner');
    if (spinner) {
        spinner.style.display = show ? 'block' : 'none';
    }
}

function viewRegistration(id) {
    showLoading(true);
    
    $.ajax({
        url: 'get-registration-details.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(data) {
            showLoading(false);
            
            if (data.error) {
                alert(data.error);
                return;
            }
            
            $('#modalDetails').html(`
                <div class="detail-group" style="grid-column: span 2;">
                    <div class="detail-label">Student Name</div>
                    <div class="detail-value">${data.first_name || ''} ${data.middle_name || ''} ${data.last_name || ''}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">School ID</div>
                    <div class="detail-value">${data.wmsu_id || 'N/A'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Current Course</div>
                    <div class="detail-value">${data.current_course || 'N/A'}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Desired Course</div>
                    <div class="detail-value">${data.course_to_shift || 'N/A'}</div>
                </div>
                <div class="detail-group" style="grid-column: span 2;">
                    <div class="detail-label">Reason for Shifting</div>
                    <div class="detail-value">${data.reason_to_shift || 'No reason provided'}</div>
                </div>
                <div class="attachments">
                    <div class="detail-label">Attachments</div>
                    <ul class="attachment-list">
                        <li>
                            <a class="attachment-link" onclick="viewAttachment('${data.picture}', 'Student Photo')">
                                <i class="fas fa-image attachment-icon"></i>Student Photo
                            </a>
                        </li>
                        <li>
                            <a class="attachment-link" onclick="viewAttachment('${data.grades}', 'Grades')">
                                <i class="fas fa-file-alt attachment-icon"></i>Grades
                            </a>
                        </li>
                        <li>
                            <a class="attachment-link" onclick="viewAttachment('${data.cor}', 'Certificate of Registration')">
                                <i class="fas fa-file attachment-icon"></i>Certificate of Registration
                            </a>
                        </li>
                        <li>
                            <a class="attachment-link" onclick="viewAttachment('${data.cet_result}', 'CET Result')">
                                <i class="fas fa-id-card attachment-icon"></i>CET Result
                            </a>
                        </li>
                    </ul>
                </div>
            `);
            
            showModal('viewModal');
        },
        error: function(xhr, status, error) {
            showLoading(false);
            alert('Could not fetch registration details: ' + error);
        }
    });
}

function viewAttachment(url, caption) {
    if (!url) {
        alert('No attachment available');
        return;
    }

    const normalizedUrl = url.replace(/(\/shared\/main\/uploads\/shifting)+/g, '/shared/main/uploads/shifting');
    
    showLoading(true);
    showModal('fileViewerModal');
    
    const pdfViewer = document.getElementById('pdfViewer');
    const imageViewer = document.getElementById('imageViewer');
    const textViewer = document.getElementById('textViewer');
    const downloadBtn = document.getElementById('download-btn');
    const viewerTitle = document.getElementById('viewer-title');

    viewerTitle.textContent = caption;
    downloadBtn.href = normalizedUrl;
    downloadBtn.download = caption || 'document';
    downloadBtn.style.display = 'block';

    clearFileViewer();

    checkFileExists(normalizedUrl).then(exists => {
        if (!exists) {
            alert('File not found on server. Please contact administrator.');
            showLoading(false);
            const downloadBtn = document.getElementById('download-btn');
            if (downloadBtn) {
                downloadBtn.style.display = 'none';
            }
            return;
        }

        const extension = normalizedUrl.split('.').pop().toLowerCase().split('?')[0];
        
        if (PREVIEWABLE_TYPES.images.includes(extension)) {
            imageViewer.onload = function() {
                showLoading(false);
                imageViewer.style.display = 'block';
            };
            imageViewer.src = normalizedUrl;
        } 
        else if (PREVIEWABLE_TYPES.pdf.includes(extension)) {
            pdfViewer.onload = function() {
                showLoading(false);
                pdfViewer.style.display = 'block';
            };
            pdfViewer.onerror = function() {
                alert('Error loading PDF. Please download the file instead.');
                showLoading(false);
            };
            pdfViewer.src = normalizedUrl + '#view=FitH';
        } 
        else if (PREVIEWABLE_TYPES.text.includes(extension)) {
            fetchAttachmentAsText(normalizedUrl);
        } 
        else {
            alert('This file format cannot be previewed. Please download the file instead.');
            showLoading(false);
        }
    }).catch(() => {
        alert('Error checking file. Please try again or contact administrator.');
        showLoading(false);
    });
}

function checkFileExists(url) {
    return fetch(url, { method: 'HEAD' })
        .then(response => {
            return response.ok;
        })
        .catch(() => {
            return false;
        });
}

function fetchAttachmentAsText(url) {
    const textViewer = document.getElementById('textViewer');
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Failed to load');
            return response.text();
        })
        .then(text => {
            textViewer.textContent = text;
            textViewer.style.display = 'block';
            showLoading(false);
        })
        .catch(() => {
            alert('This file format cannot be previewed. Please download the file instead.');
            showLoading(false);
        });
}
</script>

</body>
</html>