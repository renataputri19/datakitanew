/**
 * Blok 3B Industri (SIBSTR) client interactions
 * - Currency display inputs synced to hidden numeric fields
 * - Auto-save via SurveyManager for each field change
 * - Positive-only validation for all currency fields
 */

class SurveyBlok3bIndustriManager {
    constructor() {
        this.form = document.getElementById('survey-form');
        if (!this.form) return;

        this.currencyDisplays = Array.from(this.form.querySelectorAll('.currency-display'));
        // Use generic percent-input class to include q305_online, q314, q315, q319a-f
        this.percentInputs = Array.from(this.form.querySelectorAll('.percent-input'));

        this.setupEventListeners();
        this.initializeDisplayValues();
        this.updateInventoryTotals();
        this.updateAssetTotal();
        this.updateAssetRequiredIndicators();
        this.updateOwnershipTotal();
        this.updateWorkerTotals();
    }

    setupEventListeners() {
        // Currency display inputs: positive-only validation, sync to hidden numeric inputs, and auto-save
        this.currencyDisplays.forEach(input => {
            input.addEventListener('input', (e) => {
                const targetName = e.target.getAttribute('data-target-name');
                if (!targetName) return;

                const raw = e.target.value;
                // Show error and strip negative signs immediately
                if (/-/.test(String(raw))) {
                    this.showFieldError(e.target, 'Nilai tidak boleh negatif');
                    e.target.value = String(raw).replace(/-/g, '');
                }

                // Sanitize and validate numeric value (positive-only)
                const numericValue = this.parseCurrencyToNumber(e.target.value);
                const hidden = this.form.querySelector(`input[type="hidden"][name="${targetName}"]`);
                if (hidden) {
                    if (numericValue === null) {
                        // Keep hidden empty and show error for invalid input
                        hidden.value = '';
                        this.showFieldError(e.target, 'Nilai tidak boleh negatif');
                    } else {
                        // Valid value: clear error, sync, and auto-save
                        this.clearFieldError(e.target);
                        hidden.value = numericValue;
                        if (window.surveyManager) {
                            window.surveyManager.scheduleAutoSave(targetName, numericValue);
                        }
                    }
                }

                // Recompute totals when relevant inputs change
                if (targetName && /\[q30[678]_(awal|akhir)\]/.test(targetName)) {
                    this.updateInventoryTotals();
                }
                if (targetName && /\[q318(a|b)\]/.test(targetName)) {
                    this.updateAssetTotal();
                    this.updateAssetRequiredIndicators();
                }
                if (targetName && /\[q313_(a1|a2|b1|b2)\]/.test(targetName)) {
                    this.updateWorkerTotal313();
                }
                if (targetName && /\[q314_(a1|a2|b1|b2)\]/.test(targetName)) {
                    this.updateWorkerTotal314();
                }
            });

            input.addEventListener('blur', (e) => {
                // Format for display using Indonesian locale
                const numericValue = this.parseCurrencyToNumber(e.target.value);
                if (numericValue === null) {
                    // Keep empty and show error
                    e.target.value = '';
                    this.showFieldError(e.target, 'Nilai tidak boleh negatif');
                } else {
                    // Valid: clear errors and format display
                    this.clearFieldError(e.target);
                    e.target.value = this.formatCurrencyDisplay(numericValue);
                }
            });
        });

        // Percent inputs: enforce bounds, show errors, and auto-save
        this.percentInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                let v = e.target.value;
                const fieldName = e.target.getAttribute('name');
                if (v === '') {
                    this.clearFieldError(e.target);
                    if (window.surveyManager) {
                        window.surveyManager.scheduleAutoSave(fieldName, '', true);
                    }
                    // Update ownership total when any percent input cleared
                    if (/blok3b_industri\[q319[abcdefgh]\]/.test(fieldName)) {
                        this.updateOwnershipTotal();
                    }
                    return;
                }
                // Allow decimals, clamp to [0, 100]
                v = String(v).replace(/[^0-9.,-]/g, '');
                v = v.replace(',', '.');
                let num = parseFloat(v);
                if (isNaN(num)) num = 0;
                let errorMsg = null;
                if (String(v).includes('-') || num < 0) {
                    errorMsg = 'Nilai tidak boleh negatif';
                } else if (num > 100) {
                    errorMsg = 'Nilai maksimal 100';
                }

                if (num < 0) num = 0;
                if (num > 100) num = 100;
                e.target.value = num;
                if (errorMsg) {
                    this.showFieldError(e.target, errorMsg);
                } else {
                    this.clearFieldError(e.target);
                    if (window.surveyManager) {
                        window.surveyManager.scheduleAutoSave(fieldName, String(num));
                    }
                    // Update ownership total when one of 319a-h changed
                    if (/blok3b_industri\[q319[abcdefgh]\]/.test(fieldName)) {
                        this.updateOwnershipTotal();
                    }
                }
            });
        });

        // Save buttons are handled by SurveyManager globally; we rely on that

        // Ensure Q318.d (area) is always required and show required indicator
        const areaInput = this.form.querySelector('#q318d_area');
        if (areaInput) {
            areaInput.required = true;
            const areaLabel = this.form.querySelector('label[for="q318d_area"]');
            if (areaLabel) areaLabel.classList.add('required');
        }
    }

    initializeDisplayValues() {
        // Populate display inputs from their hidden numeric counterparts
        this.currencyDisplays.forEach(display => {
            const targetName = display.getAttribute('data-target-name');
            if (!targetName) return;
            const hidden = this.form.querySelector(`input[type="hidden"][name="${targetName}"]`);
            if (hidden && hidden.value !== '') {
                const num = Number(hidden.value);
                if (!isNaN(num)) {
                    display.value = this.formatCurrencyDisplay(num);
                }
            }
        });
    }

    parseCurrencyToNumber(raw) {
        if (raw === undefined || raw === null) return null;
        const s = String(raw).trim();
        if (s === '') return null;
        // Remove thousand separators and normalize decimal
        const normalized = s.replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.\-]/g, '');
        // Disallow negative values
        if (normalized.includes('-')) return null;
        const num = parseFloat(normalized);
        if (isNaN(num)) return null;
        if (num < 0) return null;
        // Return up to 2 decimals precision as string for backend numeric parsing
        return Number(num.toFixed(2)).toString();
    }

    formatCurrencyDisplay(num) {
        try {
            const n = typeof num === 'number' ? num : parseFloat(num);
            if (isNaN(n)) return '';
            // Indonesian locale style: thousand separator '.', decimal ','
            return n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        } catch (_e) {
            return String(num);
        }
    }

    getHiddenValue(name) {
        const hidden = this.form.querySelector(`input[type="hidden"][name="${name}"]`);
        if (!hidden) return 0;
        const v = hidden.value;
        if (v === '' || v === null || v === undefined) return 0;
        const num = parseFloat(String(v));
        return isNaN(num) ? 0 : num;
    }

    setHiddenAndDisplay(name, value, displaySelectorId = null) {
        const hidden = this.form.querySelector(`input[type="hidden"][name="${name}"]`);
        if (hidden) hidden.value = Number(value).toFixed(2);
        if (displaySelectorId) {
            const disp = document.getElementById(displaySelectorId);
            if (disp) disp.value = this.formatCurrencyDisplay(value);
        }
    }

    updateInventoryTotals() {
        const awal = this.getHiddenValue('blok3b_industri[q306_awal]')
                   + this.getHiddenValue('blok3b_industri[q307_awal]')
                   + this.getHiddenValue('blok3b_industri[q308_awal]');
        const akhir = this.getHiddenValue('blok3b_industri[q306_akhir]')
                    + this.getHiddenValue('blok3b_industri[q307_akhir]')
                    + this.getHiddenValue('blok3b_industri[q308_akhir]');
        this.setHiddenAndDisplay('blok3b_industri[q309_awal]', awal, 'q309_awal_display');
        this.setHiddenAndDisplay('blok3b_industri[q309_akhir]', akhir, 'q309_akhir_display');
        const hidAwal = document.getElementById('q309_awal_val');
        if (hidAwal) hidAwal.value = Number(awal).toFixed(2);
        const hidAkhir = document.getElementById('q309_akhir_val');
        if (hidAkhir) hidAkhir.value = Number(akhir).toFixed(2);
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_industri[q309_awal]', Number(awal).toFixed(2), true);
            window.surveyManager.scheduleAutoSave('blok3b_industri[q309_akhir]', Number(akhir).toFixed(2), true);
        }
    }

    updateAssetTotal() {
        const a = this.getHiddenValue('blok3b_industri[q318a]');
        const b = this.getHiddenValue('blok3b_industri[q318b]');
        const total = a + b;
        this.setHiddenAndDisplay('blok3b_industri[q318c]', total, 'q318c_display');
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_industri[q318c]', Number(total).toFixed(2), true);
        }
    }

    // Toggle red asterisks on Q318a/b labels only when both are empty
    updateAssetRequiredIndicators() {
        const hiddenA = this.form.querySelector('input[type="hidden"][name="blok3b_industri[q318a]"]');
        const hiddenB = this.form.querySelector('input[type="hidden"][name="blok3b_industri[q318b]"]');
        const labelA = this.form.querySelector('label[for="q318a_display"]');
        const labelB = this.form.querySelector('label[for="q318b_display"]');
        if (!hiddenA || !hiddenB || !labelA || !labelB) return;
        const bothEmpty = (!hiddenA.value || hiddenA.value.trim() === '') && (!hiddenB.value || hiddenB.value.trim() === '');
        if (bothEmpty) {
            labelA.classList.add('required');
            labelB.classList.add('required');
        } else {
            labelA.classList.remove('required');
            labelB.classList.remove('required');
        }
    }

    updateOwnershipTotal() {
        const keys = ['a','b','c','d','e','f','g','h'];
        let sum = 0;
        keys.forEach(k => {
            const input = this.form.querySelector(`input[name="blok3b_industri[q319${k}]"]`);
            const v = input ? parseFloat(input.value) : 0;
            if (!isNaN(v)) sum += v;
        });
        // Clamp to 100 for display but save actual sum
        const disp = document.getElementById('q319i_display');
        if (disp) disp.value = Math.min(100, Math.max(0, sum));
        const hidden = this.form.querySelector('input[type="hidden"][name="blok3b_industri[q319i]"]');
        if (hidden) hidden.value = Number(sum).toFixed(2);
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_industri[q319i]', Number(sum).toFixed(2), true);
        }
    }

    updateWorkerTotals() {
        this.updateWorkerTotal313();
        this.updateWorkerTotal314();
    }

    updateWorkerTotal313() {
        const a1 = this.getHiddenValue('blok3b_industri[q313_a1]');
        const a2 = this.getHiddenValue('blok3b_industri[q313_a2]');
        const b1 = this.getHiddenValue('blok3b_industri[q313_b1]');
        const b2 = this.getHiddenValue('blok3b_industri[q313_b2]');
        const total = a1 + a2 + b1 + b2;
        this.setHiddenAndDisplay('blok3b_industri[q313_c]', total, 'q313_c_display');
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_industri[q313_c]', Number(total).toFixed(2), true);
        }
    }

    updateWorkerTotal314() {
        const a1 = this.getHiddenValue('blok3b_industri[q314_a1]');
        const a2 = this.getHiddenValue('blok3b_industri[q314_a2]');
        const b1 = this.getHiddenValue('blok3b_industri[q314_b1]');
        const b2 = this.getHiddenValue('blok3b_industri[q314_b2]');
        const total = a1 + a2 + b1 + b2;
        this.setHiddenAndDisplay('blok3b_industri[q314_c]', total, 'q314_c_display');
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_industri[q314_c]', Number(total).toFixed(2), true);
        }
    }

    // Inline field error helpers (consistent with other survey forms)
    showFieldError(field, message) {
        // If the backend/validator points to a hidden field, route error to its visible display input
        let targetField = field;
        if (field && field.type === 'hidden') {
            const bracketName = field.getAttribute('name');
            const displayInput = this.form.querySelector(`.currency-display[data-target-name="${bracketName}"]`);
            if (displayInput) targetField = displayInput;
        }

        // Clear any existing error first
        this.clearFieldError(targetField);
        // Add error class to field for visual feedback
        targetField.classList.add('field-error');
        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error-message';
        errorElement.textContent = message;
        // Prefer rendering into the dedicated left-side error container
        const formRow = targetField.closest('.form-row');
        const errorContainer = formRow ? formRow.querySelector('.form-errors') : null;
        if (errorContainer) {
            // Clear any existing message in the container first to avoid duplicates
            const existing = errorContainer.querySelector('.field-error-message');
            if (existing) existing.remove();
            errorContainer.appendChild(errorElement);
        } else {
            // Fallback: insert after the field if container not found
            targetField.parentNode.insertBefore(errorElement, targetField.nextSibling);
        }
    }

    clearFieldError(field) {
        field.classList.remove('field-error');
        const formRow = field.closest('.form-row');
        const errorContainer = formRow ? formRow.querySelector('.form-errors') : null;
        if (errorContainer) {
            const existing = errorContainer.querySelector('.field-error-message');
            if (existing) existing.remove();
            return;
        }
        const fallback = field.parentNode.querySelector('.field-error-message');
        if (fallback) fallback.remove();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('survey-form')) {
        window.surveyBlok3bIndustriManager = new SurveyBlok3bIndustriManager();
    }

    // Back navigation to Blok 3A for consistency with other blocks
    const backBtn = document.getElementById('back-to-blok3a');
    if (backBtn && window.surveyRoutes && window.surveyRoutes.backToBlok3a) {
        backBtn.addEventListener('click', function() {
            window.location.href = window.surveyRoutes.backToBlok3a;
        });
    }

    // Integrate server-side validation handling with SurveyManager for better UX
    // - Display errors in dedicated `.form-errors` containers via this manager's showFieldError
    // - Map Laravel dot-notation keys to bracket notation to match input `name`s
    // - Prefer showing errors on visible display inputs when backend validates hidden fields
    const mgr = window.surveyBlok3bIndustriManager;
    if (window.surveyManager && mgr) {
        // Helper: convert Laravel dot-notation (e.g., blok3b_industri.q306_awal)
        // to bracket notation used by input names (blok3b_industri[q306_awal])
        const toBracketName = (key) => {
            if (!key || typeof key !== 'string') return key;
            if (!key.includes('.')) return key;
            const parts = key.split('.');
            const root = parts.shift();
            return root + '[' + parts.join('][') + ']';
        };

        // Prefer this manager's field error rendering to keep consistency with other blocks
        window.surveyManager.showFieldError = function(field, message) {
            mgr.showFieldError(field, message);
        };
        window.surveyManager.clearFieldError = function(field) {
            mgr.clearFieldError(field);
        };

        // Override server-side validation error handler to:
        // - Clear previous errors
        // - Resolve field names properly (dot -> bracket)
        // - Route hidden-field errors to visible display inputs
        window.surveyManager.handleServerValidationErrors = function(errors) {
            if (!this.form || !errors) return;

            // Clear any existing errors first
            const errorFields = this.form.querySelectorAll('.field-error');
            errorFields.forEach(field => mgr.clearFieldError(field));
            const errorMsgs = this.form.querySelectorAll('.field-error-message');
            errorMsgs.forEach(msg => msg.remove());

            // Display each validation error
            Object.keys(errors).forEach((fieldKey) => {
                const messages = Array.isArray(errors[fieldKey]) ? errors[fieldKey] : [errors[fieldKey]];
                const bracketName = toBracketName(fieldKey);

                // Try to find the field by bracket name first
                let field = this.form.querySelector(`[name="${bracketName}"]`);
                // Fallback to raw key (in case backend already uses bracket notation)
                if (!field) {
                    field = this.form.querySelector(`[name="${fieldKey}"]`);
                }

                // If backend validated a hidden field, prefer the paired visible display input
                if (field && field.type === 'hidden') {
                    const displayInput = this.form.querySelector(`.currency-display[data-target-name="${bracketName}"]`);
                    if (displayInput) field = displayInput;
                }

                if (field) {
                    mgr.showFieldError(field, messages[0]);
                }
            });

            // Scroll and focus the first error for accessibility
            if (typeof this.scrollToFirstError === 'function') {
                this.scrollToFirstError();
            }
        };
    }
});