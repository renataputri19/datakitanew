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

    // setupEventListeners is defined later in the class to include extended logic

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

        // Initialize conditional routing for R202 then enforce 204 required state
        this.updateR202ConditionalFlow();
        this.updateInformasiKantorPusatVisibility();

        // Initialize visibility for Tujuan Penggunaan Internet (R210a) and Teknologi Digital (R210b)
        this.updateInternetUsageVisibility();
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

    // ---- Custom conditional handlers for newly added questions ----
    updateInformasiKantorPusatVisibility() {
        // R204 shown only if R202 = b (pabrik_unit_produksi)
        const selectedJaringan = document.querySelector('input[name="jaringan_unit_kegiatan"]:checked');
        const infoRow = document.getElementById('informasi_kantor_pusat_row');
        if (!infoRow) return;

        const shouldShow = selectedJaringan && selectedJaringan.value === 'pabrik_unit_produksi';

        if (shouldShow) {
            infoRow.style.display = '';
            infoRow.style.opacity = '1';
            // Mark 204 label required and enforce required on sub-fields
            const label = infoRow.querySelector('.form-label');
            if (label) {
                label.classList.add('required');
            }
            const inputs = infoRow.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.required = true;
            });
        } else {
            // Hide and clear inputs, and auto-save empty values to clear server state
            infoRow.style.display = 'none';
            infoRow.style.opacity = '0';
            const inputs = infoRow.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                // Remove required state when hidden
                input.required = false;
                if (input.type === 'radio' || input.type === 'checkbox') {
                    input.checked = false;
                } else {
                    input.value = '';
                }
                this.clearFieldError(input);
                if (window.surveyManager) {
                    const name = input.name || '';
                    if (name.startsWith('info_kantor_pusat_')) {
                        window.surveyManager.scheduleAutoSave(name, '', true);
                    }
                }
            });
            // Remove 204 label required indicator
            const label = infoRow.querySelector('.form-label');
            if (label) {
                label.classList.remove('required');
            }
        }
    }

    // Handle visibility routing for R202 selections
    updateR202ConditionalFlow() {
        const selected = document.querySelector('input[name="jaringan_unit_kegiatan"]:checked');
        const q203Input = document.querySelector('input[name="jumlah_cabang_dan_unit_usaha"]');
        const q203Row = q203Input ? q203Input.closest('.form-row') : null;
        const q204Row = document.getElementById('informasi_kantor_pusat_row');

        const rows205to211 = this.getFormRowsByQuestionNumbers(['205', '206', '207', '208', '209', '210', '211']);

        const saveCompleteButton = document.getElementById('save-complete');
        const blok6Button = document.getElementById('go-to-blok6');

        const setRowVisible = (row, visible) => {
            if (!row) return;
            row.style.display = visible ? '' : 'none';
            row.style.opacity = visible ? '1' : '0';
            const inputs = row.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.dataset.originalRequired === undefined) {
                    input.dataset.originalRequired = input.required ? 'true' : 'false';
                }
                input.required = visible ? (input.dataset.originalRequired === 'true') : false;
                if (!visible) {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                    this.clearFieldError(input);
                    // Auto-save empty values to clear previously saved data for Q203/Q204
                    if (window.surveyManager) {
                        const name = input.name || '';
                        if (name === 'jumlah_cabang_dan_unit_usaha' || name.startsWith('info_kantor_pusat_')) {
                            window.surveyManager.scheduleAutoSave(name, '', true);
                        }
                    }
                }
            });
        };

        if (!selected) {
            setRowVisible(q203Row, true);
            setRowVisible(q204Row, false);
            rows205to211.forEach(row => setRowVisible(row, true));
            if (saveCompleteButton) saveCompleteButton.style.display = '';
            if (blok6Button) blok6Button.style.display = 'none';
            return;
        }

        switch (selected.value) {
            case 'tunggal':
                // Skip to 205: hide 203 and 204
                setRowVisible(q203Row, false);
                setRowVisible(q204Row, false);
                rows205to211.forEach(row => setRowVisible(row, true));
                if (saveCompleteButton) saveCompleteButton.style.display = '';
                if (blok6Button) blok6Button.style.display = 'none';
                break;

            case 'pabrik_unit_produksi':
                // Continue to 204: show 204, 203 not required
                setRowVisible(q203Row, false);
                setRowVisible(q204Row, true);
                rows205to211.forEach(row => setRowVisible(row, true));
                if (saveCompleteButton) saveCompleteButton.style.display = '';
                if (blok6Button) blok6Button.style.display = 'none';
                break;

            case 'pusat_ada_kegiatan_produksi':
            case 'kantor_pusat_administrasi_perwakilan':
                // Continue to 203: show 203 only
                setRowVisible(q203Row, true);
                setRowVisible(q204Row, false);
                rows205to211.forEach(row => setRowVisible(row, true));
                if (saveCompleteButton) saveCompleteButton.style.display = '';
                if (blok6Button) blok6Button.style.display = 'none';
                break;

            case 'unit_pembantu_penunjang':
                // Skip to Blok VI: hide 203, 204, and 205 onwards
                setRowVisible(q203Row, false);
                setRowVisible(q204Row, false);
                rows205to211.forEach(row => setRowVisible(row, false));
                if (saveCompleteButton) saveCompleteButton.style.display = 'none';
                if (blok6Button) blok6Button.style.display = '';
                break;
        }
    }

    // Utility: find all form rows by question number labels
    getFormRowsByQuestionNumbers(numbers) {
        const rows = Array.from(document.querySelectorAll('.form-row'));
        return rows.filter(row => {
            const q = row.querySelector('.question-number');
            if (!q) return false;
            const text = (q.textContent || '').trim().replace(/\.$/, '');
            return numbers.includes(text);
        });
    }

    updateInternetUsageVisibility() {
        // If R210 = "ya" show 210a and 210b, else hide and clear
        const selectedInternet = document.querySelector('input[name="penggunaan_internet"]:checked');
        const tujuanRow = document.getElementById('tujuan_penggunaan_internet_row');
        const techRow = document.getElementById('teknologi_digital_row');

        const shouldShow = selectedInternet && selectedInternet.value === 'ya';

        // Define radio group names for 210a and 210b
        const internetPurposeGroups = [
            'internet_a1_menerima_pesanan',
            'internet_a2_produksi',
            'internet_a3_distribusi',
            'internet_a4_beli_bahan_baku',
            'internet_a5_promosi',
            'internet_a6_lainnya'
        ];
        const techGroup = 'pemanfaatan_teknologi_digital';

        // Helper to set required on all radios in a group
        const setRadioGroupRequired = (groupName, required) => {
            const radios = document.querySelectorAll(`input[name="${groupName}"]`);
            radios.forEach(radio => {
                radio.required = !!required;
                // Attach validation listener when becoming required
                if (required) {
                    radio.addEventListener('change', () => this.validateRadioGroup(groupName));
                }
            });
            // Clear any existing group error when removing requirement
            if (!required) {
                this.clearRadioGroupError(groupName);
            }
        };

        [tujuanRow, techRow].forEach(row => {
            if (!row) return;
            if (shouldShow) {
                row.style.display = '';
                row.style.opacity = '1';
                const label = row.querySelector('.form-label');
                if (label) {
                    label.classList.add('required');
                }
            } else {
                row.style.display = 'none';
                row.style.opacity = '0';
                const inputs = row.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                    this.clearFieldError(input);
                });
                const label = row.querySelector('.form-label');
                if (label) {
                    label.classList.remove('required');
                }
            }
        });

        // Apply conditional required: when 210 = Ya, require 210a and 210b; otherwise not required
        internetPurposeGroups.forEach(group => setRadioGroupRequired(group, shouldShow));
        setRadioGroupRequired(techGroup, shouldShow);
    }

    // Attach listeners for conditional fields
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
            blok6Button.addEventListener('click', async (e) => {
                e.preventDefault();
                const nextUrl = window.surveyRoutes?.blok6;

                // Ensure kondisi_perusahaan is saved before redirecting
                const selectedKondisi = document.querySelector('input[name="kondisi_perusahaan"]:checked');
                // Also ensure R202 (jaringan_unit_kegiatan) is saved for Blok 6 back-navigation
                const selectedR202 = document.querySelector('input[name="jaringan_unit_kegiatan"]:checked');
                const saveAllUrl = window.surveyRoutes?.saveAll;

                // Prefer auto-save for individual fields to avoid full-form validation issues
                try {
                    if (window.surveyManager) {
                        if (selectedKondisi) {
                            window.surveyManager.scheduleAutoSave('kondisi_perusahaan', selectedKondisi.value, true);
                        }
                        if (selectedR202) {
                            window.surveyManager.scheduleAutoSave('jaringan_unit_kegiatan', selectedR202.value, true);
                        }
                    } else if (selectedKondisi && saveAllUrl) {
                        // Fallback to minimal save via saveAll for kondisi_perusahaan only
                        const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                        const csrfToken = csrfTokenMeta ? csrfTokenMeta.getAttribute('content') : null;
                        const formData = new FormData();
                        formData.append('kondisi_perusahaan', selectedKondisi.value);
                        await fetch(saveAllUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken || '',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });
                    }
                } catch (err) {
                    // Non-blocking: if save fails, still proceed to Blok 6
                    console.warn('Failed to auto-save before redirect:', err);
                }

                if (nextUrl) {
                    window.location.href = nextUrl;
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

        // Conditional: Informasi Kantor Pusat depends on R202
        const jaringanRadios = document.querySelectorAll('input[name="jaringan_unit_kegiatan"]');
        jaringanRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                // First handle overall flow and visibility
                this.updateR202ConditionalFlow();
                // Then enforce 204 required state and asterisk
                this.updateInformasiKantorPusatVisibility();
                // Auto-save selected R202 value to support navigation logic in Blok VI
                if (window.surveyManager) {
                    const selected = document.querySelector('input[name="jaringan_unit_kegiatan"]:checked');
                    if (selected) {
                        window.surveyManager.scheduleAutoSave('jaringan_unit_kegiatan', selected.value, true);
                    }
                }
            });
        });

        // Conditional: Internet usage (R210) controls 210a and 210b
        const internetRadios = document.querySelectorAll('input[name="penggunaan_internet"]');
        internetRadios.forEach(radio => {
            radio.addEventListener('change', () => this.updateInternetUsageVisibility());
        });
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
            
            // Show friendly guidance near the submit button
            this.showSubmissionGuidance('Mohon lengkapi semua field yang wajib diisi dengan benar');

            // Scroll to first error field or radio group error
            let firstErrorField = this.form.querySelector('.field-error');
            if (!firstErrorField) {
                const radioError = this.form.querySelector('.radio-group-error');
                if (radioError) {
                    radioError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    // Try to focus the first radio input in the related group
                    const groupContainer = radioError.previousElementSibling?.closest('.radio-group');
                    const firstRadio = groupContainer ? groupContainer.querySelector('input[type="radio"]') : null;
                    if (firstRadio) {
                        firstRadio.focus();
                    }
                    return;
                }
            }
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                if (typeof firstErrorField.focus === 'function') {
                    firstErrorField.focus();
                }
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

    // Guidance message near submit button
    showSubmissionGuidance(message) {
        if (!this.form) return;
        const submitBtn = this.form.querySelector('#save-complete');
        if (!submitBtn) return;
        const container = submitBtn.parentElement || submitBtn.closest('.form-actions') || this.form;
        let guidance = container.querySelector('.form-guidance-message');
        if (!guidance) {
            guidance = document.createElement('div');
            guidance.className = 'field-error-message form-guidance-message';
            container.appendChild(guidance);
        }
        guidance.textContent = message;
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
