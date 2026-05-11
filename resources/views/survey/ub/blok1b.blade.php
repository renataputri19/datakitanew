@extends('layouts.user-dashboard')

@section('title', 'Survei UB — Blok I-B: Kegiatan & Digital')

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
.ub-input{width:100%;border:1px solid #d1d5db;border-radius:.625rem;padding:.55rem .85rem;font-size:.875rem;color:#111827;background:#fff;transition:border-color .15s,box-shadow .15s;}
.ub-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.dark .ub-input{background:#111827;border-color:#4b5563;color:#f9fafb;}
.ub-input.error{border-color:#ef4444;}
.klu-wrap{position:relative;width:100%;}
.klu-btn{display:flex;align-items:center;justify-content:space-between;gap:.5rem;width:100%;text-align:left;border:1px solid #d1d5db;border-radius:.625rem;padding:.5rem .85rem;font-size:.875rem;color:#111827;background:#fff;cursor:pointer;transition:border-color .15s,box-shadow .15s;min-height:2.25rem;}
.klu-btn:focus,.klu-btn.open{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.klu-btn.error{border-color:#ef4444 !important;}
.dark .klu-btn{background:#111827;border-color:#4b5563;color:#f9fafb;}
.klu-btn>svg{flex-shrink:0;transition:transform .2s;color:#6b7280;}
.klu-btn.open>svg{transform:rotate(180deg);}
.klu-list{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #e5e7eb;border-radius:.75rem;box-shadow:0 6px 20px rgba(0,0,0,.1);z-index:200;max-height:232px;overflow-y:auto;padding:.3rem;}
.dark .klu-list{background:#1f2937;border-color:#374151;}
.klu-item{display:flex;align-items:center;gap:.55rem;padding:.4rem .65rem;border-radius:.5rem;cursor:pointer;font-size:.8rem;color:#374151;transition:background .1s;}
.dark .klu-item{color:#d1d5db;}
.klu-item:hover{background:#f3f4f6;}
.dark .klu-item:hover{background:#374151;}
.klu-item.klu-active{background:#eff6ff;color:#1d4ed8;}
.dark .klu-item.klu-active{background:#1e3a5f;color:#93c5fd;}
.klu-code{display:inline-flex;align-items:center;justify-content:center;min-width:1.35rem;height:1.35rem;padding:0 .25rem;background:#3b82f6;color:#fff;border-radius:.3rem;font-size:.65rem;font-weight:700;flex-shrink:0;}
.klu-item.klu-active .klu-code{background:#1d4ed8;}
.klu-empty{color:#9ca3af;}
.ub-hint{font-size:.73rem;color:#6b7280;margin-top:.3rem;line-height:1.4;}
.dark .ub-hint{color:#9ca3af;}
.ub-radio-group{display:flex;flex-wrap:wrap;gap:.5rem .75rem;}
.ub-radio-label{display:inline-flex;align-items:center;gap:.45rem;padding:.4rem .85rem;border:1.5px solid #d1d5db;border-radius:.625rem;font-size:.8125rem;cursor:pointer;transition:all .15s;}
.ub-radio-label:has(input:checked){border-color:#3b82f6;background:#eff6ff;color:#1d4ed8;}
.dark .ub-radio-label{border-color:#4b5563;color:#d1d5db;}
.dark .ub-radio-label:has(input:checked){border-color:#3b82f6;background:#1e3a5f;color:#93c5fd;}
.ub-radio-label input{width:.875rem;height:.875rem;accent-color:#3b82f6;}
.ub-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.ub-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;}
@media(max-width:640px){.ub-grid-2,.ub-grid-3{grid-template-columns:1fr;}}
.ub-stepper{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;margin-bottom:1.5rem;}
.ub-step.done{background:#dcfce7;color:#15803d;border:1.5px solid #bbf7d0;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.active{background:#dbeafe;color:#1d4ed8;border:1.5px solid #bfdbfe;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.ub-step.pending{background:#f3f4f6;color:#9ca3af;border:1.5px solid #e5e7eb;padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;}
.dark .ub-step.done{background:#14532d;color:#86efac;border-color:#166534;}
.dark .ub-step.active{background:#1e3a5f;color:#93c5fd;border-color:#1d4ed8;}
.dark .ub-step.pending{background:#374151;color:#6b7280;border-color:#4b5563;}
.ub-err-msg{font-size:.73rem;color:#ef4444;margin-top:.3rem;}
.ub-checkbox-group{display:flex;flex-wrap:wrap;gap:.5rem .75rem;}
.ub-check-label{display:inline-flex;align-items:center;gap:.45rem;padding:.4rem .85rem;border:1.5px solid #d1d5db;border-radius:.625rem;font-size:.8125rem;cursor:pointer;transition:all .15s;}
.ub-check-label:has(input:checked){border-color:#3b82f6;background:#eff6ff;color:#1d4ed8;}
.dark .ub-check-label{border-color:#4b5563;color:#d1d5db;}
.dark .ub-check-label:has(input:checked){border-color:#3b82f6;background:#1e3a5f;color:#93c5fd;}
.conditional-section{display:none;}
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
    <h1 class="ud-page-title">Blok I-B — Kegiatan &amp; Digital</h1>
    <p class="ud-page-description">SE2026-L.UB · Pertanyaan 9–14: Kegiatan usaha, jaringan, internet, teknologi, lingkungan, karya seni</p>
  </div>
  <a href="{{ route('survey.ub.blok1a') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Kembali
  </a>
</div>

<div class="flex gap-5 items-start mt-4">
@include('survey.ub.partials.sidebar')
<div class="flex-1 min-w-0">

<div id="globalErr" class="hidden mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm dark:bg-red-900/30 dark:border-red-700 dark:text-red-300"></div>

<div id="autosave-status" class="autosave-status hidden" style="margin-bottom:.75rem;"><span id="autosave-text"></span></div>

<form id="survey-form" method="POST" action="{{ ($editMode ?? false) ? route('survey.ub.edit.blok1b.save') : route('survey.ub.blok1b.save') }}" novalidate>
@csrf

{{-- Q9: Kegiatan Utama dan Produk Utama --}}
<div class="ub-card">
  <p class="ub-section-title">9. Kegiatan Utama dan Produk Utama Perusahaan</p>

  {{-- 9a --}}
  <div class="mb-4">
    <label class="ub-label">a. Apa kegiatan utama perusahaan ini? Tuliskan selengkapnya. <span class="ub-required">*</span></label>
    <textarea name="kegiatan_utama" class="ub-input" rows="3" placeholder="Tuliskan selengkapnya…">{{ old('kegiatan_utama',$response->kegiatan_utama) }}</textarea>
    {{-- Petunjuk 9a --}}
    <div class="mt-3" id="petunjuk9aWrap">
      <button type="button" id="petunjuk9aToggle"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
        <svg id="petunjuk9aChevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
        <span id="petunjuk9aLabel">Lihat contoh kegiatan utama</span>
      </button>
      <div id="petunjuk9aPanel" class="hidden mt-3 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 overflow-hidden">
        <div class="flex overflow-x-auto border-b border-blue-100 dark:border-blue-900/60 bg-white/70 dark:bg-gray-800/50">
          <button type="button" data-tab="a1" class="ptab ptab-active shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Pertanian &amp; Perikanan</button>
          <button type="button" data-tab="a2" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Industri &amp; Pengolahan</button>
          <button type="button" data-tab="a3" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Perdagangan &amp; Transportasi</button>
          <button type="button" data-tab="a4" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Akomodasi &amp; Jasa</button>
          <button type="button" data-tab="a5" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Kreatif &amp; Teknologi</button>
        </div>
        <div class="p-4 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">

          <div data-panel="a1">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Pertanian, Peternakan &amp; Perikanan</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>membudidayakan tanaman padi</li>
              <li>membudidayakan cabai</li>
              <li>membudidayakan tanaman tembakau</li>
              <li>membudidayakan udang di air payau</li>
              <li>membibitkan ayam ras</li>
              <li>menangkap ikan konsumsi di sungai</li>
              <li>memungut madu di hutan</li>
            </ul>
          </div>

          <div data-panel="a2" class="hidden">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Industri &amp; Pengolahan</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>menggiling daging ikan menjadi sosis dan dijual di rumah/online</li>
              <li>membuat kebab di rumah dan dititipkan di warung</li>
              <li>membuat sosis dari ikan, disajikan dengan pembakaran, dijual di depan rumah/online</li>
              <li>membuat kebab berdasarkan pesanan langsung di gerobak depan alfamart</li>
            </ul>
          </div>

          <div data-panel="a3" class="hidden">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Perdagangan &amp; Transportasi</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>menjual mobil bekas di showroom</li>
              <li>menjual kaset video game yang dibeli dari pihak lain di marketplace</li>
              <li>angkutan bus antarkota antarprovinsi</li>
              <li>membuat kunci duplikat di pinggir jalan</li>
            </ul>
          </div>

          <div data-panel="a4" class="hidden">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Akomodasi, Properti &amp; Jasa</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>menyewakan kos bulanan</li>
              <li>menyewakan kamar hotel bintang 5</li>
              <li>jasa borongan/konstruksi rumah</li>
              <li>membuat rumah kemudian dipasarkan sendiri</li>
              <li>menyewakan rumah dengan periode tahunan</li>
              <li>sekolah menengah pertama negeri</li>
              <li>rumah sakit pemerintah</li>
              <li>penyediaan telekomunikasi internet tanpa kabel</li>
              <li>meracik jamu secara langsung untuk siap konsumsi di sebuah kedai</li>
            </ul>
          </div>

          <div data-panel="a5" class="hidden">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Ekonomi Kreatif &amp; Teknologi</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>menjual perangkat lunak video gim yang dikembangkan sendiri</li>
              <li>membuat desain kemasan botol minuman</li>
              <li>membuat seni lukisan yang dipajang untuk dijual</li>
              <li>menulis cerpen</li>
              <li>membuat desain interior rumah</li>
              <li>pengembangan aplikasi video gim</li>
            </ul>
          </div>

        </div>
      </div>
    </div>
    <div class="ub-err-msg" data-field="kegiatan_utama"></div>
  </div>

  {{-- 9b: pilih yang paling sesuai --}}
  <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-700 rounded-xl p-4 mb-4">
    <p class="text-xs font-semibold text-amber-800 dark:text-amber-300 mb-1">b. Pilih yang paling sesuai dengan kegiatan utama perusahaan ini: <span class="ub-required">*</span></p>
    <p class="ub-hint mb-3 text-amber-700 dark:text-amber-400">⚠ Minimal salah satu dari b1 s.d. b4 harus terisi <strong>Ya</strong>.</p>

    {{-- b1 --}}
    <div class="mb-3">
      <label class="ub-label text-xs">b1. Apakah memproduksi barang di lokasi ini? <span class="ub-required">*</span></label>
      <div class="ub-radio-group">
        <label class="ub-radio-label"><input type="radio" name="produksi_di_lokasi" id="b1_ya" value="1" {{ old('produksi_di_lokasi',$response->produksi_di_lokasi)==1?'checked':'' }}> 1. Ya</label>
        <label class="ub-radio-label"><input type="radio" name="produksi_di_lokasi" id="b1_tidak" value="2" {{ old('produksi_di_lokasi',$response->produksi_di_lokasi)==2?'checked':'' }}> 2. Tidak</label>
      </div>
      <div class="ub-err-msg" data-field="produksi_di_lokasi"></div>
    </div>

    {{-- b2 --}}
    <div class="mb-3">
      <label class="ub-label text-xs">b2. Apakah menyediakan layanan makan minum? <span class="ub-required">*</span></label>
      <p class="ub-hint mb-1">Ciri: terdapat kegiatan peracikan, pemanasan ulang, atau pembuatan produk berdasarkan pesanan/permintaan pelanggan.</p>
      <div class="ub-radio-group">
        <label class="ub-radio-label"><input type="radio" name="layanan_makan_minum" id="b2_ya" value="1" {{ old('layanan_makan_minum',$response->layanan_makan_minum)==1?'checked':'' }}> 1. Ya</label>
        <label class="ub-radio-label"><input type="radio" name="layanan_makan_minum" id="b2_tidak" value="2" {{ old('layanan_makan_minum',$response->layanan_makan_minum)==2?'checked':'' }}> 2. Tidak</label>
      </div>
      <div class="ub-err-msg" data-field="layanan_makan_minum"></div>
    </div>

    {{-- b3: shown when b1=Tidak AND b2=Tidak --}}
    <div id="sec_9b3" class="conditional-section mb-3">
      <label class="ub-label text-xs">b3. Apakah melakukan penjualan barang? <span class="ub-required">*</span></label>
      <div class="ub-radio-group">
        <label class="ub-radio-label"><input type="radio" name="penjualan_barang" id="b3_ya" value="1" {{ old('penjualan_barang',$response->penjualan_barang)==1?'checked':'' }}> 1. Ya</label>
        <label class="ub-radio-label"><input type="radio" name="penjualan_barang" id="b3_tidak" value="2" {{ old('penjualan_barang',$response->penjualan_barang)==2?'checked':'' }}> 2. Tidak</label>
      </div>
      <div class="ub-err-msg" data-field="penjualan_barang"></div>
    </div>

    {{-- b4: shown when b1=Tidak AND b2=Tidak AND b3=Tidak --}}
    <div id="sec_9b4" class="conditional-section">
      <label class="ub-label text-xs">b4. Apakah melakukan aktivitas jasa atau kegiatan pertanian? <span class="ub-required">*</span></label>
      <div class="ub-radio-group">
        <label class="ub-radio-label"><input type="radio" name="aktivitas_jasa_pertanian" id="b4_ya" value="1" {{ old('aktivitas_jasa_pertanian',$response->aktivitas_jasa_pertanian)==1?'checked':'' }}> 1. Ya</label>
        <label class="ub-radio-label"><input type="radio" name="aktivitas_jasa_pertanian" id="b4_tidak" value="2" {{ old('aktivitas_jasa_pertanian',$response->aktivitas_jasa_pertanian)==2?'checked':'' }}> 2. Tidak</label>
      </div>
      <div class="ub-err-msg" data-field="aktivitas_jasa_pertanian"></div>
    </div>
  </div>

  {{-- 9c: lokasi usaha — shown when b2=Ya OR b3=Ya --}}
  <div id="sec_9c" class="conditional-section mb-4">
    <label class="ub-label">c. Di mana usaha tersebut biasa dilakukan? <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      @foreach([1=>'Apotek',2=>'Swalayan',3=>'Los Pasar',4=>'Toko, ruko, dan sejenisnya',5=>'Kedai, stan, tenda',6=>'Bar',7=>'Kelab malam, diskotek',8=>'Kafe',9=>'Restoran, warung makan',10=>'Keliling',11=>'Daring (online)'] as $val=>$lbl)
      <label class="ub-radio-label"><input type="radio" name="lokasi_usaha" value="{{ $val }}" {{ old('lokasi_usaha',$response->lokasi_usaha)==$val?'checked':'' }}> {{ $val }}. {{ $lbl }}</label>
      @endforeach
    </div>
    <div class="ub-err-msg" data-field="lokasi_usaha"></div>
  </div>

  {{-- 9d: input produksi — shown when b1=Ya AND b2=Tidak --}}
  <div id="sec_9d" class="conditional-section mb-4">
    <label class="ub-label">d. Apa input yang digunakan? <span class="ub-required">*</span></label>
    <textarea name="input_produksi" class="ub-input" rows="2" placeholder="Tuliskan input bahan baku yang digunakan">{{ old('input_produksi',$response->input_produksi) }}</textarea>
    <div class="mt-2" id="petunjuk9dWrap">
      <button type="button" id="petunjuk9dToggle"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
        <svg id="petunjuk9dChevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
        <span id="petunjuk9dLabel">Lihat contoh input</span>
      </button>
      <div id="petunjuk9dPanel" class="hidden mt-2 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 p-3 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
        <p class="font-semibold text-blue-700 dark:text-blue-300 mb-1.5">Contoh bahan baku/input:</p>
        <ul class="list-disc list-inside space-y-0.5 pl-1">
          <li>daging ikan, tepung</li>
          <li>daging kebab, kulit kebab, sayuran</li>
          <li>bambu</li>
          <li>jagung pipil</li>
          <li>kaca</li>
          <li>kain</li>
          <li>kulit sapi</li>
          <li>kayu bulat</li>
          <li>rotan</li>
          <li>kunci polos</li>
        </ul>
      </div>
    </div>
    <div class="ub-err-msg" data-field="input_produksi"></div>
  </div>

  {{-- 9e: proses produksi — shown when b1=Ya AND b2=Tidak --}}
  <div id="sec_9e" class="conditional-section mb-4">
    <label class="ub-label">e. Bagaimana proses mengubah input tersebut menjadi produk output (beserta alatnya)? <span class="ub-required">*</span></label>
    <textarea name="proses_produksi" class="ub-input" rows="2" placeholder="Tuliskan proses produksi">{{ old('proses_produksi',$response->proses_produksi) }}</textarea>
    <div class="mt-2" id="petunjuk9eWrap">
      <button type="button" id="petunjuk9eToggle"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
        <svg id="petunjuk9eChevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
        <span id="petunjuk9eLabel">Lihat contoh proses produksi</span>
      </button>
      <div id="petunjuk9ePanel" class="hidden mt-2 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 p-3 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">
        <p class="font-semibold text-blue-700 dark:text-blue-300 mb-1.5">Contoh proses produksi:</p>
        <ul class="list-disc list-inside space-y-0.5 pl-1">
          <li>menggiling daging ikan menjadi sosis</li>
          <li>membuat kebab di rumah, dikemas, kemudian dititipkan di warung</li>
          <li>penggaraman</li>
          <li>pengasapan</li>
          <li>pemindangan</li>
          <li>pembekuan</li>
        </ul>
      </div>
    </div>
    <div class="ub-err-msg" data-field="proses_produksi"></div>
  </div>

  {{-- 9f: produk utama — always required --}}
  <div class="mb-4">
    <label class="ub-label">f. Apa produk utama yang dihasilkan? Tuliskan selengkapnya. <span class="ub-required">*</span></label>
    <textarea name="produk_utama" class="ub-input" rows="2" placeholder="Tuliskan produk/jasa utama">{{ old('produk_utama',$response->produk_utama) }}</textarea>
    {{-- Petunjuk 9f --}}
    <div class="mt-3" id="petunjuk9fWrap">
      <button type="button" id="petunjuk9fToggle"
        class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
        <svg id="petunjuk9fChevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
        <span id="petunjuk9fLabel">Lihat contoh produk utama</span>
      </button>
      <div id="petunjuk9fPanel" class="hidden mt-3 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 overflow-hidden">
        <div class="flex overflow-x-auto border-b border-blue-100 dark:border-blue-900/60 bg-white/70 dark:bg-gray-800/50">
          <button type="button" data-tab="p1" class="ptab ptab-active shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Pertanian &amp; Perikanan</button>
          <button type="button" data-tab="p2" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Industri Pengolahan</button>
          <button type="button" data-tab="p3" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Perdagangan &amp; Transportasi</button>
          <button type="button" data-tab="p4" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Akomodasi &amp; Jasa</button>
          <button type="button" data-tab="p5" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Kreatif &amp; Teknologi</button>
        </div>
        <div class="p-4 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">

          <div data-panel="p1">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Pertanian, Peternakan &amp; Perikanan</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>padi</li>
              <li>tembakau</li>
              <li>udang</li>
              <li>ayam ras</li>
              <li>ikan segar konsumsi</li>
              <li>madu hutan</li>
            </ul>
          </div>

          <div data-panel="p2" class="hidden">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Industri Pengolahan</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>sosis</li>
              <li>kebab yang sudah dikemas</li>
              <li>sosis ikan yang dibakar</li>
              <li>rendang</li>
              <li>gudeg</li>
            </ul>
          </div>

          <div data-panel="p3" class="hidden">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Perdagangan &amp; Transportasi</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>jasa perdagangan mobil bekas</li>
              <li>jasa perdagangan kaset video gim</li>
              <li>jasa angkutan bus antarkota antarprovinsi</li>
              <li>jasa pembuatan kunci duplikat</li>
            </ul>
          </div>

          <div data-panel="p4" class="hidden">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Akomodasi, Properti &amp; Jasa</p>
            <ul class="list-disc list-inside space-y-0.5 pl-1">
              <li>jasa sewa kos bulanan</li>
              <li>jasa penyediaan akomodasi hotel bintang 5</li>
              <li>jasa penyajian kebab</li>
              <li>jasa renovasi/membuat rumah</li>
              <li>jasa penjualan rumah yang dibangun sendiri</li>
              <li>jasa penyediaan rumah dengan periode tahunan</li>
              <li>jasa pendidikan sekolah menengah pertama negeri</li>
              <li>jasa kesehatan oleh rumah sakit pemerintah</li>
            </ul>
          </div>

          <div data-panel="p5" class="hidden">
            <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Ekonomi Kreatif &amp; Teknologi</p>
            <div class="space-y-2">
              <div>
                <p class="font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Karya seni &amp; sastra:</p>
                <ul class="list-disc list-inside space-y-0.5 pl-1">
                  <li>lukisan, patung, kerajinan</li>
                  <li>musik, tari, foto, film</li>
                  <li>ilustrasi, animasi, board game</li>
                  <li>puisi, cerpen, novel, naskah drama</li>
                  <li>gamelan, angklung</li>
                </ul>
              </div>
              <div>
                <p class="font-semibold text-gray-600 dark:text-gray-400 mb-0.5">Desain &amp; teknologi:</p>
                <ul class="list-disc list-inside space-y-0.5 pl-1">
                  <li>desain arsitektur, desain produk, desain interior</li>
                  <li>desain komunikasi visual, desain fesyen</li>
                  <li>pembuatan perangkat lunak (software)</li>
                  <li>aplikasi digital, aplikasi gim, perangkat elektronik</li>
                  <li>inovasi berbasis kecerdasan buatan</li>
                  <li>jasa pengembangan perangkat lunak video gim</li>
                  <li>jasa telekomunikasi internet tanpa kabel</li>
                </ul>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
    <div class="ub-err-msg" data-field="produk_utama"></div>
  </div>

  {{-- 9g & 9h: Kode KBLI & Kategori Lapangan Usaha --}}
  <div class="ub-grid-2 mb-4">
    <div>
      <label class="ub-label">g. Kode KBLI <span class="ub-required">*</span></label>
      <input name="kode_kbli" class="ub-input" maxlength="10" value="{{ old('kode_kbli',$response->kode_kbli) }}" placeholder="Contoh: 47711">
      <p class="ub-hint">Kode KBLI 5 digit. Contoh: 47711</p>
      <div class="ub-err-msg" data-field="kode_kbli"></div>
    </div>
    <div>
      <label class="ub-label">h. Kategori Lapangan Usaha <span class="ub-required">*</span></label>
      @php
      $kbliKategori = [
        'A' => 'Pertanian, Kehutanan, dan Perikanan',
        'B' => 'Pertambangan dan Penggalian',
        'C' => 'Industri',
        'D' => 'Penyediaan Listrik, Gas, Uap/Air Panas, dan Udara Dingin',
        'E' => 'Penyediaan Air; Pengelolaan Air Limbah, Penanganan Limbah, dan Remediasi',
        'F' => 'Konstruksi',
        'G' => 'Perdagangan Besar dan Eceran',
        'H' => 'Transportasi dan Penyimpanan',
        'I' => 'Aktivitas Penyediaan Akomodasi dan Makan Minum',
        'J' => 'Aktivitas Penerbitan, Penyiaran, serta Produksi dan Distribusi Konten',
        'K' => 'Aktivitas Telekomunikasi, Pemrograman Komputer, Konsultansi, Infrastruktur Komputasi, dan Jasa Informasi Lainnya',
        'L' => 'Aktivitas Keuangan dan Asuransi',
        'M' => 'Aktivitas Real Estat',
        'N' => 'Aktivitas Profesional, Ilmiah, dan Teknis',
        'O' => 'Aktivitas Administratif dan Penunjang Usaha',
        'P' => 'Administrasi Pemerintahan dan Pertahanan, Serta Jaminan Sosial Wajib',
        'Q' => 'Pendidikan',
        'R' => 'Aktivitas Kesehatan Manusia dan Aktivitas Sosial',
        'S' => 'Kesenian, Olahraga, dan Rekreasi',
        'T' => 'Aktivitas Jasa Lainnya',
        'U' => 'Aktivitas Rumah Tangga sebagai Pemberi Kerja dan Aktivitas Produksi Barang dan Jasa oleh Rumah Tangga untuk Keperluan Sendiri yang Tidak Terdiferensiasi',
        'V' => 'Aktivitas Badan Internasional dan Badan Ekstra Internasional Lainnya',
      ];
      $savedKlu = old('kategori_lapangan_usaha', $response->kategori_lapangan_usaha);
      @endphp
      <select name="kategori_lapangan_usaha" id="klu-select" style="display:none;" aria-hidden="true" tabindex="-1">
        <option value=""></option>
        @foreach($kbliKategori as $kode => $nama)
        <option value="{{ $kode }}" {{ $savedKlu==$kode?'selected':'' }}>{{ $kode }}</option>
        @endforeach
      </select>
      <div class="klu-wrap" id="klu-wrap">
        <button type="button" class="klu-btn" id="klu-btn" aria-haspopup="listbox" aria-expanded="false">
          <span id="klu-display">
            @if($savedKlu && isset($kbliKategori[$savedKlu]))
              <span class="klu-code">{{ $savedKlu }}</span>&nbsp;<span>{{ $kbliKategori[$savedKlu] }}</span>
            @else
              <span class="klu-empty">— Pilih Kategori —</span>
            @endif
          </span>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="klu-list" id="klu-list" role="listbox" style="display:none;">
          <div class="klu-item" data-val="" role="option">
            <span class="klu-empty">— Pilih Kategori —</span>
          </div>
          @foreach($kbliKategori as $kode => $nama)
          <div class="klu-item{{ $savedKlu==$kode?' klu-active':'' }}" data-val="{{ $kode }}" data-code="{{ $kode }}" data-name="{{ $nama }}" role="option" aria-selected="{{ $savedKlu==$kode?'true':'false' }}">
            <span class="klu-code">{{ $kode }}</span>
            <span>{{ $nama }}</span>
          </div>
          @endforeach
        </div>
      </div>
      <p class="ub-hint">Pilih kategori KBLI sesuai bidang usaha utama perusahaan.</p>
      <div class="ub-err-msg" data-field="kategori_lapangan_usaha"></div>
    </div>
  </div>

  {{-- 9i: klasifikasi akomodasi — optional, only for accommodation --}}
  <div class="mb-2">
    <label class="ub-label">i. Jika perusahaan merupakan akomodasi jangka pendek, apa klasifikasi perusahaan ini?</label>
    <p class="ub-hint mb-1">Hanya isi jika perusahaan bergerak di bidang akomodasi jangka pendek.</p>
    <div class="ub-radio-group">
      @foreach([1=>'Hotel Bintang 1',2=>'Hotel Bintang 2',3=>'Hotel Bintang 3',4=>'Hotel Bintang 4',5=>'Hotel Bintang 5',6=>'Lainnya (hotel nonbintang, vila, dll)'] as $val=>$lbl)
      <label class="ub-radio-label"><input type="radio" name="klasifikasi_akomodasi" value="{{ $val }}" {{ old('klasifikasi_akomodasi',$response->klasifikasi_akomodasi)==$val?'checked':'' }}> {{ $val }}. {{ $lbl }}</label>
      @endforeach
    </div>
  </div>
</div>

{{-- Q10 & Q11: Jaringan Usaha & Informasi Kantor Pusat --}}
<div class="ub-card">
  <p class="ub-section-title">10. Jaringan Usaha</p>
  <div class="mb-4">
    <label class="ub-label">a. Apa jaringan usaha dari perusahaan ini? <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      @foreach([1=>'Tunggal',2=>'Kantor pusat',3=>'Cabang',4=>'Perwakilan',5=>'Pabrik',6=>'Unit pembantu/penunjang'] as $val=>$lbl)
      <label class="ub-radio-label"><input type="radio" name="jaringan_usaha" value="{{ $val }}" class="jaringan-radio" {{ old('jaringan_usaha',$response->jaringan_usaha)==$val?'checked':'' }}> {{ $val }}. {{ $lbl }}</label>
      @endforeach
    </div>
    <div class="ub-err-msg" data-field="jaringan_usaha"></div>
  </div>

  {{-- 10b: jumlah cabang (only if Kantor pusat = 2) --}}
  <div id="sec_kantor_pusat_count" class="conditional-section mb-4">
    <label class="ub-label">b. Berapa jumlah seluruh kantor cabang dan unit usaha di bawah kantor pusat? <span class="ub-required">*</span></label>
    <input name="jumlah_cabang" type="number" min="0" class="ub-input" style="max-width:180px;" value="{{ old('jumlah_cabang',$response->jumlah_cabang) }}" placeholder="0">
    <div class="ub-err-msg" data-field="jumlah_cabang"></div>
  </div>

  {{-- Q11: Informasi Kantor Pusat (only if 10a = 3, 4, 5, 6) --}}
  <div id="sec_info_kp" class="conditional-section">
    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-3 mt-2 pt-3 border-t border-gray-100 dark:border-gray-700">11. Informasi Kantor Pusat</p>
    <div class="ub-grid-2">
      <div>
        <label class="ub-label">a. Nama Kantor Pusat <span class="ub-required">*</span></label>
        <input name="kp_nama" class="ub-input" value="{{ old('kp_nama',$response->kp_nama) }}" placeholder="Nama kantor pusat">
        <div class="ub-err-msg" data-field="kp_nama"></div>
      </div>
      <div>
        <label class="ub-label">d. Negara <span class="ub-required">*</span></label>
        <input name="kp_negara" class="ub-input" value="{{ old('kp_negara',$response->kp_negara) }}" placeholder="Negara">
        <div class="ub-err-msg" data-field="kp_negara"></div>
      </div>
      <div>
        <label class="ub-label">b. Alamat Kantor Pusat <span class="ub-required">*</span></label>
        <textarea name="kp_alamat" class="ub-input" rows="2" placeholder="Alamat lengkap">{{ old('kp_alamat',$response->kp_alamat) }}</textarea>
        <div class="ub-err-msg" data-field="kp_alamat"></div>
      </div>
      <div>
        <label class="ub-label">e. Provinsi <span class="ub-required">*</span></label>
        <input name="kp_provinsi" class="ub-input" value="{{ old('kp_provinsi',$response->kp_provinsi) }}" placeholder="Provinsi">
        <div class="ub-err-msg" data-field="kp_provinsi"></div>
      </div>
      <div>
        <label class="ub-label">c. Email <span class="ub-required">*</span></label>
        <input name="kp_email" type="email" class="ub-input" value="{{ old('kp_email',$response->kp_email) }}" placeholder="email@kantorpusat.com">
        <div class="ub-err-msg" data-field="kp_email"></div>
      </div>
      <div>
        <label class="ub-label">f. Kabupaten/Kota <span class="ub-required">*</span></label>
        <input name="kp_kabkota" class="ub-input" value="{{ old('kp_kabkota',$response->kp_kabkota) }}" placeholder="Kabupaten/Kota">
        <div class="ub-err-msg" data-field="kp_kabkota"></div>
      </div>
    </div>
  </div>

  {{-- Unit pembantu notice: shown AFTER Q11 --}}
  <div id="sec_unit_pembantu" class="conditional-section mt-4">
    <div class="px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700 rounded-xl text-sm text-green-800 dark:text-green-300">
      <p class="font-bold text-base mb-1">⚠ PENDATAAN SELESAI</p>
      <p>Untuk unit pembantu/penunjang (kode 6), isi Informasi Kantor Pusat (No. 11) di atas, lalu klik <strong>Simpan &amp; Lanjut ke Blok III</strong>. Pertanyaan 12–25 tidak perlu diisi. Di Blok III, lengkapi data responden lalu klik <strong>Selesaikan Survei</strong>.</p>
    </div>
  </div>
</div>

{{-- Q12: Internet --}}
<div class="ub-card" id="card_q12">
  <p class="ub-section-title">12. Penggunaan Internet dan Teknologi Digital</p>
  <div class="mb-4">
    <label class="ub-label">a. Apakah perusahaan ini menggunakan internet dalam menjalankan usaha? <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="uses_internet" value="1" id="inet_ya" {{ old('uses_internet',$response->uses_internet)==1?'checked':'' }}> 1. Ya</label>
      <label class="ub-radio-label"><input type="radio" name="uses_internet" value="2" id="inet_tidak" {{ old('uses_internet',$response->uses_internet)==2?'checked':'' }}> 2. Tidak</label>
    </div>
    <div class="ub-err-msg" data-field="uses_internet"></div>
  </div>
  <div id="sec_internet_detail" class="conditional-section mb-4">
    <label class="ub-label">b. Tujuan penggunaan internet (pilih semua yang sesuai)</label>
    <div class="ub-checkbox-group mt-1">
      <label class="ub-check-label"><input type="checkbox" name="internet_pesanan" value="1" {{ $response->internet_pesanan?'checked':'' }}> b1. Menerima pesanan barang/jasa</label>
      <label class="ub-check-label"><input type="checkbox" name="internet_produksi" value="1" {{ $response->internet_produksi?'checked':'' }}> b2. Produksi barang/jasa</label>
      <label class="ub-check-label"><input type="checkbox" name="internet_distribusi" value="1" {{ $response->internet_distribusi?'checked':'' }}> b3. Distribusi barang/jasa</label>
      <label class="ub-check-label"><input type="checkbox" name="internet_beli_bahan_baku" value="1" {{ $response->internet_beli_bahan_baku?'checked':'' }}> b4. Membeli bahan baku online</label>
      <label class="ub-check-label"><input type="checkbox" name="internet_promosi" value="1" {{ $response->internet_promosi?'checked':'' }}> b5. Promosi</label>
      <label class="ub-check-label"><input type="checkbox" name="internet_lainnya" value="1" {{ $response->internet_lainnya?'checked':'' }}> b6. Lainnya</label>
    </div>
  </div>
  <div class="mb-2">
    <label class="ub-label">c. Apakah perusahaan ini memanfaatkan teknologi digital (AI, IoT, big data, printer 3D, blockchain, atau cloud computing)? <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="uses_teknologi_digital" value="1" {{ old('uses_teknologi_digital',$response->uses_teknologi_digital)==1?'checked':'' }}> 1. Ya</label>
      <label class="ub-radio-label"><input type="radio" name="uses_teknologi_digital" value="2" {{ old('uses_teknologi_digital',$response->uses_teknologi_digital)==2?'checked':'' }}> 2. Tidak</label>
    </div>
    <div class="ub-err-msg" data-field="uses_teknologi_digital"></div>
  </div>
</div>

{{-- Q13: Lingkungan --}}
<div class="ub-card" id="card_q13">
  <p class="ub-section-title">13. Ramah Lingkungan</p>
  <div class="mb-4">
    <label class="ub-label">a. Apakah perusahaan ini memproduksi barang/jasa yang ramah lingkungan? <span class="ub-required">*</span></label>
    <p class="ub-hint mb-2">Lihat contoh pada petunjuk di bawah — tab <strong>13a</strong>.</p>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="produk_ramah_lingkungan" value="1" {{ old('produk_ramah_lingkungan',$response->produk_ramah_lingkungan)==1?'checked':'' }}> 1. Ya, seluruhnya</label>
      <label class="ub-radio-label"><input type="radio" name="produk_ramah_lingkungan" value="2" {{ old('produk_ramah_lingkungan',$response->produk_ramah_lingkungan)==2?'checked':'' }}> 2. Ya, sebagian</label>
      <label class="ub-radio-label"><input type="radio" name="produk_ramah_lingkungan" value="3" {{ old('produk_ramah_lingkungan',$response->produk_ramah_lingkungan)==3?'checked':'' }}> 3. Tidak sama sekali</label>
    </div>
    <div class="ub-err-msg" data-field="produk_ramah_lingkungan"></div>
  </div>
  <div>
    <label class="ub-label">b. Apakah perusahaan ini menggunakan input untuk tujuan perlindungan lingkungan dan/atau pembelian barang dan jasa yang ramah lingkungan? <span class="ub-required">*</span></label>
    <p class="ub-hint mb-2">Lihat contoh pada petunjuk di bawah — tab <strong>13b</strong>.</p>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="uses_input_lingkungan" value="1" {{ old('uses_input_lingkungan',$response->uses_input_lingkungan)==1?'checked':'' }}> 1. Ya</label>
      <label class="ub-radio-label"><input type="radio" name="uses_input_lingkungan" value="2" {{ old('uses_input_lingkungan',$response->uses_input_lingkungan)==2?'checked':'' }}> 2. Tidak</label>
    </div>
    <div class="ub-err-msg" data-field="uses_input_lingkungan"></div>
  </div>

  {{-- Petunjuk Q13 tabbed panel --}}
  <div class="mt-4" id="petunjuk13Wrap">
    <button type="button" id="petunjuk13Toggle"
      class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
      <svg id="petunjuk13Chevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
      </svg>
      <span id="petunjuk13Label">Lihat petunjuk pengisian ramah lingkungan</span>
    </button>
    <div id="petunjuk13Panel" class="hidden mt-3 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 overflow-hidden">
      <div class="flex overflow-x-auto border-b border-blue-100 dark:border-blue-900/60 bg-white/70 dark:bg-gray-800/50">
        <button type="button" data-tab="l1" class="ptab ptab-active shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">13a. Produk Ramah Lingkungan</button>
        <button type="button" data-tab="l2" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">13b. Input Perlindungan Lingkungan</button>
      </div>
      <div class="p-4 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">

        <div data-panel="l1">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">13a. Contoh produk/jasa yang ramah lingkungan:</p>
          <ul class="list-disc list-inside space-y-1 pl-1">
            <li><strong>Produk efisiensi energi</strong> — lampu atau mesin hemat listrik</li>
            <li><strong>Energi terbarukan</strong> — panel surya, turbin angin, biogas</li>
            <li><strong>Kendaraan ramah lingkungan</strong> — kendaraan listrik/hybrid</li>
            <li><strong>Produk berbahan daur ulang</strong> — kertas/plastik daur ulang, kemasan ramah lingkungan</li>
            <li><strong>Jasa pengelolaan limbah dan sampah</strong> — pengolahan sampah/air limbah, daur ulang</li>
          </ul>
        </div>

        <div data-panel="l2" class="hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">13b. Contoh input/pengeluaran perlindungan lingkungan:</p>
          <ul class="list-disc list-inside space-y-1 pl-1">
            <li>Pengelolaan dan pembersihan limbah dan sampah</li>
            <li>Pengendalian polusi udara</li>
            <li>Perbaikan tanah/air tanah</li>
            <li>Pengurangan kebisingan</li>
            <li>Pelestarian alam/keanekaragaman hayati</li>
            <li>Audit/penilaian lingkungan</li>
          </ul>
        </div>

      </div>
    </div>
  </div>
</div>

{{-- Q14: Karya Seni --}}
<div class="ub-card" id="card_q14">
  <p class="ub-section-title">14. Produk Karya Seni, Sastra, Desain, Teknologi, atau Warisan Budaya</p>
  <label class="ub-label">Apakah perusahaan ini menggunakan produk karya seni, sastra, desain, teknologi atau warisan budaya, baik diproduksi sendiri maupun oleh pihak lain? <span class="ub-required">*</span></label>
  {{-- Petunjuk Q14 tabbed panel --}}
  <div class="mt-3 mb-3" id="petunjuk14Wrap">
    <button type="button" id="petunjuk14Toggle"
      class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
      <svg id="petunjuk14Chevron" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
      </svg>
      <span id="petunjuk14Label">Lihat contoh produk per kategori</span>
    </button>
    <div id="petunjuk14Panel" class="hidden mt-3 rounded-xl border border-blue-100 dark:border-blue-900/60 bg-blue-50/60 dark:bg-blue-950/30 overflow-hidden">
      <div class="flex overflow-x-auto border-b border-blue-100 dark:border-blue-900/60 bg-white/70 dark:bg-gray-800/50">
        <button type="button" data-tab="k1" class="ptab ptab-active shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Karya Seni</button>
        <button type="button" data-tab="k2" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Sastra</button>
        <button type="button" data-tab="k3" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Desain</button>
        <button type="button" data-tab="k4" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Teknologi</button>
        <button type="button" data-tab="k5" class="ptab shrink-0 px-3.5 py-2 text-xs font-semibold border-b-2 whitespace-nowrap transition-colors">Warisan Budaya</button>
      </div>
      <div class="p-4 text-xs text-gray-700 dark:text-gray-300 leading-relaxed">

        <div data-panel="k1">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Produk Karya Seni</p>
          <ul class="list-disc list-inside space-y-0.5 pl-1">
            <li>Lukisan, patung, kerajinan tangan</li>
            <li>Musik, tari, pertunjukan</li>
            <li>Foto, film, videografi</li>
            <li>Ilustrasi, animasi</li>
            <li>Board game, permainan kreatif</li>
            <li>Dan produk karya seni lainnya</li>
          </ul>
        </div>

        <div data-panel="k2" class="hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Produk Sastra</p>
          <ul class="list-disc list-inside space-y-0.5 pl-1">
            <li>Puisi</li>
            <li>Cerpen (cerita pendek)</li>
            <li>Novel</li>
            <li>Naskah drama</li>
            <li>Dan karya tulis sastra lainnya</li>
          </ul>
        </div>

        <div data-panel="k3" class="hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Produk Desain</p>
          <ul class="list-disc list-inside space-y-0.5 pl-1">
            <li>Desain arsitektur</li>
            <li>Desain produk</li>
            <li>Desain interior</li>
            <li>Desain komunikasi visual</li>
            <li>Desain fesyen</li>
            <li>Dan karya desain lainnya</li>
          </ul>
        </div>

        <div data-panel="k4" class="hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Produk Teknologi</p>
          <ul class="list-disc list-inside space-y-0.5 pl-1">
            <li>Perangkat lunak (software)</li>
            <li>Aplikasi digital</li>
            <li>Aplikasi gim</li>
            <li>Perangkat elektronik</li>
            <li>Inovasi berbasis kecerdasan buatan (AI)</li>
            <li>Dan produk teknologi lainnya</li>
          </ul>
        </div>

        <div data-panel="k5" class="hidden">
          <p class="font-bold text-blue-700 dark:text-blue-300 mb-2">Produk Warisan Budaya</p>
          <ul class="list-disc list-inside space-y-0.5 pl-1">
            <li>Makanan tradisional (rendang, gudeg, dll.)</li>
            <li>Peralatan tradisional (gamelan, angklung, dll.)</li>
            <li>Obat tradisional/jamu</li>
            <li>Pakaian/kain tradisional (batik, tenun, songket, dll.)</li>
            <li>Dan produk warisan budaya lainnya</li>
          </ul>
        </div>

      </div>
    </div>
  </div>
  <div class="ub-radio-group">
    <label class="ub-radio-label"><input type="radio" name="uses_karya_seni" value="1" {{ old('uses_karya_seni',$response->uses_karya_seni)==1?'checked':'' }}> 1. Ya</label>
    <label class="ub-radio-label"><input type="radio" name="uses_karya_seni" value="2" {{ old('uses_karya_seni',$response->uses_karya_seni)==2?'checked':'' }}> 2. Tidak</label>
  </div>
  <div class="ub-err-msg" data-field="uses_karya_seni"></div>
</div>

<div class="flex flex-wrap items-center justify-between gap-4 mt-6 mb-8">
  <a href="{{ route('survey.ub.blok1a') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali ke Blok I-A
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
    autoSave: '{{ route("survey.ub.blok1b.autosave") }}',
    saveAll:  '{{ ($editMode ?? false) ? route("survey.ub.edit.blok1b.save") : route("survey.ub.blok1b.save") }}',
    status:   '{{ route("survey.ub.blok1b.status") }}',
    nextBlok: '{{ route("survey.ub.blok1c") }}',
    blok3:    '{{ route("survey.ub.blok3") }}',
    showGuidanceNearSubmit: false
};
(function(){
  // ── Q9b toggles ───────────────────────────────────────────────────────────
  function toggle9b(){
    const b1 = document.querySelector('input[name="produksi_di_lokasi"]:checked')?.value;
    const b2 = document.querySelector('input[name="layanan_makan_minum"]:checked')?.value;
    const b3 = document.querySelector('input[name="penjualan_barang"]:checked')?.value;
    // b3: show if b1=Tidak AND b2=Tidak
    const showB3 = b1==='2' && b2==='2';
    document.getElementById('sec_9b3').style.display = showB3 ? 'block' : 'none';
    // b4: show if b1=Tidak AND b2=Tidak AND b3=Tidak
    document.getElementById('sec_9b4').style.display = (showB3 && b3==='2') ? 'block' : 'none';
    // c: show if b2=Ya, OR b3 section is visible AND b3=Ya (guard stale b3 value)
    document.getElementById('sec_9c').style.display = (b2==='1' || (showB3 && b3==='1')) ? 'block' : 'none';
    // d & e: show if b1=Ya AND b2=Tidak
    const showDe = b1==='1' && b2==='2';
    document.getElementById('sec_9d').style.display = showDe ? 'block' : 'none';
    document.getElementById('sec_9e').style.display = showDe ? 'block' : 'none';
  }
  document.querySelectorAll('input[name="produksi_di_lokasi"],input[name="layanan_makan_minum"],input[name="penjualan_barang"]')
    .forEach(el => el.addEventListener('change', toggle9b));
  toggle9b();

  // ── Q10/Q11 jaringan usaha ─────────────────────────────────────────────────
  // Spec routing: 2→10b→Q12; 3,4,5→Q11→Q12; 6→Q11→PENDATAAN SELESAI (skip Q12-Q14)
  function toggleJaringan(){
    const val = document.querySelector('input[name="jaringan_usaha"]:checked')?.value;
    const isUnitPembantu = val === '6';

    document.getElementById('sec_kantor_pusat_count').style.display = val==='2' ? 'block' : 'none';
    document.getElementById('sec_info_kp').style.display = (val==='3'||val==='4'||val==='5'||val==='6') ? 'block' : 'none';
    document.getElementById('sec_unit_pembantu').style.display = isUnitPembantu ? 'block' : 'none';

    // Hide Q12-Q14 entirely for unit pembantu/penunjang
    const q12card = document.getElementById('card_q12');
    const q13card = document.getElementById('card_q13');
    const q14card = document.getElementById('card_q14');
    if (q12card) q12card.style.display = isUnitPembantu ? 'none' : '';
    if (q13card) q13card.style.display = isUnitPembantu ? 'none' : '';
    if (q14card) q14card.style.display = isUnitPembantu ? 'none' : '';

    // Change submit button label
    const btn = document.getElementById('submitBtn');
    if (btn) {
      btn.innerHTML = isUnitPembantu
        ? 'Simpan &amp; Lanjut ke Blok III <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>'
        : 'Simpan &amp; Lanjut <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>';
    }
  }
  document.querySelectorAll('.jaringan-radio').forEach(el => el.addEventListener('change', toggleJaringan));
  toggleJaringan();

  // ── Tabbed petunjuk panels ─────────────────────────────────────────────────
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
  initPetunjukPanel('petunjuk9aToggle',  'petunjuk9aPanel',  'petunjuk9aChevron',  'petunjuk9aLabel',  'Lihat contoh kegiatan utama',                'Sembunyikan contoh');
  initPetunjukPanel('petunjuk9dToggle',  'petunjuk9dPanel',  'petunjuk9dChevron',  'petunjuk9dLabel',  'Lihat contoh input',                         'Sembunyikan contoh');
  initPetunjukPanel('petunjuk9eToggle',  'petunjuk9ePanel',  'petunjuk9eChevron',  'petunjuk9eLabel',  'Lihat contoh proses produksi',               'Sembunyikan contoh');
  initPetunjukPanel('petunjuk9fToggle',  'petunjuk9fPanel',  'petunjuk9fChevron',  'petunjuk9fLabel',  'Lihat contoh produk utama',                  'Sembunyikan contoh');
  initPetunjukPanel('petunjuk13Toggle',  'petunjuk13Panel',  'petunjuk13Chevron',  'petunjuk13Label',  'Lihat petunjuk pengisian ramah lingkungan',   'Sembunyikan petunjuk');
  initPetunjukPanel('petunjuk14Toggle',  'petunjuk14Panel',  'petunjuk14Chevron',  'petunjuk14Label',  'Lihat contoh produk per kategori',            'Sembunyikan contoh');

  // ── Q12 internet ───────────────────────────────────────────────────────────
  const inetYa = document.getElementById('inet_ya');
  const inetTidak = document.getElementById('inet_tidak');
  function toggleInet(){
    document.getElementById('sec_internet_detail').style.display = inetYa?.checked ? 'block' : 'none';
  }
  inetYa && inetYa.addEventListener('change', toggleInet);
  inetTidak && inetTidak.addEventListener('change', toggleInet);
  toggleInet();

  // ── Custom KLU dropdown ─────────────────────────────────────────────────────
  (function() {
    var btn  = document.getElementById('klu-btn');
    var list = document.getElementById('klu-list');
    var sel  = document.getElementById('klu-select');
    var disp = document.getElementById('klu-display');
    if (!btn) return;
    function openList()  { list.style.display='block'; btn.classList.add('open'); btn.setAttribute('aria-expanded','true'); }
    function closeList() { list.style.display='none';  btn.classList.remove('open'); btn.setAttribute('aria-expanded','false'); }
    btn.addEventListener('click', function(e) { e.stopPropagation(); list.style.display==='none' ? openList() : closeList(); });
    list.querySelectorAll('.klu-item').forEach(function(item) {
      item.addEventListener('click', function() {
        var val  = this.dataset.val  || '';
        var code = this.dataset.code || '';
        var name = this.dataset.name || '';
        sel.value = val;
        disp.innerHTML = val
          ? '<span class="klu-code">'+code+'</span>&nbsp;<span>'+name+'</span>'
          : '<span class="klu-empty">— Pilih Kategori —</span>';
        list.querySelectorAll('.klu-item').forEach(function(i) { i.classList.remove('klu-active'); i.setAttribute('aria-selected','false'); });
        this.classList.add('klu-active');
        this.setAttribute('aria-selected','true');
        btn.classList.remove('error');
        var errEl = document.querySelector('.ub-err-msg[data-field="kategori_lapangan_usaha"]');
        if (errEl) errEl.textContent = '';
        closeList();
      });
    });
    document.addEventListener('click', function(e) { if (!document.getElementById('klu-wrap').contains(e.target)) closeList(); });
    document.addEventListener('keydown', function(e) { if (e.key==='Escape') closeList(); });
  })();
})();
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-ub-blok1b.js') }}"></script>
@endpush
@endsection
