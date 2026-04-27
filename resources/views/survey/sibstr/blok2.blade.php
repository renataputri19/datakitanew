@extends('layouts.app')

@section('title', 'SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR) - Blok II - DataKita')
@section('description', 'Survei Industri Besar dan Sedang Triwulanan - Blok II: Pendahuluan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
@endpush

@section('content')
@php
    $currentTriwulan = $triwulan ?? $surveyResponse->triwulan ?? 0;
    $currentTahun    = $tahun    ?? $surveyResponse->tahun    ?? 2025;
    $currentPeriod   = $period   ?? ($currentTriwulan === 0 ? 'tahunan' : (string) $currentTriwulan);
    $isReadOnlyMode  = false; // Triwulanan edit mode is fully editable
@endphp

@if($isReadOnlyMode)
{{-- ── READ-ONLY MODE: historical quarterly data (triwulan > 0) ── --}}
@include('survey.partials.edit-mode-banner', ['exitUrl' => route('dashboard.surveys.sibstr.results')])

<div class="period-indicator mb-4 px-4 py-2 rounded-lg bg-amber-50 border border-amber-200 dark:bg-amber-950/30 dark:border-amber-700 text-sm text-amber-800 dark:text-amber-300 flex items-center gap-2">
    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
    <span>Tampilan Baca-Saja — <strong>{{ \App\Models\SurveyResponse::triwulanLabel($currentTriwulan) }} {{ $currentTahun }}</strong></span>
</div>

@include('survey.sibstr.partials.blok2-readonly')

<div style="padding: 1rem 1.5rem 2rem;">
    <div class="flex items-center gap-4">
        <a href="{{ $editRoutes['backToBlok1'] ?? route('survey.sibstr.edit.blok1', ['year' => $currentTahun, 'period' => $currentPeriod]) }}" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15,18 9,12 15,6"></polyline>
            </svg>
            Kembali ke Bab 1
        </a>
        <a href="{{ $editRoutes['nextBlok'] ?? route('survey.sibstr.edit.blok3a', ['year' => $currentTahun, 'period' => $currentPeriod]) }}" class="btn btn-primary">
            Lanjut ke Bab 3
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9,18 15,12 9,6"></polyline>
            </svg>
        </a>
    </div>
</div>

@else
{{-- ── ACTIVE FORM MODE ── --}}
<div class="survey-container">
    @if(!empty($isEditMode))
    @include('survey.partials.edit-mode-banner', ['exitUrl' => route('dashboard.surveys.sibstr.results')])
    @endif

    @if(isset($triwulan) && $triwulan > 0)
    <div class="period-indicator mb-4 px-4 py-2 rounded-lg bg-blue-50 border border-blue-200 dark:bg-blue-950/30 dark:border-blue-700 text-sm text-blue-800 dark:text-blue-300 flex items-center gap-2">
        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <span>Mengisi untuk: <strong>{{ \App\Models\SurveyResponse::triwulanLabel($triwulan) }} {{ $tahun }}</strong></span>
    </div>
    @endif

    @if(session('q207_required'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-amber-50 border border-amber-300 dark:bg-amber-900/20 dark:border-amber-700 text-sm text-amber-800 dark:text-amber-300 flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <span>Sebelum memulai survei triwulanan 2026, mohon lengkapi <strong>Pertanyaan 209 (Jumlah Pekerja Selama Tahun 2025)</strong> pada formulir ini terlebih dahulu.</span>
    </div>
    @endif

    <!-- Survey Header -->
    <div class="survey-header" data-aos="fade-up">
        <h1 class="survey-title">
            SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR)
        </h1>
        <h2 class="survey-subtitle">
            II. PENDAHULUAN
        </h2>
        <p class="survey-description">
            Formulir survei untuk pengumpulan data industri besar dan sedang triwulanan sesuai standar BPS
        </p>

        @if(isset($referenceResponse) && $referenceResponse && !(isset($triwulan) && $triwulan === 1))
        <div style="margin-top:1rem;">
            <button type="button"
                    onclick="openRefDrawer()"
                    style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.1rem;
                           border-radius:0.625rem;border:2px solid #fbbf24;
                           background:rgba(254,243,199,0.85);color:#92400e;
                           font-size:0.8125rem;font-weight:700;cursor:pointer;
                           transition:background 0.15s,border-color 0.15s,box-shadow 0.15s;
                           box-shadow:0 1px 4px rgba(251,191,36,0.25);"
                    aria-label="Buka panel data referensi untuk perbandingan">
                <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Lihat Data Referensi
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
        <input type="hidden" name="tahun" value="{{ $tahun ?? 2025 }}">
        <input type="hidden" name="triwulan" value="{{ $triwulan ?? 0 }}">

        <!-- Section II: PENDAHULUAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK II. PENDAHULUAN</h3>
            </div>
            <div class="form-grid">

                <!-- Question 201 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">201.</span>
                        <span>Kondisi Perusahaan:</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_masih_aktif" value="masih_aktif"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'masih_aktif' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="kondisi_masih_aktif" class="radio-label">
                                a. Masih Aktif
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_belum_beroperasi" value="belum_beroperasi"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'belum_beroperasi' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="kondisi_belum_beroperasi" class="radio-label">
                                b. Belum Beroperasi
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_tutup" value="tutup"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'tutup' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="kondisi_tutup" class="radio-label">
                                c. Tutup
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_pindah" value="pindah"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'pindah' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="kondisi_pindah" class="radio-label">
                                d. Pindah
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_tidak_ditemukan" value="tidak_ditemukan"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'tidak_ditemukan' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="kondisi_tidak_ditemukan" class="radio-label">
                                e. Tidak Ditemukan
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_double_ganda_duplikat" value="double_ganda_duplikat"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'double_ganda_duplikat' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="kondisi_double_ganda_duplikat" class="radio-label">
                                f. Double / Ganda / Duplikat
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Question 202 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">202.</span>
                        <span>Jaringan atau unit kegiatan perusahaan:</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan" id="jaringan_tunggal" value="tunggal"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'tunggal' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="jaringan_tunggal" class="radio-label">
                                a. Tunggal
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan" id="jaringan_pabrik_unit_produksi" value="pabrik_unit_produksi"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'pabrik_unit_produksi' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="jaringan_pabrik_unit_produksi" class="radio-label">
                                b. Pabrik/Unit produksi, Cabang atau Perwakilan
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan" id="jaringan_pusat_ada_kegiatan_produksi" value="pusat_ada_kegiatan_produksi"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'pusat_ada_kegiatan_produksi' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="jaringan_pusat_ada_kegiatan_produksi" class="radio-label">
                                c. Pusat ada kegiatan produksi
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan" id="jaringan_kantor_pusat_administrasi_perwakilan" value="kantor_pusat_administrasi_perwakilan"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'kantor_pusat_administrasi_perwakilan' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="jaringan_kantor_pusat_administrasi_perwakilan" class="radio-label">
                                d. Kantor Pusat / Kantor Administrasi / Kantor Perwakilan
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan" id="jaringan_unit_pembantu_penunjang" value="unit_pembantu_penunjang"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'unit_pembantu_penunjang' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="jaringan_unit_pembantu_penunjang" class="radio-label">
                                e. Unit pembantu / penunjang
                            </label>
                        </div>
                    </div>
                </div>

                @if($currentPeriod === 'tahunan')
                <!-- Question 203 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">203.</span>
                        <span>Berapa jumlah seluruh kantor cabang dan unit usaha yang berada di bawah kantor pusat?</span>
                    </label>
                    <input type="number" name="jumlah_cabang_dan_unit_usaha" id="jumlah_cabang_dan_unit_usaha"
                           value="{{ $surveyResponse->jumlah_cabang_dan_unit_usaha ?? '' }}"
                           class="form-control" required min="0" step="1"
                           placeholder="Masukkan jumlah kantor cabang dan unit usaha">
                    @error('jumlah_cabang_dan_unit_usaha')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 204: Informasi kantor pusat (Diisi jika R203 berkode b) -->
                <div class="form-row" id="informasi_kantor_pusat_row">
                    <label class="form-label">
                        <span class="question-number">204.</span>
                        <span>Informasi kantor pusat (Diisi jika R203 berkode b):</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Nama Kantor Pusat:</label>
                            <input type="text" name="info_kantor_pusat_nama" id="info_kantor_pusat_nama"
                                   value="{{ $surveyResponse->info_kantor_pusat_nama ?? '' }}"
                                   class="form-control"
                                   placeholder="Masukkan nama kantor pusat">
                            @error('info_kantor_pusat_nama')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Alamat Kantor Pusat:</label>
                            <textarea name="info_kantor_pusat_alamat" id="info_kantor_pusat_alamat" rows="3"
                                      class="form-control textarea"
                                      placeholder="Masukkan alamat lengkap kantor pusat">{{ $surveyResponse->info_kantor_pusat_alamat ?? '' }}</textarea>
                            @error('info_kantor_pusat_alamat')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Email Kantor Pusat:</label>
                            <input type="email" name="info_kantor_pusat_email" id="info_kantor_pusat_email"
                                   value="{{ $surveyResponse->info_kantor_pusat_email ?? '' }}"
                                   class="form-control"
                                   placeholder="contoh: email@kantorpusat.co.id">
                            @error('info_kantor_pusat_email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Negara:</label>
                            <input type="text" name="info_kantor_pusat_negara" id="info_kantor_pusat_negara"
                                   value="{{ $surveyResponse->info_kantor_pusat_negara ?? '' }}"
                                   class="form-control" placeholder="Masukkan negara">
                            @error('info_kantor_pusat_negara')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">e. Provinsi:</label>
                            <input type="text" name="info_kantor_pusat_provinsi" id="info_kantor_pusat_provinsi"
                                   value="{{ $surveyResponse->info_kantor_pusat_provinsi ?? '' }}"
                                   class="form-control" placeholder="Masukkan provinsi">
                            @error('info_kantor_pusat_provinsi')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">f. Kabupaten / Kota:</label>
                            <input type="text" name="info_kantor_pusat_kabkota" id="info_kantor_pusat_kabkota"
                                   value="{{ $surveyResponse->info_kantor_pusat_kabkota ?? '' }}"
                                   class="form-control" placeholder="Masukkan kabupaten/kota">
                            @error('info_kantor_pusat_kabkota')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Question 205 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">205.</span>
                        <span>Jumlah bulan perusahaan aktif berproduksi selama tahun 2025:</span>
                    </label>
                    <input type="number" name="jumlah_bulan_aktif_2025" id="jumlah_bulan_aktif_2025"
                           value="{{ $surveyResponse->jumlah_bulan_aktif_2025 ?? '' }}"
                           class="form-control" required min="0" max="12" step="1"
                           placeholder="Masukkan jumlah bulan (0-12)">
                    @error('jumlah_bulan_aktif_2025')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 206 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">206.</span>
                        <span>Rata-rata waktu kerja selama tahun 2025:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Rata-rata hari kerja per bulan selama tahun 2025:</label>
                            <input type="number" name="rata_hari_kerja_bulanan_2025" id="rata_hari_kerja_bulanan_2025"
                                   value="{{ $surveyResponse->rata_hari_kerja_bulanan_2025 ?? '' }}"
                                   class="form-control" required min="0" max="31" step="1"
                                   placeholder="Masukkan rata-rata hari kerja per bulan (0-31)">
                            @error('rata_hari_kerja_bulanan_2025')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Rata-rata jam kerja per hari selama tahun 2025:</label>
                            <input type="number" name="rata_jam_kerja_per_hari_2025" id="rata_jam_kerja_per_hari_2025"
                                   value="{{ $surveyResponse->rata_jam_kerja_per_hari_2025 ?? '' }}"
                                   class="form-control" required min="0" max="24" step="1"
                                   placeholder="Masukkan rata-rata jam kerja per hari (0-24)">
                            @error('rata_jam_kerja_per_hari_2025')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Rata-rata jumlah shift per hari selama tahun 2025:</label>
                            <input type="number" name="rata_shift_per_hari_2025" id="rata_shift_per_hari_2025"
                                   value="{{ $surveyResponse->rata_shift_per_hari_2025 ?? '' }}"
                                   class="form-control" required min="0" max="3" step="1"
                                   placeholder="Masukkan rata-rata jumlah shift per hari (0-3)">
                            @error('rata_shift_per_hari_2025')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @endif

                @if($currentTriwulan == 0)
                {{-- Q207: Tahunan 2025 — detailed worker breakdown --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">207.</span>
                        <span>Jumlah pekerja selama tahun 2025 (tidak termasuk pekerja komisi saja, konsultan, kontraktor, dan pekerja di luar pabrik/unit produksi):</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Jumlah seluruh pekerja:</label>
                            <input type="number" name="jumlah_seluruh_pekerja" id="jumlah_seluruh_pekerja"
                                   value="{{ $surveyResponse->jumlah_seluruh_pekerja ?? '' }}"
                                   class="form-control" required min="0" step="1"
                                   placeholder="Masukkan jumlah seluruh pekerja">
                            @error('jumlah_seluruh_pekerja')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Jumlah pekerja menurut jenis kelamin:</label>
                            <div class="form-subgrid">
                                <div class="form-subrow">
                                    <label class="form-sublabel">b.1. Pekerja laki-laki:</label>
                                    <input type="number" name="tenaga_kerja_laki_laki" id="tenaga_kerja_laki_laki"
                                           value="{{ $surveyResponse->tenaga_kerja_laki_laki ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja laki-laki">
                                    @error('tenaga_kerja_laki_laki')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-subrow">
                                    <label class="form-sublabel">b.2. Pekerja perempuan:</label>
                                    <input type="number" name="tenaga_kerja_perempuan" id="tenaga_kerja_perempuan"
                                           value="{{ $surveyResponse->tenaga_kerja_perempuan ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja perempuan">
                                    @error('tenaga_kerja_perempuan')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Jumlah pekerja bukan outsourcing:</label>
                            <div class="form-subgrid">
                                <div class="form-subrow">
                                    <label class="form-sublabel">c.1. Pekerja produksi (teknis/operasional):</label>
                                    <input type="number" name="pekerja_bukan_outsourcing_produksi" id="pekerja_bukan_outsourcing_produksi"
                                           value="{{ $surveyResponse->pekerja_bukan_outsourcing_produksi ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja bukan outsourcing produksi">
                                    @error('pekerja_bukan_outsourcing_produksi')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-subrow">
                                    <label class="form-sublabel">c.2. Pekerja lainnya (administrasi/non-produksi):</label>
                                    <input type="number" name="pekerja_bukan_outsourcing_lainnya" id="pekerja_bukan_outsourcing_lainnya"
                                           value="{{ $surveyResponse->pekerja_bukan_outsourcing_lainnya ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja bukan outsourcing lainnya">
                                    @error('pekerja_bukan_outsourcing_lainnya')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Jumlah pekerja outsourcing:</label>
                            <div class="form-subgrid">
                                <div class="form-subrow">
                                    <label class="form-sublabel">d.1. Pekerja produksi:</label>
                                    <input type="number" name="pekerja_outsourcing_produksi" id="pekerja_outsourcing_produksi"
                                           value="{{ $surveyResponse->pekerja_outsourcing_produksi ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja outsourcing produksi">
                                    @error('pekerja_outsourcing_produksi')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-subrow">
                                    <label class="form-sublabel">d.2. Pekerja lainnya:</label>
                                    <input type="number" name="pekerja_outsourcing_lainnya" id="pekerja_outsourcing_lainnya"
                                           value="{{ $surveyResponse->pekerja_outsourcing_lainnya ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja outsourcing lainnya">
                                    @error('pekerja_outsourcing_lainnya')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">e. Jumlah pekerja berwarganegara asing:</label>
                            <input type="number" name="tenaga_kerja_asing" id="tenaga_kerja_asing"
                                   value="{{ $surveyResponse->tenaga_kerja_asing ?? '' }}"
                                   class="form-control" required min="0" step="1"
                                   placeholder="Masukkan jumlah pekerja asing">
                            @error('tenaga_kerja_asing')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                @else
                {{-- Q207: Triwulanan — simplified single-entry --}}
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">207.</span>
                        <span>Rata-rata tenaga kerja di perusahaan pada triwulan ini:</span>
                    </label>
                    <input type="number" name="rata_rata_tenaga_kerja" id="rata_rata_tenaga_kerja"
                           value="{{ $surveyResponse->rata_rata_tenaga_kerja ?? '' }}"
                           class="form-control" required min="0" step="1"
                           placeholder="Masukkan rata-rata jumlah tenaga kerja">
                    @error('rata_rata_tenaga_kerja')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                @endif

                <!-- Question 208 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">208.</span>
                        <span>Tuliskan kegiatan utama perusahaan beserta produk utama dan KBLI utama:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label for="kegiatan_utama_q210" class="form-sublabel">a. Uraian kegiatan utama perusahaan:</label>
                            <textarea name="kegiatan_utama_perusahaan" id="kegiatan_utama_q210" class="form-control" required maxlength="1000" placeholder="Contoh: Membuat sepatu dari kulit sintetis, membuat ikan tongkol beku, dll.">{{ old('kegiatan_utama_perusahaan', $surveyResponse->kegiatan_utama_perusahaan ?? '') }}</textarea>
                            @error('kegiatan_utama_perusahaan')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        @if($currentPeriod === 'tahunan')
                        <div class="form-subrow">
                            <label for="produk_utama_q210" class="form-sublabel">b. Tuliskan produk utama tahun 2025:</label>
                            <textarea name="produk_utama_perusahaan" id="produk_utama_q210" class="form-control" required maxlength="1000" placeholder="Contoh: sandal karet, sepatu kulit, ikan tongkol beku, dll.">{{ old('produk_utama_perusahaan', $surveyResponse->produk_utama_perusahaan ?? '') }}</textarea>
                        </div>
                        @endif
                        <div class="form-subrow">
                            <label for="kbli_q210" class="form-sublabel">c. KBLI utama (5 digit):</label>
                            <input type="text" name="kbli_utama" id="kbli_q210" value="{{ old('kbli_utama', $surveyResponse->kbli_utama ?? '') }}" class="form-control" required maxlength="5" inputmode="numeric" placeholder="Masukkan kode KBLI (contoh: 11041)">
                            @error('kbli_utama')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if($currentPeriod === 'tahunan')
                <!-- Question 209 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">209.</span>
                        <span>Pilih yang paling sesuai dengan kegiatan utama usaha/perusahaan ini:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">Apakah memproduksi barang sendiri?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="memproduksi_barang_sendiri" id="memproduksi_barang_sendiri_ya" value="ya" class="radio-input" required {{ old('memproduksi_barang_sendiri', $surveyResponse->memproduksi_barang_sendiri ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="memproduksi_barang_sendiri_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="memproduksi_barang_sendiri" id="memproduksi_barang_sendiri_tidak" value="tidak" class="radio-input" required {{ old('memproduksi_barang_sendiri', $surveyResponse->memproduksi_barang_sendiri ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="memproduksi_barang_sendiri_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">Apakah menyediakan layanan makan minum?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="menyediakan_layanan_makan_minum" id="menyediakan_layanan_makan_minum_ya" value="ya" class="radio-input" required {{ old('menyediakan_layanan_makan_minum', $surveyResponse->menyediakan_layanan_makan_minum ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="menyediakan_layanan_makan_minum_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="menyediakan_layanan_makan_minum" id="menyediakan_layanan_makan_minum_tidak" value="tidak" class="radio-input" required {{ old('menyediakan_layanan_makan_minum', $surveyResponse->menyediakan_layanan_makan_minum ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="menyediakan_layanan_makan_minum_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">Apakah melakukan penjualan barang dari pihak lain?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="penjualan_barang_pihak_lain" id="penjualan_barang_pihak_lain_ya" value="ya" class="radio-input" required {{ old('penjualan_barang_pihak_lain', $surveyResponse->penjualan_barang_pihak_lain ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="penjualan_barang_pihak_lain_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="penjualan_barang_pihak_lain" id="penjualan_barang_pihak_lain_tidak" value="tidak" class="radio-input" required {{ old('penjualan_barang_pihak_lain', $surveyResponse->penjualan_barang_pihak_lain ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="penjualan_barang_pihak_lain_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">Apakah melakukan aktivitas jasa?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="aktivitas_jasa" id="aktivitas_jasa_ya" value="ya" class="radio-input" required {{ old('aktivitas_jasa', $surveyResponse->aktivitas_jasa ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="aktivitas_jasa_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="aktivitas_jasa" id="aktivitas_jasa_tidak" value="tidak" class="radio-input" required {{ old('aktivitas_jasa', $surveyResponse->aktivitas_jasa ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="aktivitas_jasa_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($currentPeriod === 'tahunan')
                <!-- Question 210 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">210.</span>
                        <span>Sebutkan sertifikasi produk yang dimiliki perusahaan (pilih semua yang sesuai):</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Sertifikasi Keamanan Produk <span class="text-sm text-gray-500">(mis. SNI, CPSP, HACCP, GMP/SKP, dll.)</span></label>
                            <input type="text" name="sertifikasi_keamanan_produk" id="sertifikasi_keamanan_produk" value="{{ old('sertifikasi_keamanan_produk', $surveyResponse->sertifikasi_keamanan_produk ?? '') }}" class="form-control" maxlength="500" placeholder="">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Sertifikasi Kesehatan dan Keberlanjutan <span class="text-sm text-gray-500">(mis. OEKO-TEX, Leather Working Group, dll.)</span></label>
                            <input type="text" name="sertifikasi_kesehatan_keberlanjutan" id="sertifikasi_kesehatan_keberlanjutan" value="{{ old('sertifikasi_kesehatan_keberlanjutan', $surveyResponse->sertifikasi_kesehatan_keberlanjutan ?? '') }}" class="form-control" maxlength="500" placeholder="">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Sertifikasi Kualitas Manajemen <span class="text-sm text-gray-500">(mis. ISO 9001, ISO 22000, ISO 14001, dll.)</span></label>
                            <input type="text" name="sertifikasi_kualitas_manajemen" id="sertifikasi_kualitas_manajemen" value="{{ old('sertifikasi_kualitas_manajemen', $surveyResponse->sertifikasi_kualitas_manajemen ?? '') }}" class="form-control" maxlength="500" placeholder="">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Tidak memiliki / tidak tahu</label>
                            <input type="text" name="sertifikasi_tidak_ada" id="sertifikasi_tidak_ada" value="{{ old('sertifikasi_tidak_ada', $surveyResponse->sertifikasi_tidak_ada ?? '') }}" class="form-control" maxlength="500" placeholder="">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">e. Lainnya (tuliskan)</label>
                            <input type="text" name="sertifikasi_lainnya" id="sertifikasi_lainnya" value="{{ old('sertifikasi_lainnya', $surveyResponse->sertifikasi_lainnya ?? '') }}" class="form-control" maxlength="500" placeholder="">
                        </div>
                    </div>
                </div>
                @endif

                @if($currentPeriod === 'tahunan')
                <!-- Question 211 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">211.</span>
                        <span>Model industri manufaktur yang diterapkan di perusahaan (pilihan boleh lebih dari 1):</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="checkbox" name="model_industri_oem" id="model_industri_oem" value="1" class="radio-input" {{ old('model_industri_oem', $surveyResponse->model_industri_oem ?? 0) ? 'checked' : '' }}>
                            <label for="model_industri_oem" class="radio-label">a. OEM (Original Equipment Manufacturer)</label>
                        </div>
                        <div class="radio-option">
                            <input type="checkbox" name="model_industri_odm" id="model_industri_odm" value="1" class="radio-input" {{ old('model_industri_odm', $surveyResponse->model_industri_odm ?? 0) ? 'checked' : '' }}>
                            <label for="model_industri_odm" class="radio-label">b. ODM (Original Design Manufacturer)</label>
                        </div>
                        <div class="radio-option">
                            <input type="checkbox" name="model_industri_obm" id="model_industri_obm" value="1" class="radio-input" {{ old('model_industri_obm', $surveyResponse->model_industri_obm ?? 0) ? 'checked' : '' }}>
                            <label for="model_industri_obm" class="radio-label">c. OBM (Original Brand Manufacturer)</label>
                        </div>
                        <div class="radio-option">
                            <input type="checkbox" name="model_industri_tidak_ada" id="model_industri_tidak_ada" value="1" class="radio-input" {{ old('model_industri_tidak_ada', $surveyResponse->model_industri_tidak_ada ?? 0) ? 'checked' : '' }}>
                            <label for="model_industri_tidak_ada" class="radio-label">d. Tidak ada / tidak tahu</label>
                        </div>
                        <div class="radio-option">
                            <input type="checkbox" name="model_industri_lainnya_check" id="model_industri_lainnya_check" value="1" class="radio-input" {{ old('model_industri_lainnya', $surveyResponse->model_industri_lainnya ?? '') ? 'checked' : '' }}>
                            <label for="model_industri_lainnya_check" class="radio-label">e. Lainnya, sebutkan:</label>
                        </div>
                        <div style="padding-left:1.75rem;">
                            <input type="text" name="model_industri_lainnya" id="model_industri_lainnya" value="{{ old('model_industri_lainnya', $surveyResponse->model_industri_lainnya ?? '') }}" class="form-control" maxlength="500" placeholder="Sebutkan model industri lainnya">
                        </div>
                    </div>
                </div>
                @endif

                @if($currentPeriod === 'tahunan')
                <!-- Question 212 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">212.</span>
                        <span>Apakah perusahaan ini menggunakan internet dalam menjalankan usaha?</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="penggunaan_internet" id="penggunaan_internet_ya" value="ya" class="radio-input" required {{ old('penggunaan_internet', $surveyResponse->penggunaan_internet ?? '') == 'ya' ? 'checked' : '' }}>
                            <label for="penggunaan_internet_ya" class="radio-label">Ya</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="penggunaan_internet" id="penggunaan_internet_tidak" value="tidak" class="radio-input" required {{ old('penggunaan_internet', $surveyResponse->penggunaan_internet ?? '') == 'tidak' ? 'checked' : '' }}>
                            <label for="penggunaan_internet_tidak" class="radio-label">Tidak <span class="text-sm text-gray-500">(Lanjut ke Pertanyaan 215)</span></label>
                        </div>
                    </div>
                    @error('penggunaan_internet')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 214a: Tujuan penggunaan internet (shown only if 214=Ya) -->
                <div class="form-row" id="tujuan_penggunaan_internet_row">
                    <label class="form-label">
                        <span class="question-number">214a.</span>
                        <span>Tujuan penggunaan internet:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a1. Menerima pesanan barang/jasa</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="internet_a1_menerima_pesanan" id="internet_a1_ya" value="ya" class="radio-input" {{ old('internet_a1_menerima_pesanan', $surveyResponse->internet_a1_menerima_pesanan ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="internet_a1_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="internet_a1_menerima_pesanan" id="internet_a1_tidak" value="tidak" class="radio-input" {{ old('internet_a1_menerima_pesanan', $surveyResponse->internet_a1_menerima_pesanan ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="internet_a1_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">a2. Produksi barang/jasa</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="internet_a2_produksi" id="internet_a2_ya" value="ya" class="radio-input" {{ old('internet_a2_produksi', $surveyResponse->internet_a2_produksi ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="internet_a2_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="internet_a2_produksi" id="internet_a2_tidak" value="tidak" class="radio-input" {{ old('internet_a2_produksi', $surveyResponse->internet_a2_produksi ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="internet_a2_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">a3. Distribusi barang/jasa</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="internet_a3_distribusi" id="internet_a3_ya" value="ya" class="radio-input" {{ old('internet_a3_distribusi', $surveyResponse->internet_a3_distribusi ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="internet_a3_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="internet_a3_distribusi" id="internet_a3_tidak" value="tidak" class="radio-input" {{ old('internet_a3_distribusi', $surveyResponse->internet_a3_distribusi ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="internet_a3_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">a4. Membeli bahan baku online</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="internet_a4_beli_bahan_baku" id="internet_a4_ya" value="ya" class="radio-input" {{ old('internet_a4_beli_bahan_baku', $surveyResponse->internet_a4_beli_bahan_baku ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="internet_a4_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="internet_a4_beli_bahan_baku" id="internet_a4_tidak" value="tidak" class="radio-input" {{ old('internet_a4_beli_bahan_baku', $surveyResponse->internet_a4_beli_bahan_baku ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="internet_a4_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">a5. Promosi</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="internet_a5_promosi" id="internet_a5_ya" value="ya" class="radio-input" {{ old('internet_a5_promosi', $surveyResponse->internet_a5_promosi ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="internet_a5_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="internet_a5_promosi" id="internet_a5_tidak" value="tidak" class="radio-input" {{ old('internet_a5_promosi', $surveyResponse->internet_a5_promosi ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="internet_a5_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">a6. Lainnya</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="internet_a6_lainnya" id="internet_a6_ya" value="ya" class="radio-input" {{ old('internet_a6_lainnya', $surveyResponse->internet_a6_lainnya ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="internet_a6_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="internet_a6_lainnya" id="internet_a6_tidak" value="tidak" class="radio-input" {{ old('internet_a6_lainnya', $surveyResponse->internet_a6_lainnya ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="internet_a6_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 214b: Teknologi digital -->
                <div class="form-row" id="teknologi_digital_row">
                    <label class="form-label">
                        <span class="question-number">214b.</span>
                        <span>Apakah perusahaan memanfaatkan teknologi digital (AI, IoT, big data, printer 3D, blockchain, cloud)?</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="pemanfaatan_teknologi_digital" id="pemanfaatan_teknologi_digital_ya" value="ya" class="radio-input" {{ old('pemanfaatan_teknologi_digital', $surveyResponse->pemanfaatan_teknologi_digital ?? '') == 'ya' ? 'checked' : '' }}>
                            <label for="pemanfaatan_teknologi_digital_ya" class="radio-label">Ya</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="pemanfaatan_teknologi_digital" id="pemanfaatan_teknologi_digital_tidak" value="tidak" class="radio-input" {{ old('pemanfaatan_teknologi_digital', $surveyResponse->pemanfaatan_teknologi_digital ?? '') == 'tidak' ? 'checked' : '' }}>
                            <label for="pemanfaatan_teknologi_digital_tidak" class="radio-label">Tidak</label>
                        </div>
                    </div>
                </div>

                <!-- Question 213 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">213.</span>
                        <span>Praktik ramah lingkungan:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Apakah perusahaan memproduksi barang/jasa yang ramah lingkungan?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="produksi_ramah_lingkungan" id="produksi_ramah_lingkungan_seluruh" value="ya_seluruh" class="radio-input" required {{ old('produksi_ramah_lingkungan', $surveyResponse->produksi_ramah_lingkungan ?? '') == 'ya_seluruh' ? 'checked' : '' }}>
                                    <label for="produksi_ramah_lingkungan_seluruh" class="radio-label">Ya, seluruhnya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="produksi_ramah_lingkungan" id="produksi_ramah_lingkungan_sebagian" value="ya_sebagian" class="radio-input" required {{ old('produksi_ramah_lingkungan', $surveyResponse->produksi_ramah_lingkungan ?? '') == 'ya_sebagian' ? 'checked' : '' }}>
                                    <label for="produksi_ramah_lingkungan_sebagian" class="radio-label">Ya, sebagian</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="produksi_ramah_lingkungan" id="produksi_ramah_lingkungan_tidak" value="tidak" class="radio-input" required {{ old('produksi_ramah_lingkungan', $surveyResponse->produksi_ramah_lingkungan ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="produksi_ramah_lingkungan_tidak" class="radio-label">Tidak sama sekali</label>
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Apakah usaha/perusahaan menggunakan input untuk tujuan perlindungan lingkungan dan/atau pembelian barang dan jasa yang ramah lingkungan?</label>
                            <div class="radio-group">
                                <div class="radio-option">
                                    <input type="radio" name="penggunaan_input_ramah_lingkungan" id="penggunaan_input_ramah_lingkungan_ya" value="ya" class="radio-input" required {{ old('penggunaan_input_ramah_lingkungan', $surveyResponse->penggunaan_input_ramah_lingkungan ?? '') == 'ya' ? 'checked' : '' }}>
                                    <label for="penggunaan_input_ramah_lingkungan_ya" class="radio-label">Ya</label>
                                </div>
                                <div class="radio-option">
                                    <input type="radio" name="penggunaan_input_ramah_lingkungan" id="penggunaan_input_ramah_lingkungan_tidak" value="tidak" class="radio-input" required {{ old('penggunaan_input_ramah_lingkungan', $surveyResponse->penggunaan_input_ramah_lingkungan ?? '') == 'tidak' ? 'checked' : '' }}>
                                    <label for="penggunaan_input_ramah_lingkungan_tidak" class="radio-label">Tidak</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="flex items-center gap-4">
                <button type="button" id="back-to-blok1" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15,18 9,12 15,6"></polyline>
                    </svg>
                    Kembali ke Bab 1
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
                    Simpan & Lanjut ke Bab 3
                </button>

                <button type="button" id="go-to-blok6" class="btn btn-primary" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                    Lanjut ke Blok VI
                </button>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                <span>* Wajib diisi</span>
            </div>
        </div>
    </form>

    @if(isset($referenceResponse) && $referenceResponse && !(isset($triwulan) && $triwulan === 1))
    @php
    $refFields = $currentTriwulan > 0 ? [
        ['name' => 'kondisi_perusahaan',        'label' => 'Kondisi Perusahaan (201)',           'copyable' => true],
        ['name' => 'jaringan_unit_kegiatan',    'label' => 'Jaringan Unit Kegiatan (202)',       'copyable' => true],
        ['name' => 'kegiatan_utama_perusahaan', 'label' => 'Kegiatan Utama Perusahaan (208.a)', 'copyable' => true],
        ['name' => 'kbli_utama',                'label' => 'KBLI Utama (208.c)',                'copyable' => true],
    ] : [
        ['name' => 'kondisi_perusahaan',                  'label' => 'Kondisi Perusahaan',                       'copyable' => true],
        ['name' => 'jaringan_unit_kegiatan',              'label' => 'Jaringan Unit Kegiatan',                   'copyable' => true],
        ['name' => 'kegiatan_utama_perusahaan',           'label' => 'Kegiatan Utama Perusahaan',                'copyable' => true],
        ['name' => 'kbli_utama',                          'label' => 'KBLI Utama',                               'copyable' => true],
        ['name' => 'jumlah_bulan_aktif_2025',             'label' => 'Jumlah Bulan Aktif',                       'copyable' => true],
        ['name' => 'rata_hari_kerja_bulanan_2025',        'label' => 'Rata-rata Hari Kerja Bulanan',             'copyable' => true],
        ['name' => 'jumlah_seluruh_pekerja',              'label' => 'Jumlah Seluruh Pekerja (207.a)',           'copyable' => true],
        ['name' => 'tenaga_kerja_laki_laki',              'label' => 'TK Laki-laki (207.b.1)',                   'copyable' => true],
        ['name' => 'tenaga_kerja_perempuan',              'label' => 'TK Perempuan (207.b.2)',                   'copyable' => true],
        ['name' => 'pekerja_bukan_outsourcing_produksi',  'label' => 'TK Bukan Outsourcing Produksi (207.c.1)', 'copyable' => true],
        ['name' => 'pekerja_bukan_outsourcing_lainnya',   'label' => 'TK Bukan Outsourcing Lainnya (207.c.2)',  'copyable' => true],
        ['name' => 'pekerja_outsourcing_produksi',        'label' => 'TK Outsourcing Produksi (207.d.1)',        'copyable' => true],
        ['name' => 'pekerja_outsourcing_lainnya',         'label' => 'TK Outsourcing Lainnya (207.d.2)',         'copyable' => true],
        ['name' => 'tenaga_kerja_asing',                  'label' => 'TK Asing (207.e)',                         'copyable' => true],
        ['name' => 'memproduksi_barang_sendiri',          'label' => 'Memproduksi Barang Sendiri',               'copyable' => true],
        ['name' => 'penggunaan_internet',                 'label' => 'Penggunaan Internet',                      'copyable' => true],
        ['name' => 'produksi_ramah_lingkungan',           'label' => 'Produksi Ramah Lingkungan',                'copyable' => true],
    ];
    @endphp
    @include('survey.sibstr.partials.reference-drawer', [
        'referenceResponse' => $referenceResponse,
        'currentTwLabel'    => isset($triwulan) && $triwulan > 0
                                ? \App\Models\SurveyResponse::triwulanLabel($triwulan) . ' ' . ($tahun ?? 2025)
                                : 'Tahunan ' . ($tahun ?? 2025),
        'fields'            => $refFields,
    ])
    @endif
</div>
@endif
{{-- end active form / read-only gate --}}

@push('scripts')
@if(!$isReadOnlyMode)
<script>
// Set up survey routes for the JavaScript module
@if(isset($editRoutes) && !empty($editRoutes))
window.surveyRoutes = @json($editRoutes);
@else
window.surveyRoutes = {
    autoSave:          '{{ route("survey.sibstr.blok2.autosave",       ["year" => $tahun, "period" => $period]) }}',
    saveAll:           '{{ route("survey.sibstr.blok2.save",           ["year" => $tahun, "period" => $period]) }}',
    status:            '{{ route("survey.sibstr.blok2.status",         ["year" => $tahun, "period" => $period]) }}',
    backToBlok1:       '{{ route("survey.sibstr.blok1",                ["year" => $tahun, "period" => $period]) }}',
    nextBlok:          '{{ route("survey.sibstr.blok3a",               ["year" => $tahun, "period" => $period]) }}',
    blok3a:            '{{ route("survey.sibstr.blok3a",               ["year" => $tahun, "period" => $period]) }}',
    blok6:             '{{ route("survey.sibstr.blok6",                ["year" => $tahun, "period" => $period]) }}',
    blok3b_industri:   '{{ route("survey.sibstr.blok3b.industri",      ["year" => $tahun, "period" => $period]) }}',
    blok3b_nonindustri:'{{ route("survey.sibstr.blok3b.nonindustri",   ["year" => $tahun, "period" => $period]) }}'
};
@endif
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok2.js') }}"></script>
@endif
@endpush
@endsection
