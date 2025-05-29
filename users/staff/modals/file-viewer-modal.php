<?php
// File Viewer Modal
?>
<!-- Image Viewer Modal -->
<div id="fileViewerModal" class="modal">
    <div class="modal-content">
        <div class="viewer-header">
            <h3 id="viewer-title">Document Viewer</h3>
            <div class="header-controls">
                <a id="download-btn" href="#" class="download-link">
                    <i class="fas fa-download"></i> Download
                </a>
                <span class="close" onclick="closeModal('fileViewerModal')">&times;</span>
            </div>
        </div>
        <div class="viewer-container">
            <img id="imageViewer" style="display: none;">
            <iframe id="pdfViewer" style="display: none;"></iframe>
            <pre id="textViewer" style="display: none;"></pre>
            <div id="unsupportedViewer" style="display: none;">
                <i class="fas fa-file"></i>
                <p>This file type cannot be previewed</p>
                <a id="download-link" href="#" class="download-link">Download File</a>
            </div>
            <div class="loading-spinner"></div>
        </div>
    </div>
</div>

<style>
/* File Viewer Modal Styles */
#fileViewerModal .modal-content {
    padding: 20px;
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    width: 90%;
    max-width: 1200px;
}

.viewer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.header-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}

.download-link {
    color: #2196F3;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 4px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.download-link:hover {
    background-color: #f5f5f5;
    text-decoration: underline;
}

.viewer-container {
    width: 100%;
    height: 70vh;
    display: flex;
    flex-direction: column;
    position: relative;
}

#pdfViewer, 
#imageViewer {
    width: 100%;
    height: 100%;
    border: none;
}

#imageViewer {
    object-fit: contain;
    max-width: 100%;
    max-height: 100%;
}

#textViewer {
    white-space: pre-wrap;
    overflow: auto;
    padding: 10px;
    background: #f5f5f5;
    font-family: monospace;
}

#unsupportedViewer {
    text-align: center;
    padding: 20px;
    color: #777;
}

#unsupportedViewer i {
    font-size: 48px;
    margin-bottom: 15px;
}

.loading-spinner {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 2s linear infinite;
}

@keyframes spin {
    to { transform: translate(-50%, -50%) rotate(360deg); }
}
</style>

<script>
// File viewer functionality is handled in the main view-shifting-modal.php file
</script> 