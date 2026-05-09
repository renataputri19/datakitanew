@extends('layouts.user-dashboard')

@section('title', 'Survei UB — Blok I-A: Identitas & Lokasi')

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
.dark .ub-input:focus{border-color:#3b82f6;}
.ub-input.error{border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.15);}
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
.ub-step{padding:.3rem .85rem;border-radius:999px;font-size:.75rem;font-weight:600;border:1.5px solid transparent;}
.ub-step.done{background:#dcfce7;color:#15803d;border-color:#bbf7d0;}
.ub-step.active{background:#dbeafe;color:#1d4ed8;border-color:#bfdbfe;}
.ub-step.pending{background:#f3f4f6;color:#9ca3af;border-color:#e5e7eb;}
.dark .ub-step.done{background:#14532d;color:#86efac;border-color:#166534;}
.dark .ub-step.active{background:#1e3a5f;color:#93c5fd;border-color:#1d4ed8;}
.dark .ub-step.pending{background:#374151;color:#6b7280;border-color:#4b5563;}
.ub-err-msg{font-size:.73rem;color:#ef4444;margin-top:.3rem;}
.conditional-section{display:none;}
</style>
@endpush

@section('dashboard-content')
<div class="lg:hidden mb-4">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400" type="button" data-open-sidebar>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>Menu
  </button>
</div>

{{-- Header --}}
<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">Blok I-A — Identitas &amp; Lokasi</h1>
    <p class="ud-page-description">SE2026-L.UB · Pertanyaan 1–8: Lokasi, nama perusahaan, NIB, badan usaha, penanggung jawab</p>
  </div>
  <a href="{{ route('survey.ub.entry') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali
  </a>
</div>

<div class="flex gap-5 items-start mt-4">
@include('survey.ub.partials.sidebar')
<div class="flex-1 min-w-0">

<div id="globalErr" class="hidden mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm dark:bg-red-900/30 dark:border-red-700 dark:text-red-300"></div>

<div id="autosave-status" class="autosave-status hidden" style="margin-bottom:.75rem;"><span id="autosave-text"></span></div>

<form id="survey-form" method="POST" action="{{ ($editMode ?? false) ? route('survey.ub.edit.blok1a.save') : route('survey.ub.blok1a.save') }}" novalidate>
@csrf

{{-- LOKASI --}}
<div class="ub-card">
  <p class="ub-section-title">BLOK I : KETERANGAN PERUSAHAAN — Lokasi</p>
  <div class="ub-grid-2">
    <div>
      <label class="ub-label">1. Provinsi <span class="ub-required">*</span></label>
      <input name="provinsi" class="ub-input" value="{{ old('provinsi', $response->provinsi) }}" placeholder="Nama provinsi">
      <div class="ub-err-msg" data-field="provinsi"></div>
    </div>
    <div>
      <label class="ub-label">2. Kabupaten/Kota <span class="ub-required">*</span></label>
      <input name="kabupaten_kota" class="ub-input" value="{{ old('kabupaten_kota', $response->kabupaten_kota) }}" placeholder="Kabupaten atau kota">
      <div class="ub-err-msg" data-field="kabupaten_kota"></div>
    </div>
    <div>
      <label class="ub-label">3. Kecamatan <span class="ub-required">*</span></label>
      <input name="kecamatan" class="ub-input" value="{{ old('kecamatan', $response->kecamatan) }}" placeholder="Kecamatan">
      <div class="ub-err-msg" data-field="kecamatan"></div>
    </div>
    <div>
      <label class="ub-label">4. Kelurahan/Desa/Nagari <span class="ub-required">*</span></label>
      <input name="kelurahan_desa" class="ub-input" value="{{ old('kelurahan_desa', $response->kelurahan_desa) }}" placeholder="Kelurahan, desa, atau nagari">
      <div class="ub-err-msg" data-field="kelurahan_desa"></div>
    </div>
  </div>
</div>

{{-- NAMA & ALAMAT --}}
<div class="ub-card">
  <p class="ub-section-title">5. Nama dan Alamat Perusahaan</p>
  <div class="mb-4">
    <label class="ub-label">a. Nama Perusahaan <span class="ub-required">*</span></label>
    <input name="nama_perusahaan" class="ub-input" value="{{ old('nama_perusahaan', $response->nama_perusahaan) }}" placeholder="Nama lengkap beserta status badan usaha. Contoh: KAWAN BARU, PT TBK">
    <p class="ub-hint">Tuliskan nama perusahaan dengan lengkap, beserta status badan usaha. Contoh: KAWAN BARU, PT TBK</p>
    <div class="ub-err-msg" data-field="nama_perusahaan"></div>
  </div>
  <div class="mb-4">
    <label class="ub-label">b. Nama Komersial Perusahaan <span class="ub-required">*</span></label>
    <input name="nama_komersial" class="ub-input" value="{{ old('nama_komersial', $response->nama_komersial) }}" placeholder="Jika tidak ada, tuliskan nama perusahaan">
    <p class="ub-hint">Jika tidak memiliki nama komersial, maka tuliskan nama perusahaan.</p>
    <div class="ub-err-msg" data-field="nama_komersial"></div>
  </div>
  <div class="mb-4">
    <label class="ub-label">c. Alamat Perusahaan <span class="ub-required">*</span></label>
    <textarea name="alamat_perusahaan" class="ub-input" rows="2" placeholder="Alamat lengkap">{{ old('alamat_perusahaan', $response->alamat_perusahaan) }}</textarea>
    <div class="ub-grid-3 mt-2">
      <div>
        <label class="ub-label text-xs">RT</label>
        <input name="rt" class="ub-input" value="{{ old('rt', $response->rt) }}" placeholder="RT">
      </div>
      <div>
        <label class="ub-label text-xs">RW</label>
        <input name="rw" class="ub-input" value="{{ old('rw', $response->rw) }}" placeholder="RW">
      </div>
      <div>
        <label class="ub-label text-xs">Kode Pos</label>
        <input name="kode_pos" class="ub-input" value="{{ old('kode_pos', $response->kode_pos) }}" placeholder="Kode pos">
      </div>
    </div>
    <div class="ub-grid-2 mt-2">
      <div>
        <label class="ub-label text-xs">Nomor Telepon</label>
        <input name="nomor_telepon" class="ub-input" value="{{ old('nomor_telepon', $response->nomor_telepon) }}" placeholder="Telepon">
      </div>
      <div>
        <label class="ub-label text-xs">Nomor HP/WhatsApp <span class="ub-required">*</span></label>
        <input name="nomor_hp" class="ub-input" value="{{ old('nomor_hp', $response->nomor_hp) }}" placeholder="08xx-xxxx-xxxx">
        <div class="ub-err-msg" data-field="nomor_hp"></div>
      </div>
      <div>
        <label class="ub-label text-xs">Email</label>
        <input name="email_perusahaan" type="email" class="ub-input" value="{{ old('email_perusahaan', $response->email_perusahaan) }}" placeholder="email@perusahaan.com">
      </div>
      <div>
        <label class="ub-label text-xs">Homepage/Website</label>
        <input name="homepage" class="ub-input" value="{{ old('homepage', $response->homepage) }}" placeholder="www.contoh.com">
        <p class="ub-hint">Alamat website diawali dengan www. Contoh: www.bps.go.id</p>
      </div>
    </div>
  </div>
  <div class="mb-4">
    <label class="ub-label">d. Jenis Kawasan Beroperasi <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      @foreach([1=>'Kawasan Ekonomi Khusus (KEK)',2=>'Kawasan Industri (KI)',3=>'Stasiun',4=>'Bandara',5=>'Pelabuhan',6=>'Terminal',7=>'Rest area jalan tol',8=>'Kawasan sentra ekonomi perdesaan/kelurahan',9=>'Kawasan usaha lainnya',10=>'Di luar kawasan'] as $val => $lbl)
      <label class="ub-radio-label">
        <input type="radio" name="jenis_kawasan" value="{{ $val }}" {{ old('jenis_kawasan', $response->jenis_kawasan) == $val ? 'checked' : '' }}>
        {{ $val }}. {{ $lbl }}
      </label>
      @endforeach
    </div>
    <div class="ub-err-msg" data-field="jenis_kawasan"></div>
  </div>
  <div>
    <label class="ub-label">e. Nama Kawasan</label>
    <input name="nama_kawasan" class="ub-input" value="{{ old('nama_kawasan', $response->nama_kawasan) }}" placeholder="Nama kawasan (jika ada)">
  </div>
</div>

{{-- NIB --}}
<div class="ub-card">
  <p class="ub-section-title">6. Nomor Induk Berusaha (NIB)</p>
  <div class="mb-4">
    <label class="ub-label">a. Apakah memiliki NIB? <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="has_nib" value="1" id="nib_ya" {{ old('has_nib',$response->has_nib)==1?'checked':'' }}> 1. Ya</label>
      <label class="ub-radio-label"><input type="radio" name="has_nib" value="2" id="nib_tidak" {{ old('has_nib',$response->has_nib)==2?'checked':'' }}> 2. Tidak</label>
    </div>
    <div class="ub-err-msg" data-field="has_nib"></div>
  </div>
  <div id="sec_nib_ya" class="conditional-section mb-4">
    <label class="ub-label">b. Tuliskan NIB <span class="ub-required">*</span></label>
    <input name="nib" class="ub-input" value="{{ old('nib',$response->nib) }}" placeholder="Nomor Induk Berusaha" style="max-width:240px;">
  </div>
  <div id="sec_nib_tidak" class="conditional-section">
    <label class="ub-label">c. Alasan utama tidak memiliki NIB</label>
    <div class="ub-radio-group">
      @foreach([1=>'Dalam proses pembuatan NIB',2=>'Pengurusan NIB rumit',3=>'Tidak memerlukan NIB',4=>'Tidak tahu tentang NIB',5=>'Lainnya'] as $val=>$lbl)
      <label class="ub-radio-label"><input type="radio" name="alasan_tidak_nib" value="{{ $val }}" {{ old('alasan_tidak_nib',$response->alasan_tidak_nib)==$val?'checked':'' }}> {{ $val }}. {{ $lbl }}</label>
      @endforeach
    </div>
  </div>
</div>

{{-- STATUS BADAN USAHA --}}
<div class="ub-card">
  <p class="ub-section-title">7. Status Badan Usaha</p>
  <div class="mb-4">
    <label class="ub-label">a. Status badan usaha <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      @foreach([1=>'Perseroan (PT/NV/PT Persero/PT Tbk/Perseroan Daerah/Perseroan Perorangan)',2=>'Yayasan',3=>'Koperasi',4=>'Dana Pensiun',5=>'Perum/Perumda',6=>'BUM Desa',7=>'Persekutuan Komanditer (CV)',8=>'Persekutuan Firma (Fa)',9=>'Persekutuan Perdata (Maatschap)',10=>'Kantor Perwakilan Luar Negeri',11=>'Badan Usaha Luar Negeri',12=>'Badan Usaha Lainnya (BLU, PTN-BH dll)',13=>'Bukan Badan Usaha'] as $val=>$lbl)
      <label class="ub-radio-label"><input type="radio" name="status_badan_usaha" value="{{ $val }}" {{ old('status_badan_usaha',$response->status_badan_usaha)==$val?'checked':'' }} class="sbu-radio"> {{ $val }}. {{ $lbl }}</label>
      @endforeach
    </div>
    <div class="ub-err-msg" data-field="status_badan_usaha"></div>
  </div>
  <div id="sec_koperasi" class="conditional-section">
    <div class="bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-800 rounded-xl p-4 space-y-4">
      <div>
        <label class="ub-label">b. Apakah koperasi ini merupakan Koperasi Desa/Kelurahan Merah Putih (KDKMP)? <span class="ub-required">*</span></label>
        <div class="ub-radio-group">
          <label class="ub-radio-label"><input type="radio" name="is_koperasi_kdkmp" value="1" {{ old('is_koperasi_kdkmp',$response->is_koperasi_kdkmp)==1?'checked':'' }}> 1. Ya</label>
          <label class="ub-radio-label"><input type="radio" name="is_koperasi_kdkmp" value="2" {{ old('is_koperasi_kdkmp',$response->is_koperasi_kdkmp)==2?'checked':'' }}> 2. Tidak</label>
        </div>
        <div class="ub-err-msg" data-field="is_koperasi_kdkmp"></div>
      </div>
      <div>
        <label class="ub-label">c. Jenis koperasi berdasarkan layanannya <span class="ub-required">*</span></label>
        <div class="ub-radio-group">
          <label class="ub-radio-label"><input type="radio" name="jenis_koperasi" value="1" {{ old('jenis_koperasi',$response->jenis_koperasi)==1?'checked':'' }}> 1. Open Loop (dapat melayani nonanggota)</label>
          <label class="ub-radio-label"><input type="radio" name="jenis_koperasi" value="2" {{ old('jenis_koperasi',$response->jenis_koperasi)==2?'checked':'' }}> 2. Close Loop (hanya melayani anggota)</label>
        </div>
        <div class="ub-err-msg" data-field="jenis_koperasi"></div>
      </div>
    </div>
  </div>
  <div class="mt-4">
    <label class="ub-label">d. Apakah mempunyai laporan/catatan keuangan? <span class="ub-required">*</span></label>
    <div class="ub-radio-group">
      <label class="ub-radio-label"><input type="radio" name="has_laporan_keuangan" value="1" {{ old('has_laporan_keuangan',$response->has_laporan_keuangan)==1?'checked':'' }}> 1. Ya</label>
      <label class="ub-radio-label"><input type="radio" name="has_laporan_keuangan" value="2" {{ old('has_laporan_keuangan',$response->has_laporan_keuangan)==2?'checked':'' }}> 2. Tidak</label>
    </div>
    <div class="ub-err-msg" data-field="has_laporan_keuangan"></div>
  </div>
</div>

{{-- PENGUSAHA --}}
<div class="ub-card">
  <p class="ub-section-title">8. Pengusaha / Penanggung Jawab</p>
  <div class="ub-grid-2">
    <div>
      <label class="ub-label">a. Nama <span class="ub-required">*</span></label>
      <input name="nama_pengusaha" class="ub-input" value="{{ old('nama_pengusaha',$response->nama_pengusaha) }}" placeholder="Nama lengkap">
      <div class="ub-err-msg" data-field="nama_pengusaha"></div>
    </div>
    <div>
      <label class="ub-label">b. Jenis Kelamin <span class="ub-required">*</span></label>
      <div class="ub-radio-group mt-1">
        <label class="ub-radio-label"><input type="radio" name="jenis_kelamin" value="1" {{ old('jenis_kelamin',$response->jenis_kelamin)==1?'checked':'' }}> 1. Laki-laki</label>
        <label class="ub-radio-label"><input type="radio" name="jenis_kelamin" value="2" {{ old('jenis_kelamin',$response->jenis_kelamin)==2?'checked':'' }}> 2. Perempuan</label>
      </div>
      <div class="ub-err-msg" data-field="jenis_kelamin"></div>
    </div>
    <div>
      <label class="ub-label">c. Umur <span class="ub-required">*</span></label>
      <div class="flex items-center gap-2">
        <input name="umur" type="number" min="1" max="120" class="ub-input" style="max-width:100px;" value="{{ old('umur',$response->umur) }}" placeholder="Umur">
        <span class="text-sm text-gray-500">tahun</span>
      </div>
      <div class="ub-err-msg" data-field="umur"></div>
    </div>
    <div>
      <label class="ub-label">d. NIK <span class="ub-required">*</span></label>
      <input name="nik" class="ub-input" value="{{ old('nik',$response->nik) }}" placeholder="16 digit NIK" maxlength="20">
      <div class="ub-err-msg" data-field="nik"></div>
    </div>
  </div>
</div>

{{-- Navigation --}}
<div class="flex items-center justify-between mt-6 mb-8">
  <a href="{{ route('survey.ub.entry') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali ke Dashboard
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
    autoSave: '{{ route("survey.ub.blok1a.autosave") }}',
    saveAll:  '{{ ($editMode ?? false) ? route("survey.ub.edit.blok1a.save") : route("survey.ub.blok1a.save") }}',
    status:   '{{ route("survey.ub.blok1a.status") }}',
    nextBlok: '{{ route("survey.ub.blok1b") }}',
    showGuidanceNearSubmit: false
};
(function(){
  // Conditional: NIB
  const nibYa = document.getElementById('nib_ya');
  const nibTidak = document.getElementById('nib_tidak');
  function toggleNib(){
    document.getElementById('sec_nib_ya').style.display = nibYa.checked ? 'block' : 'none';
    document.getElementById('sec_nib_tidak').style.display = nibTidak.checked ? 'block' : 'none';
  }
  nibYa && nibYa.addEventListener('change', toggleNib);
  nibTidak && nibTidak.addEventListener('change', toggleNib);
  toggleNib();

  // Conditional: Koperasi
  function toggleKoperasi(){
    const sbu = document.querySelector('input[name="status_badan_usaha"]:checked');
    document.getElementById('sec_koperasi').style.display = (sbu && sbu.value == '3') ? 'block' : 'none';
  }
  document.querySelectorAll('.sbu-radio').forEach(el => el.addEventListener('change', toggleKoperasi));
  toggleKoperasi();
})();
</script>
<script src="{{ asset('js/survey.js') }}"></script>
@endpush

@endsection
