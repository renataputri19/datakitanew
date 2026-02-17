@extends('layouts.app')

@section('title', 'SIBSTR - Blok V Kondisi dan Prospek Usaha')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<style>
    /* Minor responsive helpers specific to Blok 5 */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .survey-table { width: 100%; border-collapse: collapse; min-width: 920px; }
    .survey-table th, .survey-table td { border: 1px solid var(--gray-200); padding: 12px; text-align: center; vertical-align: middle; }
    .survey-table th { position: sticky; top: 0; background: var(--card-bg, #fff); z-index: 2; }
    .row-label { text-align: left; font-weight: 600; }
    .row-label .component-desc { display: block; margin-top: 6px; font-weight: 400; color: var(--gray-600); font-size: var(--font-size-sm); line-height: 1.4; }
    .radio-group { display: flex; gap: 10px; justify-content: center; align-items: center; flex-wrap: wrap; }
    .radio-pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 999px; cursor: pointer; user-select: none; }
    .radio-pill input[type="radio"] { width: 18px; height: 18px; }
    .table-caption { text-align: left; margin-bottom: 12px; color: var(--muted-text, #6b7280); white-space: nowrap; }
    .sticky-col { position: sticky; left: 0; background: var(--card-bg, #fff); z-index: 1; }
    .help-details { margin-top: 8px; }
    .help-details summary { cursor: pointer; }
    /* Distinguish Prospect columns */
    .survey-table th.prospect { background: var(--info-blue-light); color: var(--gray-900); }
    .survey-table td.prospect-col { background: #f7fbff; }
    .col-subtype { font-size: var(--font-size-sm); color: var(--gray-600); }
    @media (max-width: 768px) { .survey-table th, .survey-table td { padding: 10px; } }
</style>
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
            BLOK V. KONDISI DAN PROSPEK USAHA
        </h2>
        <p class="survey-description">
            Isi indikator kondisi dan prospek usaha per triwulan.
        </p>
    </div>

    <!-- Auto-save Status -->
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    <!-- Survey Form -->
    <form id="survey-form" class="survey-form" data-aos="fade-up" data-aos-delay="200">
        @csrf

        <!-- BLOK V: Kondisi dan Prospek Usaha -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK V - Kondisi dan Prospek Usaha</h3>
                <p class="section-subtitle">Isi pilihan untuk setiap periode waktu.</p>
            </div>

            <p class="table-caption">Gunakan geser horizontal pada tabel.</p>
            <div class="table-responsive">
                <table class="survey-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Komponen</th>
                            <th>Kondisi TW I-2025 vs TW IV-2024</th>
                            <th>Kondisi TW II-2025 vs TW I-2025</th>
                            <th>Kondisi TW III-2025 vs TW II-2025</th>
                            <th class="prospect">Prospek TW IV-2025 vs TW III-2025</th>
                            <th>Kondisi TW IV-2025 vs TW III-2025</th>
                            <th class="prospect">Prospek TW I-2026 vs TW IV-2025</th>
                        </tr>
                        <tr>
                            <th class="sticky-col"></th>
                            <th class="col-subtype">Triwulan</th>
                            <th class="col-subtype">Triwulan</th>
                            <th class="col-subtype">Triwulan</th>
                            <th class="col-subtype prospect">Prospek</th>
                            <th class="col-subtype">Triwulan</th>
                            <th class="col-subtype prospect">Prospek</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rows = [
                                ['key' => '501', 'label' => 'Pesanan', 'type' => 'normal'],
                                ['key' => '502', 'label' => 'Produksi', 'type' => 'normal'],
                                ['key' => '503', 'label' => 'Kapasitas Produksi', 'type' => 'normal'],
                                ['key' => '504', 'label' => 'Tenaga Kerja', 'type' => 'normal'],
                                ['key' => '505', 'label' => 'Jam Kerja', 'type' => 'normal'],
                                ['key' => '506', 'label' => 'Waktu Pengiriman Pemasok', 'type' => 'delivery'],
                                ['key' => '507', 'label' => 'Persediaan Bahan Baku', 'type' => 'normal'],
                            ];
                            $periods = ['p1','p2','p3','p4','p5','p6'];
                            $labelsNormal = [ ['value'=>'naik','text'=>'Naik'], ['value'=>'tetap','text'=>'Tetap'], ['value'=>'turun','text'=>'Turun'] ];
                            $labelsDelivery = [ ['value'=>'lebih_cepat','text'=>'Lebih cepat'], ['value'=>'tetap','text'=>'Tetap'], ['value'=>'lebih_lambat','text'=>'Lebih lambat'] ];
                            $data = $surveyResponse->blok5_data ?? [];
                            $descriptions = [
                                '501' => 'Jumlah pesanan barang produksi yang diterima perusahaan baik domestik dan ekspor',
                                '502' => 'Jumlah produksi barang yang dihasilkan oleh perusahaan',
                                '503' => 'Besaran keluaran (output produksi) maksimum yang mampu dihasilkan oleh mesin produksi utama dalam rentang waktu tertentu',
                                '504' => 'Rata-rata jumlah tenaga kerja',
                                '505' => 'Rata-rata jam kerja per hari',
                                '506' => 'Waktu pengiriman bahan baku dari pemasok',
                                '507' => 'Jumlah persediaan bahan baku yang disimpan perusahaan',
                            ];
                        @endphp

                        @foreach($rows as $row)
                            <tr>
                                <td class="row-label sticky-col">
                                    <span class="question-number">{{ $row['key'] }}.</span>
                                    <span>{{ $row['label'] }}</span>
                                    @if(isset($descriptions[$row['key']]))
                                        <small class="component-desc">{{ $descriptions[$row['key']] }}</small>
                                    @endif
                                </td>
                                @foreach($periods as $index => $period)
                                    @php $isProspect = in_array($index, [3,5]); @endphp
                                    <td class="{{ $isProspect ? 'prospect-col' : '' }}">
                                        <div class="radio-group">
                                            @foreach(($row['type']==='delivery' ? $labelsDelivery : $labelsNormal) as $opt)
                                                @php
                                                    $name = "blok5[{$row['key']}][$period]";
                                                    $checked = isset($data[$row['key']][$period]) && $data[$row['key']][$period] === $opt['value'];
                                                @endphp
                                                <label class="radio-pill">
                                                    <input type="radio" name="{{ $name }}" value="{{ $opt['value'] }}" {{ $checked ? 'checked' : '' }}>
                                                    <span>{{ $opt['text'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok4" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    Kembali ke Bab 4
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
                <span>Pastikan semua pilihan diisi sesuai kondisi/prospek perusahaan.</span>
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
    autoSave: '{{ route("survey.sibstr.blok5.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok5.save") }}',
    status: '{{ route("survey.sibstr.blok5.status") }}',
    backToBlok4: '{{ route("survey.sibstr.blok4") }}',
    // Explicit route key used by SurveyManager when result.next_block === 'blok6'
    blok6: '{{ route("survey.sibstr.blok6") }}',
    // Fallback default next block
    nextBlok: '{{ route("survey.sibstr.blok6") }}'
};
@endif
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok5.js') }}"></script>
@endpush
@endsection