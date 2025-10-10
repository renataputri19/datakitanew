@extends('layouts.app')

@section('title', 'SIBSTR - Blok IIIA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-blok3a.css') }}">
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
@endpush

@section('content')
<div class="survey-container">
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
            <strong>Petunjuk:</strong> Mencatat semua pendapatan dari hasil produksi
        </p>
    </div>

    <!-- Auto-save Status -->
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    <!-- Survey Form -->
    <form id="survey-form" class="survey-form" data-aos="fade-up" data-aos-delay="200">
        @csrf

        <!-- Section IIIA: KONDISI PEREKONOMIAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK IIIA - KONDISI PEREKONOMIAN (PELAKU USAHA)</h3>
                <p class="section-subtitle">Barang-barang yang diproduksi dan pendapatan perusahaan per bulan</p>
            </div>

            <!-- Dynamic Products Table -->
            <div class="products-table-container">
                <div class="table-responsive">
                    <table class="products-table" id="products-table">
                        <thead>
                            <!-- Header Row 1: Main Headers -->
                            <tr class="header-row-1">
                                <th rowspan="3" class="col-no">No.</th>
                                <th rowspan="3" class="col-jenis">301. Jenis Barang yang dihasilkan/diproduksi</th>
                                <th rowspan="3" class="col-uraian">Uraian</th>
                                <th rowspan="3" class="col-satuan">Satuan</th>
                                <th rowspan="2" class="col-2024">2024</th>
                                <th colspan="12" class="col-2025">2025</th>
                            </tr>
                            
                            <!-- Header Row 2: Quarter Groups -->
                            <tr class="header-row-2">
                                <th colspan="3" class="quarter-header">Triwulan I</th>
                                <th colspan="3" class="quarter-header">Triwulan II</th>
                                <th colspan="3" class="quarter-header">Triwulan III</th>
                                <th colspan="3" class="quarter-header">Triwulan IV</th>
                            </tr>
                            
                            <!-- Header Row 3: Months and Column Numbers -->
                            <tr class="header-row-3">
                                <th class="month-header">Desember</th>
                                <th class="month-header">Januari</th>
                                <th class="month-header">Februari</th>
                                <th class="month-header">Maret</th>
                                <th class="month-header">April</th>
                                <th class="month-header">Mei</th>
                                <th class="month-header">Juni</th>
                                <th class="month-header">Juli</th>
                                <th class="month-header">Agustus</th>
                                <th class="month-header">September</th>
                                <th class="month-header">Oktober</th>
                                <th class="month-header">Nopember</th>
                                <th class="month-header">Desember</th>
                            </tr>
                            
                            <!-- Column Numbers Row -->
                            <tr class="column-numbers-row">
                                <td class="col-number">(1)</td>
                                <td class="col-number">(2)</td>
                                <td class="col-number">(3)</td>
                                <td class="col-number">(4)</td>
                                <td class="col-number">(5)</td>
                                <td class="col-number">(6)</td>
                                <td class="col-number">(7)</td>
                                <td class="col-number">(8)</td>
                                <td class="col-number">(9)</td>
                                <td class="col-number">(10)</td>
                                <td class="col-number">(11)</td>
                                <td class="col-number">(12)</td>
                                <td class="col-number">(13)</td>
                                <td class="col-number">(14)</td>
                                <td class="col-number">(15)</td>
                                <td class="col-number">(16)</td>
                                <td class="col-number">(17)</td>
                            </tr>
                        </thead>
                        <tbody id="products-tbody">
                            <!-- Dynamic product rows will be inserted here -->
                        </tbody>
                        <tfoot>
                            <!-- Row 302: Lainnya -->
                            <tr class="lainnya-row">
                                <td class="row-number">302.</td>
                                <td class="product-info">
                                    <strong>Lainnya*)</strong>
                                </td>
                                <td class="sub-row-label">Nilai</td>
                                <td class="sub-row-unit">Jutaan Rp</td>
                                @foreach(['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'] as $month)
                                <td>
                                    <input type="number"
                                           name="blok3a_lainnya[nilai][{{ $month }}]"
                                           value="{{ ($surveyResponse->blok3a_lainnya['nilai'][$month] ?? '') }}"
                                           class="form-control form-control-sm nilai-input lainnya-nilai"
                                           data-month="{{ $month }}"
                                           step="0.01"
                                           min="0"
                                           placeholder="">
                                </td>
                                @endforeach
                            </tr>

                            <!-- Row 303: Total -->
                            <tr class="total-row">
                                <td class="row-number">303.</td>
                                <td class="product-info"><strong>Total</strong></td>
                                <td class="sub-row-label">Nilai</td>
                                <td class="sub-row-unit">Jutaan Rp</td>
                                @foreach(['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'] as $month)
                                <td>
                                    <input type="number"
                                           name="blok3a_totals[{{ $month }}]"
                                           value="{{ ($surveyResponse->blok3a_totals[$month] ?? 0) }}"
                                           class="form-control form-control-sm total-input"
                                           data-month="{{ $month }}"
                                           readonly
                                           tabindex="-1">
                                </td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Footer Note -->
                <div class="footer-note">
                    <p><strong>*) Yang termasuk dalam lainnya atau pendapatan lainnya dalam R302 antara lain keuntungan/kerugian dari penjualan barang yang sama, menyewakan gedung/ruangan/tempat, menyewakan gudang, menyewakan kendaraan/mesin/dan peralatan (tanpa operator), pendapatan dari ongkos kirim barang, penjualan energi sampingan (listrik, steam, gas), jasa pengemasan, dan jasa perbaikan kecil</strong></p>
                </div>
                
                <!-- Add Product Button -->
                <div class="add-product-container">
                    <button type="button" id="add-product-btn" class="btn btn-outline-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah Produk
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok2" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    Kembali ke Bab 2
                </button>

                <button type="button" id="save-draft" class="btn btn-outline-primary">
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

@push('scripts')
<script>
// Set up survey routes for the JavaScript module
window.surveyRoutes = {
    autoSave: '{{ route("survey.sibstr.blok3a.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok3a.save") }}',
    status: '{{ route("survey.sibstr.blok3a.status") }}',
    backToBlok2: '{{ route("survey.sibstr.blok2") }}',
    nextBlok: '{{ route("survey.sibstr.blok6") }}'
};

// Pass existing data to JavaScript
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
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });
</script>
@endpush
@endsection
