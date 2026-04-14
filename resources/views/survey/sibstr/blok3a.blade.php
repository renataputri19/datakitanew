@extends('layouts.app')

@section('title', 'SIBSTR - Blok IIIA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-blok3a.css') }}">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
            BLOK IIIA. KONDISI PEREKONOMIAN (PELAKU USAHA)
        </h2>
        <p class="survey-description">
            Barang-barang yang diproduksi dan pendapatan perusahaan per bulan
        </p>
        <p class="survey-instruction">
            <strong>Petunjuk:</strong> Mencatat semua pendapatan dari hasil produksi. Klik tombol "Tambah Produk" untuk menambahkan jenis barang.
        </p>

        @if(!empty($historicalResponses) && $historicalResponses->isNotEmpty())
        <div style="margin-top:1rem;">
            <button type="button"
                    onclick="openHistDrawer()"
                    style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.1rem;
                           border-radius:0.625rem;border:2px solid #fbbf24;
                           background:rgba(254,243,199,0.85);color:#92400e;
                           font-size:0.8125rem;font-weight:700;cursor:pointer;
                           transition:background 0.15s,border-color 0.15s,box-shadow 0.15s;
                           box-shadow:0 1px 4px rgba(251,191,36,0.25);"
                    aria-label="Buka panel data historis untuk referensi">
                <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Lihat Data Historis
                <span style="display:inline-flex;align-items:center;justify-content:center;width:1.2rem;height:1.2rem;
                             border-radius:9999px;background:#fbbf24;color:#7c2d12;font-size:0.7rem;font-weight:800;">
                    {{ $historicalResponses->count() }}
                </span>
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

        <!-- Global quarter tabs removed: per-card tabs will be used -->

        <!-- Dynamic Product Cards Container -->
        <div id="products-container" class="products-container">
            <!-- Cards will be injected here by JS -->
        </div>

        <!-- Footnotes for Section 301 -->
        <div style="margin-bottom:1.25rem;padding:1rem 1.25rem;background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.625rem;font-size:0.8125rem;color:#1e40af;line-height:1.6;">
            <p><strong>Catatan:</strong> Bila satuan yang digunakan tidak standar seperti &apos;botol&apos; atau &apos;kaleng&apos;, agar dikonversikan ke satuan metrik seperti liter, M3, dsb.</p>
            <p style="margin-top:0.4rem;"><strong>(*)</strong> Termasuk yang diekspor oleh eksportir umum atau pihak lain.</p>
            <p style="margin-top:0.4rem;"><strong>(**)</strong> Jika negara tujuan ekspor lebih dari satu, tuliskan negara tujuan ekspor dengan nilai terbesar.</p>
        </div>

        <!-- Add Product Button -->
        <div class="add-product-container">
            <button type="button" id="add-product-btn" class="btn-add">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah Produk
            </button>
        </div>

        <!-- Total Section -->
        <div class="special-section border-l-4 border-green-500" id="total-section">
            <h3 class="special-title text-green-700 dark:text-green-400">
                <span class="text-xl">303.</span> Total Pendapatan
            </h3>
            <!-- Per-section Quarter Tabs (dynamic based on tahun/triwulan) -->
            @php
                $currentTw  = $triwulan ?? 0;
                $curYear    = $tahun ?? 2025;
                $prevYear   = $curYear - 1;
                $twMonthMap = [
                    1 => ['key' => 'q1', 'label' => 'Triwulan I',   'months' => ["{$curYear}_jan", "{$curYear}_feb", "{$curYear}_mar"]],
                    2 => ['key' => 'q2', 'label' => 'Triwulan II',  'months' => ["{$curYear}_apr", "{$curYear}_mei", "{$curYear}_jun"]],
                    3 => ['key' => 'q3', 'label' => 'Triwulan III', 'months' => ["{$curYear}_jul", "{$curYear}_agu", "{$curYear}_sep"]],
                    4 => ['key' => 'q4', 'label' => 'Triwulan IV',  'months' => ["{$curYear}_okt", "{$curYear}_nov", "{$curYear}_des"]],
                ];
                $quarterConf = ['dec_prev' => ['label' => "Des {$prevYear}", 'months' => ["{$prevYear}_des"]]];
                if ($currentTw > 0) {
                    // Triwulanan: show only previous Dec + current quarter
                    $quarterConf[$twMonthMap[$currentTw]['key']] = $twMonthMap[$currentTw];
                } else {
                    // Tahunan: all 4 quarters
                    foreach ($twMonthMap as $tw) {
                        $quarterConf[$tw['key']] = $tw;
                    }
                }
                $firstQKey = array_key_first($quarterConf);
            @endphp
            <div class="quarter-tabs" id="total-tabs" role="tablist" aria-label="Pilih Triwulan untuk Total">
                @foreach($quarterConf as $qKey => $qConf)
                <button type="button" class="quarter-tab {{ $loop->first ? 'active' : '' }}" data-quarter="{{ $qKey }}">{{ $qConf['label'] }}</button>
                @endforeach
            </div>
            <div class="data-grid quarter-grid" id="total-grid-container">
                <!-- Total inputs will be injected here -->
            </div>
        </div>

        <!-- Excel-Style Preview (Read-Only) -->
        <div class="special-section" id="preview-section">
            <h3 class="special-title">
                Pratinjau Excel (Ringkasan Baca-Saja)
            </h3>
            <div id="blok3a-preview-table">
                @php
                    $products = $surveyResponse->blok3a_products ?? [];
                    $totals = $surveyResponse->blok3a_totals ?? [];
                    // Build preview months/labels from dynamic quarterConf
                    $monthNameMap = ['jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei',
                                     'jun'=>'Jun','jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt',
                                     'nov'=>'Nov','des'=>'Des'];
                    $months = [];
                    $monthLabels = [];
                    foreach ($quarterConf as $qConf) {
                        foreach ($qConf['months'] as $mKey) {
                            $months[] = $mKey;
                            preg_match('/^(\d{4})_(\w+)$/', $mKey, $mm);
                            $moLabel = $monthNameMap[$mm[2] ?? ''] ?? strtoupper($mm[2] ?? $mKey);
                            // Show year for the Dec baseline and for all triwulanan columns
                            $monthLabels[] = ($mm[2] === 'des' || $currentTw > 0)
                                ? $moLabel . ' ' . ($mm[1] ?? '')
                                : $moLabel;
                        }
                    }
                @endphp

                @if(count($products) > 0 || !empty($totals))
                <div class="table-responsive" style="overflow-x:auto; padding: 0.5rem 0;">
                    <table class="preview-table-el" style="width:100%; border-collapse: collapse; min-width: 980px;">
                        <thead>
                            <tr>
                                <th class="sticky-col" style="text-align:left; background:#f9fafb; border:1px solid #e5e7eb;">Kode/Nama</th>
                                <th style="background:#f9fafb; border:1px solid #e5e7eb;">Uraian</th>
                                @foreach($monthLabels as $ml)
                                    <th style="text-align:center; background:#f9fafb; border:1px solid #e5e7eb;">{{ $ml }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $i => $p)
                                @php
                                    $productName = $p['jenis_barang'] ?? ($p['name'] ?? ('Produk ' . ($i+1)));
                                @endphp
                                <tr>
                                    <td class="sticky-col" rowspan="3" style="border:1px solid #e5e7eb;">
                                        <div class="code">{{ '301.'.($i+1) }}</div>
                                        <div class="name">{{ $productName }}</div>
                                    </td>
                                    <td style="border:1px solid #e5e7eb;">Banyaknya</td>
                                    @foreach($months as $m)
                                        @php
                                            $qty = $p['banyaknya'][$m] ?? null;
                                            $unit = $p['satuan'] ?? '';
                                            $qtyText = ($qty !== null) ? number_format((float)$qty, 0, ',', '.') : null;
                                        @endphp
                                        <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                            {{ $qtyText !== null ? ($qtyText . ($unit ? ' ' . e($unit) : '')) : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td style="border:1px solid #e5e7eb;">Nilai</td>
                                    @foreach($months as $m)
                                        <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                            {{ isset($p['nilai'][$m]) && $p['nilai'][$m] !== null ? number_format((float)$p['nilai'][$m], 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td style="border:1px solid #e5e7eb;">Harga/Satuan</td>
                                    @foreach($months as $m)
                                        @php
                                            $qty = $p['banyaknya'][$m] ?? null;
                                            $nilai = $p['nilai'][$m] ?? null;
                                            $computed = ($qty !== null && (float)$qty > 0 && $nilai !== null) ? ((float)$nilai / (float)$qty) : null;
                                            $price = $computed ?? ($p['harga_satuan'][$m] ?? null);
                                        @endphp
                                        <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                            {{ $price !== null ? number_format((float)$price, 2, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @if(!empty($totals))
                                <tr class="total-row" style="background:#f0fdf4; font-weight:600;">
                                    <td class="sticky-col" style="border:1px solid #e5e7eb;">
                                        <div class="code">303.</div>
                                        <div class="name">Total</div>
                                    </td>
                                    <td style="border:1px solid #e5e7eb;">Nilai</td>
                                    @foreach($months as $m)
                                        <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                            {{ isset($totals[$m]) && $totals[$m] !== null ? number_format((float)$totals[$m], 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @else
                    <div style="text-align:center; padding: 1rem; color:#6b7280;">Belum ada data untuk ditampilkan.</div>
                @endif
            </div>
            <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                Ringkasan ini tidak dapat diedit. Untuk mengubah, silakan isi kartu di atas.
            </div>
        </div>

        <!-- Questions 302, 305, 306 — unified section matching Blok 2 layout -->
        @php $pl = $surveyResponse->blok3a_pendapatan_lainnya ?? []; @endphp
        @if(($triwulan ?? 0) == 0)
        <div class="form-section" id="pendapatan-section">
            <div class="section-header">
                <h3 class="section-title">Pendapatan Lainnya &amp; Jasa Industri</h3>
            </div>
            <div class="form-grid">

                <!-- 302 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">302.</span>
                        <span>Pendapatan lainnya selama tahun 2025
                            <small class="form-hint-inline">Isi nilai dalam rupiah. Isi 0 jika tidak ada.</small>
                        </span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q302a">a. Keuntungan/kerugian penjualan barang dalam bentuk yang sama (tanpa proses lebih lanjut)</label>
                            <div>
                                <input type="number" id="q302a" name="blok3a_pendapatan_lainnya[q302a]" class="form-control" min="0" step="1" value="{{ $pl['q302a'] ?? '' }}" placeholder="0" required>
                                <span class="field-error-message" id="err-q302a" style="display:none;">Bidang ini wajib diisi. Isi 0 jika tidak ada.</span>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q302b">b. Penjualan kekayaan intelektual (Paten, Merk, Hak cipta, Desain industri)</label>
                            <div>
                                <input type="number" id="q302b" name="blok3a_pendapatan_lainnya[q302b]" class="form-control" min="0" step="1" value="{{ $pl['q302b'] ?? '' }}" placeholder="0" required>
                                <span class="field-error-message" id="err-q302b" style="display:none;">Bidang ini wajib diisi. Isi 0 jika tidak ada.</span>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q302c">c. Nilai jasa yang tidak berkaitan dengan proses produksi</label>
                            <div>
                                <input type="number" id="q302c" name="blok3a_pendapatan_lainnya[q302c]" class="form-control" min="0" step="1" value="{{ $pl['q302c'] ?? '' }}" placeholder="0" required>
                                <span class="field-error-message" id="err-q302c" style="display:none;">Bidang ini wajib diisi. Isi 0 jika tidak ada.</span>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q302d">d. Tenaga listrik yang dijual</label>
                            <div>
                                <input type="number" id="q302d" name="blok3a_pendapatan_lainnya[q302d]" class="form-control" min="0" step="1" value="{{ $pl['q302d'] ?? '' }}" placeholder="0" required>
                                <span class="field-error-message" id="err-q302d" style="display:none;">Bidang ini wajib diisi. Isi 0 jika tidak ada.</span>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q302e">e. Pendapatan non operasional (Laba/dividen yang diterima, bunga atas simpanan dan piutang, pendapatan dari sewa lahan, klaim asuransi kerugian yang diterima)</label>
                            <div>
                                <input type="number" id="q302e" name="blok3a_pendapatan_lainnya[q302e]" class="form-control" min="0" step="1" value="{{ $pl['q302e'] ?? '' }}" placeholder="0" required>
                                <span class="field-error-message" id="err-q302e" style="display:none;">Bidang ini wajib diisi. Isi 0 jika tidak ada.</span>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q302f">f. Lainnya</label>
                            <div>
                                <input type="number" id="q302f" name="blok3a_pendapatan_lainnya[q302f]" class="form-control" min="0" step="1" value="{{ $pl['q302f'] ?? '' }}" placeholder="0" required>
                                <span class="field-error-message" id="err-q302f" style="display:none;">Bidang ini wajib diisi. Isi 0 jika tidak ada.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 305 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">305.</span>
                        <span>Pendapatan dari jasa industri (maklun) selama tahun 2025</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q305a_maklun_nilai">a. Nilai pendapatan dari jasa industri (maklun)</label>
                            <div>
                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <span class="input-prefix">Rp</span>
                                    <input type="number" id="q305a_maklun_nilai" name="blok3a_q305a_maklun_nilai"
                                           class="form-control" min="0" step="1"
                                           value="{{ $surveyResponse->blok3a_q305a_maklun_nilai ?? '' }}"
                                           placeholder="0" required>
                                </div>
                                <small class="form-hint-inline">Isi nilai dalam rupiah (bilangan bulat).</small>
                                <span class="field-error-message" id="err-q305a_maklun_nilai" style="display:none;">Bidang ini wajib diisi.</span>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q305b_maklun_pct">b. Persentase nilai pendapatan dari jasa industri (maklun) luar negeri</label>
                            <div>
                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                    <input type="number" id="q305b_maklun_pct" name="blok3a_q305b_maklun_pct"
                                           class="form-control" min="0" max="100" step="0.01"
                                           value="{{ $surveyResponse->blok3a_q305b_maklun_pct ?? '' }}"
                                           placeholder="0" required>
                                    <span class="input-prefix">%</span>
                                </div>
                                <small class="form-hint-inline">Maksimal 100%.</small>
                                <span class="field-error-message" id="err-q305b_maklun_pct" style="display:none;">Bidang ini wajib diisi.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 306 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">306.</span>
                        <span>Berapa persentase pendapatan yang diperoleh dari usaha online (%)</span>
                    </label>
                    <div>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <input type="number" id="q305_online" name="blok3a_q305_online"
                                   class="form-control" min="0" max="100" step="0.01"
                                   value="{{ $surveyResponse->blok3a_q305_online ?? '' }}"
                                   placeholder="0" required>
                            <span class="input-prefix">%</span>
                        </div>
                        <small class="form-hint-inline">Maksimal 100%.</small>
                        <span class="field-error-message" id="err-q305_online" style="display:none;">Bidang ini wajib diisi.</span>
                    </div>
                </div>

            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="form-actions mt-8">
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
</div>

@if(!empty($historicalResponses))
@include('survey.sibstr.partials.historical-drawer', [
    'historicalResponses' => $historicalResponses,
    'blockKey'            => 'blok3a',
])
@endif

<!-- Scripts -->
@push('scripts')
<script>
@if(isset($editRoutes) && !empty($editRoutes))
window.surveyRoutes = @json($editRoutes);
@else
window.surveyRoutes = {
    autoSave:    '{{ route("survey.sibstr.blok3a.autosave",      ["year" => $tahun, "period" => $period]) }}',
    saveAll:     '{{ route("survey.sibstr.blok3a.save",          ["year" => $tahun, "period" => $period]) }}',
    status:      '{{ route("survey.sibstr.blok3a.status",        ["year" => $tahun, "period" => $period]) }}',
    backToBlok2: '{{ route("survey.sibstr.blok2",                ["year" => $tahun, "period" => $period]) }}',
    nextBlok: @php
        $blok3aNext = (isset($kbliPrefix) && $kbliPrefix >= 10 && $kbliPrefix <= 33)
            ? route('survey.sibstr.blok3b.industri',   ['year' => $tahun, 'period' => $period])
            : route('survey.sibstr.blok3b.nonindustri',['year' => $tahun, 'period' => $period]);
        echo "'{$blok3aNext}'";
    @endphp,
    blok6:              '{{ route("survey.sibstr.blok6",                ["year" => $tahun, "period" => $period]) }}',
    blok3b_industri:    '{{ route("survey.sibstr.blok3b.industri",      ["year" => $tahun, "period" => $period]) }}',
    blok3b_nonindustri: '{{ route("survey.sibstr.blok3b.nonindustri",   ["year" => $tahun, "period" => $period]) }}'
};
@endif

window.surveyData = {
    products: @json($surveyResponse->blok3a_products ?? []),
    totals: @json($surveyResponse->blok3a_totals ?? []),
    quarterConf: @json($quarterConf),
    firstQuarter: '{{ $firstQKey }}'
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok3a.js') }}"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, easing: 'ease-in-out', once: true });
</script>
@endpush
@endsection
