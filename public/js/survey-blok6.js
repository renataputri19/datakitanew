/**
 * SIBSTR Survey Blok VI Specific JavaScript
 * Handles validation and interactions specific to Blok VI (Catatan)
 */

class SurveyBlok6Manager {
    constructor() {
        this.form = document.getElementById('survey-form');
        this.init();
    }

    init() {
        if (!this.form) return;

        this.setupEventListeners();
        this.setupNavigation();
    }

    setupEventListeners() {
        // Back to Blok 2 button
        const backButton = document.getElementById('back-to-blok2');
        if (backButton) {
            backButton.addEventListener('click', () => {
                if (window.surveyRoutes?.backToBlok2) {
                    window.location.href = window.surveyRoutes.backToBlok2;
                }
            });
        }
    }

    setupNavigation() {
        // Override the finish survey button
        const finishButton = document.getElementById('finish-survey');
        if (finishButton) {
            finishButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleFinishSurvey();
            });
        }
    }

    handleFinishSurvey() {
        // Save the current data and mark as completed
        if (window.surveyManager) {
            // Save all data with completion flag
            const formData = new FormData(this.form);
            formData.append('is_completed', 'true');
            
            window.surveyManager.saveAll(true).then(() => {
                // Show success message
                alert('Survei telah berhasil diselesaikan. Terima kasih atas partisipasi Anda!');
                
                // Redirect to survey list or home page
                window.location.href = '/survei/sibstr';
            }).catch((error) => {
                console.error('Error finishing survey:', error);
                alert('Terjadi kesalahan saat menyelesaikan survei. Silakan coba lagi.');
            });
        }
    }
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('#survey-form') && window.location.pathname.includes('blok6')) {
        window.surveyBlok6Manager = new SurveyBlok6Manager();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyBlok6Manager;
}
