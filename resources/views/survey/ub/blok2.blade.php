@extends('layouts.user-dashboard')

@section('title', 'Survei UB — Blok II: Catatan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<style>
.ub-card{background:#fff;border-radius:1rem;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:1.75rem 2rem;margin-bottom:1.25rem;}
.dark .ub-card{background:#1f2937;border-color:#374151;}
.ub-section-title{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#3b82f6;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid #dbeafe;}
.dark .ub-section-title{color:#93c5fd;border-color:#1e3a5f;}
.ub-label{display:block;font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.35rem;}
.dark .ub-label{color:#d1d5db;}
.ub-input{width:100%;border:1px solid #d1d5db;border-radius:.625rem;padding:.55rem .85rem;font-size:.875rem;color:#111827;background:#fff;transition:border-color .15s;}
.ub-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.dark .ub-input{background:#111827;border-color:#4b5563;color:#f9fafb;}
.ub-stepper{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ub-step.done{background:#dcfce7;color:#15803d;border:1.5px solid #bbf7d0;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.active{background:#dbeafe;color:#1d4ed8;border:1.5px solid #bfdbfe;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.pending{background:#f3f4f6;color:#9ca3af;border:1.5px solid #e5e7eb;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.dark .ub-step.done{background:#14532d;color:#86efac;border-color:#166534;}
.dark .ub-step.active{background:#1e3a5f;color:#93c5fd;border-color:#1d4ed8;}
.dark .ub-step.pending{background:#374151;color:#6b7280;border-color:#4b5563;}
.ub-required{color:#ef4444;margin-left:.2rem;}
.ub-err-msg{font-size:.73rem;color:#ef4444;margin-top:.3rem;}
.ub-input.input-error{border-color:#ef4444 !important;}
</style>
@endpush

@section('dashboard-content')
<div class="lg:hidden mb-4">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400" type="button" data-open-sidebar>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>Menu
  </button>
</div>

<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">Blok II — Catatan</h1>
    <p class="ud-page-description">SE2026-L.UB · Catatan tambahan dari petugas atau responden</p>
  </div>
  <a href="{{ route('survey.ub.blok1d') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Kembali
  </a>
</div>

<div class="flex gap-5 items-start mt-4">
@include('survey.ub.partials.sidebar')
<div class="flex-1 min-w-0">

<div id="autosave-status" class="autosave-status hidden" style="margin-bottom:.75rem;"><span id="autosave-text"></span></div>

<form id="survey-form" method="POST" action="{{ ($editMode ?? false) ? route('survey.ub.edit.blok2.save') : route('survey.ub.blok2.save') }}" novalidate>
@csrf
<div class="ub-card">
  <p class="ub-section-title">BLOK II : CATATAN</p>
  <label class="ub-label">Catatan <span class="ub-required">*</span></label>
  <textarea name="catatan" id="catatan" class="ub-input" rows="8"
    placeholder="Tuliskan catatan tambahan, atau isi &quot;-&quot; jika tidak ada catatan.">{{ old('catatan',$response->catatan) }}</textarea>
  <div class="ub-err-msg" data-field="catatan"></div>
  <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
    Wajib diisi. Jika tidak ada catatan, isi dengan tanda <strong>-</strong>.
    Dapat berisi keterangan tambahan, klarifikasi jawaban, atau hal-hal yang perlu diperhatikan dalam pengolahan data.
  </p>
  <button type="button" id="btn-no-catatan"
    class="mt-2 text-xs text-blue-600 dark:text-blue-400 underline hover:no-underline">
    Tidak ada catatan? Klik untuk isi otomatis "-"
  </button>
</div>

<div class="flex flex-wrap items-center justify-between gap-4 mt-6 mb-8">
  <a href="{{ route('survey.ub.blok1d') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali ke Blok I-D
  </a>
  <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition">
    Simpan &amp; Lanjut ke Blok III
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
</div>
</form>
</div>
</div>

@push('scripts')
<script>
window.surveyRoutes = {
    autoSave: '{{ route("survey.ub.blok2.autosave") }}',
    saveAll:  '{{ ($editMode ?? false) ? route("survey.ub.edit.blok2.save") : route("survey.ub.blok2.save") }}',
    status:   '{{ route("survey.ub.blok2.status") }}',
    nextBlok: '{{ route("survey.ub.blok3") }}',
    showGuidanceNearSubmit: false
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script>
// Auto-fill "-" helper
document.getElementById('btn-no-catatan').addEventListener('click', function () {
    const ta = document.getElementById('catatan');
    if (!ta.value.trim()) {
        ta.value = '-';
        ta.dispatchEvent(new Event('input', { bubbles: true }));
        ta.dispatchEvent(new Event('change', { bubbles: true }));
    }
});

// Inline client-side validation on form submit
document.getElementById('survey-form').addEventListener('submit', function (e) {
    const ta  = document.getElementById('catatan');
    const err = document.querySelector('[data-field="catatan"]');
    if (!ta.value.trim()) {
        e.preventDefault();
        if (err) err.textContent = 'Catatan wajib diisi. Jika tidak ada catatan, isi dengan tanda "-".';
        ta.classList.add('input-error');
        ta.focus();
    } else {
        if (err) err.textContent = '';
        ta.classList.remove('input-error');
    }
});
document.getElementById('catatan').addEventListener('input', function () {
    const err = document.querySelector('[data-field="catatan"]');
    if (err) err.textContent = '';
    this.classList.remove('input-error');
});
</script>
@endpush
@endsection
