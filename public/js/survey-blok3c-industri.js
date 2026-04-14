/**
 * survey-blok3c-industri.js
 * Handles Q318 auto-total (a+b=c) and Q319 auto-total (a+b+c+d+e+f+g+h=i),
 * currency formatting for Q318, and back navigation to Blok IIIB.
 */
(function () {
    'use strict';

    // ── Currency display helpers ──────────────────────────────────────────────
    function parseCurrency(str) {
        if (str === '' || str === null || str === undefined) return 0;
        return parseFloat(String(str).replace(/\./g, '').replace(',', '.')) || 0;
    }

    function formatCurrency(val) {
        if (val === '' || val === null || val === undefined || isNaN(val)) return '';
        return new Intl.NumberFormat('id-ID').format(Math.round(val));
    }

    // ── Q318: total aset auto-calc ────────────────────────────────────────────
    function recalcQ318() {
        const a = parseCurrency(document.getElementById('q318a_display')?.value);
        const b = parseCurrency(document.getElementById('q318b_display')?.value);
        const total = a + b;

        const displayEl = document.getElementById('q318c_display');
        const hiddenEl  = document.getElementById('q318c');
        if (displayEl) displayEl.value = total > 0 ? formatCurrency(total) : '';
        if (hiddenEl)  hiddenEl.value  = total > 0 ? total : '';
    }

    // ── Q319: total modal auto-calc ───────────────────────────────────────────
    function recalcQ319() {
        const ids = ['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h'];
        let sum = 0;
        ids.forEach(id => {
            const el = document.getElementById(id);
            if (el) sum += parseFloat(el.value || 0);
        });

        const displayEl = document.getElementById('q319i_display');
        const hiddenEl  = document.getElementById('q319i');
        if (displayEl) displayEl.value = sum.toFixed(2);
        if (hiddenEl)  hiddenEl.value  = sum.toFixed(2);
    }

    // ── Wire up currency-display inputs ──────────────────────────────────────
    function initCurrencyDisplays() {
        document.querySelectorAll('.currency-display').forEach(display => {
            const targetName = display.dataset.targetName;
            if (!targetName) return;

            const hidden = document.querySelector(`[name="${targetName}"]`);
            if (!hidden) return;

            // Pre-fill display from hidden value
            if (hidden.value) display.value = formatCurrency(hidden.value);

            display.addEventListener('input', () => {
                // Strip non-numeric except minus
                const raw = display.value.replace(/[^0-9-]/g, '');
                hidden.value = raw;
                recalcQ318();
            });

            display.addEventListener('blur', () => {
                const num = parseCurrency(display.value);
                display.value = num ? formatCurrency(num) : '';
                hidden.value = num || '';
                recalcQ318();
            });

            display.addEventListener('focus', () => {
                // Show plain number while editing
                const num = parseCurrency(display.value);
                display.value = num ? num : '';
            });
        });
    }

    // ── Back button ──────────────────────────────────────────────────────────
    function initBackButton() {
        const btn = document.getElementById('back-to-blok3b');
        if (!btn) return;
        btn.addEventListener('click', () => {
            if (window.surveyRoutes?.backToBlok3b) {
                window.location.href = window.surveyRoutes.backToBlok3b;
            } else if (window.surveyRoutes?.blok3b_industri) {
                window.location.href = window.surveyRoutes.blok3b_industri;
            }
        });
    }

    // ── Pre-fill Q318/Q319 from surveyData.blok3b ────────────────────────────
    function prefillFromSurveyData() {
        const blok3b = window.surveyData?.blok3b || {};

        // Q318a / Q318b
        ['q318a','q318b'].forEach(key => {
            const display = document.getElementById(key + '_display');
            const hidden  = document.getElementById(key);
            if (hidden && blok3b[key] !== undefined && blok3b[key] !== '') {
                hidden.value  = blok3b[key];
                if (display) display.value = formatCurrency(blok3b[key]);
            }
        });

        // Q318c_range select
        const rangeEl = document.getElementById('q318c_range');
        if (rangeEl && blok3b['q318c_range']) rangeEl.value = blok3b['q318c_range'];

        // Q318d_area
        const areaEl = document.getElementById('q318d_area');
        if (areaEl && blok3b['q318d_area'] !== undefined) areaEl.value = blok3b['q318d_area'];

        // Q319a-h
        ['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h'].forEach(key => {
            const el = document.getElementById(key);
            if (el && blok3b[key] !== undefined) el.value = blok3b[key];
        });

        recalcQ318();
        recalcQ319();
    }

    // ── Field error helpers (matching blok3b-industri approach) ─────────────
    function showFieldError(el, message) {
        if (!el) return;
        el.classList.add('input-invalid');
        // Prefer a dedicated .form-errors container in the nearest .form-row
        const formRow = el.closest('.form-row');
        const errorContainer = formRow ? formRow.querySelector('.form-errors') : null;
        if (errorContainer) {
            const existing = errorContainer.querySelector('.field-error-message');
            if (existing) existing.remove();
            const errorEl = document.createElement('div');
            errorEl.className = 'field-error-message';
            errorEl.textContent = message;
            errorContainer.appendChild(errorEl);
        } else {
            // Fallback: insert directly after the input
            const existing = el.parentNode.querySelector('.field-error-message');
            if (existing) existing.remove();
            const errorEl = document.createElement('div');
            errorEl.className = 'field-error-message';
            errorEl.textContent = message;
            el.insertAdjacentElement('afterend', errorEl);
        }
    }

    function clearFieldError(el) {
        if (!el) return;
        el.classList.remove('input-invalid');
        const formRow = el.closest('.form-row');
        const errorContainer = formRow ? formRow.querySelector('.form-errors') : null;
        if (errorContainer) {
            const existing = errorContainer.querySelector('.field-error-message');
            if (existing) existing.remove();
        } else {
            const existing = el.parentNode.querySelector('.field-error-message');
            if (existing) existing.remove();
        }
    }

    // ── Q319 total validation (must equal 100%) ───────────────────────────────
    function validateQ319() {
        const displayEl = document.getElementById('q319i_display');
        const total = parseFloat(displayEl?.value || 0);
        const isValid = Math.abs(total - 100) < 0.01;
        if (isValid) {
            clearFieldError(displayEl);
        } else {
            showFieldError(displayEl, 'Total kepemilikan modal harus 100% (saat ini: ' + total.toFixed(2) + '%).');
        }
        return isValid;
    }

    // ── Q318d area validation (required) ─────────────────────────────────────
    function validateQ318d() {
        const areaEl = document.getElementById('q318d_area');
        if (!areaEl) return true;
        const val = areaEl.value.trim();
        const isValid = val !== '' && !isNaN(parseFloat(val));
        if (isValid) {
            clearFieldError(areaEl);
        } else {
            showFieldError(areaEl, 'Luas tanah yang digunakan untuk usaha wajib diisi.');
        }
        return isValid;
    }

    // ── Expose combined validator so blok3a2 manager can check it ────────────
    window.validateBlok3cQ319 = function () {
        const q319ok = validateQ319();
        const q318dok = validateQ318d();
        return q319ok && q318dok;
    };

    // ── Init ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        initCurrencyDisplays();
        prefillFromSurveyData();
        initBackButton();

        // Disable SurveyManager's own saveForm on this page.
        // survey-blok3a2.js owns the save-complete/save-draft buttons here,
        // and it already handles validation + navigation. If we let SurveyManager
        // also fire, it sends a second request to the server which returns 422
        // and injects unwanted inline error text.
        if (window.surveyManager && typeof window.surveyManager.saveForm === 'function') {
            window.surveyManager.saveForm = async function () { /* blok3a2 handles save */ };
        }

        // Q318 listeners
        ['q318a_display','q318b_display'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', recalcQ318);
        });

        // Q319 listeners — recalc total and re-validate border on each change
        ['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', () => { recalcQ319(); validateQ319(); });
        });

        // Q318d — clear red border as soon as user types
        const areaEl = document.getElementById('q318d_area');
        if (areaEl) areaEl.addEventListener('input', () => validateQ318d());
    });
})();
