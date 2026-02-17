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

        <!-- Lainnya Section -->
        <div class="special-section border-l-4 border-yellow-500" id="lainnya-section">
            <h3 class="special-title">
                <span class="text-xl">302.</span> Lainnya*)
            </h3>
            <!-- Per-section Quarter Tabs -->
            <div class="quarter-tabs" id="lainnya-tabs" role="tablist" aria-label="Pilih Triwulan untuk Lainnya">
                <button type="button" class="quarter-tab active" data-quarter="dec2024">Des 2024</button>
                <button type="button" class="quarter-tab" data-quarter="q1">Triwulan I</button>
                <button type="button" class="quarter-tab" data-quarter="q2">Triwulan II</button>
                <button type="button" class="quarter-tab" data-quarter="q3">Triwulan III</button>
                <button type="button" class="quarter-tab" data-quarter="q4">Triwulan IV</button>
            </div>
            <div class="data-grid quarter-grid" id="lainnya-grid-container">
                <!-- Lainnya inputs will be injected here -->
            </div>
            <div class="mt-4 p-4 bg-gray-50 rounded-lg text-sm text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                <strong>*) Keterangan:</strong> Yang termasuk dalam lainnya atau pendapatan lainnya dalam R302 antara lain keuntungan/kerugian dari penjualan barang yang sama, menyewakan gedung/ruangan/tempat, menyewakan gudang, menyewakan kendaraan/mesin/dan peralatan (tanpa operator), pendapatan dari ongkos kirim barang, penjualan energi sampingan (listrik, steam, gas), jasa pengemasan, dan jasa perbaikan kecil.
            </div>
        </div>

        <!-- Total Section -->
        <div class="special-section border-l-4 border-green-500" id="total-section">
            <h3 class="special-title text-green-700 dark:text-green-400">
                <span class="text-xl">303.</span> Total Pendapatan
            </h3>
            <!-- Per-section Quarter Tabs -->
            <div class="quarter-tabs" id="total-tabs" role="tablist" aria-label="Pilih Triwulan untuk Total">
                <button type="button" class="quarter-tab active" data-quarter="dec2024">Des 2024</button>
                <button type="button" class="quarter-tab" data-quarter="q1">Triwulan I</button>
                <button type="button" class="quarter-tab" data-quarter="q2">Triwulan II</button>
                <button type="button" class="quarter-tab" data-quarter="q3">Triwulan III</button>
                <button type="button" class="quarter-tab" data-quarter="q4">Triwulan IV</button>
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
                    $lainnya = $surveyResponse->blok3a_lainnya ?? [];
                    $totals = $surveyResponse->blok3a_totals ?? [];
                    $months = ['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'];
                    $monthLabels = ['Des 2024', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                @endphp

                @if(count($products) > 0 || !empty($lainnya) || !empty($totals))
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
                                        <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                            {{ isset($p['banyaknya'][$m]) && $p['banyaknya'][$m] !== null ? number_format((float)$p['banyaknya'][$m], 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td style="border:1px solid #e5e7eb;">Nilai (Jutaan Rp)</td>
                                    @foreach($months as $m)
                                        <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                            {{ isset($p['nilai'][$m]) && $p['nilai'][$m] !== null ? number_format((float)$p['nilai'][$m], 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td style="border:1px solid #e5e7eb;">Harga/Satuan (Ribu Rp)</td>
                                    @foreach($months as $m)
                                        <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                            {{ isset($p['harga_satuan'][$m]) && $p['harga_satuan'][$m] !== null ? number_format((float)$p['harga_satuan'][$m], 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            @if(!empty($lainnya))
                                <tr style="background:#fefce8;">
                                    <td class="sticky-col" style="border:1px solid #e5e7eb;">
                                        <div class="code">302.</div>
                                        <div class="name">Lainnya</div>
                                    </td>
                                    <td style="border:1px solid #e5e7eb;">Nilai</td>
                                    @foreach($months as $m)
                                        <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                            {{ isset($lainnya['nilai'][$m]) && $lainnya['nilai'][$m] !== null ? number_format((float)$lainnya['nilai'][$m], 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endif

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

<!-- Scripts -->
@push('scripts')
<script>
window.surveyRoutes = @json($editRoutes ?? null) || {
    autoSave: '{{ route("survey.sibstr.blok3a.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok3a.save") }}',
    status: '{{ route("survey.sibstr.blok3a.status") }}',
    backToBlok2: '{{ route("survey.sibstr.blok2") }}',
    // Default fallback next block based on KBLI prefix (10–33 = Industri)
    nextBlok: @php
        $fallbackNext = (isset($kbliPrefix) && $kbliPrefix >= 10 && $kbliPrefix <= 33)
            ? route('survey.sibstr.blok3b.industri')
            : route('survey.sibstr.blok3b.nonindustri');
        echo "'{$fallbackNext}'";
    @endphp,
    blok6: '{{ route("survey.sibstr.blok6") }}',
    blok3b_industri: '{{ route("survey.sibstr.blok3b.industri") }}',
    blok3b_nonindustri: '{{ route("survey.sibstr.blok3b.nonindustri") }}'
};

window.surveyData = {
    products: @json($surveyResponse->blok3a_products ?? []),
    lainnya: @json($surveyResponse->blok3a_lainnya ?? []),
    totals: @json($surveyResponse->blok3a_totals ?? [])
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
