/**
 * SIBSTR Blok IIIA-2 – Bahan Baku & Bahan Penolong (section 304)
 * Improved: product-card CSS classes, custom delete modal,
 *           integer validation, scrollable preview with arrows,
 *           real-time delete + autosave.
 */

class SurveyBlok3a2Manager {

    /* ─────────────────────────────────────────────────────────── */
    constructor() {
        this.container      = document.getElementById('materials-container');
        this.addBtn         = document.getElementById('add-material-btn');
        this.form           = document.getElementById('survey-form');
        this.previewEl      = document.getElementById('blok3a2-preview-table');
        this.sectionBody    = document.getElementById('section-304-body');
        this.sectionToggle  = document.getElementById('toggle-304');
        this.toggleLabel    = document.getElementById('toggle-304-label');

        // Delete modal references
        this.deleteOverlay  = document.getElementById('delete-confirm-overlay');
        this.deleteCancelBtn = document.getElementById('delete-cancel-btn');
        this.deleteConfirmBtn = document.getElementById('delete-confirm-btn');
        this.deleteModalDesc = document.getElementById('del-modal-desc');
        this._pendingDeleteCard = null;

        // Preview scroll references
        this.previewLeft    = document.getElementById('preview-left');
        this.previewRight   = document.getElementById('preview-right');

        this._autoSaveTimer = null;
        this.init();
    }

    /* ─────────────────────────────────────────────────────────── */
    init() {
        if (!this.container) return;
        this._setupDeleteModal();
        this._setupNavButtons();
        this._setupSectionToggle();
        this._setupPreviewScroll();
        this._loadInitialData();
        this._renderPreview();
    }

    /* ══════════════════════════════════════════════════════════
       EVENT WIRING
    ══════════════════════════════════════════════════════════ */

    _setupDeleteModal() {
        if (!this.deleteOverlay) return;

        this.deleteCancelBtn && this.deleteCancelBtn.addEventListener('click', () => this._closeDeleteModal());
        this.deleteConfirmBtn && this.deleteConfirmBtn.addEventListener('click', () => this._doDelete());

        // Close on overlay background click
        this.deleteOverlay.addEventListener('click', (e) => {
            if (e.target === this.deleteOverlay) this._closeDeleteModal();
        });

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.deleteOverlay.classList.contains('active')) {
                this._closeDeleteModal();
            }
        });
    }

    _setupNavButtons() {
        const bind = (id, fn) => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('click', fn);
        };

        bind('back-to-blok3a', () => {
            if (window.surveyRoutes && window.surveyRoutes.backToBlok3a) {
                window.location.href = window.surveyRoutes.backToBlok3a;
            }
        });
        bind('save-draft',    () => this._saveDraft());
        bind('save-complete', () => this._saveAndContinue());

        if (this.addBtn) this.addBtn.addEventListener('click', () => this._addCard());

        // Delegated events on form
        this.form.addEventListener('input', (e) => this._onInput(e));
        this.form.addEventListener('keypress', (e) => this._onKeypress(e));
        this.form.addEventListener('click',  (e) => this._onClick(e));
    }

    _setupSectionToggle() {
        if (!this.sectionToggle) return;
        this.sectionToggle.addEventListener('click', () => {
            const isOpen = this.sectionBody && this.sectionBody.style.display !== 'none';
            if (this.sectionBody) {
                this.sectionBody.style.display = isOpen ? 'none' : '';
            }
            this.sectionToggle.setAttribute('aria-expanded', String(!isOpen));
            if (isOpen) {
                this.sectionToggle.classList.add('collapsed');
                if (this.toggleLabel) this.toggleLabel.textContent = 'Buka';
            } else {
                this.sectionToggle.classList.remove('collapsed');
                if (this.toggleLabel) this.toggleLabel.textContent = 'Tutup';
            }
        });
    }

    _setupPreviewScroll() {
        const wrapper = this.previewEl;
        const leftBtn  = this.previewLeft;
        const rightBtn = this.previewRight;
        if (!wrapper || !leftBtn || !rightBtn) return;

        const SCROLL_AMT = 240;

        leftBtn.addEventListener('click', () => { wrapper.scrollLeft -= SCROLL_AMT; });
        rightBtn.addEventListener('click', () => { wrapper.scrollLeft += SCROLL_AMT; });

        // Update arrow visibility on scroll
        wrapper.addEventListener('scroll', () => this._updateScrollArrows());
    }

    _updateScrollArrows() {
        const w = this.previewEl;
        const l = this.previewLeft;
        const r = this.previewRight;
        if (!w || !l || !r) return;
        const atStart = w.scrollLeft <= 2;
        const atEnd   = w.scrollLeft + w.clientWidth >= w.scrollWidth - 2;
        l.classList.toggle('hidden-arrow', atStart);
        r.classList.toggle('hidden-arrow', atEnd);
    }

    /* ══════════════════════════════════════════════════════════
       DATA LOADING
    ══════════════════════════════════════════════════════════ */

    _loadInitialData() {
        const saved = (window.surveyData && window.surveyData.materials) || [];
        if (saved.length > 0) {
            saved.forEach((m, i) => this._addCard(m, i === 0));
        } else {
            this._addCard();
        }
    }

    /* ══════════════════════════════════════════════════════════
       CARD MANAGEMENT
    ══════════════════════════════════════════════════════════ */

    _addCard(data, expand) {
        const index = this.container.querySelectorAll('.product-card').length;
        const d = Object.assign({ nama_bahan:'', satuan_standar:'', dn_banyaknya:'', dn_nilai:'', ln_banyaknya:'', ln_nilai:'', negara_asal:'' }, data || {});
        const collapsed = (expand === false || (!data && index > 0));
        this.container.insertAdjacentHTML('beforeend', this._cardHTML(index, d, !collapsed));
        // Trigger preview update
        this._renderPreview();
    }

    _requestDelete(card) {
        const name = card.querySelector('input[name*="[nama_bahan]"]');
        const label = name && name.value ? `"${name.value}"` : 'bahan ini';
        if (this.deleteModalDesc) {
            this.deleteModalDesc.innerHTML = `Bahan <strong>${label}</strong> akan dihapus secara permanen dari daftar.`;
        }
        this._pendingDeleteCard = card;
        this._openDeleteModal();
    }

    _doDelete() {
        const card = this._pendingDeleteCard;
        this._closeDeleteModal();
        if (!card) return;

        // Animate removal
        card.style.transition = 'all 0.25s ease';
        card.style.opacity = '0';
        card.style.transform = 'translateX(20px)';
        setTimeout(() => {
            card.remove();
            this._reindex();
            this._renderPreview();
            this._scheduleAutoSave();
        }, 260);
    }

    _openDeleteModal() {
        if (this.deleteOverlay) this.deleteOverlay.classList.add('active');
    }

    _closeDeleteModal() {
        if (this.deleteOverlay) this.deleteOverlay.classList.remove('active');
        this._pendingDeleteCard = null;
    }

    _reindex() {
        const cards = Array.from(this.container.querySelectorAll('.product-card'));
        cards.forEach((card, newIdx) => {
            card.id = 'pc-' + newIdx;

            const counter = card.querySelector('.material-counter');
            if (counter) counter.textContent = newIdx + 1;

            card.querySelectorAll('input, textarea').forEach(inp => {
                if (inp.name) {
                    inp.name = inp.name.replace(/blok3a2_materials\[\d+\]/, 'blok3a2_materials[' + newIdx + ']');
                }
            });

            card.querySelectorAll('.field-error').forEach(el => {
                el.id = el.id.replace(/\d+/, newIdx);
            });

            const delBtn = card.querySelector('.btn-delete-material');
            if (delBtn) delBtn.dataset.idx = newIdx;
        });
    }

    /* ══════════════════════════════════════════════════════════
       INPUT / CLICK HANDLERS
    ══════════════════════════════════════════════════════════ */

    _onClick(e) {
        // Delete button — must check before header toggle
        const delBtn = e.target.closest('.btn-delete-material');
        if (delBtn) {
            e.stopPropagation();
            const card = delBtn.closest('.product-card');
            if (card) this._requestDelete(card);
            return;
        }

        // Card header toggle (collapse/expand)
        const header = e.target.closest('.product-card > .card-header');
        if (header && !e.target.closest('.btn-delete-material')) {
            const card = header.closest('.product-card');
            if (card) {
                const collapsed = card.classList.toggle('collapsed');
                const icon = header.querySelector('.toggle-icon-svg');
                if (icon) icon.style.transform = collapsed ? 'rotate(-180deg)' : '';
                header.setAttribute('aria-expanded', String(!collapsed));
            }
        }
    }

    _onInput(e) {
        const inp = e.target;
        if (!inp || !inp.name || !inp.name.startsWith('blok3a2_')) return;

        // Clear required-field error as soon as the user types a non-empty value
        const requiredFields = ['nama_bahan', 'dn_banyaknya', 'dn_nilai'];
        if (requiredFields.some(f => inp.name.includes('[' + f + ']')) && inp.value.trim() !== '') {
            this._clearRequiredError(inp);
        }

        // Integer validation for numeric fields
        const numericFields = ['dn_banyaknya', 'dn_nilai', 'ln_banyaknya', 'ln_nilai'];
        const isNumeric = numericFields.some(f => inp.name.includes('[' + f + ']'));
        if (isNumeric) this._validateInteger(inp);

        // Update name preview in card header
        if (inp.name.includes('[nama_bahan]')) {
            const card = inp.closest('.product-card');
            if (card) {
                const preview = card.querySelector('.card-name-preview');
                if (preview) preview.textContent = inp.value ? '— ' + inp.value : '';
            }
        }

        // Live preview update
        this._renderPreview();

        // Debounced autosave
        clearTimeout(this._autoSaveTimer);
        this._autoSaveTimer = setTimeout(() => this._autoSaveField(inp.name, inp.value), 1200);
    }

    _onKeypress(e) {
        const inp = e.target;
        if (!inp || !inp.name) return;
        const numericFields = ['dn_banyaknya', 'dn_nilai', 'ln_banyaknya', 'ln_nilai'];
        const isNumeric = numericFields.some(f => inp.name.includes('[' + f + ']'));
        if (!isNumeric) return;

        // Allow: digits, backspace, delete, arrows, tab, home, end, minus (disallow)
        const allowed = ['0','1','2','3','4','5','6','7','8','9'];
        const ctrl = e.ctrlKey || e.metaKey;
        if (!ctrl && !allowed.includes(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab','Home','End'].includes(e.key)) {
            e.preventDefault();
        }
    }

    _validateInteger(inp) {
        const val = inp.value.trim();
        const errId = 'err-' + inp.name.replace(/[\[\]]/g, '_');
        let errEl = document.getElementById(errId);

        const valid = val === '' || /^\d+$/.test(val);

        inp.classList.toggle('input-invalid', !valid);

        if (!errEl) {
            errEl = document.createElement('span');
            errEl.id = errId;
            errEl.className = 'field-error';
            errEl.textContent = 'Hanya bilangan bulat positif yang diizinkan.';
            inp.insertAdjacentElement('afterend', errEl);
        }
        errEl.classList.toggle('visible', !valid);
        return valid;
    }

    // ── Required-field error helpers for material cards ───────────────────────
    _showRequiredError(inp, message) {
        if (!inp) return;
        inp.classList.add('input-invalid');
        // Only insert if not already present to avoid duplicates
        if (!inp.parentNode.querySelector('[data-req-err]')) {
            const errEl = document.createElement('span');
            errEl.className = 'field-error visible';
            errEl.setAttribute('data-req-err', '1');
            errEl.textContent = message;
            inp.insertAdjacentElement('afterend', errEl);
        }
    }

    _clearRequiredError(inp) {
        if (!inp) return;
        inp.classList.remove('input-invalid');
        const errEl = inp.parentNode.querySelector('[data-req-err]');
        if (errEl) errEl.remove();
    }

    // ── Validate all required fields in every material card ──────────────────
    // Returns true if all cards pass; false + expands failing cards otherwise.
    _validateCards() {
        let allValid = true;
        const cards = Array.from(this.container.querySelectorAll('.product-card'));

        cards.forEach(card => {
            const m = card.id.match(/pc-(\d+)/);
            const ci = m ? m[1] : '0';
            let cardValid = true;

            const checks = [
                { name: `blok3a2_materials[${ci}][nama_bahan]`,   msg: 'Nama bahan wajib diisi.' },
                { name: `blok3a2_materials[${ci}][dn_banyaknya]`, msg: 'Banyaknya Dalam Negeri wajib diisi.' },
                { name: `blok3a2_materials[${ci}][dn_nilai]`,     msg: 'Nilai Dalam Negeri wajib diisi.' },
            ];

            checks.forEach(({ name, msg }) => {
                const inp = card.querySelector(`input[name="${name}"]`);
                if (!inp) return;
                if (inp.value.trim() === '') {
                    this._showRequiredError(inp, msg);
                    cardValid = false;
                } else {
                    this._clearRequiredError(inp);
                }
            });

            // Expand the card so the user can see the highlighted fields
            if (!cardValid) {
                card.classList.remove('collapsed');
                const icon = card.querySelector('.toggle-icon-svg');
                if (icon) icon.style.transform = '';
                const header = card.querySelector('.card-header');
                if (header) header.setAttribute('aria-expanded', 'true');
                allValid = false;
            }
        });

        // Scroll to the first failing card
        if (!allValid) {
            const firstBad = this.container.querySelector('.product-card .input-invalid');
            if (firstBad) firstBad.closest('.product-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        return allValid;
    }

    _notify(message, type = 'error') {
        // Use SurveyManager's status bar if available
        if (window.surveyManager && typeof window.surveyManager.showStatus === 'function') {
            window.surveyManager.showStatus(message, type);
            if (type === 'error' && typeof window.surveyManager.showSubmissionGuidance === 'function') {
                window.surveyManager.showSubmissionGuidance(message);
            }
        } else {
            // Fallback: inline banner near the save button
            let banner = document.getElementById('blok3a2-notify-banner');
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'blok3a2-notify-banner';
                banner.style.cssText = 'margin-top:0.75rem;padding:0.75rem 1rem;border-radius:0.5rem;font-size:0.875rem;font-weight:600;';
                const actions = document.querySelector('.form-actions');
                if (actions) actions.appendChild(banner);
                else document.body.appendChild(banner);
            }
            banner.style.background = type === 'error' ? '#fee2e2' : '#dcfce7';
            banner.style.color      = type === 'error' ? '#b91c1c' : '#166534';
            banner.style.border     = type === 'error' ? '1px solid #fca5a5' : '1px solid #86efac';
            banner.textContent = message;
            banner.style.display = 'block';
            setTimeout(() => { banner.style.display = 'none'; }, 5000);
        }
    }

    _hasValidationErrors() {
        // 1. Validate required fields in material cards (expands & highlights bad cards)
        const cardsValid = this._validateCards();
        if (!cardsValid) return true;

        // 2. Check for lingering integer-format errors (field-error.visible)
        if (this.form.querySelectorAll('.field-error.visible').length > 0) return true;

        // 3. Check any remaining input-invalid state (covers format errors)
        if (this.form.querySelectorAll('.input-invalid').length > 0) return true;

        // 4. Run blok3c-specific validation (Q319 total=100%, Q318d required)
        if (typeof window.validateBlok3cQ319 === 'function') {
            if (!window.validateBlok3cQ319()) return true;
        }

        return false;
    }

    /* ══════════════════════════════════════════════════════════
       CARD HTML BUILDER
    ══════════════════════════════════════════════════════════ */

    _cardHTML(index, d, expanded) {
        const esc = v => String(v || '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
        const collCls = expanded ? '' : 'collapsed';

        return `
<div class="product-card ${collCls}" id="pc-${index}" style="animation:slideIn 0.3s ease-out;">
  <div class="card-header" aria-expanded="${expanded}" role="button" tabindex="0" title="Klik untuk buka/tutup kartu">
    <div class="product-title">
      <span class="material-counter">${index + 1}</span>
      <span style="color:#6b7280;font-size:0.8rem;font-weight:500;">304.</span>
      <span style="font-weight:600;color:#374151;">Bahan baku &amp; penolong</span>
      <span class="card-name-preview" style="color:#6b7280;font-weight:400;font-size:0.85rem;">${d.nama_bahan ? '— ' + esc(d.nama_bahan) : ''}</span>
    </div>
    <div class="card-header-actions">
      <svg class="toggle-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="transition:transform 0.25s;${!expanded ? 'transform:rotate(-180deg)' : ''}">
        <polyline points="18 15 12 9 6 15"></polyline>
      </svg>
      <button type="button" class="btn-delete-material" data-idx="${index}" title="Hapus bahan ini">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="3 6 5 6 21 6"></polyline>
          <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
        </svg>
        Hapus
      </button>
    </div>
  </div>
  <div class="card-body">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:0.25rem;">
      <div class="form-group">
        <label class="form-label">Nama bahan baku &amp; penolong <span style="color:#ef4444;">*</span></label>
        <input type="text" name="blok3a2_materials[${index}][nama_bahan]"
               value="${esc(d.nama_bahan)}" class="form-control"
               placeholder="Contoh: Tepung terigu, Gula pasir" autocomplete="off">
      </div>
      <div class="form-group">
        <label class="form-label">(3) Satuan standar</label>
        <input type="text" name="blok3a2_materials[${index}][satuan_standar]"
               value="${esc(d.satuan_standar)}" class="form-control"
               placeholder="Contoh: kg, ton, liter" autocomplete="off">
      </div>
    </div>

    <div class="dn-ln-grid">
      <div class="dn-box">
        <div class="box-label">Dalam Negeri</div>
        <div class="form-group">
          <label class="form-label" style="font-size:0.8rem;">(4) Banyaknya <span style="color:#ef4444;">*</span></label>
          <input type="text" inputmode="numeric"
                 name="blok3a2_materials[${index}][dn_banyaknya]"
                 value="${esc(d.dn_banyaknya)}" class="form-control"
                 placeholder="0">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label" style="font-size:0.8rem;">(5) Nilai (Rp) <span style="color:#ef4444;">*</span></label>
          <input type="text" inputmode="numeric"
                 name="blok3a2_materials[${index}][dn_nilai]"
                 value="${esc(d.dn_nilai)}" class="form-control"
                 placeholder="0">
        </div>
      </div>
      <div class="ln-box">
        <div class="box-label">Luar Negeri <span style="font-weight:400;font-size:0.75rem;">*)</span></div>
        <div class="form-group">
          <label class="form-label" style="font-size:0.8rem;">(6) Banyaknya</label>
          <input type="text" inputmode="numeric"
                 name="blok3a2_materials[${index}][ln_banyaknya]"
                 value="${esc(d.ln_banyaknya)}" class="form-control"
                 placeholder="0">
        </div>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label" style="font-size:0.8rem;">(7) Nilai (Rp)</label>
          <input type="text" inputmode="numeric"
                 name="blok3a2_materials[${index}][ln_nilai]"
                 value="${esc(d.ln_nilai)}" class="form-control"
                 placeholder="0">
        </div>
      </div>
    </div>

    <div class="form-group" style="margin-top:1rem;margin-bottom:0;">
      <label class="form-label">(8) Negara utama asal bahan baku <span style="color:#9ca3af;font-size:0.75rem;">**)</span></label>
      <input type="text" name="blok3a2_materials[${index}][negara_asal]"
             value="${esc(d.negara_asal)}" class="form-control"
             placeholder="Contoh: Indonesia, Tiongkok" autocomplete="off">
      <p style="margin-top:0.3rem;font-size:0.75rem;color:#9ca3af;">Jika lebih dari satu negara, tuliskan negara dengan nilai impor terbesar.</p>
    </div>
  </div>
</div>`;
    }

    /* ══════════════════════════════════════════════════════════
       PREVIEW TABLE
    ══════════════════════════════════════════════════════════ */

    _renderPreview() {
        if (!this.previewEl) return;
        const cards = Array.from(this.container.querySelectorAll('.product-card'));

        if (cards.length === 0) {
            this.previewEl.innerHTML = `
              <div style="text-align:center;padding:2rem;color:#9ca3af;">
                <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.5rem;display:block;opacity:.35;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Tambahkan bahan baku untuk melihat pratinjau.
              </div>`;
            this._updateScrollArrows();
            return;
        }

        const esc = s => String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const fmt = v => {
            const n = parseInt(String(v || '').replace(/\D/g, ''), 10);
            return !isNaN(n) && n > 0 ? n.toLocaleString('id-ID') : '-';
        };
        const gv = (card, idx, fld) => {
            const el = card.querySelector(`input[name="blok3a2_materials[${idx}][${fld}]"]`);
            return el ? el.value : '';
        };

        const TH = (s, extra='') => `<th style="border:1px solid #e5e7eb;padding:0.55rem 0.6rem;font-size:0.78rem;white-space:nowrap;${extra}">${s}</th>`;
        const TD = (s, align='left', bg='') => `<td style="border:1px solid #e5e7eb;padding:0.5rem 0.6rem;text-align:${align};${bg}font-size:0.8125rem;">${s}</td>`;

        let rows = '';
        cards.forEach((card, di) => {
            const m = card.id.match(/pc-(\d+)/);
            const ci = m ? parseInt(m[1]) : di;
            const odd = di % 2 === 1 ? 'background:#fafafa;' : '';
            rows += `<tr>
              ${TD(di + 1, 'center', odd)}
              ${TD('<strong>' + esc(gv(card, ci, 'nama_bahan')) + '</strong>', 'left', odd)}
              ${TD(esc(gv(card, ci, 'satuan_standar')), 'center', odd)}
              ${TD(fmt(gv(card, ci, 'dn_banyaknya')), 'right', odd)}
              ${TD(fmt(gv(card, ci, 'dn_nilai')), 'right', odd)}
              ${TD(fmt(gv(card, ci, 'ln_banyaknya')), 'right', odd)}
              ${TD(fmt(gv(card, ci, 'ln_nilai')), 'right', odd)}
              ${TD(esc(gv(card, ci, 'negara_asal')), 'center', odd)}
            </tr>`;
        });

        this.previewEl.innerHTML = `
          <table style="width:100%;border-collapse:collapse;min-width:780px;">
            <thead>
              <tr>
                ${TH('(1)<br>No.',    'background:#f1f5f9;text-align:center;min-width:38px;')}
                ${TH('(2)<br>Nama bahan baku &amp; penolong', 'background:#f1f5f9;text-align:left;min-width:180px;')}
                ${TH('(3)<br>Satuan standar', 'background:#f1f5f9;text-align:center;min-width:80px;')}
                ${TH('(4)<br>Banyaknya',  'background:#fef9c3;text-align:center;min-width:90px;')}
                ${TH('(5)<br>Nilai (Rp)', 'background:#fef9c3;text-align:center;min-width:100px;')}
                ${TH('(6)<br>Banyaknya',  'background:#dbeafe;text-align:center;min-width:90px;')}
                ${TH('(7)<br>Nilai (Rp)', 'background:#dbeafe;text-align:center;min-width:100px;')}
                ${TH('(8)<br>Negara asal **)', 'background:#f1f5f9;text-align:center;min-width:120px;')}
              </tr>
              <tr>
                <th colspan="3" style="background:#f8fafc;border:1px solid #e5e7eb;padding:0.3rem 0.6rem;font-size:0.73rem;text-align:center;color:#6b7280;"></th>
                <th colspan="2" style="background:#fef9c3;border:1px solid #e5e7eb;padding:0.3rem 0.6rem;font-size:0.73rem;text-align:center;color:#92400e;">Dalam Negeri</th>
                <th colspan="2" style="background:#dbeafe;border:1px solid #e5e7eb;padding:0.3rem 0.6rem;font-size:0.73rem;text-align:center;color:#1e40af;">Luar Negeri *)</th>
                <th style="background:#f8fafc;border:1px solid #e5e7eb;"></th>
              </tr>
            </thead>
            <tbody>${rows}</tbody>
          </table>`;

        this._updateScrollArrows();
    }

    /* ══════════════════════════════════════════════════════════
       AUTOSAVE
    ══════════════════════════════════════════════════════════ */

    _scheduleAutoSave() {
        clearTimeout(this._autoSaveTimer);
        this._autoSaveTimer = setTimeout(() => this._saveDraft(true), 800);
    }

    _autoSaveField(fieldName, value) {
        if (!window.surveyRoutes || !window.surveyRoutes.autoSave) return;
        const token = document.querySelector('meta[name="csrf-token"]');
        if (!token) return;
        const fd = new FormData();
        fd.append('_token', token.content);
        fd.append('field', fieldName);
        fd.append('value', value);
        fetch(window.surveyRoutes.autoSave, { method: 'POST', body: fd })
            .then(r => r.json())
            .then(() => {}) // silent
            .catch(() => {});
    }

    _showSaved(msg) {
        const el = document.getElementById('autosave-text');
        const box = document.getElementById('autosave-status');
        if (el) el.textContent = msg;
        if (box) {
            box.classList.remove('hidden');
            clearTimeout(this._savedTimer);
            this._savedTimer = setTimeout(() => box.classList.add('hidden'), 3500);
        }
    }

    /* ══════════════════════════════════════════════════════════
       SAVE ACTIONS
    ══════════════════════════════════════════════════════════ */

    _saveDraft(silent = false) {
        if (!window.surveyRoutes || !window.surveyRoutes.saveAll) return;
        const btn = document.getElementById('save-draft');
        const orig = btn ? btn.innerHTML : '';
        if (!silent && btn) { btn.innerHTML = '<span>Menyimpan…</span>'; btn.disabled = true; }

        const fd = new FormData(this.form);
        fetch(window.surveyRoutes.saveAll, {
            method: 'POST', body: fd,
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) this._showSaved('Draft disimpan pukul ' + (d.last_saved_at || ''));
            else if (!silent) this._notify('Gagal menyimpan: ' + (d.message || ''));
        })
        .catch(() => { if (!silent) this._notify('Terjadi kesalahan saat menyimpan draft.'); })
        .finally(() => { if (btn) { btn.innerHTML = orig; btn.disabled = false; } });
    }

    _saveAndContinue() {
        if (this._hasValidationErrors()) {
            this._notify('Perbaiki kesalahan validasi terlebih dahulu sebelum melanjutkan.');
            return;
        }
        const btn = document.getElementById('save-complete');
        if (btn) { btn.innerHTML = '<span>Menyimpan…</span>'; btn.disabled = true; }

        const fd = new FormData(this.form);
        fd.append('is_completed', 'true');

        fetch(window.surveyRoutes.saveAll, {
            method: 'POST', body: fd,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => r.json().then(j => ({ ok: r.ok, body: j })))
        .then(({ ok, body }) => {
            if (!ok || !body || !body.success) throw new Error(body ? body.message : 'Gagal menyimpan.');

            let url = null;
            const nb = body.next_block;
            if (nb === 'blok3b_industri' && window.surveyRoutes.blok3b_industri)       url = window.surveyRoutes.blok3b_industri;
            else if (nb === 'blok3b_nonindustri' && window.surveyRoutes.blok3b_nonindustri) url = window.surveyRoutes.blok3b_nonindustri;
            else if (nb && window.surveyRoutes[nb]) url = window.surveyRoutes[nb];
            if (!url) url = window.surveyRoutes.nextBlok;

            if (url) { window.location.href = url; }
            else {
                this._notify('Data tersimpan, tetapi blok berikutnya tidak dikonfigurasi.', 'info');
                if (btn) { btn.innerHTML = 'Simpan dan Lanjutkan'; btn.disabled = false; }
            }
        })
        .catch(err => {
            console.error(err);
            this._notify('Gagal menyimpan: ' + err.message);
            if (btn) { btn.innerHTML = 'Simpan dan Lanjutkan'; btn.disabled = false; }
        });
    }
}

/* ─── Boot ─────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    window.blok3a2Manager = new SurveyBlok3a2Manager();
});
