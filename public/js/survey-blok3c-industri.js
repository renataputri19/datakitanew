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
        prefillRadioFields();
    }

    // ── Pre-fill prospek/kendala radio fields from surveyData.blok3b ─────────
    function prefillRadioFields() {
        const blok3b = window.surveyData?.blok3b || {};
        ['q320','q321','q322','q323','q324','q325','q326','q327','q328'].forEach(key => {
            const val = blok3b[key];
            if (val === undefined || val === null || val === '') return;
            document.querySelectorAll(`[name="blok3b_industri[${key}]"]`).forEach(r => {
                r.checked = (r.value === String(val));
            });
        });
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

    // ── Prospek & Kendala radio validation (Q701–Q703, tahunan only) ─────────
    var PROSPEK_RADIO_DEFS = [
        { key: 'q320', groupId: 'q701a-group', errorId: 'q701a-error', label: '320a. Permodalan' },
        { key: 'q321', groupId: 'q701b-group', errorId: 'q701b-error', label: '320b. Bahan baku' },
        { key: 'q322', groupId: 'q701c-group', errorId: 'q701c-error', label: '320c. Pemasaran' },
        { key: 'q323', groupId: 'q701d-group', errorId: 'q701d-error', label: '320d. Iklim Usaha' },
        { key: 'q324', groupId: 'q702-group',  errorId: 'q702-error',  label: '321. Rencana merekrut/kembangkan usaha 2026' },
        { key: 'q325', groupId: 'q703a-group', errorId: 'q703a-error', label: '322a. Inovasi (barang dan jasa)' },
        { key: 'q326', groupId: 'q703b-group', errorId: 'q703b-error', label: '322b. Pengembangan Teknologi' },
        { key: 'q327', groupId: 'q703c-group', errorId: 'q703c-error', label: '322c. Pemasaran (marketing)' },
        { key: 'q328', groupId: 'q703d-group', errorId: 'q703d-error', label: '322d. Kemitraan (UMKM, pemerintah, dll)' },
    ];

    function validateProspekKendala() {
        var errors = [];
        if (!document.getElementById('section-prospek-kendala')) return errors;
        PROSPEK_RADIO_DEFS.forEach(function (def) {
            var radios = document.querySelectorAll('[name="blok3b_industri[' + def.key + ']"]');
            if (!radios.length) return;
            var checked = Array.from(radios).some(function (r) { return r.checked; });
            var groupEl = document.getElementById(def.groupId);
            var errorEl = document.getElementById(def.errorId);
            if (!checked) {
                errors.push(def.label);
                if (groupEl) groupEl.classList.add('radio-group-invalid');
                if (errorEl) errorEl.classList.add('visible');
            } else {
                if (groupEl) groupEl.classList.remove('radio-group-invalid');
                if (errorEl) errorEl.classList.remove('visible');
            }
        });
        return errors;
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

        // Q318c_range — when range is selected, 318a/b become optional (and vice versa)
        const rangeSelectEl = document.getElementById('q318c_range');
        if (rangeSelectEl) {
            rangeSelectEl.addEventListener('change', () => {
                const hasRange = rangeSelectEl.value !== '';
                ['q318a_display', 'q318b_display'].forEach(id => {
                    const el = document.getElementById(id);
                    if (!el) return;
                    if (hasRange) {
                        el.removeAttribute('required');
                        clearFieldError(el);
                    } else {
                        el.setAttribute('required', '');
                    }
                });
            });
            // Apply immediately on load in case range is already pre-filled
            if (rangeSelectEl.value !== '') {
                ['q318a_display', 'q318b_display'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.removeAttribute('required');
                });
            }
        }

        // Q701–Q703 radios — clear group error state as soon as a selection is made
        PROSPEK_RADIO_DEFS.forEach(function (def) {
            document.querySelectorAll('[name="blok3b_industri[' + def.key + ']"]').forEach(function (r) {
                r.addEventListener('change', function () {
                    var groupEl = document.getElementById(def.groupId);
                    var errorEl = document.getElementById(def.errorId);
                    if (groupEl) groupEl.classList.remove('radio-group-invalid');
                    if (errorEl) errorEl.classList.remove('visible');
                });
            });
        });

        // ── Validation summary helpers ────────────────────────────────────────

        function collectClientValidationErrors() {
            const form = document.getElementById('survey-form');
            if (!form) return [];
            const errors = [];
            const seen = new Set();

            const addError = (key, label) => {
                if (label && !seen.has(key)) { seen.add(key); errors.push(label); }
            };

            const getLabel = (el) => {
                const subrow = el.closest('.form-subrow');
                if (subrow) {
                    const sublabel = subrow.querySelector('.form-sublabel');
                    if (sublabel) {
                        const row = subrow.closest('.form-row');
                        const qNum = row?.querySelector('.question-number')?.textContent?.trim() ?? '';
                        let text = sublabel.textContent.trim();
                        if (text.length > 60) text = text.substring(0, 60).trimEnd() + '\u2026';
                        return (qNum + ' ' + text).trim() || null;
                    }
                }
                const formRow = el.closest('.form-row');
                if (!formRow) return null;
                const formLabel = formRow.querySelector('.form-label');
                if (!formLabel) return null;
                const qNum = formLabel.querySelector('.question-number')?.textContent?.trim() ?? '';
                const spans = formLabel.querySelectorAll('span:not(.question-number)');
                let title = spans.length > 0 ? spans[0].textContent.trim() : '';
                if (title.length > 70) title = title.substring(0, 70).trimEnd() + '\u2026';
                return (qNum + ' ' + title).trim() || null;
            };

            // q318a / q318b are optional when c1 range is selected
            const rangeEl = document.getElementById('q318c_range');
            const rangeSelected = rangeEl && rangeEl.value !== '';

            form.querySelectorAll('[required]').forEach(el => {
                if (el.type === 'hidden') return;
                if (rangeSelected && (el.id === 'q318a_display' || el.id === 'q318b_display')) {
                    el.classList.remove('input-invalid');
                    return;
                }
                const parentRow = el.closest('.form-row');
                if (parentRow && (parentRow.style.display === 'none' || parentRow.style.opacity === '0')) return;

                let isEmpty;
                if (el.classList.contains('currency-display')) {
                    const targetName = el.getAttribute('data-target-name');
                    const hidden = targetName
                        ? form.querySelector(`input[type="hidden"][name="${targetName}"]`)
                        : null;
                    isEmpty = hidden
                        ? (hidden.value ?? '').toString().trim() === ''
                        : (el.value ?? '').trim() === '';
                } else {
                    isEmpty = (el.value ?? '').trim() === '';
                }

                if (isEmpty) {
                    el.classList.add('input-invalid');
                    addError(el.name || el.id, getLabel(el));
                } else {
                    el.classList.remove('input-invalid');
                }
            });

            return errors;
        }

        function showSummary(errors) {
            document.getElementById('blok3c-industri-validation-summary')?.remove();
            const formActions = document.querySelector('.form-actions');
            if (!formActions || !errors.length) return;
            const esc = s => String(s).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
            formActions.insertAdjacentHTML('beforebegin',
                `<div id="blok3c-industri-validation-summary" class="validation-summary">
                    <div class="validation-summary-header">
                        <span class="validation-summary-icon">&#9888;</span>
                        <h4 class="validation-summary-title">Data belum lengkap</h4>
                    </div>
                    <p class="validation-summary-desc">Mohon lengkapi bidang berikut sebelum menyimpan:</p>
                    <ul class="validation-summary-list">
                        ${errors.map(e => `<li class="validation-summary-item">${esc(e)}</li>`).join('')}
                    </ul>
                </div>`
            );
            document.getElementById('blok3c-industri-validation-summary')
                ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function handleSaveComplete() {
            document.getElementById('blok3c-industri-validation-summary')?.remove();

            // 1. Material card validation (blok3a2)
            let cardsValid = true;
            if (window.blok3a2Manager && typeof window.blok3a2Manager._validateCards === 'function') {
                cardsValid = window.blok3a2Manager._validateCards();
            }

            // 2. Required-field validation (Q318a/b/d, Q319a-h)
            const errors = collectClientValidationErrors();

            // 3. Q319 total must equal 100%
            const totalEl = document.getElementById('q319i_display');
            const total = parseFloat(totalEl?.value || 0);
            if (totalEl && Math.abs(total - 100) >= 0.01) {
                errors.push('319. Total kepemilikan modal harus 100% (saat ini: ' + total.toFixed(2) + '%)');
                totalEl.classList.add('input-invalid');
            } else if (totalEl) {
                totalEl.classList.remove('input-invalid');
            }

            // 4. Prospek & Kendala radio validation (Q701–Q703, tahunan only)
            const prospekErrors = validateProspekKendala();
            prospekErrors.forEach(e => errors.push(e));

            if (!cardsValid || errors.length > 0) {
                if (errors.length > 0) {
                    showSummary(errors);
                } else {
                    // Only card errors — show a generic card summary
                    const div = document.createElement('div');
                    div.id = 'blok3c-industri-validation-summary';
                    div.className = 'validation-summary';
                    div.innerHTML = `
                        <div class="validation-summary-header">
                            <span class="validation-summary-icon">&#9888;</span>
                            <h4 class="validation-summary-title">Data belum lengkap</h4>
                        </div>
                        <p class="validation-summary-desc">Data bahan baku belum lengkap. Perbaiki kartu yang ditandai merah di atas.</p>`;
                    const formActions = document.querySelector('.form-actions');
                    if (formActions) formActions.insertAdjacentElement('beforebegin', div);
                    div.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }

            // All valid — delegate to blok3a2Manager
            if (window.blok3a2Manager) {
                window.blok3a2Manager._saveAndContinue();
            }
        }

        // Clone-replace save-complete button to strip blok3a2's click handler
        const saveOld = document.getElementById('save-complete');
        if (saveOld) {
            const saveBtn = saveOld.cloneNode(true);
            saveOld.parentNode.replaceChild(saveBtn, saveOld);
            saveBtn.addEventListener('click', (e) => {
                e.preventDefault();
                handleSaveComplete();
            });
        }

        // Override showSubmissionGuidance so server-side error messages
        // also appear in the summary panel instead of near the button
        if (window.surveyManager) {
            window.surveyManager.showSubmissionGuidance = function (message) {
                document.getElementById('blok3c-industri-validation-summary')?.remove();
                const formActions = document.querySelector('.form-actions');
                if (!formActions) return;
                const esc = s => String(s).replace(/[&<>]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;' }[c]));
                formActions.insertAdjacentHTML('beforebegin',
                    `<div id="blok3c-industri-validation-summary" class="validation-summary">
                        <div class="validation-summary-header">
                            <span class="validation-summary-icon">&#9888;</span>
                            <h4 class="validation-summary-title">Data belum lengkap</h4>
                        </div>
                        <p class="validation-summary-desc">${esc(message)}</p>
                    </div>`
                );
                document.getElementById('blok3c-industri-validation-summary')
                    ?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };
        }
    });
})();
