/**
 * Blok 3B Non-Industri (SIBSTR) client interactions
 * - Currency display inputs synced to hidden numeric fields
 * - Auto-save via SurveyManager for each field change
 * - Auto-total for Q305 (pendapatan)
 * - Percentage inputs clamped to [0, 100]
 */

class SurveyBlok3bNonIndustriManager {
    constructor() {
        this.form = document.getElementById('survey-form');
        if (!this.form) return;

        this.currencyDisplays = Array.from(this.form.querySelectorAll('.currency-display'));
        // Percent inputs: use a generic class so new fields are covered
        this.percentInputs = Array.from(this.form.querySelectorAll('.percent-input'));

        this.setupEventListeners();
        this.initializeDisplayValues();
        // Enforce readonly + styling for auto-calculated totals
        this.enforceReadonlyForTotals();
        // Ensure automated totals are computed and auto-saved on initial load
        this.updateInventoryTotals();
        this.updateRevenueTotal();
        this.updateRevenueTotalYear();
        this.updateAssetTotal();
        this.updateOwnershipTotal();
        this.updateWorkerTotals();
    }

    setupEventListeners() {
        // Currency display inputs: positive-only validation, sync to hidden numeric inputs, and auto-save
        this.currencyDisplays.forEach(input => {
            // Skip adding listeners for readonly auto-calculated fields
            if (input.hasAttribute('readonly') || input.classList.contains('readonly')) return;
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
                if (targetName && /(q303|q304)/.test(targetName)) {
                    this.updateRevenueTotal();
                }
                if (targetName && /(q303_year|q304_year)/.test(targetName)) {
                    this.updateRevenueTotalYear();
                }
                if (targetName && /(q318a|q318b)/.test(targetName)) {
                    this.updateAssetTotal();
                }
                if (targetName && /\[q313_(a1|a2|b1|b2)\]/.test(targetName)) {
                    this.updateWorkerTotal313();
                }
                if (targetName && /\[q314_(a1|a2|b1|b2)\]/.test(targetName)) {
                    this.updateWorkerTotal314();
                }
            });

            input.addEventListener('blur', (e) => {
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

        // Percent inputs (Q314, Q315, 319a-f): enforce bounds, show errors, and auto-save
        this.percentInputs.forEach(input => {
            input.addEventListener('input', (e) => {
                let v = e.target.value;
                const fieldName = e.target.getAttribute('name');
                if (v === '') {
                    this.clearFieldError(e.target);
                    if (window.surveyManager) {
                        window.surveyManager.scheduleAutoSave(fieldName, '', true);
                    }
                    return;
                }
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

                // Clamp to [0, 100]
                if (num < 0) num = 0;
                if (num > 100) num = 100;
                e.target.value = num;

                // Show/clear error and auto-save only when valid
                if (errorMsg) {
                    this.showFieldError(e.target, errorMsg);
                } else {
                    this.clearFieldError(e.target);
                    if (window.surveyManager) {
                        window.surveyManager.scheduleAutoSave(fieldName, String(num));
                    }
                    // Update ownership total if one of 319a-h changed
                    if (/\[q319[a-h]\]/.test(fieldName)) {
                        this.updateOwnershipTotal();
                    }
                }
            });
        });

        // Back navigation handled with surveyRoutes
        const backBtn = document.getElementById('back-to-blok3a');
        if (backBtn && window.surveyRoutes && window.surveyRoutes.backToBlok2) {
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                window.location.href = window.surveyRoutes.backToBlok2;
            });
        }

        // Ensure Q318.d (area) is always required and show required indicator
        const areaInput = this.form.querySelector('#q318d_area');
        if (areaInput) {
            areaInput.required = true;
            const areaLabel = this.form.querySelector('label[for="q318d_area"]');
            if (areaLabel) areaLabel.classList.add('required');
        }
    }

    initializeDisplayValues() {
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

    enforceReadonlyForTotals() {
        const readonlyIds = [
            'q305_display', 'q305_year_display',
            'q309_ni_awal_display', 'q309_ni_akhir_display',
            'q318c_display', 'q319i_display',
            'q313_c_display', 'q314_c_display',
        ];
        readonlyIds.forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            // Ensure readonly attribute and visual distinction
            el.readOnly = true;
            el.classList.add('readonly');
            el.style.backgroundColor = '#e9ecef';
            el.style.cursor = 'not-allowed';
            // Prevent focus via tab if desired
            el.setAttribute('tabindex', '-1');
        });
    }

    parseCurrencyToNumber(raw) {
        if (raw === undefined || raw === null) return null;
        const s = String(raw).trim();
        if (s === '') return null;
        const normalized = s.replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.\-]/g, '');
        // Disallow negative values
        if (normalized.includes('-')) return null;
        const num = parseFloat(normalized);
        if (isNaN(num)) return null;
        if (num < 0) return null;
        return Number(num.toFixed(2)).toString();
    }

    formatCurrencyDisplay(num) {
        try {
            const n = typeof num === 'number' ? num : parseFloat(num);
            if (isNaN(n)) return '';
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
        const awal = this.getHiddenValue('blok3b_nonindustri[q306_awal]')
                   + this.getHiddenValue('blok3b_nonindustri[q307_awal]')
                   + this.getHiddenValue('blok3b_nonindustri[q308_awal]');
        const akhir = this.getHiddenValue('blok3b_nonindustri[q306_akhir]')
                    + this.getHiddenValue('blok3b_nonindustri[q307_akhir]')
                    + this.getHiddenValue('blok3b_nonindustri[q308_akhir]');
        this.setHiddenAndDisplay('blok3b_nonindustri[q309_awal]', awal, 'q309_ni_awal_display');
        this.setHiddenAndDisplay('blok3b_nonindustri[q309_akhir]', akhir, 'q309_ni_akhir_display');
        const hidAwal = document.getElementById('q309_ni_awal_val');
        if (hidAwal) hidAwal.value = Number(awal).toFixed(2);
        const hidAkhir = document.getElementById('q309_ni_akhir_val');
        if (hidAkhir) hidAkhir.value = Number(akhir).toFixed(2);
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_nonindustri[q309_awal]', Number(awal).toFixed(2), true);
            window.surveyManager.scheduleAutoSave('blok3b_nonindustri[q309_akhir]', Number(akhir).toFixed(2), true);
        }
    }

    updateRevenueTotal() {
        const pendBarangJasa = this.getHiddenValue('blok3b_nonindustri[q303]');
        const pendLain = this.getHiddenValue('blok3b_nonindustri[q304]');
        const total = pendBarangJasa + pendLain;

        this.setHiddenAndDisplay('blok3b_nonindustri[q305]', total, 'q305_display');

        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_nonindustri[q305]', Number(total).toFixed(2), true);
        }
    }

    updateRevenueTotalYear() {
        const pendBarangJasa = this.getHiddenValue('blok3b_nonindustri[q303_year]');
        const pendLain = this.getHiddenValue('blok3b_nonindustri[q304_year]');
        const total = pendBarangJasa + pendLain;

        this.setHiddenAndDisplay('blok3b_nonindustri[q305_year]', total, 'q305_year_display');

        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_nonindustri[q305_year]', Number(total).toFixed(2), true);
        }
    }

    updateAssetTotal() {
        const a = this.getHiddenValue('blok3b_nonindustri[q318a]');
        const b = this.getHiddenValue('blok3b_nonindustri[q318b]');
        const total = a + b;
        this.setHiddenAndDisplay('blok3b_nonindustri[q318c]', total, 'q318c_display');
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_nonindustri[q318c]', Number(total).toFixed(2), true);
        }
    }

    updateWorkerTotals() {
        this.updateWorkerTotal313();
        this.updateWorkerTotal314();
    }

    updateWorkerTotal313() {
        const a1 = this.getHiddenValue('blok3b_nonindustri[q313_a1]');
        const a2 = this.getHiddenValue('blok3b_nonindustri[q313_a2]');
        const b1 = this.getHiddenValue('blok3b_nonindustri[q313_b1]');
        const b2 = this.getHiddenValue('blok3b_nonindustri[q313_b2]');
        const total = a1 + a2 + b1 + b2;
        this.setHiddenAndDisplay('blok3b_nonindustri[q313_c]', total, 'q313_c_display');
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_nonindustri[q313_c]', Number(total).toFixed(2), true);
        }
    }

    updateWorkerTotal314() {
        const a1 = this.getHiddenValue('blok3b_nonindustri[q314_a1]');
        const a2 = this.getHiddenValue('blok3b_nonindustri[q314_a2]');
        const b1 = this.getHiddenValue('blok3b_nonindustri[q314_b1]');
        const b2 = this.getHiddenValue('blok3b_nonindustri[q314_b2]');
        const total = a1 + a2 + b1 + b2;
        this.setHiddenAndDisplay('blok3b_nonindustri[q314_c]', total, 'q314_c_display');
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_nonindustri[q314_c]', Number(total).toFixed(2), true);
        }
    }

    updateOwnershipTotal() {
        const total = ['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h']
            .map(k => {
                const el = this.form.querySelector(`input[name="blok3b_nonindustri[${k}]"]`);
                if (!el) return 0;
                const v = parseFloat(String(el.value));
                return isNaN(v) ? 0 : v;
            })
            .reduce((acc, v) => acc + v, 0);
        const disp = document.getElementById('q319i_display');
        if (disp) disp.value = Number(total.toFixed(2));
        const hidden = this.form.querySelector('input[type="hidden"][name="blok3b_nonindustri[q319i]"]');
        if (hidden) hidden.value = Number(total.toFixed(2));
        if (window.surveyManager) {
            window.surveyManager.scheduleAutoSave('blok3b_nonindustri[q319i]', Number(total).toFixed(2), true);
        }
    }

    // Inline field error helpers (consistent with other survey forms)
    showFieldError(field, message) {
        // Clear any existing error first
        this.clearFieldError(field);
        // Add error class to field for visual feedback
        field.classList.add('field-error');
        // Create error message element
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error-message';
        errorElement.textContent = message;
        // Prefer rendering into the dedicated left-side error container
        const formRow = field.closest('.form-row');
        const errorContainer = formRow ? formRow.querySelector('.form-errors') : null;
        if (errorContainer) {
            // Clear any existing message in the container first to avoid duplicates
            const existing = errorContainer.querySelector('.field-error-message');
            if (existing) existing.remove();
            errorContainer.appendChild(errorElement);
        } else {
            // Fallback: insert after the field if container not found
            field.parentNode.insertBefore(errorElement, field.nextSibling);
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

    // ── Collect all empty required visible fields ─────────────────────────────
    collectClientValidationErrors() {
        const errors = [];
        const seen = new Set();

        const addError = (key, label) => {
            if (label && !seen.has(key)) { seen.add(key); errors.push(label); }
        };

        const getLabel = (el) => {
            const subrow = el.closest('.form-subrow');
            if (subrow) {
                const sublabel = subrow.querySelector('.form-sublabel');
                if (sublabel) {
                    const row = subrow.closest('.form-row');
                    const qNum = row?.querySelector('.question-number')?.textContent?.trim() ?? '';
                    let text = sublabel.textContent.trim();
                    if (text.length > 60) text = text.substring(0, 60).trimEnd() + '\u2026';
                    return (qNum + ' ' + text).trim() || null;
                }
            }
            const formRow = el.closest('.form-row');
            if (!formRow) return null;
            const formLabel = formRow.querySelector('.form-label');
            if (!formLabel) return null;
            const qNum = formLabel.querySelector('.question-number')?.textContent?.trim() ?? '';
            const spans = formLabel.querySelectorAll('span:not(.question-number)');
            let title = spans.length > 0 ? spans[0].textContent.trim() : '';
            if (title.length > 70) title = title.substring(0, 70).trimEnd() + '\u2026';
            return (qNum + ' ' + title).trim() || null;
        };

        this.form.querySelectorAll('[required]').forEach(el => {
            if (el.type === 'hidden') return;
            if (el.hasAttribute('readonly') || el.classList.contains('readonly')) return;
            const parentRow = el.closest('.form-row');
            if (parentRow && (parentRow.style.display === 'none' || parentRow.style.opacity === '0')) return;

            let isEmpty;
            if (el.classList.contains('currency-display')) {
                const targetName = el.getAttribute('data-target-name');
                const hidden = targetName
                    ? this.form.querySelector(`input[type="hidden"][name="${targetName}"]`)
                    : null;
                isEmpty = hidden
                    ? (hidden.value ?? '').toString().trim() === ''
                    : (el.value ?? '').trim() === '';
            } else {
                isEmpty = (el.value ?? '').trim() === '';
            }

            if (isEmpty) {
                el.classList.add('field-error');
                addError(el.name || el.id, getLabel(el));
            } else {
                el.classList.remove('field-error');
            }
        });

        return errors;
    }

    // ── Validation summary + save handler ─────────────────────────────────────
    handleSaveComplete() {
        document.getElementById('blok3b-nonindustri-validation-summary')?.remove();

        const errors = this.collectClientValidationErrors();

        if (errors.length > 0) {
            const esc = s => String(s).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
            const summaryHTML = `
            <div id="blok3b-nonindustri-validation-summary" class="validation-summary">
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
                document.getElementById('blok3b-nonindustri-validation-summary')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            return;
        }

        // All valid — delegate to surveyManager
        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            window.surveyManager.saveForm(true);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('survey-form')) return;

    window.surveyBlok3bNonIndustriManager = new SurveyBlok3bNonIndustriManager();
    const mgr = window.surveyBlok3bNonIndustriManager;

    // Clone-replace save-complete to strip survey.js's generic click handler
    const saveOld = document.getElementById('save-complete');
    if (saveOld) {
        const saveBtn = saveOld.cloneNode(true);
        saveOld.parentNode.replaceChild(saveBtn, saveOld);
        saveBtn.addEventListener('click', (e) => {
            e.preventDefault();
            mgr.handleSaveComplete();
        });
    }

    // Override showSubmissionGuidance so server-side error messages
    // appear in the summary panel instead of near the button
    if (window.surveyManager) {
        window.surveyManager.showSubmissionGuidance = function(message, details) {
            document.getElementById('blok3b-nonindustri-validation-summary')?.remove();
            const form = document.getElementById('survey-form');
            const formActions = form ? form.querySelector('.form-actions') : null;
            if (!formActions) return;
            const esc = s => String(s).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
            let itemsHTML = '';
            if (Array.isArray(details) && details.length) {
                itemsHTML = '<ul class="validation-summary-list">'
                    + details.map(d => `<li class="validation-summary-item">${esc(d)}</li>`).join('')
                    + '</ul>';
            }
            formActions.insertAdjacentHTML('beforebegin', `
                <div id="blok3b-nonindustri-validation-summary" class="validation-summary">
                    <div class="validation-summary-header">
                        <span class="validation-summary-icon">&#9888;</span>
                        <h4 class="validation-summary-title">Data belum lengkap</h4>
                    </div>
                    <p class="validation-summary-desc">${esc(message)}</p>
                    ${itemsHTML}
                </div>`
            );
            document.getElementById('blok3b-nonindustri-validation-summary')
                ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        // Override handleServerValidationErrors to redirect inline server errors
        // to the summary panel instead of highlighting individual fields near the button
        const origHandleServer = window.surveyManager.handleServerValidationErrors?.bind(window.surveyManager);
        if (origHandleServer) {
            window.surveyManager.handleServerValidationErrors = function(errors) {
                origHandleServer(errors);
                // After inline errors are set, pull them into the summary panel
                const form = document.getElementById('survey-form');
                const formActions = form ? form.querySelector('.form-actions') : null;
                if (!formActions) return;
                const errorMessages = Object.values(errors).flat();
                if (!errorMessages.length) return;
                document.getElementById('blok3b-nonindustri-validation-summary')?.remove();
                const esc = s => String(s).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
                formActions.insertAdjacentHTML('beforebegin', `
                    <div id="blok3b-nonindustri-validation-summary" class="validation-summary">
                        <div class="validation-summary-header">
                            <span class="validation-summary-icon">&#9888;</span>
                            <h4 class="validation-summary-title">Data belum lengkap</h4>
                        </div>
                        <p class="validation-summary-desc">Mohon lengkapi bidang berikut sebelum menyimpan:</p>
                        <ul class="validation-summary-list">
                            ${errorMessages.map(e => `<li class="validation-summary-item">${esc(e)}</li>`).join('')}
                        </ul>
                    </div>`
                );
                document.getElementById('blok3b-nonindustri-validation-summary')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };
        }
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyBlok3bNonIndustriManager;
}