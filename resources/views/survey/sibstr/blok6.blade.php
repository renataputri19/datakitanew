@extends('layouts.app')

@section('title', 'SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR) - Blok VI - DataKita')
@section('description', 'Survei Industri Besar dan Sedang Triwulanan - Blok VI: Catatan')

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
            VI. CATATAN
        </h2>
        <p class="survey-description">
            Catatan tambahan untuk survei industri besar dan sedang triwulanan
        </p>

        @if(isset($referenceResponse) && $referenceResponse)
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

    @if(session('warning'))
    <div class="autosave-status info" data-aos="fade-up">
        <span>{{ session('warning') }}</span>
    </div>
    @endif

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
                @php
                    $p6 = $tahun ?? $surveyResponse->tahun ?? 2025;
                    $r6 = $period ?? ($triwulan === 0 ? 'tahunan' : (string) ($triwulan ?? 0));
                    if (!empty($isEditMode)) {
                        $backHref = ($jaringanUnitKegiatan === 'unit_pembantu_penunjang')
                            ? route('survey.sibstr.edit.blok2', ['year' => $p6, 'period' => $r6])
                            : (($kondisiPerusahaan === 'masih_aktif')
                                ? route('survey.sibstr.edit.blok5', ['year' => $p6, 'period' => $r6])
                                : route('survey.sibstr.edit.blok2', ['year' => $p6, 'period' => $r6]));
                    } else {
                        $backHref = ($jaringanUnitKegiatan === 'unit_pembantu_penunjang')
                            ? route('survey.sibstr.blok2', ['year' => $p6, 'period' => $r6])
                            : (($kondisiPerusahaan === 'masih_aktif')
                                ? route('survey.sibstr.blok5', ['year' => $p6, 'period' => $r6])
                                : route('survey.sibstr.blok2', ['year' => $p6, 'period' => $r6]));
                    }
                @endphp
                <a href="{{ $backHref }}" id="back-to-blok5" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    {{ ($jaringanUnitKegiatan === 'unit_pembantu_penunjang') ? 'Kembali ke Bab 2' : (($kondisiPerusahaan === 'masih_aktif') ? 'Kembali ke Bab 5' : 'Kembali ke Bab 2') }}
                </a>

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
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                    Selesaikan Survei
                </button>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>Catatan bersifat opsional</span>
            </div>
        </div>
    </form>

    @if(isset($referenceResponse) && $referenceResponse)
    @include('survey.sibstr.partials.reference-drawer', [
        'referenceResponse' => $referenceResponse,
        'currentTwLabel'    => isset($triwulan) && $triwulan > 0
                                ? \App\Models\SurveyResponse::triwulanLabel($triwulan) . ' ' . ($tahun ?? 2025)
                                : 'Tahunan ' . ($tahun ?? 2025),
        'fields' => [
            ['name' => 'catatan', 'label' => '601. Catatan Tambahan', 'copyable' => true],
        ],
    ])
    @endif
</div>

@push('scripts')
<script>
// Set up survey routes for the JavaScript module
@if(isset($editRoutes) && !empty($editRoutes))
window.surveyRoutes = @json($editRoutes);
@else
window.surveyRoutes = {
    autoSave:    '{{ route("survey.sibstr.blok6.autosave", ["year" => $tahun ?? $surveyResponse->tahun ?? 2025, "period" => $period ?? ($triwulan === 0 ? "tahunan" : (string)($triwulan ?? 0))]) }}',
    saveAll:     '{{ route("survey.sibstr.blok6.save",     ["year" => $tahun ?? $surveyResponse->tahun ?? 2025, "period" => $period ?? ($triwulan === 0 ? "tahunan" : (string)($triwulan ?? 0))]) }}',
    status:      '{{ route("survey.sibstr.blok6.status",   ["year" => $tahun ?? $surveyResponse->tahun ?? 2025, "period" => $period ?? ($triwulan === 0 ? "tahunan" : (string)($triwulan ?? 0))]) }}',
    backToBlok5: '{{ route("survey.sibstr.blok5",          ["year" => $tahun ?? $surveyResponse->tahun ?? 2025, "period" => $period ?? ($triwulan === 0 ? "tahunan" : (string)($triwulan ?? 0))]) }}',
    backToBlok2: '{{ route("survey.sibstr.blok2",          ["year" => $tahun ?? $surveyResponse->tahun ?? 2025, "period" => $period ?? ($triwulan === 0 ? "tahunan" : (string)($triwulan ?? 0))]) }}',
    finishSurvey:'{{ route("survey.sibstr.blok6.finish",   ["year" => $tahun ?? $surveyResponse->tahun ?? 2025, "period" => $period ?? ($triwulan === 0 ? "tahunan" : (string)($triwulan ?? 0))]) }}'
};
@endif

// Pass kondisi_perusahaan from Blok 2 for conditional back navigation
window.surveyData = {
    kondisiPerusahaan: @json($kondisiPerusahaan),
    jaringanUnitKegiatan: @json($jaringanUnitKegiatan)
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok6.js') }}"></script>
@endpush
@endsection
