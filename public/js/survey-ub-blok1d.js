/**
 * Survey UB Blok I-D Manager
 * Validates all required text/number/currency fields in Blok I-D (Pekerja & Keuangan).
 * Mirrors the pattern established in survey-ub-blok1b.js.
 */
class SurveyUbBlok1dManager {
    constructor() {
        this.form = document.getElementById('survey-form');
        if (!this.form) return;
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    // ── Event listeners ──────────────────────────────────────────────────────

    setupEventListeners() {
        const submitOld = document.getElementById('submitBtn');
        if (submitOld) {
            const submitBtn = submitOld.cloneNode(true);
            submitOld.parentNode.replaceChild(submitBtn, submitOld);
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });
        }

        [
            'pekerja_laki', 'pekerja_perempuan', 'tahun_beroperasi',
            'pengeluaran_upah_gaji', 'pengeluaran_biaya_produksi',
            'pengeluaran_pembelian_barang', 'pengeluaran_operasional',
            'pengeluaran_nonoperasional', 'nilai_produksi_barang_jasa',
            'nilai_aset_tanah_bangunan', 'nilai_aset_lainnya', 'luas_tanah'
        ].forEach(name => {
            const el = this.form.querySelector(`[name="${name}"]`);
            if (el) el.addEventListener('input', () => this.clearFieldError(name));
        });
    }

    // ── Label extraction ──────────────────────────────────────────────────────

    getFieldLabel(fieldName) {
        const errEl = this.form.querySelector(`.ub-err-msg[data-field="${fieldName}"]`);
        if (!errEl) return fieldName;
        const parent = errEl.parentElement;
        const label = parent?.querySelector('.ub-label');
        if (!label) return fieldName;

        const clone = label.cloneNode(true);
        clone.querySelectorAll('.ub-required').forEach(el => el.remove());
        let text = clone.textContent.trim();

        text = text.replace(/^[\da-z]+\.\s+/i, '');
        text = text.replace(/^(Apakah|Apa|Berapa|Di mana|Bagaimana)\s+/i, '');
        text = text.charAt(0).toUpperCase() + text.slice(1);
        const qIdx = text.indexOf('?');
        if (qIdx > 0) text = text.substring(0, qIdx).trim();
        if (text.length > 70) {
            let t = text.substring(0, 70);
            const lastSpace = t.lastIndexOf(' ');
            if (lastSpace > 30) t = t.substring(0, lastSpace);
            text = t + '…';
        }

        return text || fieldName;
    }

    // ── Error display / clear ─────────────────────────────────────────────────

    showFieldError(fieldName, message) {
        const errEl = this.form.querySelector(`.ub-err-msg[data-field="${fieldName}"]`);
        if (errEl) errEl.textContent = message;
        const input = this.form.querySelector(`[name="${fieldName}"]`);
        if (input) input.classList.add('error');
    }

    clearFieldError(fieldName) {
        const errEl = this.form.querySelector(`.ub-err-msg[data-field="${fieldName}"]`);
        if (errEl) errEl.textContent = '';
        const input = this.form.querySelector(`[name="${fieldName}"]`);
        if (input) input.classList.remove('error');
    }

    // ── Full form validation ──────────────────────────────────────────────────

    validateForm() {
        let isValid = true;

        const checkText = (name) => {
            const el = this.form.querySelector(`[name="${name}"]`);
            if (!el || el.value.trim() === '') {
                this.showFieldError(name, `${this.getFieldLabel(name)} wajib diisi`);
                return false;
            }
            this.clearFieldError(name);
            return true;
        };

        if (!checkText('pekerja_laki'))                  isValid = false;
        if (!checkText('pekerja_perempuan'))             isValid = false;
        if (!checkText('tahun_beroperasi'))              isValid = false;
        if (!checkText('pengeluaran_upah_gaji'))         isValid = false;
        if (!checkText('pengeluaran_biaya_produksi'))    isValid = false;
        if (!checkText('pengeluaran_pembelian_barang'))  isValid = false;
        if (!checkText('pengeluaran_operasional'))       isValid = false;
        if (!checkText('pengeluaran_nonoperasional'))    isValid = false;
        if (!checkText('nilai_produksi_barang_jasa'))    isValid = false;
        if (!checkText('nilai_aset_tanah_bangunan'))     isValid = false;
        if (!checkText('nilai_aset_lainnya'))            isValid = false;
        if (!checkText('luas_tanah'))                    isValid = false;

        return isValid;
    }

    // ── Submit handler ────────────────────────────────────────────────────────

    handleSubmit() {
        if (!this.validateForm()) {
            this.form.querySelector('.ub-err-msg:not(:empty), .ub-input.error')
                ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            window.surveyManager.saveForm(true);
        } else {
            this.form.submit();
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('survey-form') && window.location.pathname.includes('blok1d')) {
        window.surveyUbBlok1dManager = new SurveyUbBlok1dManager();
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyUbBlok1dManager;
}
