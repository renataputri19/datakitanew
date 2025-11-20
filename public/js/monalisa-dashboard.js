/**
 * MONALISA Dashboard JavaScript
 * Handles interactive features for the evaluation system
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initMaturitySelector();
    initDocumentUpload();
    initAssessmentForm();
    initScoreToggle();
    initCharts();
});

/**
 * Initialize maturity level selector
 */
function initMaturitySelector() {
    const maturityInputs = document.querySelectorAll('.monalisa-maturity-input');
    
    maturityInputs.forEach(input => {
        input.addEventListener('change', function() {
            // Update visual feedback
            const label = this.nextElementSibling;
            const allLabels = document.querySelectorAll('.monalisa-maturity-label');
            
            allLabels.forEach(l => l.classList.remove('selected'));
            label.classList.add('selected');
            
            // Enable submit button if justification is filled
            validateAssessmentForm();
        });
    });
}

/**
 * Initialize document upload functionality
 */
function initDocumentUpload() {
    const uploadArea = document.getElementById('monalisaUploadArea');
    const fileInput = document.getElementById('monalisaFileInput');
    
    if (!uploadArea || !fileInput) return;
    
    // Click to upload
    uploadArea.addEventListener('click', () => fileInput.click());
    
    // Drag and drop
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        handleFileUpload(files);
    });
    
    // File input change
    fileInput.addEventListener('change', (e) => {
        handleFileUpload(e.target.files);
    });
}

/**
 * Handle file upload
 */
function handleFileUpload(files) {
    const allowedTypes = ['application/pdf'];
    const maxSize = 10 * 1024 * 1024; // 10MB
    
    Array.from(files).forEach(file => {
        // Validate file type
        if (!allowedTypes.includes(file.type)) {
            showNotification('Format file tidak didukung: ' + file.name, 'error');
            return;
        }
        
        // Validate file size
        if (file.size > maxSize) {
            showNotification('Ukuran file terlalu besar: ' + file.name, 'error');
            return;
        }
        
        // Upload file
        uploadFile(file);
    });
}

/**
 * Upload file to server
 */
function uploadFile(file) {
    const assessmentId = document.getElementById('assessmentId')?.value;
    if (!assessmentId) return;
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('description', '');
    
    // Show upload progress
    const progressItem = createProgressItem(file.name);
    document.getElementById('monalisaDocumentList')?.appendChild(progressItem);
    
    fetch(`/monalisa/kominfo/assessment/${assessmentId}/upload`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            progressItem.remove();
            addDocumentToList(data.document);
            showNotification('Dokumen berhasil diunggah', 'success');
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        progressItem.remove();
        showNotification('Gagal mengunggah dokumen: ' + error.message, 'error');
    });
}

/**
 * Create progress item for upload
 */
function createProgressItem(filename) {
    const div = document.createElement('div');
    div.className = 'monalisa-document-item uploading';
    div.innerHTML = `
        <div class="monalisa-document-info">
            <div class="monalisa-document-icon">📄</div>
            <div>
                <div class="font-medium">${filename}</div>
                <div class="text-sm text-gray-500">Mengunggah...</div>
            </div>
        </div>
        <div class="spinner"></div>
    `;
    return div;
}

/**
 * Add document to list
 */
function addDocumentToList(document) {
    const list = document.getElementById('monalisaDocumentList');
    if (!list) return;
    
    const div = document.createElement('div');
    div.className = 'monalisa-document-item';
    div.dataset.documentId = document.id;
    div.innerHTML = `
        <div class="monalisa-document-info">
            <div class="monalisa-document-icon">📄</div>
            <div>
                <div class="font-medium">${document.original_filename}</div>
                <div class="text-sm text-gray-500">${formatFileSize(document.file_size)}</div>
            </div>
        </div>
        <div class="flex gap-2">
            <button onclick="downloadDocument('${document.id}')" class="monalisa-btn monalisa-btn-secondary btn-sm">
                Download
            </button>
            <button onclick="deleteDocument('${document.id}')" class="monalisa-btn monalisa-btn-danger btn-sm">
                Hapus
            </button>
        </div>
    `;
    list.appendChild(div);
}

/**
 * Delete document
 */
function deleteDocument(documentId) {
    if (!confirm('Apakah Anda yakin ingin menghapus dokumen ini?')) return;
    
    fetch(`/monalisa/kominfo/document/${documentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector(`[data-document-id="${documentId}"]`)?.remove();
            showNotification('Dokumen berhasil dihapus', 'success');
        }
    })
    .catch(error => {
        showNotification('Gagal menghapus dokumen', 'error');
    });
}

/**
 * Download document
 */
function downloadDocument(documentId) {
    window.location.href = `/monalisa/kominfo/document/${documentId}/download`;
}

/**
 * Initialize assessment form
 */
function initAssessmentForm() {
    const form = document.getElementById('monalisaAssessmentForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        saveAssessment();
    });
    
    // Auto-save on input
    const justificationInput = document.getElementById('justification');
    if (justificationInput) {
        let timeout;
        justificationInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                saveAssessment(true); // Auto-save
            }, 2000);
        });
    }
}

/**
 * Save assessment
 */
function saveAssessment(autoSave = false) {
    const form = document.getElementById('monalisaAssessmentForm');
    const formData = new FormData(form);
    const indikatorId = document.getElementById('indikatorId')?.value;
    
    if (!indikatorId) return;
    
    fetch(`/monalisa/kominfo/assessment/${indikatorId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!autoSave) {
                showNotification('Assessment berhasil disimpan', 'success');
            } else {
                updateLastSaved();
            }
        }
    })
    .catch(error => {
        if (!autoSave) {
            showNotification('Gagal menyimpan assessment', 'error');
        }
    });
}

/**
 * Validate assessment form
 */
function validateAssessmentForm() {
    const maturityLevel = document.querySelector('.monalisa-maturity-input:checked');
    const justification = document.getElementById('justification')?.value;
    const submitBtn = document.getElementById('submitAssessmentBtn');
    
    if (submitBtn) {
        submitBtn.disabled = !(maturityLevel && justification && justification.length >= 50);
    }
}

/**
 * Initialize score toggle (Kominfo vs BPS)
 */
function initScoreToggle() {
    const toggleBtns = document.querySelectorAll('[data-score-toggle]');
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const scoreType = this.dataset.scoreToggle;
            toggleScoreView(scoreType);
            
            // Update active state
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

/**
 * Toggle score view
 */
function toggleScoreView(scoreType) {
    const kominfoScores = document.querySelectorAll('[data-score-type="kominfo"]');
    const bpsScores = document.querySelectorAll('[data-score-type="bps"]');
    
    if (scoreType === 'kominfo') {
        kominfoScores.forEach(el => el.style.display = 'block');
        bpsScores.forEach(el => el.style.display = 'none');
    } else {
        kominfoScores.forEach(el => el.style.display = 'none');
        bpsScores.forEach(el => el.style.display = 'block');
    }
}

/**
 * Initialize charts (placeholder for Chart.js integration)
 */
function initCharts() {
    // This will be implemented when creating the dashboard views
    // Will use Chart.js for data visualization
}

/**
 * Utility: Format file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Utility: Show notification
 */
function showNotification(message, type = 'info') {
    // Simple notification - can be enhanced with a library like Toastr
    const notification = document.createElement('div');
    notification.className = `monalisa-notification monalisa-notification-${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1rem 1.5rem;
        background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
        color: white;
        border-radius: 0.5rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Utility: Update last saved timestamp
 */
function updateLastSaved() {
    const lastSavedEl = document.getElementById('lastSaved');
    if (lastSavedEl) {
        const now = new Date();
        lastSavedEl.textContent = `Terakhir disimpan: ${now.toLocaleTimeString('id-ID')}`;
    }
}

