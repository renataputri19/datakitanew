# MONALISA Unified Dashboard Implementation

## Overview
This document describes the changes made to unify the MONALISA dashboard and charts views for both BPS and Kominfo users, promoting transparency by showing both self-assessment and verification scores to all users.

## Changes Made

### 1. Controller Updates

#### `app/Http/Controllers/Monalisa/KominfoController.php`
**Changes:**
- Updated `dashboard()` method to calculate both Kominfo and BPS scores
- Changed view from `monalisa.kominfo.dashboard` to `monalisa.bps.dashboard`
- Updated `showCharts()` method to prepare comparison chart data
- Changed view from `monalisa.kominfo.charts` to `monalisa.bps.charts`
- Modified `calculateScores()` to accept a `$type` parameter ('kominfo' or 'bps')
- Updated `prepareChartData()` to include both `kominfo_score` and `bps_score` fields

**Key Methods:**
```php
public function dashboard()
{
    // Now calculates both kominfoScores and bpsScores
    // Returns unified BPS dashboard view
}

public function showCharts()
{
    // Prepares comparison chart data
    // Returns unified BPS charts view
}

private function calculateScores($assessments, $domains, $type = 'kominfo')
{
    // Supports both 'kominfo' and 'bps' score types
}

private function prepareChartData($domains, $assessments)
{
    // Returns data with both kominfo_score and bps_score
}
```

#### `app/Http/Controllers/Monalisa/BpsController.php`
**Changes:**
- Updated `dashboard()` method to filter assessments based on user type
  - BPS users: See all submitted/verified assessments
  - Kominfo users: See only their own assessments
- Updated `showCharts()` method with same filtering logic
- Both methods now work for both user types

**Key Logic:**
```php
if ($user->is_bps) {
    // BPS users see all submitted/verified assessments
    $assessments = MonalisaAssessment::whereIn('status', ['submitted', 'verified'])
        ->with(['indikator.aspek.domain', 'kominfoUser', 'documents'])
        ->get();
} else {
    // Kominfo users see only their own assessments
    $assessments = MonalisaAssessment::where('kominfo_user_id', $user->id)
        ->with(['indikator.aspek.domain', 'kominfoUser', 'documents'])
        ->get();
}
```

### 2. View Updates

#### `resources/views/monalisa/bps/dashboard.blade.php`
**Changes:**
- Added user type detection at the top of the file
- Updated page title and description to be dynamic based on user type
- Added icons to score toggle buttons for better UX
- View now works seamlessly for both BPS and Kominfo users

**Dynamic Elements:**
```blade
@php
    $isBpsUser = auth()->user()->is_bps;
    $isKominfoUser = auth()->user()->is_kominfo_user;
@endphp

<h1 class="ud-page-title">Dashboard MONALISA{{ $isBpsUser ? ' - BPS' : '' }}</h1>
<p class="ud-page-description">
    Sistem Monitoring dan Evaluasi Statistik Sektoral - 
    {{ $isBpsUser ? 'Audit & Verifikasi' : 'Self Assessment' }}
</p>
```

#### `resources/views/monalisa/bps/charts.blade.php`
**Changes:**
- Added user type detection
- Updated page title and description to be dynamic
- Updated back button to route to appropriate dashboard based on user type
- View now works for both user types

#### `resources/views/partials/monalisa/sidebar.blade.php`
**Changes:**
- Added route variable definitions for clarity
- Both user types now use their respective routes but point to unified views
- Comments added to clarify the unified approach

### 3. Deprecated Views

The following views are now deprecated but kept for reference:

#### `resources/views/monalisa/kominfo/dashboard.blade.php`
- Added deprecation notice at the top
- Explains that the unified BPS dashboard is now used
- File kept for reference only

#### `resources/views/monalisa/kominfo/charts.blade.php`
- Added deprecation notice at the top
- Explains that the unified BPS charts view is now used
- File kept for reference only

## Benefits of Unified Dashboard

### 1. **Transparency**
- Both Kominfo and BPS users can see both self-assessment and verification scores
- Promotes accountability and understanding of the verification process
- Reduces information silos between user types

### 2. **Consistency**
- Single source of truth for dashboard and charts logic
- Easier to maintain and update
- Consistent user experience across user types

### 3. **Comparison**
- Users can easily compare Kominfo and BPS scores side-by-side
- Visual charts show differences clearly
- Helps identify areas needing improvement

### 4. **Maintainability**
- Fewer view files to maintain
- Changes to dashboard/charts only need to be made once
- Reduced code duplication

## User Experience

### For Kominfo Users:
- Can see their own self-assessment scores
- Can see BPS verification scores (once verified)
- Can compare their assessment with BPS verification
- Better understanding of verification process

### For BPS Users:
- Can see all submitted/verified assessments
- Can compare Kominfo and BPS scores across all assessments
- Same familiar interface as before
- No functionality lost

## Routes

Both user types maintain their own routes but use the same underlying views:

**Kominfo Routes:**
- `/monalisa/kominfo/dashboard` → Uses `monalisa.bps.dashboard` view
- `/monalisa/kominfo/charts` → Uses `monalisa.bps.charts` view

**BPS Routes:**
- `/monalisa/bps/dashboard` → Uses `monalisa.bps.dashboard` view
- `/monalisa/bps/charts` → Uses `monalisa.bps.charts` view

## Data Filtering

The controllers ensure proper data filtering:

- **Kominfo users**: Only see their own assessments
- **BPS users**: See all submitted/verified assessments

This maintains security while providing transparency.

## Testing Recommendations

1. **Test as Kominfo User:**
   - Verify dashboard shows both Kominfo and BPS scores
   - Verify only own assessments are visible
   - Verify charts show comparison data
   - Verify navigation works correctly

2. **Test as BPS User:**
   - Verify dashboard shows all submitted/verified assessments
   - Verify both score types are visible
   - Verify charts show comparison across all assessments
   - Verify verification functionality still works

3. **Test Score Toggle:**
   - Verify toggle switches between Kominfo and BPS views
   - Verify data updates correctly
   - Verify dark mode compatibility

4. **Test Navigation:**
   - Verify sidebar links work for both user types
   - Verify back buttons route correctly
   - Verify breadcrumbs (if any) are correct

## Future Enhancements

Potential improvements to consider:

1. **Agreement Rate Display**: Show percentage agreement between Kominfo and BPS scores
2. **Trend Analysis**: Show score changes over time
3. **Export Functionality**: Allow users to export comparison data
4. **Notifications**: Alert Kominfo users when BPS verification is complete
5. **Comments/Feedback**: Allow BPS to provide feedback visible to Kominfo users

## Migration Notes

- No database changes required
- No route changes required
- Controllers updated to use unified views
- Old Kominfo-specific views deprecated but not deleted
- Fully backward compatible

## Conclusion

The unified dashboard implementation successfully achieves the goal of transparency while maintaining security and user-specific data filtering. Both user types now have access to comparison data, promoting better understanding and collaboration in the assessment and verification process.

