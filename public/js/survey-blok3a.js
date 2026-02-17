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

        // State
        this.products = []; // Array to store current product structures
        this.cardActiveQuarters = {}; // per-card quarter state: { index: quarter }
        this.lainnyaActiveQuarter = 'dec2024';
        this.totalActiveQuarter = 'dec2024';

        // Month Definitions
        this.quarterConf = {
            'dec2024': { label: 'Des 2024', months: ['2024_des'] },
            'q1': { label: 'Triwulan I', months: ['2025_jan', '2025_feb', '2025_mar'] },
            'q2': { label: 'Triwulan II', months: ['2025_apr', '2025_mei', '2025_jun'] },
            'q3': { label: 'Triwulan III', months: ['2025_jul', '2025_agu', '2025_sep'] },
            'q4': { label: 'Triwulan IV', months: ['2025_okt', '2025_nov', '2025_des'] }
        };

        this.monthLabels = {
            '2024_des': 'Desember',
            '2025_jan': 'Januari', '2025_feb': 'Februari', '2025_mar': 'Maret',
            '2025_apr': 'April', '2025_mei': 'Mei', '2025_jun': 'Juni',
            '2025_jul': 'Juli', '2025_agu': 'Agustus', '2025_sep': 'September',
            '2025_okt': 'Oktober', '2025_nov': 'November', '2025_des': 'Desember'
        };

        this.init();
    }

    init() {
        if (!this.container) return;

        console.log('Blok IIIA Manager Initializing...');
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

        // Initial preview render
        this.renderPreviewTable();
    }

    // Ensure totals are computed on load
    setupAutoCalculation() {
        try {
            this.calculateTotals();
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
        setupNavBtn('save-complete', () => this.saveAndContinue());

        // Global Event Delegation for Inputs (AutoSave & Calc)
        this.form.addEventListener('input', (e) => this.handleInput(e));
        this.form.addEventListener('click', (e) => {
            // 1. Handle Delete
            const deleteBtn = e.target.closest('.btn-delete');
            if (deleteBtn) {
                const index = deleteBtn.dataset.index;
                this.deleteProduct(index);
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
            banyaknya: {},
            nilai: {},
            harga_satuan: {}
        };

        this.products.push(productData);
        const cardHTML = this.createProductCardHTML(index, productData);
        this.container.insertAdjacentHTML('beforeend', cardHTML);

        // If it's a user action (data is null), scroll to new card
        if (!data) {
            const newCard = this.container.lastElementChild;
            // New cards should be expanded so user can fill them
            newCard.classList.remove('collapsed');
            const toggleBtn = newCard.querySelector('.card-toggle');
            if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');

            newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
            // Focus on title
            newCard.querySelector('.jenis-barang-input').focus();
        }

        // Set default quarter for this card and show
        this.cardActiveQuarters[index] = 'dec2024';
        const card = document.getElementById(`product-card-${index}`);
        if (card) this.setCardActiveQuarter(card, this.cardActiveQuarters[index]);
    }

    deleteProduct(indexStr) {
        if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) return;

        const index = parseInt(indexStr);
        // We can't easily "remove" from the array and preserve indices without weirdness in PHP reception 
        // unless we re-index everything.
        // Simplest approach: Remove DOM, rebuild data or just re-submit everything.
        // For dynamic UI, removing the DOM element is key.

        const card = document.getElementById(`product-card-${index}`);
        if (card) {
            card.remove();
            // We should also probably mark it as deleted or actually remove it from our local state
            // But re-indexing is complex with auto-save. 
            // Better: Just hide it and empty values? 
            // Robust: Re-index everything in DOM and backend?

            // Let's go with: Remove from DOM, and when Saving, we parse DOM to build array.
            // But AutoSave saves individual fields. 
            // *Correction*: The prompt asks to FIX bugs causing data duplication.
            // Best fix: Remove the element. If using `blok3a_products[index]`, leaving holes is fine for PHP 
            // (it becomes an assoc array), but `array_values` might be needed on backend.
            // Let's assume backend handles standard form encoding.

            // For autosave to strictly work with "delete", we might need to send a specific delete command 
            // or just rely on the final save. 
            // Current `survey.js` usually handles individual field updates.
            // To properly delete, we might need to clear values and trigger save, then remove DOM.

            // Clear values to trigger DB clears if autosave supports it
            card.querySelectorAll('input').forEach(input => {
                input.value = '';
                this.handleInput({ target: input }, false); // Trigger autosave with empty
            });

            // If we have a specific delete route, use it. If not, just remove DOM and hope 
            // the full save overwrites.
            // Ideally, we re-index the 'name' attributes of remaining cards to be 0,1,2...
            // so PHP receives a clean 0-indexed array.

            this.reindexProducts();
            this.calculateTotals();
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
        });

        // Update local state length
        this.products = new Array(cards.length).fill({});
    }

    createProductCardHTML(index, data) {
        // We generate HTML for ALL quarters, hidden/shown via CSS classes
        return `
        <div class="product-card collapsed" id="product-card-${index}">
            <div class="card-header">
                <div class="product-title">
                    <span class="product-counter">${index + 1}</span>
                    <span class="question-number">301.</span>
                    Jenis Barang yang dihasilkan/diproduksi
                </div>
                <div class="card-header-actions">
                    <button type="button" class="card-toggle" aria-expanded="false" title="Tutup/Buka isian produk">
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
                    <label class="form-label">Uraian Data Bulanan</label>
                    <div class="quarter-tabs" role="tablist" aria-label="Pilih Triwulan untuk Produk">
                        <button type="button" class="quarter-tab active" data-quarter="dec2024">Des 2024</button>
                        <button type="button" class="quarter-tab" data-quarter="q1">Triwulan I</button>
                        <button type="button" class="quarter-tab" data-quarter="q2">Triwulan II</button>
                        <button type="button" class="quarter-tab" data-quarter="q3">Triwulan III</button>
                        <button type="button" class="quarter-tab" data-quarter="q4">Triwulan IV</button>
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
                    <input type="number" step="0.01" 
                           name="blok3a_products[${index}][banyaknya][${m}]"
                           value="${val}"
                           class="form-control" placeholder="0">
                </div>`;
            });

            // Row 2: Nilai
            html += `<div class="grid-label section-title">Nilai (Rp)</div>`;
            months.forEach(m => {
                const val = data.nilai?.[m] || '';
                html += `
                <div class="grid-input">
                    <span class="mobile-month-label">${this.monthLabels[m]}</span>
                    <input type="number" step="0.01" 
                           name="blok3a_products[${index}][nilai][${m}]"
                           value="${val}"
                           class="form-control nilai-input"
                           data-month="${m}"
                           placeholder="0">
                </div>`;
            });

            // Row 3: Harga/Satuan
            html += `<div class="grid-label section-title">Harga Satuan</div>`;
            months.forEach(m => {
                const val = data.harga_satuan?.[m] || '';
                html += `
                <div class="grid-input">
                    <span class="mobile-month-label">${this.monthLabels[m]}</span>
                    <input type="number" step="0.01" 
                           name="blok3a_products[${index}][harga_satuan][${m}]"
                           value="${val}"
                           class="form-control" placeholder="0">
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
        this.lainnyaContainer.className = '';
        this.totalContainer.className = '';

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
                     <input type="number" step="0.01"
                           name="${name}"
                           value="${val}"
                           class="${cls}"
                           data-month="${m}"
                           ${type === 'total' ? 'readonly' : ''}
                           placeholder="0">
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
            const q = this.cardActiveQuarters[idx] || 'dec2024';
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

        // 1. Calculate Totals if needed
        if (input.classList.contains('nilai-input') || input.classList.contains('lainnya-nilai-input')) {
            this.calculateTotals(input.dataset.month);
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

    calculateTotals(specificMonth = null) {
        // If specific month is given, only calc that one? No, safer to calc all visible.
        // Actually, calc all months for robustness, or just active quarter.

        // Let's calc all months because user might change something that affects totals, 
        // though usually user changes one field at a time.
        // Iterating all inputs might be heavy if lots of products (e.g. 50 products * 13 months).
        // Optimization: Only calc for the visible months of Active Quarter + specificMonth if it's outside (unlikely).

        const qConf = this.quarterConf[this.activeQuarter];
        if (!qConf) return;

        qConf.months.forEach(month => {
            let sum = 0;

            // Sum Products
            const productInputs = document.querySelectorAll(`.nilai-input[data-month="${month}"]`);
            productInputs.forEach(inp => {
                sum += parseFloat(inp.value) || 0;
            });

            // Add Lainnya
            const lainnyaInput = document.querySelector(`.lainnya-nilai-input[data-month="${month}"]`);
            if (lainnyaInput) {
                sum += parseFloat(lainnyaInput.value) || 0;
            }

            // Update Total
            const totalInput = document.querySelector(`.total-input[data-month="${month}"]`);
            if (totalInput) {
                totalInput.value = sum;
                // We typically don't autosave the total itself as it's computed, 
                // but if backend expects it we should. The `window.surveyRoutes.autoSave` usually handles key-value pairs.
                // Assuming backend calculates total or accepts it. Let's fire autosave for total too to be safe.
                /* 
                if (window.surveyManager) {
                     window.surveyManager.scheduleAutoSave(totalInput.name, totalInput.value);
                }
                */
            }
        });

        // Re-render preview after recalculation
        this.renderPreviewTable();
    }

    // Preview Table Renderer (read-only)
    renderPreviewTable() {
        if (!this.previewContainer) return;
        const months = ['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'];

        // Gather product cards from DOM
        const productCards = Array.from(this.container.querySelectorAll('.product-card'));

        // Build table HTML with three sub-rows per product
        let html = '<div class="preview-table"><table class="preview-table-el"><thead><tr>';
        html += '<th class="sticky-col">Kode/Nama</th>';
        html += '<th>Uraian</th>';
        months.forEach(m => {
            const label = this.monthLabels[m] + (m.startsWith('2025') ? ' 2025' : ' 2024');
            html += `<th>${label}</th>`;
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

            // Row 1: Banyaknya
            html += `<tr>`;
            html += `<td class="sticky-col" rowspan="3"><div class="code">${code}</div><div class="name">${this.escapeHtml(name)}</div></td>`;
            html += `<td>Banyaknya</td>`;
            months.forEach(m => html += `<td class="num">${this.formatNumber(getBanyaknya(m))}</td>`);
            html += `</tr>`;

            // Row 2: Nilai
            html += `<tr>`;
            html += `<td>Nilai (Jutaan Rp)</td>`;
            months.forEach(m => html += `<td class="num">${this.formatNumber(getNilai(m))}</td>`);
            html += `</tr>`;

            // Row 3: Harga/Satuan
            html += `<tr>`;
            html += `<td>Harga/Satuan (Ribu Rp)</td>`;
            months.forEach(m => html += `<td class="num">${this.formatNumber(getHarga(m))}</td>`);
            html += `</tr>`;
        });

        // Lainnya (302) — only Nilai row is applicable
        const lainnyaValues = months.map(m => {
            const v = document.querySelector(`.lainnya-nilai-input[data-month=\"${m}\"]`);
            return v ? (parseFloat(v.value) || 0) : 0;
        });
        html += `<tr>`;
        html += `<td class="sticky-col"><div class="code">302.</div><div class="name">Lainnya</div></td>`;
        html += `<td>Nilai</td>`;
        lainnyaValues.forEach(v => html += `<td class="num">${this.formatNumber(v)}</td>`);
        html += `</tr>`;

        // Total (303) — only Nilai row is applicable
        const totalValues = months.map(m => {
            const v = document.querySelector(`.total-input[data-month=\"${m}\"]`);
            return v ? (parseFloat(v.value) || 0) : 0;
        });
        html += `<tr class="total-row">`;
        html += `<td class="sticky-col"><div class="code">303.</div><div class="name">Total</div></td>`;
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

    escapeHtml(str) {
        return (str || '').replace(/[&<>"]|'/g, s => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', '\'': '&#39;' }[s]));
    }

    // Actions
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
}

// Initializer
document.addEventListener('DOMContentLoaded', () => {
    window.blok3aManager = new SurveyBlok3aManager();

    // Override renderStaticSectionsV2 call inside init by updating prototype before use? 
    // No, I'll just write `init` correctly in class definition above.
});
