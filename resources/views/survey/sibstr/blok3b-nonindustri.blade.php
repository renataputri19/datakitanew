@extends('layouts.app')

@section('title', 'SIBSTR - Blok IIIB Non-Industri')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
@endpush

@section('content')
<div class="survey-container">
    <!-- Survey Header -->
    <div class="survey-header" data-aos="fade-up">
        <h1 class="survey-title">
            SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR)
        </h1>
        <h2 class="survey-subtitle">
            BLOK IIIB. NON-INDUSTRI
        </h2>
        <p class="survey-description">
            Pendapatan, persediaan, dan pengeluaran perusahaan satu triwulan yang lalu
        </p>
    </div>

    @if(session('warning'))
    <div class="autosave-status info" data-aos="fade-up">
        <span>{{ session('warning') }}</span>
    </div>
    @endif

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
                        <span class="question-number">303.</span>
                        <span>Nilai pendapatan dari penjualan barang dan jasa perusahaan (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q303_display">a. Satu triwulan yang lalu</label>
                            <input type="text" id="q303_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q303]" required>
                            <input type="hidden" name="blok3b_nonindustri[q303]" id="q303" value="{{ $surveyResponse->blok3b_nonindustri_data['q303'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q303_year_display">b. Selama tahun 2025</label>
                            <input type="text" id="q303_year_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q303_year]" required>
                            <input type="hidden" name="blok3b_nonindustri[q303_year]" id="q303_year" value="{{ $surveyResponse->blok3b_nonindustri_data['q303_year'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Barang yang dijual baik yang diproduksi sendiri maupun tidak</li>
                                    <li>✓ Penjualan ekspor (FOB-Free On Board)</li>
                                    <li>✓ Penjualan atau transfer ke rekan bisnis/ organisasi atau cabang di luar negeri (termasuk dalam di rincian 302)</li>
                                    <li>✓ Pendapatan dari pengangkutan barang yang tidak dijual perusahaan</li>
                                    <li>✓ Pendapatan jasa perbaikan dan layanan</li>
                                    <li>✓ Pendapatan dari kontrak, subkontrak, dan komisi</li>
                                    <li>✓ Pendapatan manajemen dari perusahaan/organisasi terkait maupun tidak</li>
                                    <li>✓ Pendapatan dari jasa pemasangan</li>
                                    <li>✓ Pendapatan dari jasa berlangganan dan keanggotaan</li>
                                    <li>✓ Pendapatan dari jasa iklan</li>
                                    <li>✓ Pendapatan dari sewa operasi</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Penjualan aset</li>
                                    <li>⮾ Royalti dari penggunaan lahan di bawah pengaturan sewa mineral</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">304.</span>
                        <span>Pendapatan royalti, bunga, dividen dan lainnya (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q304_display">a. Satu triwulan yang lalu</label>
                            <input type="text" id="q304_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q304]" required>
                            <input type="hidden" name="blok3b_nonindustri[q304]" id="q304" value="{{ $surveyResponse->blok3b_nonindustri_data['q304'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q304_year_display">b. Selama tahun 2025</label>
                            <input type="text" id="q304_year_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q304_year]" required>
                            <input type="hidden" name="blok3b_nonindustri[q304_year]" id="q304_year" value="{{ $surveyResponse->blok3b_nonindustri_data['q304_year'] ?? '' }}">
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
                                    <li>✓ Pendanaan dari Pemerintah (subsidi, skema magang dan pelatihan)</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Pendanaan yang disediakan khusus untuk barang modal tertentu</li>
                                </ul>
                            </div>
                        </div>
                        <!-- <div class="hint-note text-muted">Tidak bisa negatif.</div> -->
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">305.</span>
                        <span>Total pendapatan (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q305_display">a. Satu triwulan yang lalu (jumlah 303.a + 304.a)</label>
                            <input type="text" id="q305_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q305]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q305]" id="q305" value="{{ $surveyResponse->blok3b_nonindustri_data['q305'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q305_year_display">b. Selama tahun 2025 (jumlah 303.b + 304.b)</label>
                            <input type="text" id="q305_year_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q305_year]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q305_year]" id="q305_year" value="{{ $surveyResponse->blok3b_nonindustri_data['q305_year'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Otomatis jumlah dari 303 dan 304 sesuai periode.</div>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">306.</span>
                        <span>Berapa persentase pendapatan yang diperoleh dari usaha online (%)</span>
                    </label>
                    <input type="number" id="q306_online" name="blok3b_nonindustri[q306_online]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q306_online'] ?? '' }}" placeholder="0" required>
                    <div class="form-errors"></div>
                </div>
            </div>
        </div>

        <!-- PERSEDIAAN (INVENTORI) -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">PERSEDIAAN (INVENTORI)</h3>
                <p class="section-subtitle">Barang yang ditahan untuk digunakan, dijual, atau diberikan mendatang</p>
            </div>
            <div class="form-grid">
                <!-- Q306 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">307.</span>
                        <span>Nilai persediaan bahan baku, bahan bakar, dan sebagainya satu triwulan yang lalu (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q306_awal_display">a. Persediaan Awal Periode (<span id="q1_awal_label">1 ...</span>)</label>
                            <input type="text" id="q306_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q306a]" required>
                            <input type="hidden" name="blok3b_nonindustri[q306a]" id="q306_awal" value="{{ $surveyResponse->blok3b_nonindustri_data['q306a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q306_akhir_display">b. Persediaan Akhir Periode (<span id="q1_akhir_label">31 ...</span>)</label>
                            <input type="text" id="q306_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q306b]" required>
                    <input type="hidden" name="blok3b_nonindustri[q306b]" id="q306_akhir" value="{{ $surveyResponse->blok3b_nonindustri_data['q306b'] ?? '' }}">
                </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q306_year_awal_display">c. Tahun 2025 - Persediaan Awal Periode (<span id="q1_year_awal_label">1 Jan 2025</span>)</label>
                            <input type="text" id="q306_year_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q306_year_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q306_year_awal]" id="q306_year_awal" value="{{ $surveyResponse->blok3b_nonindustri_data['q306_year_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q306_year_akhir_display">d. Tahun 2025 - Persediaan Akhir Periode (<span id="q1_year_akhir_label">31 Des 2025</span>)</label>
                            <input type="text" id="q306_year_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q306_year_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q306_year_akhir]" id="q306_year_akhir" value="{{ $surveyResponse->blok3b_nonindustri_data['q306_year_akhir'] ?? '' }}">
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
                <div class="hint-note text-muted"><!-- Tidak bisa negatif. --> Periode muncul otomatis sesuai triwulan.</div>
            </div>
        </div>

                <!-- Q308 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">308.</span>
                        <span>Nilai persediaan barang dalam proses satu triwulan yang lalu (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307_awal_display">a. Persediaan Awal Periode (<span id="q2_awal_label">1 ...</span>)</label>
                            <input type="text" id="q307_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q307a]" required>
                            <input type="hidden" name="blok3b_nonindustri[q307a]" id="q307_awal" value="{{ $surveyResponse->blok3b_nonindustri_data['q307a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307_akhir_display">b. Persediaan Akhir Periode (<span id="q2_akhir_label">31 ...</span>)</label>
                            <input type="text" id="q307_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q307b]" required>
                    <input type="hidden" name="blok3b_nonindustri[q307b]" id="q307_akhir" value="{{ $surveyResponse->blok3b_nonindustri_data['q307b'] ?? '' }}">
                </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307_year_awal_display">c. Tahun 2025 - Persediaan Awal Periode (<span id="q2_year_awal_label">1 Jan 2025</span>)</label>
                            <input type="text" id="q307_year_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q307_year_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q307_year_awal]" id="q307_year_awal" value="{{ $surveyResponse->blok3b_nonindustri_data['q307_year_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307_year_akhir_display">d. Tahun 2025 - Persediaan Akhir Periode (<span id="q2_year_akhir_label">31 Des 2025</span>)</label>
                            <input type="text" id="q307_year_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q307_year_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q307_year_akhir]" id="q307_year_akhir" value="{{ $surveyResponse->blok3b_nonindustri_data['q307_year_akhir'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Barang setengah jadi atau barang pabrikasi yang akan diproses lebih lanjut sebelum dijual</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Penerimaan Pembayaran</li>
                                </ul>
                            </div>
                </div>
                <div class="hint-note text-muted"><!-- Tidak bisa negatif. --> Periode muncul otomatis sesuai triwulan.</div>
            </div>
        </div>

                <!-- Q309 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">309.</span>
                        <span>Nilai persediaan barang jadi (termasuk untuk dijual kembali) satu triwulan yang lalu (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308_awal_display">a. Persediaan Awal Periode (<span id="q3_awal_label">1 ...</span>)</label>
                            <input type="text" id="q308_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q308a]" required>
                            <input type="hidden" name="blok3b_nonindustri[q308a]" id="q308_awal" value="{{ $surveyResponse->blok3b_nonindustri_data['q308a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308_akhir_display">b. Persediaan Akhir Periode (<span id="q3_akhir_label">31 ...</span>)</label>
                            <input type="text" id="q308_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q308b]" required>
                    <input type="hidden" name="blok3b_nonindustri[q308b]" id="q308_akhir" value="{{ $surveyResponse->blok3b_nonindustri_data['q308b'] ?? '' }}">
                </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308_year_awal_display">c. Tahun 2025 - Persediaan Awal Periode (<span id="q3_year_awal_label">1 Jan 2025</span>)</label>
                            <input type="text" id="q308_year_awal_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q308_year_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q308_year_awal]" id="q308_year_awal" value="{{ $surveyResponse->blok3b_nonindustri_data['q308_year_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308_year_akhir_display">d. Tahun 2025 - Persediaan Akhir Periode (<span id="q3_year_akhir_label">31 Des 2025</span>)</label>
                            <input type="text" id="q308_year_akhir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q308_year_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q308_year_akhir]" id="q308_year_akhir" value="{{ $surveyResponse->blok3b_nonindustri_data['q308_year_akhir'] ?? '' }}">
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
                <div class="hint-note text-muted"><!-- Tidak bisa negatif. --> Periode muncul otomatis sesuai triwulan.</div>
            </div>
        </div>

                <!-- Q310 totals -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">310.</span>
                        <span>Total persediaan (rupiah)</span>
                    </label>
            <div class="form-subgrid">
                <div class="form-subrow">
                            <label class="form-sublabel" for="q309_awal_display">a. Satu triwulan yang lalu - Persediaan Awal Periode (jumlah 307.a + 308.a + 309.a)</label>
                            <input type="text" id="q309_awal_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q309a]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q309a]" id="q309_awal" value="{{ $surveyResponse->blok3b_nonindustri_data['q309a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q309_akhir_display">b. Satu triwulan yang lalu - Persediaan Akhir Periode (jumlah 307.b + 308.b + 309.b)</label>
                            <input type="text" id="q309_akhir_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q309b]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q309b]" id="q309_akhir" value="{{ $surveyResponse->blok3b_nonindustri_data['q309b'] ?? '' }}">
                </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q310b_awal_display">c. Tahun 2025 - Persediaan Awal Periode (jumlah 307.c + 308.c + 309.c)</label>
                            <input type="text" id="q310b_awal_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q310b_awal]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q310b_awal]" id="q310b_awal" value="{{ $surveyResponse->blok3b_nonindustri_data['q310b_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q310b_akhir_display">d. Tahun 2025 - Persediaan Akhir Periode (jumlah 307.d + 308.d + 309.d)</label>
                            <input type="text" id="q310b_akhir_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q310b_akhir]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q310b_akhir]" id="q310b_akhir" value="{{ $surveyResponse->blok3b_nonindustri_data['q310b_akhir'] ?? '' }}">
                        </div>
            </div>
            <div class="form-errors"></div>
            <div class="form-hint">
                <div class="hint-note text-muted">Otomatis terisi dari penjumlahan di atasnya (triwulan dan tahunan).</div>
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
                        <span>Total upah dan gaji, serta jaminan sosial pegawai (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q311a_display">a. Total upah dan gaji, serta jaminan sosial pegawai selama satu triwulan yang lalu</label>
                            <input type="text" id="q311a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q311a]" required>
                            <input type="hidden" name="blok3b_nonindustri[q311a]" id="q311a" value="{{ $surveyResponse->blok3b_nonindustri_data['q311a'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q311b_display">b. Total upah dan gaji, serta jaminan sosial pegawai selama tahun 2025</label>
                            <input type="text" id="q311b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q311b]" required>
                            <input type="hidden" name="blok3b_nonindustri[q311b]" id="q311b" value="{{ $surveyResponse->blok3b_nonindustri_data['q311b'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q311b1_display">b.1 Total upah dan gaji, serta jaminan sosial pegawai produksi selama tahun 2025</label>
                            <input type="text" id="q311b1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q311b1]" required>
                            <input type="hidden" name="blok3b_nonindustri[q311b1]" id="q311b1" value="{{ $surveyResponse->blok3b_nonindustri_data['q311b1'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q311b2_display">b.2 Total upah dan gaji, serta jaminan sosial selain pegawai produksi selama tahun 2025</label>
                            <input type="text" id="q311b2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q311b2]" required>
                            <input type="hidden" name="blok3b_nonindustri[q311b2]" id="q311b2" value="{{ $surveyResponse->blok3b_nonindustri_data['q311b2'] ?? '' }}" required>
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
                        <!-- <div class="hint-note text-muted">Tidak bisa negatif.</div> -->
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">312.</span>
                        <span>Penambahan aset tetap (kecuali pembelian tanah) satu triwulan yang lalu (rupiah)</span>
                    </label>
                    <input type="text" id="q311_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q311]" required>
                    <input type="hidden" name="blok3b_nonindustri[q311]" id="q311" value="{{ $surveyResponse->blok3b_nonindustri_data['q311'] ?? '' }}">
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
                            <input type="text" id="q312_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q312]" required>
                            <input type="hidden" name="blok3b_nonindustri[q312]" id="q312" value="{{ $surveyResponse->blok3b_nonindustri_data['q312'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q312_year_display">b. Selama tahun 2025</label>
                            <input type="text" id="q312_year_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q312_year]" required>
                            <input type="hidden" name="blok3b_nonindustri[q312_year]" id="q312_year" value="{{ $surveyResponse->blok3b_nonindustri_data['q312_year'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Catatan:</div>
                                <div>Mencakup seluruh nilai barang dan jasa yang digunakan sebagai bahan baku dalam proses produksi, tidak termasuk aset tetap.</div>
                            </div>
                        </div>
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
                                    <li>⮾ Pembelian barang yang dikapitalisasi</li>
                                    <li>⮾ Perubahan persediaan</li>
                                </ul>
                        </div>
                        </div>
                        <!-- <div class="hint-note text-muted">Tidak bisa negatif.</div> -->
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
                            <input type="text" id="q313_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q313]" required>
                            <input type="hidden" name="blok3b_nonindustri[q313]" id="q313" value="{{ $surveyResponse->blok3b_nonindustri_data['q313'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_year_display">b. Selama tahun 2025</label>
                            <input type="text" id="q313_year_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q313_year]" required>
                            <input type="hidden" name="blok3b_nonindustri[q313_year]" id="q313_year" value="{{ $surveyResponse->blok3b_nonindustri_data['q313_year'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Catatan:</div>
                                <div>Mencakup biaya-biaya yang tidak secara langsung dalam proses produksi seperti air, listrik, gas, pemeliharaan, serta biaya angkutan.</div>
                            </div>
                        </div>
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Pengeluaran listrik, bahan bakar dan air</li>
                                    <li>✓ Pembelian bahan perkantoran umum</li>
                                    <li>✓ Pembelian komponen dan bahan bakar untuk kendaraan bermotor</li>
                                    <li>✓ pembayaran ke pihak lain untuk kargo, delivery, dan jasa angkutan</li>
                                    <li>✓ Pembayaran sewa operasi (dengan atau tanpa operator)</li>
                                    <li>✓ Biaya lisensi software komputer yang berumur kurang dari satu tahun (termasuk biaya instalasi oleh provider eksternal)</li>
                                </ul>
                        </div>
                        </div>
                        <!-- <div class="hint-note text-muted">Tidak bisa negatif.</div> -->
                    </div>
                </div>
            </div>
        </div>

        <!-- QUESTION 315: NON-OPERASIONAL COSTS -->
        <div class="form-section">
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">315.</span>
                        <span>Biaya Non operasional (air, listrik, gas, pemeliharaan, biaya angkutan) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315a_display">a. Satu triwulan yang lalu</label>
                            <input type="text" id="q315a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q315a]" required>
                            <input type="hidden" name="blok3b_nonindustri[q315a]" id="q315a" value="{{ $surveyResponse->blok3b_nonindustri_data['q315a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315b_display">b. Selama tahun 2025</label>
                            <input type="text" id="q315b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q315b]" required>
                            <input type="hidden" name="blok3b_nonindustri[q315b]" id="q315b" value="{{ $surveyResponse->blok3b_nonindustri_data['q315b'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Catatan:</div>
                                <div>Mencakup biaya-biaya yang tidak secara langsung dalam proses produksi seperti air, listrik, gas, pemeliharaan, serta biaya angkutan.</div>
                            </div>
                        </div>
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Pengeluaran listrik, bahan bakar dan air</li>
                                    <li>✓ Pembelian bahan perkantoran umum</li>
                                    <li>✓ Pembelian komponen dan bahan bakar untuk kendaraan bermotor</li>
                                    <li>✓ pembayaran ke pihak lain untuk kargo, delivery, dan jasa angkutan</li>
                                    <li>✓ Pembayaran sewa operasi (dengan atau tanpa operator)</li>
                                    <li>✓ Biaya lisensi software komputer yang berumur kurang dari satu tahun (termasuk biaya instalasi oleh provider eksternal)</li>
                                </ul>
                            </div>
                        </div>
                        <!-- <div class="hint-note text-muted">Tidak bisa negatif.</div> -->
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
                    <input type="number" id="q314" name="blok3b_nonindustri[q314]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q314'] ?? '' }}" placeholder="0" required>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">317.</span>
                        <span>Berapa persentase nilai bahan baku dan bahan penolong yang diperoleh melalui impor luar negeri langsung atau melalui jasa importir (%)</span>
                    </label>
                    <input type="number" id="q315" name="blok3b_nonindustri[q315]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q315'] ?? '' }}" placeholder="0" required>
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
                            <input type="text" id="q318a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q318a]">
                            <input type="hidden" name="blok3b_nonindustri[q318a]" id="q318a" value="{{ $surveyResponse->blok3b_nonindustri_data['q318a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318b_display">b. Selain tanah dan bangunan</label>
                            <input type="text" id="q318b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q318b]">
                            <input type="hidden" name="blok3b_nonindustri[q318b]" id="q318b" value="{{ $surveyResponse->blok3b_nonindustri_data['q318b'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318c_display">c. Nilai total aset (otomatis jumlah a + b)</label>
                            <input type="text" id="q318c_display" class="form-control currency-display readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q318c]" id="q318c" value="{{ $surveyResponse->blok3b_nonindustri_data['q318c'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318c_range">c1. Jika tidak dapat mengisikan nominal, pilih rentang</label>
                            <select id="q318c_range" name="blok3b_nonindustri[q318c_range]" class="form-control">
                                <option value="">Pilih rentang</option>
                                <option value="1" {{ (isset($surveyResponse->blok3b_nonindustri_data['q318c_range']) && $surveyResponse->blok3b_nonindustri_data['q318c_range'] == '1') ? 'selected' : '' }}>1 s.d. Rp 500 juta</option>
                                <option value="2" {{ (isset($surveyResponse->blok3b_nonindustri_data['q318c_range']) && $surveyResponse->blok3b_nonindustri_data['q318c_range'] == '2') ? 'selected' : '' }}>Lebih dari Rp 500 juta s.d. Rp 1 miliar</option>
                                <option value="3" {{ (isset($surveyResponse->blok3b_nonindustri_data['q318c_range']) && $surveyResponse->blok3b_nonindustri_data['q318c_range'] == '3') ? 'selected' : '' }}>Lebih dari Rp 1 miliar s.d. Rp 5 miliar</option>
                                <option value="4" {{ (isset($surveyResponse->blok3b_nonindustri_data['q318c_range']) && $surveyResponse->blok3b_nonindustri_data['q318c_range'] == '4') ? 'selected' : '' }}>Lebih dari Rp 5 miliar s.d. Rp 10 miliar</option>
                                <option value="5" {{ (isset($surveyResponse->blok3b_nonindustri_data['q318c_range']) && $surveyResponse->blok3b_nonindustri_data['q318c_range'] == '5') ? 'selected' : '' }}>Lebih dari Rp 10 miliar</option>
                            </select>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel required" for="q318d_area">d. Luas tanah yang digunakan untuk usaha (m persegi)</label>
                            <input type="number" id="q318d_area" name="blok3b_nonindustri[q318d_area]" class="form-control" min="0" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q318d_area'] ?? '' }}" placeholder="0" required>
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
                            <input type="number" id="q319a" name="blok3b_nonindustri[q319a]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319a'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319b">b. Lembaga Nonprofit yang Melayani Rumah Tangga</label>
                            <input type="number" id="q319b" name="blok3b_nonindustri[q319b]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319b'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319c">c. Korporasi Publik</label>
                            <input type="number" id="q319c" name="blok3b_nonindustri[q319c]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319c'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319d">d. Korporasi Non Publik</label>
                            <input type="number" id="q319d" name="blok3b_nonindustri[q319d]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319d'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319e">e. Pemerintah</label>
                            <input type="number" id="q319e" name="blok3b_nonindustri[q319e]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319e'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319f">f. Asing</label>
                            <input type="number" id="q319f" name="blok3b_nonindustri[q319f]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319f'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319g_display">g. Total (otomatis)</label>
                            <input type="number" id="q319g_display" class="form-control readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q319g]" id="q319g" value="{{ $surveyResponse->blok3b_nonindustri_data['q319g'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok3a" class="btn btn-secondary">
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
                <span>* 310 otomatis dijumlahkan.</span>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
// Set up survey routes for the JavaScript module
window.surveyRoutes = {
    autoSave: '{{ route("survey.sibstr.blok3b.nonindustri.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok3b.nonindustri.save") }}',
    status: '{{ route("survey.sibstr.blok3b.nonindustri.status") }}',
    backToBlok2: '{{ route("survey.sibstr.blok2") }}',
    nextBlok: '{{ route("survey.sibstr.blok4") }}',
    blok3b_nonindustri: '{{ route("survey.sibstr.blok3b.nonindustri") }}'
};

// Back navigation click handler (align with Industri block)
document.addEventListener('DOMContentLoaded', function() {
    const backBtn = document.getElementById('back-to-blok3a');
    if (backBtn && window.surveyRoutes && window.surveyRoutes.backToBlok2) {
        backBtn.addEventListener('click', function() {
            window.location.href = window.surveyRoutes.backToBlok2;
        });
    }
});

// Existing data
window.surveyData = {
    blok3b: @json($surveyResponse->blok3b_nonindustri_data ?? [])
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok3b-nonindustri.js') }}"></script>
@endpush
@endsection