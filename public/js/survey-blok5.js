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
                const target = window.surveyRoutes?.backToBlok4;
                if (target) {
                    window.location.href = target;
                }
            });
        }
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