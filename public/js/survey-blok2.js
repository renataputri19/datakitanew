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

        // Run R207 check immediately so pre-filled inconsistent data is flagged
        // without requiring the user to touch any field first.
        this.validateR207();
    }

    // setupEventListeners is defined later in the class to include extended logic

    setupValidation() {
        // Real-time validation for required fields
        const requiredFields = this.form.querySelectorAll('[required]');
        const processedRadioGroups = new Set();
        requiredFields.forEach(field => {
            if (field.type === 'radio') {
                // Only attach listeners once per group name to avoid duplicates
                if (!processedRadioGroups.has(field.name)) {
                    processedRadioGroups.add(field.name);
                    const radioGroup = document.querySelectorAll(`input[name="${field.name}"]`);
                    radioGroup.forEach(radio => {
                        radio.addEventListener('change', () => {
                            this.validateRadioGroup(field.name);
                        });
                    });
                }
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
        // Gate Blok 2 by Question 201 (Kondisi Perusahaan)
        const checkedKondisi = document.querySelector('input[name="kondisi_perusahaan"]:checked');
        if (checkedKondisi) {
            this.handleKondisiPerusahaanChange(checkedKondisi.value);
        } else {
            // No selection yet: hide 202+ to ensure 201 is answered first
            const q202Row = this.getFormRowsByQuestionNumbers(['202'])[0] || null;
            const q203Input = document.querySelector('input[name="jumlah_cabang_dan_unit_usaha"]');
            const q203Row = q203Input ? q203Input.closest('.form-row') : null;
            const q204Row = document.getElementById('informasi_kantor_pusat_row');
            const rows205to213 = this.getFormRowsByQuestionNumbers(['205', '206', '207', '208', '209', '210', '211', '212', '212a', '212b', '213']);
            this.setRowVisible(q202Row, false);
            this.setRowVisible(q203Row, false);
            this.setRowVisible(q204Row, false);
            rows205to213.forEach(row => this.setRowVisible(row, false));
            const saveCompleteButton = document.getElementById('save-complete');
            const blok6Button = document.getElementById('go-to-blok6');
            if (saveCompleteButton) saveCompleteButton.style.display = 'none';
            if (blok6Button) blok6Button.style.display = 'none';
        }

        // Initialize conditional routing only if perusahaan masih aktif
        if (checkedKondisi && checkedKondisi.value === 'masih_aktif') {
            this.updateR202ConditionalFlow();
            this.updateInformasiKantorPusatVisibility();
            this.updateInternetUsageVisibility();
        }
    }

    // Generic visibility helper to toggle a form row and manage required/clearing
    setRowVisible(row, visible, options = {}) {
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
                // Optional auto-save clearing for specific fields/prefixes
                if (window.surveyManager) {
                    const name = input.name || '';
                    if (Array.isArray(options.autoSaveNames) && options.autoSaveNames.includes(name)) {
                        window.surveyManager.scheduleAutoSave(name, '', true);
                    }
                    if (typeof options.autoSavePrefix === 'string' && name.startsWith(options.autoSavePrefix)) {
                        window.surveyManager.scheduleAutoSave(name, '', true);
                    }
                }
            }
        });
    }

    handleKondisiPerusahaanChange(value) {
        console.log('Kondisi perusahaan changed to:', value);
        const isActive = value === 'masih_aktif';

        const q202Row = this.getFormRowsByQuestionNumbers(['202'])[0] || null;
        const q203Input = document.querySelector('input[name="jumlah_cabang_dan_unit_usaha"]');
        const q203Row = q203Input ? q203Input.closest('.form-row') : null;
        const q204Row = document.getElementById('informasi_kantor_pusat_row');
        const rows205to213 = this.getFormRowsByQuestionNumbers(['205', '206', '207', '208', '209', '210', '211', '212', '212a', '212b', '213']);

        // Toggle rows based on kondisi perusahaan
        this.setRowVisible(q202Row, isActive, { autoSaveNames: ['jaringan_unit_kegiatan'] });
        this.setRowVisible(q203Row, isActive, { autoSaveNames: ['jumlah_cabang_dan_unit_usaha'] });
        this.setRowVisible(q204Row, isActive, { autoSavePrefix: 'info_kantor_pusat_' });
        rows205to213.forEach(row => this.setRowVisible(row, isActive));

        // Update navigation buttons immediately
        this.updateNavigationButtons(value);

        // When active, re-evaluate 202-based flows and internet usage visibility
        if (isActive) {
            this.updateR202ConditionalFlow();
            this.updateInformasiKantorPusatVisibility();
            this.updateInternetUsageVisibility();
        }
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
        // Respect 201: only relevant when perusahaan masih aktif
        const kondisi = document.querySelector('input[name="kondisi_perusahaan"]:checked');
        const infoRow = document.getElementById('informasi_kantor_pusat_row');
        if (!infoRow) return;
        if (!kondisi || kondisi.value !== 'masih_aktif') {
            this.setRowVisible(infoRow, false, { autoSavePrefix: 'info_kantor_pusat_' });
            return;
        }
        // R204 shown only if R202 = b (pabrik_unit_produksi)
        const selectedJaringan = document.querySelector('input[name="jaringan_unit_kegiatan"]:checked');

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
            // Hide and clear inputs, and auto-save empty values to clear server state
            this.setRowVisible(infoRow, false, { autoSavePrefix: 'info_kantor_pusat_' });
            // Remove 204 label required indicator
            const label = infoRow.querySelector('.form-label');
            if (label) {
                label.classList.remove('required');
            }
        }
    }

    // Handle visibility routing for R202 selections
    updateR202ConditionalFlow() {
        // Guard by 201: only run this flow when perusahaan masih aktif
        const kondisi = document.querySelector('input[name="kondisi_perusahaan"]:checked');
        const selected = document.querySelector('input[name="jaringan_unit_kegiatan"]:checked');
        const q202Row = this.getFormRowsByQuestionNumbers(['202'])[0] || null;
        const q203Input = document.querySelector('input[name="jumlah_cabang_dan_unit_usaha"]');
        const q203Row = q203Input ? q203Input.closest('.form-row') : null;
        const q204Row = document.getElementById('informasi_kantor_pusat_row');

        const rows205to213 = this.getFormRowsByQuestionNumbers(['205', '206', '207', '208', '209', '210', '211', '212', '212a', '212b', '213']);

        const saveCompleteButton = document.getElementById('save-complete');
        const blok6Button = document.getElementById('go-to-blok6');

        if (!kondisi || kondisi.value !== 'masih_aktif') {
            // Hide everything after 201, show Blok VI button
            this.setRowVisible(q202Row, false);
            this.setRowVisible(q203Row, false, { autoSaveNames: ['jumlah_cabang_dan_unit_usaha'] });
            this.setRowVisible(q204Row, false, { autoSavePrefix: 'info_kantor_pusat_' });
            rows205to213.forEach(row => this.setRowVisible(row, false));
            if (saveCompleteButton) saveCompleteButton.style.display = 'none';
            if (blok6Button) blok6Button.style.display = '';
            return;
        }

        if (!selected) {
            // No selection yet: show 202, hide 203 and 204, keep 205+ visible
            this.setRowVisible(q202Row, true);
            this.setRowVisible(q203Row, false);
            this.setRowVisible(q204Row, false);
            rows205to213.forEach(row => this.setRowVisible(row, true));
            if (saveCompleteButton) saveCompleteButton.style.display = '';
            if (blok6Button) blok6Button.style.display = 'none';
            return;
        }

        switch (selected.value) {
            case 'tunggal':
                // Skip to 205: hide 203 and 204
                this.setRowVisible(q203Row, false);
                this.setRowVisible(q204Row, false);
                rows205to213.forEach(row => this.setRowVisible(row, true));
                if (saveCompleteButton) saveCompleteButton.style.display = '';
                if (blok6Button) blok6Button.style.display = 'none';
                break;

            case 'pabrik_unit_produksi':
                // Continue to 204: show 204, 203 not required
                this.setRowVisible(q203Row, false);
                this.setRowVisible(q204Row, true);
                rows205to213.forEach(row => this.setRowVisible(row, true));
                if (saveCompleteButton) saveCompleteButton.style.display = '';
                if (blok6Button) blok6Button.style.display = 'none';
                break;

            case 'pusat_ada_kegiatan_produksi':
            case 'kantor_pusat_administrasi_perwakilan':
                // Continue to 203: show 203 only
                this.setRowVisible(q203Row, true);
                this.setRowVisible(q204Row, false);
                rows205to213.forEach(row => this.setRowVisible(row, true));
                if (saveCompleteButton) saveCompleteButton.style.display = '';
                if (blok6Button) blok6Button.style.display = 'none';
                break;

            case 'unit_pembantu_penunjang':
                // Skip to Blok VI: hide 203, 204, and 205 onwards
                this.setRowVisible(q203Row, false);
                this.setRowVisible(q204Row, false);
                rows205to213.forEach(row => this.setRowVisible(row, false));
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
        // Guard by 201: only relevant when perusahaan masih aktif
        const kondisi = document.querySelector('input[name="kondisi_perusahaan"]:checked');
        const tujuanRow = document.getElementById('tujuan_penggunaan_internet_row');
        const techRow = document.getElementById('teknologi_digital_row');
        if (!kondisi || kondisi.value !== 'masih_aktif') {
            [tujuanRow, techRow].forEach(row => {
                if (row) this.setRowVisible(row, false);
            });
            return;
        }

        // If R210 = "ya" show 210a and 210b, else hide and clear
        const selectedInternet = document.querySelector('input[name="penggunaan_internet"]:checked');

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
                this.setRowVisible(row, false);
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
        // Clone-and-replace to remove any listener attached by survey.js before ours,
        // so only blok2's handler fires on click (preventing double validation / duplicate errors).
        const saveCompleteOld = document.getElementById('save-complete');
        if (saveCompleteOld) {
            const saveCompleteButton = saveCompleteOld.cloneNode(true);
            saveCompleteOld.parentNode.replaceChild(saveCompleteButton, saveCompleteOld);
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

        // R207 consistency validation — real-time on every value change.
        // 'input'  : fires on each keystroke / spinner click / paste
        // 'change' : catches autofill, programmatic updates, and browser-native spinner
        //            interactions that some browsers only fire 'change' for (not 'input').
        const r207FieldIds = [
            'jumlah_seluruh_pekerja',
            'tenaga_kerja_laki_laki',
            'tenaga_kerja_perempuan',
            'pekerja_bukan_outsourcing_produksi',
            'pekerja_bukan_outsourcing_lainnya',
            'pekerja_outsourcing_produksi',
            'pekerja_outsourcing_lainnya'
        ];
        r207FieldIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input',  () => this.validateR207());
                el.addEventListener('change', () => this.validateR207());
            }
        });

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

        // Q210: clear group error when any sertifikasi field is filled
        const sertifikasiIds = [
            'sertifikasi_keamanan_produk', 'sertifikasi_kesehatan_keberlanjutan',
            'sertifikasi_kualitas_manajemen', 'sertifikasi_tidak_ada', 'sertifikasi_lainnya'
        ];
        sertifikasiIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', () => {
                    const q210Row = this.getFormRowsByQuestionNumbers(['210'])[0];
                    if (!q210Row) return;
                    const existing = q210Row.querySelector('.q210-group-error');
                    if (existing) existing.remove();
                    const textInputs = q210Row.querySelectorAll('input[type="text"]');
                    textInputs.forEach(f => f.classList.remove('field-error'));
                });
            }
        });

        // Q211: clear group error when any model industri checkbox is changed
        const modelCheckboxIds = [
            'model_industri_oem', 'model_industri_odm', 'model_industri_obm',
            'model_industri_tidak_ada', 'model_industri_lainnya_check'
        ];
        modelCheckboxIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', () => {
                    const q211Row = this.getFormRowsByQuestionNumbers(['211'])[0];
                    if (!q211Row) return;
                    const existing = q211Row.querySelector('.q211-group-error');
                    if (existing) existing.remove();
                    const radioGrp = q211Row.querySelector('.radio-group');
                    if (radioGrp) radioGrp.classList.remove('radio-group-has-error');
                });
            }
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
            this.showRadioGroupError(groupName, 'Field ini wajib dipilih');
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
        // Remove existing error for this specific group
        this.clearRadioGroupError(groupName);

        // Find the radio group container
        const radioGroup = document.querySelectorAll(`input[name="${groupName}"]`);
        if (radioGroup.length === 0) return;

        const firstRadio = radioGroup[0];
        const radioGroupContainer = firstRadio.closest('.radio-group');

        if (radioGroupContainer) {
            // Apply red outline to the group container
            radioGroupContainer.classList.add('radio-group-has-error');

            // Create error message element scoped to this group
            const errorElement = document.createElement('div');
            errorElement.className = 'field-error-message radio-group-error';
            errorElement.dataset.group = groupName;
            errorElement.textContent = message;

            // Insert error message after the radio group container
            radioGroupContainer.parentNode.insertBefore(errorElement, radioGroupContainer.nextSibling);
        }
    }

    clearRadioGroupError(groupName) {
        // Remove red outline from the group container
        const radioGroup = document.querySelectorAll(`input[name="${groupName}"]`);
        const firstRadio = radioGroup[0];
        if (firstRadio) {
            const radioGroupContainer = firstRadio.closest('.radio-group');
            if (radioGroupContainer) {
                radioGroupContainer.classList.remove('radio-group-has-error');
            }
        }

        // Remove only the error message scoped to this group (prevents cross-group removal)
        const errorElement = document.querySelector(`.radio-group-error[data-group="${groupName}"]`);
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

        // Q210: at least one sertifikasi field must be filled
        if (!this.validateQ210()) {
            isValid = false;
        }

        // Q211: at least one model industri checkbox must be checked
        if (!this.validateQ211()) {
            isValid = false;
        }

        // R207: konsistensi jumlah pekerja (edit rules)
        if (!this.validateR207()) {
            isValid = false;
        }

        return isValid;
    }

    validateQ210() {
        const q210Row = this.getFormRowsByQuestionNumbers(['210'])[0];
        if (!q210Row || q210Row.style.display === 'none') return true;

        const textInputs = q210Row.querySelectorAll('input[type="text"]');
        const anyFilled = Array.from(textInputs).some(f => f.value.trim() !== '');

        // Clear previous Q210 group error
        const existing = q210Row.querySelector('.q210-group-error');
        if (existing) existing.remove();
        textInputs.forEach(f => f.classList.remove('field-error'));

        if (!anyFilled) {
            textInputs.forEach(f => f.classList.add('field-error'));
            const subgrid = q210Row.querySelector('.form-subgrid');
            const anchor = subgrid || q210Row;
            const errorElement = document.createElement('div');
            errorElement.className = 'field-error-message q210-group-error';
            errorElement.textContent = 'Field ini wajib dipilih';
            anchor.parentNode.insertBefore(errorElement, anchor.nextSibling);
            return false;
        }
        return true;
    }

    validateQ211() {
        const q211Row = this.getFormRowsByQuestionNumbers(['211'])[0];
        if (!q211Row || q211Row.style.display === 'none') return true;

        const checkboxes = q211Row.querySelectorAll('input[type="checkbox"]');
        const anyChecked = Array.from(checkboxes).some(c => c.checked);

        // Clear previous Q211 group error
        const existing = q211Row.querySelector('.q211-group-error');
        if (existing) existing.remove();
        const radioGroupContainer = q211Row.querySelector('.radio-group');
        if (radioGroupContainer) radioGroupContainer.classList.remove('radio-group-has-error');

        if (!anyChecked) {
            if (radioGroupContainer) radioGroupContainer.classList.add('radio-group-has-error');
            const errorElement = document.createElement('div');
            errorElement.className = 'field-error-message q211-group-error';
            errorElement.textContent = 'Field ini wajib dipilih';
            const anchor = radioGroupContainer || q211Row.querySelector('.form-label');
            if (anchor) {
                anchor.parentNode.insertBefore(errorElement, anchor.nextSibling);
            }
            return false;
        }
        return true;
    }

    // ═══════════════════════════════════════════════════════════════
    // R207 Consistency Validation (Edit Rules — Rincian 207)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Validate R207 consistency rules:
     *   Rule 1 (jenis kelamin) : b1 + b2 = a
     *   Rule 2 (status pekerja): (c1 + c2) + (d1 + d2) = a
     * Only triggers when all operands in a rule are filled.
     * Returns true when both rules pass (or the section is hidden / not rendered).
     */
    validateR207() {
        const fieldA = document.getElementById('jumlah_seluruh_pekerja');
        // Section absent (triwulanan mode) or hidden (perusahaan tidak aktif) — skip
        if (!fieldA || fieldA.closest('.form-row')?.style.display === 'none') {
            this.clearR207Errors();
            return true;
        }

        const getVal = (id) => {
            const el = document.getElementById(id);
            if (!el || el.value.trim() === '') return null;
            const v = parseInt(el.value, 10);
            return isNaN(v) ? null : v;
        };

        const a  = getVal('jumlah_seluruh_pekerja');
        const b1 = getVal('tenaga_kerja_laki_laki');
        const b2 = getVal('tenaga_kerja_perempuan');
        const c1 = getVal('pekerja_bukan_outsourcing_produksi');
        const c2 = getVal('pekerja_bukan_outsourcing_lainnya');
        const d1 = getVal('pekerja_outsourcing_produksi');
        const d2 = getVal('pekerja_outsourcing_lainnya');

        let rule1Fail = false;
        let rule2Fail = false;

        // ── Rule 1: b1 + b2 = a (jenis kelamin) ──────────────────────
        // Trigger as soon as 'a' is known and at least one of b1/b2 is filled:
        //   • if both b1 and b2 are filled → full equality check
        //   • if only one is filled but already exceeds 'a' → early warning
        if (a !== null && (b1 !== null || b2 !== null)) {
            const sumB = (b1 ?? 0) + (b2 ?? 0);
            const allFilled = b1 !== null && b2 !== null;
            const earlyExceed = !allFilled && sumB > a;

            if (allFilled && sumB !== a) {
                rule1Fail = true;
                const msg = `Validasi jenis kelamin: laki-laki (${b1}) + perempuan (${b2}) = ${sumB}, harus sama dengan jumlah seluruh pekerja (${a}).`;
                this.showR207Error('r207-rule1-error', 'tenaga_kerja_perempuan', msg);
            } else if (earlyExceed) {
                rule1Fail = true;
                const filled = b1 !== null ? `laki-laki (${b1})` : `perempuan (${b2})`;
                const msg = `Validasi jenis kelamin: ${filled} sudah melebihi jumlah seluruh pekerja (${a}).`;
                this.showR207Error('r207-rule1-error', 'tenaga_kerja_perempuan', msg);
            } else {
                this.clearR207ErrorById('r207-rule1-error');
            }
        } else {
            this.clearR207ErrorById('r207-rule1-error');
        }

        // ── Rule 2: (c1+c2) + (d1+d2) = a (status pekerja) ──────────
        // Trigger as soon as 'a' is known and at least one of c1/c2/d1/d2 is filled.
        if (a !== null && (c1 !== null || c2 !== null || d1 !== null || d2 !== null)) {
            const sumCD = (c1 ?? 0) + (c2 ?? 0) + (d1 ?? 0) + (d2 ?? 0);
            const allFilled = c1 !== null && c2 !== null && d1 !== null && d2 !== null;
            const earlyExceed = !allFilled && sumCD > a;

            if (allFilled && sumCD !== a) {
                rule2Fail = true;
                const nonOs = c1 + c2;
                const os    = d1 + d2;
                const msg = `Validasi status pekerja: bukan outsourcing (${nonOs}) + outsourcing (${os}) = ${sumCD}, harus sama dengan jumlah seluruh pekerja (${a}).`;
                this.showR207Error('r207-rule2-error', 'pekerja_outsourcing_lainnya', msg);
            } else if (earlyExceed) {
                rule2Fail = true;
                const msg = `Validasi status pekerja: subtotal yang terisi (${sumCD}) sudah melebihi jumlah seluruh pekerja (${a}).`;
                this.showR207Error('r207-rule2-error', 'pekerja_outsourcing_lainnya', msg);
            } else {
                this.clearR207ErrorById('r207-rule2-error');
            }
        } else {
            this.clearR207ErrorById('r207-rule2-error');
        }

        // Update sorotan merah pada semua field terkait
        this._updateR207FieldHighlights(rule1Fail, rule2Fail);

        return !rule1Fail && !rule2Fail;
    }

    /**
     * Tampilkan pesan error konsistensi R207 setelah blok field yang relevan.
     * anchorId: ID field terakhir dalam grup (b.2 atau d.2) — dipakai sebagai
     * titik navigasi untuk menyisipkan error di level subgrid grupnya.
     */
    showR207Error(errorId, anchorId, message) {
        this.clearR207ErrorById(errorId);

        const anchor = document.getElementById(anchorId);
        if (!anchor) return;

        // Struktur DOM: anchor → .form-subrow (b.2/d.2) → .form-subgrid (grup b/d)
        // → .form-subrow (header "b." / "d."). Sisipkan error setelah .form-subgrid.
        const innerSubrow  = anchor.closest('.form-subrow');
        const groupSubgrid = innerSubrow?.parentElement;   // .form-subgrid grup b atau d
        const insertAfter  = groupSubgrid || innerSubrow;

        const errorEl = document.createElement('div');
        errorEl.id        = errorId;
        errorEl.className = 'field-error-message r207-consistency-error';
        errorEl.setAttribute('role', 'alert');
        errorEl.textContent = message;

        if (insertAfter?.parentNode) {
            insertAfter.parentNode.insertBefore(errorEl, insertAfter.nextSibling);
        }
    }

    clearR207ErrorById(errorId) {
        const el = document.getElementById(errorId);
        if (el) el.remove();
    }

    clearR207Errors() {
        this.clearR207ErrorById('r207-rule1-error');
        this.clearR207ErrorById('r207-rule2-error');
        this._updateR207FieldHighlights(false, false);
    }

    /** Tandai/hapus sorotan merah field-field R207 sesuai hasil kedua aturan. */
    _updateR207FieldHighlights(rule1Fail, rule2Fail) {
        const toggle = (id, hasError) => {
            const el = document.getElementById(id);
            if (el) el.classList.toggle('field-error', hasError);
        };
        // Field 'a' dipakai kedua aturan — merah jika salah satunya gagal
        toggle('jumlah_seluruh_pekerja',             rule1Fail || rule2Fail);
        // Rule 1
        toggle('tenaga_kerja_laki_laki',             rule1Fail);
        toggle('tenaga_kerja_perempuan',             rule1Fail);
        // Rule 2
        toggle('pekerja_bukan_outsourcing_produksi', rule2Fail);
        toggle('pekerja_bukan_outsourcing_lainnya',  rule2Fail);
        toggle('pekerja_outsourcing_produksi',       rule2Fail);
        toggle('pekerja_outsourcing_lainnya',        rule2Fail);
    }

    handleSaveComplete() {
        console.log('handleSaveComplete called');

        // Remove any previous validation summary
        const existingSummary = document.getElementById('blok2-validation-summary');
        if (existingSummary) existingSummary.remove();

        // Validate form first - if invalid, stop here
        if (!this.validateForm()) {
            console.log('Form validation failed, cannot save');

            // Build and inject validation summary panel before form actions (same as blok3a)
            const errors = this.collectValidationErrors();
            if (errors.length > 0) {
                const esc = s => s.replace(/[&<>]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;'}[c]));
                const summaryHTML = `
                <div id="blok2-validation-summary" class="validation-summary">
                    <div class="validation-summary-header">
                        <span class="validation-summary-icon">&#9888;</span>
                        <h4 class="validation-summary-title">Data belum lengkap</h4>
                    </div>
                    <p class="validation-summary-desc">Mohon lengkapi bidang berikut sebelum menyimpan:</p>
                    <ul class="validation-summary-list">
                        ${errors.map(e => `<li class="validation-summary-item">${esc(e)}</li>`).join('')}
                    </ul>
                </div>`;
                const formActions = this.form.querySelector('.form-actions');
                if (formActions) {
                    formActions.insertAdjacentHTML('beforebegin', summaryHTML);
                    document.getElementById('blok2-validation-summary')
                        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                // Fallback scroll to first error if no label could be collected
                const firstError = this.form.querySelector('.field-error, .radio-group-has-error');
                if (firstError) firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            return;
        }

        console.log('Form validation passed, proceeding to save');

        // Use the existing survey manager's save functionality
        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            // Ensure fallback next route points to Blok 3A when perusahaan masih aktif
            const kondisi = document.querySelector('input[name="kondisi_perusahaan"]:checked');
            if (kondisi && kondisi.value === 'masih_aktif' && window.surveyRoutes && window.surveyRoutes.blok3a) {
                window.surveyRoutes.nextBlok = window.surveyRoutes.blok3a;
            }
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

    collectValidationErrors() {
        const errors = [];
        const seen = new Set();
        const addLabel = (label) => { if (label && !seen.has(label)) { seen.add(label); errors.push(label); } };

        const getLabel = (el) => {
            const subrow = el.closest('.form-subrow');
            if (subrow) {
                const sublabel = subrow.querySelector('.form-sublabel');
                if (sublabel) {
                    let text = sublabel.textContent.trim();
                    // Truncate long parenthetical notes
                    const parenIdx = text.indexOf('(');
                    if (parenIdx > 30) text = text.substring(0, parenIdx).trimEnd();
                    if (text.length > 80) text = text.substring(0, 80).trimEnd() + '\u2026';
                    const row = subrow.closest('.form-row');
                    const qNum = row?.querySelector('.question-number')?.textContent?.trim();
                    return qNum ? qNum + ' ' + text : text;
                }
            }
            const row = el.closest('.form-row');
            if (row) {
                const formLabel = row.querySelector('.form-label');
                if (formLabel) {
                    const qNum = formLabel.querySelector('.question-number')?.textContent?.trim() ?? '';
                    const titleSpans = formLabel.querySelectorAll('span:not(.question-number)');
                    let title = titleSpans.length > 0 ? titleSpans[0].textContent.trim() : '';
                    const parenIdx = title.indexOf('(');
                    if (parenIdx > 30) title = title.substring(0, parenIdx).trimEnd();
                    if (title.length > 70) title = title.substring(0, 70).trimEnd() + '\u2026';
                    return (qNum + ' ' + title).trim();
                }
            }
            return null;
        };

        // 1. Radio / checkbox group container errors
        this.form.querySelectorAll('.radio-group-has-error').forEach(container => addLabel(getLabel(container)));

        // 2. Text / number / textarea field errors (skip bare radio inputs)
        this.form.querySelectorAll('.field-error').forEach(field => {
            if (field.classList.contains('radio-input') || field.type === 'radio') return;
            addLabel(getLabel(field));
        });

        // 3. Q210 sertifikasi group (at-least-one)
        if (this.form.querySelector('.q210-group-error')) {
            const row = this.getFormRowsByQuestionNumbers(['210'])[0];
            if (row) addLabel(getLabel(row));
        }

        // 4. Q211 model industri group (at-least-one)
        if (this.form.querySelector('.q211-group-error')) {
            const row = this.getFormRowsByQuestionNumbers(['211'])[0];
            if (row) addLabel(getLabel(row));
        }

        // 5. R207 konsistensi jenis kelamin (Rule 1)
        if (document.getElementById('r207-rule1-error')) {
            addLabel('207 Validasi jenis kelamin: b.1 (laki-laki) + b.2 (perempuan) \u2260 a (jumlah seluruh pekerja)');
        }

        // 6. R207 konsistensi status pekerja (Rule 2)
        if (document.getElementById('r207-rule2-error')) {
            addLabel('207 Validasi status pekerja: (c.1 + c.2) + (d.1 + d.2) \u2260 a (jumlah seluruh pekerja)');
        }

        return errors;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
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
