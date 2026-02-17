@extends('layouts.app')

@section('title', 'SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR) - Blok II - DataKita')
@section('description', 'Survei Industri Besar dan Sedang Triwulanan - Blok II: Pendahuluan')

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
            II. PENDAHULUAN
        </h2>
        <p class="survey-description">
            Formulir survei untuk pengumpulan data industri besar dan sedang triwulanan sesuai standar BPS
        </p>
    </div>

    <!-- Auto-save Status -->
    <div id="autosave-status" class="autosave-status hidden">
        <span id="autosave-text"></span>
    </div>

    <!-- Survey Form -->
    <form id="survey-form" class="survey-form" data-aos="fade-up" data-aos-delay="200">
        @csrf

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

                <!-- Question 204: Informasi kantor pusat (Diisi jika R202 berkode b) -->
                <div class="form-row" id="informasi_kantor_pusat_row">
                    <label class="form-label">
                        <span class="question-number">204.</span>
                        <span>Informasi kantor pusat (Diisi jika R202 berkode b):</span>
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
                        <span>Rata-rata hari kerja per bulan selama tahun 2025:</span>
                    </label>
                    <input type="number" name="rata_hari_kerja_bulanan_2025" id="rata_hari_kerja_bulanan_2025"
                           value="{{ $surveyResponse->rata_hari_kerja_bulanan_2025 ?? '' }}"
                           class="form-control" required min="0" max="31" step="1"
                           placeholder="Masukkan rata-rata hari kerja per bulan">
                    @error('rata_hari_kerja_bulanan_2025')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 207 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">207.</span>
                        <span>Rata-rata jam kerja dan jumlah shift per hari selama tahun 2025:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Rata-rata jam kerja per hari:</label>
                            <input type="number" name="rata_jam_kerja_per_hari_2025" id="rata_jam_kerja_per_hari_2025"
                                   value="{{ $surveyResponse->rata_jam_kerja_per_hari_2025 ?? '' }}"
                                   class="form-control" required min="0" step="0.1"
                                   placeholder="Masukkan rata-rata jam kerja per hari">
                            @error('rata_jam_kerja_per_hari_2025')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Rata-rata jumlah shift per hari:</label>
                            <input type="number" name="rata_shift_per_hari_2025" id="rata_shift_per_hari_2025"
                                   value="{{ $surveyResponse->rata_shift_per_hari_2025 ?? '' }}"
                                   class="form-control" required min="0" step="1"
                                   placeholder="Masukkan rata-rata jumlah shift per hari">
                            @error('rata_shift_per_hari_2025')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Question 208 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">208.</span>
                        <span>Rata-rata tenaga kerja di perusahaan pada triwulan ini:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Jumlah pekerja menurut jenis kelamin (termasuk pekerja asing dan outsourcing):</label>
                            <div class="form-subgrid">
                                <div class="form-subrow">
                                    <label class="form-sublabel">- Pekerja Laki-laki:</label>
                                    <input type="number" name="tenaga_kerja_laki_laki" id="tenaga_kerja_laki_laki"
                                           value="{{ $surveyResponse->tenaga_kerja_laki_laki ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja laki-laki">
                                    @error('tenaga_kerja_laki_laki')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-subrow">
                                    <label class="form-sublabel">- Pekerja Perempuan:</label>
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
                            <label class="form-sublabel">b. Jumlah pekerja menurut klasifikasi pekerja:</label>
                            <div class="form-subgrid">
                                <div class="form-subrow">
                                    <label class="form-sublabel">- Pekerja produksi:</label>
                                    <input type="number" name="tenaga_kerja_produksi" id="tenaga_kerja_produksi"
                                           value="{{ $surveyResponse->tenaga_kerja_produksi ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja produksi">
                                    @error('tenaga_kerja_produksi')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-subrow">
                                    <label class="form-sublabel">- Pekerja lainnya:</label>
                                    <input type="number" name="tenaga_kerja_lainnya" id="tenaga_kerja_lainnya"
                                           value="{{ $surveyResponse->tenaga_kerja_lainnya ?? '' }}"
                                           class="form-control" required min="0" step="1"
                                           placeholder="Masukkan jumlah pekerja lainnya">
                                    @error('tenaga_kerja_lainnya')
                                        <div class="error-message">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Jumlah pekerja berwarganegara asing:</label>
                            <input type="number" name="tenaga_kerja_asing" id="tenaga_kerja_asing"
                                   value="{{ $surveyResponse->tenaga_kerja_asing ?? '' }}"
                                   class="form-control" required min="0" step="1"
                                   placeholder="Masukkan jumlah pekerja asing">
                            @error('tenaga_kerja_asing')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Jumlah pekerja outsourcing:</label>
                            <input type="number" name="tenaga_kerja_outsourcing" id="tenaga_kerja_outsourcing"
                                   value="{{ $surveyResponse->tenaga_kerja_outsourcing ?? '' }}"
                                   class="form-control" required min="0" step="1"
                                   placeholder="Masukkan jumlah pekerja outsourcing">
                            @error('tenaga_kerja_outsourcing')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Question 208 (KBLI) -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">208.</span>
                        <span>Tuliskan kegiatan utama perusahaan beserta KBLI utama:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label for="kegiatan_utama_perusahaan" class="form-sublabel">a. Uraian kegiatan utama perusahaan:</label>
                            <textarea name="kegiatan_utama_perusahaan" id="kegiatan_utama_perusahaan" class="form-control" required maxlength="1000" placeholder="Contoh: Produksi minuman ringan, distribusi minuman botol">{{ old('kegiatan_utama_perusahaan', $surveyResponse->kegiatan_utama_perusahaan ?? '') }}</textarea>
                            @error('kegiatan_utama_perusahaan')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-subrow">
                            <label for="kbli_utama" class="form-sublabel">b. KBLI utama (5 digit):</label>
                            <input type="text" name="kbli_utama" id="kbli_utama" value="{{ old('kbli_utama', $surveyResponse->kbli_utama ?? '') }}" class="form-control" required maxlength="5" inputmode="numeric" placeholder="Masukkan kode KBLI (contoh: 11041)">
                            @error('kbli_utama')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

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

                <!-- Question 210 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">210.</span>
                        <span>Apakah perusahaan ini menggunakan internet dalam menjalankan usaha?</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="penggunaan_internet" id="penggunaan_internet_ya" value="ya" class="radio-input" required {{ old('penggunaan_internet', $surveyResponse->penggunaan_internet ?? '') == 'ya' ? 'checked' : '' }}>
                            <label for="penggunaan_internet_ya" class="radio-label">Ya</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="penggunaan_internet" id="penggunaan_internet_tidak" value="tidak" class="radio-input" required {{ old('penggunaan_internet', $surveyResponse->penggunaan_internet ?? '') == 'tidak' ? 'checked' : '' }}>
                            <label for="penggunaan_internet_tidak" class="radio-label">Tidak <span class="text-sm text-gray-500">(Lanjut ke Pertanyaan 211)</span></label>
                        </div>
                    </div>
                    @error('penggunaan_internet')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 210a: Tujuan penggunaan internet (shown only if 210=Ya) -->
                <div class="form-row" id="tujuan_penggunaan_internet_row">
                    <label class="form-label">
                        <span class="question-number">210a.</span>
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

                <!-- 210b: Teknologi digital -->
                <div class="form-row" id="teknologi_digital_row">
                    <label class="form-label">
                        <span class="question-number">210b.</span>
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

                <!-- Question 211 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">211.</span>
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
</div>

@push('scripts')
<script>
// Set up survey routes for the JavaScript module
window.surveyRoutes = @json($editRoutes ?? null) || {
    autoSave: '{{ route("survey.sibstr.blok2.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok2.save") }}',
    status: '{{ route("survey.sibstr.blok2.status") }}',
    backToBlok1: '{{ route("survey.sibstr.blok1") }}',
    nextBlok: '{{ route("survey.sibstr.blok3a") }}', // Conditional navigation handled in JS
    blok3a: '{{ route("survey.sibstr.blok3a") }}',
    blok6: '{{ route("survey.sibstr.blok6") }}',
    blok3b_industri: '{{ route("survey.sibstr.blok3b.industri") }}',
    blok3b_nonindustri: '{{ route("survey.sibstr.blok3b.nonindustri") }}'
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok2.js') }}"></script>
@endpush
@endsection
