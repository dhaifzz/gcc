<?php
require_once '../../font/font.php';
require_once '../../database/database.php';
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'Staff') {
    header("Location: ../../auth/sign-in.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $id = $_POST['id'];
        $status = 'approved';
    } elseif (isset($_POST['reschedule'])) {
        $id = $_POST['id'];
        $status = 'rescheduled';
    }

    if (isset($id) && isset($status)) {
        $stmt = $pdo->prepare("UPDATE shifting SET status = :status WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);

        if ($stmt->rowCount() > 0) {
            echo '<script>alert("Request ' . $status . ' successfully.");</script>';
        } else {
            echo '<script>alert("Error processing request.");</script>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shifting Exam Registration</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/staff-shifting.css">
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
        <?php
        $stmt = $pdo->query("SELECT id, user_id, first_name, middle_name, last_name, current_course, course_to_shift FROM shifting WHERE status = 'pending'");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>".htmlspecialchars($row['user_id'])."</td>";
            echo "<td>".htmlspecialchars($row['first_name'].' '.(isset($row['middle_name']) ? strtoupper($row['middle_name'][0]).'. ' : '').$row['last_name'])."</td>";
            echo "<td>".htmlspecialchars($row['current_course'])."</td>";
            echo "<td>".htmlspecialchars($row['course_to_shift'])."</td>";
            echo "<td>
                <button class='view-btn' onclick='viewRegistration({$row['id']})'><i class='fas fa-eye'></i> View</button>
                <form method='POST' style='display:inline;'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <button type='submit' name='approve' class='approve-btn'><i class='fas fa-check'></i> Approve</button>
                </form>
                <form method='POST' style='display:inline;'>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <button type='submit' name='reschedule' class='reschedule-btn'><i class='fas fa-times'></i> Reschedule</button>
                </form>
            </td>";
            echo "</tr>";
        }
        ?>
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
            <iframe id="pdfViewer"></iframe>
            <img id="imageViewer">
            <pre id="textViewer"></pre>
            <div id="unsupportedViewer">
                <i class="fas fa-file-alt"></i>
                <p>This file format cannot be previewed</p>
            </div>
            <div class="loading-spinner"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
$(document).ready(function() {
    $('#examTable').DataTable({
        responsive: true
    });

    initModals();
});

const PREVIEWABLE_TYPES = {
    images: ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'],
    pdf: ['pdf'],
    text: ['txt', 'csv', 'json', 'xml']
};

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
    document.getElementById('pdfViewer').src = '';
    document.getElementById('pdfViewer').style.display = 'none';
    document.getElementById('imageViewer').src = '';
    document.getElementById('imageViewer').style.display = 'none';
    document.getElementById('textViewer').textContent = '';
    document.getElementById('textViewer').style.display = 'none';
    document.getElementById('unsupportedViewer').style.display = 'none';
}

function viewRegistration(id) {
    showLoading(true);
    
    $.ajax({
        url: 'get_registration_details.php',
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
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
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
                        </ul>
                        <ul class="attachment-list">
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
                </div>
            `);
            
            showModal('viewModal');
        },
        error: function() {
            showLoading(false);
            alert('Could not fetch registration details.');
        }
    });
}

function viewAttachment(url, caption) {
    if (!url) {
        alert('No attachment available');
        return;
    }

    showLoading(true);
    showModal('fileViewerModal');
    
    const pdfViewer = document.getElementById('pdfViewer');
    const imageViewer = document.getElementById('imageViewer');
    const textViewer = document.getElementById('textViewer');
    const unsupportedViewer = document.getElementById('unsupportedViewer');
    const downloadBtn = document.getElementById('download-btn');
    const viewerTitle = document.getElementById('viewer-title');

    viewerTitle.textContent = caption;
    downloadBtn.href = url;
    downloadBtn.download = caption;

    clearFileViewer();

    const extension = url.split('.').pop().toLowerCase().split('?')[0];
    
    if (PREVIEWABLE_TYPES.images.includes(extension)) {
        imageViewer.onload = function() {
            showLoading(false);
            imageViewer.style.display = 'block';
        };
        imageViewer.onerror = function() {
            showUnsupported();
        };
        imageViewer.src = url;
    } else if (PREVIEWABLE_TYPES.pdf.includes(extension)) {
        pdfViewer.onload = function() {
            showLoading(false);
            pdfViewer.style.display = 'block';
        };
        pdfViewer.onerror = function() {
            showUnsupported();
        };
        pdfViewer.src = url + '#view=FitH';
    } else if (PREVIEWABLE_TYPES.text.includes(extension)) {
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
                showUnsupported();
            });
    } else {
        showUnsupported();
    }
}

function showUnsupported() {
    showLoading(false);
    document.getElementById('unsupportedViewer').style.display = 'block';
}

function showLoading(show) {
    const spinner = document.querySelector('.loading-spinner');
    if (show) {
        spinner.style.display = 'block';
    } else {
        spinner.style.display = 'none';
    }
}
</script>

</body>
</html>
