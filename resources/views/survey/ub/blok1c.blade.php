@extends('layouts.user-dashboard')

@section('title', 'Survei UB — Blok I-C: Sertifikasi & Kemitraan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/survey.css') }}">
<link rel="stylesheet" href="{{ asset('css/survey-validation.css') }}">
<style>
.ub-card{background:#fff;border-radius:1rem;border:1px solid #e5e7eb;box-shadow:0 1px 4px rgba(0,0,0,.06);padding:1.75rem 2rem;margin-bottom:1.25rem;}
.dark .ub-card{background:#1f2937;border-color:#374151;}
.ub-section-title{font-size:.7rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#3b82f6;margin-bottom:1rem;padding-bottom:.5rem;border-bottom:2px solid #dbeafe;}
.dark .ub-section-title{color:#93c5fd;border-color:#1e3a5f;}
.ub-label{display:block;font-size:.8125rem;font-weight:600;color:#374151;margin-bottom:.35rem;}
.dark .ub-label{color:#d1d5db;}
.ub-required{color:#ef4444;margin-left:.2rem;}
.ub-input{width:100%;border:1px solid #d1d5db;border-radius:.625rem;padding:.55rem .85rem;font-size:.875rem;color:#111827;background:#fff;transition:border-color .15s;}
.ub-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.dark .ub-input{background:#111827;border-color:#4b5563;color:#f9fafb;}
.ub-input.error{border-color:#ef4444;}
.ub-hint{font-size:.73rem;color:#6b7280;margin-top:.3rem;line-height:1.4;}
.dark .ub-hint{color:#9ca3af;}
.ub-radio-group{display:flex;flex-wrap:wrap;gap:.5rem .75rem;}
.ub-radio-label{display:inline-flex;align-items:center;gap:.45rem;padding:.4rem .85rem;border:1.5px solid #d1d5db;border-radius:.625rem;font-size:.8125rem;cursor:pointer;transition:all .15s;}
.ub-radio-label:has(input:checked){border-color:#3b82f6;background:#eff6ff;color:#1d4ed8;}
.dark .ub-radio-label{border-color:#4b5563;color:#d1d5db;}
.dark .ub-radio-label:has(input:checked){border-color:#3b82f6;background:#1e3a5f;color:#93c5fd;}
.ub-radio-label input{width:.875rem;height:.875rem;accent-color:#3b82f6;}
.ub-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
@media(max-width:640px){.ub-grid-2{grid-template-columns:1fr;}}
.ub-stepper{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ub-step.done{background:#dcfce7;color:#15803d;border:1.5px solid #bbf7d0;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.active{background:#dbeafe;color:#1d4ed8;border:1.5px solid #bfdbfe;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.pending{background:#f3f4f6;color:#9ca3af;border:1.5px solid #e5e7eb;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.dark .ub-step.done{background:#14532d;color:#86efac;border-color:#166534;}
.dark .ub-step.active{background:#1e3a5f;color:#93c5fd;border-color:#1d4ed8;}
.dark .ub-step.pending{background:#374151;color:#6b7280;border-color:#4b5563;}
.ub-err-msg{font-size:.73rem;color:#ef4444;margin-top:.3rem;}
.conditional-section{display:none;}
.kbli-note{background:#fef9c3;border:1px solid #fde68a;border-radius:.625rem;padding:.75rem 1rem;font-size:.78rem;color:#854d0e;margin-bottom:1rem;}
.dark .kbli-note{background:#451a03;border-color:#92400e;color:#fde68a;}
.ptab{border-color:transparent;color:#6b7280;}
.ptab:hover{color:#374151;background:#f9fafb;}
.dark .ptab{color:#9ca3af;}
.dark .ptab:hover{color:#e5e7eb;background:rgba(55,65,81,.4);}
.ptab-active{border-color:#3b82f6 !important;color:#1d4ed8 !important;background:#eff6ff !important;}
.dark .ptab-active{color:#93c5fd !important;background:rgba(30,58,95,.5) !important;}
</style>
@endpush

@section('dashboard-content')
<div class="lg:hidden mb-4">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400" type="button" data-open-sidebar>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>Menu
  </button>
</div>

<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">Blok I-C — Sertifikasi &amp; Kemitraan</h1>
    <p class="ud-page-description">SE2026-L.UB · Pertanyaan 15–19: Sertifikat halal, izin edar, KDKMP, MBG, ekspor/impor</p>
  </div>
  <a href="{{ route('survey.ub.blok1b') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Kembali
  </a>
</div>

<div class="flex gap-5 items-start mt-4">
@include('survey.ub.partials.sidebar')
<div class="flex-1 min-w-0">

<div id="globalErr" class="hidden mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm dark:bg-red-900/30 dark:border-red-700 dark:text-red-300"></div>

<div id="autosave-status" class="autosave-status hidden" style="margin-bottom:.75rem;"><span id="autosave-text"></span></div>

<form id="survey-form" method="POST" action="{{ ($editMode ?? false) ? route('survey.ub.edit.blok1c.save') : route('survey.ub.blok1c.save') }}" novalidate>
@csrf

{{-- Q15: Sertifikat Halal --}}
<div class="ub-card">
  <p class="ub-section-title">15. Sertifikat Halal (BPJPH)</p>
  <div class="kbli-note">
    Rincian 15 hanya ditanyakan kepada kategori usaha/perusahaan khusus berdasarkan BPJPH.
    Lihat daftar KBLI di: <a href="http://s.bps.go.id/kbli_halal" target="_blank" class="underline">http://s.bps.go.id/kbli_halal</a>
  </div>
  <div class="mb-4">
    <label class="ub-label">a. Apakah produk usaha/perusahaan ini sudah mendapatkan sertifikat halal?</label>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="sertifikat_halal" value="1" id="halal_ya_bpjph" {{ old('sertifikat_halal',$response->sertifikat_halal)==1?'checked':'' }}> 1. Ya, oleh BPJPH</label>
      <label class="ub-radio-label"><input type="radio" name="sertifikat_halal" value="2" id="halal_ya_bukan" {{ old('sertifikat_halal',$response->sertifikat_halal)==2?'checked':'' }}> 2. Ya, bukan oleh BPJPH</label>
      <label class="ub-radio-label"><input type="radio" name="sertifikat_halal" value="3" id="halal_tidak" {{ old('sertifikat_halal',$response->sertifikat_halal)==3?'checked':'' }}> 3. Belum/tidak</label>
      <label class="ub-radio-label"><input type="radio" name="sertifikat_halal" value="4" id="halal_proses" {{ old('sertifikat_halal',$response->sertifikat_halal)==4?'checked':'' }}> 4. Dalam proses</label>
    </div>
  </div>
  <div id="sec_halal_bpjph" class="conditional-section mb-3">
    <label class="ub-label">b. Jumlah varian produk yang sudah bersertifikat halal BPJPH <span class="ub-required">*</span></label>
    <div class="flex items-center gap-2">
      <input name="jumlah_produk_halal_bpjph" type="number" min="0" class="ub-input" style="max-width:120px;" value="{{ old('jumlah_produk_halal_bpjph',$response->jumlah_produk_halal_bpjph) }}" placeholder="0">
      <span class="text-sm text-gray-500">varian</span>
    </div>
    <div class="ub-err-msg" data-field="jumlah_produk_halal_bpjph"></div>
  </div>
  <div>
    <label class="ub-label">c. Jumlah varian produk yang belum bersertifikat halal BPJPH <span class="ub-required">*</span></label>
    <div class="flex items-center gap-2">
      <input name="jumlah_produk_belum_halal_bpjph" type="number" min="0" class="ub-input" style="max-width:120px;" value="{{ old('jumlah_produk_belum_halal_bpjph',$response->jumlah_produk_belum_halal_bpjph) }}" placeholder="0">
      <span class="text-sm text-gray-500">varian</span>
    </div>
    <div class="ub-err-msg" data-field="jumlah_produk_belum_halal_bpjph"></div>
  </div>
</div>

{{-- Q16: Izin Edar --}}
<div class="ub-card">
  <p class="ub-section-title">16. Izin Edar (BPOM)</p>
  <div class="kbli-note">
    Rincian 16 hanya ditanyakan kepada kategori usaha/perusahaan khusus berdasarkan BPOM.
    Lihat daftar KBLI di: <a href="http://s.bps.go.id/kbli_izinedar" target="_blank" class="underline">http://s.bps.go.id/kbli_izinedar</a>
  </div>
  <div class="mb-4">
    <label class="ub-label">a. Apakah perusahaan ini memiliki izin edar?</label>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="izin_edar" value="1" id="izin_ya_bpom" {{ old('izin_edar',$response->izin_edar)==1?'checked':'' }}> 1. Ya, oleh BPOM</label>
      <label class="ub-radio-label"><input type="radio" name="izin_edar" value="2" id="izin_ya_bukan" {{ old('izin_edar',$response->izin_edar)==2?'checked':'' }}> 2. Ya, bukan oleh BPOM</label>
      <label class="ub-radio-label"><input type="radio" name="izin_edar" value="3" id="izin_tidak" {{ old('izin_edar',$response->izin_edar)==3?'checked':'' }}> 3. Tidak</label>
    </div>
  </div>
  <div id="sec_izin_edar_bpom" class="conditional-section mb-3">
    <label class="ub-label">b. Jumlah varian produk dengan izin edar BPOM <span class="ub-required">*</span></label>
    <div class="flex items-center gap-2">
      <input name="jumlah_produk_izin_edar_bpom" type="number" min="0" class="ub-input" style="max-width:120px;" value="{{ old('jumlah_produk_izin_edar_bpom',$response->jumlah_produk_izin_edar_bpom) }}" placeholder="0">
      <span class="text-sm text-gray-500">varian</span>
    </div>
    <div class="ub-err-msg" data-field="jumlah_produk_izin_edar_bpom"></div>
  </div>
  <div>
    <label class="ub-label">c. Jumlah varian produk tanpa izin edar BPOM <span class="ub-required">*</span></label>
    <div class="flex items-center gap-2">
      <input name="jumlah_produk_tanpa_izin_edar_bpom" type="number" min="0" class="ub-input" style="max-width:120px;" value="{{ old('jumlah_produk_tanpa_izin_edar_bpom',$response->jumlah_produk_tanpa_izin_edar_bpom) }}" placeholder="0">
      <span class="text-sm text-gray-500">varian</span>
    </div>
    <div class="ub-err-msg" data-field="jumlah_produk_tanpa_izin_edar_bpom"></div>
  </div>
</div>

{{-- Q17: KDKMP --}}
<div class="ub-card">
  <p class="ub-section-title">17. Kemitraan KDKMP</p>
  <label class="ub-label">Apakah perusahaan ini bermitra dengan Koperasi Desa/Kelurahan Merah Putih (KDKMP)? <span class="ub-required">*</span></label>
  <div class="ub-radio-group mt-1">
    <label class="ub-radio-label"><input type="radio" name="bermitra_kdkmp" value="1" {{ old('bermitra_kdkmp',$response->bermitra_kdkmp)==1?'checked':'' }}> 1. Ya</label>
    <label class="ub-radio-label"><input type="radio" name="bermitra_kdkmp" value="2" {{ old('bermitra_kdkmp',$response->bermitra_kdkmp)==2?'checked':'' }}> 2. Tidak</label>
  </div>
  <div class="ub-err-msg" data-field="bermitra_kdkmp"></div>
</div>

{{-- Q18: MBG --}}
<div class="ub-card">
  <p class="ub-section-title">18. Program Makan Bergizi Gratis (MBG)</p>
  <label class="ub-label">Apakah perusahaan ini terlibat dalam program Makan Bergizi Gratis (MBG)? <span class="ub-required">*</span></label>
  <div class="ub-radio-group mt-1">
    <label class="ub-radio-label"><input type="radio" name="terlibat_mbg" value="1" {{ old('terlibat_mbg',$response->terlibat_mbg)==1?'checked':'' }}> 1. Ya, sebagai SATUAN PELAYANAN PEMENUHAN GIZI (SPPG)</label>
    <label class="ub-radio-label"><input type="radio" name="terlibat_mbg" value="2" {{ old('terlibat_mbg',$response->terlibat_mbg)==2?'checked':'' }}> 2. Ya, sebagai supplier</label>
    <label class="ub-radio-label"><input type="radio" name="terlibat_mbg" value="3" {{ old('terlibat_mbg',$response->terlibat_mbg)==3?'checked':'' }}> 3. Ya, sebagai penerima manfaat MBG (Sekolah, Puskesmas, Posyandu)</label>
    <label class="ub-radio-label"><input type="radio" name="terlibat_mbg" value="4" {{ old('terlibat_mbg',$response->terlibat_mbg)==4?'checked':'' }}> 4. Ya, peran lainnya</label>
    <label class="ub-radio-label"><input type="radio" name="terlibat_mbg" value="5" {{ old('terlibat_mbg',$response->terlibat_mbg)==5?'checked':'' }}> 5. Tidak terlibat MBG</label>
  </div>
  <div class="ub-err-msg" data-field="terlibat_mbg"></div>
</div>

{{-- Q19: Ekspor/Impor --}}
<div class="ub-card">
  <p class="ub-section-title">19. Penjualan/Pembelian kepada Bukan Penduduk Indonesia (1 Mei 2024 s.d. 31 Agustus 2026)</p>
  {{-- Petunjuk Q19 tabbed panel --}}
  <div class="mb-4" id="petunjuk19Wrap">
    <button type="button" id="petunjuk19Toggle"
      class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
      <svg id="petunjuk19Chevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
      </svg>
      <span id="petunjuk19Label">Lihat petunjuk &amp; contoh transaksi</span>
    </button>
    <div id="petunjuk19Panel" class="hidden mt-3 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 overflow-hidden">
      <div class="flex overflow-x-auto border-b border-blue-100 dark:border-blue-900/60 bg-white/70 dark:bg-gray-800/50">
        <button type="button" data-tab="q1" class="ptab ptab-active shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Definisi Bukan Penduduk</button>
        <button type="button" data-tab="q2" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">19a. Contoh Barang</button>
        <button type="button" data-tab="q3" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">19b. Contoh Jasa</button>
      </div>
      <div class="p-4 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">

        <div data-panel="q1">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Siapa yang dimaksud Bukan Penduduk Indonesia?</p>
          <p class="mb-3">Bukan penduduk adalah orang atau badan yang <strong>pusat kegiatan ekonominya berada di luar Indonesia</strong>.</p>
          <div class="space-y-2">
            <div>
              <p class="font-semibold text-gray-600 dark:text-gray-400 mb-1">Contoh orang:</p>
              <ul class="list-disc list-inside pl-1 space-y-0.5">
                <li>Turis/wisatawan mancanegara yang berwisata kurang dari 1 tahun</li>
              </ul>
            </div>
            <div>
              <p class="font-semibold text-gray-600 dark:text-gray-400 mb-1">Contoh badan:</p>
              <ul class="list-disc list-inside pl-1 space-y-0.5">
                <li>Usaha/perusahaan yang <strong>tidak</strong> terdaftar badan usahanya di Indonesia</li>
              </ul>
            </div>
          </div>
        </div>

        <div data-panel="q2" class="hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-3">19a. Contoh Penjualan/Pembelian Barang</p>
          <div class="space-y-3">
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-2.5">
              <p class="font-semibold text-green-700 dark:text-green-400 mb-1">↗ Ekspor Barang</p>
              <p>Pengrajin perak di Bali mengirimkan aksesori dan perhiasan ke pembeli di luar negeri.</p>
            </div>
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-2.5">
              <p class="font-semibold text-orange-700 dark:text-orange-400 mb-1">↙ Impor Barang</p>
              <p>Toko elektronik Indonesia mengimpor smartphone dari Tiongkok untuk dijual kembali.</p>
            </div>
          </div>
        </div>

        <div data-panel="q3" class="hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-3">19b. Contoh Penjualan/Pembelian Jasa</p>
          <div class="space-y-3">
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-2.5">
              <p class="font-semibold text-green-700 dark:text-green-400 mb-1">↗ Ekspor Jasa</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Konsultan Indonesia memberikan jasa pelatihan secara online ke perusahaan luar negeri</li>
                <li>Hotel di Indonesia melayani tamu wisatawan mancanegara</li>
              </ul>
            </div>
            <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-2.5">
              <p class="font-semibold text-orange-700 dark:text-orange-400 mb-1">↙ Impor Jasa</p>
              <ul class="list-disc list-inside space-y-0.5 pl-1">
                <li>Usaha/perusahaan Indonesia berlangganan layanan digital berbayar dari perusahaan luar negeri <em>(Canva, Microsoft 365, Google Cloud, Adobe, ChatGPT, dll.)</em></li>
                <li>Perusahaan Indonesia menggunakan jasa konsultan asing</li>
              </ul>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
  <div class="mb-4">
    <label class="ub-label">a. Barang <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="ekspor_impor_barang" value="1" {{ old('ekspor_impor_barang',$response->ekspor_impor_barang)==1?'checked':'' }}> 1. Ya</label>
      <label class="ub-radio-label"><input type="radio" name="ekspor_impor_barang" value="2" {{ old('ekspor_impor_barang',$response->ekspor_impor_barang)==2?'checked':'' }}> 2. Tidak</label>
    </div>
    <div class="ub-err-msg" data-field="ekspor_impor_barang"></div>
  </div>
  <div>
    <label class="ub-label">b. Jasa <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="ekspor_impor_jasa" value="1" {{ old('ekspor_impor_jasa',$response->ekspor_impor_jasa)==1?'checked':'' }}> 1. Ya</label>
      <label class="ub-radio-label"><input type="radio" name="ekspor_impor_jasa" value="2" {{ old('ekspor_impor_jasa',$response->ekspor_impor_jasa)==2?'checked':'' }}> 2. Tidak</label>
    </div>
    <div class="ub-err-msg" data-field="ekspor_impor_jasa"></div>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-4 mt-6 mb-8">
  <a href="{{ route('survey.ub.blok1b') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali ke Blok I-B
  </a>
  <button type="submit" id="submitBtn" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition">
    Simpan &amp; Lanjut
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
</div>
</form>
</div>
</div>

@push('scripts')
<script>
window.surveyRoutes = {
    autoSave: '{{ route("survey.ub.blok1c.autosave") }}',
    saveAll:  '{{ ($editMode ?? false) ? route("survey.ub.edit.blok1c.save") : route("survey.ub.blok1c.save") }}',
    status:   '{{ route("survey.ub.blok1c.status") }}',
    nextBlok: '{{ route("survey.ub.blok1d") }}',
    showGuidanceNearSubmit: false
};
(function(){
  // Tabbed petunjuk panels
  function initPetunjukPanel(toggleId, panelId, chevronId, labelId, openText, closeText) {
    const toggle = document.getElementById(toggleId);
    const panel  = document.getElementById(panelId);
    const chevron= document.getElementById(chevronId);
    const label  = document.getElementById(labelId);
    if (!toggle) return;
    toggle.addEventListener('click', function() {
      const open = !panel.classList.contains('hidden');
      panel.classList.toggle('hidden', open);
      chevron.style.transform = open ? '' : 'rotate(90deg)';
      label.textContent = open ? openText : closeText;
    });
    panel.querySelectorAll('[data-tab]').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const t = this.dataset.tab;
        panel.querySelectorAll('[data-tab]').forEach(b => b.classList.remove('ptab-active'));
        this.classList.add('ptab-active');
        panel.querySelectorAll('[data-panel]').forEach(p => p.classList.add('hidden'));
        panel.querySelector('[data-panel="' + t + '"]').classList.remove('hidden');
      });
    });
  }
  initPetunjukPanel('petunjuk19Toggle', 'petunjuk19Panel', 'petunjuk19Chevron', 'petunjuk19Label', 'Lihat petunjuk & contoh transaksi', 'Sembunyikan petunjuk');

  function toggleHalal(){
    const v = document.querySelector('input[name="sertifikat_halal"]:checked')?.value;
    document.getElementById('sec_halal_bpjph').style.display = v == '1' ? 'block' : 'none';
  }
  document.querySelectorAll('input[name="sertifikat_halal"]').forEach(el => el.addEventListener('change', toggleHalal));
  toggleHalal();

  function toggleIzin(){
    const v = document.querySelector('input[name="izin_edar"]:checked')?.value;
    document.getElementById('sec_izin_edar_bpom').style.display = v == '1' ? 'block' : 'none';
  }
  document.querySelectorAll('input[name="izin_edar"]').forEach(el => el.addEventListener('change', toggleIzin));
  toggleIzin();
})();
</script>
<script src="{{ asset('js/survey-ub-blok1c.js') }}"></script>
<script src="{{ asset('js/survey.js') }}"></script>
@endpush
@endsection
