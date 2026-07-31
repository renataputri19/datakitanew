@extends('layouts.user-dashboard')

@section('title', 'SIBSTR — Blok IV: Fenomena & Catatan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/sibstr-form.css') }}">
@endpush

@section('dashboard-content')
@include('survey.sibstr.partials.page-head', [
    'blokTitle' => 'Blok IV — Fenomena & Catatan',
    'blokSub'   => 'Peristiwa penting per triwulan',
])
<div class="survey-container">
    @include('survey.sibstr.partials.blok-toolbar', [
        'instruction' => 'Jelaskan fenomena penting dan catatan per triwulan terkait perubahan yang signifikan pada kegiatan usaha.',
    ])

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
        'currentTwLabel'    => null,
        'fields' => [
            ['name' => 'blok4_data.triwulan1', 'target' => 'blok4[triwulan1]', 'label' => '401. TW I (Jan–Mar): Fenomena/Catatan',   'copyable' => false],
            ['name' => 'blok4_data.triwulan2', 'target' => 'blok4[triwulan2]', 'label' => '402. TW II (Apr–Jun): Fenomena/Catatan',  'copyable' => false],
            ['name' => 'blok4_data.triwulan3', 'target' => 'blok4[triwulan3]', 'label' => '403. TW III (Jul–Sep): Fenomena/Catatan', 'copyable' => false],
            ['name' => 'blok4_data.triwulan4', 'target' => 'blok4[triwulan4]', 'label' => '404. TW IV (Okt–Des): Fenomena/Catatan', 'copyable' => false],
        ],
    ])
    @endif
</div>
@include('survey.sibstr.partials.page-foot')

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