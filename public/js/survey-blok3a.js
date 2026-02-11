/**
 * SIBSTR Survey Blok IIIA JavaScript Module
 * Handles dynamic product table, auto-calculations, and form interactions
 */

class SurveyBlok3aManager {
    constructor() {
        this.form = document.getElementById('survey-form');
        this.productsTable = document.getElementById('products-table');
        this.productsTbody = document.getElementById('products-tbody');
        this.addProductBtn = document.getElementById('add-product-btn');

        this.productCount = 0;
        this.months = ['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'];
        // Quarter management to reduce horizontal scrolling
        this.activeQuarter = 'dec2024';
        this.quarterMap = {
            '2024_des': 'dec2024',
            '2025_jan': 'q1', '2025_feb': 'q1', '2025_mar': 'q1',
            '2025_apr': 'q2', '2025_mei': 'q2', '2025_jun': 'q2',
            '2025_jul': 'q3', '2025_agu': 'q3', '2025_sep': 'q3',
            '2025_okt': 'q4', '2025_nov': 'q4', '2025_des': 'q4'
        };
        this.columnNumberMap = {
            '2024_des': 4,
            '2025_jan': 5,
            '2025_feb': 6,
            '2025_mar': 7,
            '2025_apr': 8,
            '2025_mei': 9,
            '2025_jun': 10,
            '2025_jul': 11,
            '2025_agu': 12,
            '2025_sep': 13,
            '2025_okt': 14,
            '2025_nov': 15,
            '2025_des': 16
        };
        this.formValues = {};

        // Column resize state
        this.columnWidths = {};          // { month_key: widthPx }
        this.defaultColumnWidth = 150;   // default month column width in px
        this.minColumnWidth = 60;        // minimum allowed width
        this.maxColumnWidth = 400;       // maximum allowed width
        this._resizeState = null;        // active resize drag state
        this._resizeTooltip = null;      // tooltip element during drag
        this._loadSavedColumnWidths();   // restore from localStorage

        this.init();
    }

    init() {
        if (!this.form) {
            console.error('Survey form not found');
            return;
        }

        this.setupEventListeners();
        // Tabs toggle for quarter/month visibility/rendering
        this.setupTabs();
        this.loadExistingData();
        // Initial rendering based on default quarter
        this.applyQuarterVisibility();
        this.setupValidation();
        this.setupAutoCalculation();

        console.log('Blok IIIA Manager initialized');
    }

    setupEventListeners() {
        // Add product button
        if (this.addProductBtn) {
            this.addProductBtn.addEventListener('click', () => {
                this.addProductRow();
            });
        }

        // Navigation buttons
        const backBtn = document.getElementById('back-to-blok2');
        const saveDraftBtn = document.getElementById('save-draft');
        const saveCompleteBtn = document.getElementById('save-complete');

        if (backBtn) {
            backBtn.addEventListener('click', () => {
                if (window.surveyRoutes?.backToBlok2) {
                    window.location.href = window.surveyRoutes.backToBlok2;
                }
            });
        }

        if (saveDraftBtn) {
            saveDraftBtn.addEventListener('click', () => {
                this.saveDraft();
            });
        }

        if (saveCompleteBtn) {
            saveCompleteBtn.addEventListener('click', () => {
                this.saveAndContinue();
            });
        }

        // Auto-save on input changes
        this.form.addEventListener('input', (e) => {
            if (e.target.matches('input, textarea, select')) {
                this.handleFieldChange(e.target);
            }
        });


    }

    // Quarter/Month tabs setup
    setupTabs() {
        const tabsContainer = document.getElementById('months-tabs');
        if (!tabsContainer) return;

        const tabs = tabsContainer.querySelectorAll('.month-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                this.activeQuarter = tab.dataset.quarter || 'q1';
                this.applyQuarterVisibility();
            });
        });
    }

    // Compute visible months based on active quarter, always include Dec 2024
    getVisibleMonths() {
        const base = ['2024_des'];
        switch (this.activeQuarter) {
            case 'dec2024':
                return base;
            case 'q1':
                return base.concat(['2025_jan', '2025_feb', '2025_mar']);
            case 'q2':
                return base.concat(['2025_apr', '2025_mei', '2025_jun']);
            case 'q3':
                return base.concat(['2025_jul', '2025_agu', '2025_sep']);
            case 'q4':
                return base.concat(['2025_okt', '2025_nov', '2025_des']);
            default:
                return base;
        }
    }

    getMonthLabel(month) {
        const map = {
            '2024_des': 'Desember',
            '2025_jan': 'Januari',
            '2025_feb': 'Februari',
            '2025_mar': 'Maret',
            '2025_apr': 'April',
            '2025_mei': 'Mei',
            '2025_jun': 'Juni',
            '2025_jul': 'Juli',
            '2025_agu': 'Agustus',
            '2025_sep': 'September',
            '2025_okt': 'Oktober',
            '2025_nov': 'November',
            '2025_des': 'Desember'
        };
        return map[month] || month;
    }

    getQuarterLabel(quarter) {
        const map = {
            dec2024: 'Des 2024',
            q1: 'Triwulan I',
            q2: 'Triwulan II',
            q3: 'Triwulan III',
            q4: 'Triwulan IV'
        };
        return map[quarter] || quarter;
    }

    captureFormValues() {
        this.formValues = {};
        const inputs = this.form.querySelectorAll('input');
        inputs.forEach(input => {
            this.formValues[input.name] = input.value;
        });
    }

    getCurrentValue(name) {
        if (this.formValues && Object.prototype.hasOwnProperty.call(this.formValues, name)) {
            return this.formValues[name];
        }
        return undefined;
    }

    // Render header rows to only include visible months
    renderHeader() {
        if (!this.productsTable) return;
        const thead = this.productsTable.querySelector('thead');
        const row1 = thead?.querySelector('.header-row-1');
        const row2 = thead?.querySelector('.header-row-2');
        const monthsRow = thead?.querySelector('.header-row-3');
        const colNumRow = thead?.querySelector('.column-numbers-row');
        const visibleMonths = this.getVisibleMonths();
        const quarterMonths = visibleMonths.filter(m => m !== '2024_des');

        // ── Build / rebuild <colgroup> so table-layout:fixed respects our widths ──
        this._buildColgroup(visibleMonths);

        // Adjust Year cells in row 1
        if (row1) {
            const year2025Cell = row1.querySelector('.year-2025');
            if (quarterMonths.length > 0) {
                if (year2025Cell) {
                    year2025Cell.style.display = '';
                    year2025Cell.colSpan = quarterMonths.length;
                } else {
                    const th2025 = document.createElement('th');
                    th2025.className = 'col-2025 year-col year-2025';
                    th2025.setAttribute('data-quarter', 'q1 q2 q3 q4');
                    th2025.textContent = '2025';
                    th2025.colSpan = quarterMonths.length;
                    row1.appendChild(th2025);
                }
            } else if (year2025Cell) {
                year2025Cell.remove();
            }
        }

        // Quarter header row (row 2): show only active quarter if any
        if (row2) {
            // Clear existing quarter headers
            row2.innerHTML = '';
            if (quarterMonths.length > 0) {
                const qCell = document.createElement('th');
                qCell.className = `quarter-header quarter-${this.activeQuarter}`;
                qCell.setAttribute('data-quarter', this.activeQuarter);
                qCell.textContent = this.getQuarterLabel(this.activeQuarter);
                qCell.colSpan = quarterMonths.length;
                row2.appendChild(qCell);
            }
        }

        // Months header (row 3)
        if (monthsRow) {
            // Remove all existing month header cells (exclude sticky columns)
            Array.from(monthsRow.children).forEach(child => {
                // If it's a generated month header or doesn't have a sticky class
                if (child.classList.contains('month-col') ||
                    (!child.classList.contains('col-no') &&
                        !child.classList.contains('col-jenis') &&
                        !child.classList.contains('col-uraian'))) {
                    child.remove();
                }
            });

            // Append visible months headers
            visibleMonths.forEach(month => {
                const th = document.createElement('th');
                th.className = `month-header month-col month-${month}`;
                th.setAttribute('data-month', month);
                th.setAttribute('data-quarter', this.quarterMap[month]);
                th.textContent = this.getMonthLabel(month);
                monthsRow.appendChild(th);
            });
        }

        // Column numbers row
        if (colNumRow) {
            colNumRow.querySelectorAll('.month-col').forEach(el => el.remove());
            visibleMonths.forEach(month => {
                const td = document.createElement('td');
                td.className = `col-number month-col month-${month}`;
                td.setAttribute('data-month', month);
                td.setAttribute('data-quarter', this.quarterMap[month]);
                td.textContent = `(${this.columnNumberMap[month]})`;
                colNumRow.appendChild(td);
            });
        }
    }

    /**
     * Build / rebuild a <colgroup> with one <col> per visible column.
     * With table-layout:fixed the <col> widths are authoritative.
     */
    _buildColgroup(visibleMonths) {
        // Remove old colgroup if it exists
        const old = this.productsTable.querySelector('colgroup');
        if (old) old.remove();

        const cg = document.createElement('colgroup');

        // 3 fixed sticky columns (No., Jenis, Uraian)
        const fixedCols = [
            { cls: 'col-no', width: 'var(--col-no-width)' },
            { cls: 'col-jenis', width: 'var(--col-jenis-width)' },
            { cls: 'col-uraian', width: 'var(--col-uraian-width)' },
        ];
        fixedCols.forEach(fc => {
            const col = document.createElement('col');
            col.className = fc.cls;
            col.style.width = fc.width;
            cg.appendChild(col);
        });

        // One <col> per visible month
        visibleMonths.forEach(month => {
            const col = document.createElement('col');
            col.className = `col-month col-month-${month}`;
            col.setAttribute('data-month', month);
            // Use saved width or default
            const savedW = this.columnWidths[month];
            col.style.width = savedW ? (savedW + 'px') : (this.defaultColumnWidth + 'px');
            cg.appendChild(col);
        });

        // Insert colgroup before thead
        this.productsTable.insertBefore(cg, this.productsTable.firstChild);
    }

    // Re-render month inputs for existing product rows based on visible months
    rerenderProductRows() {
        const visibleMonths = this.getVisibleMonths();
        const mainRows = this.productsTbody.querySelectorAll('tr.product-row');
        mainRows.forEach(mainRow => {
            const productIndex = parseInt(mainRow.dataset.productIndex || '-1', 10);
            const nilaiRow = mainRow.nextElementSibling;
            const hargaRow = nilaiRow?.nextElementSibling;

            // Clean existing month cells
            mainRow.querySelectorAll('td.month-col').forEach(el => el.remove());
            nilaiRow?.querySelectorAll('td.month-col').forEach(el => el.remove());
            hargaRow?.querySelectorAll('td.month-col').forEach(el => el.remove());

            // Append month cells for Banyaknya
            visibleMonths.forEach(month => {
                const td = document.createElement('td');
                td.className = `month-col month-${month}`;
                td.setAttribute('data-month', month);
                td.setAttribute('data-quarter', this.quarterMap[month]);
                const input = document.createElement('input');
                input.type = 'number';
                input.name = `blok3a_products[${productIndex}][banyaknya][${month}]`;
                const saved = this.getCurrentValue(input.name);
                const initialBanyaknya = saved ?? (window.surveyData?.products?.[productIndex]?.banyaknya?.[month] ?? '');
                input.value = initialBanyaknya;
                input.className = 'form-control form-control-sm month-input';
                input.setAttribute('data-month', month);
                input.setAttribute('data-quarter', this.quarterMap[month]);
                input.step = '0.01';
                input.min = '0';
                input.placeholder = '';
                td.appendChild(input);
                mainRow.appendChild(td);
            });

            // Append Nilai cells
            if (nilaiRow) {
                visibleMonths.forEach(month => {
                    const td = document.createElement('td');
                    td.className = `month-col month-${month}`;
                    td.setAttribute('data-month', month);
                    td.setAttribute('data-quarter', this.quarterMap[month]);
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.name = `blok3a_products[${productIndex}][nilai][${month}]`;
                    const saved = this.getCurrentValue(input.name);
                    const initialNilai = saved ?? (window.surveyData?.products?.[productIndex]?.nilai?.[month] ?? '');
                    input.value = initialNilai;
                    input.className = 'form-control form-control-sm nilai-input month-input';
                    input.setAttribute('data-product-index', productIndex);
                    input.setAttribute('data-month', month);
                    input.setAttribute('data-quarter', this.quarterMap[month]);
                    input.step = '0.01';
                    input.min = '0';
                    input.placeholder = '';
                    td.appendChild(input);
                    nilaiRow.appendChild(td);
                });
            }

            // Append Harga/Satuan cells
            if (hargaRow) {
                visibleMonths.forEach(month => {
                    const td = document.createElement('td');
                    td.className = `month-col month-${month}`;
                    td.setAttribute('data-month', month);
                    td.setAttribute('data-quarter', this.quarterMap[month]);
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.name = `blok3a_products[${productIndex}][harga_satuan][${month}]`;
                    const saved = this.getCurrentValue(input.name);
                    const initialHarga = saved ?? (window.surveyData?.products?.[productIndex]?.harga_satuan?.[month] ?? '');
                    input.value = initialHarga;
                    input.className = 'form-control form-control-sm month-input';
                    input.setAttribute('data-month', month);
                    input.setAttribute('data-quarter', this.quarterMap[month]);
                    input.step = '0.01';
                    input.min = '0';
                    input.placeholder = '';
                    td.appendChild(input);
                    hargaRow.appendChild(td);
                });
            }

            // Rebind auto-save and numeric validations for new inputs
            this.setupRowAutoSave(mainRow);
            if (nilaiRow) this.setupRowAutoSave(nilaiRow);
            if (hargaRow) this.setupRowAutoSave(hargaRow);
            this.addNumericValidationToInputs();
        });
    }

    // Render tfoot rows for Lainnya and Total using visible months
    renderTfoot() {
        const visibleMonths = this.getVisibleMonths();
        const tfoot = this.productsTable.querySelector('tfoot');
        if (!tfoot) return;
        const lainnyaRow = tfoot.querySelector('.lainnya-row');
        const totalRow = tfoot.querySelector('.total-row');

        if (lainnyaRow) {
            // Remove existing month cells
            lainnyaRow.querySelectorAll('.month-col').forEach(el => el.remove());
            visibleMonths.forEach(month => {
                const td = document.createElement('td');
                td.className = `month-col month-${month}`;
                td.setAttribute('data-month', month);
                td.setAttribute('data-quarter', this.quarterMap[month]);
                const input = document.createElement('input');
                input.type = 'number';
                input.name = `blok3a_lainnya[nilai][${month}]`;
                const saved = this.getCurrentValue(input.name);
                const initial = saved ?? (window.surveyData?.lainnya?.nilai?.[month] ?? '');
                input.value = initial;
                input.className = 'form-control form-control-sm nilai-input lainnya-nilai month-input';
                input.setAttribute('data-month', month);
                input.setAttribute('data-quarter', this.quarterMap[month]);
                input.step = '0.01';
                input.min = '0';
                input.placeholder = '';
                td.appendChild(input);
                lainnyaRow.appendChild(td);
            });
        }

        if (totalRow) {
            totalRow.querySelectorAll('.month-col').forEach(el => el.remove());
            visibleMonths.forEach(month => {
                const td = document.createElement('td');
                td.className = `month-col month-${month}`;
                td.setAttribute('data-month', month);
                td.setAttribute('data-quarter', this.quarterMap[month]);
                const input = document.createElement('input');
                input.type = 'number';
                input.name = `blok3a_totals[${month}]`;
                const saved = this.getCurrentValue(input.name);
                const initial = saved ?? (window.surveyData?.totals?.[month] ?? 0);
                input.value = initial;
                input.className = 'form-control form-control-sm total-input month-input';
                input.setAttribute('data-month', month);
                input.setAttribute('data-quarter', this.quarterMap[month]);
                input.readOnly = true;
                input.tabIndex = -1;
                td.appendChild(input);
                totalRow.appendChild(td);
            });
        }
    }

    // Apply quarter rendering: rebuild header, body rows, and tfoot for visible months
    applyQuarterVisibility() {
        // Capture current form values to preserve across re-renders
        this.captureFormValues();
        // Render header and tfoot based on active quarter
        this.renderHeader();
        this.rerenderProductRows();
        this.renderTfoot();
        // Recalculate totals for visible months
        this.calculateTotals();
        // Re-apply saved column widths and attach resize handles
        this._applyColumnWidths();
        this._setupResizeHandles();
    }

    loadExistingData() {
        // Clear existing rows to prevent duplication with server-rendered rows
        if (this.productsTbody) {
            this.productsTbody.innerHTML = '';
        }
        this.productCount = 0;

        const existingProducts = window.surveyData?.products || [];

        if (existingProducts && existingProducts.length > 0) {
            // Load existing products
            existingProducts.forEach((product, index) => {
                this.addProductRow(product, index);
            });
        } else {
            // Add 2 default empty product rows when no existing data
            this.addProductRow(null);
            this.addProductRow(null);
        }

        // Update visual row numbers
        this.updateRowNumbers();

        // Calculate totals after loading data
        setTimeout(() => {
            this.calculateTotals();
        }, 100);
    }

    addProductRow(productData = null, index = null) {
        // Use provided index or generate new unique index
        // If index is null (new row), use productCount as unique ID
        const productIndex = index !== null ? index : this.productCount;

        // Ensure productCount tracks the highest index to avoid collisions
        this.productCount = Math.max(this.productCount, productIndex + 1);

        const product = productData || {
            jenis_barang: '',
            uraian: '',
            satuan: '',
            banyaknya: {},
            nilai: {},
            harga_satuan: {}
        };

        // Create product row group (3 sub-rows)
        const productRowGroup = this.createProductRowGroup(productIndex + 1, product, productIndex);

        // Append rows to the dedicated tbody to ensure proper DOM structure and serialization
        this.productsTbody.appendChild(productRowGroup);

        // Add animation class to the main product row
        const lastMainRow = this.productsTbody.querySelector('tr.product-row:last-of-type');
        if (lastMainRow) {
            lastMainRow.classList.add('new-row');
            setTimeout(() => {
                lastMainRow.classList.remove('new-row');
            }, 300);
        }

        // Setup auto-save for new inputs
        this.setupRowAutoSave(productRowGroup);
        // Apply current quarter rendering for newly added rows
        this.applyQuarterVisibility();

        // Update row numbers (1., 2., etc) and delete buttons - visual only
        this.updateRowNumbers();

        // Auto-add new row if this is the last row and has data
        this.checkAutoAddRow();
    }

    createProductRowGroup(rowNumber, product, productIndex) {
        const fragment = document.createDocumentFragment();

        // Main product info row (Banyaknya)
        const mainRow = document.createElement('tr');
        mainRow.className = 'product-row';
        mainRow.dataset.productIndex = productIndex;

        const visibleMonths = this.getVisibleMonths();

        mainRow.innerHTML = `
            <td rowspan="3" class="row-number sticky-col col-no">
                ${rowNumber}.
                <!-- Delete button will be managed by updateRowNumbers -->
            </td>
            <td rowspan="3" class="product-info sticky-col col-jenis">
                <input type="text" name="blok3a_products[${productIndex}][jenis_barang]"
                       value="${product.jenis_barang || ''}"
                       class="form-control form-control-sm jenis-barang-input"
                       placeholder="Jenis barang yang dihasilkan">
            </td>
            <td class="sub-row-label sticky-col col-uraian">Banyaknya</td>
            ${visibleMonths.map(month => `
                <td class="month-col month-${month}" data-month="${month}" data-quarter="${this.quarterMap[month]}">
                    <input type="number"
                           name="blok3a_products[${productIndex}][banyaknya][${month}]"
                           value="${product.banyaknya?.[month] || ''}"
                           class="form-control form-control-sm month-input"
                           data-month="${month}" data-quarter="${this.quarterMap[month]}"
                           step="0.01"
                           min="0"
                           placeholder="">
                </td>
            `).join('')}
        `;

        // Nilai (value) row
        const nilaiRow = document.createElement('tr');
        nilaiRow.className = 'sub-row nilai-row';
        nilaiRow.dataset.parentIndex = productIndex; // Link to parent
        nilaiRow.innerHTML = `
            <td class="sub-row-label sticky-col col-uraian">Nilai</td>
            ${visibleMonths.map(month => `
                <td class="month-col month-${month}" data-month="${month}" data-quarter="${this.quarterMap[month]}">
                    <input type="number"
                           name="blok3a_products[${productIndex}][nilai][${month}]"
                           value="${product.nilai?.[month] || ''}"
                           class="form-control form-control-sm nilai-input month-input"
                           data-product-index="${productIndex}"
                           data-month="${month}"
                           data-quarter="${this.quarterMap[month]}"
                           step="0.01"
                           min="0"
                           placeholder="">
                </td>
            `).join('')}
        `;

        // Harga/Satuan row
        const hargaRow = document.createElement('tr');
        hargaRow.className = 'sub-row harga-row';
        hargaRow.dataset.parentIndex = productIndex; // Link to parent
        hargaRow.innerHTML = `
            <td class="sub-row-label sticky-col col-uraian">Harga/Satuan</td>
            ${visibleMonths.map(month => `
                <td class="month-col month-${month}" data-month="${month}" data-quarter="${this.quarterMap[month]}">
                    <input type="number"
                           name="blok3a_products[${productIndex}][harga_satuan][${month}]"
                           value="${product.harga_satuan?.[month] || ''}"
                           class="form-control form-control-sm month-input"
                           data-month="${month}" data-quarter="${this.quarterMap[month]}"
                           step="0.01"
                           min="0"
                           placeholder="">
                </td>
            `).join('')}
        `;

        fragment.appendChild(mainRow);
        fragment.appendChild(nilaiRow);
        fragment.appendChild(hargaRow);

        return fragment;
    }

    updateRowNumbers() {
        const mainRows = this.productsTbody.querySelectorAll('tr.product-row');
        mainRows.forEach((row, index) => {
            const numberCell = row.querySelector('.row-number');
            if (numberCell) {
                // Update text content for number (1., 2., etc)
                // Use a span for the number to safely manipulate text without affecting children (like buttons)
                // But simpler: just clear and rebuild.

                // Keep the productIndex from the row dataset
                const productIndex = row.dataset.productIndex;

                // Clear content
                numberCell.innerHTML = '';

                // Add number
                const numSpan = document.createElement('span');
                numSpan.textContent = `${index + 1}.`;
                numberCell.appendChild(numSpan);

                // Add delete button if it's not the first row (or always if requirement changed)
                // Requirement: "ensure row numbers are sequential"
                // Let's assume user wants to be able to delete any row except maybe the very first one if list is empty?
                // Or just > 0.
                if (index > 0) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'remove-product-btn';
                    btn.dataset.productIndex = productIndex;
                    btn.title = 'Hapus produk';
                    btn.innerHTML = '×';
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation(); // Prevent bubbling
                        this.removeProductRow(productIndex);
                    });
                    numberCell.appendChild(btn);
                }
            }
        });
    }

    removeProductRow(productIndexInput) {
        const productIndex = productIndexInput.toString();

        // Find main row with specific index
        const mainRow = this.productsTbody.querySelector(`tr.product-row[data-product-index="${productIndex}"]`);

        if (!mainRow) {
            console.warn(`Product row with index ${productIndex} not found`);
            return;
        }

        // Find associated sub-rows
        const rowsToRemove = [mainRow];
        let nextSibling = mainRow.nextElementSibling;

        // Collect associated rows (nilai-row and harga-row) which are siblings
        while (nextSibling && !nextSibling.classList.contains('product-row')) {
            rowsToRemove.push(nextSibling);
            nextSibling = nextSibling.nextElementSibling;
        }

        if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
            rowsToRemove.forEach(row => row.remove());

            // Re-index visual numbers
            this.updateRowNumbers();

            this.calculateTotals();

            // Auto-save the removal
            if (window.surveyManager) {
                window.surveyManager.scheduleAutoSave(`blok3a_products[${productIndex}]`, null, true);
            }
        }
    }

    setupRowAutoSave(rowGroup) {
        const inputs = rowGroup.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                this.handleFieldChange(input);
            });
        });
    }

    handleFieldChange(field) {
        const fieldName = field.name;
        const fieldValue = field.value;

        // Schedule auto-save with enhanced feedback
        if (window.surveyManager) {
            // Remove existing validation classes before saving
            field.classList.remove('field-valid', 'field-invalid');

            // Schedule the auto-save
            window.surveyManager.scheduleAutoSave(fieldName, fieldValue);

            // Add visual feedback after a short delay to ensure save completes
            setTimeout(() => {
                if (fieldValue.trim() !== '') {
                    field.classList.add('field-valid');
                }
            }, 1000);
        }

        // Clear any existing validation errors
        this.clearFieldError(field);

        // Trigger total calculation if this is a nilai or lainnya field
        if (fieldName.includes('[nilai]') || fieldName.includes('lainnya')) {
            setTimeout(() => {
                this.calculateTotals();
            }, 100);
        }
    }

    setupAutoCalculation() {
        // Set up auto-calculation for nilai inputs
        this.form.addEventListener('input', (e) => {
            if (e.target.classList.contains('nilai-input') || e.target.classList.contains('lainnya-nilai')) {
                // Debounce the calculation to avoid excessive calls
                clearTimeout(this.calculationTimeout);
                this.calculationTimeout = setTimeout(() => {
                    this.calculateTotals();
                }, 300);
            }
        });

        // Set up auto-calculation for when new rows are added
        this.form.addEventListener('change', (e) => {
            if (e.target.classList.contains('nilai-input') || e.target.classList.contains('lainnya-nilai')) {
                this.calculateTotals();
            }
        });
    }

    calculateTotals() {
        const visibleMonths = this.getVisibleMonths();
        console.log('calculateTotals called for months:', visibleMonths);

        visibleMonths.forEach(month => {
            let total = 0;

            // Debug: Check if form exists
            if (!this.form) {
                console.error('Form not found!');
                return;
            }

            // Sum all nilai inputs for this month from products - try multiple selectors
            let productNilaiInputs = this.form.querySelectorAll(`input[name^="blok3a_products"][name*="[nilai][${month}]"]`);

            // If first selector doesn't work, try alternative selectors
            if (productNilaiInputs.length === 0) {
                productNilaiInputs = this.form.querySelectorAll(`input[name*="blok3a_products"][name*="nilai"][name*="${month}"]`);
            }

            if (productNilaiInputs.length === 0) {
                productNilaiInputs = this.form.querySelectorAll(`input.nilai-input[data-month="${month}"]`);
            }

            console.log(`Month ${month}: Found ${productNilaiInputs.length} product nilai inputs`);

            productNilaiInputs.forEach((input, index) => {
                const value = parseFloat(input.value) || 0;
                console.log(`Product input ${index}: name=${input.name}, value=${input.value} -> parsed: ${value}`);
                if (!isNaN(value) && value > 0) {
                    total += value;
                }
            });

            // Add lainnya nilai for this month - try multiple selectors
            let lainnyaNilaiInput = this.form.querySelector(`input[name="blok3a_lainnya[nilai][${month}]"]`);

            if (!lainnyaNilaiInput) {
                lainnyaNilaiInput = this.form.querySelector(`input[name*="blok3a_lainnya"][name*="nilai"][name*="${month}"]`);
            }

            if (!lainnyaNilaiInput) {
                lainnyaNilaiInput = this.form.querySelector(`input.lainnya-nilai[data-month="${month}"]`);
            }

            if (lainnyaNilaiInput) {
                const lainnyaValue = parseFloat(lainnyaNilaiInput.value) || 0;
                console.log(`Lainnya input: name=${lainnyaNilaiInput.name}, value=${lainnyaNilaiInput.value} -> parsed: ${lainnyaValue}`);
                if (!isNaN(lainnyaValue) && lainnyaValue > 0) {
                    total += lainnyaValue;
                }
            } else {
                console.log(`No lainnya input found for month ${month}`);
            }

            console.log(`Month ${month}: Total calculated = ${total}`);

            // Update total input - try multiple selectors
            let totalInput = this.form.querySelector(`input[name="blok3a_totals[${month}]"]`);

            if (!totalInput) {
                totalInput = this.form.querySelector(`input[name*="blok3a_totals"][name*="${month}"]`);
            }

            if (!totalInput) {
                totalInput = this.form.querySelector(`input.total-input[data-month="${month}"]`);
            }

            if (totalInput) {
                const formattedTotal = total.toFixed(2);
                totalInput.value = formattedTotal;
                console.log(`Month ${month}: Set total input to ${formattedTotal}`);

                // Auto-save the calculated total
                if (window.surveyManager) {
                    window.surveyManager.scheduleAutoSave(`blok3a_totals[${month}]`, formattedTotal, true);
                }
            } else {
                console.error(`No total input found for month ${month}`);
            }
        });
    }

    checkAutoAddRow() {
        // Check if we need to add a new row
        const lastProductRows = this.productsTbody.querySelectorAll('tr.product-row');
        if (lastProductRows.length === 0) {
            this.addProductRow();
            return;
        }

        const lastRow = lastProductRows[lastProductRows.length - 1];
        const lastRowInputs = lastRow.querySelectorAll('input[type="text"]');

        let hasData = false;
        lastRowInputs.forEach(input => {
            if (input.value.trim() !== '') {
                hasData = true;
            }
        });

        if (hasData) {
            this.addProductRow();
        }
    }

    setupValidation() {
        // Basic validation setup
        const requiredFields = this.form.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });
        });

        // Add numeric-only validation for all number inputs
        this.setupNumericValidation();
    }

    setupNumericValidation() {
        // Add event listeners to all current numeric inputs
        this.addNumericValidationToInputs();

        // Use MutationObserver to handle dynamically added inputs
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        const numericInputs = node.querySelectorAll('input[type="number"]');
                        numericInputs.forEach(input => {
                            this.addNumericValidationToInput(input);
                        });
                    }
                });
            });
        });

        observer.observe(this.productsTbody, {
            childList: true,
            subtree: true
        });
    }

    addNumericValidationToInputs() {
        const numericInputs = this.form.querySelectorAll('input[type="number"]');
        numericInputs.forEach(input => {
            this.addNumericValidationToInput(input);
        });
    }

    addNumericValidationToInput(input) {
        // Prevent non-numeric characters from being typed
        input.addEventListener('keypress', (e) => {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true)) {
                return;
            }

            // Allow: decimal point (only one)
            if (e.key === '.' && input.value.indexOf('.') === -1) {
                return;
            }

            // Allow: numbers 0-9
            if (e.key >= '0' && e.key <= '9') {
                return;
            }

            // Prevent all other characters
            e.preventDefault();
        });

        // Handle paste events to filter out non-numeric content
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const numericValue = paste.replace(/[^0-9.]/g, '');

            // Ensure only one decimal point
            const parts = numericValue.split('.');
            if (parts.length > 2) {
                const cleanValue = parts[0] + '.' + parts.slice(1).join('');
                input.value = cleanValue;
            } else {
                input.value = numericValue;
            }

            // Trigger change event for auto-save
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });

        // Validate on input to remove any non-numeric characters that might slip through
        input.addEventListener('input', (e) => {
            let value = e.target.value;

            // Remove any non-numeric characters except decimal point
            const cleanValue = value.replace(/[^0-9.]/g, '');

            // Ensure only one decimal point
            const parts = cleanValue.split('.');
            if (parts.length > 2) {
                const finalValue = parts[0] + '.' + parts.slice(1).join('');
                e.target.value = finalValue;
            } else if (cleanValue !== value) {
                e.target.value = cleanValue;
            }
        });

        // Validate on blur to ensure proper format
        input.addEventListener('blur', (e) => {
            let value = e.target.value.trim();

            if (value === '') return;

            // Parse and reformat to ensure valid number
            const numValue = parseFloat(value);
            if (!isNaN(numValue)) {
                // Format to 2 decimal places if it's a currency field
                if (input.classList.contains('nilai-input') || input.name.includes('nilai') || input.name.includes('totals')) {
                    e.target.value = numValue.toFixed(2);
                } else {
                    e.target.value = numValue.toString();
                }
            } else {
                e.target.value = '';
            }
        });
    }

    validateField(field) {
        if (field.required && !this.isFieldFilled(field)) {
            this.showFieldError(field, 'Field ini wajib diisi');
            return false;
        }

        this.clearFieldError(field);
        return true;
    }

    isFieldFilled(field) {
        if (field.type === 'radio' || field.type === 'checkbox') {
            const group = this.form.querySelectorAll(`input[name="${field.name}"]`);
            return Array.from(group).some(input => input.checked);
        }
        return field.value.trim() !== '';
    }

    showFieldError(field, message) {
        this.clearFieldError(field);

        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;

        field.parentNode.appendChild(errorDiv);
        field.classList.add('error');
    }

    clearFieldError(field) {
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }
        field.classList.remove('error');
    }

    validateForm() {
        let isValid = true;
        const requiredFields = this.form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });

        return isValid;
    }

    async saveDraft() {
        try {
            if (!window.surveyManager) {
                throw new Error('Survey manager not available');
            }

            // Use global SurveyManager to save without validation (draft mode)
            await window.surveyManager.saveForm(false);
        } catch (error) {
            console.error('Save draft error:', error);
            window.surveyManager?.showStatus('Gagal menyimpan draft: ' + error.message, 'error');
        }
    }

    async saveAndContinue() {
        try {
            if (!this.validateForm()) {
                window.surveyManager?.showStatus('Mohon lengkapi semua field yang wajib diisi', 'error');
                return;
            }

            if (!window.surveyManager) {
                throw new Error('Survey manager not available');
            }

            // Use global SurveyManager to save and complete (with validation)
            await window.surveyManager.saveForm(true);
        } catch (error) {
            console.error('Save and continue error:', error);
            window.surveyManager?.showStatus('Gagal menyimpan data: ' + error.message, 'error');
        }
    }

    // =====================================================================
    //  Column Resize – Excel-like draggable column borders
    //  Uses <colgroup>/<col> elements which are authoritative under
    //  table-layout:fixed.
    // =====================================================================

    /** Load saved column widths from localStorage */
    _loadSavedColumnWidths() {
        try {
            const raw = localStorage.getItem('blok3a_col_widths');
            if (raw) {
                this.columnWidths = JSON.parse(raw);
            }
        } catch (_) {
            this.columnWidths = {};
        }
    }

    /** Persist column widths to localStorage */
    _saveColumnWidths() {
        try {
            localStorage.setItem('blok3a_col_widths', JSON.stringify(this.columnWidths));
        } catch (_) { /* ignore quota errors */ }
    }

    /**
     * Apply stored column widths via the <col> elements in the colgroup.
     * Must be called AFTER _buildColgroup / renderHeader.
     */
    _applyColumnWidths() {
        if (!this.productsTable) return;
        const visibleMonths = this.getVisibleMonths();

        visibleMonths.forEach(month => {
            const w = this.columnWidths[month];
            if (!w) return;
            const col = this.productsTable.querySelector(`col.col-month-${month}`);
            if (col) {
                col.style.width = w + 'px';
            }
        });
    }

    /**
     * Attach resize-handle elements to every visible month header cell.
     * Called after each quarter switch or row re-render.
     */
    _setupResizeHandles() {
        if (!this.productsTable) return;

        // Remove old handles
        this.productsTable.querySelectorAll('.col-resize-handle').forEach(h => h.remove());

        const monthHeaders = this.productsTable.querySelectorAll('.header-row-3 th.month-header');
        monthHeaders.forEach(th => {
            const month = th.getAttribute('data-month');
            if (!month) return;

            const handle = document.createElement('div');
            handle.className = 'col-resize-handle';
            handle.setAttribute('data-resize-month', month);
            handle.title = 'Seret untuk ubah lebar kolom · Klik ganda untuk reset';
            th.appendChild(handle);

            // --- Pointer-based drag (works for mouse + touch) ---
            handle.addEventListener('pointerdown', (e) => this._onResizePointerDown(e, month, th, handle));

            // --- Double-click to reset ---
            handle.addEventListener('dblclick', (e) => {
                e.preventDefault();
                e.stopPropagation();
                delete this.columnWidths[month];
                this._saveColumnWidths();
                // Reset <col> width to default
                const col = this.productsTable.querySelector(`col.col-month-${month}`);
                if (col) col.style.width = this.defaultColumnWidth + 'px';
            });
        });
    }

    /**
     * Find the <col> element for a given month.
     */
    _getColElement(month) {
        return this.productsTable.querySelector(`col.col-month-${month}`);
    }

    /** Pointer-down handler – start resize drag */
    _onResizePointerDown(e, month, th, handle) {
        e.preventDefault();
        e.stopPropagation();

        // Capture pointer so moves outside the handle still fire
        handle.setPointerCapture(e.pointerId);

        const col = this._getColElement(month);
        if (!col) return;

        const startX = e.clientX;
        // Read current computed width from the <col> or the header cell
        const startW = th.getBoundingClientRect().width;

        handle.classList.add('active');
        document.body.classList.add('col-resizing');

        // Create tooltip
        const tooltip = document.createElement('div');
        tooltip.className = 'col-resize-tooltip';
        tooltip.textContent = `${Math.round(startW)}px`;
        tooltip.style.left = e.clientX + 'px';
        tooltip.style.top = (e.clientY - 28) + 'px';
        document.body.appendChild(tooltip);

        const onMove = (moveEvt) => {
            const dx = moveEvt.clientX - startX;
            let newW = Math.round(startW + dx);
            newW = Math.max(this.minColumnWidth, Math.min(this.maxColumnWidth, newW));

            // Apply width via <col> — this is what table-layout:fixed respects
            col.style.width = newW + 'px';

            // Update tooltip
            tooltip.textContent = `${newW}px`;
            tooltip.style.left = moveEvt.clientX + 'px';
            tooltip.style.top = (moveEvt.clientY - 28) + 'px';
        };

        const onUp = (upEvt) => {
            handle.releasePointerCapture(upEvt.pointerId);
            handle.removeEventListener('pointermove', onMove);
            handle.removeEventListener('pointerup', onUp);
            handle.removeEventListener('pointercancel', onUp);

            handle.classList.remove('active');
            document.body.classList.remove('col-resizing');

            // Persist final width from the <col> or actual cell
            const finalW = th.getBoundingClientRect().width;
            this.columnWidths[month] = Math.round(finalW);
            this._saveColumnWidths();

            // Remove tooltip
            if (tooltip.parentNode) tooltip.parentNode.removeChild(tooltip);
        };

        handle.addEventListener('pointermove', onMove);
        handle.addEventListener('pointerup', onUp);
        handle.addEventListener('pointercancel', onUp);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('survey-form') && document.getElementById('products-table')) {
        window.surveyBlok3aManager = new SurveyBlok3aManager();
    }
});
