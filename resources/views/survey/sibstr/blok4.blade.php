@extends('layouts.app')

@section('title', 'SIBSTR - Blok IV Fenomena dan Catatan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
@endpush

@section('content')
<div class="survey-container">
    @if(!empty($isEditMode))
    @include('survey.partials.edit-mode-banner', ['exitUrl' => route('dashboard.surveys.sibstr.results')])
    @endif

    <!-- Survey Header -->
    <div class="survey-header" data-aos="fade-up">
        <h1 class="survey-title">
            SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR)
        </h1>
        <h2 class="survey-subtitle">
            BLOK IV. FENOMENA DAN CATATAN
        </h2>
        <p class="survey-description">
            Jelaskan fenomena penting dan catatan per triwulan terkait perubahan signifikan.
        </p>
    </div>

    <!-- Auto-save Status -->
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    <!-- Survey Form -->
    <form id="survey-form" class="survey-form" data-aos="fade-up" data-aos-delay="200">
        @csrf

        <!-- BLOK IV: Fenomena per Triwulan -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK IV - Fenomena dan Catatan</h3>
                <p class="section-subtitle">Isi bila ada peningkatan/penurunan signifikan per triwulan.</p>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">401.</span>
                        <span>Triwulan I (Jan–Mar): Jelaskan fenomena atau catatan</span>
                    </label>
                    <textarea name="blok4[triwulan1]" id="blok4_triwulan1" rows="4"
                              class="form-control textarea"
                              placeholder="Contoh: Lonjakan pesanan produk X karena kampanye...">{{ $surveyResponse->blok4_data['triwulan1'] ?? '' }}</textarea>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">402.</span>
                        <span>Triwulan II (Apr–Jun): Jelaskan fenomena atau catatan</span>
                    </label>
                    <textarea name="blok4[triwulan2]" id="blok4_triwulan2" rows="4"
                              class="form-control textarea"
                              placeholder="Contoh: Penurunan produksi karena perawatan mesin...
">{{ $surveyResponse->blok4_data['triwulan2'] ?? '' }}</textarea>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">403.</span>
                        <span>Triwulan III (Jul–Sep): Jelaskan fenomena atau catatan</span>
                    </label>
                    <textarea name="blok4[triwulan3]" id="blok4_triwulan3" rows="4"
                              class="form-control textarea"
                              placeholder="Contoh: Perubahan harga bahan baku impor...">{{ $surveyResponse->blok4_data['triwulan3'] ?? '' }}</textarea>
                </div>

                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">404.</span>
                        <span>Triwulan IV (Okt–Des): Jelaskan fenomena atau catatan</span>
                    </label>
                    <textarea name="blok4[triwulan4]" id="blok4_triwulan4" rows="4"
                              class="form-control textarea"
                              placeholder="Contoh: Peningkatan ekspor pada akhir tahun...">{{ $surveyResponse->blok4_data['triwulan4'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok3b" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    Kembali ke Bab 3B
                </button>

                <button type="button" id="save-draft" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17,21 17,13 7,13 7,21"></polyline>
                        <polyline points="7,3 7,8 15,8"></polyline>
                    </svg>
                    Simpan Draft
                </button>

                <button type="button" id="save-complete" class="btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                    Simpan dan Lanjutkan
                </button>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>Opsional, isi bila ada fenomena penting.</span>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Set up survey routes for the JavaScript module
@if(isset($editRoutes) && !empty($editRoutes))
window.surveyRoutes = @json($editRoutes);
@else
window.surveyRoutes = {
    autoSave: '{{ route("survey.sibstr.blok4.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok4.save") }}',
    status: '{{ route("survey.sibstr.blok4.status") }}',
    backToBlok3bIndustri: '{{ route("survey.sibstr.blok3b.industri") }}',
    backToBlok3bNonIndustri: '{{ route("survey.sibstr.blok3b.nonindustri") }}',
    blok5: '{{ route("survey.sibstr.blok5") }}',
    nextBlok: '{{ route("survey.sibstr.blok5") }}'
};
@endif

// Pass KBLI prefix for back navigation decision
window.surveyData = {
    kbliPrefix: @json($kbliPrefix)
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok4.js') }}"></script>
@endpush
@endsection