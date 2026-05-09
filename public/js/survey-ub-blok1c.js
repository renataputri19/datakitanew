/**
 * Survey UB Blok I-C Manager
 * Validates required radio fields and conditional/always-required number fields.
 * Mirrors the pattern established in survey-ub-blok1b.js.
 */
class SurveyUbBlok1cManager {
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

        ['bermitra_kdkmp', 'terlibat_mbg', 'ekspor_impor_barang', 'ekspor_impor_jasa']
            .forEach(name => {
                document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                    radio.addEventListener('change', () => this.clearFieldError(name));
                });
            });

        // Clear number-field errors on input
        [
            'jumlah_produk_halal_bpjph',
            'jumlah_produk_belum_halal_bpjph',
            'jumlah_produk_izin_edar_bpom',
            'jumlah_produk_tanpa_izin_edar_bpom',
        ].forEach(name => {
            const el = this.form.querySelector(`input[name="${name}"]`);
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
        const radioInput = this.form.querySelector(`input[type="radio"][name="${fieldName}"]`);
        if (radioInput) {
            radioInput.closest('.ub-radio-group')?.classList.add('ub-radio-error');
        }
        const numInput = this.form.querySelector(`input[type="number"][name="${fieldName}"]`);
        if (numInput) numInput.classList.add('error');
    }

    clearFieldError(fieldName) {
        const errEl = this.form.querySelector(`.ub-err-msg[data-field="${fieldName}"]`);
        if (errEl) errEl.textContent = '';
        const radioInput = this.form.querySelector(`input[type="radio"][name="${fieldName}"]`);
        if (radioInput) {
            radioInput.closest('.ub-radio-group')?.classList.remove('ub-radio-error');
        }
        const numInput = this.form.querySelector(`input[type="number"][name="${fieldName}"]`);
        if (numInput) numInput.classList.remove('error');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    getRadioValue(name) {
        return document.querySelector(`input[name="${name}"]:checked`)?.value ?? null;
    }

    // ── Full form validation ──────────────────────────────────────────────────

    validateForm() {
        let isValid = true;

        const checkRadio = (name) => {
            if (!this.getRadioValue(name)) {
                this.showFieldError(name, `${this.getFieldLabel(name)} wajib dipilih`);
                return false;
            }
            this.clearFieldError(name);
            return true;
        };

        const checkNumber = (name, label) => {
            const input = this.form.querySelector(`input[name="${name}"]`);
            if (!input) return true;
            const val = input.value.trim();
            if (val === '' || isNaN(parseInt(val, 10)) || parseInt(val, 10) < 0) {
                this.showFieldError(name, `${label} wajib diisi (minimal 0)`);
                return false;
            }
            this.clearFieldError(name);
            return true;
        };

        if (!checkRadio('bermitra_kdkmp'))      isValid = false;
        if (!checkRadio('terlibat_mbg'))        isValid = false;
        if (!checkRadio('ekspor_impor_barang')) isValid = false;
        if (!checkRadio('ekspor_impor_jasa'))   isValid = false;

        // Rincian 15 — Sertifikat Halal
        const halalVal = this.getRadioValue('sertifikat_halal');
        if (halalVal === '1') {
            if (!checkNumber('jumlah_produk_halal_bpjph', 'Jumlah produk halal BPJPH')) isValid = false;
        } else {
            this.clearFieldError('jumlah_produk_halal_bpjph');
        }
        if (halalVal) {
            // 15c is always required once 15a is answered
            if (!checkNumber('jumlah_produk_belum_halal_bpjph', 'Jumlah produk belum halal BPJPH')) isValid = false;
        }

        // Rincian 16 — Izin Edar
        const izinVal = this.getRadioValue('izin_edar');
        if (izinVal === '1') {
            if (!checkNumber('jumlah_produk_izin_edar_bpom', 'Jumlah produk izin edar BPOM')) isValid = false;
        } else {
            this.clearFieldError('jumlah_produk_izin_edar_bpom');
        }
        if (izinVal) {
            // 16c is always required once 16a is answered
            if (!checkNumber('jumlah_produk_tanpa_izin_edar_bpom', 'Jumlah produk tanpa izin edar BPOM')) isValid = false;
        }

        return isValid;
    }

    // ── Submit handler ────────────────────────────────────────────────────────

    handleSubmit() {
        if (!this.validateForm()) {
            this.form.querySelector('.ub-err-msg:not(:empty)')
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
    if (document.getElementById('survey-form') && window.location.pathname.includes('blok1c')) {
        window.surveyUbBlok1cManager = new SurveyUbBlok1cManager();
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyUbBlok1cManager;
}
