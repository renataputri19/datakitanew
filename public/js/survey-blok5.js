/**
 * SIBSTR Survey Blok V Specific JavaScript
 * Handles autosave for radio selections and back navigation.
 * In triwulanan mode, all 7 components × 2 columns (kondisi + prospek) are required.
 * Error display is consistent with Blok I, II, III, and IV.
 */

class SurveyBlok5Manager {
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
        // preventing double-invocation of saveForm.
        const saveCompleteOld = document.getElementById('save-complete');
        if (saveCompleteOld) {
            const saveCompleteBtn = saveCompleteOld.cloneNode(true);
            saveCompleteOld.parentNode.replaceChild(saveCompleteBtn, saveCompleteOld);
            saveCompleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSaveComplete();
            });
        }

        // Clear per-group error highlight when a radio is selected
        const radios = this.form.querySelectorAll('input[type="radio"][name^="blok5["]');
        const seen = new Set();
        radios.forEach(radio => {
            const name = radio.name;
            if (seen.has(name)) return;
            seen.add(name);
            this.form.querySelectorAll(`input[type="radio"][name="${name}"]`).forEach(r => {
                r.addEventListener('change', () => this.clearRadioGroupError(name));
            });
        });
    }

    // ── Error display / clear ─────────────────────────────────────────────────

    showRadioGroupError(groupName) {
        const firstRadio = this.form.querySelector(`input[type="radio"][name="${groupName}"]`);
        const container = firstRadio?.closest('.radio-group');
        if (container) {
            container.classList.add('radio-group-has-error');
        }
    }

    clearRadioGroupError(groupName) {
        const firstRadio = this.form.querySelector(`input[type="radio"][name="${groupName}"]`);
        firstRadio?.closest('.radio-group')?.classList.remove('radio-group-has-error');
    }

    // ── Client-side validation for radio groups ───────────────────────────────

    collectClientValidationErrors() {
        const errors = [];
        const seenGroups = new Set();

        // Only radio buttons with [required] are validated (triwulanan only in blade)
        this.form.querySelectorAll('input[type="radio"][required]').forEach(radio => {
            const name = radio.name;
            if (seenGroups.has(name)) return;
            seenGroups.add(name);

            // Check if any radio in this group is checked
            const isChecked = Array.from(
                this.form.querySelectorAll('input[type="radio"]')
            ).some(r => r.name === name && r.checked);

            const td = radio.closest('td');
            const radioGroup = td?.querySelector('.radio-group');

            if (!isChecked) {
                // Highlight the group in the table
                radioGroup?.classList.add('radio-group-has-error');

                // Build human-readable label from table row
                const tr = radio.closest('tr');
                const rowLabel = tr?.querySelector('td.row-label');
                const qNum = rowLabel?.querySelector('.question-number')?.textContent?.trim() ?? '';
                let labelText = '';
                rowLabel?.querySelectorAll('span').forEach(s => {
                    if (!s.classList.contains('question-number')) {
                        labelText = s.textContent.trim();
                    }
                });
                const colType = td?.classList.contains('prospect-col') ? 'Prospek' : 'Kondisi';
                const errorLabel = [qNum, labelText, `(${colType})`].filter(Boolean).join(' ');
                errors.push(errorLabel);
            } else {
                radioGroup?.classList.remove('radio-group-has-error');
            }
        });

        return errors;
    }

    // ── Validation summary + save handler ─────────────────────────────────────

    handleSaveComplete() {
        document.getElementById('blok5-validation-summary')?.remove();

        const errors = this.collectClientValidationErrors();

        if (errors.length > 0) {
            const esc = s => String(s).replace(/[&<>]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;'}[c]));
            const summaryHTML = `
            <div id="blok5-validation-summary" class="validation-summary">
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
                document.getElementById('blok5-validation-summary')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        // All valid — proceed with save
        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            window.surveyManager.saveForm(true);
        } else {
            console.error('SurveyManager not available');
            alert('Terjadi kesalahan sistem. Silakan refresh halaman dan coba lagi.');
        }
    }

    // ── Auto-save ────────────────────────────────────────────────────────────

    setupAutoSave() {
        // Immediate save on radio change to keep UX snappy
        const radios = this.form.querySelectorAll('input[type="radio"][name^="blok5["]');
        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (window.surveyManager) {
                    window.surveyManager.scheduleAutoSave(radio.name, radio.value, true);
                }
            });
        });
    }

    // ── Back navigation ──────────────────────────────────────────────────────

    setupBackNavigation() {
        const backBtn = document.getElementById('back-to-blok4');
        if (backBtn) {
            backBtn.addEventListener('click', (e) => {
                e.preventDefault();
                let target;
                const isTriwulanan = window.surveyData?.isTriwulanan;
                if (isTriwulanan) {
                    const prefix = window.surveyData?.kbliPrefix;
                    const isIndustri = typeof prefix === 'number' && prefix >= 10 && prefix <= 33;
                    target = isIndustri
                        ? window.surveyRoutes?.backToBlok3bIndustri
                        : window.surveyRoutes?.backToBlok3bNonIndustri;
                } else {
                    target = window.surveyRoutes?.backToBlok4;
                }
                if (target) {
                    window.location.href = target;
                }
            });
        }
    }
}

// Initialise when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('survey-form') && window.location.pathname.includes('blok5')) {
        window.surveyBlok5Manager = new SurveyBlok5Manager();

        // Override showSubmissionGuidance so server-side errors appear in the summary panel
        if (window.surveyManager) {
            const origGuidance = window.surveyManager.showSubmissionGuidance?.bind(window.surveyManager);
            window.surveyManager.showSubmissionGuidance = function (message, details) {
                document.getElementById('blok5-validation-summary')?.remove();
                const esc = s => String(s).replace(/[&<>]/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;'}[c]));
                const items = Array.isArray(details) && details.length
                    ? details.map(d => `<li class="validation-summary-item">${esc(d)}</li>`).join('')
                    : `<li class="validation-summary-item">${esc(message)}</li>`;
                const summaryHTML = `
                <div id="blok5-validation-summary" class="validation-summary">
                    <div class="validation-summary-header">
                        <span class="validation-summary-icon">&#9888;</span>
                        <h4 class="validation-summary-title">Data belum lengkap</h4>
                    </div>
                    <p class="validation-summary-desc">Mohon lengkapi bidang berikut sebelum menyimpan:</p>
                    <ul class="validation-summary-list">${items}</ul>
                </div>`;
                const form = document.getElementById('survey-form');
                const fa = form?.querySelector('.form-actions');
                if (fa) {
                    fa.insertAdjacentHTML('beforebegin', summaryHTML);
                    document.getElementById('blok5-validation-summary')
                        ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else if (origGuidance) {
                    origGuidance(message, details);
                }
            };
        }

        console.log('Survey Blok 5 Manager initialized');
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyBlok5Manager;
}
