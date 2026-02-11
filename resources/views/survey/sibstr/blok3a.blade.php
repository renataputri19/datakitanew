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
                <!-- Quarter/Month Tabs -->
                <div class="months-tabs" id="months-tabs">
                    <button type="button" class="month-tab active" data-quarter="dec2024">Des 2024</button>
                    <button type="button" class="month-tab" data-quarter="q1">Triwulan I (Jan–Mar)</button>
                    <button type="button" class="month-tab" data-quarter="q2">Triwulan II (Apr–Jun)</button>
                    <button type="button" class="month-tab" data-quarter="q3">Triwulan III (Jul–Sep)</button>
                    <button type="button" class="month-tab" data-quarter="q4">Triwulan IV (Okt–Des)</button>
                </div>
                <div class="table-responsive">
                    <table class="products-table" id="products-table">
                        <thead>
                            <!-- Header Row 1: Main Headers -->
                            <tr class="header-row-1">
                                <th rowspan="3" class="col-no sticky-col" data-sticky-col="1">No.</th>
                                <th rowspan="3" class="col-jenis sticky-col" data-sticky-col="2">301. Jenis Barang yang dihasilkan/diproduksi</th>
                                <th rowspan="3" class="col-uraian sticky-col" data-sticky-col="3">Uraian</th>
                                <th rowspan="2" class="col-2024 year-col year-2024" data-quarter="dec2024">2024</th>
                                <th colspan="12" class="col-2025 year-col year-2025" data-quarter="q1 q2 q3 q4">2025</th>
                            </tr>
                            
                            <!-- Header Row 2: Quarter Groups -->
                            <tr class="header-row-2">
                                <th colspan="3" class="quarter-header quarter-q1" data-quarter="q1">Triwulan I</th>
                                <th colspan="3" class="quarter-header quarter-q2" data-quarter="q2">Triwulan II</th>
                                <th colspan="3" class="quarter-header quarter-q3" data-quarter="q3">Triwulan III</th>
                                <th colspan="3" class="quarter-header quarter-q4" data-quarter="q4">Triwulan IV</th>
                            </tr>
                            
                            <!-- Header Row 3: Months and Column Numbers -->
                            <tr class="header-row-3"><!-- Month headers will be rendered dynamically by JS --></tr>
                            
                            <!-- Column Numbers Row -->
                            <tr class="column-numbers-row">
                                <td class="col-number">(1)</td>
                                <td class="col-number">(2)</td>
                                <td class="col-number">(3)</td>
                                <!-- Month column numbers will be rendered dynamically by JS -->
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
                                <!-- Month inputs will be rendered dynamically by JS -->
                            </tr>

                            <!-- Row 303: Total -->
                            <tr class="total-row">
                                <td class="row-number">303.</td>
                                <td class="product-info"><strong>Total</strong></td>
                                <td class="sub-row-label">Nilai</td>
                                <!-- Month totals will be rendered dynamically by JS -->
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

@push('scripts')
<script>
// Set up survey routes for the JavaScript module
window.surveyRoutes = {
    autoSave: '{{ route("survey.sibstr.blok3a.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok3a.save") }}',
    status: '{{ route("survey.sibstr.blok3a.status") }}',
    backToBlok2: '{{ route("survey.sibstr.blok2") }}',
    nextBlok: '{{ route("survey.sibstr.blok6") }}',
    blok3b_industri: '{{ route("survey.sibstr.blok3b.industri") }}',
    blok3b_nonindustri: '{{ route("survey.sibstr.blok3b.nonindustri") }}'
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
