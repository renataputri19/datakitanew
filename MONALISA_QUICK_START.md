# MONALISA System - Quick Start Guide

## ✅ What's Been Completed

### Backend (100% Complete)
- ✅ Database schema with 6 tables
- ✅ All migrations executed successfully
- ✅ 5 Eloquent models with relationships
- ✅ Data seeder with complete structure (5 domains, 19 aspeks, 38 indikators)
- ✅ Kominfo controller with 8 methods
- ✅ BPS controller with 7 methods
- ✅ All routes configured
- ✅ Scoring calculation system
- ✅ File upload/download system
- ✅ Document commenting system

### Frontend (40% Complete)
- ✅ CSS file with distinctive MONALISA styling
- ✅ JavaScript file with interactive features
- ✅ Kominfo dashboard view
- ✅ BPS dashboard view
- ⏳ Assessment form views (pending)
- ⏳ Domain detail views (pending)
- ⏳ Chart.js integration (pending)

## 🚀 How to Test Right Now

### Step 1: Create Test Users

Run these SQL commands in your database:

```sql
-- Create or update a Kominfo test user
UPDATE users 
SET is_kominfo_user = 1 
WHERE email = 'your-email@example.com';

-- Create or update a BPS test user
UPDATE users 
SET is_bps = 1 
WHERE email = 'another-email@example.com';
```

Or register new users and then update their roles.

### Step 2: Access the Dashboards

**Kominfo Dashboard:**
- URL: `http://localhost:8000/monalisa/kominfo/dashboard`
- Login with Kominfo user credentials
- You'll see:
  - Total IPS score
  - Completed assessments count
  - Verified assessments count
  - Domain overview with progress bars
  - Recent assessments table

**BPS Dashboard:**
- URL: `http://localhost:8000/monalisa/bps/dashboard`
- Login with BPS user credentials
- You'll see:
  - Dual score view (Kominfo vs BPS) with toggle
  - Pending verifications count
  - Agreement rate
  - Domain overview
  - Pending verifications table

### Step 3: Verify Database Structure

Check that the seeder populated the data:

```sql
-- Should return 5 domains
SELECT * FROM monalisa_domains ORDER BY `order`;

-- Should return 19 aspeks
SELECT * FROM monalisa_aspeks ORDER BY domain_id, `order`;

-- Should return 38 indikators
SELECT * FROM monalisa_indikators ORDER BY aspek_id, `order`;
```

## 📋 What Still Needs to Be Done

### Priority 1: Assessment Form Views

Create these files to enable the full workflow:

1. **`resources/views/monalisa/kominfo/assessment.blade.php`**
   - Form to select maturity level (1-5)
   - Justification textarea
   - Document upload area
   - Save and submit buttons
   - Auto-save indicator

2. **`resources/views/monalisa/bps/assessment.blade.php`**
   - Display Kominfo's assessment
   - Show uploaded documents
   - BPS maturity level selector
   - Audit comment textarea
   - Document comment section
   - Verify button

### Priority 2: Domain Detail Views

3. **`resources/views/monalisa/kominfo/domain.blade.php`**
   - List all aspeks in the domain
   - List all indikators under each aspek
   - Show assessment status for each indikator
   - Links to assessment forms

4. **`resources/views/monalisa/bps/domain.blade.php`**
   - Similar to Kominfo but with verification focus
   - Show both Kominfo and BPS scores
   - Filter by verification status

### Priority 3: Assessment List View

5. **`resources/views/monalisa/bps/assessment-list.blade.php`**
   - Paginated list of all assessments
   - Filters by status and domain
   - Search functionality
   - Bulk actions (if needed)

### Priority 4: Data Visualizations

Add Chart.js integration:
- Domain score comparison (bar chart)
- Aspek-level radar chart
- Kominfo vs BPS comparison (line chart)
- Progress over time (area chart)

## 🎯 Complete User Workflow

### Kominfo User Journey:
1. Login → Redirected to `/monalisa/kominfo/dashboard`
2. Click on a domain → See all aspeks and indikators
3. Click on an indikator → Assessment form
4. Select maturity level (1-5)
5. Write justification
6. Upload supporting documents
7. Submit for BPS verification
8. View status on dashboard

### BPS User Journey:
1. Login → Redirected to `/monalisa/bps/dashboard`
2. See pending verifications
3. Click "Verify" on an assessment
4. Review Kominfo's self-assessment
5. Review uploaded documents
6. Add comments on documents
7. Assign BPS maturity level
8. Write audit comment
9. Submit verification
10. View updated scores

## 🔧 API Endpoints Reference

### Kominfo Endpoints

```
GET  /monalisa/kominfo/dashboard
GET  /monalisa/kominfo/domain/{domainId}
GET  /monalisa/kominfo/assessment/{indikatorId}
POST /monalisa/kominfo/assessment/{indikatorId}
POST /monalisa/kominfo/assessment/{assessmentId}/submit
POST /monalisa/kominfo/assessment/{assessmentId}/upload
DELETE /monalisa/kominfo/document/{documentId}
GET  /monalisa/kominfo/document/{documentId}/download
```

### BPS Endpoints

```
GET  /monalisa/bps/dashboard
GET  /monalisa/bps/domain/{domainId}
GET  /monalisa/bps/assessments
GET  /monalisa/bps/assessment/{assessmentId}
POST /monalisa/bps/assessment/{assessmentId}/verify
POST /monalisa/bps/document/{documentId}/comment
GET  /monalisa/bps/document/{documentId}/download
```

## 📊 Database Schema Quick Reference

### monalisa_assessments (Core Table)

```
id (UUID)
indikator_id (UUID) → monalisa_indikators
kominfo_user_id (UUID) → users
kominfo_maturity_level (1-5)
kominfo_justification (text)
kominfo_submitted_at (timestamp)
bps_user_id (UUID) → users
bps_maturity_level (1-5)
bps_audit_comment (text)
bps_verified_at (timestamp)
status (draft|submitted|verified|rejected)
```

### monalisa_documents

```
id (UUID)
assessment_id (UUID) → monalisa_assessments
uploaded_by (UUID) → users
original_filename
stored_filename
file_path
file_type
file_size
description
```

### monalisa_document_comments

```
id (UUID)
document_id (UUID) → monalisa_documents
user_id (UUID) → users
comment (text)
status (pass|fail|needs_revision|info)
```

## 🎨 Frontend Integration

### Include CSS and JS in Blade Templates

```blade
@push('styles')
<link rel="stylesheet" href="{{ asset('css/monalisa-dashboard.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/monalisa-dashboard.js') }}"></script>
@endpush
```

### Use MONALISA Components

```blade
<!-- Score Card -->
<div class="monalisa-score-card">
    <div class="monalisa-score-label">Total IPS Score</div>
    <div class="monalisa-score-value">{{ number_format($score, 2) }}</div>
</div>

<!-- Domain Card -->
<div class="monalisa-domain-card">
    <div class="monalisa-domain-header">
        <div class="monalisa-domain-title">Domain 1</div>
        <div class="monalisa-domain-weight">28%</div>
    </div>
    <div class="monalisa-progress">
        <div class="monalisa-progress-bar" style="width: 75%"></div>
    </div>
</div>

<!-- Maturity Selector -->
<div class="monalisa-maturity-selector">
    @for($i = 1; $i <= 5; $i++)
    <div class="monalisa-maturity-option">
        <input type="radio" name="maturity_level" value="{{ $i }}" 
               id="level{{ $i }}" class="monalisa-maturity-input">
        <label for="level{{ $i }}" class="monalisa-maturity-label">
            <span class="monalisa-maturity-number">{{ $i }}</span>
            <span class="monalisa-maturity-name">{{ $levels[$i] }}</span>
        </label>
    </div>
    @endfor
</div>

<!-- Status Badge -->
<span class="monalisa-badge monalisa-badge-{{ $status }}">
    {{ ucfirst($status) }}
</span>
```

## 🐛 Troubleshooting

### Issue: Routes not found
**Solution:** Clear route cache
```bash
php artisan route:clear
php artisan route:cache
```

### Issue: CSS/JS not loading
**Solution:** Clear view cache and check public path
```bash
php artisan view:clear
php artisan config:clear
```

### Issue: File upload fails
**Solution:** Check storage permissions
```bash
php artisan storage:link
chmod -R 775 storage
```

### Issue: Scores showing 0
**Solution:** Make sure assessments have maturity levels set and are submitted

## 📞 Support

For questions or issues:
1. Check `MONALISA_IMPLEMENTATION_SUMMARY.md` for detailed documentation
2. Review controller methods for API usage
3. Check browser console for JavaScript errors
4. Check Laravel logs: `storage/logs/laravel.log`

---

**Quick Start Version:** 1.0
**Last Updated:** November 2, 2025

