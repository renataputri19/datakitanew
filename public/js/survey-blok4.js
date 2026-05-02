/**
 * SIBSTR Survey Blok IV Specific JavaScript
 * Handles validation, autosave, and back navigation for Blok IV (Fenomena dan Catatan).
 * Validation UI/UX matches the pattern used in Blok I, II, and III.
 */

class SurveyBlok4Manager {
    constructor() {
        this.form = document.getElementById('survey-form');
        if (!this.form) return;
        this.setupEventListeners();
        this.setupAutoSave();
        this.setupBackNavigation();
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

        // Real-time error clearing and blur validation on required textareas
        this.form.querySelectorAll('textarea[required]').forEach(field => {
            field.addEventListener('input', () => this.clearFieldError(field));
            field.addEventListener('blur', () => this.validateField(field));
        });
    }

    // ── Validation ────────────────────────────────────────────────────────────

    validateForm() {
        let isValid = true;
        this.form.querySelectorAll('textarea[required]').forEach(field => {
            if (!this.validateField(field)) isValid = false;
        });
        return isValid;
    }

    validateField(field) {
        const value = (field.value || '').trim();
        if (field.required && value === '') {
            this.showFieldError(field, `${this._getFieldLabel(field)} wajib diisi`);
            return false;
        }
        this.clearFieldError(field);
        return true;
    }

    _getFieldLabel(el) {
        if (!el) return 'Field ini';
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
        field.classList.remove('field-error');
        const errorEl = field.parentNode.querySelector('.field-error-message');
        if (errorEl) errorEl.remove();
    }

    // ── Validation summary ────────────────────────────────────────────────────

    collectValidationErrors() {
        const errors = [];
        const seen = new Set();
        const addLabel = (label) => { if (label && !seen.has(label)) { seen.add(label); errors.push(label); } };

        this.form.querySelectorAll('.field-error').forEach(field => {
            const row = field.closest('.form-row');
            if (!row) return;
            const formLabel = row.querySelector('.form-label');
            if (!formLabel) return;
            const qNum = formLabel.querySelector('.question-number')?.textContent?.trim() ?? '';
            const titleSpans = formLabel.querySelectorAll('span:not(.question-number)');
            let title = titleSpans.length > 0 ? titleSpans[0].textContent.trim() : '';
            if (title.length > 70) title = title.substring(0, 70).trimEnd() + '\u2026';
            addLabel((qNum + ' ' + title).trim() || null);
        });

        return errors;
    }

    // ── Save handler ──────────────────────────────────────────────────────────

    handleSaveComplete() {
        // Remove any previous summary
        document.getElementById('blok4-validation-summary')?.remove();

        if (!this.validateForm()) {
            const errors = this.collectValidationErrors();
            if (errors.length > 0) {
                const esc = s => s.replace(/[&<>]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;'}[c]));
                const summaryHTML = `
                <div id="blok4-validation-summary" class="validation-summary">
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
                    document.getElementById('blok4-validation-summary')
                        ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                // Fallback: scroll to first error element
                this.form.querySelector('.field-error')
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

    // ── Auto-save ────────────────────────────────────────────────────────────

    setupAutoSave() {
        const fields = this.form.querySelectorAll('textarea[name^="blok4["]');
        fields.forEach(field => {
            field.addEventListener('input', () => {
                if (window.surveyManager) {
                    window.surveyManager.scheduleAutoSave(field.name, field.value);
                }
            });
        });
    }

    // ── Back navigation ──────────────────────────────────────────────────────

    setupBackNavigation() {
        const backBtn = document.getElementById('back-to-blok3b');
        if (backBtn) {
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const prefix = window.surveyData?.kbliPrefix;
                const isIndustri = typeof prefix === 'number' && prefix >= 10 && prefix <= 33;
                const target = isIndustri ? window.surveyRoutes?.backToBlok3cIndustri : window.surveyRoutes?.backToBlok3bNonIndustri;
                if (target) {
                    window.location.href = target;
                }
            });
        }
    }
}

// Initialise when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('survey-form') && window.location.pathname.includes('blok4')) {
        window.surveyBlok4Manager = new SurveyBlok4Manager();
        console.log('Survey Blok 4 Manager initialized');
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyBlok4Manager;
}