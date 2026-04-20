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

        @if(isset($referenceResponse) && $referenceResponse && !(isset($triwulan) && $triwulan === 1))
        <div style="margin-top:1rem;">
            <button type="button"
                    onclick="openRefDrawer()"
                    style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.1rem;
                           border-radius:0.625rem;border:2px solid #fbbf24;
                           background:rgba(254,243,199,0.85);color:#92400e;
                           font-size:0.8125rem;font-weight:700;cursor:pointer;
                           transition:background 0.15s,border-color 0.15s,box-shadow 0.15s;
                           box-shadow:0 1px 4px rgba(251,191,36,0.25);"
                    aria-label="Buka panel data referensi untuk perbandingan">
                <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Lihat Data Referensi
            </button>
        </div>
        @endif
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
                    <label class="form-label required">
                        <span class="question-number">401.</span>
                        <span>Triwulan I (Jan–Mar): Jelaskan fenomena atau catatan</span>
                    </label>
                    <textarea name="blok4[triwulan1]" id="blok4_triwulan1" rows="4"
                              class="form-control textarea"
                              required
                              placeholder="Contoh: Lonjakan pesanan produk X karena kampanye...">{{ $surveyResponse->blok4_data['triwulan1'] ?? '' }}</textarea>
                </div>

                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">402.</span>
                        <span>Triwulan II (Apr–Jun): Jelaskan fenomena atau catatan</span>
                    </label>
                    <textarea name="blok4[triwulan2]" id="blok4_triwulan2" rows="4"
                              class="form-control textarea"
                              required
                              placeholder="Contoh: Penurunan produksi karena perawatan mesin...
">{{ $surveyResponse->blok4_data['triwulan2'] ?? '' }}</textarea>
                </div>

                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">403.</span>
                        <span>Triwulan III (Jul–Sep): Jelaskan fenomena atau catatan</span>
                    </label>
                    <textarea name="blok4[triwulan3]" id="blok4_triwulan3" rows="4"
                              class="form-control textarea"
                              required
                              placeholder="Contoh: Perubahan harga bahan baku impor...">{{ $surveyResponse->blok4_data['triwulan3'] ?? '' }}</textarea>
                </div>

                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">404.</span>
                        <span>Triwulan IV (Okt–Des): Jelaskan fenomena atau catatan</span>
                    </label>
                    <textarea name="blok4[triwulan4]" id="blok4_triwulan4" rows="4"
                              class="form-control textarea"
                              required
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
                    Kembali ke Bab 3
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
                <span>* Wajib diisi</span>
            </div>
        </div>
    </form>

    @if(isset($referenceResponse) && $referenceResponse && !(isset($triwulan) && $triwulan === 1))
    @include('survey.sibstr.partials.reference-drawer', [
        'referenceResponse' => $referenceResponse,
        'currentTwLabel'    => isset($triwulan) && $triwulan > 0
                                ? \App\Models\SurveyResponse::triwulanLabel($triwulan) . ' ' . ($tahun ?? 2025)
                                : 'Tahunan ' . ($tahun ?? 2025),
        'fields' => [
            ['name' => 'blok4_data.triwulan1', 'target' => 'blok4[triwulan1]', 'label' => '401. TW I (Jan–Mar): Fenomena/Catatan',   'copyable' => true],
            ['name' => 'blok4_data.triwulan2', 'target' => 'blok4[triwulan2]', 'label' => '402. TW II (Apr–Jun): Fenomena/Catatan',  'copyable' => true],
            ['name' => 'blok4_data.triwulan3', 'target' => 'blok4[triwulan3]', 'label' => '403. TW III (Jul–Sep): Fenomena/Catatan', 'copyable' => true],
            ['name' => 'blok4_data.triwulan4', 'target' => 'blok4[triwulan4]', 'label' => '404. TW IV (Okt–Des): Fenomena/Catatan', 'copyable' => true],
        ],
    ])
    @endif
</div>

@push('scripts')
<script>
// Auto-redirect triwulanan users — blok4 fenomena is tahunan-only
@if(($triwulan ?? 0) > 0)
window.location.replace('{{ route("survey.sibstr.blok5", ["year" => $tahun, "period" => $period]) }}');
@endif

// Set up survey routes for the JavaScript module
@if(isset($editRoutes) && !empty($editRoutes))
window.surveyRoutes = @json($editRoutes);
@else
window.surveyRoutes = {
    autoSave:               '{{ route("survey.sibstr.blok4.autosave",          ["year" => $tahun, "period" => $period]) }}',
    saveAll:                '{{ route("survey.sibstr.blok4.save",              ["year" => $tahun, "period" => $period]) }}',
    status:                 '{{ route("survey.sibstr.blok4.status",            ["year" => $tahun, "period" => $period]) }}',
    backToBlok3cIndustri:   '{{ route("survey.sibstr.blok3c.industri",         ["year" => $tahun, "period" => $period]) }}',
    backToBlok3bNonIndustri:'{{ route("survey.sibstr.blok3b.nonindustri",      ["year" => $tahun, "period" => $period]) }}',
    blok5:                  '{{ route("survey.sibstr.blok5",                   ["year" => $tahun, "period" => $period]) }}',
    nextBlok:               '{{ route("survey.sibstr.blok5",                   ["year" => $tahun, "period" => $period]) }}'
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