/**
 * Survey UB Blok I-B Manager
 * Full form validation for Blok I-B (Kegiatan & Digital), including the cross-field
 * rule that requires at least one of 9b1–9b4 to be answered "Ya".
 * Follows the patterns established in survey-blok1.js and survey-blok2.js.
 */
class SurveyUbBlok1bManager {
    constructor() {
        this.form = document.getElementById('survey-form');
        if (!this.form) return;
        this.init();
    }

    init() {
        this.setupEventListeners();
        // Flag pre-filled "all Tidak" state immediately (matches R207 pattern in blok2)
        this.validateQ9bGroup();
    }

    // ── Event listeners ──────────────────────────────────────────────────────

    setupEventListeners() {
        // Clone-and-replace #submitBtn so only our handler fires on click,
        // preventing the form submit event from bypassing our validation.
        const submitOld = document.getElementById('submitBtn');
        if (submitOld) {
            const submitBtn = submitOld.cloneNode(true);
            submitOld.parentNode.replaceChild(submitBtn, submitOld);
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleSubmit();
            });
        }

        // Real-time cross-field validation on any b1–b4 change
        ['produksi_di_lokasi', 'layanan_makan_minum', 'penjualan_barang', 'aktivitas_jasa_pertanian']
            .forEach(name => {
                document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                    radio.addEventListener('change', () => {
                        this.clearFieldError(name);
                        this.validateQ9bGroup();
                    });
                });
            });

        // Clear individual radio-group errors on change
        [
            'jaringan_usaha', 'uses_internet', 'uses_teknologi_digital',
            'produk_ramah_lingkungan', 'uses_input_lingkungan', 'uses_karya_seni', 'lokasi_usaha'
        ].forEach(name => {
            document.querySelectorAll(`input[name="${name}"]`).forEach(radio => {
                radio.addEventListener('change', () => this.clearFieldError(name));
            });
        });

        // Clear text/textarea errors on input
        [
            'kegiatan_utama', 'produk_utama', 'input_produksi', 'proses_produksi',
            'kode_kbli', 'kategori_lapangan_usaha',
            'jumlah_cabang', 'kp_nama', 'kp_alamat', 'kp_email',
            'kp_negara', 'kp_provinsi', 'kp_kabkota'
        ].forEach(name => {
            const el = this.form.querySelector(`[name="${name}"]`);
            if (el) {
                el.addEventListener('input', () => {
                    el.classList.remove('error');
                    this.clearFieldError(name);
                });
            }
        });
    }

    // ── Cross-field validation: at least one of 9b1–9b4 must be "Ya" ─────────

    validateQ9bGroup() {
        const b1 = document.querySelector('input[name="produksi_di_lokasi"]:checked')?.value;
        const b2 = document.querySelector('input[name="layanan_makan_minum"]:checked')?.value;
        const b3 = document.querySelector('input[name="penjualan_barang"]:checked')?.value;
        const b4 = document.querySelector('input[name="aktivitas_jasa_pertanian"]:checked')?.value;

        // Any "Ya" → immediately valid, clear error
        if (b1 === '1' || b2 === '1' || b3 === '1' || b4 === '1') {
            this.clearQ9bGroupError();
            return true;
        }

        const b3Visible = this.isVisible('sec_9b3');
        const b4Visible = this.isVisible('sec_9b4');

        const b1Tidak = b1 === '2';
        const b2Tidak = b2 === '2';
        const b3Done  = !b3Visible || b3 === '2';
        const b4Done  = !b4Visible || b4 === '2';

        // Guard: only trigger when at least one field has been answered
        const anyAnswered = b1 || b2 || b3 || b4;

        if (anyAnswered && b1Tidak && b2Tidak && b3Done && b4Done) {
            this.showQ9bGroupError(
                "Perhatian! Pastikan salah satu dari rincian b1, b2, b3, atau b4 terisi jawaban 'YA'."
            );
            return false;
        }

        this.clearQ9bGroupError();
        return true;
    }

    // ── Error display ─────────────────────────────────────────────────────────

    showQ9bGroupError(message) {
        document.getElementById('q9b-group-error')?.remove();

        const amberBox = document.querySelector('input[name="produksi_di_lokasi"]')
            ?.closest('.bg-amber-50, [class*="amber"]');
        if (!amberBox) return;

        amberBox.classList.add('q9b-group-has-error');

        const errorEl = document.createElement('div');
        errorEl.id        = 'q9b-group-error';
        errorEl.className = 'ub-err-msg';
        errorEl.setAttribute('role', 'alert');
        errorEl.style.cssText = [
            'color:#b91c1c',
            'background:#fef2f2',
            'border:1px solid #fca5a5',
            'border-radius:.5rem',
            'padding:.5rem .75rem',
            'margin-top:.75rem',
            'font-size:.78rem',
            'font-weight:600'
        ].join(';');
        errorEl.textContent = message;
        amberBox.appendChild(errorEl);
    }

    clearQ9bGroupError() {
        const existing = document.getElementById('q9b-group-error');
        if (existing) {
            existing.closest('.bg-amber-50, [class*="amber"]')
                ?.classList.remove('q9b-group-has-error');
            existing.remove();
        }
        document.querySelector('input[name="produksi_di_lokasi"]')
            ?.closest('.bg-amber-50, [class*="amber"]')
            ?.classList.remove('q9b-group-has-error');
    }

    /** Write an error message and add red border to the field or radio group. */
    showFieldError(fieldName, message) {
        const errEl = this.form.querySelector(`.ub-err-msg[data-field="${fieldName}"]`);
        if (errEl) errEl.textContent = message;
        // For radio groups: add red outline to the .ub-radio-group container
        const radioInput = this.form.querySelector(`input[type="radio"][name="${fieldName}"]`);
        if (radioInput) {
            radioInput.closest('.ub-radio-group')?.classList.add('ub-radio-error');
        }
    }

    /** Clear the .ub-err-msg container and remove error styling. */
    clearFieldError(fieldName) {
        const errEl = this.form.querySelector(`.ub-err-msg[data-field="${fieldName}"]`);
        if (errEl) errEl.textContent = '';
        const input = this.form.querySelector(`[name="${fieldName}"]`);
        if (input) {
            input.classList.remove('error');
            if (input.type === 'radio') {
                input.closest('.ub-radio-group')?.classList.remove('ub-radio-error');
            }
        }
    }

    /** Add red border to text/textarea input AND set error message. */
    showInputError(fieldName, message) {
        this.form.querySelector(`[name="${fieldName}"]`)?.classList.add('error');
        this.showFieldError(fieldName, message);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Extract a human-readable label for fieldName from its paired .ub-label element.
     * Strips leading code prefixes ("a. ", "b1. "), the asterisk span, and common
     * Indonesian question-word prefixes ("Apakah", "Apa", "Berapa", "Di mana").
     */
    getFieldLabel(fieldName) {
        const errEl = this.form.querySelector(`.ub-err-msg[data-field="${fieldName}"]`);
        if (!errEl) return fieldName;
        const parent = errEl.parentElement;
        const label = parent?.querySelector('.ub-label');
        if (!label) return fieldName;

        const clone = label.cloneNode(true);
        clone.querySelectorAll('.ub-required').forEach(el => el.remove());
        let text = clone.textContent.trim();

        // Strip leading letter/digit code, e.g. "a. ", "b1. ", "12. "
        text = text.replace(/^[\da-z]+\.\s+/i, '');
        // Strip common Indonesian question-word prefixes
        text = text.replace(/^(Apakah|Apa|Berapa|Di mana|Bagaimana)\s+/i, '');
        // Capitalize
        text = text.charAt(0).toUpperCase() + text.slice(1);
        // Truncate at first "?" keeping only the subject part
        const qIdx = text.indexOf('?');
        if (qIdx > 0) text = text.substring(0, qIdx).trim();
        // Hard truncate at 70 chars on a word boundary
        if (text.length > 70) {
            let t = text.substring(0, 70);
            const lastSpace = t.lastIndexOf(' ');
            if (lastSpace > 30) t = t.substring(0, lastSpace);
            text = t + '…';
        }

        return text || fieldName;
    }

    isVisible(sectionId) {
        const el = document.getElementById(sectionId);
        return el != null && el.style.display !== 'none';
    }

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

        const checkText = (name) => {
            const el = this.form.querySelector(`[name="${name}"]`);
            if (!el || el.value.trim() === '') {
                this.showInputError(name, `${this.getFieldLabel(name)} wajib diisi`);
                return false;
            }
            const max = el.getAttribute('maxlength');
            if (max && el.value.length > parseInt(max, 10)) {
                this.showInputError(name, `${this.getFieldLabel(name)} maksimal ${max} karakter`);
                return false;
            }
            this.clearFieldError(name);
            return true;
        };

        // ── Always-required fields ───────────────────────────────────────────
        if (!checkText('kegiatan_utama'))          isValid = false;
        if (!checkRadio('produksi_di_lokasi'))      isValid = false;
        if (!checkRadio('layanan_makan_minum'))     isValid = false;
        if (!checkText('produk_utama'))             isValid = false;
        if (!checkText('kode_kbli'))               isValid = false;
        if (!checkText('kategori_lapangan_usaha')) isValid = false;
        if (!checkRadio('jaringan_usaha'))          isValid = false;

        // Q12-Q14 not required for unit pembantu/penunjang (code 6) — cards are hidden
        const isUnitPembantu = this.getRadioValue('jaringan_usaha') === '6';
        if (!isUnitPembantu) {
            if (!checkRadio('uses_internet'))           isValid = false;
            if (!checkRadio('uses_teknologi_digital'))  isValid = false;
            if (!checkRadio('produk_ramah_lingkungan')) isValid = false;
            if (!checkRadio('uses_input_lingkungan'))   isValid = false;
            if (!checkRadio('uses_karya_seni'))         isValid = false;
        }

        // ── Conditionally-required fields ────────────────────────────────────
        if (this.isVisible('sec_9b3')  && !checkRadio('penjualan_barang'))         isValid = false;
        if (this.isVisible('sec_9b4')  && !checkRadio('aktivitas_jasa_pertanian')) isValid = false;
        if (this.isVisible('sec_9c')   && !checkRadio('lokasi_usaha'))             isValid = false;
        if (this.isVisible('sec_9d')   && !checkText('input_produksi'))            isValid = false;
        if (this.isVisible('sec_9e')   && !checkText('proses_produksi'))           isValid = false;
        if (this.isVisible('sec_kantor_pusat_count') && !checkText('jumlah_cabang')) isValid = false;
        if (this.isVisible('sec_info_kp')) {
            ['kp_nama', 'kp_alamat', 'kp_email', 'kp_negara', 'kp_provinsi', 'kp_kabkota']
                .forEach(name => { if (!checkText(name)) isValid = false; });
        }

        // ── Cross-field "at least one Ya" — only when all 9b fields answered ─
        const all9bAnswered =
            this.getRadioValue('produksi_di_lokasi') &&
            this.getRadioValue('layanan_makan_minum') &&
            (!this.isVisible('sec_9b3') || this.getRadioValue('penjualan_barang')) &&
            (!this.isVisible('sec_9b4') || this.getRadioValue('aktivitas_jasa_pertanian'));

        if (all9bAnswered && !this.validateQ9bGroup()) {
            isValid = false;
        }

        return isValid;
    }

    // ── Submit handler ────────────────────────────────────────────────────────

    handleSubmit() {
        if (!this.validateForm()) {
            // Scroll to the first visible inline error
            this.form.querySelector('.ub-err-msg:not(:empty), .ub-input.error')
                ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Validation passed — delegate to survey manager (fetch-based save + navigation)
        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            window.surveyManager.saveForm(true);
        } else {
            this.form.submit();
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('survey-form') && window.location.pathname.includes('blok1b')) {
        window.surveyUbBlok1bManager = new SurveyUbBlok1bManager();
    }
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = SurveyUbBlok1bManager;
}
