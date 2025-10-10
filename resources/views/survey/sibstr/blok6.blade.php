@extends('layouts.app')

@section('title', 'SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR) - Blok VI - DataKita')
@section('description', 'Survei Industri Besar dan Sedang Triwulanan - Blok VI: Catatan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
@endpush

@section('content')
<div class="survey-container">
    <!-- Survey Header -->
    <div class="survey-header" data-aos="fade-up">
        <h1 class="survey-title">
            SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR)
        </h1>
        <h2 class="survey-subtitle">
            VI. CATATAN
        </h2>
        <p class="survey-description">
            Catatan tambahan untuk survei industri besar dan sedang triwulanan
        </p>
    </div>

    <!-- Auto-save Status -->
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    <!-- Survey Form -->
    <form id="survey-form" class="survey-form" data-aos="fade-up" data-aos-delay="200">
        @csrf

        <!-- Section VI: CATATAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK VI - VI. CATATAN</h3>
            </div>
            <div class="form-grid">
                <!-- Catatan Field -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">601.</span>
                        <span>Catatan tambahan (jika ada):</span>
                    </label>
                    <textarea name="catatan" id="catatan" rows="5"
                              class="form-control textarea"
                              placeholder="Tuliskan catatan tambahan jika diperlukan">{{ $surveyResponse->catatan ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok2" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    Kembali ke Bab 2
                </button>

                <button type="button" id="save-draft" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17,21 17,13 7,13 7,21"></polyline>
                        <polyline points="7,3 7,8 15,8"></polyline>
                    </svg>
                    Simpan Draft
                </button>

                <button type="button" id="finish-survey" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20,6 9,17 4,12"></polyline>
                    </svg>
                    Selesaikan Survei
                </button>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>Catatan bersifat opsional</span>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Set up survey routes for the JavaScript module
window.surveyRoutes = {
    autoSave: '{{ route("survey.sibstr.blok6.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok6.save") }}',
    status: '{{ route("survey.sibstr.blok6.status") }}',
    backToBlok2: '{{ route("survey.sibstr.blok2") }}',
    finishSurvey: '{{ route("survey.sibstr.blok6.finish") }}'
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok6.js') }}"></script>
@endpush
@endsection
