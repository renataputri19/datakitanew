/**
 * Survey Blok 1 Manager
 * Handles client-side validation and save behaviour for Blok I (Keterangan Umum).
 * Works for both the normal fill flow and the edit-mode flow.
 */
class SurveyBlok1Manager {
    constructor() {
        this.form = document.getElementById('survey-form');
        if (!this.form) return;
        this.setupEventListeners();
    }

    // ── Event listeners ──────────────────────────────────────────────────────

    setupEventListeners() {
        // Clone-and-replace #save-complete to strip survey.js's competing click handler,
        // preventing double-validation and duplicate error messages.
        const saveCompleteOld = document.getElementById('save-complete');
        if (saveCompleteOld) {
            const saveCompleteBtn = saveCompleteOld.cloneNode(true);
            saveCompleteOld.parentNode.replaceChild(saveCompleteBtn, saveCompleteOld);
            saveCompleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSaveComplete();
            });
        }

        // Real-time error clearing on input / change
        const processedRadioGroups = new Set();
        this.form.querySelectorAll('[required]').forEach(field => {
            if (field.type === 'radio') {
                if (!processedRadioGroups.has(field.name)) {
                    processedRadioGroups.add(field.name);
                    this.form.querySelectorAll(`input[name="${field.name}"]`).forEach(radio => {
                        radio.addEventListener('change', () => this.clearRadioGroupError(field.name));
                    });
                }
            } else {
                field.addEventListener('input', () => this.clearFieldError(field));
                field.addEventListener('blur', () => this.validateField(field));
            }
        });
    }

    // ── Validation ────────────────────────────────────────────────────────────

    validateForm() {
        let isValid = true;
        const processedRadioGroups = new Set();

        this.form.querySelectorAll('[required]').forEach(field => {
            if (field.type === 'radio') {
                if (!processedRadioGroups.has(field.name)) {
                    processedRadioGroups.add(field.name);
                    if (!this.validateRadioGroup(field.name)) isValid = false;
                }
            } else {
                if (!this.validateField(field)) isValid = false;
            }
        });

        return isValid;
    }

    validateField(field) {
        const value = (field.value || '').trim();

        // Homepage is optional: empty or single dash is always valid
        if (field.name === 'homepage' && (value === '' || value === '-')) {
            this.clearFieldError(field);
            return true;
        }

        if (field.required && value === '') {
            this.showFieldError(field, `${this._getFieldLabel(field)} wajib diisi`);
            return false;
        }
        this.clearFieldError(field);
        return true;
    }

    validateRadioGroup(groupName) {
        const radios = this.form.querySelectorAll(`input[name="${groupName}"]`);
        const isSelected = Array.from(radios).some(r => r.checked);
        if (!isSelected) {
            const label = this._getFieldLabel(radios[0]);
            this.showRadioGroupError(groupName, `${label} wajib dipilih`);
            return false;
        }
        this.clearRadioGroupError(groupName);
        return true;
    }

    // ── Label extraction helper ───────────────────────────────────────────────

    _getFieldLabel(el) {
        if (!el) return 'Field ini';
        const subrow = el.closest('.form-subrow');
        if (subrow) {
            const sublabel = subrow.querySelector('.form-sublabel');
            if (sublabel) {
                let text = sublabel.textContent.trim();
                if (text.length > 60) text = text.substring(0, 60).trimEnd() + '…';
                return text;
            }
        }
        const row = el.closest('.form-row');
        if (!row) return 'Field ini';
        const formLabel = row.querySelector('.form-label');
        if (!formLabel) return 'Field ini';
        const titleSpans = formLabel.querySelectorAll('span:not(.question-number)');
        let title = titleSpans.length > 0
            ? titleSpans[0].textContent.trim()
            : formLabel.textContent.trim().replace(/^\d+[\.\s]+/, '');
        if (title.length > 60) title = title.substring(0, 60).trimEnd() + '…';
        return title || 'Field ini';
    }

    // ── Error display / clear ─────────────────────────────────────────────────

    showFieldError(field, message) {
        this.clearFieldError(field);
        field.classList.add('field-error');
        const errorEl = document.createElement('div');
        errorEl.className = 'field-error-message';
        errorEl.textContent = message;
        field.parentNode.insertBefore(errorEl, field.nextSibling);
    }

    clearFieldError(field) {
        if (field.type === 'radio') { this.clearRadioGroupError(field.name); return; }
        field.classList.remove('field-error');
        const errorEl = field.parentNode.querySelector('.field-error-message');
        if (errorEl) errorEl.remove();
    }

    showRadioGroupError(groupName, message) {
        this.clearRadioGroupError(groupName);
        const firstRadio = this.form.querySelector(`input[name="${groupName}"]`);
        const container = firstRadio?.closest('.radio-group');
        if (container) {
            container.classList.add('radio-group-has-error');
            const errorEl = document.createElement('div');
            errorEl.className = 'field-error-message radio-group-error';
            errorEl.dataset.group = groupName;
            errorEl.textContent = message;
            container.parentNode.insertBefore(errorEl, container.nextSibling);
        }
    }

    clearRadioGroupError(groupName) {
        const firstRadio = this.form.querySelector(`input[name="${groupName}"]`);
        firstRadio?.closest('.radio-group')?.classList.remove('radio-group-has-error');
        this.form.querySelector(`.radio-group-error[data-group="${groupName}"]`)?.remove();
    }

    // ── Validation summary (same style as blok2 / blok3a) ────────────────────

    collectValidationErrors() {
        const errors = [];
        const seen = new Set();
        const addLabel = (label) => { if (label && !seen.has(label)) { seen.add(label); errors.push(label); } };

        const getLabel = (el) => {
            const row = el.closest('.form-row');
            if (!row) return null;
            const formLabel = row.querySelector('.form-label');
            if (!formLabel) return null;
            const qNum = formLabel.querySelector('.question-number')?.textContent?.trim() ?? '';
            const titleSpans = formLabel.querySelectorAll('span:not(.question-number)');
            let title = titleSpans.length > 0 ? titleSpans[0].textContent.trim() : '';
            if (title.length > 70) title = title.substring(0, 70).trimEnd() + '\u2026';
            return (qNum + ' ' + title).trim() || null;
        };

        // Radio group errors
        this.form.querySelectorAll('.radio-group-has-error').forEach(container => addLabel(getLabel(container)));

        // Text / number / textarea / email / url errors
        this.form.querySelectorAll('.field-error').forEach(field => {
            if (field.type === 'radio') return;
            addLabel(getLabel(field));
        });

        return errors;
    }

    // ── Save handler ──────────────────────────────────────────────────────────

    handleSaveComplete() {
        // Remove any previous summary
        document.getElementById('blok1-validation-summary')?.remove();

        if (!this.validateForm()) {
            const errors = this.collectValidationErrors();
            if (errors.length > 0) {
                const esc = s => s.replace(/[&<>]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;'}[c]));
                const summaryHTML = `
                <div id="blok1-validation-summary" class="validation-summary">
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
                    document.getElementById('blok1-validation-summary')
                        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                // Fallback: scroll to first error element
                this.form.querySelector('.field-error, .radio-group-has-error')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // Validation passed – delegate to the survey manager
        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            window.surveyManager.saveForm(true);
        } else {
            console.error('SurveyManager not available');
            alert('Terjadi kesalahan sistem. Silakan refresh halaman dan coba lagi.');
        }
    }
}

// Initialise when DOM is ready – works for both fill and edit-mode URLs
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('survey-form') && window.location.pathname.includes('blok1')) {
        window.surveyBlok1Manager = new SurveyBlok1Manager();
        console.log('Survey Blok 1 Manager initialized');
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyBlok1Manager;
}
