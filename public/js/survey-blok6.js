/**
 * SIBSTR Survey Blok VI Specific JavaScript
 * Handles validation and interactions specific to Blok VI (Catatan)
 */

/**
 * Show a styled in-page modal instead of browser alert().
 *
 * @param {object} opts
 *   type        : 'warning' | 'success' | 'error'
 *   title       : headline text
 *   body        : body text
 *   confirmText : confirm button label (default 'OK')
 *   cancelText  : cancel button label; omit to hide cancel button
 *   redirectUrl : if set, auto-redirect after `redirectDelay` ms
 *   redirectDelay: ms before auto-redirect (default 3000)
 *   onConfirm   : callback when confirm is clicked (overrides redirectUrl)
 *   onCancel    : callback when cancel is clicked
 */
function showSurveyModal(opts = {}) {
    const overlay  = document.getElementById('survey-modal-overlay');
    const icon     = document.getElementById('survey-modal-icon');
    const title    = document.getElementById('survey-modal-title');
    const body     = document.getElementById('survey-modal-body');
    const confirm  = document.getElementById('survey-modal-confirm');
    const cancel   = document.getElementById('survey-modal-cancel');
    const progWrap = document.getElementById('survey-modal-progress-wrap');
    const progBar  = document.getElementById('survey-modal-progress-bar');
    const countdown= document.getElementById('survey-modal-countdown');
    if (!overlay) return;

    const palette = {
        warning : { icon: '⚠️',  color: '#d97706', bg: '#fbbf24' },
        success : { icon: '✅',  color: '#059669', bg: '#10b981' },
        error   : { icon: '❌',  color: '#dc2626', bg: '#ef4444' },
    };
    const p = palette[opts.type] || palette.error;

    icon.textContent    = p.icon;
    title.textContent   = opts.title  || '';
    body.textContent    = opts.body   || '';
    confirm.textContent = opts.confirmText || 'OK';
    confirm.style.background = p.bg;

    // Cancel button
    if (opts.cancelText) {
        cancel.textContent = opts.cancelText;
        cancel.style.display = '';
    } else {
        cancel.style.display = 'none';
    }

    // Auto-redirect countdown
    let timer = null;
    const delay = opts.redirectDelay ?? 3000;
    if (opts.redirectUrl && !opts.onConfirm) {
        progWrap.style.display = '';
        progBar.style.background = p.bg;
        progBar.style.transitionDuration = delay + 'ms';
        countdown.textContent = Math.ceil(delay / 1000) + ' detik…';
        // trigger reflow so transition fires
        void progBar.offsetWidth;
        progBar.style.width = '0%';

        let remaining = delay;
        const tick = 1000;
        timer = setInterval(() => {
            remaining -= tick;
            if (remaining <= 0) {
                clearInterval(timer);
                close();
                window.location.href = opts.redirectUrl;
            } else {
                countdown.textContent = Math.ceil(remaining / 1000) + ' detik…';
            }
        }, tick);
    } else {
        progWrap.style.display = 'none';
    }

    function close() {
        clearInterval(timer);
        overlay.style.display = 'none';
    }

    confirm.onclick = () => {
        close();
        if (opts.onConfirm)       opts.onConfirm();
        else if (opts.redirectUrl) window.location.href = opts.redirectUrl;
    };
    cancel.onclick = () => {
        close();
        if (opts.onCancel) opts.onCancel();
    };

    overlay.style.display = 'flex';
}

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
        const finishUrl = window.surveyRoutes?.finishSurvey;
        if (!finishUrl) {
            console.error('finishSurvey route not configured in window.surveyRoutes');
            return;
        }

        const isTahunan = window.surveyData?.isTahunan === true;
        const originalBtnText = isTahunan ? 'Simpan dan Lanjutkan' : 'Selesaikan Survei';

        const formData = new FormData(this.form);
        formData.append('is_completed', 'true');

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            || this.form.querySelector('input[name="_token"]')?.value;

        // Disable button to prevent double-submit
        const btn = document.getElementById('finish-survey');
        if (btn) {
            btn.disabled = true;
            btn.textContent = 'Memvalidasi…';
        }

        fetch(finishUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: formData,
        })
            .then(async (response) => {
                const result = await response.json().catch(() => ({}));

                if (result.success) {
                    if (result.is_tahunan) {
                        showSurveyModal({
                            type: 'success',
                            title: 'Survei Tahunan 2025 Selesai!',
                            body: 'Data Survei Tahunan 2025 Anda telah berhasil disimpan. Anda akan diarahkan ke halaman dashboard.',
                            confirmText: 'Ke Dashboard',
                            redirectUrl: '/dashboard/surveys/sibstr/results',
                            redirectDelay: 4000,
                        });
                    } else {
                        showSurveyModal({
                            type: 'success',
                            title: 'Survei Selesai!',
                            body: 'Data survei Triwulanan Anda telah berhasil disimpan. Anda akan diarahkan ke halaman dashboard.',
                            confirmText: 'Ke Dashboard',
                            redirectUrl: '/dashboard/surveys/sibstr/results',
                            redirectDelay: 3000,
                        });
                    }
                    return;
                }

                // Re-enable button on failure
                if (btn) {
                    btn.disabled = false;
                    btn.textContent = originalBtnText;
                }

                if (result.redirect_to) {
                    const url = new URL(result.redirect_to, window.location.origin);
                    url.searchParams.set('_incomplete', '1');
                    showSurveyModal({
                        type: 'warning',
                        title: 'Isian Belum Lengkap',
                        body: (result.message || 'Terdapat isian yang belum lengkap.') + '\n\nAnda akan diarahkan ke halaman yang perlu dilengkapi.',
                        confirmText: 'Lengkapi Sekarang',
                        cancelText: 'Nanti',
                        redirectUrl: url.href,
                        redirectDelay: 5000,
                        onCancel: () => {
                            if (btn) { btn.disabled = false; btn.textContent = originalBtnText; }
                        },
                    });
                } else {
                    showSurveyModal({
                        type: 'error',
                        title: 'Terjadi Kesalahan',
                        body: result.message || 'Terjadi kesalahan saat menyelesaikan survei.',
                    });
                }
            })
            .catch((error) => {
                console.error('Error finishing survey:', error);
                if (btn) { btn.disabled = false; btn.textContent = originalBtnText; }
                showSurveyModal({
                    type: 'error',
                    title: 'Kesalahan Jaringan',
                    body: 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda dan coba lagi.',
                });
            });
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
