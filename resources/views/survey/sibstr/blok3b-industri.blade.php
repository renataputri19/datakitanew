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
            Persediaan stok, barang modal, dan pengeluaran perusahaan tahun 2025
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

        {{-- Q304 (Pendapatan royalti) and Q305 (usaha online) removed from this block --}}

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
                            <input type="text" id="q307a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q306_year_awal]">
                            <input type="hidden" name="blok3b_industri[q306_year_awal]" id="q307a" value="{{ $surveyResponse->blok3b_industri_data['q306_year_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q307b_display">b. Kondisi 31 Desember 2025 (Rp)</label>
                            <input type="text" id="q307b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q306_year_akhir]">
                            <input type="hidden" name="blok3b_industri[q306_year_akhir]" id="q307b" value="{{ $surveyResponse->blok3b_industri_data['q306_year_akhir'] ?? '' }}" required>
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
                            <input type="text" id="q308a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q307_year_awal]">
                            <input type="hidden" name="blok3b_industri[q307_year_awal]" id="q308a" value="{{ $surveyResponse->blok3b_industri_data['q307_year_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q308b_display">b. Kondisi 31 Desember 2025 (Rp)</label>
                            <input type="text" id="q308b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q307_year_akhir]">
                            <input type="hidden" name="blok3b_industri[q307_year_akhir]" id="q308b" value="{{ $surveyResponse->blok3b_industri_data['q307_year_akhir'] ?? '' }}" required>
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
                            <input type="text" id="q309a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q308_year_awal]">
                            <input type="hidden" name="blok3b_industri[q308_year_awal]" id="q309a" value="{{ $surveyResponse->blok3b_industri_data['q308_year_awal'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q309b_display">b. Kondisi 31 Desember 2025 (Rp)</label>
                            <input type="text" id="q309b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q308_year_akhir]">
                            <input type="hidden" name="blok3b_industri[q308_year_akhir]" id="q309b" value="{{ $surveyResponse->blok3b_industri_data['q308_year_akhir'] ?? '' }}" required>
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
                    <input type="text" id="q310_beli_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q310_beli_modal]">
                    <input type="hidden" name="blok3b_industri[q310_beli_modal]" id="q310_beli_modal" value="{{ $surveyResponse->blok3b_industri_data['q310_beli_modal'] ?? '' }}" required>
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
                    <input type="text" id="q311_jual_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q311_jual_modal]">
                    <input type="hidden" name="blok3b_industri[q311_jual_modal]" id="q311_jual_modal" value="{{ $surveyResponse->blok3b_industri_data['q311_jual_modal'] ?? '' }}" required>
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
                    <input type="text" id="q312_taksir_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q312_taksir_modal]">
                    <input type="hidden" name="blok3b_industri[q312_taksir_modal]" id="q312_taksir_modal" value="{{ $surveyResponse->blok3b_industri_data['q312_taksir_modal'] ?? '' }}" required>
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
                            <input type="text" id="q313_a1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q313_a1]" required>
                            <input type="hidden" name="blok3b_industri[q313_a1]" id="q313_a1" value="{{ $surveyResponse->blok3b_industri_data['q313_a1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_a2_display">a.2 Pengeluaran lain untuk pekerja produksi (Rp)</label>
                            <input type="text" id="q313_a2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q313_a2]" required>
                            <input type="hidden" name="blok3b_industri[q313_a2]" id="q313_a2" value="{{ $surveyResponse->blok3b_industri_data['q313_a2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_b1_display">b.1 Upah/gaji, lembur, dan tunjangan pekerja lainnya (Rp)</label>
                            <input type="text" id="q313_b1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q313_b1]" required>
                            <input type="hidden" name="blok3b_industri[q313_b1]" id="q313_b1" value="{{ $surveyResponse->blok3b_industri_data['q313_b1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_b2_display">b.2 Pengeluaran lain untuk pekerja lainnya (Rp)</label>
                            <input type="text" id="q313_b2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q313_b2]" required>
                            <input type="hidden" name="blok3b_industri[q313_b2]" id="q313_b2" value="{{ $surveyResponse->blok3b_industri_data['q313_b2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q313_c_display">c. Total (a.1+a.2+b.1+b.2) (Rp)</label>
                            <input type="text" id="q313_c_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_industri[q313_c]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q313_c]" id="q313_c" value="{{ $surveyResponse->blok3b_industri_data['q313_c'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Termasuk pajak penghasilan untuk pekerja/karyawan, pajak perseorangan. Contoh pengeluaran lainnya: Bonus, hadiah, premi, dll. Tidak bisa negatif. Total (c) terisi otomatis.</div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">314.</span>
                        <span>Pengeluaran untuk pekerja/karyawan Outsourcing (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_a1_display">a.1 Upah/gaji, lembur, dan tunjangan pekerja produksi (Rp)</label>
                            <input type="text" id="q314_a1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q314_a1]">
                            <input type="hidden" name="blok3b_industri[q314_a1]" id="q314_a1" value="{{ $surveyResponse->blok3b_industri_data['q314_a1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_a2_display">a.2 Pengeluaran lain untuk pekerja produksi (Rp)</label>
                            <input type="text" id="q314_a2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q314_a2]">
                            <input type="hidden" name="blok3b_industri[q314_a2]" id="q314_a2" value="{{ $surveyResponse->blok3b_industri_data['q314_a2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_b1_display">b.1 Upah/gaji, lembur, dan tunjangan pekerja lainnya (Rp)</label>
                            <input type="text" id="q314_b1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q314_b1]">
                            <input type="hidden" name="blok3b_industri[q314_b1]" id="q314_b1" value="{{ $surveyResponse->blok3b_industri_data['q314_b1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_b2_display">b.2 Pengeluaran lain untuk pekerja lainnya (Rp)</label>
                            <input type="text" id="q314_b2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q314_b2]">
                            <input type="hidden" name="blok3b_industri[q314_b2]" id="q314_b2" value="{{ $surveyResponse->blok3b_industri_data['q314_b2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q314_c_display">c. Total (a.1+a.2+b.1+b.2) (Rp)</label>
                            <input type="text" id="q314_c_display" class="form-control currency-display readonly" placeholder="0" data-target-name="blok3b_industri[q314_c]" readonly style="background-color:#e9ecef">
                            <input type="hidden" name="blok3b_industri[q314_c]" id="q314_c" value="{{ $surveyResponse->blok3b_industri_data['q314_c'] ?? '' }}">
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
                            <input type="number" id="q315_a" name="blok3b_industri[q315_a]" class="form-control" placeholder="0" min="0" step="1" value="{{ $surveyResponse->blok3b_industri_data['q315_a'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_b">b. Daya tersambung dari Non PLN yang dipakai oleh perusahaan (VA)</label>
                            <input type="number" id="q315_b" name="blok3b_industri[q315_b]" class="form-control" placeholder="0" min="0" step="1" value="{{ $surveyResponse->blok3b_industri_data['q315_b'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_c">c. Banyaknya penggunaan listrik dari PLN (kWh)</label>
                            <input type="number" id="q315_c" name="blok3b_industri[q315_c]" class="form-control" placeholder="0" min="0" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q315_c'] ?? '' }}" required>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_d">d. Banyaknya penggunaan listrik dari Non PLN (kWh)</label>
                            <input type="number" id="q315_d" name="blok3b_industri[q315_d]" class="form-control" placeholder="0" min="0" step="0.01" value="{{ $surveyResponse->blok3b_industri_data['q315_d'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q315_e_display">e. Pengeluaran listrik yang dipakai oleh perusahaan (Rp)</label>
                            <input type="text" id="q315_e_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q315_e]" required>
                            <input type="hidden" name="blok3b_industri[q315_e]" id="q315_e" value="{{ $surveyResponse->blok3b_industri_data['q315_e'] ?? '' }}">
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
                        <span class="question-number">317.</span>
                        <span>Pengeluaran perusahaan selama tahun 2025 (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_a_display">a. Biaya operasional (air, listrik, gas, pemeliharaan, biaya angkutan)</label>
                            <input type="text" id="q317_a_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_a]" required>
                            <input type="hidden" name="blok3b_industri[q317_a]" id="q317_a" value="{{ $surveyResponse->blok3b_industri_data['q317_a'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_b_display">b. Biaya Non operasional (bunga pinjaman, pajak, premi asuransi, nilai hadiah/sumbangan)</label>
                            <input type="text" id="q317_b_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_b]">
                            <input type="hidden" name="blok3b_industri[q317_b]" id="q317_b" value="{{ $surveyResponse->blok3b_industri_data['q317_b'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_c1_display">c.1 Sewa/kontrak gedung, mesin, serta alat-alat (Rp)</label>
                            <input type="text" id="q317_c1_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_c1]">
                            <input type="hidden" name="blok3b_industri[q317_c1]" id="q317_c1" value="{{ $surveyResponse->blok3b_industri_data['q317_c1'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_c2_display">c.2 Sewa/kontrak tanah (Rp)</label>
                            <input type="text" id="q317_c2_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_c2]">
                            <input type="hidden" name="blok3b_industri[q317_c2]" id="q317_c2" value="{{ $surveyResponse->blok3b_industri_data['q317_c2'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_d_display">d. Pajak/Tax (Rp)</label>
                            <input type="text" id="q317_d_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_d]">
                            <input type="hidden" name="blok3b_industri[q317_d]" id="q317_d" value="{{ $surveyResponse->blok3b_industri_data['q317_d'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_e_display">e. Nilai bunga atas pinjaman (Rp)</label>
                            <input type="text" id="q317_e_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_e]">
                            <input type="hidden" name="blok3b_industri[q317_e]" id="q317_e" value="{{ $surveyResponse->blok3b_industri_data['q317_e'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_f_display">f. Nilai hadiah, sumbangan, derma dan sejenisnya (Rp)</label>
                            <input type="text" id="q317_f_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_f]">
                            <input type="hidden" name="blok3b_industri[q317_f]" id="q317_f" value="{{ $surveyResponse->blok3b_industri_data['q317_f'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_g_display">g. Nilai dividen/laba yang dibagikan (Rp)</label>
                            <input type="text" id="q317_g_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_g]">
                            <input type="hidden" name="blok3b_industri[q317_g]" id="q317_g" value="{{ $surveyResponse->blok3b_industri_data['q317_g'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_h_display">h. Nilai premi asuransi kerugian yang dibayarkan (Rp)</label>
                            <input type="text" id="q317_h_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_h]">
                            <input type="hidden" name="blok3b_industri[q317_h]" id="q317_h" value="{{ $surveyResponse->blok3b_industri_data['q317_h'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_i_display">i. Nilai jasa industri (maklun) yang dibayarkan ke pihak lain (Rp)</label>
                            <input type="text" id="q317_i_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_i]">
                            <input type="hidden" name="blok3b_industri[q317_i]" id="q317_i" value="{{ $surveyResponse->blok3b_industri_data['q317_i'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_j_display">j. Air (selain untuk bahan baku dan penolong) (Rp)</label>
                            <input type="text" id="q317_j_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_j]">
                            <input type="hidden" name="blok3b_industri[q317_j]" id="q317_j" value="{{ $surveyResponse->blok3b_industri_data['q317_j'] ?? '' }}">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel" for="q317_k_display">k. Pengeluaran lainnya (Rp)</label>
                            <input type="text" id="q317_k_display" class="form-control currency-display" placeholder="0" data-target-name="blok3b_industri[q317_k]">
                            <input type="hidden" name="blok3b_industri[q317_k]" id="q317_k" value="{{ $surveyResponse->blok3b_industri_data['q317_k'] ?? '' }}">
                        </div>
                    </div>
                    <div class="form-errors"></div>
                    <div class="form-hint">
                        <div class="hint-note text-muted">Selama tahun 2025. Sub c = sewa/kontrak. Sub d = Termasuk Pajak Badan, PBB, BPHTB, Pajak Kendaraan; tidak termasuk PPh karyawan. Tidak bisa negatif.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- EKSPOR IMPOR LUAR NEGERI (Q316, Q317), NILAI ASET (Q318), KEPEMILIKAN MODAL (Q319) moved to Blok IIIC --}}

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
                <span>* Tidak boleh negatif.</span>
            </div>
        </div>
    </form>
</div>

@if(!empty($historicalResponses))
@include('survey.sibstr.partials.historical-drawer', [
    'historicalResponses' => $historicalResponses,
    'blockKey'            => 'blok3b_industri',
])
@endif

@push('scripts')
<script>
// Set up survey routes for the JavaScript module
@if(isset($editRoutes) && !empty($editRoutes))
window.surveyRoutes = @json($editRoutes);
@else
window.surveyRoutes = {
    autoSave:    '{{ route("survey.sibstr.blok3b.industri.autosave", ["year" => $tahun, "period" => $period]) }}',
    saveAll:     '{{ route("survey.sibstr.blok3b.industri.save",     ["year" => $tahun, "period" => $period]) }}',
    status:      '{{ route("survey.sibstr.blok3b.industri.status",   ["year" => $tahun, "period" => $period]) }}',
    backToBlok3a:'{{ route("survey.sibstr.blok3a",                   ["year" => $tahun, "period" => $period]) }}',
    nextBlok:    '{{ route("survey.sibstr.blok3c.industri",          ["year" => $tahun, "period" => $period]) }}'
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