@extends('layouts.user-dashboard')

@section('title', 'Survei Listrik — Blok I: Identitas & Lokasi')

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
.ub-hint{font-size:.71rem;color:#9ca3af;margin-top:.3rem;}
.ub-input{width:100%;border:1px solid #d1d5db;border-radius:.625rem;padding:.55rem .85rem;font-size:.875rem;color:#111827;background:#fff;transition:border-color .15s;}
.ub-input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15);}
.dark .ub-input{background:#111827;border-color:#4b5563;color:#f9fafb;}
.ub-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.ub-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;}
@media(max-width:640px){.ub-grid-2,.ub-grid-3{grid-template-columns:1fr;}}
.ub-required{color:#ef4444;margin-left:.2rem;}
.ub-err-msg{font-size:.73rem;color:#ef4444;margin-top:.3rem;}
.ub-input.input-error{border-color:#ef4444 !important;}
.ub-radio-row{display:flex;gap:1.25rem;flex-wrap:wrap;padding:.35rem 0;}
.ub-radio-row label{display:inline-flex;align-items:center;gap:.45rem;font-size:.85rem;color:#374151;cursor:pointer;}
.dark .ub-radio-row label{color:#d1d5db;}
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
    <h1 class="ud-page-title">Blok I — Identitas &amp; Lokasi</h1>
    <p class="ud-page-description">Survei Listrik · Keterangan perusahaan/pembangkit dan penanggung jawab</p>
  </div>
  <a href="{{ route('survey.listrik.entry') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>Kembali
  </a>
</div>

@if(session('error'))
<div class="mt-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-300 text-sm flex items-start gap-2">
  <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
  {{ session('error') }}
</div>
@endif
@if(session('info'))
<div class="mt-4 px-4 py-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 dark:bg-blue-900/30 dark:border-blue-700 dark:text-blue-300 text-sm">{{ session('info') }}</div>
@endif

<div class="flex gap-5 items-start mt-4">
@include('survey.listrik.partials.sidebar')
<div class="flex-1 min-w-0">

<div id="autosave-status" class="autosave-status hidden" style="margin-bottom:.75rem;"><span id="autosave-text"></span></div>

@if(isset($errors) && $errors->any())
<div class="mb-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm dark:bg-red-900/30 dark:border-red-700 dark:text-red-300">
  {{ $errors->first() }}
</div>
@endif

<form id="survey-form" method="POST" action="{{ route('survey.listrik.blok1.save') }}" novalidate>
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
      <label class="ub-label">4. Kelurahan/Desa <span class="ub-required">*</span></label>
      <input name="kelurahan_desa" class="ub-input" value="{{ old('kelurahan_desa', $response->kelurahan_desa) }}" placeholder="Kelurahan atau desa">
      <div class="ub-err-msg" data-field="kelurahan_desa"></div>
    </div>
  </div>
</div>

{{-- NAMA & ALAMAT --}}
<div class="ub-card">
  <p class="ub-section-title">5. Nama dan Alamat Perusahaan</p>
  <div class="mb-4">
    <label class="ub-label">a. Nama Perusahaan <span class="ub-required">*</span></label>
    <input name="nama_perusahaan" class="ub-input" value="{{ old('nama_perusahaan', $response->nama_perusahaan) }}" placeholder="Nama lengkap beserta status badan usaha. Contoh: PLN BATAM, PT">
    <p class="ub-hint">Tuliskan nama perusahaan/unit pembangkit dengan lengkap, beserta status badan usaha.</p>
    <div class="ub-err-msg" data-field="nama_perusahaan"></div>
  </div>
  <div class="mb-4">
    <label class="ub-label">b. Nama Komersial Perusahaan</label>
    <input name="nama_komersial" class="ub-input" value="{{ old('nama_komersial', $response->nama_komersial) }}" placeholder="Jika tidak ada, kosongkan atau tuliskan nama perusahaan">
    <div class="ub-err-msg" data-field="nama_komersial"></div>
  </div>
  <div class="mb-1">
    <label class="ub-label">c. Alamat Perusahaan <span class="ub-required">*</span></label>
    <textarea name="alamat_perusahaan" class="ub-input" rows="2" placeholder="Alamat lengkap">{{ old('alamat_perusahaan', $response->alamat_perusahaan) }}</textarea>
    <div class="ub-err-msg" data-field="alamat_perusahaan"></div>
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
    <div class="ub-grid-3 mt-2">
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
        <div class="ub-err-msg" data-field="email_perusahaan"></div>
      </div>
    </div>
  </div>
</div>

{{-- PEMBANGKIT --}}
<div class="ub-card">
  <p class="ub-section-title">6. Keterangan Pembangkit</p>
  <div class="ub-grid-2">
    <div>
      <label class="ub-label">a. Jenis Pembangkit <span class="ub-required">*</span></label>
      @php $jp = old('jenis_pembangkit', $response->jenis_pembangkit); @endphp
      <select name="jenis_pembangkit" class="ub-input">
        <option value="">— Pilih jenis pembangkit —</option>
        @foreach(['PLTD','PLTU','PLTG','PLTGU','PLTMG','PLTS','PLTA','PLTB','Lainnya'] as $opt)
          <option value="{{ $opt }}" {{ $jp === $opt ? 'selected' : '' }}>{{ $opt }}</option>
        @endforeach
      </select>
      <div class="ub-err-msg" data-field="jenis_pembangkit"></div>
    </div>
    <div>
      <label class="ub-label">b. Daya Terpasang (KW)</label>
      <input name="daya_terpasang_kw" type="number" min="0" step="0.01" class="ub-input" value="{{ old('daya_terpasang_kw', $response->daya_terpasang_kw) }}" placeholder="0">
      <p class="ub-hint">Total kapasitas daya terpasang seluruh unit, dalam kilowatt.</p>
    </div>
  </div>
</div>

{{-- PENANGGUNG JAWAB --}}
<div class="ub-card">
  <p class="ub-section-title">7. Penanggung Jawab / Pengusaha</p>
  <div class="ub-grid-2">
    <div>
      <label class="ub-label">a. Nama Penanggung Jawab <span class="ub-required">*</span></label>
      <input name="nama_pengusaha" class="ub-input" value="{{ old('nama_pengusaha', $response->nama_pengusaha) }}" placeholder="Nama lengkap">
      <div class="ub-err-msg" data-field="nama_pengusaha"></div>
    </div>
    <div>
      <label class="ub-label">b. Jenis Kelamin <span class="ub-required">*</span></label>
      @php $jk = (int) old('jenis_kelamin', $response->jenis_kelamin); @endphp
      <div class="ub-radio-row">
        <label><input type="radio" name="jenis_kelamin" value="1" {{ $jk === 1 ? 'checked' : '' }}> Laki-laki</label>
        <label><input type="radio" name="jenis_kelamin" value="2" {{ $jk === 2 ? 'checked' : '' }}> Perempuan</label>
      </div>
      <div class="ub-err-msg" data-field="jenis_kelamin"></div>
    </div>
    <div>
      <label class="ub-label">c. Umur <span class="ub-required">*</span></label>
      <input name="umur" type="number" min="1" max="120" class="ub-input" value="{{ old('umur', $response->umur) }}" placeholder="Tahun">
      <div class="ub-err-msg" data-field="umur"></div>
    </div>
    <div>
      <label class="ub-label">d. NIK</label>
      <input name="nik" class="ub-input" value="{{ old('nik', $response->nik) }}" placeholder="16 digit NIK" maxlength="20">
      <div class="ub-err-msg" data-field="nik"></div>
    </div>
  </div>
</div>

<div class="flex flex-wrap items-center justify-between gap-4 mt-6 mb-8">
  <a href="{{ route('survey.listrik.entry') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali
  </a>
  <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold shadow transition">
    Simpan &amp; Lanjut ke Blok II
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>
</div>
</form>
</div>
</div>

@push('scripts')
<script>
window.surveyRoutes = {
    autoSave: '{{ route("survey.listrik.blok1.autosave") }}',
    saveAll:  '{{ route("survey.listrik.blok1.save") }}',
    status:   '{{ route("survey.listrik.blok1.status") }}',
    nextBlok: '{{ route("survey.listrik.blok2") }}',
    showGuidanceNearSubmit: false
};
</script>
<script src="{{ asset('js/survey.js') }}"></script>
<script>
// Client-side required check with inline messages before normal POST
(function () {
    var REQUIRED = {
        provinsi: 'Provinsi wajib diisi.',
        kabupaten_kota: 'Kabupaten/Kota wajib diisi.',
        kecamatan: 'Kecamatan wajib diisi.',
        kelurahan_desa: 'Kelurahan/Desa wajib diisi.',
        nama_perusahaan: 'Nama perusahaan wajib diisi.',
        alamat_perusahaan: 'Alamat perusahaan wajib diisi.',
        nomor_hp: 'Nomor HP wajib diisi.',
        jenis_pembangkit: 'Jenis pembangkit wajib dipilih.',
        nama_pengusaha: 'Nama penanggung jawab wajib diisi.',
        jenis_kelamin: 'Jenis kelamin wajib dipilih.',
        umur: 'Umur wajib diisi.'
    };
    var form = document.getElementById('survey-form');
    form.addEventListener('submit', function (e) {
        var firstBad = null;
        Object.keys(REQUIRED).forEach(function (name) {
            var els = form.querySelectorAll('[name="' + name + '"]');
            if (!els.length) return;
            var filled;
            if (els[0].type === 'radio') {
                filled = Array.prototype.some.call(els, function (r) { return r.checked; });
            } else {
                filled = els[0].value.trim() !== '';
            }
            var err = form.querySelector('[data-field="' + name + '"]');
            if (!filled) {
                if (err) err.textContent = REQUIRED[name];
                els[0].classList.add('input-error');
                if (!firstBad) firstBad = els[0];
            } else {
                if (err) err.textContent = '';
                els[0].classList.remove('input-error');
            }
        });
        if (firstBad) {
            e.preventDefault();
            firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstBad.focus({ preventScroll: true });
        }
    });
    form.addEventListener('input', function (e) {
        var name = e.target.name;
        if (!name) return;
        var err = form.querySelector('[data-field="' + name + '"]');
        if (err) err.textContent = '';
        e.target.classList.remove('input-error');
    });
})();
</script>
@endpush
@endsection
