# SIBSTR Blok IIIA Implementation

## Overview
This document describes the implementation of conditional validation and Blok IIIA (KONDISI PEREKONOMIAN) for the SIBSTR survey system.

## Features Implemented

### 1. Conditional Validation Based on Question 201
- **Question 201**: "Kondisi Perusahaan" with options:
  - a. Masih Aktif
  - b. Belum Beroperasi  
  - c. Tutup
  - d. Pindah
  - e. Tidak Ditemukan
  - f. Double / Ganda / Duplikat

- **Conditional Logic**: 
  - If "a. Masih Aktif" is selected → User proceeds to Blok IIIA
  - If any other option is selected → User skips to Blok VI

### 2. Database Schema Updates
- **Migration**: `2025_10_07_080000_add_blok3a_fields_to_survey_responses_table.php`
- **New Fields**:
  - `blok3a_products` (JSON) - Stores dynamic product entries
  - `blok3a_lainnya` (JSON) - Stores "Lainnya" (Others) data
  - `blok3a_totals` (JSON) - Stores calculated totals

### 3. Blok IIIA Features
- **Dynamic Product Table**: Excel-like layout with monthly columns
- **Auto-calculation**: Totals automatically calculated from product values
- **Dynamic Rows**: Automatically adds new product rows when needed
- **Three Sub-rows per Product**:
  - Banyaknya (Quantity)
  - Nilai (Value in Million Rupiah)
  - Harga/Satuan (Price per Unit in Thousand Rupiah)

### 4. Monthly Data Structure
- **Columns**: 2024 (Des) + 2025 (Jan-Des) = 13 months total
- **Data Types**: Numeric inputs with decimal support
- **Auto-save**: Real-time saving as user types

## Files Modified/Created

### Backend Files
1. **app/Http/Controllers/SurveyController.php**
   - Added `sibstrBlok3a()` method
   - Added `autoSaveBlok3a()` method
   - Added `saveAllBlok3a()` method
   - Added `getStatusBlok3a()` method
   - Updated `saveAllBlok2()` with conditional validation

2. **app/Models/SurveyResponse.php**
   - Added Blok IIIA fields to fillable array
   - Added JSON casting for new fields
   - Added helper methods for data structure

3. **database/migrations/2025_10_07_080000_add_blok3a_fields_to_survey_responses_table.php**
   - New migration for Blok IIIA fields

4. **routes/web.php**
   - Added Blok IIIA routes

### Frontend Files
1. **resources/views/survey/sibstr/blok3a.blade.php**
   - New Blade template for Blok IIIA
   - Excel-like table layout
   - Dynamic product rows

2. **public/css/survey-blok3a.css**
   - Specific styling for Blok IIIA
   - Excel-like table appearance
   - Responsive design

3. **public/js/survey-blok3a.js**
   - Dynamic table management
   - Auto-calculation logic
   - Form validation
   - Auto-save functionality

4. **public/js/survey.js**
   - Updated conditional navigation logic

5. **resources/views/survey/sibstr/blok2.blade.php**
   - Updated route configuration

## Testing Instructions

### 1. Test Conditional Flow
1. Navigate to `/survei/sibstr/blok2`
2. **Test Case 1 - Masih Aktif**:
   - Select "a. Masih Aktif" for Question 201
   - Fill other required fields
   - Click "Simpan dan Lanjutkan"
   - Should navigate to Blok IIIA (`/survei/sibstr/blok3a`)

3. **Test Case 2 - Other Options**:
   - Select any option other than "a. Masih Aktif"
   - Click "Simpan dan Lanjutkan"  
   - Should navigate to Blok VI (`/survei/sibstr/blok6`)

### 2. Test Blok IIIA Functionality
1. Navigate to `/survei/sibstr/blok3a` (only accessible if kondisi_perusahaan = 'masih_aktif')
2. **Test Dynamic Rows**:
   - Fill in product information in the first row
   - New row should automatically appear
   - Test remove button functionality

3. **Test Auto-calculation**:
   - Enter values in "Nilai (Jutaan Rp)" fields
   - Enter values in "Lainnya" row
   - Total row should automatically calculate sum

4. **Test Auto-save**:
   - Enter data and wait 2 seconds
   - Check auto-save status indicator
   - Refresh page to verify data persistence

### 3. Test Validation
1. **Conditional Validation in Blok 2**:
   - Try to save without selecting kondisi_perusahaan
   - Should show validation error
   - If "Masih Aktif" selected, other fields become required
   - If other option selected, other fields become optional

2. **Access Control**:
   - Try to access `/survei/sibstr/blok3a` without setting kondisi_perusahaan to 'masih_aktif'
   - Should redirect to Blok VI with warning message

## Data Structure

### Product Data Structure
```json
{
  "blok3a_products": [
    {
      "jenis_barang": "Product Name",
      "uraian": "Description", 
      "satuan": "Unit",
      "banyaknya": {
        "2024_des": "100",
        "2025_jan": "110",
        // ... other months
      },
      "nilai": {
        "2024_des": "1000.50",
        "2025_jan": "1100.75",
        // ... other months
      },
      "harga_satuan": {
        "2024_des": "10.00",
        "2025_jan": "10.50",
        // ... other months
      }
    }
  ],
  "blok3a_lainnya": {
    "uraian": "Other income description",
    "nilai": {
      "2024_des": "500.00",
      // ... other months
    }
  },
  "blok3a_totals": {
    "2024_des": "1500.50",
    "2025_jan": "1600.75",
    // ... other months (auto-calculated)
  }
}
```

## Routes Added
- `GET /survei/sibstr/blok3a` - Display Blok IIIA form
- `POST /survei/sibstr/blok3a/auto-save` - Auto-save Blok IIIA data
- `POST /survei/sibstr/blok3a/save-all` - Save all Blok IIIA data
- `GET /survei/sibstr/blok3a/status` - Get Blok IIIA status

## Notes
- All Blok IIIA fields are nullable in the database
- JSON fields provide flexibility for dynamic product entries
- Auto-calculation happens in real-time on the frontend
- Server-side validation ensures data integrity
- Responsive design works on mobile devices
