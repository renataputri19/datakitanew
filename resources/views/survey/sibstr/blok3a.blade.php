@extends('layouts.user-dashboard')

@section('title', 'SIBSTR — Blok IIIA: Barang yang Diproduksi')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-blok3a.css') }}">
<link rel="stylesheet" href="{{ asset('css/sibstr-form.css') }}">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
.ekspor-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 0.5rem;
    align-items: end;
    margin-bottom: 0.5rem;
}
@media (max-width: 600px) {
    .ekspor-row {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        padding: 0.6rem 3rem 0.6rem 0.6rem;
        margin-bottom: 0.5rem;
        background: #f9fafb;
        border: 1px solid #d1fae5;
        border-radius: 0.5rem;
    }
    .ekspor-row > div:last-child {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        padding-bottom: 0 !important;
    }
}
.delete-overlay {
    display: none; position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.45); backdrop-filter: blur(4px);
    align-items: center; justify-content: center;
}
.delete-overlay.active { display: flex; }
.delete-modal-card {
    background: #fff; border-radius: 1rem; padding: 2rem 2rem 1.75rem;
    max-width: 380px; width: 92%; text-align: center;
    box-shadow: 0 24px 64px rgba(0,0,0,0.25);
    animation: popIn 0.22s ease-out;
}
.dark .delete-modal-card { background: #1f2937; }
@keyframes popIn {
    from { opacity:0; transform:scale(0.85) translateY(12px); }
    to   { opacity:1; transform:scale(1) translateY(0); }
}
.delete-modal-icon {
    width: 56px; height: 56px; background: #fee2e2; border-radius: 50%;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;
}
.delete-modal-card h3 { font-size: 1.125rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem; }
.dark .delete-modal-card h3 { color: #f9fafb; }
.delete-modal-card p { color: #6b7280; font-size: 0.875rem; margin: 0 0 1.5rem; line-height: 1.6; }
.delete-modal-actions { display: flex; gap: 0.75rem; justify-content: center; }
.btn-cancel-del {
    padding: 0.55rem 1.4rem; border-radius: 0.5rem;
    border: 1px solid #d1d5db; background: #f9fafb; color: #374151;
    font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s;
}
.btn-cancel-del:hover { background: #f3f4f6; }
.btn-confirm-del {
    padding: 0.55rem 1.4rem; border-radius: 0.5rem;
    border: none; background: #dc2626; color: #fff;
    font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: background 0.15s, transform 0.1s;
}
.btn-confirm-del:hover { background: #b91c1c; }
.btn-confirm-del:active { transform: scale(0.97); }
</style>
@endpush

@section('dashboard-content')
{{-- ══ Delete Confirmation Modal ══════════════════════════════ --}}
<div id="delete-confirm-overlay" class="delete-overlay" role="dialog" aria-modal="true" aria-labelledby="del-modal-title">
    <div class="delete-modal-card">
        <div class="delete-modal-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
        <h3 id="del-modal-title">Konfirmasi Aksi</h3>
        <p id="del-modal-desc">Produk ini akan dihapus secara permanen dari daftar.</p>
        <div class="delete-modal-actions">
            <button type="button" id="delete-cancel-btn" class="btn-cancel-del">Batal</button>
            <button type="button" id="delete-confirm-btn" class="btn-confirm-del">Ya, Konfirmasi</button>
        </div>
    </div>
</div>

@include('survey.sibstr.partials.page-head', [
    'blokTitle' => 'Blok IIIA — Barang yang Diproduksi',
    'blokSub'   => 'Produksi & pendapatan per bulan',
])
<div class="survey-container">
    @include('survey.sibstr.partials.blok-toolbar', [
        'instruction' => '<strong>Petunjuk:</strong> Catat semua pendapatan dari hasil produksi. Gunakan tombol <strong>"Tambah Produk"</strong> untuk menambahkan jenis barang.',
    ])

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
                $quarterConf[$twMonthMap[$currentTw]['key']] = $twMonthMap[$currentTw];
            } else {
                foreach ($twMonthMap as $tw) {
                    $quarterConf[$tw['key']] = $tw;
                }
            }
            $firstQKey = array_key_first($quarterConf);
        @endphp

        @if($currentTw > 0)
        {{-- 302. Pendapatan Lainnya — triwulanan only --}}
        <div class="special-section" id="lainnya-section">
            <h3 class="special-title">
                <span class="text-xl">302.</span> Pendapatan Lainnya
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 italic mt-1 mb-3">*) Yang termasuk dalam lainnya atau pendapatan lainnya dalam R302 antara lain keuntungan/kerugian dari penjualan barang yang sama, menyewakan gedung/ruangan/tempat, menyewakan gudang, menyewakan kendaraan/mesin/dan peralatan (tanpa operator), pendapatan dari ongkos kirim barang, penjualan energi sampingan (listrik, steam, gas), jasa pengemasan, dan jasa perbaikan kecil</p>
            <div class="quarter-tabs" id="lainnya-tabs" role="tablist" aria-label="Pilih Triwulan untuk Lainnya">
                @foreach($quarterConf as $qKey => $qConf)
                <button type="button" class="quarter-tab {{ $loop->first ? 'active' : '' }}" data-quarter="{{ $qKey }}">{{ $qConf['label'] }}</button>
                @endforeach
            </div>
            <div id="lainnya-grid-container">
                {{-- Inputs injected by JS --}}
            </div>
        </div>
        @endif

        <!-- Total Section -->
        <div class="special-section border-l-4 border-green-500" id="total-section">
            <h3 class="special-title text-green-700 dark:text-green-400">
                <span class="text-xl">303.</span> Total Pendapatan
            </h3>
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
                Ringkasan Data Produksi
                <span style="font-size:0.75rem; font-weight:400; color:#6b7280; margin-left:0.5rem;">(Pratinjau - diperbarui secara otomatis)</span>
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
                                <th style="text-align:left; background:#f9fafb; border:1px solid #e5e7eb; min-width:170px;">Detail Produk</th>
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
                                    <td rowspan="3" style="border:1px solid #e5e7eb; vertical-align:top; padding:0.5rem 0.625rem; min-width:170px; font-size:0.8125rem; line-height:1.5;">
                                        <div style="margin-bottom:0.4rem;">
                                            <span style="font-weight:600; color:#374151; display:block;">KBLI 5 Digit</span>
                                            <span style="color:#1f2937;">{{ !empty($p['kbli_5digit']) ? e($p['kbli_5digit']) : '-' }}</span>
                                        </div>
                                        <div style="margin-bottom:0.4rem;">
                                            <span style="font-weight:600; color:#374151; display:block;">Persentase Diekspor (*)</span>
                                            <span style="color:#1f2937;">{{ (isset($p['persen_ekspor']) && $p['persen_ekspor'] !== '') ? number_format((float)$p['persen_ekspor'], 2, ',', '.') . ' %' : '-' }}</span>
                                        </div>
                                        <div>
                                            <span style="font-weight:600; color:#374151; display:block;">Negara Tujuan Ekspor (**)</span>
                                            <span style="color:#1f2937;">{{ !empty($p['negara_ekspor']) ? e($p['negara_ekspor']) : '-' }}</span>
                                        </div>
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
                                    <td style="border:1px solid #e5e7eb;"></td>
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

@if(!empty($historicalResponses) && !(isset($triwulan) && $triwulan === 1))
@include('survey.sibstr.partials.historical-drawer', [
    'historicalResponses' => $historicalResponses,
    'blockKey'            => 'blok3a',
])
@endif
</div>
@include('survey.sibstr.partials.page-foot')

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
    lainnya: @json($surveyResponse->blok3a_lainnya ?? []),
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
