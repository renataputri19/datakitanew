@extends('layouts.user-dashboard')

@section('title', 'SIBSTR — Blok VI: Catatan & Selesai')
@section('description', 'Survei Industri Besar dan Sedang Triwulanan - Blok VI: Catatan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/sibstr-form.css') }}">
@endpush

@section('dashboard-content')
@include('survey.sibstr.partials.page-head', [
    'blokTitle' => 'Blok VI — Catatan & Selesai',
    'blokSub'   => 'Langkah terakhir pengisian',
])
<div class="survey-container">
    @include('survey.sibstr.partials.blok-toolbar')

    <!-- Auto-save Status -->
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    {{-- Pre-flight checklist: show what is still missing before finishing --}}
    @include('survey.sibstr.partials.completeness-summary')

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
                    {{ $triwulan === 0 ? 'Simpan dan Lanjutkan' : 'Selesaikan Survei' }}
                </button>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>Catatan bersifat opsional</span>
            </div>
        </div>
    </form>

    <!-- Survey Notification Modal -->
    <div id="survey-modal-overlay"
         style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.45);
                backdrop-filter:blur(2px);align-items:center;justify-content:center;padding:1rem;">
        <div id="survey-modal-box"
             style="background:#fff;border-radius:1rem;padding:2rem 2rem 1.5rem;max-width:28rem;width:100%;
                    box-shadow:0 20px 60px rgba(0,0,0,0.25);position:relative;animation:modalIn .18s ease-out;">
            <div style="display:flex;align-items:flex-start;gap:0.875rem;margin-bottom:1.25rem;">
                <span id="survey-modal-icon" style="font-size:1.75rem;line-height:1;flex-shrink:0;margin-top:0.1rem;"></span>
                <div style="flex:1;min-width:0;">
                    <p id="survey-modal-title"
                       style="font-size:1rem;font-weight:700;color:#1f2937;margin:0 0 0.35rem;"></p>
                    <p id="survey-modal-body"
                       style="font-size:0.875rem;color:#4b5563;margin:0;line-height:1.5;"></p>
                </div>
            </div>
            <div id="survey-modal-progress-wrap" style="display:none;margin-bottom:1rem;">
                <div style="height:4px;background:#e5e7eb;border-radius:99px;overflow:hidden;">
                    <div id="survey-modal-progress-bar"
                         style="height:100%;width:100%;border-radius:99px;
                                transition:width linear;"></div>
                </div>
                <p id="survey-modal-countdown"
                   style="font-size:0.75rem;color:#9ca3af;margin:0.4rem 0 0;text-align:right;"></p>
            </div>
            <div style="display:flex;justify-content:flex-end;gap:0.5rem;">
                <button id="survey-modal-cancel"
                        style="display:none;padding:0.5rem 1rem;border-radius:0.5rem;border:1px solid #d1d5db;
                               background:#fff;color:#374151;font-size:0.875rem;font-weight:600;cursor:pointer;">
                    Batal
                </button>
                <button id="survey-modal-confirm"
                        style="padding:0.5rem 1.25rem;border-radius:0.5rem;border:none;
                               font-size:0.875rem;font-weight:700;cursor:pointer;color:#fff;transition:opacity .15s;">
                    OK
                </button>
            </div>
        </div>
    </div>
    <style>
    @keyframes modalIn {
        from { opacity:0; transform:scale(.94) translateY(8px); }
        to   { opacity:1; transform:scale(1)  translateY(0);    }
    }
    </style>

    @if(isset($referenceResponse) && $referenceResponse && !(isset($triwulan) && $triwulan === 1))
    @include('survey.sibstr.partials.reference-drawer', [
        'referenceResponse' => $referenceResponse,
        'currentTwLabel'    => null,
        'fields' => [
            ['name' => 'catatan', 'label' => '601. Catatan Tambahan', 'copyable' => false],
        ],
    ])
    @endif
</div>
@include('survey.sibstr.partials.page-foot')

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
    jaringanUnitKegiatan: @json($jaringanUnitKegiatan),
    isTahunan: @json($triwulan === 0),
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok6.js') }}"></script>
@endpush
@endsection
