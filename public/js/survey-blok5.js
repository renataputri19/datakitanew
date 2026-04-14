/**
 * SIBSTR Survey Blok V Specific JavaScript
 * Handles autosave for radio selections and back navigation
 */

class SurveyBlok5Manager {
    constructor() {
        this.form = document.getElementById('survey-form');
        this.init();
    }

    init() {
        if (!this.form) return;
        this.setupAutoSave();
        this.setupValidation();
        this.setupBackNavigation();
    }

    setupAutoSave() {
        // Auto-save whenever a radio option under blok5[...] changes
        const radios = this.form.querySelectorAll('input[type="radio"][name^="blok5["]');
        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                const name = radio.name;
                const value = radio.value;
                if (window.surveyManager) {
                    // immediate save to keep UX snappy for radios
                    window.surveyManager.scheduleAutoSave(name, value, true);
                }
            });
        });
    }

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

    setupValidation() {
        // Attach change listeners to all Blok 5 radio groups for immediate feedback
        const radios = this.form.querySelectorAll('input[type="radio"][name^="blok5["]');
        const seen = new Set();
        radios.forEach(radio => {
            const name = radio.name;
            if (seen.has(name)) return;
            seen.add(name);
            const group = this.form.querySelectorAll(`input[type="radio"][name="${name}"]`);
            group.forEach(r => {
                r.addEventListener('change', () => this.validateRadioGroup(name));
                r.addEventListener('blur', () => this.validateRadioGroup(name));
            });
            // Initial validation pass (helps highlight missing groups when attempting save)
            this.validateRadioGroup(name);
        });
    }

    validateRadioGroup(groupName) {
        const radios = this.form.querySelectorAll(`input[type="radio"][name="${groupName}"]`);
        if (!radios.length) return true;
        const first = radios[0];
        const isSelected = Array.from(radios).some(r => r.checked);
        if (!isSelected && first && first.required) {
            if (window.surveyManager && typeof window.surveyManager.showRadioGroupError === 'function') {
                window.surveyManager.showRadioGroupError(groupName, 'Pilihan ini wajib dipilih');
            }
            return false;
        }
        if (window.surveyManager && typeof window.surveyManager.clearRadioGroupError === 'function') {
            window.surveyManager.clearRadioGroupError(groupName);
        }
        return true;
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('#survey-form') && window.location.pathname.includes('blok5')) {
        window.surveyBlok5Manager = new SurveyBlok5Manager();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyBlok5Manager;
}