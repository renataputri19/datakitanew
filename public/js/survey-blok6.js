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
        // Conditional back navigation: Blok 5 if 'masih_aktif', else Blok 2
        const backButton = document.getElementById('back-to-blok5');
        if (backButton) {
            backButton.addEventListener('click', (e) => {
                e.preventDefault();
                const kondisi = window.surveyData?.kondisiPerusahaan;
                const r202 = window.surveyData?.jaringanUnitKegiatan;
                // If R202 is 'e. Unit pembantu / penunjang', always go back to Blok 2
                const enforceBackToBlok2 = r202 === 'unit_pembantu_penunjang';
                // Otherwise, go back to Blok 5 only when kondisi_perusahaan is 'masih_aktif'
                const cameFromBlok5 = kondisi === 'masih_aktif' && !enforceBackToBlok2;
                const target = cameFromBlok5 ? window.surveyRoutes?.backToBlok5 : window.surveyRoutes?.backToBlok2;
                if (target) {
                    window.location.href = target;
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
        // Save the current data and mark as completed, following global SurveyManager pattern
        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            window.surveyManager.saveForm(true)
                .then(() => {
                    // Redirect to results summary under dashboard
                    const resultsUrl = '/dashboard/surveys/sibstr/results';
                    window.location.href = resultsUrl;
                })
                .catch((error) => {
                    console.error('Error finishing survey:', error);
                    alert('Terjadi kesalahan saat menyelesaikan survei. Silakan coba lagi.');
                });
        } else {
            // Fallback: directly hit finish endpoint if SurveyManager is unavailable
            const finishUrl = window.surveyRoutes?.finishSurvey;
            if (finishUrl) {
                const formData = new FormData(this.form);
                formData.append('is_completed', 'true');

                // Ensure CSRF token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || this.form.querySelector('input[name="_token"]')?.value;

                fetch(finishUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken || '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(async (response) => {
                        const result = await response.json().catch(() => ({}));
                        if (response.ok && result.success) {
                            window.location.href = '/dashboard/surveys/sibstr/results';
                        } else {
                            throw new Error(result.message || 'Finish survey failed');
                        }
                    })
                    .catch((error) => {
                        console.error('Error finishing survey:', error);
                        alert('Terjadi kesalahan saat menyelesaikan survei. Silakan coba lagi.');
                    });
            }
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
