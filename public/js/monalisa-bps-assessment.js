/**
 * MONALISA BPS Assessment Form Handler
 * Handles BPS verification form submission with validation and error display
 */

class BpsAssessmentForm {
    constructor() {
        this.form = document.querySelector('form[action*="/monalisa/bps/assessment/"][action*="/verify"]');
        if (!this.form) return;

        this.maturityLevelSelect = document.getElementById('bps_maturity_level');
        this.auditCommentTextarea = document.getElementById('bps_audit_comment');
        this.submitButtons = this.form.querySelectorAll('button[type="submit"]');
        
        this.init();
    }

    init() {
        // Prevent default form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit(e);
        });

        // Real-time validation
        this.setupRealtimeValidation();

        // Character counter for audit comment
        this.setupCharacterCounter();
    }

    setupRealtimeValidation() {
        // Validate maturity level on change
        if (this.maturityLevelSelect) {
            this.maturityLevelSelect.addEventListener('change', () => {
                this.validateMaturityLevel();
            });
        }

        // Validate audit comment on input
        if (this.auditCommentTextarea) {
            this.auditCommentTextarea.addEventListener('input', () => {
                this.clearFieldError(this.auditCommentTextarea);
            });

            this.auditCommentTextarea.addEventListener('blur', () => {
                this.validateAuditComment();
            });
        }
    }

    setupCharacterCounter() {
        if (!this.auditCommentTextarea) return;

        // Create character counter element
        const helpText = this.auditCommentTextarea.parentElement.querySelector('.monalisa-form-help');
        if (helpText) {
            const counter = document.createElement('span');
            counter.className = 'character-counter';
            counter.style.cssText = 'float: right; font-weight: 600;';
            
            const updateCounter = () => {
                const length = this.auditCommentTextarea.value.length;
                counter.textContent = `${length}/50 karakter`;
                counter.style.color = length >= 50 ? '#10b981' : '#6b7280';
            };

            helpText.appendChild(counter);
            this.auditCommentTextarea.addEventListener('input', updateCounter);
            updateCounter();
        }
    }

    validateMaturityLevel() {
        if (!this.maturityLevelSelect) return true;

        const value = this.maturityLevelSelect.value;
        
        if (!value) {
            this.showFieldError(this.maturityLevelSelect, 'BPS Maturity Level wajib dipilih.');
            return false;
        }

        const numValue = parseInt(value);
        if (numValue < 1 || numValue > 5) {
            this.showFieldError(this.maturityLevelSelect, 'BPS Maturity Level harus antara 1-5.');
            return false;
        }

        this.clearFieldError(this.maturityLevelSelect);
        return true;
    }

    validateAuditComment() {
        if (!this.auditCommentTextarea) return true;

        const value = this.auditCommentTextarea.value.trim();
        
        if (!value) {
            this.showFieldError(this.auditCommentTextarea, 'Komentar Audit wajib diisi.');
            return false;
        }

        if (value.length < 50) {
            this.showFieldError(this.auditCommentTextarea, `Komentar Audit minimal 50 karakter. Saat ini: ${value.length} karakter.`);
            return false;
        }

        this.clearFieldError(this.auditCommentTextarea);
        return true;
    }

    validateForm() {
        let isValid = true;

        if (!this.validateMaturityLevel()) {
            isValid = false;
        }

        if (!this.validateAuditComment()) {
            isValid = false;
        }

        return isValid;
    }

    showFieldError(field, message) {
        // Clear existing error first
        this.clearFieldError(field);

        // Add error class to field
        field.classList.add('field-error');

        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error-message';
        errorElement.textContent = message;

        // Insert error message after the field
        field.parentNode.insertBefore(errorElement, field.nextSibling);
    }

    clearFieldError(field) {
        // Remove error class
        field.classList.remove('field-error');

        // Remove error message
        const errorMessage = field.parentNode.querySelector('.field-error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }

    clearAllErrors() {
        const errorFields = this.form.querySelectorAll('.field-error');
        errorFields.forEach(field => {
            this.clearFieldError(field);
        });
    }

    disableSubmitButtons() {
        this.submitButtons.forEach(btn => {
            btn.disabled = true;
            btn.style.opacity = '0.6';
            btn.style.cursor = 'not-allowed';
        });
    }

    enableSubmitButtons() {
        this.submitButtons.forEach(btn => {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
        });
    }

    showNotification(message, type = 'success') {
        // Use global MONALISA/DataKita toast if available for consistent styling
        if (window.MonalisaNotifications && typeof window.MonalisaNotifications.showToast === 'function') {
            const title = type === 'success' ? 'Berhasil' :
                          type === 'error' ? 'Kesalahan' : 'Info';
            window.MonalisaNotifications.showToast(title, message, type);
            return;
        }

        // Fallback inline notification (keeps previous behavior if toast script missing)
        const notification = document.createElement('div');
        notification.className = `monalisa-notification monalisa-notification-${type}`;
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
            animation: slideInRight 0.3s ease;
            max-width: 400px;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => notification.remove(), 300);
        }, 5000);
    }

    async handleSubmit(e) {
        const submitter = e.submitter;
        const action = submitter?.value || 'verify';

        // For rejection, only validate audit comment (maturity level not required)
        if (action === 'reject') {
            if (!this.validateAuditComment()) {
                this.showNotification('Mohon isi komentar penolakan minimal 50 karakter.', 'error');
                this.auditCommentTextarea?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.auditCommentTextarea?.focus();
                return;
            }
        } else {
            // For verification, validate all fields
            if (!this.validateForm()) {
                this.showNotification('Mohon lengkapi semua field yang wajib diisi dengan benar.', 'error');

                // Scroll to first error
                const firstError = this.form.querySelector('.field-error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                return;
            }
        }

        // Disable submit buttons
        this.disableSubmitButtons();

        // Prepare form data
        const formData = new FormData(this.form);

        // Determine the correct URL based on action
        const baseUrl = this.form.getAttribute('action').replace('/verify', '');
        const formActionUrl = action === 'reject'
            ? baseUrl + '/reject'
            : baseUrl + '/verify';

        try {
            const response = await fetch(formActionUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Success
                const defaultMessage = action === 'reject'
                    ? 'Assessment berhasil ditolak.'
                    : 'Assessment berhasil diverifikasi.';
                this.showNotification(result.message || defaultMessage, 'success');

                // Clear form errors
                this.clearAllErrors();

                // Redirect after short delay
                setTimeout(() => {
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        window.location.reload();
                    }
                }, 1500);

            } else if (response.status === 422) {
                // Validation errors
                this.handleValidationErrors(result.errors || {});
                this.showNotification('Terdapat kesalahan pada form. Mohon periksa kembali.', 'error');
            } else {
                // Other errors
                const defaultError = action === 'reject'
                    ? 'Terjadi kesalahan saat menolak assessment.'
                    : 'Terjadi kesalahan saat memverifikasi assessment.';
                throw new Error(result.message || defaultError);
            }

        } catch (error) {
            console.error('BPS Assessment submission error:', error);
            this.showNotification(error.message || 'Terjadi kesalahan. Silakan coba lagi.', 'error');
        } finally {
            // Re-enable submit buttons
            this.enableSubmitButtons();
        }
    }

    handleValidationErrors(errors) {
        // Clear all existing errors first
        this.clearAllErrors();

        // Display each validation error
        Object.keys(errors).forEach(fieldName => {
            const field = this.form.querySelector(`[name="${fieldName}"]`);
            if (field) {
                const messages = Array.isArray(errors[fieldName]) ? errors[fieldName] : [errors[fieldName]];
                this.showFieldError(field, messages[0]);
            }
        });

        // Scroll to first error
        const firstError = this.form.querySelector('.field-error');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideOutRight {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }

    .character-counter {
        display: inline-block;
        margin-left: 0.5rem;
    }
`;
document.head.appendChild(style);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new BpsAssessmentForm();
});

