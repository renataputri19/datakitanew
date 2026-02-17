@extends('layouts.app')

@section('title', 'SIBSTR - Blok IIIB Industri')

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
            BLOK IIIB. INDUSTRI
        </h2>
        <p class="survey-description">
            Pendapatan, persediaan, dan pengeluaran perusahaan satu triwulan yang lalu
        </p>
    </div>

    <!-- Auto-save Status -->
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    <!-- Survey Form -->
    <form id="survey-form" class="survey-form" data-aos="fade-up" data-aos-delay="200">
        @csrf

        <!-- PENDAPATAN PERUSAHAAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">PENDAPATAN PERUSAHAAN</h3>
                <p class="section-subtitle">Mencatat semua pendapatan selain PPN dan setelah diskon/retur</p>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">304.</span>
                        <span>Pendapatan royalti, bunga, dividen dan lainnya (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q304a_display">a. Pendapatan yang diterima perusahaan satu triwulan yang lalu</label>
                            <input type="text" id="q304a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q304a]">
                            <input type="hidden" name="blok3b_industri[q304a]" id="q304a" value="{{ $surveyResponse->blok3b_industri_data['q304a'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q304b_display">b. Pendapatan yang diterima perusahaan selama tahun 2025</label>
                            <input type="text" id="q304b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q304b]">
                            <input type="hidden" name="blok3b_industri[q304b]" id="q304b" value="{{ $surveyResponse->blok3b_industri_data['q304b'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Sewa/royalti sumber daya alam</li>
                                    <li>✓ Pendapatan bunga</li>
                                    <li>✓ Pendapatan dividen</li>
                                    <li>✓ Pendanaan dari Pemerintah (subsidi, skema magang, pelatihan)</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Pendanaan yang disediakan khusus untuk barang modal tertentu</li>
                                </ul>
                            </div>
                        </div>
                        
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">305.</span>
                        <span>Berapa persentase pendapatan yang diperoleh dari usaha online (%)</span>
                    </label>
                    <input type="number" id="q305_online" name="blok3b_industri[q305_online]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q305_online'] ?? '' }}" placeholder="0" required>
                    <div class="form-errors"></div>
                </div>
            </div>
        </div>

        <!-- PERSEDIAAN (INVENTORI) -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">PERSEDIAAN (INVENTORI)</h3>
                <p class="section-subtitle">Barang yang dikuasai dan ditahan untuk digunakan, dijual, atau diberikan</p>
            </div>
            <div class="form-grid">
                <!-- Q306 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">307.</span>
                        <span>Nilai Persediaan Bahan baku, bahan bakar, dsb (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q306_awal_display">a. Persediaan Awal Periode (<span id="q1_awal_label">1 ...</span>)</label>
                            <input type="text" id="q306_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q306_awal]">
                            <input type="hidden" name="blok3b_industri[q306_awal]" id="q306_awal" value="{{ $surveyResponse->blok3b_industri_data['q306_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q306_akhir_display">b. Persediaan Akhir Periode (<span id="q1_akhir_label">31 ...</span>)</label>
                            <input type="text" id="q306_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q306_akhir]">
                            <input type="hidden" name="blok3b_industri[q306_akhir]" id="q306_akhir" value="{{ $surveyResponse->blok3b_industri_data['q306_akhir'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q306_year_awal_display">c. Tahun 2025 - Persediaan Awal Periode (<span id="q1_year_awal_label">1 Jan 2025</span>)</label>
                            <input type="text" id="q306_year_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q306_year_awal]">
                            <input type="hidden" name="blok3b_industri[q306_year_awal]" id="q306_year_awal" value="{{ $surveyResponse->blok3b_industri_data['q306_year_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q306_year_akhir_display">d. Tahun 2025 - Persediaan Akhir Periode (<span id="q1_year_akhir_label">31 Des 2025</span>)</label>
                            <input type="text" id="q306_year_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q306_year_akhir]">
                            <input type="hidden" name="blok3b_industri[q306_year_akhir]" id="q306_year_akhir" value="{{ $surveyResponse->blok3b_industri_data['q306_year_akhir'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Kemasan kosong</li>
                                    <li>✓ Suku cadang yang tidak dikapitalisasi untuk aset tetap</li>
                                    <li>✓ Bahan baku dan bahan bakar yang biasa digunakan perusahaan ini</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Bahan bakar untuk dijual</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Tidak bisa negatif. Periode muncul otomatis sesuai waktu pendataan.</div>
                    </div>
                </div>

                <!-- Q307 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">308.</span>
                        <span>Nilai Persediaan Barang Dalam Proses (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307_awal_display">a. Persediaan Awal Periode (<span id="q2_awal_label">1 ...</span>)</label>
                            <input type="text" id="q307_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q307_awal]">
                            <input type="hidden" name="blok3b_industri[q307_awal]" id="q307_awal" value="{{ $surveyResponse->blok3b_industri_data['q307_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307_akhir_display">b. Persediaan Akhir Periode (<span id="q2_akhir_label">31 ...</span>)</label>
                            <input type="text" id="q307_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q307_akhir]">
                            <input type="hidden" name="blok3b_industri[q307_akhir]" id="q307_akhir" value="{{ $surveyResponse->blok3b_industri_data['q307_akhir'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307_year_awal_display">c. Tahun 2025 - Persediaan Awal Periode (<span id="q2_year_awal_label">1 Jan 2025</span>)</label>
                            <input type="text" id="q307_year_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q307_year_awal]">
                            <input type="hidden" name="blok3b_industri[q307_year_awal]" id="q307_year_awal" value="{{ $surveyResponse->blok3b_industri_data['q307_year_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307_year_akhir_display">d. Tahun 2025 - Persediaan Akhir Periode (<span id="q2_year_akhir_label">31 Des 2025</span>)</label>
                            <input type="text" id="q307_year_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q307_year_akhir]">
                            <input type="hidden" name="blok3b_industri[q307_year_akhir]" id="q307_year_akhir" value="{{ $surveyResponse->blok3b_industri_data['q307_year_akhir'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Barang setengah jadi atau barang yang akan diproses lebih lanjut sebelum dijual</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Penerimaan pembayaran</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Tidak bisa negatif. Periode muncul otomatis sesuai waktu pendataan.</div>
                    </div>
                </div>

                <!-- Q308 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">309.</span>
                        <span>Nilai Persediaan Barang jadi (termasuk untuk dijual kembali) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308_awal_display">a. Persediaan Awal Periode (<span id="q3_awal_label">1 ...</span>)</label>
                            <input type="text" id="q308_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q308_awal]">
                            <input type="hidden" name="blok3b_industri[q308_awal]" id="q308_awal" value="{{ $surveyResponse->blok3b_industri_data['q308_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308_akhir_display">b. Persediaan Akhir Periode (<span id="q3_akhir_label">31 ...</span>)</label>
                            <input type="text" id="q308_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q308_akhir]">
                            <input type="hidden" name="blok3b_industri[q308_akhir]" id="q308_akhir" value="{{ $surveyResponse->blok3b_industri_data['q308_akhir'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308_year_awal_display">c. Tahun 2025 - Persediaan Awal Periode (<span id="q3_year_awal_label">1 Jan 2025</span>)</label>
                            <input type="text" id="q308_year_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q308_year_awal]">
                            <input type="hidden" name="blok3b_industri[q308_year_awal]" id="q308_year_awal" value="{{ $surveyResponse->blok3b_industri_data['q308_year_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308_year_akhir_display">d. Tahun 2025 - Persediaan Akhir Periode (<span id="q3_year_akhir_label">31 Des 2025</span>)</label>
                            <input type="text" id="q308_year_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q308_year_akhir]">
                            <input type="hidden" name="blok3b_industri[q308_year_akhir]" id="q308_year_akhir" value="{{ $surveyResponse->blok3b_industri_data['q308_year_akhir'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Barang hasil produksi dan siap jual</li>
                                    <li>✓ Barang yang dibeli untuk dijual kembali tanpa proses lebih lanjut</li>
                                    <li>✓ Bahan bakar untuk dijual</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Gedung dan barang yang disewa dan disewakan</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Tidak bisa negatif. Periode muncul otomatis sesuai waktu pendataan.</div>
                    </div>
                </div>

                <!-- Q310 Auto-calculated -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">310.</span>
                        <span>Total persediaan (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel required" for="q309_awal_display">a. Satu triwulan yang lalu - Persediaan Awal Periode (jumlah 307.a + 308.a + 309.a)</label>
                            <input type="text" id="q309_awal_display" class="form-control currency-display readonly" placeholder="0" readonly disabled style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q309_awal]" id="q309_awal" value="{{ $surveyResponse->blok3b_industri_data['q309_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel required" for="q309_akhir_display">b. Satu triwulan yang lalu - Persediaan Akhir Periode (jumlah 307.b + 308.b + 309.b)</label>
                            <input type="text" id="q309_akhir_display" class="form-control currency-display readonly" placeholder="0" readonly disabled style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q309_akhir]" id="q309_akhir" value="{{ $surveyResponse->blok3b_industri_data['q309_akhir'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel required" for="q310b_awal_display">c. Tahun 2025 - Persediaan Awal Periode (jumlah 307.c + 308.c + 309.c)</label>
                            <input type="text" id="q310b_awal_display" class="form-control currency-display readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q310b_awal]" id="q310b_awal" value="{{ $surveyResponse->blok3b_industri_data['q310b_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel required" for="q310b_akhir_display">d. Tahun 2025 - Persediaan Akhir Periode (jumlah 307.d + 308.d + 309.d)</label>
                            <input type="text" id="q310b_akhir_display" class="form-control currency-display readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q310b_akhir]" id="q310b_akhir" value="{{ $surveyResponse->blok3b_industri_data['q310b_akhir'] ?? '' }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ITEM PENGELUARAN PERUSAHAAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3>
                <p class="section-subtitle">Biaya pengeluaran tanpa PPN dan diskon neto yang diberikan</p>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">311.</span>
                        <span>Total upah dan gaji serta jaminan sosial pegawai (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q311a_display">a. Total upah dan gaji, serta jaminan sosial pegawai selama satu triwulan yang lalu</label>
                            <input type="text" id="q311a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q311a]" required>
                            <input type="hidden" name="blok3b_industri[q311a]" id="q311a" value="{{ $surveyResponse->blok3b_industri_data['q311a'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q311b_display">b. Total upah dan gaji, serta jaminan sosial pegawai selama tahun 2025</label>
                            <input type="text" id="q311b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q311b]" required>
                            <input type="hidden" name="blok3b_industri[q311b]" id="q311b" value="{{ $surveyResponse->blok3b_industri_data['q311b'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q311b1_display">b.1 Total upah dan gaji, serta jaminan sosial pegawai produksi selama tahun 2025</label>
                            <input type="text" id="q311b1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q311b1]" required>
                            <input type="hidden" name="blok3b_industri[q311b1]" id="q311b1" value="{{ $surveyResponse->blok3b_industri_data['q311b1'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q311b2_display">b.2 Total upah dan gaji, serta jaminan sosial selain pegawai produksi selama tahun 2025</label>
                            <input type="text" id="q311b2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q311b2]" required>
                            <input type="hidden" name="blok3b_industri[q311b2]" id="q311b2" value="{{ $surveyResponse->blok3b_industri_data['q311b2'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Upah dan gaji pegawai/karyawan yang telah dikeluarkan  ringkasan  pembayarannya (group sertificate)</li>
                                    <li>✓ Komisi dan tips untuk pegawai/karyawan</li>
                                    <li>✓ Bonus</li>
                                    <li>✓ Pembayaran Cuti tahunan dan jenis cuti lainnya</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Upah dan gaji yang dikapitalisasi</li>
                                    <li>⮾ Pembayaran untuk konsultan dan kontraktor yang  berusaha sendiri (bukan karyawan perusahaan), yang dibayarkan dengan komisi</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">312.</span>
                        <span>Penambahan aset tetap (kecuali pembelian tanah) satu triwulan yang lalu (rupiah)</span>
                    </label>
                    <input type="text" id="q311_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q311]">
                    <input type="hidden" name="blok3b_industri[q311]" id="q311" value="{{ $surveyResponse->blok3b_industri_data['q311'] ?? '' }}" required>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Catatan: Penambahan aset tetap mencakup: Pembelian, barter, produksi sendiri, dan sewa beli (financial lease) aset tetap; Pemberian/transfer/hibah dari pihak lain; Perbaikan besar aset tetap guna meningkatkan kapasitas produksi dan usia pakai; dan Biaya alih kepemilikan atas aset nonfinansial yang tidak diproduksi.</div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">313.</span>
                        <span>Biaya produksi (pemakaian bahan baku dan penolong) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q312_display">a. Satu triwulan yang lalu</label>
                            <input type="text" id="q312_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q312]">
                            <input type="hidden" name="blok3b_industri[q312]" id="q312" value="{{ $surveyResponse->blok3b_industri_data['q312'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q312_year_display">b. Selama tahun 2025</label>
                            <input type="text" id="q312_year_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q312_year]">
                            <input type="hidden" name="blok3b_industri[q312_year]" id="q312_year" value="{{ $surveyResponse->blok3b_industri_data['q312_year'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Pembelian bahan yang digunakan dalam proses produksi dan pengemasan</li>
                                    <li>✓ Pembelian barang jadi untuk dijual kembali</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Pembelian barang yang dikapitalisasi (aset tetap)</li>
                                    <li>⮾ Perubahan persediaan</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Catatan: Mencakup seluruh nilai barang dan jasa yang digunakan sebagai bahan baku dalam proses produksi, tidak termasuk aset tetap. Tidak bisa negatif.</div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">314.</span>
                        <span>Biaya operasional (air, listrik, gas, pemeliharaan, biaya angkutan) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_display">a. Satu triwulan yang lalu</label>
                            <input type="text" id="q313_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q313]">
                            <input type="hidden" name="blok3b_industri[q313]" id="q313" value="{{ $surveyResponse->blok3b_industri_data['q313'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_year_display">b. Selama tahun 2025</label>
                            <input type="text" id="q313_year_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q313_year]">
                            <input type="hidden" name="blok3b_industri[q313_year]" id="q313_year" value="{{ $surveyResponse->blok3b_industri_data['q313_year'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Pengeluaran listrik, bahan bakar dan air</li>
                                    <li>✓ Pembelian bahan perkantoran umum</li>
                                    <li>✓ Pembelian komponen dan bahan bakar untuk kendaraan bermotor</li>
                                    <li>✓ Pembayaran sewa operasi (dengan atau tanpa operator)</li>
                                    <li>✓ Pembayaran ke pihak lain untuk kargo, delivery, dan jasa angkutan</li>
                                    <li>✓ Biaya lisensi software komputer berumur kurang dari satu tahun (termasuk biaya instalasi oleh provider eksternal)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Catatan: Mencakup biaya-biaya yang tidak secara langsung dalam proses produksi seperti air, listrik, gas, pemeliharaan, serta biaya angkutan. Tidak bisa negatif.</div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">315.</span>
                        <span>Biaya Non operasional (air, listrik, gas, pemeliharaan, biaya angkutan) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315a_display">a. Satu triwulan yang lalu</label>
                            <input type="text" id="q315a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q315a]">
                            <input type="hidden" name="blok3b_industri[q315a]" id="q315a" value="{{ $surveyResponse->blok3b_industri_data['q315a'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315b_display">b. Selama tahun 2025</label>
                            <input type="text" id="q315b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q315b]">
                            <input type="hidden" name="blok3b_industri[q315b]" id="q315b" value="{{ $surveyResponse->blok3b_industri_data['q315b'] ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Pengeluaran listrik, bahan bakar dan air</li>
                                    <li>✓ Pembelian bahan perkantoran umum</li>
                                    <li>✓ Pembelian komponen dan bahan bakar untuk kendaraan bermotor</li>
                                    <li>✓ Pembayaran sewa operasi (dengan atau tanpa operator)</li>
                                    <li>✓ Pembayaran ke pihak lain untuk kargo, delivery, dan jasa angkutan</li>
                                    <li>✓ Biaya lisensi software komputer berumur kurang dari satu tahun (termasuk biaya instalasi oleh provider eksternal)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Catatan: Mencakup biaya-biaya yang tidak secara langsung dalam proses produksi seperti air, listrik, gas, pemeliharaan, serta biaya angkutan.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 4: EKSPOR IMPOR LUAR NEGERI -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">EKSPOR IMPOR LUAR NEGERI</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">316.</span>
                        <span>Berapa persentase nilai produksi yang dijual sebagai produk ekspor luar negeri (%)</span>
                    </label>
                    <input type="number" id="q314" name="blok3b_industri[q314]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q314'] ?? '' }}" placeholder="0" required>
                    <div class="form-errors"></div>
                    
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">317.</span>
                        <span>Berapa persentase nilai bahan baku dan bahan penolong yang diperoleh melalui impor luar negeri langsung atau melalui jasa importir (%)</span>
                    </label>
                    <input type="number" id="q315" name="blok3b_industri[q315]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q315'] ?? '' }}" placeholder="0" required>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Maksimal 100%.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- NILAI ASET -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">NILAI ASET</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">318.</span>
                        <span>Nilai aset pada 31 Desember 2025 (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318a_display">a. Tanah dan bangunan</label>
                            <input type="text" id="q318a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q318a]">
                            <input type="hidden" name="blok3b_industri[q318a]" id="q318a" value="{{ $surveyResponse->blok3b_industri_data['q318a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318b_display">b. Selain tanah dan bangunan</label>
                            <input type="text" id="q318b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q318b]">
                            <input type="hidden" name="blok3b_industri[q318b]" id="q318b" value="{{ $surveyResponse->blok3b_industri_data['q318b'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318c_display">c. Nilai total aset (otomatis jumlah a + b)</label>
                            <input type="text" id="q318c_display" class="form-control currency-display readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q318c]" id="q318c" value="{{ $surveyResponse->blok3b_industri_data['q318c'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318c_range">c1. Jika tidak dapat mengisikan nominal, pilih rentang</label>
                            <select id="q318c_range" name="blok3b_industri[q318c_range]" class="form-control">
                                <option value="">Pilih rentang</option>
                                <option value="1" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '1') ? 'selected' : '' }}>1 s.d. Rp 500 juta</option>
                                <option value="2" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '2') ? 'selected' : '' }}>Lebih dari Rp 500 juta s.d. Rp 1 miliar</option>
                                <option value="3" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '3') ? 'selected' : '' }}>Lebih dari Rp 1 miliar s.d. Rp 5 miliar</option>
                                <option value="4" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '4') ? 'selected' : '' }}>Lebih dari Rp 5 miliar s.d. Rp 10 miliar</option>
                                <option value="5" {{ (isset($surveyResponse->blok3b_industri_data['q318c_range']) && $surveyResponse->blok3b_industri_data['q318c_range'] == '5') ? 'selected' : '' }}>Lebih dari Rp 10 miliar</option>
                            </select>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel required" for="q318d_area">d. Luas tanah yang digunakan untuk usaha (m persegi)</label>
                            <input type="number" id="q318d_area" name="blok3b_industri[q318d_area]" class="form-control" min="0" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q318d_area'] ?? '' }}" placeholder="0" required>
                            <div class="form-errors"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KEPEMILIKAN MODAL -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">KEPEMILIKAN MODAL</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">319.</span>
                        <span>Susunan kepemilikan modal pada 31 Desember 2025 (%)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319a">a. Pribadi/Perorangan</label>
                            <input type="number" id="q319a" name="blok3b_industri[q319a]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319a'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319b">b. Lembaga Nonprofit yang Melayani Rumah Tangga</label>
                            <input type="number" id="q319b" name="blok3b_industri[q319b]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319b'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319c">c. Korporasi Publik</label>
                            <input type="number" id="q319c" name="blok3b_industri[q319c]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319c'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319d">d. Korporasi Non Publik</label>
                            <input type="number" id="q319d" name="blok3b_industri[q319d]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319d'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319e">e. Pemerintah</label>
                            <input type="number" id="q319e" name="blok3b_industri[q319e]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319e'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319f">f. Asing</label>
                            <input type="number" id="q319f" name="blok3b_industri[q319f]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q319f'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319g_display">g. Total (otomatis)</label>
                            <input type="number" id="q319g_display" class="form-control readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q319g]" id="q319g" value="{{ $surveyResponse->blok3b_industri_data['q319g'] ?? '' }}" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions flex justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
            <div class="flex items-center gap-4">
                <button type="button" class="btn btn-secondary px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" id="back-to-blok3a">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    Kembali ke Bab 3A
                </button>

                <button type="button" id="save-draft" class="btn btn-secondary px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17,21 17,13 7,13 7,21"></polyline>
                        <polyline points="7,3 7,8 15,8"></polyline>
                    </svg>
                    Simpan Draft
                </button>

                <button type="button" id="save-complete" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 btn btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                    Simpan dan Lanjutkan
                </button>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>* Tidak boleh negatif; 310 otomatis dijumlahkan.</span>
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
    autoSave: '{{ route("survey.sibstr.blok3b.industri.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok3b.industri.save") }}',
    status: '{{ route("survey.sibstr.blok3b.industri.status") }}',
    backToBlok3a: '{{ route("survey.sibstr.blok3a") }}',
    nextBlok: '{{ route("survey.sibstr.blok4") }}'
};
@endif

// Existing data
window.surveyData = {
    blok3b: @json($surveyResponse->blok3b_industri_data ?? [])
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok3b-industri.js') }}"></script>
@endpush
@endsection