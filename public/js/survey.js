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

        // Validation for LEGALISASI NAMA (align with server: any string, required)
        const legalisasiNamaField = this.form.querySelector('input[name="legalisasi_nama"]');
        if (legalisasiNamaField) {
            legalisasiNamaField.addEventListener('input', (e) => this.handleLegalisasiNamaInput(e));
            legalisasiNamaField.addEventListener('blur', () => this.validateLegalisasiNama(legalisasiNamaField));
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
     * Handle Legalisasi Nama input - allow only alphabetic characters and spaces
     */
    handleLegalisasiNamaInput(event) {
        const field = event.target;
        // Trim spaces; do not restrict characters — server allows any string
        field.value = (field.value || '').replace(/\s+/g, ' ').trimStart();
        this.clearFieldError(field);
    }

    /**
     * Validate Legalisasi Nama field - must contain only letters and spaces
     */
    validateLegalisasiNama(field) {
        const value = (field.value || '').trim();
        // Align with server: required string up to 255 chars
        if (field.hasAttribute('required') && value === '') {
            this.showFieldError(field, 'Nama penanggung jawab wajib diisi');
            return false;
        }
        if (value.length > 255) {
            this.showFieldError(field, 'Maksimal 255 karakter');
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
        // Only enforce when the attribute exists
        if (!field || !field.hasAttribute('required')) {
            this.clearFieldError(field);
            return true;
        }

        // Special handling for radio groups
        if (field.type === 'radio') {
            const groupName = field.name;
            const radios = this.form.querySelectorAll(`input[type="radio"][name="${groupName}"]`);
            const anyChecked = Array.from(radios).some(r => r.checked);
            if (!anyChecked) {
                // Show a single error for the whole group
                this.showRadioGroupError(groupName, 'Field ini wajib dipilih');
                return false;
            }
            // Clear group error when valid
            this.clearRadioGroupError(groupName);
            return true;
        }

        // For checkboxes (if any required)
        if (field.type === 'checkbox') {
            const groupName = field.name;
            const boxes = this.form.querySelectorAll(`input[type="checkbox"][name="${groupName}"]`);
            const anyChecked = Array.from(boxes).some(b => b.checked);
            if (!anyChecked) {
                this.showFieldError(field, 'Field ini wajib dipilih');
                return false;
            }
            this.clearFieldError(field);
            return true;
        }

        // For selects and text-like inputs
        const value = (field.value || '').trim();
        if (value === '') {
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
        if (!field) return;

        // Remove existing error
        this.clearFieldError(field);

        // Add error class to field
        field.classList.add('field-error');

        // Prefer a named [data-field] placeholder already in the DOM
        if (field.name && this.form) {
            const placeholder = this.form.querySelector(`[data-field="${field.name}"]`);
            if (placeholder) {
                placeholder.textContent = message;
                placeholder.classList.add('field-error-message');
                return;
            }
        }

        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error-message';
        errorElement.textContent = message;
        // Ensure it renders under the control column in grid layout
        errorElement.style.gridColumn = '2 / span 1';

        // Prefer rendering into a dedicated `.form-errors` container if present
        const formRow = field.closest && field.closest('.form-row');
        const errorContainer = formRow ? formRow.querySelector('.form-errors') : null;
        if (errorContainer) {
            const existing = errorContainer.querySelector('.field-error-message');
            if (existing) existing.remove();
            errorContainer.appendChild(errorElement);
        } else {
            // Fallback: insert after the field
            field.parentNode.insertBefore(errorElement, field.nextSibling);
        }
    }

    /**
     * Clear field error message
     */
    clearFieldError(field) {
        if (!field) return;

        // Remove error class
        field.classList.remove('field-error');

        // Clear any [data-field] placeholder associated with this field
        if (field.name && this.form) {
            const placeholder = this.form.querySelector(`[data-field="${field.name}"]`);
            if (placeholder) {
                placeholder.textContent = '';
                placeholder.classList.remove('field-error-message');
            }
        }

        // Remove error message, preferring `.form-errors` container when present
        const formRow = field.closest && field.closest('.form-row');
        const errorContainer = formRow ? formRow.querySelector('.form-errors') : null;
        if (errorContainer) {
            const messageEl = errorContainer.querySelector('.field-error-message');
            if (messageEl) messageEl.remove();
            return;
        }

        const errorElement = field.parentNode ? field.parentNode.querySelector('.field-error-message') : null;
        if (errorElement && !errorElement.hasAttribute('data-field')) {
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

        // Validate Legalisasi Nama (Question 110)
        const legalisasiNamaField = this.form.querySelector('input[name="legalisasi_nama"]');
        if (legalisasiNamaField && !this.validateLegalisasiNama(legalisasiNamaField)) {
            isValid = false;
            errors.push(`${this.getFieldLabel(legalisasiNamaField)}: Nama hanya boleh berisi huruf dan spasi`);
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
                    const nameAttr = e.target.getAttribute('name');
                    if (nameAttr) {
                        this.scheduleAutoSave(nameAttr, e.target.value);
                    }
                });

                // Fast auto-save when user moves to next field (blur event)
                input.addEventListener('blur', (e) => {
                    const nameAttr = e.target.getAttribute('name');
                    if (nameAttr) {
                        this.scheduleAutoSave(nameAttr, e.target.value, true); // immediate save
                    }
                });

                // Fast auto-save on delete/backspace
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' || e.key === 'Delete') {
                        const nameAttr = e.target.getAttribute('name');
                        if (nameAttr) {
                            // Schedule immediate save after deletion
                            setTimeout(() => {
                                this.scheduleAutoSave(nameAttr, e.target.value, true);
                            }, 50); // Small delay to capture the deleted value
                        }
                    }
                });
            }

            // For radio buttons and checkboxes
            if (['radio', 'checkbox'].includes(input.type)) {
                input.addEventListener('change', (e) => {
                    if (e.target.checked) {
                        const nameAttr = e.target.getAttribute('name');
                        if (nameAttr) {
                            this.scheduleAutoSave(nameAttr, e.target.value, true); // immediate save for selections
                        }
                    }
                });
            }
        });
    }

    /**
     * Schedule auto-save with debouncing
     */
    scheduleAutoSave(fieldName, fieldValue, immediate = false) {
        // Guard: skip invalid field names (e.g., display inputs without name)
        if (typeof fieldName !== 'string' || fieldName.trim() === '') {
            return;
        }
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
        // Guard against invalid field names
        if (typeof fieldName !== 'string' || fieldName.trim() === '') {
            return;
        }
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
                let valueStr = '';
                try {
                    valueStr = String(fieldValue ?? '').trim();
                } catch (_e) {
                    valueStr = '';
                }
                if (field && valueStr !== '') {
                    field.classList.add('field-valid');
                }

                // Notify sidebar of blok completion status and field value (UB survey)
                if (data.blok_completed !== undefined) {
                    document.dispatchEvent(new CustomEvent('ub:autosave', {
                        detail: {
                            blok_completed: data.blok_completed,
                            field: fieldName,
                            value: fieldValue,
                        }
                    }));
                }

                console.log('Auto-save successful:', data);
            } else {
                throw new Error(data.message || 'Auto-save failed');
            }

        } catch (error) {
            console.error('Auto-save error:', error);
            this.showStatus('Gagal menyimpan: ' + error.message, 'error');

            // Show error state on field if save failed
            let valueStr = '';
            try {
                valueStr = String(fieldValue ?? '').trim();
            } catch (_e) {
                valueStr = '';
            }
            if (field && valueStr !== '') {
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
            // Validate form before saving only when completing
            if (isCompleted) {
                const validation = this.validateFormBeforeSave();
                if (!validation.isValid) {
                    // Field-level errors are already displayed by validate* calls
                    // Provide compact guidance with top error details near submit
                    const details = (validation.errors || []).slice(0, 4);
                    this.showSubmissionGuidance('Mohon lengkapi semua field yang wajib diisi dengan benar', details);
                    this.scrollToFirstError();
                    return;
                }
            }

            // Get form data properly including all fields
            const formData = new FormData(this.form);
            // Preserve nested array names by submitting FormData directly
            // Append completion flag explicitly as string for backend parsing
            formData.append('is_completed', isCompleted ? '1' : '0');

            const statusMessage = isCompleted ? 'Menyimpan dan menyelesaikan...' : 'Menyimpan draft...';
            this.showStatus(statusMessage, 'info', true);

            // Ensure CSRF token is fresh (fallback to hidden input if meta missing)
            let csrfToken = this.getCSRFToken();
            if (!csrfToken) {
                const tokenField = this.form.querySelector('input[name="_token"]');
                csrfToken = tokenField ? tokenField.value : null;
            }
            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }

            const response = await fetch(this.options.saveAllUrl, {
                method: 'POST',
                headers: {
                    // Let the browser set correct multipart/form-data headers
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
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

                    // Highest priority: server-provided absolute redirect URL
                    if (result.redirect) {
                        nextUrl = result.redirect;
                    } else if (result.next_block) {
                        // Server provided specific next block
                        if (result.next_block === 'blok3a' && window.surveyRoutes?.blok3a) {
                            nextUrl = window.surveyRoutes.blok3a;
                        } else if (result.next_block === 'blok6' && window.surveyRoutes?.blok6) {
                            nextUrl = window.surveyRoutes.blok6;
                        } else if (result.next_block === 'blok3b_industri' && window.surveyRoutes?.blok3b_industri) {
                            nextUrl = window.surveyRoutes.blok3b_industri;
                        } else if (result.next_block === 'blok3b_nonindustri' && window.surveyRoutes?.blok3b_nonindustri) {
                            nextUrl = window.surveyRoutes.blok3b_nonindustri;
                        } else if (result.next_block === 'blok4') {
                            // Map Blok 4 either via explicit route or default nextBlok
                            nextUrl = (window.surveyRoutes && window.surveyRoutes.blok4) ? window.surveyRoutes.blok4 : (window.surveyRoutes ? window.surveyRoutes.nextBlok : null);
                        } else if (window.surveyRoutes && window.surveyRoutes[result.next_block]) {
                            // Generic mapping: if the route key exists, use it
                            nextUrl = window.surveyRoutes[result.next_block];
                        }
                        // Final fallback: server gave a next_block but no matching route key found — use nextBlok
                        if (!nextUrl && window.surveyRoutes?.nextBlok) {
                            nextUrl = window.surveyRoutes.nextBlok;
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
                    this.handleServerValidationErrors(result.errors || {});

                    // Build a compact summary of first few errors by field label
                    const toBracketName = (key) => {
                        if (!key || typeof key !== 'string') return key;
                        if (key.includes('[')) return key;
                        if (!key.includes('.')) return key;
                        const parts = key.split('.');
                        const root = parts.shift();
                        return root + '[' + parts.join('][') + ']';
                    };
                    const detailItems = Object.keys(result.errors)
                        .slice(0, 4)
                        .map((key) => {
                            const bracketKey = toBracketName(key);
                            const field = this.form.querySelector(`[name="${bracketKey}"]`) || this.form.querySelector(`[name="${key}"]`);
                            const label = field ? this.getFieldLabel(field) : key;
                            const msgRaw = Array.isArray(result.errors[key]) ? result.errors[key][0] : (result.errors[key] || 'Tidak valid');
                            return `${label}: ${msgRaw}`;
                        });
                    this.showSubmissionGuidance('Mohon lengkapi semua field yang wajib diisi dengan benar', detailItems);
                    this.scrollToFirstError();
                    this.showStatus('Terdapat kesalahan pada form. Mohon periksa kembali.', 'error');
                    return;
                } else if (result.redirect) {
                    // Cross-block validation failure: server asks us to redirect to a different block
                    this.showStatus(result.message || 'Terdapat bagian survei yang belum dilengkapi.', 'error');
                    if (typeof window.showCrossBlockModal === 'function') {
                        // Page-specific modal handler (e.g. blok3 defines this for a popup UX)
                        window.showCrossBlockModal(result);
                    } else {
                        // Fallback: inline banner + auto-redirect
                        const crossBlockEl = document.getElementById('crossBlockErr');
                        if (crossBlockEl) {
                            const msgEl = document.getElementById('crossBlockErrMsg');
                            if (msgEl) msgEl.textContent = result.message || '';
                            crossBlockEl.classList.remove('hidden');
                            crossBlockEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            let sec = 3;
                            const countdown = document.getElementById('crossBlockCountdown');
                            if (countdown) {
                                countdown.textContent = sec;
                                const iv = setInterval(() => {
                                    sec--;
                                    countdown.textContent = sec;
                                    if (sec <= 0) clearInterval(iv);
                                }, 1000);
                            }
                        }
                        setTimeout(() => { window.location.href = result.redirect; }, 3000);
                    }
                    return;
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
     * Show a brief guidance message near the submit button
     */
    showSubmissionGuidance(message, details = null) {
        if (!this.form) return;
        // Allow callers to disable this panel (e.g. UB views prefer inline-only errors)
        if (this.options.showGuidanceNearSubmit === false) return;

        // Prefer the complete/save button container
        const submitBtn = this.form.querySelector('#save-complete') || this.form.querySelector('button[type="submit"]');
        if (!submitBtn) return;

        const container = submitBtn.parentElement || submitBtn.closest('.form-actions') || this.form;
        let guidance = container.querySelector('.form-guidance-message');
        if (!guidance) {
            guidance = document.createElement('div');
            guidance.className = 'field-error-message form-guidance-message';
            // Place guidance just after the button container
            container.appendChild(guidance);
        }
        if (Array.isArray(details) && details.length) {
            const items = details.map(d => `• ${d}`).join('\n');
            guidance.innerHTML = `${message}<br><span style="display:block; white-space:pre-line; margin-top:4px;">${items}</span>`;
        } else {
            guidance.textContent = message;
        }
    }

    /**
     * Scroll to and focus the first field with an error
     */
    scrollToFirstError() {
        if (!this.form) return;

        // Prefer fields marked with error
        let firstErrorField = this.form.querySelector('.field-error');

        // Fallback: look for messages inside a `.form-errors` container
        if (!firstErrorField) {
            const containerMsg = this.form.querySelector('.form-errors .field-error-message');
            if (containerMsg) {
                const formRow = containerMsg.closest('.form-row');
                if (formRow) {
                    const candidate = formRow.querySelector('input, textarea, select');
                    if (candidate) firstErrorField = candidate; else firstErrorField = containerMsg;
                } else {
                    firstErrorField = containerMsg;
                }
            }
        }

        // Last fallback: any error message
        if (!firstErrorField) {
            const anyMsg = this.form.querySelector('.field-error-message');
            if (anyMsg) firstErrorField = anyMsg;
        }

        if (firstErrorField) {
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            if (typeof firstErrorField.focus === 'function') {
                firstErrorField.focus();
            }
        }
    }

    /**
     * Display server-side validation errors inline per field
     */
    handleServerValidationErrors(errors) {
        if (!this.form || !errors) return;

        // Helper: convert Laravel dot notation to bracket notation (blok1.nama -> blok1[nama])
        const toBracketName = (key) => {
            if (!key || typeof key !== 'string') return key;
            if (key.includes('[')) return key; // already in bracket form
            if (!key.includes('.')) return key;
            const parts = key.split('.');
            const root = parts.shift();
            return root + '[' + parts.join('][') + ']';
        };

        // Clear any existing errors first
        const errorFields = this.form.querySelectorAll('.field-error');
        errorFields.forEach(field => this.clearFieldError(field));
        const errorMsgs = this.form.querySelectorAll('.field-error-message');
        errorMsgs.forEach(msg => {
            if (msg.hasAttribute('data-field')) {
                // It's a static placeholder — clear text, remove class, but keep in DOM
                msg.textContent = '';
                msg.classList.remove('field-error-message');
            } else {
                msg.remove();
            }
        });

        Object.keys(errors).forEach((fieldKey) => {
            const messages = Array.isArray(errors[fieldKey]) ? errors[fieldKey] : [errors[fieldKey]];
            const bracketName = toBracketName(fieldKey);

            // Try to find the field by bracket notation first, then raw key
            let field = this.form.querySelector(`[name="${bracketName}"]`) || this.form.querySelector(`[name="${fieldKey}"]`);

            // If field corresponds to a radio group name, pick the first radio for placement
            if (!field) {
                const radio = this.form.querySelector(`input[type="radio"][name="${bracketName}"]`) || this.form.querySelector(`input[type="radio"][name="${fieldKey}"]`);
                if (radio) field = radio;
            }

            // If the backend validated a hidden field, prefer the paired visible display input when available
            if (field && field.type === 'hidden') {
                const displayInput = this.form.querySelector(`.currency-display[data-target-name="${bracketName}"]`) || this.form.querySelector(`.currency-display[data-target-name="${fieldKey}"]`);
                if (displayInput) field = displayInput;
            }

            if (field) {
                // Radio group special rendering (use group container when possible)
                if (field.type === 'radio') {
                    const groupName = field.name;
                    this.showRadioGroupError(groupName, messages[0]);
                    return;
                }

                this.showFieldError(field, messages[0]);
            }
        });
    }

    /**
     * Show error message for a radio group by name
     */
    showRadioGroupError(groupName, message) {
        if (!this.form || !groupName) return;

        // Prefer a named [data-field] placeholder already in the DOM
        const placeholder = this.form.querySelector(`[data-field="${groupName}"]`);
        if (placeholder) {
            placeholder.textContent = message;
            placeholder.classList.add('field-error-message');
            // Also highlight the radio group container
            const firstRadio = this.form.querySelector(`input[type="radio"][name="${groupName}"]`);
            const container = firstRadio && (firstRadio.closest('.radio-group') || firstRadio.closest('.ub-radio-group'));
            if (container) container.classList.add('radio-group-has-error');
            return;
        }

        const firstRadio = this.form.querySelector(`input[type="radio"][name="${groupName}"]`);
        // Support both .radio-group (SIBSTR) and .ub-radio-group (UB) containers
        const radioGroupContainer = firstRadio
            ? (firstRadio.closest('.radio-group') || firstRadio.closest('.ub-radio-group'))
            : null;
        if (radioGroupContainer && radioGroupContainer.parentNode) {
            // Remove existing group error near this container
            const existing = radioGroupContainer.parentNode.querySelector('.radio-group-error');
            if (existing) existing.remove();
            const errorElement = document.createElement('div');
            errorElement.className = 'field-error-message radio-group-error';
            errorElement.textContent = message;
            // Ensure grid placement in the control column
            errorElement.style.gridColumn = '2 / span 1';
            radioGroupContainer.parentNode.insertBefore(errorElement, radioGroupContainer.nextSibling);
        } else if (firstRadio) {
            // Fallback: show basic field error on the first radio
            this.showFieldError(firstRadio, message);
        }
    }

    /**
     * Clear radio group error message by name
     */
    clearRadioGroupError(groupName) {
        if (!this.form || !groupName) return;
        // Clear [data-field] placeholder
        const placeholder = this.form.querySelector(`[data-field="${groupName}"]`);
        if (placeholder) {
            placeholder.textContent = '';
            placeholder.classList.remove('field-error-message');
        }
        const firstRadio = this.form.querySelector(`input[type="radio"][name="${groupName}"]`);
        // Support both .radio-group (SIBSTR) and .ub-radio-group (UB) containers
        const radioGroupContainer = firstRadio
            ? (firstRadio.closest('.radio-group') || firstRadio.closest('.ub-radio-group'))
            : null;
        if (radioGroupContainer) {
            radioGroupContainer.classList.remove('radio-group-has-error');
            if (radioGroupContainer.parentNode) {
                const existing = radioGroupContainer.parentNode.querySelector('.radio-group-error');
                if (existing) existing.remove();
            }
        }
        // Also clear individual radio field-error classes
        const radios = this.form.querySelectorAll(`input[type="radio"][name="${groupName}"]`);
        radios.forEach(r => this.clearFieldError(r));
    }

    /**
     * Setup form validation
     */
    setupFormValidation() {
        const formInputs = this.form.querySelectorAll('input, textarea, select');

        formInputs.forEach(input => {
            // Apply light feedback on blur using HTML5 validity classes
            input.addEventListener('blur', (e) => {
                this.applyFieldValidityClass(e.target);
            });
        });
    }

    /**
     * Apply HTML5 validity classes without touching inline error messages
     * Keeping the method name distinct avoids overriding core validateField().
     */
    applyFieldValidityClass(field) {
        const isValid = field.checkValidity();
        field.classList.remove('field-valid', 'field-invalid');
        if ((field.value || '').trim() !== '') {
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
document.addEventListener('DOMContentLoaded', function () {
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
            statusUrl,
            showGuidanceNearSubmit: window.surveyRoutes?.showGuidanceNearSubmit !== false
        });
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyManager;
}
