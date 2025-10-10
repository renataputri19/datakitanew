@extends('layouts.app')

@section('title', 'SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR) - DataKita')
@section('description', 'Survei Industri Besar dan Sedang Triwulanan - Blok I: Keterangan Umum')

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
            I. KETERANGAN UMUM
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

        <!-- Header Information Section -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">Header Information</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">1.</span>
                        <span>KIP (Kode Identitas Perusahaan):</span>
                    </label>
                    <input type="text" name="kip" id="kip" value="{{ $surveyResponse->kip ?? '' }}"
                           class="form-control" placeholder="Masukkan KIP">
                </div>
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">2.</span>
                        <span>IDSBR (ID Statistical Business Register):</span>
                    </label>
                    <input type="text" name="idsbr" id="idsbr" value="{{ $surveyResponse->idsbr ?? '' }}"
                           class="form-control" placeholder="Masukkan IDSBR">
                </div>
            </div>
        </div>

        <!-- Section I: KETERANGAN UMUM -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">I. KETERANGAN UMUM (General Information)</h3>
            </div>
            <div class="form-grid">
                <!-- Question 101 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">101.</span>
                        <span>Nama Perusahaan:</span>
                    </label>
                    <input type="text" name="nama_perusahaan" id="nama_perusahaan" value="{{ $surveyResponse->nama_perusahaan ?? '' }}"
                           class="form-control" required
                           placeholder="Masukkan nama lengkap perusahaan">
                    @error('nama_perusahaan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 102 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">102.</span>
                        <span>Alamat Pabrik/Tempat Usaha:</span>
                    </label>
                    <textarea name="alamat_pabrik" id="alamat_pabrik" rows="3"
                              class="form-control textarea" required
                              placeholder="Masukkan alamat lengkap pabrik/tempat usaha">{{ $surveyResponse->alamat_pabrik ?? '' }}</textarea>
                    @error('alamat_pabrik')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 103 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">103.</span>
                        <span>Kabupaten/Kota:</span>
                    </label>
                    <input type="text" name="kabupaten_kota" id="kabupaten_kota" value="{{ $surveyResponse->kabupaten_kota ?? '' }}"
                           class="form-control" required
                           placeholder="Masukkan kabupaten/kota">
                    @error('kabupaten_kota')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 104 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">104.</span>
                        <span>Telepon/Fax:</span>
                    </label>
                    <input type="text" name="telepon_fax" id="telepon_fax" value="{{ $surveyResponse->telepon_fax ?? '' }}"
                           class="form-control" required
                           placeholder="Masukkan nomor telepon/fax">
                    @error('telepon_fax')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 105 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">105.</span>
                        <span>Penghubung:</span>
                    </label>
                    <input type="text" name="penghubung" id="penghubung" value="{{ $surveyResponse->penghubung ?? '' }}"
                           class="form-control" required
                           placeholder="Masukkan nama penghubung">
                    @error('penghubung')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 106 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">106.</span>
                        <span>Email:</span>
                    </label>
                    <input type="email" name="email" id="email" value="{{ $surveyResponse->email ?? '' }}"
                           class="form-control" required
                           placeholder="Masukkan alamat email">
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 107 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">107.</span>
                        <span>NIB (Nomor Induk Berusaha):</span>
                    </label>
                    <input type="text" name="nib" id="nib" value="{{ $surveyResponse->nib ?? '' }}"
                           class="form-control" required
                           pattern="[0-9]{13}"
                           maxlength="13"
                           placeholder="Masukkan NIB (13 digit angka)"
                           title="NIB harus berupa 13 digit angka">
                    @error('nib')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                    <div id="nib-error" class="error-message" style="display: none;"></div>
                </div>

                <!-- Question 108 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">108.</span>
                        <span>Jenis Kawasan:</span>
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="jenis_kawasan" id="jenis_kawasan_ekonomi_khusus" value="ekonomi_khusus"
                                   {{ ($surveyResponse->jenis_kawasan ?? '') == 'ekonomi_khusus' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="jenis_kawasan_ekonomi_khusus" class="radio-label">
                                a. Kawasan Ekonomi Khusus
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jenis_kawasan" id="jenis_kawasan_industri" value="industri"
                                   {{ ($surveyResponse->jenis_kawasan ?? '') == 'industri' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="jenis_kawasan_industri" class="radio-label">
                                b. Kawasan Industri
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jenis_kawasan" id="jenis_kawasan_luar_kawasan" value="luar_kawasan"
                                   {{ ($surveyResponse->jenis_kawasan ?? '') == 'luar_kawasan' ? 'checked' : '' }}
                                   class="radio-input" required>
                            <label for="jenis_kawasan_luar_kawasan" class="radio-label">
                                c. Di Luar Kawasan
                            </label>
                        </div>
                    </div>
                    @error('jenis_kawasan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Question 109 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">109.</span>
                        <span>Nama Kawasan:</span>
                    </label>
                    <input type="text" name="nama_kawasan" id="nama_kawasan" value="{{ $surveyResponse->nama_kawasan ?? '' }}"
                           class="form-control" required
                           placeholder="Masukkan nama kawasan">
                    @error('nama_kawasan')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- LEGALISASI PERUSAHAAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">LEGALISASI PERUSAHAAN</h3>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 italic px-6">
                Diketahui oleh yang bertanggung jawab di perusahaan
            </p>
            <div class="form-grid">
                <!-- Question 110 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">110.</span>
                        <span>Nama:</span>
                    </label>
                    <input type="text" name="legalisasi_nama" id="legalisasi_nama" value="{{ $surveyResponse->legalisasi_nama ?? '' }}"
                           class="form-control" required
                           placeholder="Masukkan nama penanggung jawab">
                </div>

                <!-- Question 111 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">111.</span>
                        <span>Jabatan:</span>
                    </label>
                    <input type="text" name="legalisasi_jabatan" id="legalisasi_jabatan" value="{{ $surveyResponse->legalisasi_jabatan ?? '' }}"
                           class="form-control" required
                           placeholder="Masukkan jabatan">
                </div>
            </div>
        </div>

        <!-- BPS Provinsi - COMMENTED OUT -->
        {{-- 
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BPS Provinsi</h3>
            </div>
            <div class="form-grid">
                <!-- Question 112 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">112.</span>
                        <span>Penghubung:</span>
                    </label>
                    <input type="text" name="bps_provinsi_penghubung" id="bps_provinsi_penghubung" value="{{ $surveyResponse->bps_provinsi_penghubung ?? '' }}"
                           class="form-control"
                           placeholder="Masukkan nama penghubung BPS Provinsi">
                </div>

                <!-- Question 113 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">113.</span>
                        <span>Telepon:</span>
                    </label>
                    <input type="text" name="bps_provinsi_telepon" id="bps_provinsi_telepon" value="{{ $surveyResponse->bps_provinsi_telepon ?? '' }}"
                           class="form-control"
                           placeholder="Masukkan nomor telepon BPS Provinsi">
                </div>

                <!-- Question 114 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">114.</span>
                        <span>Fax:</span>
                    </label>
                    <input type="text" name="bps_provinsi_fax" id="bps_provinsi_fax" value="{{ $surveyResponse->bps_provinsi_fax ?? '' }}"
                           class="form-control"
                           placeholder="Masukkan nomor fax BPS Provinsi">
                </div>

                <!-- Question 115 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">115.</span>
                        <span>Email:</span>
                    </label>
                    <input type="email" name="bps_provinsi_email" id="bps_provinsi_email" value="{{ $surveyResponse->bps_provinsi_email ?? '' }}"
                           class="form-control"
                           placeholder="Masukkan email BPS Provinsi">
                </div>

                <!-- Question 116 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">116.</span>
                        <span>Alamat:</span>
                    </label>
                    <textarea name="bps_provinsi_alamat" id="bps_provinsi_alamat" rows="3"
                              class="form-control textarea"
                              placeholder="Masukkan alamat lengkap BPS Provinsi">{{ $surveyResponse->bps_provinsi_alamat ?? '' }}</textarea>
                </div>
            </div>
        </div>
        --}}

        <!-- BPS RI (Static Information) -->
        <div class="form-section" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(37, 99, 235, 0.05));">
            <div class="section-header" style="background: rgba(59, 130, 246, 0.1);">
                <h3 class="section-title">BPS RI (Informasi Statis)</h3>
            </div>
            <div class="form-grid">
                <!-- Question 117 -->
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">117.</span>
                        <span>Penghubung:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['penghubung'] }}</span>
                </div>

                <!-- Question 118 -->
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">118.</span>
                        <span>Telepon:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['telepon'] }}</span>
                </div>

                <!-- Question 119 -->
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">119.</span>
                        <span>Fax:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['fax'] }}</span>
                </div>

                <!-- Question 120 -->
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">120.</span>
                        <span>Email:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['email'] }}</span>
                </div>

                <!-- Question 121 -->
                <div class="form-row">
                    <span class="form-label">
                        <span class="question-number">121.</span>
                        <span>Alamat:</span>
                    </span>
                    <span class="form-control" style="background: transparent; border: none; padding: var(--spacing-3) 0;">{{ $bpsRiData['alamat'] }}</span>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <div class="flex items-center gap-4">
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
                    Simpan & Lanjut ke Bab 2
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
    autoSave: '{{ route("survey.sibstr.autosave") }}',
    saveAll: '{{ route("survey.sibstr.save") }}',
    status: '{{ route("survey.sibstr.status") }}',
    nextBlok: '{{ route("survey.sibstr.blok2") }}'
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
@endpush
@endsection