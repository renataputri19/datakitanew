# MONALISA Indicator Analysis Page Implementation

## Overview
A detailed indicator-level visualization page has been added to the MONALISA system, providing granular drill-down analysis at both domain and indicator levels with side-by-side comparison of Kominfo and BPS scores using the IPS (Indeks Pembangunan Statistik) rating system.

## Latest Update (2025-11-03)
**Major Restructure:** The page has been completely restructured with the following changes:
1. **Chart Hierarchy:** Now shows domain-level charts (aspeks as axes) AND aspek-level charts (indicators as axes)
2. **Side-by-Side Layout:** Removed toggle buttons; Kominfo and BPS charts are displayed side-by-side for direct comparison
3. **IPS Rating System:** Implemented 5-category IPS predikat system (Memuaskan, Sangat Baik, Baik, Cukup, Kurang)
4. **Responsive Design:** 2-column layout on desktop, stacked on mobile

## Implementation Summary

### 1. Sidebar Navigation
**File:** `resources/views/partials/monalisa/sidebar.blade.php`

**Changes:**
- Added new route variable: `$indicatorAnalysisRoute = 'monalisa.' . $userType . '.indicator-analysis'`
- Added new menu item "Analisis Indikator" below the "Visualisasi" link
- Icon: Magnifying glass with plus sign (search/detail icon)
- Active state tracking with route matching

### 2. Routes
**File:** `routes/web.php`

**Added Routes:**
- **Kominfo:** `GET /monalisa/kominfo/indicator-analysis` → `KominfoController@showIndicatorAnalysis`
- **BPS:** `GET /monalisa/bps/indicator-analysis` → `BpsController@showIndicatorAnalysis`

Both routes use the same unified view pattern as dashboard and charts.

### 3. Controller Methods

#### KominfoController
**File:** `app/Http/Controllers/Monalisa/KominfoController.php`

**New Methods:**
1. `showIndicatorAnalysis()` - Main method that:
   - Fetches all domains with aspeks and indicators
   - Gets user's own assessments only (filtered by `kominfo_user_id`)
   - Prepares indicator-level chart data
   - Returns unified view `monalisa.bps.indicator-analysis`

2. `prepareIndicatorChartData($domains, $assessments)` - Helper method that:
   - Groups indicators by aspek
   - Calculates both Kominfo and BPS scores for each indicator
   - Computes average scores per aspek
   - **NEW:** Computes domain-level average scores from aspek averages
   - **NEW:** Adds aspek_code field for chart labels
   - Tracks assessment progress (assessed vs total indicators)
   - Returns structured data for both domain and aspek charts

#### BpsController
**File:** `app/Http/Controllers/Monalisa/BpsController.php`

**New Methods:**
1. `showIndicatorAnalysis()` - Main method that:
   - Fetches all domains with aspeks and indicators
   - Gets all submitted/verified assessments (BPS users see all)
   - Prepares indicator-level chart data
   - Returns unified view `monalisa.bps.indicator-analysis`

2. `prepareIndicatorChartData($domains, $assessments)` - Same helper method as KominfoController

**Data Filtering:**
- **Kominfo users:** Only see their own assessments (`where('kominfo_user_id', $user->id)`)
- **BPS users:** See all submitted/verified assessments (`whereIn('status', ['submitted', 'verified'])`)

### 4. View File
**File:** `resources/views/monalisa/bps/indicator-analysis.blade.php`

**Structure:**
1. **Page Header**
   - Title: "Analisis Detail Indikator"
   - Description explaining side-by-side comparison
   - Back button to return to charts page

2. **IPS Rating Legend** (NEW)
   - Displayed prominently at the top
   - 5 categories with color coding:
     - **Memuaskan** (4.2 - 5.0) - Green (#10b981)
     - **Sangat Baik** (3.5 - <4.2) - Blue (#3b82f6)
     - **Baik** (2.6 - <3.5) - Amber (#f59e0b)
     - **Cukup** (1.8 - <2.6) - Orange (#f97316)
     - **Kurang** (<1.8) - Red (#ef4444)

3. **Chart Organization** (RESTRUCTURED)
   - Grouped by Domain
   - For each domain:
     - **Domain-Level Charts** (NEW): Side-by-side comparison
       - Left: Kominfo domain chart (aspeks as axes)
       - Right: BPS domain chart (aspeks as axes)
       - Shows domain average score
     - **Aspek-Level Charts**: Side-by-side comparison for each aspek
       - Left: Kominfo aspek chart (indicators as axes)
       - Right: BPS aspek chart (indicators as axes)
       - Shows aspek statistics and average scores

4. **Responsive Layout** (NEW)
   - Desktop (≥1024px): 2-column grid (side-by-side)
   - Mobile (<1024px): Single column (stacked vertically)
   - Uses CSS Grid with `.comparison-grid` class

5. **Chart Features**
   - ApexCharts radar/spider charts
   - Y-axis: 0-5 maturity level scale
   - X-axis:
     - Domain charts: Aspek codes (e.g., "1.1", "1.2")
     - Aspek charts: Indicator codes (e.g., "1.1.1", "1.1.2")
   - **Tooltips with IPS Predikat** (NEW):
     - Shows full name, score, and IPS predikat
     - Example: "Aspek Name: 3.75 (Sangat Baik)"
   - Color scheme:
     - Kominfo: Blue (#3b82f6)
     - BPS: Green (#10b981)
   - Dark mode support

6. **Score Section Headers** (NEW)
   - Each chart has a colored header with icon
   - Kominfo: Blue with user icon
   - BPS: Green with checkmark icon

7. **Aspek Statistics**
   - Badge showing assessed/total indicators
   - Average score badge (when applicable)
   - Color-coded by type (assessed/verified/avg-score)

8. **Styling**
   - Consistent with MONALISA design system
   - Uses existing CSS classes: `monalisa-btn`, `ud-page-header`, etc.
   - Custom styles for comparison grid, score headers, IPS legend
   - Full dark mode compatibility
   - AOS animations for scroll effects

### 5. JavaScript Functionality

**Toggle Logic:**
```javascript
// Score type toggle
toggleButtons.forEach(button => {
    button.addEventListener('click', function() {
        const scoreType = this.dataset.scoreToggle;
        
        // Update button states
        toggleButtons.forEach(btn => {
            btn.classList.remove('monalisa-btn-primary', 'active');
            btn.classList.add('monalisa-btn-secondary');
        });
        this.classList.add('monalisa-btn-primary', 'active');
        
        // Show/hide sections
        scoreSections.forEach(section => {
            section.style.display = section.dataset.scoreType === scoreType ? 'block' : 'none';
        });
    });
});
```

**Chart Rendering:**
- Separate charts for Kominfo and BPS scores
- Dynamic chart IDs: `kominfoAspekChart{aspekId}` and `bpsAspekChart{aspekId}`
- Responsive to dark mode
- Tooltips with contextual information

## Data Structure

### Chart Data Format (UPDATED)
```php
[
    'domains' => [
        [
            'id' => 'uuid',
            'name' => 'Domain Name',
            'domain_number' => 1,
            'kominfo_avg_score' => 3.45,  // NEW: Domain-level average
            'bps_avg_score' => 3.20,      // NEW: Domain-level average
            'aspeks' => [
                [
                    'id' => 'uuid',
                    'name' => 'Aspek Name',
                    'aspek_number' => 1,
                    'aspek_code' => '1.1',  // NEW: For chart labels
                    'total_indikators' => 5,
                    'assessed_indikators' => 3,
                    'verified_indikators' => 2,
                    'kominfo_avg_score' => 3.5,
                    'bps_avg_score' => 3.0,
                    'indikators' => [
                        [
                            'id' => 'uuid',
                            'name' => 'Indicator Name',
                            'code' => '1.1.1',
                            'kominfo_score' => 4,
                            'bps_score' => 3,
                            'status' => 'verified',
                            'has_assessment' => true
                        ],
                        // ... more indicators
                    ]
                ],
                // ... more aspeks
            ]
        ],
        // ... more domains
    ]
]
```

## Key Features

### 1. Dual-Level Analysis (NEW)
- **Domain-Level Charts:** Shows aspek scores within each domain for high-level overview
- **Aspek-Level Charts:** Shows individual indicator scores within each aspek for detailed analysis
- Hierarchical drill-down from domain → aspek → indicator

### 2. Side-by-Side Comparison (NEW)
- **No Toggle Required:** Both Kominfo and BPS scores visible simultaneously
- Direct visual comparison for transparency
- Easier to spot differences and discrepancies
- Responsive layout: side-by-side on desktop, stacked on mobile

### 3. IPS Rating System (NEW)
- **5-Category Predikat System:**
  - Memuaskan (4.2-5.0) - Satisfactory
  - Sangat Baik (3.5-<4.2) - Very Good
  - Baik (2.6-<3.5) - Good
  - Cukup (1.8-<2.6) - Fair
  - Kurang (<1.8) - Poor
- Color-coded visual indicators
- Tooltips show predikat for each score
- Aligned with official IPS methodology

### 4. Visual Indicators
- IPS color-coded score ranges
- Progress tracking (assessed vs total)
- Average score calculations at both domain and aspek levels
- Clear section headers with icons

### 5. User Experience
- Back button for easy navigation
- Fully responsive design (desktop/tablet/mobile)
- Smooth AOS animations
- Dark mode support
- Intuitive side-by-side layout

## Testing Checklist

### For Kominfo Users:
- [ ] Can access `/monalisa/kominfo/indicator-analysis`
- [ ] Sidebar shows "Analisis Indikator" menu item
- [ ] Menu item highlights when active
- [ ] Only sees own assessments
- [ ] **Domain charts render correctly** (NEW)
- [ ] **Aspek charts render correctly** (NEW)
- [ ] **Side-by-side layout works on desktop** (NEW)
- [ ] **Stacked layout works on mobile** (NEW)
- [ ] **IPS legend displays correctly** (NEW)
- [ ] **Tooltips show IPS predikat** (NEW)
- [ ] Back button returns to charts page
- [ ] Dark mode works correctly
- [ ] Mobile sidebar toggle works

### For BPS Users:
- [ ] Can access `/monalisa/bps/indicator-analysis`
- [ ] Sidebar shows "Analisis Indikator" menu item
- [ ] Menu item highlights when active
- [ ] Sees all submitted/verified assessments
- [ ] **Domain charts render correctly** (NEW)
- [ ] **Aspek charts render correctly** (NEW)
- [ ] **Side-by-side layout works on desktop** (NEW)
- [ ] **Stacked layout works on mobile** (NEW)
- [ ] **IPS legend displays correctly** (NEW)
- [ ] **Tooltips show IPS predikat** (NEW)
- [ ] Back button returns to charts page
- [ ] Dark mode works correctly
- [ ] Mobile sidebar toggle works

### General:
- [ ] No console errors
- [ ] Charts are responsive
- [ ] AOS animations work
- [ ] Page loads without errors
- [ ] Data displays correctly
- [ ] Statistics are accurate
- [ ] **Domain average scores calculated correctly** (NEW)
- [ ] **IPS predikat colors match legend** (NEW)
- [ ] **Responsive breakpoint at 1024px works** (NEW)

## Benefits

1. **Multi-Level Analysis:** View scores at domain, aspek, AND indicator levels for comprehensive understanding
2. **Enhanced Transparency:** Side-by-side comparison eliminates need for toggling, making differences immediately visible
3. **Standardized Rating:** IPS predikat system provides consistent, official rating methodology
4. **Actionable Insights:** Identify specific indicators with low scores or disagreements at a glance
5. **Better Decision Making:** Focus improvement efforts based on IPS categories and direct comparisons
6. **Progress Tracking:** See assessment completion at both domain and indicator levels
7. **Improved UX:** Responsive design ensures usability on all devices without sacrificing information density

## Files Modified

### Initial Implementation:
1. `resources/views/partials/monalisa/sidebar.blade.php` - Added menu item
2. `routes/web.php` - Added routes for both user types
3. `app/Http/Controllers/Monalisa/KominfoController.php` - Added controller methods
4. `app/Http/Controllers/Monalisa/BpsController.php` - Added controller methods

### Latest Update (2025-11-03):
1. `app/Http/Controllers/Monalisa/BpsController.php` - Updated `prepareIndicatorChartData()` to include domain-level aggregation
2. `app/Http/Controllers/Monalisa/KominfoController.php` - Updated `prepareIndicatorChartData()` to include domain-level aggregation
3. `resources/views/monalisa/bps/indicator-analysis.blade.php` - Complete restructure:
   - Removed toggle buttons
   - Added side-by-side comparison layout
   - Added domain-level charts
   - Implemented IPS rating system
   - Added responsive CSS grid
4. `MONALISA_INDICATOR_ANALYSIS_IMPLEMENTATION.md` - Updated documentation

## Files Created

1. `resources/views/monalisa/bps/indicator-analysis.blade.php` - Main view file (unified for both user types)

## Dependencies

- ApexCharts (already included in charts.blade.php)
- AOS (already included in layout)
- Existing MONALISA CSS/JS files
- No new dependencies required

## Notes

- Follows the unified dashboard pattern (both user types use same view)
- Consistent with existing MONALISA design system
- Maintains data filtering security (Kominfo sees own, BPS sees all)
- Fully responsive and accessible
- No breaking changes to existing functionality

