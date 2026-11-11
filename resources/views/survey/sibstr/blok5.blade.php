@extends('layouts.user-dashboard')

@section('title', 'SIBSTR — Blok V: Kondisi & Prospek Usaha')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/sibstr-form.css') }}?v={{ filemtime(public_path('css/sibstr-form.css')) }}">
<link rel="stylesheet" href="{{ asset('css/sibstr-blok5.css') }}?v={{ filemtime(public_path('css/sibstr-blok5.css')) }}">
@endpush

@section('dashboard-content')
@include('survey.sibstr.partials.page-head', [
    'blokTitle' => 'Blok V — Kondisi & Prospek Usaha',
    'blokSub'   => 'Indikator naik / tetap / turun per triwulan',
])
<div class="survey-container">
    @include('survey.sibstr.partials.blok-toolbar', [
        'instruction' => 'Pilih satu jawaban untuk setiap komponen pada setiap periode perbandingan.',
    ])

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

            @php
                $currentTw    = (int) ($triwulan ?? 0);
                $isTriwulanan = $currentTw > 0;
                $tahunInt     = (int) $tahun;
                $roman        = ['I', 'II', 'III', 'IV'];

                // One entry per answer group in a card. 'key' is the stored
                // column (blok5[501][p1]); never renumber it — saved answers
                // and the BPS views read these keys.
                if ($isTriwulanan) {
                    $prevTw   = $currentTw === 1 ? 4 : $currentTw - 1;
                    $prevYear = $currentTw === 1 ? $tahunInt - 1 : $tahunInt;
                    $nextTw   = $currentTw === 4 ? 1 : $currentTw + 1;
                    $nextYear = $currentTw === 4 ? $tahunInt + 1 : $tahunInt;
                    $twNow    = "TW {$roman[$currentTw - 1]}-{$tahunInt}";
                    $twPrev   = "TW {$roman[$prevTw - 1]}-{$prevYear}";
                    $twNext   = "TW {$roman[$nextTw - 1]}-{$nextYear}";
                    $periodDefs = [
                        ['key' => 'p1', 'prospek' => false, 'range' => "$twNow vs $twPrev"],
                        ['key' => 'p2', 'prospek' => true,  'range' => "$twNext vs $twNow"],
                    ];
                } else {
                    $y = $tahunInt;
                    $periodDefs = [
                        ['key' => 'p1', 'prospek' => false, 'range' => "TW I-$y vs TW IV-" . ($y - 1)],
                        ['key' => 'p2', 'prospek' => false, 'range' => "TW II-$y vs TW I-$y"],
                        ['key' => 'p3', 'prospek' => false, 'range' => "TW III-$y vs TW II-$y"],
                        ['key' => 'p5', 'prospek' => false, 'range' => "TW IV-$y vs TW III-$y"],
                        ['key' => 'p6', 'prospek' => true,  'range' => 'TW I-' . ($y + 1) . " vs TW IV-$y"],
                    ];
                }

                $rows = [
                    ['key' => '501', 'label' => 'Pesanan', 'type' => 'normal'],
                    ['key' => '502', 'label' => 'Produksi', 'type' => 'normal'],
                    ['key' => '503', 'label' => 'Kapasitas Produksi', 'type' => 'normal'],
                    ['key' => '504', 'label' => 'Tenaga Kerja', 'type' => 'normal'],
                    ['key' => '505', 'label' => 'Jam Kerja', 'type' => 'normal'],
                    ['key' => '506', 'label' => 'Waktu Pengiriman Pemasok', 'type' => 'delivery'],
                    ['key' => '507', 'label' => 'Persediaan Bahan Baku', 'type' => 'normal'],
                ];
                $labelsNormal   = [['value' => 'naik', 'text' => 'Naik'], ['value' => 'tetap', 'text' => 'Tetap'], ['value' => 'turun', 'text' => 'Turun']];
                $labelsDelivery = [['value' => 'lebih_cepat', 'text' => 'Lebih cepat'], ['value' => 'tetap', 'text' => 'Tetap'], ['value' => 'lebih_lambat', 'text' => 'Lebih lambat']];
                // Stroke icons (24×24) shown above each answer's text.
                $optIcons = [
                    'naik'         => '<polyline points="3 17 9 11 13 15 21 7"/><polyline points="14 7 21 7 21 14"/>',
                    'turun'        => '<polyline points="3 7 9 13 13 9 21 17"/><polyline points="14 17 21 17 21 10"/>',
                    'tetap'        => '<line x1="4" y1="12" x2="20" y2="12"/><polyline points="14 6 20 12 14 18"/>',
                    'lebih_cepat'  => '<polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/>',
                    'lebih_lambat' => '<circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/>',
                ];
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

            <div class="b5-intro">
                @if($isTriwulanan)
                    <p>Setiap komponen punya <strong>dua</strong> pertanyaan. Jawab keduanya:</p>
                    <ul class="b5-intro-list">
                        <li><span class="b5-tag">Kondisi</span><span><strong>{{ $twNow }}</strong> dibanding {{ $twPrev }}</span></li>
                        <li class="is-prospek"><span class="b5-tag">Prospek</span><span>perkiraan <strong>{{ $twNext }}</strong> dibanding {{ $twNow }}</span></li>
                    </ul>
                @else
                    <p>Setiap komponen punya lima periode: kondisi tiap triwulan {{ $tahunInt }} dibanding triwulan sebelumnya, dan prospek TW I-{{ $tahunInt + 1 }}.</p>
                @endif
            </div>

            <div class="b5-list{{ $isTriwulanan ? ' is-triwulanan' : '' }}">
                @foreach($rows as $row)
                    @php $qId = 'b5-q-' . $row['key']; @endphp
                    <div class="b5-item" data-b5-label="{{ $row['key'] }}. {{ $row['label'] }}">
                        <div class="b5-q">
                            <div class="b5-q-title" id="{{ $qId }}">
                                <span class="question-number">{{ $row['key'] }}.</span>
                                {{ $row['label'] }}@if($isTriwulanan)&nbsp;<span class="b5-req" aria-hidden="true">*</span>@endif
                            </div>
                            @if(isset($descriptions[$row['key']]))
                                <p class="b5-desc">{{ $descriptions[$row['key']] }}</p>
                            @endif
                        </div>

                        <div class="b5-periods">
                            @foreach($periodDefs as $pd)
                                @php
                                    $name   = "blok5[{$row['key']}][{$pd['key']}]";
                                    $headId = "b5-{$row['key']}-{$pd['key']}";
                                    $kind   = $pd['prospek'] ? 'Prospek' : 'Kondisi';
                                @endphp
                                <div class="b5-period{{ $pd['prospek'] ? ' is-prospek' : '' }}" data-b5-period="{{ $kind }} {{ $pd['range'] }}">
                                    <div class="b5-period-head" id="{{ $headId }}">
                                        <span class="b5-tag">{{ $kind }}</span>
                                        <span class="b5-range">{{ $pd['range'] }}</span>
                                    </div>
                                    <div class="radio-group b5-seg" role="radiogroup" aria-labelledby="{{ $qId }} {{ $headId }}"@if($isTriwulanan) aria-required="true"@endif>
                                        @foreach(($row['type'] === 'delivery' ? $labelsDelivery : $labelsNormal) as $opt)
                                            @php $checked = isset($data[$row['key']][$pd['key']]) && $data[$row['key']][$pd['key']] === $opt['value']; @endphp
                                            <label class="b5-opt">
                                                <input type="radio" class="b5-opt-input" name="{{ $name }}" value="{{ $opt['value'] }}" {{ $checked ? 'checked' : '' }}{{ $isTriwulanan ? ' required' : '' }}>
                                                <span class="b5-opt-face">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $optIcons[$opt['value']] !!}</svg>
                                                    <span>{{ $opt['text'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok4" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    {{ ($triwulan ?? 0) > 0 ? 'Kembali ke Bab 3' : 'Kembali ke Bab 4' }}
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
                <span>{{ $isTriwulanan ? '* Semua pilihan wajib diisi.' : 'Opsional, isi sesuai kondisi yang diketahui.' }}</span>
            </div>
        </div>
    </form>

    @if(isset($referenceResponse) && $referenceResponse && !(isset($triwulan) && $triwulan === 1))
    @php
        // Blok 5 rows (questions 501–507).
        $b5rows = [
            '501' => 'Pesanan', '502' => 'Produksi', '503' => 'Kapasitas Produksi',
            '504' => 'Tenaga Kerja', '505' => 'Jam Kerja',
            '506' => 'Waktu Pengiriman Pemasok', '507' => 'Persediaan Bahan Baku',
        ];
        $b5fields = [];
        if (($triwulan ?? 0) > 0) {
            // Triwulanan: the previous quarter stored only p1 (Kondisi/realisasi) and
            // p2 (Prospek — its forecast for the quarter now being filled).
            foreach ($b5rows as $k => $lbl) {
                $b5fields[] = ['name' => "blok5_data.$k.p1", 'label' => "$k. $lbl — Kondisi (realisasi triwulan lalu)",    'copyable' => false];
                $b5fields[] = ['name' => "blok5_data.$k.p2", 'label' => "$k. $lbl — Prospek (perkiraan utk triwulan ini)", 'copyable' => false];
            }
        } else {
            // Tahunan: quarter-over-quarter columns p1–p5.
            $b5cols = ['p1' => 'TW I vs TW IV', 'p2' => 'TW II vs TW I', 'p3' => 'TW III vs TW II', 'p5' => 'TW IV vs TW III'];
            foreach ($b5rows as $k => $lbl) {
                foreach ($b5cols as $pk => $plabel) {
                    $b5fields[] = ['name' => "blok5_data.$k.$pk", 'label' => "$k. $lbl — $plabel", 'copyable' => false];
                }
            }
        }
    @endphp
    @include('survey.sibstr.partials.reference-drawer', [
        'referenceResponse' => $referenceResponse,
        'currentTwLabel'    => null,
        'fields'            => $b5fields,
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
    autoSave:                '{{ route("survey.sibstr.blok5.autosave", ["year" => $tahun, "period" => $period]) }}',
    saveAll:                 '{{ route("survey.sibstr.blok5.save",     ["year" => $tahun, "period" => $period]) }}',
    status:                  '{{ route("survey.sibstr.blok5.status",   ["year" => $tahun, "period" => $period]) }}',
    backToBlok4:             '{{ route("survey.sibstr.blok4",              ["year" => $tahun, "period" => $period]) }}',
    backToBlok3bIndustri:    '{{ route("survey.sibstr.blok3b.industri",    ["year" => $tahun, "period" => $period]) }}',
    backToBlok3bNonIndustri: '{{ route("survey.sibstr.blok3b.nonindustri", ["year" => $tahun, "period" => $period]) }}',
    blok6:                   '{{ route("survey.sibstr.blok6",              ["year" => $tahun, "period" => $period]) }}',
    nextBlok:                '{{ route("survey.sibstr.blok6",              ["year" => $tahun, "period" => $period]) }}'
};
window.surveyData = { isTriwulanan: {{ ($triwulan ?? 0) > 0 ? 'true' : 'false' }}, kbliPrefix: @json($kbliPrefix ?? '') };
@endif
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok5.js') }}?v={{ filemtime(public_path('js/survey-blok5.js')) }}"></script>
@endpush
@endsection