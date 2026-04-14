/**
 * SIBSTR Survey Blok IV Specific JavaScript
 * Handles autosave for textareas and back navigation to appropriate Blok 3B
 */

class SurveyBlok4Manager {
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
        const fields = this.form.querySelectorAll('textarea[name^="blok4["]');
        fields.forEach(field => {
            field.addEventListener('input', () => {
                const name = field.name;
                const value = field.value;
                if (window.surveyManager) {
                    window.surveyManager.scheduleAutoSave(name, value);
                }
            });
        });
    }

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

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('#survey-form') && window.location.pathname.includes('blok4')) {
        window.surveyBlok4Manager = new SurveyBlok4Manager();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyBlok4Manager;
}