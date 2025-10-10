/**
 * SIBSTR Survey Blok II Specific JavaScript
 * Handles validation and interactions specific to Blok II
 */

class SurveyBlok2Manager {
    constructor() {
        this.form = document.getElementById('survey-form');
        this.init();
    }

    init() {
        if (!this.form) return;

        this.setupEventListeners();
        this.setupValidation();
        this.setupNavigation();
        
        // Initialize conditional logic on page load
        this.initializeConditionalLogic();
    }

    setupEventListeners() {
        // Save and Complete button
        const saveCompleteButton = document.getElementById('save-complete');
        if (saveCompleteButton) {
            saveCompleteButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSaveComplete();
            });
        }

        // Back to Blok 1 button
        const backButton = document.getElementById('back-to-blok1');
        if (backButton) {
            backButton.addEventListener('click', () => {
                if (window.surveyRoutes?.backToBlok1) {
                    window.location.href = window.surveyRoutes.backToBlok1;
                }
            });
        }

        // Go to Blok 6 button
        const blok6Button = document.getElementById('go-to-blok6');
        if (blok6Button) {
            blok6Button.addEventListener('click', () => {
                if (window.surveyRoutes?.blok6) {
                    window.location.href = window.surveyRoutes.blok6;
                }
            });
        }

        // Kondisi Perusahaan change handler
        const kondisiPerusahaanRadios = document.querySelectorAll('input[name="kondisi_perusahaan"]');
        kondisiPerusahaanRadios.forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.handleKondisiPerusahaanChange(e.target.value);
            });
        });

        // KBLI input filtering - only allow digits
        const kbliField = document.getElementById('kbli_utama');
        if (kbliField) {
            kbliField.addEventListener('input', (e) => {
                // Only allow digits, limit to 5 characters
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 5) {
                    value = value.substring(0, 5);
                }
                e.target.value = value;
                
                // Validate in real-time
                this.validateKBLI(e.target);
            });

            kbliField.addEventListener('blur', (e) => {
                this.validateKBLI(e.target);
            });
        }

        // Numeric input filtering for rata-rata tenaga kerja
        const rataRataField = document.getElementById('rata_rata_tenaga_kerja');
        if (rataRataField) {
            rataRataField.addEventListener('input', (e) => {
                // Only allow digits
                e.target.value = e.target.value.replace(/\D/g, '');
                this.validateNumeric(e.target);
            });

            rataRataField.addEventListener('blur', (e) => {
                this.validateNumeric(e.target);
            });
        }
    }

    setupValidation() {
        // Real-time validation for required fields
        const requiredFields = this.form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (field.type === 'radio') {
                // For radio buttons, add validation to all buttons in the group
                const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
                radioGroup.forEach(radio => {
                    radio.addEventListener('change', () => {
                        this.validateRadioGroup(field.name);
                    });
                });
            } else {
                field.addEventListener('blur', () => {
                    this.validateField(field);
                });

                field.addEventListener('input', () => {
                    this.clearFieldError(field);
                });
            }
        });

        // Validation for text fields
        const textFields = this.form.querySelectorAll('input[type="text"], textarea');
        textFields.forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.clearFieldError(field));
        });
    }

    setupNavigation() {
        // Navigation logic if needed
    }

    initializeConditionalLogic() {
        // Check current kondisi perusahaan value and apply logic
        const checkedKondisi = document.querySelector('input[name="kondisi_perusahaan"]:checked');
        if (checkedKondisi) {
            this.handleKondisiPerusahaanChange(checkedKondisi.value);
        }
    }

    handleKondisiPerusahaanChange(value) {
        console.log('Kondisi perusahaan changed to:', value);

        // Get all conditional question rows by finding the form rows that contain these inputs
        const conditionalQuestions = [
            'jaringan_unit_kegiatan',
            'rata_rata_tenaga_kerja', 
            'kegiatan_utama_perusahaan',
            'kbli_utama'
        ];

        const shouldShowQuestions = value === 'masih_aktif';

        conditionalQuestions.forEach(questionName => {
            // Find the input element first
            const inputElement = document.querySelector(`input[name="${questionName}"], textarea[name="${questionName}"]`);
            
            if (inputElement) {
                // Find the parent form-row
                const formRow = inputElement.closest('.form-row');
                
                if (formRow) {
                    if (shouldShowQuestions) {
                        // Show the question
                        formRow.style.display = '';
                        formRow.style.opacity = '1';
                        
                        // Restore required validation
                        const inputs = formRow.querySelectorAll('input, textarea, select');
                        inputs.forEach(input => {
                            if (input.dataset.originalRequired === 'true') {
                                input.required = true;
                            }
                        });
                    } else {
                        // Hide the question
                        formRow.style.display = 'none';
                        formRow.style.opacity = '0';
                        
                        // Disable required validation and clear values
                        const inputs = formRow.querySelectorAll('input, textarea, select');
                        inputs.forEach(input => {
                            // Store original required state
                            input.dataset.originalRequired = input.required ? 'true' : 'false';
                            input.required = false;
                            
                            // Clear values and validation states
                            if (input.type === 'radio' || input.type === 'checkbox') {
                                input.checked = false;
                            } else {
                                input.value = '';
                            }
                            
                            // Clear any error messages
                            this.clearFieldError(input);
                        });
                    }
                }
            }
        });

        // If not "Masih Aktif", also clear any auto-saved data for hidden fields
        if (!shouldShowQuestions) {
            const fieldsToClear = ['jaringan_unit_kegiatan', 'rata_rata_tenaga_kerja', 'kegiatan_utama_perusahaan', 'kbli_utama'];
            fieldsToClear.forEach(fieldName => {
                // Auto-save empty values to clear the database
                if (window.surveyManager) {
                    window.surveyManager.scheduleAutoSave(fieldName, '', true);
                }
            });
        }

        // Update navigation buttons based on kondisi perusahaan
        this.updateNavigationButtons(value);
    }

    updateNavigationButtons(kondisiValue) {
        const saveCompleteButton = document.getElementById('save-complete');
        const blok6Button = document.getElementById('go-to-blok6');
        
        if (kondisiValue === 'masih_aktif') {
            // For "Masih Aktif", show save button, hide Blok 6 button
            if (saveCompleteButton) {
                saveCompleteButton.style.display = '';
                saveCompleteButton.textContent = 'Simpan dan Lanjutkan';
            }
            if (blok6Button) {
                blok6Button.style.display = 'none';
            }
        } else {
            // For other conditions, show Blok 6 button, hide save button
            if (saveCompleteButton) {
                saveCompleteButton.style.display = 'none';
            }
            if (blok6Button) {
                blok6Button.style.display = '';
            }
        }
    }

    // Field validation methods using the same approach as Blok 1
    validateField(field) {
        // Special handling for radio groups
        if (field.type === 'radio') {
            return this.validateRadioGroup(field.name);
        }

        // Special handling for KBLI field
        if (field.id === 'kbli_utama') {
            return this.validateKBLI(field);
        }

        // Special handling for numeric fields
        if (field.type === 'number' || field.id === 'rata_rata_tenaga_kerja') {
            return this.validateNumeric(field);
        }

        // Standard required field validation
        if (field.required && !this.isFieldFilled(field)) {
            this.showFieldError(field, 'Field ini wajib diisi');
            return false;
        }

        this.clearFieldError(field);
        return true;
    }

    validateRadioGroup(groupName) {
        const radioGroup = document.querySelectorAll(`input[name="${groupName}"]`);
        const isSelected = Array.from(radioGroup).some(radio => radio.checked);
        const firstRadio = radioGroup[0];

        if (!isSelected && firstRadio && firstRadio.required) {
            this.showRadioGroupError(groupName, 'Pilihan ini wajib dipilih');
            return false;
        }

        this.clearRadioGroupError(groupName);
        return true;
    }

    validateKBLI(field) {
        const value = field.value.trim();

        // Clear previous errors
        this.clearFieldError(field);

        if (value === '') {
            if (field.required) {
                this.showFieldError(field, 'Field ini wajib diisi');
                return false;
            }
            return true;
        }

        // KBLI must be exactly 5 digits
        if (!/^\d{5}$/.test(value)) {
            this.showFieldError(field, 'KBLI harus berupa 5 digit angka (contoh: 12345)');
            return false;
        }

        return true;
    }

    validateNumeric(field) {
        const value = field.value.trim();

        // Clear previous errors
        this.clearFieldError(field);

        if (value === '') {
            if (field.required) {
                this.showFieldError(field, 'Field ini wajib diisi');
                return false;
            }
            return true;
        }

        // Must be a valid positive number
        const numericValue = parseInt(value);
        if (isNaN(numericValue) || numericValue < 0) {
            this.showFieldError(field, 'Masukkan angka yang valid (minimal 0)');
            return false;
        }

        return true;
    }

    isFieldFilled(field) {
        if (field.type === 'radio') {
            const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
            return Array.from(radioGroup).some(radio => radio.checked);
        }
        
        if (field.type === 'checkbox') {
            return field.checked;
        }
        
        return field.value.trim() !== '';
    }

    // Field error display methods - same as Blok 1
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

    clearFieldError(field) {
        // Special handling for radio buttons
        if (field.type === 'radio') {
            this.clearRadioGroupError(field.name);
            return;
        }

        // Remove error class
        field.classList.remove('field-error');

        // Remove error message
        const errorElement = field.parentNode.querySelector('.field-error-message');
        if (errorElement) {
            errorElement.remove();
        }
    }

    // Radio group error display methods
    showRadioGroupError(groupName, message) {
        // Remove existing error
        this.clearRadioGroupError(groupName);

        // Find the radio group container
        const radioGroup = document.querySelectorAll(`input[name="${groupName}"]`);
        if (radioGroup.length === 0) return;

        // Note: Removed adding 'field-error' class to radio buttons for cleaner error display
        // radioGroup.forEach(radio => {
        //     radio.classList.add('field-error');
        // });

        // Find the radio group container (should be the parent div with class 'radio-group')
        const firstRadio = radioGroup[0];
        const radioGroupContainer = firstRadio.closest('.radio-group');

        if (radioGroupContainer) {
            // Create error message element
            const errorElement = document.createElement('div');
            errorElement.className = 'field-error-message radio-group-error';
            errorElement.textContent = message;

            // Insert error message after the radio group container
            radioGroupContainer.parentNode.insertBefore(errorElement, radioGroupContainer.nextSibling);
        }
    }

    clearRadioGroupError(groupName) {
        // Find the radio group
        const radioGroup = document.querySelectorAll(`input[name="${groupName}"]`);

        // Note: No longer need to remove 'field-error' class since we don't add it anymore
        // radioGroup.forEach(radio => {
        //     radio.classList.remove('field-error');
        // });

        // Remove error message
        const errorElement = document.querySelector('.radio-group-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    validateForm() {
        let isValid = true;
        const processedRadioGroups = new Set();

        // Get all required fields
        const requiredFields = this.form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (field.type === 'radio') {
                // Only validate each radio group once
                if (!processedRadioGroups.has(field.name)) {
                    processedRadioGroups.add(field.name);
                    if (!this.validateRadioGroup(field.name)) {
                        isValid = false;
                    }
                }
            } else {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            }
        });

        // Additional validation for KBLI format (even if field has value)
        const kbliField = document.getElementById('kbli_utama');
        if (kbliField && kbliField.value.trim() !== '') {
            if (!this.validateKBLI(kbliField)) {
                isValid = false;
            }
        }

        return isValid;
    }

    handleSaveComplete() {
        console.log('handleSaveComplete called');

        // Validate form first - if invalid, stop here
        if (!this.validateForm()) {
            console.log('Form validation failed, cannot save');
            
            // Scroll to first error field
            const firstErrorField = this.form.querySelector('.field-error');
            if (firstErrorField) {
                firstErrorField.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
                firstErrorField.focus();
            }
            
            return;
        }

        console.log('Form validation passed, proceeding to save');

        // Use the existing survey manager's save functionality
        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            window.surveyManager.saveForm(true); // true for completed
        } else {
            console.error('SurveyManager not available or saveForm method not found');
            alert('Terjadi kesalahan sistem. Silakan refresh halaman dan coba lagi.');
        }
    }

    getFieldLabel(field) {
        const label = field.closest('.form-row')?.querySelector('.form-label');
        if (label) {
            return label.textContent.trim().replace(/^\d+\.\s*/, '').replace(/\s*\*\s*$/, '');
        }
        return field.name || field.id || 'Field';
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on the Blok 2 page
    if (document.getElementById('survey-form') && window.location.pathname.includes('blok2')) {
        window.surveyBlok2Manager = new SurveyBlok2Manager();
        console.log('Survey Blok 2 Manager initialized');
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyBlok2Manager;
}
