@extends('layouts.app')

@section('title', 'SURVEI INDUSTRI BESAR DAN SEDANG TRIWULANAN (SIBSTR) - Blok II - DataKita')
@section('description', 'Survei Industri Besar dan Sedang Triwulanan - Blok II: Pendahuluan')

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
                <h3 class="section-title">BLOK II - II. PENDAHULUAN</h3>
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
                                   class="radio-input">
                            <label for="kondisi_masih_aktif" class="radio-label">
                                a. Masih Aktif
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_belum_beroperasi" value="belum_beroperasi"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'belum_beroperasi' ? 'checked' : '' }}
                                   class="radio-input">
                            <label for="kondisi_belum_beroperasi" class="radio-label">
                                b. Belum Beroperasi
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_tutup" value="tutup"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'tutup' ? 'checked' : '' }}
                                   class="radio-input">
                            <label for="kondisi_tutup" class="radio-label">
                                c. Tutup
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_pindah" value="pindah"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'pindah' ? 'checked' : '' }}
                                   class="radio-input">
                            <label for="kondisi_pindah" class="radio-label">
                                d. Pindah
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_tidak_ditemukan" value="tidak_ditemukan"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'tidak_ditemukan' ? 'checked' : '' }}
                                   class="radio-input">
                            <label for="kondisi_tidak_ditemukan" class="radio-label">
                                e. Tidak Ditemukan
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="kondisi_perusahaan" id="kondisi_double_ganda_duplikat" value="double_ganda_duplikat"
                                   {{ ($surveyResponse->kondisi_perusahaan ?? '') == 'double_ganda_duplikat' ? 'checked' : '' }}
                                   class="radio-input">
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
                                   class="radio-input">
                            <label for="jaringan_pabrik_unit_produksi" class="radio-label">
                                b. Pabrik / Unit Produksi
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan" id="jaringan_pusat_ada_kegiatan_produksi" value="pusat_ada_kegiatan_produksi"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'pusat_ada_kegiatan_produksi' ? 'checked' : '' }}
                                   class="radio-input">
                            <label for="jaringan_pusat_ada_kegiatan_produksi" class="radio-label">
                                c. Pusat ada kegiatan produksi
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="jaringan_unit_kegiatan" id="jaringan_kantor_pusat_administrasi_perwakilan" value="kantor_pusat_administrasi_perwakilan"
                                   {{ ($surveyResponse->jaringan_unit_kegiatan ?? '') == 'kantor_pusat_administrasi_perwakilan' ? 'checked' : '' }}
                                   class="radio-input">
                            <label for="jaringan_kantor_pusat_administrasi_perwakilan" class="radio-label">
                                d. Kantor Pusat / Kantor Administrasi / Kantor Perwakilan
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Question 203 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">203.</span>
                        <span>Rata-rata tenaga kerja di perusahaan pada triwulan ini:</span>
                    </label>
                    <input type="number" name="rata_rata_tenaga_kerja" id="rata_rata_tenaga_kerja" 
                           value="{{ $surveyResponse->rata_rata_tenaga_kerja ?? '' }}"
                           class="form-control" required min="0" step="1"
                           placeholder="Masukkan jumlah rata-rata tenaga kerja">
                </div>

                <!-- Question 204 -->
                <div class="form-row">
                    <label class="form-label required">
                        <span class="question-number">204.</span>
                        <span>Tuliskan kegiatan utama perusahaan beserta KBLI utama:</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">Kegiatan Utama Perusahaan:</label>
                            <textarea name="kegiatan_utama_perusahaan" id="kegiatan_utama_perusahaan" rows="3"
                                      class="form-control textarea" required
                                      placeholder="Tuliskan kegiatan utama perusahaan secara detail">{{ $surveyResponse->kegiatan_utama_perusahaan ?? '' }}</textarea>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">KBLI:</label>
                            <input type="text" name="kbli_utama" id="kbli_utama" 
                                   value="{{ $surveyResponse->kbli_utama ?? '' }}"
                                   class="form-control" required
                                   placeholder="Masukkan kode KBLI utama">
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
window.surveyRoutes = {
    autoSave: '{{ route("survey.sibstr.blok2.autosave") }}',
    saveAll: '{{ route("survey.sibstr.blok2.save") }}',
    status: '{{ route("survey.sibstr.blok2.status") }}',
    backToBlok1: '{{ route("survey.sibstr.blok1") }}',
    nextBlok: '{{ route("survey.sibstr.blok3a") }}', // Conditional navigation handled in JS
    blok3a: '{{ route("survey.sibstr.blok3a") }}',
    blok6: '{{ route("survey.sibstr.blok6") }}'
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-blok2.js') }}"></script>
@endpush
@endsection
