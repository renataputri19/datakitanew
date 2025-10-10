/**
 * SIBSTR Survey System JavaScript Module
 * Professional survey functionality with auto-save, validation, and status management
 */

class SurveyManager {
    constructor(options = {}) {
        this.options = {
            autoSaveDelay: 2000, // Increased to 2 seconds for better reliability
            autoSaveUrl: options.autoSaveUrl || '/survei/sibstr/auto-save',
            saveAllUrl: options.saveAllUrl || '/survei/sibstr/save-all',
            statusUrl: options.statusUrl || '/survei/sibstr/status',
            formSelector: '#survey-form',
            statusSelector: '#autosave-status',
            statusTextSelector: '#autosave-text',
            ...options
        };

        this.autoSaveTimeout = null;
        this.isInitialized = false;
        this.csrfToken = null;

        this.init();
    }

    /**
     * Initialize the survey manager
     */
    init() {
        if (this.isInitialized) return;

        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setup());
        } else {
            this.setup();
        }
    }

    /**
     * Setup form validation
     */
    setupValidation() {
        // Real-time validation for email fields
        const emailFields = this.form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            field.addEventListener('blur', () => this.validateEmail(field));
            field.addEventListener('input', () => this.clearFieldError(field));
        });

        // Validation for NIB field (13 digits only)
        const nibField = this.form.querySelector('input[name="nib"]');
        if (nibField) {
            nibField.addEventListener('input', (e) => this.handleNibInput(e));
            nibField.addEventListener('blur', () => this.validateNib(nibField));
        }

        // Validation for text fields with length limits
        const textFields = this.form.querySelectorAll('input[type="text"], textarea');
        textFields.forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.clearFieldError(field));
        });

        // Validation for required fields
        const requiredFields = this.form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => this.validateRequired(field));
        });
    }

    /**
     * Validate email field
     */
    validateEmail(field) {
        const value = field.value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (value && !emailRegex.test(value)) {
            this.showFieldError(field, 'Format email tidak valid');
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }

    /**
     * Handle NIB input - only allow numeric characters
     */
    handleNibInput(event) {
        const field = event.target;
        let value = field.value;
        
        // Remove any non-numeric characters
        value = value.replace(/[^0-9]/g, '');
        
        // Limit to 13 characters
        if (value.length > 13) {
            value = value.substring(0, 13);
        }
        
        field.value = value;
        
        // Clear error if user is typing
        this.clearFieldError(field);
    }

    /**
     * Validate NIB field - must be exactly 13 digits
     */
    validateNib(field) {
        const value = field.value.trim();
        const nibRegex = /^[0-9]{13}$/;
        
        if (value && !nibRegex.test(value)) {
            this.showFieldError(field, 'NIB harus berupa 13 digit angka');
            return false;
        }
        
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, 'NIB (Nomor Induk Berusaha) wajib diisi');
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }

    /**
     * Validate field with length and other constraints
     */
    validateField(field) {
        const value = field.value.trim();
        const maxLength = field.getAttribute('maxlength');
        
        // Check max length
        if (maxLength && value.length > parseInt(maxLength)) {
            this.showFieldError(field, `Maksimal ${maxLength} karakter`);
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }

    /**
     * Validate required field
     */
    validateRequired(field) {
        const value = field.value.trim();
        
        if (field.hasAttribute('required') && !value) {
            this.showFieldError(field, 'Field ini wajib diisi');
            return false;
        }
        
        this.clearFieldError(field);
        return true;
    }

    /**
     * Show field error message
     */
    showFieldError(field, message) {
        // Remove existing error
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

    /**
     * Clear field error message
     */
    clearFieldError(field) {
        // Remove error class
        field.classList.remove('field-error');
        
        // Remove error message
        const errorElement = field.parentNode.querySelector('.field-error-message');
        if (errorElement) {
            errorElement.remove();
        }
    }

    /**
     * Validate entire form before save
     */
    validateFormBeforeSave() {
        let isValid = true;
        const errors = [];

        // Validate all email fields
        const emailFields = this.form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (!this.validateEmail(field)) {
                isValid = false;
                errors.push(`${this.getFieldLabel(field)}: Format email tidak valid`);
            }
        });

        // Validate NIB field
        const nibField = this.form.querySelector('input[name="nib"]');
        if (nibField && !this.validateNib(nibField)) {
            isValid = false;
            errors.push(`${this.getFieldLabel(nibField)}: NIB harus berupa 13 digit angka`);
        }

        // Validate all required fields
        const requiredFields = this.form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!this.validateRequired(field)) {
                isValid = false;
                errors.push(`${this.getFieldLabel(field)}: Field wajib diisi`);
            }
        });

        // Validate all text fields
        const textFields = this.form.querySelectorAll('input[type="text"], textarea');
        textFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
                errors.push(`${this.getFieldLabel(field)}: Validasi gagal`);
            }
        });

        return { isValid, errors };
    }

    /**
     * Get field label for error messages
     */
    getFieldLabel(field) {
        const label = this.form.querySelector(`label[for="${field.id}"]`);
        if (label) {
            return label.textContent.replace(/^\d+\.\s*/, '').trim();
        }
        return field.name || field.id || 'Field';
    }

    /**
     * Setup the survey functionality
     */
    setup() {
        try {
            // Get CSRF token
            this.csrfToken = this.getCSRFToken();
            if (!this.csrfToken) {
                console.error('CSRF token not found');
                return;
            }

            // Get DOM elements
            this.form = document.querySelector(this.options.formSelector);
            this.statusDiv = document.querySelector(this.options.statusSelector);
            this.statusText = document.querySelector(this.options.statusTextSelector);

            if (!this.form) {
                console.error('Survey form not found');
                return;
            }

            // Setup event listeners
            this.setupAutoSave();
            this.setupFormSubmission();
            this.setupFormValidation();
            this.setupValidation();

            this.isInitialized = true;
            console.log('Survey manager initialized successfully');

        } catch (error) {
            console.error('Error initializing survey manager:', error);
        }
    }

    /**
     * Get CSRF token from meta tag
     */
    getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
    }

    /**
     * Disable form after completion
     */
    disableForm() {
        const formElements = this.form.querySelectorAll('input, textarea, select, button');
        formElements.forEach(element => {
            element.disabled = true;
        });
        
        this.form.classList.add('form-disabled');
    }

    /**
     * Setup auto-save functionality
     */
    setupAutoSave() {
        const formInputs = this.form.querySelectorAll('input, textarea, select');
        
        formInputs.forEach(input => {
            // For text inputs, number inputs, textareas, and selects
            if (['text', 'email', 'tel', 'url', 'number', 'textarea', 'select-one'].includes(input.type) || input.tagName === 'TEXTAREA') {
                input.addEventListener('input', (e) => {
                    this.scheduleAutoSave(e.target.name, e.target.value);
                });
                
                // Fast auto-save when user moves to next field (blur event)
                input.addEventListener('blur', (e) => {
                    this.scheduleAutoSave(e.target.name, e.target.value, true); // immediate save
                });
                
                // Fast auto-save on delete/backspace
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' || e.key === 'Delete') {
                        // Schedule immediate save after deletion
                        setTimeout(() => {
                            this.scheduleAutoSave(e.target.name, e.target.value, true);
                        }, 50); // Small delay to capture the deleted value
                    }
                });
            }
            
            // For radio buttons and checkboxes
            if (['radio', 'checkbox'].includes(input.type)) {
                input.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        this.scheduleAutoSave(e.target.name, e.target.value, true); // immediate save for selections
                    }
                });
            }
        });
    }

    /**
     * Schedule auto-save with debouncing
     */
    scheduleAutoSave(fieldName, fieldValue, immediate = false) {
        // Clear existing timeout
        if (this.autoSaveTimeout) {
            clearTimeout(this.autoSaveTimeout);
        }

        // If immediate save is requested, save right away
        if (immediate) {
            this.performAutoSave(fieldName, fieldValue);
            return;
        }

        // Schedule new auto-save with delay
        this.autoSaveTimeout = setTimeout(() => {
            this.performAutoSave(fieldName, fieldValue);
        }, this.options.autoSaveDelay);
    }

    /**
     * Perform auto-save operation
     */
    async performAutoSave(fieldName, fieldValue) {
        const field = this.form.querySelector(`[name="${fieldName}"]`);
        
        try {
            this.showStatus('Menyimpan...', 'info', true);
            
            // Remove any existing validation classes while saving
            if (field) {
                field.classList.remove('field-valid', 'field-invalid');
            }

            const response = await fetch(this.options.autoSaveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    field: fieldName,
                    value: fieldValue
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                this.showStatus('Tersimpan otomatis', 'success');
                
                // Only show visual feedback after successful save
                if (field && fieldValue.trim() !== '') {
                    field.classList.add('field-valid');
                }
                
                console.log('Auto-save successful:', data);
            } else {
                throw new Error(data.message || 'Auto-save failed');
            }

        } catch (error) {
            console.error('Auto-save error:', error);
            this.showStatus('Gagal menyimpan: ' + error.message, 'error');
            
            // Show error state on field if save failed
            if (field && fieldValue.trim() !== '') {
                field.classList.add('field-invalid');
            }
        }
    }

    /**
     * Setup form submission handlers
     */
    setupFormSubmission() {
        // Save draft button
        const saveDraftBtn = document.getElementById('save-draft');
        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveForm(false);
            });
        }

        // Save and complete button
        const saveCompleteBtn = document.getElementById('save-complete');
        if (saveCompleteBtn) {
            saveCompleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveForm(true);
            });
        }

        // Form submit event
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.saveForm(true);
        });
    }

    /**
     * Save form data
     */
    async saveForm(isCompleted = false) {
        try {
            // Validate form before saving
            const validation = this.validateFormBeforeSave();
            if (!validation.isValid) {
                const errorMessage = 'Mohon perbaiki kesalahan berikut:\n' + validation.errors.join('\n');
                this.showStatus(errorMessage, 'error');
                return;
            }

            // Get form data properly including all fields
            const formData = new FormData(this.form);
            const data = {};
            
            // Convert FormData to object, handling multiple values and empty fields
            for (let [key, value] of formData.entries()) {
                if (data[key]) {
                    // Handle multiple values (like radio buttons)
                    if (Array.isArray(data[key])) {
                        data[key].push(value);
                    } else {
                        data[key] = [data[key], value];
                    }
                } else {
                    data[key] = value;
                }
            }
            
            // Add completion status
            data.is_completed = isCompleted;

            const statusMessage = isCompleted ? 'Menyimpan dan menyelesaikan...' : 'Menyimpan draft...';
            this.showStatus(statusMessage, 'info', true);

            // Ensure CSRF token is fresh
            const csrfToken = this.getCSRFToken();
            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }

            const response = await fetch(this.options.saveAllUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok && result.success) {
                const successMessage = isCompleted ? 
                    'Survei berhasil diselesaikan dan disimpan' : 
                    'Draft berhasil disimpan';
                
                this.showStatus(successMessage, 'success');

                if (isCompleted) {
                    // Handle conditional navigation based on server response
                    let nextUrl = null;

                    if (result.next_block) {
                        // Server provided specific next block
                        if (result.next_block === 'blok3a' && window.surveyRoutes?.blok3a) {
                            nextUrl = window.surveyRoutes.blok3a;
                        } else if (result.next_block === 'blok6' && window.surveyRoutes?.blok6) {
                            nextUrl = window.surveyRoutes.blok6;
                        }
                    } else if (window.surveyRoutes?.nextBlok) {
                        // Fallback to default next block
                        nextUrl = window.surveyRoutes.nextBlok;
                    }

                    if (nextUrl) {
                        setTimeout(() => {
                            window.location.href = nextUrl;
                        }, 1500); // Give user time to see success message
                    } else {
                        // Optionally disable form if no next block
                        this.disableForm();
                    }
                }
                
                console.log('Form save successful:', result);
            } else {
                // Handle validation errors specifically
                if (response.status === 422 && result.errors) {
                    const errorMessages = Object.values(result.errors).flat();
                    throw new Error('Validation failed: ' + errorMessages.join(', '));
                } else {
                    throw new Error(result.message || 'Save failed');
                }
            }

        } catch (error) {
            console.error('Form save error:', error);
            const errorMessage = isCompleted ? 
                'Gagal menyelesaikan survei: ' + error.message : 
                'Gagal menyimpan draft: ' + error.message;
            
            this.showStatus(errorMessage, 'error');
        }
    }

    /**
     * Setup form validation
     */
    setupFormValidation() {
        const formInputs = this.form.querySelectorAll('input, textarea, select');
        
        formInputs.forEach(input => {
            // Remove automatic validation on input - let auto-save handle it
            input.addEventListener('blur', (e) => {
                // Only validate on blur if the field has been saved successfully
                if (e.target.classList.contains('field-valid')) {
                    this.validateField(e.target);
                }
            });
        });
    }

    /**
     * Validate individual field
     */
    validateField(field) {
        const isValid = field.checkValidity();
        
        // Remove existing validation classes
        field.classList.remove('field-valid', 'field-invalid');
        
        // Add appropriate class
        if (field.value.trim() !== '') {
            field.classList.add(isValid ? 'field-valid' : 'field-invalid');
        }
        
        return isValid;
    }

    /**
     * Show status message
     */
    showStatus(message, type = 'info', showSpinner = false) {
        if (!this.statusDiv || !this.statusText) return;

        // Clear existing classes
        this.statusDiv.className = 'autosave-status';
        
        // Add type-specific class
        this.statusDiv.classList.add(type);
        
        // Set message with optional spinner
        if (showSpinner) {
            this.statusText.innerHTML = `
                <span class="loading-spinner"></span>
                ${message}
            `;
        } else {
            this.statusText.textContent = message;
        }
        
        // Show status
        this.statusDiv.classList.remove('hidden');
        
        // Auto-hide success messages
        if (type === 'success') {
            setTimeout(() => {
                this.hideStatus();
            }, 3000);
        }
    }

    /**
     * Hide status message
     */
    hideStatus() {
        if (this.statusDiv) {
            this.statusDiv.classList.add('hidden');
        }
    }

    /**
     * Get CSRF token from meta tag
     */
    getCSRFToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
    }

    /**
     * Enable form
     */
    enableForm() {
        const formElements = this.form.querySelectorAll('input, textarea, select, button');
        formElements.forEach(element => {
            element.disabled = false;
        });
        
        this.form.classList.remove('form-disabled');
    }

    /**
     * Get form data as object
     */
    getFormData() {
        const formData = new FormData(this.form);
        return Object.fromEntries(formData);
    }

    /**
     * Set form data from object
     */
    setFormData(data) {
        Object.keys(data).forEach(key => {
            const field = this.form.querySelector(`[name="${key}"]`);
            if (field) {
                if (field.type === 'radio') {
                    const radioOption = this.form.querySelector(`[name="${key}"][value="${data[key]}"]`);
                    if (radioOption) radioOption.checked = true;
                } else if (field.type === 'checkbox') {
                    field.checked = Boolean(data[key]);
                } else {
                    field.value = data[key] || '';
                }
            }
        });
    }

    /**
     * Destroy the survey manager
     */
    destroy() {
        if (this.autoSaveTimeout) {
            clearTimeout(this.autoSaveTimeout);
        }
        
        this.isInitialized = false;
        console.log('Survey manager destroyed');
    }
}

// Auto-initialize when script loads
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on a survey page
    if (document.querySelector('#survey-form')) {
        // Get routes from global variables or data attributes
        const autoSaveUrl = window.surveyRoutes?.autoSave || '/survei/sibstr/auto-save';
        const saveAllUrl = window.surveyRoutes?.saveAll || '/survei/sibstr/save-all';
        const statusUrl = window.surveyRoutes?.status || '/survei/sibstr/status';
        
        // Initialize survey manager
        window.surveyManager = new SurveyManager({
            autoSaveUrl,
            saveAllUrl,
            statusUrl
        });
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyManager;
}
