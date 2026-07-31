@extends('layouts.user-dashboard')

@section('title', 'SIBSTR — Blok IIIB: Pendapatan & Pengeluaran (Non-Industri)')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<link rel="stylesheet" href="{{ asset('css/sibstr-form.css') }}">
@endpush

@section('dashboard-content')
@include('survey.sibstr.partials.page-head', [
    'blokTitle' => 'Blok IIIB — Pendapatan & Pengeluaran',
    'blokSub'   => 'Alur Non-Industri (KBLI di luar 10–33)',
])
<div class="survey-container">
    @include('survey.sibstr.partials.blok-toolbar', [
        'instruction' => 'Pendapatan, persediaan, dan pengeluaran perusahaan. Isi <strong>0</strong> untuk komponen yang tidak ada — nilai tidak boleh negatif.',
    ])

    <!-- Auto-save Status -->
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    <!-- Survey Form -->
    <form id="survey-form" class="survey-form" data-aos="fade-up" data-aos-delay="200">
        @csrf

        @php
            $isTriwulanan = ($triwulan ?? 0) > 0;
            $twLabels = ['satu','dua','tiga','empat'];
            $twLabel  = $isTriwulanan ? ($twLabels[($triwulan - 1)] ?? 'satu') : '';
            $twAwal   = $isTriwulanan ? match((int)$triwulan) {
                1 => "1 Januari {$tahun}", 2 => "1 April {$tahun}",
                3 => "1 Juli {$tahun}",    4 => "1 Oktober {$tahun}", default => ''
            } : '';
            $twAkhir  = $isTriwulanan ? match((int)$triwulan) {
                1 => "31 Maret {$tahun}",     2 => "30 Juni {$tahun}",
                3 => "30 September {$tahun}", 4 => "31 Desember {$tahun}", default => ''
            } : '';
        @endphp

        @if($isTriwulanan)
        {{-- ===== TRIWULANAN FORM: Q303–Q315 ===== --}}

        <!-- PENDAPATAN PERUSAHAAN (Triwulanan) -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">PENDAPATAN PERUSAHAAN</h3>
                <p class="section-subtitle">Mencatat semua pendapatan selain PPN, setelah dikurangi diskon dan retur penjualan.</p>
            </div>
            <div class="form-grid">
                {{-- Q303 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">303.</span>
                        <span>Nilai pendapatan dari penjualan barang dan jasa perusahaan pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <input type="text" id="q303_tw_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q303]" required>
                    <input type="hidden" name="blok3b_nonindustri[q303]" id="q303_tw" value="{{ $surveyResponse->blok3b_nonindustri_data['q303'] ?? '' }}">
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Barang yang dijual baik yang diproduksi sendiri maupun tidak</li>
                                    <li>✓ Penjualan ekspor (FOB-Free On Board)</li>
                                    <li>✓ Penjualan atau transfer ke rekan bisnis/ organisasi atau cabang di luar negeri</li>
                                    <li>✓ Pendapatan dari pengangkutan barang yang tidak dijual perusahaan</li>
                                    <li>✓ Pendapatan jasa perbaikan dan layanan</li>
                                    <li>✓ Pendapatan dari kontrak, subkontrak dan komisi</li>
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
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>
                {{-- Q304 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">304.</span>
                        <span>Pendapatan royalti, bunga, deviden dan lainnya yang diterima perusahaan pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <input type="text" id="q304_tw_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q304]" required>
                    <input type="hidden" name="blok3b_nonindustri[q304]" id="q304_tw" value="{{ $surveyResponse->blok3b_nonindustri_data['q304'] ?? '' }}">
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
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>
                {{-- Q305 (auto total 303+304) --}}
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">305.</span>
                        <span>Total pendapatan pada triwulan {{ $twLabel }} (Jumlah rincian 303 dan 304)</span>
                    </label>
                    <input type="text" id="q305_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q305]" readonly style="background-color:#e9ecef">
                    <input type="hidden" name="blok3b_nonindustri[q305]" id="q305" value="{{ $surveyResponse->blok3b_nonindustri_data['q305'] ?? '' }}">
                    <div class="form-hint">
                        <div class="hint-note text-muted">Otomatis terisi dari penjumlahan 303 dan 304.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PERSEDIAAN (INVENTORI) - Triwulanan -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">PERSEDIAAN (INVENTORI)</h3>
                <p class="section-subtitle">Persediaan/inventori adalah barang yang dikuasai dan ditahan oleh suatu unit dengan tujuan untuk digunakan sendiri, dijual, atau diberikan pada unit lain di waktu mendatang.</p>
            </div>
            <div class="form-grid">
                {{-- Q306 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">306.</span>
                        <span>Nilai Persediaan Bahan baku, bahan bakar, dan sebagainya pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label>
                            <input type="text" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q306_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q306_awal]" value="{{ $surveyResponse->blok3b_nonindustri_data['q306_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label>
                            <input type="text" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q306_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q306_akhir]" value="{{ $surveyResponse->blok3b_nonindustri_data['q306_akhir'] ?? '' }}">
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
                        <div class="hint-note text-muted">Tidak bisa negatif. Periode muncul otomatis sesuai dengan waktu pendataan.</div>
                    </div>
                </div>
                {{-- Q307 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">307.</span>
                        <span>Nilai Persediaan Barang Dalam Proses pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label>
                            <input type="text" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q307_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q307_awal]" value="{{ $surveyResponse->blok3b_nonindustri_data['q307_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label>
                            <input type="text" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q307_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q307_akhir]" value="{{ $surveyResponse->blok3b_nonindustri_data['q307_akhir'] ?? '' }}">
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
                        <div class="hint-note text-muted">Tidak bisa negatif. Periode muncul otomatis sesuai dengan waktu pendataan.</div>
                    </div>
                </div>
                {{-- Q308 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">308.</span>
                        <span>Nilai Persediaan Barang jadi (termasuk persediaan untuk dijual kembali) pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label>
                            <input type="text" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q308_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q308_awal]" value="{{ $surveyResponse->blok3b_nonindustri_data['q308_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label>
                            <input type="text" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q308_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q308_akhir]" value="{{ $surveyResponse->blok3b_nonindustri_data['q308_akhir'] ?? '' }}">
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
                        <div class="hint-note text-muted">Tidak bisa negatif. Periode muncul otomatis sesuai dengan waktu pendataan.</div>
                    </div>
                </div>
                {{-- Q309 (auto total) --}}
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">309.</span>
                        <span>Total persediaan pada triwulan {{ $twLabel }} (Jumlah rincian 306 s.d 308)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label>
                            <input type="text" id="q309_ni_awal_display" class="form-control currency-display readonly" placeholder="0" readonly style="background-color:#e9ecef" data-target-name="blok3b_nonindustri[q309_awal]">
                            <input type="hidden" name="blok3b_nonindustri[q309_awal]" id="q309_ni_awal_val" value="{{ $surveyResponse->blok3b_nonindustri_data['q309_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label>
                            <input type="text" id="q309_ni_akhir_display" class="form-control currency-display readonly" placeholder="0" readonly style="background-color:#e9ecef" data-target-name="blok3b_nonindustri[q309_akhir]">
                            <input type="hidden" name="blok3b_nonindustri[q309_akhir]" id="q309_ni_akhir_val" value="{{ $surveyResponse->blok3b_nonindustri_data['q309_akhir'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Otomatis terisi dari penjumlahan 306 s.d 308.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ITEM PENGELUARAN PERUSAHAAN (Triwulanan) -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3>
                <p class="section-subtitle">Mencatat semua biaya pengeluaran (tidak termasuk PPN dan diskon neto yang diberikan).</p>
            </div>
            <div class="form-grid">
                {{-- Q310 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">310.</span>
                        <span>Total upah dan gaji, serta jaminan sosial pegawai selama pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <input type="text" id="q310_ni_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q310_tw]" required>
                    <input type="hidden" name="blok3b_nonindustri[q310_tw]" id="q310_ni" value="{{ $surveyResponse->blok3b_nonindustri_data['q310_tw'] ?? '' }}">
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <div class="hint-heading">Termasuk:</div>
                                <ul class="hint-list">
                                    <li>✓ Upah dan gaji pegawai/karyawan yang telah dikeluarkan ringkasan pembayarannya (group certificate)</li>
                                    <li>✓ Komisi dan tips untuk pegawai/karyawan</li>
                                    <li>✓ Bonus</li>
                                    <li>✓ Pembayaran Cuti tahunan dan jenis cuti lainnya</li>
                                </ul>
                            </div>
                            <div class="hint-col">
                                <div class="hint-heading">Tidak termasuk:</div>
                                <ul class="hint-list">
                                    <li>⮾ Upah dan gaji yang dikapitalisasi</li>
                                    <li>⮾ Pembayaran untuk konsultan dan kontraktor yang berusaha sendiri</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>
                {{-- Q311 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">311.</span>
                        <span>Penambahan aset tetap (kecuali pembelian tanah) pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <input type="text" id="q311_ni_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q311_tw]" required>
                    <input type="hidden" name="blok3b_nonindustri[q311_tw]" id="q311_ni" value="{{ $surveyResponse->blok3b_nonindustri_data['q311_tw'] ?? '' }}">
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Catatan: Penambahan aset tetap mencakup: Pembelian, barter, produksi sendiri, dan sewa beli (financial lease) aset tetap; Pemberian/transfer/hibah dari pihak lain; Perbaikan besar aset tetap guna meningkatkan kapasitas produksi dan usia pakai; dan Biaya alih kepemilikan atas aset nonfinansial yang tidak diproduksi.</div>
                    </div>
                </div>
                {{-- Q312 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">312.</span>
                        <span>Biaya produksi (pemakaian bahan baku dan penolong) pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <input type="text" id="q312_ni_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q312_tw]" required>
                    <input type="hidden" name="blok3b_nonindustri[q312_tw]" id="q312_ni" value="{{ $surveyResponse->blok3b_nonindustri_data['q312_tw'] ?? '' }}">
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
                                    <li>⮾ Pembelian barang yang dikapitalisasi</li>
                                    <li>⮾ Perubahan persediaan</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Catatan: Mencakup seluruh nilai barang dan jasa yang digunakan sebagai bahan baku dalam proses produksi, tidak termasuk aset tetap. Tidak bisa negatif.</div>
                    </div>
                </div>
                {{-- Q313 --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">313.</span>
                        <span>Biaya operasional (air, listrik, gas, pemeliharaan, biaya angkutan) pada triwulan {{ $twLabel }} (rupiah)</span>
                    </label>
                    <input type="text" id="q313_ni_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q313_tw]" required>
                    <input type="hidden" name="blok3b_nonindustri[q313_tw]" id="q313_ni" value="{{ $surveyResponse->blok3b_nonindustri_data['q313_tw'] ?? '' }}">
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-grid">
                            <div class="hint-col">
                                <ul class="hint-list">
                                    <li>✓ Pengeluaran listrik, bahan bakar dan air</li>
                                    <li>✓ Pembelian bahan perkantoran umum</li>
                                    <li>✓ Pembelian komponen dan bahan bakar untuk kendaraan bermotor</li>
                                    <li>✓ Pembayaran ke pihak lain untuk kargo, delivery, dan jasa angkutan</li>
                                    <li>✓ Pembayaran sewa operasi (dengan atau tanpa operator)</li>
                                    <li>✓ Biaya lisensi software komputer yang berumur kurang dari satu tahun</li>
                                </ul>
                            </div>
                        </div>
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EKSPOR IMPOR LUAR NEGERI (Triwulanan) -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">EKSPOR IMPOR LUAR NEGERI</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">314.</span>
                        <span>Berapa persentase nilai produksi yang dijual sebagai produk ekspor luar negeri (%)</span>
                    </label>
                    <input type="number" id="q314_ni_tw" name="blok3b_nonindustri[q314_tw]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q314_tw'] ?? '' }}" placeholder="0" required>
                    <div class="form-errors"></div>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">315.</span>
                        <span>Berapa persentase nilai bahan baku dan bahan penolong yang diperoleh melalui impor luar negeri langsung atau melalui jasa importir (%)</span>
                    </label>
                    <input type="number" id="q315_ni_tw" name="blok3b_nonindustri[q315_tw]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q315_tw'] ?? '' }}" placeholder="0" required>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Maksimal 100%.</div>
                    </div>
                </div>
            </div>
        </div>

        @else
        {{-- ===== TAHUNAN FORM (existing) ===== --}}

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

        <!-- PERSEDIAAN (INVENTORI) - TAHUNAN 2025 -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">PERSEDIAAN (INVENTORI)</h3>
                <p class="section-subtitle">Nilai persediaan per 1 Januari 2025 dan 31 Desember 2025</p>
            </div>
            <div class="form-grid">
                <!-- Q307 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">307.</span>
                        <span>Nilai stok bahan baku, bahan penolong, bahan bakar, bahan pembungkus, dan lain-lain (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307a_display">a. Kondisi 1 Januari 2025 (Rp)</label>
                            <input type="text" id="q307a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q306_year_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q306_year_awal]" id="q307a" value="{{ $surveyResponse->blok3b_nonindustri_data['q306_year_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307b_display">b. Kondisi 31 Desember 2025 (Rp)</label>
                            <input type="text" id="q307b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q306_year_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q306_year_akhir]" id="q307b" value="{{ $surveyResponse->blok3b_nonindustri_data['q306_year_akhir'] ?? '' }}">
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
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>

                <!-- Q308 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">308.</span>
                        <span>Nilai stok barang produksi setengah jadi (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308a_display">a. Kondisi 1 Januari 2025 (Rp)</label>
                            <input type="text" id="q308a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q307_year_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q307_year_awal]" id="q308a" value="{{ $surveyResponse->blok3b_nonindustri_data['q307_year_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308b_display">b. Kondisi 31 Desember 2025 (Rp)</label>
                            <input type="text" id="q308b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q307_year_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q307_year_akhir]" id="q308b" value="{{ $surveyResponse->blok3b_nonindustri_data['q307_year_akhir'] ?? '' }}">
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
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>

                <!-- Q309 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">309.</span>
                        <span>Nilai stok barang jadi yang dihasilkan (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q309a_display">a. Kondisi 1 Januari 2025 (Rp)</label>
                            <input type="text" id="q309a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q308_year_awal]" required>
                            <input type="hidden" name="blok3b_nonindustri[q308_year_awal]" id="q309a" value="{{ $surveyResponse->blok3b_nonindustri_data['q308_year_awal'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q309b_display">b. Kondisi 31 Desember 2025 (Rp)</label>
                            <input type="text" id="q309b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q308_year_akhir]" required>
                            <input type="hidden" name="blok3b_nonindustri[q308_year_akhir]" id="q309b" value="{{ $surveyResponse->blok3b_nonindustri_data['q308_year_akhir'] ?? '' }}">
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
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>

                <!-- Q310 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">310.</span>
                        <span>Nilai pembelian/penambahan dan pembuatan/perbaikan besar seluruh barang modal tetap (tanah, gedung, mesin, perlengkapan, kendaraan, software/database, dan lainnya) pada tahun 2025 (Rp)</span>
                    </label>
                    <input type="text" id="q310_beli_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q310_beli_modal]" required>
                    <input type="hidden" name="blok3b_nonindustri[q310_beli_modal]" id="q310_beli_modal" value="{{ $surveyResponse->blok3b_nonindustri_data['q310_beli_modal'] ?? '' }}">
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>

                <!-- Q311 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">311.</span>
                        <span>Nilai penjualan/pengurangan seluruh barang modal tetap (tanah, gedung, mesin, perlengkapan, kendaraan, software/database, dan lainnya) pada tahun 2025 (Rp)</span>
                    </label>
                    <input type="text" id="q311_jual_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q311_jual_modal]" required>
                    <input type="hidden" name="blok3b_nonindustri[q311_jual_modal]" id="q311_jual_modal" value="{{ $surveyResponse->blok3b_nonindustri_data['q311_jual_modal'] ?? '' }}">
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>

                <!-- Q312 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">312.</span>
                        <span>Nilai taksiran seluruh barang modal tetap (tanah, gedung, mesin, perlengkapan, kendaraan, software/database, dan lainnya) menurut harga berlaku per 31 Desember 2025 (Rp)</span>
                    </label>
                    <input type="text" id="q312_taksir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q312_taksir_modal]" required>
                    <input type="hidden" name="blok3b_nonindustri[q312_taksir_modal]" id="q312_taksir_modal" value="{{ $surveyResponse->blok3b_nonindustri_data['q312_taksir_modal'] ?? '' }}">
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
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
                        <span class="question-number">313.</span>
                        <span>Pengeluaran untuk pekerja/karyawan (tidak termasuk pekerja outsourcing) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_a1_display">a.1 Upah/gaji, lembur, dan tunjangan pekerja produksi (Rp)</label>
                            <input type="text" id="q313_a1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q313_a1]" required>
                            <input type="hidden" name="blok3b_nonindustri[q313_a1]" id="q313_a1" value="{{ $surveyResponse->blok3b_nonindustri_data['q313_a1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_a2_display">a.2 Pengeluaran lain untuk pekerja produksi (Rp)</label>
                            <input type="text" id="q313_a2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q313_a2]" required>
                            <input type="hidden" name="blok3b_nonindustri[q313_a2]" id="q313_a2" value="{{ $surveyResponse->blok3b_nonindustri_data['q313_a2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_b1_display">b.1 Upah/gaji, lembur, dan tunjangan pekerja lainnya (Rp)</label>
                            <input type="text" id="q313_b1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q313_b1]" required>
                            <input type="hidden" name="blok3b_nonindustri[q313_b1]" id="q313_b1" value="{{ $surveyResponse->blok3b_nonindustri_data['q313_b1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_b2_display">b.2 Pengeluaran lain untuk pekerja lainnya (Rp)</label>
                            <input type="text" id="q313_b2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q313_b2]" required>
                            <input type="hidden" name="blok3b_nonindustri[q313_b2]" id="q313_b2" value="{{ $surveyResponse->blok3b_nonindustri_data['q313_b2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_c_display">c. Total (a.1+a.2+b.1+b.2) (Rp)</label>
                            <input type="text" id="q313_c_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q313_c]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q313_c]" id="q313_c" value="{{ $surveyResponse->blok3b_nonindustri_data['q313_c'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Termasuk pajak penghasilan untuk pekerja/karyawan, pajak perseorangan. Contoh pengeluaran lainnya: Bonus, hadiah, premi, dll. Tidak bisa negatif. Total (c) terisi otomatis.</div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">314.</span>
                        <span>Pengeluaran untuk pekerja/karyawan Outsourcing (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_a1_display">a.1 Upah/gaji, lembur, dan tunjangan pekerja produksi (Rp)</label>
                            <input type="text" id="q314_a1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q314_a1]" required>
                            <input type="hidden" name="blok3b_nonindustri[q314_a1]" id="q314_a1" value="{{ $surveyResponse->blok3b_nonindustri_data['q314_a1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_a2_display">a.2 Pengeluaran lain untuk pekerja produksi (Rp)</label>
                            <input type="text" id="q314_a2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q314_a2]" required>
                            <input type="hidden" name="blok3b_nonindustri[q314_a2]" id="q314_a2" value="{{ $surveyResponse->blok3b_nonindustri_data['q314_a2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_b1_display">b.1 Upah/gaji, lembur, dan tunjangan pekerja lainnya (Rp)</label>
                            <input type="text" id="q314_b1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q314_b1]" required>
                            <input type="hidden" name="blok3b_nonindustri[q314_b1]" id="q314_b1" value="{{ $surveyResponse->blok3b_nonindustri_data['q314_b1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_b2_display">b.2 Pengeluaran lain untuk pekerja lainnya (Rp)</label>
                            <input type="text" id="q314_b2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q314_b2]" required>
                            <input type="hidden" name="blok3b_nonindustri[q314_b2]" id="q314_b2" value="{{ $surveyResponse->blok3b_nonindustri_data['q314_b2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_c_display">c. Total (a.1+a.2+b.1+b.2) (Rp)</label>
                            <input type="text" id="q314_c_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_nonindustri[q314_c]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q314_c]" id="q314_c" value="{{ $surveyResponse->blok3b_nonindustri_data['q314_c'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Tidak bisa negatif. Total (c) terisi otomatis.</div>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">315.</span>
                        <span>Penggunaan listrik yang dipakai oleh perusahaan</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_a">a. Daya tersambung dari PLN yang dipakai oleh perusahaan (VA)</label>
                            <input type="number" id="q315_a" name="blok3b_nonindustri[q315_a]" class="form-control" placeholder="0" min="0" step="1" value="{{ $surveyResponse->blok3b_nonindustri_data['q315_a'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_b">b. Daya tersambung dari Non PLN yang dipakai oleh perusahaan (VA)</label>
                            <input type="number" id="q315_b" name="blok3b_nonindustri[q315_b]" class="form-control" placeholder="0" min="0" step="1" value="{{ $surveyResponse->blok3b_nonindustri_data['q315_b'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_c">c. Banyaknya penggunaan listrik dari PLN (kWh)</label>
                            <input type="number" id="q315_c" name="blok3b_nonindustri[q315_c]" class="form-control" placeholder="0" min="0" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q315_c'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_d">d. Banyaknya penggunaan listrik dari Non PLN (kWh)</label>
                            <input type="number" id="q315_d" name="blok3b_nonindustri[q315_d]" class="form-control" placeholder="0" min="0" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q315_d'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_e_display">e. Pengeluaran listrik yang dipakai oleh perusahaan (Rp)</label>
                            <input type="text" id="q315_e_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q315_e]" required>
                            <input type="hidden" name="blok3b_nonindustri[q315_e]" id="q315_e" value="{{ $surveyResponse->blok3b_nonindustri_data['q315_e'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Tidak bisa negatif.</div>
                    </div>
                </div>

                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">316.</span>
                        <span>Biaya produksi (pemakaian bahan baku dan penolong) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q312_display">a. Satu triwulan yang lalu</label>
                            <input type="text" id="q312_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q312]" required>
                            <input type="hidden" name="blok3b_nonindustri[q312]" id="q312" value="{{ $surveyResponse->blok3b_nonindustri_data['q312'] ?? '' }}">
                        </div>
                        @if(($triwulan ?? 0) == 0)
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q312_year_display">b. Selama tahun 2025</label>
                            <input type="text" id="q312_year_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q312_year]" required>
                            <input type="hidden" name="blok3b_nonindustri[q312_year]" id="q312_year" value="{{ $surveyResponse->blok3b_nonindustri_data['q312_year'] ?? '' }}">
                        </div>
                        @endif
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
                        <span class="question-number">317.</span>
                        @if(($triwulan ?? 0) > 0)
                        <span>Biaya operasional perusahaan pada triwulan ini (rupiah)</span>
                        @else
                        <span>Pengeluaran perusahaan selama tahun 2025 (rupiah)</span>
                        @endif
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_a_display">a. Biaya operasional (air, listrik, gas, pemeliharaan, biaya angkutan)</label>
                            <input type="text" id="q317_a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_a]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_a]" id="q317_a" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_a'] ?? '' }}">
                        </div>
                        @if(($triwulan ?? 0) == 0)
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_b_display">b. Biaya Non operasional (bunga pinjaman, pajak, premi asuransi, nilai hadiah/sumbangan)</label>
                            <input type="text" id="q317_b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_b]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_b]" id="q317_b" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_b'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_c1_display">c.1 Sewa/kontrak gedung, mesin, serta alat-alat (Rp)</label>
                            <input type="text" id="q317_c1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_c1]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_c1]" id="q317_c1" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_c1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_c2_display">c.2 Sewa/kontrak tanah (Rp)</label>
                            <input type="text" id="q317_c2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_c2]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_c2]" id="q317_c2" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_c2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_d_display">d. Pajak/Tax (Rp)</label>
                            <input type="text" id="q317_d_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_d]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_d]" id="q317_d" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_d'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_e_display">e. Nilai bunga atas pinjaman (Rp)</label>
                            <input type="text" id="q317_e_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_e]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_e]" id="q317_e" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_e'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_f_display">f. Nilai hadiah, sumbangan, derma dan sejenisnya (Rp)</label>
                            <input type="text" id="q317_f_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_f]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_f]" id="q317_f" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_f'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_g_display">g. Nilai dividen/laba yang dibagikan (Rp)</label>
                            <input type="text" id="q317_g_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_g]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_g]" id="q317_g" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_g'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_h_display">h. Nilai premi asuransi kerugian yang dibayarkan (Rp)</label>
                            <input type="text" id="q317_h_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_h]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_h]" id="q317_h" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_h'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_i_display">i. Nilai jasa industri (maklun) yang dibayarkan ke pihak lain (Rp)</label>
                            <input type="text" id="q317_i_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_i]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_i]" id="q317_i" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_i'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_j_display">j. Air (selain untuk bahan baku dan penolong) (Rp)</label>
                            <input type="text" id="q317_j_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_j]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_j]" id="q317_j" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_j'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_k_display">k. Pengeluaran lainnya (Rp)</label>
                            <input type="text" id="q317_k_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q317_k]" required>
                            <input type="hidden" name="blok3b_nonindustri[q317_k]" id="q317_k" value="{{ $surveyResponse->blok3b_nonindustri_data['q317_k'] ?? '' }}">
                        </div>
                        @endif
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">
                            @if(($triwulan ?? 0) > 0)
                            Tidak bisa negatif.
                            @else
                            Selama tahun 2025. Sub c = sewa/kontrak. Sub d = Termasuk Pajak Badan, PBB, BPHTB, Pajak Kendaraan; tidak termasuk PPh karyawan. Tidak bisa negatif.
                            @endif
                        </div>
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
                        <span class="question-number">319.</span>
                        <span>Berapa persentase nilai produksi yang dijual sebagai produk ekspor luar negeri (%)</span>
                    </label>
                    <input type="number" id="q314" name="blok3b_nonindustri[q314]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q314'] ?? '' }}" placeholder="0" required>
                </div>
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">320.</span>
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

        @if(($triwulan ?? 0) == 0)
        <!-- NILAI ASET -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">NILAI ASET</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">321.</span>
                        <span>Nilai aset pada 31 Desember 2025 (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318a_display">a. Tanah dan bangunan</label>
                            <input type="text" id="q318a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q318a]" required>
                            <input type="hidden" name="blok3b_nonindustri[q318a]" id="q318a" value="{{ $surveyResponse->blok3b_nonindustri_data['q318a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q318b_display">b. Selain tanah dan bangunan</label>
                            <input type="text" id="q318b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_nonindustri[q318b]" required>
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
                        <span class="question-number">322.</span>
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
                            <label class="form-sublabel" for="q319e">e. Pemerintah Pusat</label>
                            <input type="number" id="q319e" name="blok3b_nonindustri[q319e]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319e'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319f">f. Pemerintah Daerah</label>
                            <input type="number" id="q319f" name="blok3b_nonindustri[q319f]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319f'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319g">g. Perusahaan Swasta Nasional</label>
                            <input type="number" id="q319g" name="blok3b_nonindustri[q319g]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319g'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319h">h. Asing</label>
                            <input type="number" id="q319h" name="blok3b_nonindustri[q319h]" class="form-control percent-input" min="0" max="100" step="0.01" value="{{ $surveyResponse->blok3b_nonindustri_data['q319h'] ?? '' }}" placeholder="0" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q319i_display">i. Total (otomatis)</label>
                            <input type="number" id="q319i_display" class="form-control readonly" placeholder="0" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_nonindustri[q319i]" id="q319i" value="{{ $surveyResponse->blok3b_nonindustri_data['q319i'] ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @endif {{-- end @else (tahunan) --}}

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
                <span>* Tidak boleh negatif.</span>
            </div>
        </div>
    </form>

@if(!empty($historicalResponses) && !(isset($triwulan) && $triwulan === 1))
@include('survey.sibstr.partials.historical-drawer', [
    'historicalResponses' => $historicalResponses,
    'blockKey'            => 'blok3b_nonindustri',
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
    autoSave:           '{{ route("survey.sibstr.blok3b.nonindustri.autosave", ["year" => $tahun, "period" => $period]) }}',
    saveAll:            '{{ route("survey.sibstr.blok3b.nonindustri.save",     ["year" => $tahun, "period" => $period]) }}',
    status:             '{{ route("survey.sibstr.blok3b.nonindustri.status",   ["year" => $tahun, "period" => $period]) }}',
    backToBlok2:        '{{ route("survey.sibstr.blok2",                       ["year" => $tahun, "period" => $period]) }}',
    nextBlok:           '{{ ($triwulan ?? 0) > 0 ? route("survey.sibstr.blok5", ["year" => $tahun, "period" => $period]) : route("survey.sibstr.blok4", ["year" => $tahun, "period" => $period]) }}',
    blok3b_nonindustri: '{{ route("survey.sibstr.blok3b.nonindustri",          ["year" => $tahun, "period" => $period]) }}'
};
@endif

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