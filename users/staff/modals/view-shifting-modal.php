<?php
// View Modal for Shifting Details
?>
<!-- Main Shifting Details Modal -->
<div id="viewModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-info-circle"></i> Shifting Request Details</h2>
            <span class="close" onclick="closeModal('viewModal')">&times;</span>
        </div>
        <div id="modalDetails" class="modal-body">
            <!-- Content will be dynamically loaded here -->
            <div id="loadingIndicator" class="loading-container">
                <div class="loading"></div>
                <p>Loading details...</p>
            </div>
        </div>
    </div>
</div>

<!-- Image Viewer Modal -->
<div id="imageViewerModal" class="modal">
    <div class="modal-content image-viewer-content">
        <div class="modal-header">
            <h2><i class="fas fa-image"></i> <span id="imageTitle">Image Viewer</span></h2>
            <span class="close" onclick="closeModal('imageViewerModal')">&times;</span>
        </div>
        <div class="image-viewer-body">
            <div id="imageLoadingIndicator" class="loading-container">
                <div class="loading"></div>
                <p>Loading image...</p>
            </div>
            <img id="viewedImage" src="" alt="Preview" style="display: none;">
            <div id="imageError" class="error-message" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <p>Failed to load image. Please try again.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="downloadFile()" class="action-button download-btn">
                <i class="fas fa-download"></i> Download
            </button>
            <button onclick="rotateImage(90)" class="action-button">
                <i class="fas fa-redo"></i> Rotate
            </button>
            <button onclick="zoomImage(1.2)" class="action-button">
                <i class="fas fa-search-plus"></i> Zoom In
            </button>
            <button onclick="zoomImage(0.8)" class="action-button">
                <i class="fas fa-search-minus"></i> Zoom Out
            </button>
        </div>
    </div>
</div>

<!-- PDF Viewer Modal -->
<div id="pdfViewerModal" class="modal">
    <div class="modal-content pdf-viewer-content">
        <div class="modal-header">
            <h2><i class="fas fa-file-pdf"></i> <span id="pdfTitle">Document Viewer</span></h2>
            <span class="close" onclick="closeModal('pdfViewerModal')">&times;</span>
        </div>
        <div class="pdf-viewer-body">
            <div id="pdfLoadingIndicator" class="loading-container">
                <div class="loading"></div>
                <p>Loading document...</p>
            </div>
            <iframe id="pdfViewer" src="" frameborder="0" style="display: none;"></iframe>
            <div id="pdfError" class="error-message" style="display: none;">
                <i class="fas fa-exclamation-circle"></i>
                <p>Failed to load document. Please try again or download to view.</p>
                <button onclick="downloadFile()" class="action-button">
                    <i class="fas fa-download"></i> Download Document
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    overflow-y: auto;
    padding: 20px;
}

.modal-content {
    background-color: #fff;
    margin: 2% auto;
    padding: 0;
    border-radius: 12px;
    width: 95%;
    max-width: 1400px;
    position: relative;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.modal-header {
    background-color: #2B6B48;
    color: white;
    padding: 20px 30px;
    border-radius: 12px 12px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close {
    color: white;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

.close:hover {
    opacity: 0.7;
}

.modal-body {
    padding: 30px;
    max-height: 80vh;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.detail-section, .files-section {
    background: #ffffff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
    width: 100%;
    box-sizing: border-box;
}

.detail-section {
    display: flex;
    flex-direction: row;
    gap: 20px;
}

.detail-group {
    flex: 1;
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.detail-label {
    font-weight: 600;
    color: #2B6B48;
    margin-bottom: 8px;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    color: #333;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    font-size: 1.1rem;
    line-height: 1.5;
}

.files-section {
    width: 100%;
    background: #ffffff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
}

.files-section .detail-label {
    margin-bottom: 20px;
    font-size: 1.1rem;
}

.file-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.file-item {
    background-color: #ffffff;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.file-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.file-icon {
    font-size: 32px;
    color: #2B6B48;
    margin-bottom: 15px;
}

.file-name {
    font-weight: 500;
    color: #495057;
    margin-bottom: 12px;
}

.file-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background-color: #2B6B48;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
}

.file-link:hover {
    background-color: #235c3d;
    transform: translateY(-1px);
}

.file-link i {
    font-size: 0.9rem;
}

.reason-section {
    grid-column: span 2;
    background: #ffffff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid #e9ecef;
}

.reason-text {
    white-space: pre-wrap;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    line-height: 1.6;
    font-size: 1rem;
}

.text-muted {
    color: #6c757d;
    font-style: italic;
}

/* Scrollbar Styling */
.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #2B6B48;
    border-radius: 4px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #235c3d;
}

/* Image Viewer Styles */
.image-viewer-content {
    width: 90%;
    max-width: 1200px;
    height: 90vh;
    padding: 0;
}

.image-viewer-body {
    height: calc(90vh - 70px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    padding: 20px;
    overflow: auto;
}

#viewedImage {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* PDF Viewer Styles */
.pdf-viewer-content {
    width: 90%;
    max-width: 1200px;
    height: 90vh;
    padding: 0;
}

.pdf-viewer-body {
    height: calc(90vh - 70px);
    background: #f8f9fa;
}

#pdfViewer {
    width: 100%;
    height: 100%;
    border: none;
}

/* File type specific styles */
.file-preview {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 10px;
    border: 1px solid #dee2e6;
}

.preview-container {
    margin-bottom: 10px;
    position: relative;
    overflow: hidden;
    border-radius: 4px;
}

.preview-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
}

.preview-container:hover .preview-overlay {
    opacity: 1;
}

.preview-button {
    background: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.2s;
}

.preview-button:hover {
    transform: scale(1.05);
}

/* Loading animation */
.loading {
    display: inline-block;
    width: 50px;
    height: 50px;
    border: 3px solid rgba(255,255,255,.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Add to your existing styles */
.modal-body {
    position: relative;
}

#loadingIndicator {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: none;
}

/* Loading Container Styles */
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #2B6B48;
}

.loading-container p {
    margin-top: 15px;
    font-size: 1.1rem;
    color: #495057;
}

/* Error Message Styles */
.error-message {
    text-align: center;
    color: #dc3545;
    padding: 20px;
}

.error-message i {
    font-size: 3rem;
    margin-bottom: 15px;
}

.error-message p {
    font-size: 1.1rem;
    margin-bottom: 15px;
}

/* Modal Footer Styles */
.modal-footer {
    padding: 15px;
    background-color: #f8f9fa;
    border-top: 1px solid #dee2e6;
    display: flex;
    justify-content: center;
    gap: 10px;
}

.action-button {
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    background-color: #2B6B48;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.action-button:hover {
    background-color: #235c3d;
    transform: translateY(-1px);
}

.action-button.download-btn {
    background-color: #0056b3;
}

.action-button.download-btn:hover {
    background-color: #004494;
}

/* Image Controls */
#viewedImage {
    transition: transform 0.3s ease;
}

/* Zoom Controls */
.zoom-controls {
    position: absolute;
    bottom: 20px;
    right: 20px;
    display: flex;
    gap: 10px;
}
</style>

<script>
let currentRotation = 0;
let currentZoom = 1;
let currentFileUrl = '';

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = "none";
    
    // Reset states
    if (modalId === 'pdfViewerModal') {
        document.getElementById('pdfViewer').src = '';
        document.getElementById('pdfError').style.display = 'none';
        document.getElementById('pdfLoadingIndicator').style.display = 'flex';
    } else if (modalId === 'imageViewerModal') {
        document.getElementById('viewedImage').src = '';
        document.getElementById('imageError').style.display = 'none';
        document.getElementById('imageLoadingIndicator').style.display = 'flex';
        currentRotation = 0;
        currentZoom = 1;
        updateImageTransform();
    }
}

function viewImage(url, title) {
    const modal = document.getElementById('imageViewerModal');
    const img = document.getElementById('viewedImage');
    const titleSpan = document.getElementById('imageTitle');
    const loadingIndicator = document.getElementById('imageLoadingIndicator');
    const errorMessage = document.getElementById('imageError');
    
    // Reset states
    currentRotation = 0;
    currentZoom = 1;
    currentFileUrl = url;
    updateImageTransform();
    
    // Show loading state
    modal.style.display = 'block';
    img.style.display = 'none';
    loadingIndicator.style.display = 'flex';
    errorMessage.style.display = 'none';
    titleSpan.textContent = title || 'Image Viewer';
    
    // Get the absolute URL
    const absoluteUrl = new URL(url, window.location.origin).href;
    
    img.onload = function() {
        loadingIndicator.style.display = 'none';
        img.style.display = 'block';
    }
    
    img.onerror = function() {
        loadingIndicator.style.display = 'none';
        errorMessage.style.display = 'flex';
    }
    
    img.src = absoluteUrl;
}

function viewPDF(url, title) {
    const modal = document.getElementById('pdfViewerModal');
    const iframe = document.getElementById('pdfViewer');
    const titleSpan = document.getElementById('pdfTitle');
    const loadingIndicator = document.getElementById('pdfLoadingIndicator');
    const errorMessage = document.getElementById('pdfError');
    
    // Reset states
    currentFileUrl = url;
    
    modal.style.display = 'block';
    iframe.style.display = 'none';
    loadingIndicator.style.display = 'flex';
    errorMessage.style.display = 'none';
    titleSpan.textContent = title || 'Document Viewer';
    
    // Get the absolute URL
    const absoluteUrl = new URL(url, window.location.origin).href;
    
    // For PDFs, use Google Docs Viewer for better compatibility
    const viewerUrl = `https://docs.google.com/viewer?url=${encodeURIComponent(absoluteUrl)}&embedded=true`;
    
    iframe.onload = function() {
        loadingIndicator.style.display = 'none';
        iframe.style.display = 'block';
    }
    
    iframe.onerror = function() {
        loadingIndicator.style.display = 'none';
        errorMessage.style.display = 'flex';
    }
    
    iframe.src = viewerUrl;
}

function rotateImage(degrees) {
    currentRotation = (currentRotation + degrees) % 360;
    updateImageTransform();
}

function zoomImage(factor) {
    currentZoom *= factor;
    // Limit zoom range
    currentZoom = Math.min(Math.max(0.5, currentZoom), 3);
    updateImageTransform();
}

function updateImageTransform() {
    const img = document.getElementById('viewedImage');
    img.style.transform = `rotate(${currentRotation}deg) scale(${currentZoom})`;
}

function downloadFile() {
    if (currentFileUrl) {
        const link = document.createElement('a');
        link.href = currentFileUrl;
        link.download = currentFileUrl.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function getFileType(url) {
    const ext = url.split('.').pop().toLowerCase();
    const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
    const documentTypes = ['pdf', 'doc', 'docx'];
    
    if (imageTypes.includes(ext)) return 'image';
    if (documentTypes.includes(ext)) return 'document';
    return 'unknown';
}

function viewFile(url, title) {
    const fileType = getFileType(url);
    
    switch(fileType) {
        case 'image':
            viewImage(url, title);
            break;
        case 'document':
            viewPDF(url, title);
            break;
        default:
            window.open(url, '_blank');
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        closeModal(event.target.id);
    }
}

function viewRegistration(data) {
    let modalContent = `
        <div class="detail-section">
            <div class="detail-group">
                <div class="detail-label">
                    <i class="fas fa-user"></i> Student Name
                </div>
                <div class="detail-value">${data.fullName}</div>
            </div>
        </div>

        <div class="files-section">
            <div class="detail-label">
                <i class="fas fa-file-upload"></i> Uploaded Documents
            </div>
            <div class="file-grid">
                ${data.picture ? `
                    <div class="file-preview-card" onclick="viewFile('/gcc/shared/main/${data.picture}', 'Student Photo')">
                        <div class="preview-container">
                            <img src="/gcc/shared/main/${data.picture}" alt="Student Photo" class="preview-thumbnail">
                            <div class="preview-overlay">
                                <button class="preview-button">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="file-info">
                            <i class="fas fa-image"></i>
                            <span>Student Photo</span>
                        </div>
                    </div>
                ` : ''}
                
                ${data.grades ? `
                    <div class="file-preview-card" onclick="openPDF('/gcc/shared/main/${data.grades}', 'Student Grades')">
                        <div class="preview-container pdf-preview">
                            <i class="fas fa-file-pdf"></i>
                            <div class="preview-overlay">
                                <button class="preview-button">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="file-info">
                            <i class="fas fa-file-alt"></i>
                            <span>Grades</span>
                        </div>
                    </div>
                ` : ''}
                
                ${data.cor ? `
                    <div class="file-preview-card" onclick="openPDF('/gcc/shared/main/${data.cor}', 'Certificate of Registration')">
                        <div class="preview-container pdf-preview">
                            <i class="fas fa-file-pdf"></i>
                            <div class="preview-overlay">
                                <button class="preview-button">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="file-info">
                            <i class="fas fa-file-invoice"></i>
                            <span>COR</span>
                        </div>
                    </div>
                ` : ''}
                
                ${data.cet_result ? `
                    <div class="file-preview-card" onclick="openPDF('/gcc/shared/main/${data.cet_result}', 'CET Result')">
                        <div class="preview-container pdf-preview">
                            <i class="fas fa-file-pdf"></i>
                            <div class="preview-overlay">
                                <button class="preview-button">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                        <div class="file-info">
                            <i class="fas fa-file-contract"></i>
                            <span>CET</span>
                        </div>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    
    $('#modalDetails').html(modalContent);
    $('#viewModal').css('display', 'block');
}
</script> 