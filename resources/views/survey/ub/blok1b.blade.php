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
    <p class="ub-hint">Contoh: membudidayakan tanaman padi; membuat sosis dari ikan; menyewakan kos bulanan; menyewakan kamar hotel; membuat desain interior rumah; pengembangan aplikasi video gim; meracik jamu di kedai; dll.</p>
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
    <p class="ub-hint">Contoh: daging ikan, tepung; bambu; jagung pipil; kaca; kain; kulit sapi; kayu bulat</p>
    <div class="ub-err-msg" data-field="input_produksi"></div>
  </div>

  {{-- 9e: proses produksi — shown when b1=Ya AND b2=Tidak --}}
  <div id="sec_9e" class="conditional-section mb-4">
    <label class="ub-label">e. Bagaimana proses mengubah input tersebut menjadi produk output (beserta alatnya)? <span class="ub-required">*</span></label>
    <textarea name="proses_produksi" class="ub-input" rows="2" placeholder="Tuliskan proses produksi">{{ old('proses_produksi',$response->proses_produksi) }}</textarea>
    <p class="ub-hint">Contoh: menggiling daging ikan menjadi sosis; penggaraman; pengasapan; pemindangan; pembekuan</p>
    <div class="ub-err-msg" data-field="proses_produksi"></div>
  </div>

  {{-- 9f: produk utama — always required --}}
  <div class="mb-4">
    <label class="ub-label">f. Apa produk utama yang dihasilkan? Tuliskan selengkapnya. <span class="ub-required">*</span></label>
    <textarea name="produk_utama" class="ub-input" rows="2" placeholder="Tuliskan produk/jasa utama">{{ old('produk_utama',$response->produk_utama) }}</textarea>
    <p class="ub-hint">Contoh: padi; tembakau; sosis; kebab; jasa renovasi rumah; jasa perdagangan mobil bekas; jasa sewa kos; jasa pengembangan perangkat lunak; rendang, gudeg; desain arsitektur</p>
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
      <input name="kategori_lapangan_usaha" class="ub-input" maxlength="3" value="{{ old('kategori_lapangan_usaha',$response->kategori_lapangan_usaha) }}" placeholder="Contoh: A, B, C …">
      <p class="ub-hint">Kode huruf kategori KBLI, maks. 3 karakter. Contoh: A (Pertanian), C (Industri), G (Perdagangan).</p>
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
    <p class="ub-hint mb-2">Contoh: Produk efisiensi energi (lampu atau mesin hemat listrik); Energi terbarukan (panel surya, turbin angin, biogas); Kendaraan ramah lingkungan; Produk berbahan daur ulang; Jasa pengelolaan dan pembersihan limbah dan sampah.</p>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="produk_ramah_lingkungan" value="1" {{ old('produk_ramah_lingkungan',$response->produk_ramah_lingkungan)==1?'checked':'' }}> 1. Ya, seluruhnya</label>
      <label class="ub-radio-label"><input type="radio" name="produk_ramah_lingkungan" value="2" {{ old('produk_ramah_lingkungan',$response->produk_ramah_lingkungan)==2?'checked':'' }}> 2. Ya, sebagian</label>
      <label class="ub-radio-label"><input type="radio" name="produk_ramah_lingkungan" value="3" {{ old('produk_ramah_lingkungan',$response->produk_ramah_lingkungan)==3?'checked':'' }}> 3. Tidak sama sekali</label>
    </div>
    <div class="ub-err-msg" data-field="produk_ramah_lingkungan"></div>
  </div>
  <div>
    <label class="ub-label">b. Apakah perusahaan ini menggunakan input untuk tujuan perlindungan lingkungan dan/atau pembelian barang dan jasa yang ramah lingkungan? <span class="ub-required">*</span></label>
    <p class="ub-hint mb-2">Contoh: Pengeluaran untuk pengelolaan dan pembersihan limbah, pengendalian polusi udara, perbaikan tanah/air tanah, pengurangan kebisingan, pelestarian alam, audit/penilaian lingkungan.</p>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="uses_input_lingkungan" value="1" {{ old('uses_input_lingkungan',$response->uses_input_lingkungan)==1?'checked':'' }}> 1. Ya</label>
      <label class="ub-radio-label"><input type="radio" name="uses_input_lingkungan" value="2" {{ old('uses_input_lingkungan',$response->uses_input_lingkungan)==2?'checked':'' }}> 2. Tidak</label>
    </div>
    <div class="ub-err-msg" data-field="uses_input_lingkungan"></div>
  </div>
</div>

{{-- Q14: Karya Seni --}}
<div class="ub-card" id="card_q14">
  <p class="ub-section-title">14. Produk Karya Seni, Sastra, Desain, Teknologi, atau Warisan Budaya</p>
  <label class="ub-label">Apakah perusahaan ini menggunakan produk karya seni, sastra, desain, teknologi atau warisan budaya, baik diproduksi sendiri maupun oleh pihak lain? <span class="ub-required">*</span></label>
  <p class="ub-hint mb-2">Contoh seni: lukisan, patung, kerajinan, musik, tari, foto, film, ilustrasi, animasi, board game, dll.<br>Contoh sastra: puisi, cerpen, novel, naskah drama, dll.<br>Contoh desain: arsitektur, desain produk, desain interior, desain komunikasi visual, desain fesyen, dll.<br>Contoh teknologi: perangkat lunak (software), aplikasi digital, aplikasi gim, perangkat elektronik, dll.<br>Contoh warisan budaya: makanan tradisional, peralatan tradisional, obat tradisional, dll.</p>
  <div class="ub-radio-group">
    <label class="ub-radio-label"><input type="radio" name="uses_karya_seni" value="1" {{ old('uses_karya_seni',$response->uses_karya_seni)==1?'checked':'' }}> 1. Ya</label>
    <label class="ub-radio-label"><input type="radio" name="uses_karya_seni" value="2" {{ old('uses_karya_seni',$response->uses_karya_seni)==2?'checked':'' }}> 2. Tidak</label>
  </div>
  <div class="ub-err-msg" data-field="uses_karya_seni"></div>
</div>

<div class="flex items-center justify-between mt-6 mb-8">
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

  // ── Q12 internet ───────────────────────────────────────────────────────────
  const inetYa = document.getElementById('inet_ya');
  const inetTidak = document.getElementById('inet_tidak');
  function toggleInet(){
    document.getElementById('sec_internet_detail').style.display = inetYa?.checked ? 'block' : 'none';
  }
  inetYa && inetYa.addEventListener('change', toggleInet);
  inetTidak && inetTidak.addEventListener('change', toggleInet);
  toggleInet();
})();
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script src="{{ asset('js/survey-ub-blok1b.js') }}"></script>
@endpush
@endsection
