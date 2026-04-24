/**
 * SIBSTR Survey Blok IIIA JavaScript Module
 * Handles dynamic product cards, consistent state management, and tab switching without re-rendering.
 */

class SurveyBlok3aManager {
    constructor() {
        this.container = document.getElementById('products-container');
        this.lainnyaContainer = document.getElementById('lainnya-grid-container');
        this.totalContainer = document.getElementById('total-grid-container');
        this.addProductBtn = document.getElementById('add-product-btn');
        this.form = document.getElementById('survey-form');
        this.previewContainer = document.getElementById('blok3a-preview-table');

        // Delete modal references
        this.deleteOverlay    = document.getElementById('delete-confirm-overlay');
        this.deleteCancelBtn  = document.getElementById('delete-cancel-btn');
        this.deleteConfirmBtn = document.getElementById('delete-confirm-btn');
        this.deleteModalDesc  = document.getElementById('del-modal-desc');
        this._pendingDeleteIndex = null;

        // State
        this.products = []; // Array to store current product structures
        this.cardActiveQuarters = {}; // per-card quarter state: { index: quarter }
        this._autoSaveTimer = null;

        // Month Definitions — overridden by server config if available
        const serverConf = window.surveyData?.quarterConf;
        if (serverConf && typeof serverConf === 'object') {
            this.quarterConf = serverConf;
        } else {
            this.quarterConf = {
                'dec_prev': { label: 'Des 2024', months: ['2024_des'] },
                'q1': { label: 'Triwulan I', months: ['2025_jan', '2025_feb', '2025_mar'] },
                'q2': { label: 'Triwulan II', months: ['2025_apr', '2025_mei', '2025_jun'] },
                'q3': { label: 'Triwulan III', months: ['2025_jul', '2025_agu', '2025_sep'] },
                'q4': { label: 'Triwulan IV', months: ['2025_okt', '2025_nov', '2025_des'] }
            };
        }

        // Default active quarter = first key in quarterConf
        const firstQKey = window.surveyData?.firstQuarter || Object.keys(this.quarterConf)[0];
        this.lainnyaActiveQuarter = firstQKey;
        this.totalActiveQuarter = firstQKey;

        // Triwulanan when quarterConf has ≤ 2 entries (dec_prev + one quarter)
        this.isTriwulanan = Object.keys(this.quarterConf).length <= 2;

        // Build monthLabels map from quarterConf
        const fullMonthNames = {
            'jan':'Januari','feb':'Februari','mar':'Maret','apr':'April','mei':'Mei',
            'jun':'Juni','jul':'Juli','agu':'Agustus','sep':'September',
            'okt':'Oktober','nov':'November','des':'Desember'
        };
        this.monthLabels = {};
        for (const qConf of Object.values(this.quarterConf)) {
            for (const mKey of qConf.months) {
                const parts = mKey.match(/^(\d{4})_(\w+)$/);
                this.monthLabels[mKey] = parts ? (fullMonthNames[parts[2]] || parts[2]) : mKey;
            }
        }

        // Legacy compatibility (kept for any code that still references old keys)
        if (!this.monthLabels['2024_des']) this.monthLabels['2024_des'] = 'Desember';
        if (!this.monthLabels['2025_jan']) {
            const legacyMonths = { 'jan':'Januari','feb':'Februari','mar':'Maret','apr':'April','mei':'Mei',
                'jun':'Juni','jul':'Juli','agu':'Agustus','sep':'September','okt':'Oktober','nov':'November','des':'Desember' };
            Object.entries(legacyMonths).forEach(([m,l]) => {
                if (!this.monthLabels[`2025_${m}`]) this.monthLabels[`2025_${m}`] = l;
            });
        };

        this.init();
    }

    init() {
        if (!this.container) return;

        console.log('Blok IIIA Manager Initializing...');
        this._setupDeleteModal();
        this.setupEventListeners();

        // Render Static Sections (Lainnya & Total)
        // We use the V2 logic to replace the container content with correct per-quarter grids
        const lainnyaData = window.surveyData?.lainnya || {};
        const totalData = window.surveyData?.totals || {};

        if (this.lainnyaContainer) this.lainnyaContainer.innerHTML = this.generateStaticQuarterGridsV2('lainnya', lainnyaData);
        if (this.totalContainer) this.totalContainer.innerHTML = this.generateStaticQuarterGridsV2('total', totalData);

        this.loadInitialData();
        // Set default visibility for sections
        this.updateAllCardsVisibility();
        this.updateSpecialSectionVisibility('lainnya', this.lainnyaActiveQuarter);
        this.updateSpecialSectionVisibility('total', this.totalActiveQuarter);
        this.setupAutoCalculation();

        // Initialize display inputs from hidden values
        this.initializeDisplayValues();

        // Initial preview render
        this.renderPreviewTable();
    }

    // Ensure totals are computed on load
    setupAutoCalculation() {
        try {
            this.calculateTotals();
            this.recalcHargaSatuanForAllCards(true);
        } catch (e) {
            console.warn('setupAutoCalculation noop:', e);
        }
    }

    setupEventListeners() {
        // Add Product
        if (this.addProductBtn) {
            this.addProductBtn.addEventListener('click', () => this.addProduct());
        }

        // Per-card quarter tab clicks
        this.container.addEventListener('click', (e) => {
            const tab = e.target.closest('.quarter-tab');
            if (tab) {
                const card = tab.closest('.product-card');
                if (!card) return;
                const quarter = tab.dataset.quarter;
                const index = this.getCardIndex(card.id);
                if (index == null) return;
                this.cardActiveQuarters[index] = quarter;
                this.setCardActiveQuarter(card, quarter);
            }
        });

        // Lainnya tabs
        const lainnyaTabs = document.getElementById('lainnya-tabs');
        if (lainnyaTabs) {
            lainnyaTabs.addEventListener('click', (e) => {
                const tab = e.target.closest('.quarter-tab');
                if (!tab) return;
                this.lainnyaActiveQuarter = tab.dataset.quarter;
                this.updateTabsActiveState(lainnyaTabs, this.lainnyaActiveQuarter);
                this.updateSpecialSectionVisibility('lainnya', this.lainnyaActiveQuarter);
            });
        }

        // Total tabs
        const totalTabs = document.getElementById('total-tabs');
        if (totalTabs) {
            totalTabs.addEventListener('click', (e) => {
                const tab = e.target.closest('.quarter-tab');
                if (!tab) return;
                this.totalActiveQuarter = tab.dataset.quarter;
                this.updateTabsActiveState(totalTabs, this.totalActiveQuarter);
                this.updateSpecialSectionVisibility('total', this.totalActiveQuarter);
            });
        }

        // Navigation
        const setupNavBtn = (id, callback) => {
            const btn = document.getElementById(id);
            if (btn) btn.addEventListener('click', callback);
        };

        setupNavBtn('back-to-blok2', () => window.location.href = window.surveyRoutes.backToBlok2);
        setupNavBtn('save-draft', () => this.saveDraft());

        // Clone-and-replace #save-complete to strip survey.js's competing click handler,
        // preventing duplicate validation messages near the button.
        const saveCompleteOld = document.getElementById('save-complete');
        if (saveCompleteOld) {
            const saveCompleteBtn = saveCompleteOld.cloneNode(true);
            saveCompleteOld.parentNode.replaceChild(saveCompleteBtn, saveCompleteOld);
            saveCompleteBtn.addEventListener('click', () => this.saveAndContinue());
        }

        // Real-time error clearing for mandatory fields 302, 305, 306
        const mandatoryIds = [
            'q302a', 'q302b', 'q302c', 'q302d', 'q302e', 'q302f',
            'q305a_maklun_nilai', 'q305b_maklun_pct', 'q305_online'
        ];
        mandatoryIds.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', () => this.clearFieldError(id));
            }
        });

        // Global Event Delegation for Inputs (AutoSave & Calc)
        this.form.addEventListener('input', (e) => this.handleInput(e));
        // Format on blur for display inputs
        this.form.addEventListener('blur', (e) => this.handleBlur(e), true);
        // Format numeric fields on blur
        this.form.addEventListener('blur', (e) => this.formatNumericOnBlur(e), true);
        this.form.addEventListener('click', (e) => {
            // 0a. Handle Add Ekspor Row
            const addEksporBtn = e.target.closest('.btn-add-ekspor-row');
            if (addEksporBtn) {
                this.addEksporRow(addEksporBtn.dataset.cardIndex);
                return;
            }
            // 0b. Handle Delete Ekspor Row
            const delEksporBtn = e.target.closest('.btn-delete-ekspor-row');
            if (delEksporBtn) {
                this.deleteEksporRow(delEksporBtn.dataset.cardIndex, delEksporBtn.dataset.rowIndex);
                return;
            }
            // 1. Handle Delete
            const deleteBtn = e.target.closest('.btn-delete');
            if (deleteBtn) {
                const index = deleteBtn.dataset.index;
                this._requestDelete(index);
                return;
            }

            // 2. Handle Header Toggle (Collapse/Expand)
            const header = e.target.closest('.card-header');
            if (header) {
                const card = header.closest('.product-card');
                if (card) {
                    const isCollapsed = card.classList.toggle('collapsed');
                    const toggleBtn = header.querySelector('.card-toggle');
                    if (toggleBtn) {
                        toggleBtn.setAttribute('aria-expanded', !isCollapsed);
                    }
                }
            }
        });
    }

    loadInitialData() {
        // Load products from server data or default
        const serverProducts = window.surveyData?.products || [];

        if (serverProducts.length > 0) {
            serverProducts.forEach((p, idx) => {
                // Ensure array-like index mapping needs valid unique IDs, 
                // but standard arrays in PHP mean we can just use 0,1,2... 
                // However, deleting index 1 invalidates subsequent indices for PHP arrays often.
                // We will use a running counter for DOM IDs but keep data structure as array for Laravel.
                this.addProduct(p);
            });
        } else {
            this.addProduct(); // Add one empty card
        }
    }

    addProduct(data = null) {
        const index = this.products.length; // Simple array index for now
        const productData = data || {
            jenis_barang: '',
            satuan: '',
            kbli_5digit: '',
            persen_ekspor: '',
            negara_ekspor: '',
            rincian_ekspor: [],
            banyaknya: {},
            nilai: {},
            harga_satuan: {}
        };

        this.products.push(productData);
        const cardHTML = this.createProductCardHTML(index, productData);
        this.container.insertAdjacentHTML('beforeend', cardHTML);

        // New rows should remain collapsed by default; no auto-expand on add

        // Set default quarter for this card and show (use first key from quarterConf)
        const defaultQKey = Object.keys(this.quarterConf)[0];
        this.cardActiveQuarters[index] = defaultQKey;
        const card = document.getElementById(`product-card-${index}`);
        if (card) this.setCardActiveQuarter(card, this.cardActiveQuarters[index]);
    }

    _setupDeleteModal() {
        if (!this.deleteOverlay) return;

        this.deleteCancelBtn && this.deleteCancelBtn.addEventListener('click', () => this._closeDeleteModal());
        this.deleteConfirmBtn && this.deleteConfirmBtn.addEventListener('click', () => this._doDelete());

        this.deleteOverlay.addEventListener('click', (e) => {
            if (e.target === this.deleteOverlay) this._closeDeleteModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.deleteOverlay.classList.contains('active')) {
                this._closeDeleteModal();
            }
        });
    }

    _openDeleteModal() {
        if (this.deleteOverlay) this.deleteOverlay.classList.add('active');
    }

    _closeDeleteModal() {
        if (this.deleteOverlay) this.deleteOverlay.classList.remove('active');
        this._pendingDeleteIndex = null;
    }

    _requestDelete(indexStr) {
        const index = parseInt(indexStr);
        const card = document.getElementById(`product-card-${index}`);
        if (!card) return;

        const nameInput = card.querySelector('input[name*="[jenis_barang]"]');
        const label = nameInput && nameInput.value ? `"${nameInput.value}"` : 'produk ini';

        if (this.deleteModalDesc) {
            if (index === 0) {
                this.deleteModalDesc.innerHTML = `Produk pertama <strong>${label}</strong> tidak dapat dihapus. Semua isian akan <strong>dikosongkan/direset</strong>.`;
            } else {
                this.deleteModalDesc.innerHTML = `Produk <strong>${label}</strong> akan dihapus secara permanen dari daftar.`;
            }
        }

        this._pendingDeleteIndex = index;
        this._openDeleteModal();
    }

    _doDelete() {
        const index = this._pendingDeleteIndex;
        this._closeDeleteModal();
        if (index === null || index === undefined) return;

        const card = document.getElementById(`product-card-${index}`);
        if (!card) return;

        if (index === 0) {
            // First card: clear all inputs and autosave instead of removing
            card.querySelectorAll('input').forEach(input => {
                input.value = '';
                this.handleInput({ target: input });
            });
            this.calculateTotals();
        } else {
            // Subsequent cards: clear inputs first so autosave sends empty values, then remove
            card.querySelectorAll('input').forEach(input => { input.value = ''; });
            card.remove();
            this.reindexProducts();
            this.calculateTotals();
            this._scheduleAutoSave();
        }
    }

    reindexProducts() {
        const cards = this.container.querySelectorAll('.product-card');
        cards.forEach((card, newIndex) => {
            card.id = `product-card-${newIndex}`;
            card.querySelector('.question-number').textContent = `${301 + newIndex}.`; // Wait, 301 is title. Card counter:
            card.querySelector('.product-counter').textContent = newIndex + 1;

            // Update inputs
            card.querySelectorAll('input').forEach(input => {
                // name="blok3a_products[OLD][field][month]" -> "blok3a_products[newIndex][field][month]"
                const oldName = input.name;
                const newName = oldName.replace(/blok3a_products\[\d+\]/, `blok3a_products[${newIndex}]`);
                input.name = newName;
            });

            // Update delete button
            const delBtn = card.querySelector('.btn-delete');
            if (delBtn) delBtn.dataset.index = newIndex;

            // Update ekspor rows container ID and button data-card-index
            const eksporContainer = card.querySelector('.ekspor-rows-container');
            if (eksporContainer) eksporContainer.id = `ekspor-rows-${newIndex}`;
            const addEkspBtn = card.querySelector('.btn-add-ekspor-row');
            if (addEkspBtn) addEkspBtn.dataset.cardIndex = newIndex;
            card.querySelectorAll('.btn-delete-ekspor-row').forEach(btn => {
                btn.dataset.cardIndex = newIndex;
            });
        });

        // Update local state length
        this.products = new Array(cards.length).fill({});
    }

    createProductCardHTML(index, data) {
        // We generate HTML for ALL quarters, hidden/shown via CSS classes
        return `
        <div class="product-card ${index === 0 ? '' : 'collapsed'}" id="product-card-${index}">
            <div class="card-header">
                <div class="product-title">
                    <span class="product-counter">${index + 1}</span>
                    <span class="question-number">301.</span>
                    Jenis Barang yang dihasilkan/diproduksi
                </div>
                <div class="card-header-actions">
                    <button type="button" class="card-toggle" aria-expanded="${index === 0 ? 'true' : 'false'}" title="Tutup/Buka isian produk">
                        <svg class="toggle-icon-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                    
                    <button type="button" class="btn-delete" data-index="${index}" title="Hapus produk ini">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        <span>Hapus</span>
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                <div class="form-group">
                    <label class="form-label">Nama/Jenis Barang</label>
                    <input type="text" name="blok3a_products[${index}][jenis_barang]" 
                           value="${data.jenis_barang || ''}" 
                           class="form-control jenis-barang-input" 
                           placeholder="Contoh: Minyak Kelapa Sawit, Pakaian Jadi, dll">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Satuan/Unit untuk Banyaknya</label>
                    <input type="text" name="blok3a_products[${index}][satuan]"
                           value="${data.satuan || ''}"
                           class="form-control unit-input"
                           placeholder="Contoh: kg, ton, pcs">
                    <p class="form-hint" style="margin-top:0.3rem;font-size:0.78rem;color:#6b7280;">Catatan: Bila satuan yang digunakan tidak standar seperti 'botol' atau 'kaleng', agar dikonversikan ke satuan metrik seperti liter, M3, dsb.</p>
                </div>

                ${!this.isTriwulanan ? `
                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">KBLI 5 Digit</label>
                        <input type="text" name="blok3a_products[${index}][kbli_5digit]"
                               value="${data.kbli_5digit || ''}"
                               class="form-control kbli5digit-input"
                               maxlength="5"
                               pattern="[0-9]{5}"
                               placeholder="Contoh: 10610">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Persentase yang diekspor (*)</label>
                        <div style="position:relative;">
                            <input type="number" name="blok3a_products[${index}][persen_ekspor]"
                                   value="${data.persen_ekspor || ''}"
                                   class="form-control persen-ekspor-input"
                                   min="0" max="100" step="0.01"
                                   placeholder="0">
                            <span style="position:absolute;right:0.6rem;top:50%;transform:translateY(-50%);color:#6b7280;font-size:0.875rem;pointer-events:none;">%</span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label">Negara tujuan utama ekspor (**)</label>
                        <input type="text" name="blok3a_products[${index}][negara_ekspor]"
                               value="${data.negara_ekspor || ''}"
                               class="form-control negara-ekspor-input"
                               placeholder="Contoh: Amerika Serikat">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:1rem;padding:0.875rem;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:0.5rem;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                        <label class="form-label" style="margin-bottom:0;color:#166534;font-weight:600;font-size:0.875rem;">Rincian Provinsi Tujuan Ekspor <span style="color:#ef4444;">*</span></label>
                        <button type="button" class="btn-add-ekspor-row" data-card-index="${index}"
                                style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.3rem 0.75rem;
                                       border-radius:0.4rem;border:1px solid #86efac;background:#dcfce7;color:#166534;
                                       font-size:0.75rem;font-weight:600;cursor:pointer;transition:background 0.15s;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Tambah
                        </button>
                    </div>
                    <span class="ekspor-provinsi-error" style="display:none;color:#dc2626;font-size:0.8rem;margin-bottom:0.4rem;"></span>
                    <div class="ekspor-rows-container" id="ekspor-rows-${index}">
                        ${this.generateEksporRows(index, data.rincian_ekspor || [])}
                    </div>
                </div>` : ''}

                <div class="form-group">
                    <label class="form-label">Uraian Data Bulanan/Triwulanan</label>
                    <div class="quarter-tabs" role="tablist" aria-label="Pilih Triwulan untuk Produk">
                        ${Object.entries(this.quarterConf).map(([qKey, qConf], i) =>
                            `<button type="button" class="quarter-tab${i===0?' active':''}" data-quarter="${qKey}">${qConf.label}</button>`
                        ).join('')}
                    </div>
                    
                    ${this.generateAllQuartersGrid(index, data)}
                    
                </div>
            </div>
        </div>
        `;
    }

    generateAllQuartersGrid(index, data) {
        let html = '';

        // Loop through all quarter definitions
        for (const [qKey, qConf] of Object.entries(this.quarterConf)) {
            const months = qConf.months; // ['2025_jan', etc]

            html += `<div class="data-grid quarter-grid quarter-section" data-quarter="${qKey}">`;

            // Header Row (Label + Month Names)
            html += `<div class="grid-header">Uraian</div>`;
            months.forEach(m => {
                html += `<div class="grid-header">${this.monthLabels[m]}</div>`;
            });

            // Row 1: Banyaknya
            html += `<div class="grid-label section-title">Banyaknya</div>`;
            months.forEach(m => {
                const val = data.banyaknya?.[m] || '';
                html += `
                <div class="grid-input">
                    <span class="mobile-month-label">${this.monthLabels[m]}</span>
                    <input type="text"
                           value="${this.formatCurrencyDisplay(val)}"
                           class="form-control numeric-display banyaknya-display" 
                           data-month="${m}"
                           data-target-name="blok3a_products[${index}][banyaknya][${m}]"
                           placeholder="0">
                    <input type="hidden"
                           name="blok3a_products[${index}][banyaknya][${m}]"
                           value="${val}"
                           class="form-control banyaknya-input" data-month="${m}">
                </div>`;
            });

            // Row 2: Nilai
            html += `<div class="grid-label section-title">Nilai (Rp)</div>`;
            months.forEach(m => {
                const val = data.nilai?.[m] || '';
                html += `
                <div class="grid-input">
                    <span class="mobile-month-label">${this.monthLabels[m]}</span>
                    <input type="text"
                           value="${this.formatCurrencyDisplay(val)}"
                           class="form-control numeric-display nilai-display"
                           data-month="${m}"
                           data-target-name="blok3a_products[${index}][nilai][${m}]"
                           placeholder="0">
                    <input type="hidden"
                           name="blok3a_products[${index}][nilai][${m}]"
                           value="${val}"
                           class="form-control nilai-input"
                           data-month="${m}">
                </div>`;
            });

            // Row 3: Harga/Satuan (auto-calculated, read-only)
            html += `<div class="grid-label section-title">Harga Satuan</div>`;
            months.forEach(m => {
                const val = data.harga_satuan?.[m] || '';
                html += `
                <div class="grid-input">
                    <span class="mobile-month-label">${this.monthLabels[m]}</span>
                    <input type="text"
                           value="${this.formatCurrencyDisplay(val)}"
                           class="form-control numeric-display harga-satuan-display readonly" 
                           data-month="${m}"
                           data-target-name="blok3a_products[${index}][harga_satuan][${m}]"
                           readonly placeholder="0">
                    <input type="hidden" 
                           name="blok3a_products[${index}][harga_satuan][${m}]"
                           value="${val}"
                           class="form-control readonly harga-satuan-input" data-month="${m}">
                </div>`;
            });

            html += `</div>`; // End grid
        }
        return html;
    }

    renderStaticSections() {
        // "Lainnya" and "Total" also need to respect the Active Quarter switching
        // We will generate the HTML for all quarters for them too.

        const lainnyaData = window.surveyData?.lainnya || {};
        const totalData = window.surveyData?.totals || {};

        let lainnyaHTML = '';
        let totalHTML = '';

        for (const [qKey, qConf] of Object.entries(this.quarterConf)) {
            const months = qConf.months;

            // --- Lainnya ---
            lainnyaHTML += `<div class="quarter-content" data-quarter="${qKey}" style="display:contents">`;
            lainnyaHTML += `<div class="grid-label">Nilai (Rp)</div>`; // Label column
            months.forEach(m => {
                const val = lainnyaData.nilai?.[m] || '';
                lainnyaHTML += `
                <div class="grid-input">
                   <span class="mobile-month-label">${this.monthLabels[m]}</span>
                   <input type="number" step="0.01"
                          name="blok3a_lainnya[nilai][${m}]"
                          value="${val}"
                          class="form-control lainnya-nilai-input"
                          data-month="${m}"
                          placeholder="0">
                </div>`;
            });
            lainnyaHTML += `</div>`;

            // --- Total ---
            totalHTML += `<div class="quarter-content" data-quarter="${qKey}" style="display:contents">`;
            totalHTML += `<div class="grid-label">Total (Rp)</div>`;
            months.forEach(m => {
                const val = totalData[m] || 0;
                totalHTML += `
                <div class="grid-input">
                    <span class="mobile-month-label">${this.monthLabels[m]}</span>
                    <input type="number" readonly
                           name="blok3a_totals[${m}]"
                           value="${val}"
                           class="form-control total-input font-bold text-green-600"
                           data-month="${m}"
                           placeholder="0">
                </div>`;
            });
            totalHTML += `</div>`;
        }

        // For grid systems to work with "display:contents", the parent needs to be the grid.
        // The container `lainnya-grid-container` is the grid.
        // We need to make sure headers are also there or handle them differently.
        // Simplified approach for static sections: Just inject headers dynamically? 
        // Or just render all headers and hide/show. Creates valid grid? 
        // Let's use the same structure: separate grids per quarter, store them all, hide/show the container.

        // Re-write render for Static Sections:

        this.lainnyaContainer.innerHTML = this.generateStaticQuarterGrids('lainnya', lainnyaData);
        this.totalContainer.innerHTML = this.generateStaticQuarterGrids('total', totalData);
    }

    generateStaticQuarterGrids(type, data) {
        let allHtml = '';
        for (const [qKey, qConf] of Object.entries(this.quarterConf)) {
            const months = qConf.months;
            allHtml += `<div class="quarter-section contents" data-quarter="${qKey}">`; // 'contents' to respect parent grid?
            // Actually parent is `grid`. If we wrap in div with display:block, grid breaks.
            // If we use display:contents, valid.

            // Wait, hide/show `display:contents` elements is tricky. 
            // Better: The container IS the wrapper. We put separate Grid implementations for each quarter.
            // But styling uses `quarter-grid` class on the container.
            // Let's change Blade: Remove `quarter-grid` from container, put it on inner divs.
        }
        return allHtml;
        // Correction: The Blade has `id="lainnya-grid-container" class="data-grid quarter-grid"`.
        // This expects direct children to be grid items.
        // Plan: Clear class from container in JS, append full Grid Divs.
    }

    // Better implementation for Static Sections
    generateStaticQuarterGridsV2(type, data) {
        // We will replace the innerHTML of the container.
        // The container should NOT be a grid itself if we want to toggle whole blocks.
        // I will remove `quarter-grid` and `data-grid` from parent in init if present.
        if (this.lainnyaContainer) this.lainnyaContainer.className = '';
        if (this.totalContainer) this.totalContainer.className = '';

        let html = '';
        for (const [qKey, qConf] of Object.entries(this.quarterConf)) {
            html += `<div class="data-grid quarter-grid quarter-section" data-quarter="${qKey}">`;

            // Header
            html += `<div class="grid-header">Uraian</div>`;
            qConf.months.forEach(m => html += `<div class="grid-header">${this.monthLabels[m]}</div>`);

            // Body
            html += `<div class="grid-label">${type === 'lainnya' ? 'Nilai' : 'Total'}</div>`;
            qConf.months.forEach(m => {
                // Value extraction
                let val = '';
                let name = '';
                let cls = '';

                if (type === 'lainnya') {
                    val = data.nilai?.[m] || '';
                    name = `blok3a_lainnya[nilai][${m}]`;
                    cls = 'form-control lainnya-nilai-input';
                } else {
                    val = data[m] || 0;
                    name = `blok3a_totals[${m}]`;
                    cls = 'form-control total-input readonly font-bold text-green-600';
                }

                html += `
                <div class="grid-input">
                     <label class="block text-xs text-gray-500 mb-1 sm:hidden">${this.monthLabels[m]}</label>
                     <input type="text"
                           value="${this.formatCurrencyDisplay(val)}"
                           class="form-control numeric-display ${type === 'lainnya' ? 'lainnya-nilai-display' : 'total-display'} ${type === 'total' ? 'readonly' : ''}"
                           data-month="${m}"
                           data-target-name="${name}"
                           ${type === 'total' ? 'readonly' : ''}
                           placeholder="0">
                     <input type="hidden"
                           name="${name}"
                           value="${val}"
                           class="${cls}"
                           data-month="${m}">
                </div>
                `;
            });

            html += `</div>`;
        }
        return html;
    }

    // Logic: Quarter Visibility (per-card and per-section)
    getCardIndex(cardId) {
        const m = cardId && cardId.match(/product-card-(\d+)/);
        if (!m) return null;
        return parseInt(m[1]);
    }

    setCardActiveQuarter(cardElem, quarter) {
        // Tabs active state
        const tabs = cardElem.querySelectorAll('.quarter-tab');
        tabs.forEach(t => t.classList.toggle('active', t.dataset.quarter === quarter));
        // Toggle sections within this card only
        const sections = cardElem.querySelectorAll('.quarter-section');
        sections.forEach(sec => {
            if (sec.dataset.quarter === quarter) {
                sec.classList.remove('hidden');
            } else {
                sec.classList.add('hidden');
            }
        });
    }

    updateAllCardsVisibility() {
        const cards = this.container.querySelectorAll('.product-card');
        cards.forEach(card => {
            const idx = this.getCardIndex(card.id);
            const q = this.cardActiveQuarters[idx] || Object.keys(this.quarterConf)[0];
            this.setCardActiveQuarter(card, q);
        });
    }

    updateSpecialSectionVisibility(type, quarter) {
        const container = type === 'lainnya' ? this.lainnyaContainer : this.totalContainer;
        if (!container) return;
        container.querySelectorAll('.quarter-section').forEach(sec => {
            sec.classList.toggle('hidden', sec.dataset.quarter !== quarter);
        });
    }

    updateTabsActiveState(tabsContainer, activeQuarter) {
        tabsContainer.querySelectorAll('.quarter-tab').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.quarter === activeQuarter);
        });
    }

    // Logic: Input Handling & Autosave
    handleInput(e, shouldAutoSave = true) {
        const input = e.target;
        const card = input.closest('.product-card');
        const month = input.dataset ? input.dataset.month : null;

        // Clear first-card required field errors on input
        if (input.name && input.name.startsWith('blok3a_products[0]') && input.value.trim() !== '') {
            input.classList.remove('input-invalid');
            const errEl = input.parentNode.querySelector('[data-req-err]');
            if (errEl) errEl.remove();
        }

        // Handle formatted display inputs
        if (input.classList && input.classList.contains('numeric-display')) {
            const targetName = input.getAttribute('data-target-name');
            const hidden = targetName ? this.form.querySelector(`input[type="hidden"][name="${targetName}"]`) : null;
            const numericValue = this.parseCurrencyToNumber(input.value);

            if (hidden) {
                if (numericValue === null) {
                    hidden.value = '';
                } else {
                    hidden.value = Number(numericValue).toFixed(2);
                }
            }

            // Compute dependent values
            const isNilai = input.classList.contains('nilai-display') || input.classList.contains('lainnya-nilai-display');
            const isBanyaknya = input.classList.contains('banyaknya-display');
            if (isNilai) {
                this.calculateTotals(month);
                if (card) this.recalcHargaSatuanForCard(card, month);
            }
            if (isBanyaknya) {
                if (card) this.recalcHargaSatuanForCard(card, month);
            }

            // Autosave with hidden field name
            if (shouldAutoSave && window.surveyManager && targetName) {
                input.classList.remove('bg-green-50', 'border-green-500');
                window.surveyManager.scheduleAutoSave(targetName, hidden ? hidden.value : '');
                setTimeout(() => {
                    if (input.value) input.classList.add('bg-green-50', 'border-green-500');
                    setTimeout(() => input.classList.remove('bg-green-50', 'border-green-500'), 2000);
                }, 500);
            }

            // Update preview
            this.renderPreviewTable();
            return;
        }

        // Attach numeric restriction once for rincian_ekspor numeric fields
        if (input.name && input.name.includes('[rincian_ekspor]') && (input.name.includes('[jumlah]') || input.name.includes('[nilai]'))) {
            if (!input.hasAttribute('data-numeric-restricted')) {
                input.setAttribute('data-numeric-restricted', '1');
                input.addEventListener('input', () => {
                    const val = input.value;
                    const cleaned = val.replace(/[^0-9,.]/g, '');
                    if (val !== cleaned) input.value = cleaned;
                });
            }
        }

        // Full-form autosave for rincian_ekspor fields (individual saves can't handle nested arrays)
        if (input.name && input.name.includes('[rincian_ekspor]')) {
            if (input.name.includes('[provinsi]') && input.value.trim() !== '') {
                const cardEl = input.closest('.product-card');
                if (cardEl) {
                    const errSpan = cardEl.querySelector('.ekspor-provinsi-error');
                    if (errSpan) errSpan.style.display = 'none';
                }
            }
            this._scheduleAutoSave();
            return;
        }

        // 1. Calculate Totals if needed
        if (input.classList.contains('nilai-input') || input.classList.contains('lainnya-nilai-input')) {
            this.calculateTotals(input.dataset.month);
            // Update price for the affected product/month
            if (card) this.recalcHargaSatuanForCard(card, month);
        }

        // Update price when quantity changes
        if (input.classList.contains('banyaknya-input')) {
            if (card) this.recalcHargaSatuanForCard(card, month);
        }

        // 2. Autosave
        if (shouldAutoSave && window.surveyManager) {
            // Remove validation styles
            input.classList.remove('bg-green-50', 'border-green-500');

            window.surveyManager.scheduleAutoSave(input.name, input.value);

            // Feedback
            setTimeout(() => {
                if (input.value) input.classList.add('bg-green-50', 'border-green-500');
                setTimeout(() => input.classList.remove('bg-green-50', 'border-green-500'), 2000);
            }, 500);
        }

        // 3. Update preview table live
        this.renderPreviewTable();
    }

    handleBlur(e) {
        const input = e.target;
        if (!input || !input.classList) return;
        if (input.classList.contains('numeric-display')) {
            const numericValue = this.parseCurrencyToNumber(input.value);
            if (numericValue === null) {
                input.value = '';
            } else {
                input.value = this.formatCurrencyDisplay(numericValue);
            }
        }
    }

    // Compute Harga/Satuan = Nilai / Banyaknya in the given card
    recalcHargaSatuanForCard(cardElem, specificMonth = null, skipAutoSave = false) {
        if (!cardElem) return;
        const months = ['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'];
        const targetMonths = specificMonth ? [specificMonth] : months;

        targetMonths.forEach(m => {
            const nilaiInp = cardElem.querySelector(`.nilai-input[data-month="${m}"]`);
            const qtyInp = cardElem.querySelector(`.banyaknya-input[data-month="${m}"]`);
            const priceInp = cardElem.querySelector(`.harga-satuan-input[data-month="${m}"]`);

            if (!priceInp) return;
            const nilai = nilaiInp ? parseFloat(nilaiInp.value) || 0 : 0;
            const qty = qtyInp ? parseFloat(qtyInp.value) || 0 : 0;
            const price = qty > 0 ? (nilai / qty) : '';

            priceInp.value = price;
            priceInp.readOnly = true;
            priceInp.classList.add('readonly');
            priceInp.setAttribute('aria-readonly', 'true');

            // Update display field for harga satuan
            const priceDisp = cardElem.querySelector(`.harga-satuan-display[data-month="${m}"]`);
            if (priceDisp) {
                priceDisp.value = price !== '' ? this.formatCurrencyDisplay(price) : '';
            }

            // Auto-save computed price for persistence (unless skipped)
            if (!skipAutoSave && window.surveyManager && priceInp.name) {
                window.surveyManager.scheduleAutoSave(priceInp.name, priceInp.value);
            }
        });

        // Refresh preview to reflect new price
        this.renderPreviewTable();
    }

    // Compute prices for all product cards (used on initial load)
    recalcHargaSatuanForAllCards(skipAutoSave = true) {
        const cards = Array.from(this.container.querySelectorAll('.product-card'));
        cards.forEach(card => this.recalcHargaSatuanForCard(card, null, skipAutoSave));
    }

    calculateTotals(specificMonth = null) {
        // Robust totals: compute for the requested month or for all months
        const allMonths = Object.values(this.quarterConf).flatMap(q => q.months);
        const monthsToCalc = specificMonth ? [specificMonth] : allMonths;

        monthsToCalc.forEach(month => {
            let sum = 0;

            // Sum Products (Nilai per bulan) from hidden inputs
            const productInputs = document.querySelectorAll(`.nilai-input[data-month="${month}"]`);
            productInputs.forEach(inp => {
                sum += parseFloat(inp.value) || 0;
            });

            // Add Lainnya (Nilai per bulan)
            const lainnyaInput = document.querySelector(`.lainnya-nilai-input[data-month="${month}"]`);
            if (lainnyaInput) {
                sum += parseFloat(lainnyaInput.value) || 0;
            }

            // Update Total (readonly hidden input)
            const totalInput = document.querySelector(`.total-input[data-month="${month}"]`);
            if (totalInput) {
                totalInput.value = sum;
                // Update display field
                const totalDisp = document.querySelector(`.total-display[data-month="${month}"]`);
                if (totalDisp) {
                    totalDisp.value = this.formatCurrencyDisplay(sum);
                }
                // Auto-save computed total silently
                if (window.surveyManager && totalInput.name) {
                    window.surveyManager.scheduleAutoSave(totalInput.name, Number(sum).toFixed(2), true);
                }
            }
        });

        // Re-render preview after recalculation
        this.renderPreviewTable();
    }

    // Preview Table Renderer (read-only)
    renderPreviewTable() {
        if (!this.previewContainer) return;
        // Dynamic months from quarterConf (replaces hardcoded 2024/2025 list)
        const months = Object.values(this.quarterConf).flatMap(q => q.months);

        // Build label: always include year extracted from the key (e.g. "2026_jan" → "Januari 2026")
        const fullMonthNames = {
            'jan':'Januari','feb':'Februari','mar':'Maret','apr':'April','mei':'Mei',
            'jun':'Juni','jul':'Juli','agu':'Agustus','sep':'September',
            'okt':'Oktober','nov':'November','des':'Desember'
        };
        const getMonthLabel = (m) => {
            const parts = m.match(/^(\d{4})_(\w+)$/);
            if (!parts) return m;
            return (fullMonthNames[parts[2]] || parts[2]) + ' ' + parts[1];
        };

        // Gather product cards from DOM
        const productCards = Array.from(this.container.querySelectorAll('.product-card'));

        // Build table HTML with three sub-rows per product
        let html = '<div class="preview-table"><table class="preview-table-el"><thead><tr>';
        html += '<th class="sticky-col">Kode/Nama</th>';
        if (!this.isTriwulanan) html += '<th style="text-align:left;min-width:170px;">Detail Produk</th>';
        html += '<th>Uraian</th>';
        months.forEach(m => {
            html += `<th>${getMonthLabel(m)}</th>`;
        });
        html += '</tr></thead><tbody>';

        productCards.forEach((card, idx) => {
            const nameInput = card.querySelector('input[name^="blok3a_products"][name$="[jenis_barang]"]');
            const name = nameInput ? (nameInput.value || `Produk ${idx + 1}`) : `Produk ${idx + 1}`;
            const code = `301.${idx + 1}`;

            // Helper selectors for each month within this card
            const getBanyaknya = (m) => {
                const sel = `input[name^=\"blok3a_products\"][name*=\"[banyaknya][${m}]\"]`;
                const inp = card.querySelector(sel);
                return inp ? (parseFloat(inp.value) || 0) : 0;
            };
            const getNilai = (m) => {
                const inp = card.querySelector(`.nilai-input[data-month=\"${m}\"]`);
                return inp ? (parseFloat(inp.value) || 0) : 0;
            };
            const getHarga = (m) => {
                const sel = `input[name^=\"blok3a_products\"][name*=\"[harga_satuan][${m}]\"]`;
                const inp = card.querySelector(sel);
                return inp ? (parseFloat(inp.value) || 0) : 0;
            };
            const getUnit = () => {
                const inp = card.querySelector('.unit-input');
                return inp ? (inp.value || '') : '';
            };

            const getKbli = () => { const i = card.querySelector('.kbli5digit-input'); return i ? i.value.trim() : ''; };
            const getPersenEkspor = () => { const i = card.querySelector('.persen-ekspor-input'); return i ? i.value.trim() : ''; };
            const getNegaraEkspor = () => { const i = card.querySelector('.negara-ekspor-input'); return i ? i.value.trim() : ''; };

            const kbliVal = getKbli();
            const persenVal = getPersenEkspor();
            const negaraVal = getNegaraEkspor();
            const persenFormatted = persenVal !== '' ? (parseFloat(persenVal).toLocaleString('id-ID', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' %') : '-';

            // Row 1: Banyaknya
            html += `<tr>`;
            html += `<td class="sticky-col" rowspan="3"><div class="code">${code}</div><div class="name">${this.escapeHtml(name)}</div></td>`;
            if (!this.isTriwulanan) {
                html += `<td rowspan="3" style="vertical-align:top;padding:0.5rem 0.625rem;font-size:0.8125rem;line-height:1.5;border:1px solid #e5e7eb;">`;
                html += `<div style="margin-bottom:0.4rem;"><span style="font-weight:600;color:#374151;display:block;">KBLI 5 Digit</span><span style="color:#1f2937;">${this.escapeHtml(kbliVal) || '-'}</span></div>`;
                html += `<div style="margin-bottom:0.4rem;"><span style="font-weight:600;color:#374151;display:block;">Persentase Diekspor (*)</span><span style="color:#1f2937;">${persenFormatted}</span></div>`;
                html += `<div><span style="font-weight:600;color:#374151;display:block;">Negara Tujuan Ekspor (**)</span><span style="color:#1f2937;">${this.escapeHtml(negaraVal) || '-'}</span></div>`;
                html += `</td>`;
            }
            html += `<td>Banyaknya</td>`;
            months.forEach(m => {
                const qty = getBanyaknya(m);
                const unit = getUnit();
                const unitText = unit ? ` ${this.escapeHtml(unit)}` : '';
                html += `<td class="num">${this.formatNumber(qty)}${unitText}</td>`;
            });
            html += `</tr>`;

            // Row 2: Nilai
            html += `<tr>`;
            html += `<td>Nilai</td>`;
            months.forEach(m => html += `<td class="num">${this.formatNumber(getNilai(m))}</td>`);
            html += `</tr>`;

            // Row 3: Harga/Satuan
            html += `<tr>`;
            html += `<td>Harga/Satuan</td>`;
            months.forEach(m => html += `<td class="num">${this.formatNumber(getHarga(m))}</td>`);
            html += `</tr>`;
        });

        // 302. Lainnya row (triwulanan only, when lainnya section exists)
        if (this.isTriwulanan && this.lainnyaContainer) {
            html += `<tr>`;
            html += `<td class="sticky-col"><div class="code" style="font-weight:700;font-size:0.8125rem;">302.</div><div class="name" style="font-size:0.875rem;">Lainnya</div></td>`;
            html += `<td>Nilai</td>`;
            months.forEach(m => {
                const v = this.lainnyaContainer.querySelector(`.lainnya-nilai-input[data-month="${m}"]`);
                html += `<td class="num">${this.formatNumber(v ? (parseFloat(v.value) || 0) : 0)}</td>`;
            });
            html += `</tr>`;
        }

        // Total (303) — only Nilai row is applicable
        const totalValues = months.map(m => {
            const v = document.querySelector(`.total-input[data-month="${m}"]`);
            return v ? (parseFloat(v.value) || 0) : 0;
        });
        html += `<tr class="total-row">`;
        html += `<td class="sticky-col"><div class="code">303.</div><div class="name">Total</div></td>`;
        if (!this.isTriwulanan) html += `<td style="border:1px solid #e5e7eb;"></td>`;
        html += `<td>Nilai</td>`;
        totalValues.forEach(v => html += `<td class="num bold">${this.formatNumber(v)}</td>`);
        html += `</tr>`;

        html += '</tbody></table></div>';
        this.previewContainer.innerHTML = html;
    }

    formatNumber(n) {
        if (!isFinite(n) || n === 0) return '';
        return new Intl.NumberFormat('id-ID').format(n);
    }

    // Initialize display fields from hidden raw values
    initializeDisplayValues() {
        if (!this.form) return;
        const displays = Array.from(this.form.querySelectorAll('.numeric-display'));
        displays.forEach(disp => {
            const targetName = disp.getAttribute('data-target-name');
            if (!targetName) return;
            const hidden = this.form.querySelector(`input[type="hidden"][name="${targetName}"]`);
            if (!hidden) return;
            const v = hidden.value;
            if (v !== '' && v !== null && v !== undefined) {
                const num = parseFloat(String(v).replace(',', '.'));
                if (!isNaN(num)) {
                    disp.value = this.formatCurrencyDisplay(num);
                }
            }
        });
    }

    // Parsing and formatting utilities (similar to Blok 3B)
    parseCurrencyToNumber(raw) {
        if (raw === undefined || raw === null) return null;
        const s = String(raw).trim();
        if (s === '') return null;
        const normalized = s.replace(/\./g, '').replace(/,/g, '.').replace(/[^0-9.\-]/g, '');
        // Disallow negatives
        if (normalized.includes('-')) return null;
        const num = parseFloat(normalized);
        if (isNaN(num)) return null;
        if (num < 0) return null;
        return Number(num.toFixed(2));
    }

    formatCurrencyDisplay(num) {
        try {
            const n = typeof num === 'number' ? num : parseFloat(num);
            if (isNaN(n)) return '';
            return n.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        } catch (_e) {
            return String(num ?? '');
        }
    }

    escapeHtml(str) {
        return (str || '').replace(/[&<>"]|'/g, s => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', '\'': '&#39;' }[s]));
    }

    // Validation helpers for mandatory fields 302, 305, 306
    validateMandatoryFields() {
        const requiredFields = [
            { id: 'q302a', label: '302a. Keuntungan/kerugian penjualan barang dalam bentuk yang sama' },
            { id: 'q302b', label: '302b. Penjualan kekayaan intelektual' },
            { id: 'q302c', label: '302c. Nilai jasa yang tidak berkaitan dengan proses produksi' },
            { id: 'q302d', label: '302d. Tenaga listrik yang dijual' },
            { id: 'q302e', label: '302e. Pendapatan non operasional' },
            { id: 'q302f', label: '302f. Lainnya (pendapatan lainnya)' },
            { id: 'q305a_maklun_nilai', label: '305a. Nilai pendapatan dari jasa industri (maklun)' },
            { id: 'q305b_maklun_pct', label: '305b. Persentase nilai pendapatan jasa industri luar negeri' },
            { id: 'q305_online', label: '306. Persentase pendapatan dari usaha online' },
        ];

        let errors = [];
        let firstErrorField = null;

        // Remove previous validation summary
        const existingSummary = document.getElementById('blok3a-validation-summary');
        if (existingSummary) existingSummary.remove();

        // Clear previous field-level error states
        requiredFields.forEach(({ id }) => {
            const input = document.getElementById(id);
            if (input) input.classList.remove('field-error');
            const errSpan = document.getElementById('err-' + id);
            if (errSpan) errSpan.style.display = 'none';
        });

        // Clear previous first-card product field errors
        const _p0Card = document.getElementById('product-card-0');
        if (_p0Card) {
            _p0Card.querySelectorAll('[data-req-err]').forEach(el => el.remove());
            _p0Card.querySelectorAll('.input-invalid').forEach(el => el.classList.remove('input-invalid'));
        }

        // Check each field
        requiredFields.forEach(({ id, label }) => {
            const input = document.getElementById(id);
            if (!input) return;
            if (input.value === '' || input.value === null || input.value === undefined) {
                errors.push(label);
                input.classList.add('field-error');
                const errSpan = document.getElementById('err-' + id);
                if (errSpan) errSpan.style.display = 'block';
                if (!firstErrorField) firstErrorField = input;
            }
        });

        // ── Validate first product card required fields (index 0 only) ───────────
        const p0Card = document.getElementById('product-card-0');
        if (p0Card) {
            const p0Checks = [
                { sel: 'input[name="blok3a_products[0][jenis_barang]"]', label: 'Produk 1: Nama/Jenis Barang wajib diisi' },
                { sel: 'input[name="blok3a_products[0][satuan]"]',       label: 'Produk 1: Satuan/Unit wajib diisi' },
            ];
            if (!this.isTriwulanan) {
                p0Checks.push(
                    { sel: 'input[name="blok3a_products[0][kbli_5digit]"]',  label: 'Produk 1: KBLI 5 Digit wajib diisi' },
                    { sel: 'input[name="blok3a_products[0][persen_ekspor]"]', label: 'Produk 1: Persentase yang diekspor wajib diisi' },
                    { sel: 'input[name="blok3a_products[0][negara_ekspor]"]', label: 'Produk 1: Negara tujuan utama ekspor wajib diisi' },
                );
            }
            let p0HasError = false;
            p0Checks.forEach(({ sel, label }) => {
                const inp = p0Card.querySelector(sel);
                if (!inp) return;
                if (inp.value.trim() === '') {
                    inp.classList.add('input-invalid');
                    if (!inp.parentNode.querySelector('[data-req-err]')) {
                        const errEl = document.createElement('span');
                        errEl.className = 'field-error visible';
                        errEl.setAttribute('data-req-err', '1');
                        errEl.textContent = 'Wajib diisi';
                        inp.insertAdjacentElement('afterend', errEl);
                    }
                    errors.push(label);
                    p0HasError = true;
                    if (!firstErrorField) firstErrorField = inp;
                }
            });
            if (p0HasError) {
                p0Card.classList.remove('collapsed');
                const icon = p0Card.querySelector('.toggle-icon-svg');
                if (icon) icon.style.transform = '';
            }
        }

        // Validate rincian_ekspor: first product card (index 0) only — needs ≥1 provinsi filled
        if (!this.isTriwulanan) {
            const p0CardEkspor = document.getElementById('product-card-0');
            if (p0CardEkspor) {
                const eksporRows = Array.from(p0CardEkspor.querySelectorAll('.ekspor-rows-container .ekspor-row'));
                const hasProvinsi = eksporRows.some(row => {
                    const pInp = row.querySelector('input[name*="[provinsi]"]');
                    return pInp && pInp.value.trim() !== '';
                });
                const errSpan = p0CardEkspor.querySelector('.ekspor-provinsi-error');
                if (!hasProvinsi) {
                    if (errSpan) {
                        errSpan.textContent = 'Minimal 1 rincian provinsi tujuan ekspor wajib diisi.';
                        errSpan.style.display = 'block';
                    }
                    p0CardEkspor.classList.remove('collapsed');
                    const icon = p0CardEkspor.querySelector('.toggle-icon-svg');
                    if (icon) icon.style.transform = '';
                    if (!firstErrorField) firstErrorField = errSpan || p0CardEkspor;
                    errors.push('Produk 1: Minimal 1 provinsi tujuan ekspor wajib diisi');
                } else {
                    if (errSpan) errSpan.style.display = 'none';
                }
            }
            // Clear rincian ekspor errors on subsequent cards (index 1+)
            Array.from(this.container.querySelectorAll('.product-card')).slice(1).forEach(card => {
                const errSpan = card.querySelector('.ekspor-provinsi-error');
                if (errSpan) errSpan.style.display = 'none';
            });
        }

        if (errors.length > 0) {
            // Build and inject validation summary before form actions
            const summaryHTML = `
            <div id="blok3a-validation-summary" class="validation-summary">
                <div class="validation-summary-header">
                    <span class="validation-summary-icon">&#9888;</span>
                    <h4 class="validation-summary-title">Data belum lengkap</h4>
                </div>
                <p class="validation-summary-desc">Mohon lengkapi bidang berikut sebelum menyimpan:</p>
                <ul class="validation-summary-list">
                    ${errors.map(e => `<li class="validation-summary-item">${e}</li>`).join('')}
                </ul>
            </div>`;
            const formActions = this.form.querySelector('.form-actions');
            if (formActions) {
                formActions.insertAdjacentHTML('beforebegin', summaryHTML);
            }
            // Scroll to and focus the first invalid field
            if (firstErrorField) {
                firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => firstErrorField.focus(), 400);
            }
            return false;
        }

        return true;
    }

    clearFieldError(fieldId) {
        const input = document.getElementById(fieldId);
        if (input) input.classList.remove('field-error');
        const errSpan = document.getElementById('err-' + fieldId);
        if (errSpan) errSpan.style.display = 'none';
        // Remove summary if all mandatory fields are now filled
        const summary = document.getElementById('blok3a-validation-summary');
        if (summary) {
            const anyStillEmpty = [
                'q302a', 'q302b', 'q302c', 'q302d', 'q302e', 'q302f',
                'q305a_maklun_nilai', 'q305b_maklun_pct', 'q305_online'
            ].some(id => {
                const el = document.getElementById(id);
                return el && el.value === '';
            });
            if (!anyStillEmpty) summary.remove();
        }
    }

    // Actions
    _scheduleAutoSave() {
        clearTimeout(this._autoSaveTimer);
        this._autoSaveTimer = setTimeout(() => this._saveDraftSilent(), 800);
    }

    _saveDraftSilent() {
        if (!window.surveyRoutes?.saveAll) return;
        const token = document.querySelector('meta[name="csrf-token"]');
        if (!token) return;
        const fd = new FormData(this.form);
        fetch(window.surveyRoutes.saveAll, {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': token.content }
        })
        .then(r => r.json())
        .then(() => {})
        .catch(() => {});
    }

    saveDraft() {
        // Collect form data and submit
        const data = new FormData(this.form);
        // Use SurveyManager or fetch
        if (window.surveyRoutes?.saveAll) {
            const btn = document.getElementById('save-draft');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Menyimpan...';
            btn.disabled = true;

            fetch(window.surveyRoutes.saveAll, {
                method: 'POST',
                body: data,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
                .then(res => res.json())
                .then(data => {
                    alert('Draft berhasil disimpan');
                })
                .catch(err => {
                    console.error(err);
                    alert('Gagal menyimpan draft');
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }
    }

    saveAndContinue() {
        // Validate mandatory fields before proceeding
        if (!this.validateMandatoryFields()) {
            return;
        }

        const data = new FormData(this.form);
        // Mark completion so backend can compute next_block correctly
        data.append('is_completed', 'true');

        const btn = document.getElementById('save-complete');
        btn.innerHTML = 'Menyimpan...';
        btn.disabled = true;

        fetch(window.surveyRoutes.saveAll, {
            method: 'POST',
            body: data,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => res.json().then(json => ({ ok: res.ok, body: json })))
            .then(({ ok, body }) => {
                if (!ok || !body?.success) {
                    throw new Error(body?.message || 'Save failed');
                }

                // Decide next URL based on server-provided next_block
                let nextUrl = null;
                const nextBlock = body?.next_block;
                if (nextBlock) {
                    if (nextBlock === 'blok3b_industri' && window.surveyRoutes?.blok3b_industri) {
                        nextUrl = window.surveyRoutes.blok3b_industri;
                    } else if (nextBlock === 'blok3b_nonindustri' && window.surveyRoutes?.blok3b_nonindustri) {
                        nextUrl = window.surveyRoutes.blok3b_nonindustri;
                    } else if (nextBlock === 'blok6' && window.surveyRoutes?.blok6) {
                        nextUrl = window.surveyRoutes.blok6;
                    } else if (window.surveyRoutes && window.surveyRoutes[nextBlock]) {
                        nextUrl = window.surveyRoutes[nextBlock];
                    }
                }

                // Fallback to predefined nextBlok if server did not send next_block
                if (!nextUrl && window.surveyRoutes?.nextBlok) {
                    nextUrl = window.surveyRoutes.nextBlok;
                }

                if (nextUrl) {
                    window.location.href = nextUrl;
                } else {
                    alert('Data tersimpan tetapi tidak ada blok berikutnya.');
                    btn.innerHTML = 'Simpan dan Lanjutkan';
                    btn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal menyimpan data');
                btn.innerHTML = 'Simpan dan Lanjutkan';
                btn.disabled = false;
            });
    }

    generateEksporRows(cardIndex, rows) {
        if (!rows || rows.length === 0) {
            return this.generateEksporRowHTML(cardIndex, 0, {});
        }
        return rows.map((row, ri) => this.generateEksporRowHTML(cardIndex, ri, row)).join('');
    }

    generateEksporRowHTML(cardIndex, rowIndex, rowData) {
        const v = rowData || {};
        return `<div class="ekspor-row" data-row-index="${rowIndex}">
            <div>
                <label style="font-size:0.75rem;color:#6b7280;display:block;margin-bottom:0.2rem;">Provinsi Tujuan</label>
                <input type="text" name="blok3a_products[${cardIndex}][rincian_ekspor][${rowIndex}][provinsi]"
                       value="${this.escapeHtml(v.provinsi || '')}" class="form-control" placeholder="Nama provinsi">
            </div>
            <div>
                <label style="font-size:0.75rem;color:#6b7280;display:block;margin-bottom:0.2rem;">Banyaknya</label>
                <input type="text" inputmode="numeric" name="blok3a_products[${cardIndex}][rincian_ekspor][${rowIndex}][jumlah]"
                       value="${this.escapeHtml(v.jumlah || '')}" class="form-control" placeholder="0">
            </div>
            <div>
                <label style="font-size:0.75rem;color:#6b7280;display:block;margin-bottom:0.2rem;">Nilai (Rp)</label>
                <input type="text" inputmode="numeric" name="blok3a_products[${cardIndex}][rincian_ekspor][${rowIndex}][nilai]"
                       value="${this.escapeHtml(v.nilai || '')}" class="form-control" placeholder="0">
            </div>
            <div>
                <button type="button" class="btn-delete-ekspor-row" data-card-index="${cardIndex}" data-row-index="${rowIndex}"
                        title="Hapus baris ini"
                        style="display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;
                               border-radius:0.375rem;border:1px solid #fca5a5;background:#fee2e2;color:#b91c1c;
                               cursor:pointer;flex-shrink:0;transition:background 0.15s;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>`;
    }

    addEksporRow(cardIndex) {
        const container = document.getElementById(`ekspor-rows-${cardIndex}`);
        if (!container) return;
        const hint = container.querySelector('.ekspor-empty-hint');
        if (hint) hint.remove();
        const rowCount = container.querySelectorAll('.ekspor-row').length;
        container.insertAdjacentHTML('beforeend', this.generateEksporRowHTML(cardIndex, rowCount, {}));
    }

    deleteEksporRow(cardIndex, rowIndexStr) {
        const container = document.getElementById(`ekspor-rows-${cardIndex}`);
        if (!container) return;
        const rows = container.querySelectorAll('.ekspor-row');
        const rowIndex = parseInt(rowIndexStr);
        if (rows[rowIndex]) {
            rows[rowIndex].remove();
            container.querySelectorAll('.ekspor-row').forEach((row, ni) => {
                row.dataset.rowIndex = ni;
                row.querySelectorAll('input').forEach(inp => {
                    inp.name = inp.name.replace(
                        /blok3a_products\[(\d+)\]\[rincian_ekspor\]\[\d+\]/,
                        (_, ci) => `blok3a_products[${ci}][rincian_ekspor][${ni}]`
                    );
                });
                const delBtn = row.querySelector('.btn-delete-ekspor-row');
                if (delBtn) delBtn.dataset.rowIndex = ni;
            });
            if (container.querySelectorAll('.ekspor-row').length === 0) {
                container.insertAdjacentHTML('beforeend', this.generateEksporRowHTML(cardIndex, 0, {}));
            }
        }
    }

    formatNumericOnBlur(e) {
        const inp = e.target;
        if (!inp || !inp.name || !inp.name.startsWith('blok3a_products')) return;
        const isNumeric = (inp.name.includes('[banyaknya]') || inp.name.includes('[nilai]') || inp.name.includes('[harga_satuan]') || (inp.name.includes('[rincian_ekspor]') && (inp.name.includes('[jumlah]') || inp.name.includes('[nilai]'))));
        if (!isNumeric) return;
        // Attach numeric restriction once per input
        if (!inp.hasAttribute('data-numeric-restricted')) {
            inp.setAttribute('data-numeric-restricted', '1');
            inp.addEventListener('input', () => {
                const val = inp.value;
                const cleaned = val.replace(/[^0-9,.]/g, '');
                if (val !== cleaned) inp.value = cleaned;
            });
        }
        const raw = inp.value.replace(/[^0-9]/g, '');
        if (raw === '') {
            inp.value = '';
        } else {
            const num = parseInt(raw, 10);
            if (!isNaN(num) && num > 0) {
                inp.value = num.toLocaleString('id-ID');
            } else {
                inp.value = raw;
            }
        }
        // Re-save the formatted value so the persisted value matches display
        if (inp.name && inp.name.includes('[rincian_ekspor]')) {
            this._scheduleAutoSave();
        } else if (window.surveyManager && inp.name) {
            window.surveyManager.scheduleAutoSave(inp.name, inp.value);
        }
    }
}

// Initializer
document.addEventListener('DOMContentLoaded', () => {
    window.blok3aManager = new SurveyBlok3aManager();

    // Override renderStaticSectionsV2 call inside init by updating prototype before use? 
    // No, I'll just write `init` correctly in class definition above.
});
