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
        
        this.init();
    }

    init() {
        if (!this.form) {
            console.error('Survey form not found');
            return;
        }

        this.setupEventListeners();
        this.loadExistingData();
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

    loadExistingData() {
        const existingProducts = window.surveyData?.products || [];
        
        if (existingProducts && existingProducts.length > 0) {
            // Load existing products
            existingProducts.forEach((product, index) => {
                this.addProductRow(product, index);
            });
        } else {
            // Add 2 default empty product rows when no existing data
            this.addProductRow(null, 0);
            this.addProductRow(null, 1);
        }

        // Calculate totals after loading data
        setTimeout(() => {
            this.calculateTotals();
        }, 100);
    }

    addProductRow(productData = null, index = null) {
        const productIndex = index !== null ? index : this.productCount;
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
        const productRowGroup = this.createProductRowGroup(productIndex + 1, product);
        
        // Insert before the tfoot (lainnya and total rows)
        const tfoot = this.productsTable.querySelector('tfoot');
        tfoot.parentNode.insertBefore(productRowGroup, tfoot);

        // Add animation class
        productRowGroup.classList.add('new-row');
        setTimeout(() => {
            productRowGroup.classList.remove('new-row');
        }, 300);

        // Setup auto-save for new inputs
        this.setupRowAutoSave(productRowGroup);
        
        // Auto-add new row if this is the last row and has data
        this.checkAutoAddRow();
    }

    createProductRowGroup(rowNumber, product) {
        const fragment = document.createDocumentFragment();
        const productIndex = rowNumber - 1;

        // Main product info row (Banyaknya)
        const mainRow = document.createElement('tr');
        mainRow.className = 'product-row';
        mainRow.dataset.productIndex = productIndex;
        
        // Only show delete button for rows after the first one (productIndex > 0)
        const deleteButtonHtml = productIndex > 0 ?
            `<button type="button" class="remove-product-btn" data-product-index="${productIndex}" title="Hapus produk">×</button>` :
            '';

        mainRow.innerHTML = `
            <td rowspan="3" class="row-number">
                ${rowNumber}.
                ${deleteButtonHtml}
            </td>
            <td rowspan="3" class="product-info">
                <input type="text" name="blok3a_products[${productIndex}][jenis_barang]"
                       value="${product.jenis_barang || ''}"
                       class="form-control form-control-sm jenis-barang-input"
                       placeholder="Jenis barang yang dihasilkan">
            </td>
            <td class="sub-row-label">Banyaknya</td>
            <td class="sub-row-unit"></td>
            ${this.months.map(month => `
                <td>
                    <input type="number"
                           name="blok3a_products[${productIndex}][banyaknya][${month}]"
                           value="${product.banyaknya?.[month] || ''}"
                           class="form-control form-control-sm"
                           step="0.01"
                           min="0"
                           placeholder="">
                </td>
            `).join('')}
        `;

        // Nilai (value) row
        const nilaiRow = document.createElement('tr');
        nilaiRow.className = 'sub-row nilai-row';
        nilaiRow.innerHTML = `
            <td class="sub-row-label">Nilai</td>
            <td class="sub-row-unit">Jutaan Rp</td>
            ${this.months.map(month => `
                <td>
                    <input type="number"
                           name="blok3a_products[${productIndex}][nilai][${month}]"
                           value="${product.nilai?.[month] || ''}"
                           class="form-control form-control-sm nilai-input"
                           data-product-index="${productIndex}"
                           data-month="${month}"
                           step="0.01"
                           min="0"
                           placeholder="">
                </td>
            `).join('')}
        `;

        // Harga/Satuan row
        const hargaRow = document.createElement('tr');
        hargaRow.className = 'sub-row harga-row';
        hargaRow.innerHTML = `
            <td class="sub-row-label">Harga/Satuan</td>
            <td class="sub-row-unit">000 Rp</td>
            ${this.months.map(month => `
                <td>
                    <input type="number"
                           name="blok3a_products[${productIndex}][harga_satuan][${month}]"
                           value="${product.harga_satuan?.[month] || ''}"
                           class="form-control form-control-sm"
                           step="0.01"
                           min="0"
                           placeholder="">
                </td>
            `).join('')}
        `;

        fragment.appendChild(mainRow);
        fragment.appendChild(nilaiRow);
        fragment.appendChild(hargaRow);

        // Add remove button functionality (only if button exists)
        const removeBtn = mainRow.querySelector('.remove-product-btn');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                this.removeProductRow(productIndex);
            });
        }

        return fragment;
    }

    removeProductRow(productIndex) {
        // Find all rows for this product (main row + 2 sub-rows)
        const allRows = this.productsTable.querySelectorAll('tr');
        const rowsToRemove = [];

        allRows.forEach(row => {
            if (row.dataset.productIndex === productIndex.toString() ||
                row.querySelector(`[data-product-index="${productIndex}"]`)) {
                rowsToRemove.push(row);
            }
        });

        if (rowsToRemove.length > 0) {
            // Confirm deletion
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                rowsToRemove.forEach(row => row.remove());
                this.calculateTotals();

                // Auto-save the removal
                if (window.surveyManager) {
                    window.surveyManager.scheduleAutoSave(`blok3a_products[${productIndex}]`, null, true);
                }
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
        console.log('calculateTotals called');
        console.log('Available months:', this.months);
        
        this.months.forEach(month => {
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
            
            // Debug: Log all input names that contain 'nilai'
            const allNilaiInputs = this.form.querySelectorAll('input[name*="nilai"]');
            console.log(`All nilai inputs found: ${allNilaiInputs.length}`);
            allNilaiInputs.forEach(input => {
                console.log(`Nilai input name: ${input.name}, value: ${input.value}`);
            });
            
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

            const formData = new FormData(this.form);
            const response = await window.surveyManager.saveAll(formData, false);

            if (response.success) {
                window.surveyManager.showStatus('Draft berhasil disimpan', 'success');
            } else {
                throw new Error(response.message || 'Failed to save draft');
            }
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

            const formData = new FormData(this.form);
            const response = await window.surveyManager.saveAll(formData, true);

            if (response.success) {
                window.surveyManager.showStatus('Data berhasil disimpan', 'success');
                
                // Navigate to next block
                setTimeout(() => {
                    if (window.surveyRoutes?.nextBlok) {
                        window.location.href = window.surveyRoutes.nextBlok;
                    }
                }, 1000);
            } else {
                throw new Error(response.message || 'Failed to save and continue');
            }
        } catch (error) {
            console.error('Save and continue error:', error);
            window.surveyManager?.showStatus('Gagal menyimpan data: ' + error.message, 'error');
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('survey-form') && document.getElementById('products-table')) {
        window.surveyBlok3aManager = new SurveyBlok3aManager();
    }
});
