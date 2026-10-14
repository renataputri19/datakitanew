@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi Triwulanan — Updating Status Perusahaan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Updating Status Perusahaan',
  'subtitle' => 'Kondisi perusahaan pada triwulan berjalan',
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.status.save', $routeParams) }}" novalidate>
  @csrf

  <div class="k-card">
    <p class="k-card-title">1. Status Perusahaan</p>
    <p class="k-note">
      Kondisi perusahaan pada triwulan ini sudah disetel <b>Aktif</b>. Ubah hanya bila
      perusahaan Anda <b>tutup</b>, <b>tutup sementara</b>, <b>bukan bidang konstruksi</b>,
      atau <b>pindah kabupaten/kota</b>. Bila diubah ke salah satu kondisi tersebut,
      pengisian berhenti di halaman ini dan Anda cukup melanjutkan ke Penutup.
    </p>
    <div class="k-radio-row">
      @foreach(K::STATUS_PERUSAHAAN_MANDIRI as $code)
        <label>
          <input type="radio" name="status_perusahaan" value="{{ $code }}"
                 @checked(old('status_perusahaan', $response->status_perusahaan ?: K::STATUS_AKTIF) === (string) $code)>
          {{ $code }}. {{ K::STATUS_PERUSAHAAN[$code] }}
        </label>
      @endforeach
    </div>
    <div class="k-err" data-field="status_perusahaan"></div>
  </div>

  <div class="k-card" id="k-pindah-wrap">
    <p class="k-card-title">4. Alamat Baru</p>
    <p class="k-note">Diisi hanya bila status perusahaan <b>6. Pindah Kabupaten/Kota</b>.</p>
    <div class="k-grid-3">
      <div>
        <label class="k-label">a. Kabupaten/Kota Baru <span class="k-req">*</span></label>
        <input name="pindah_kabupaten_kota" class="k-input" value="{{ old('pindah_kabupaten_kota', $response->pindah_kabupaten_kota) }}">
        <div class="k-err" data-field="pindah_kabupaten_kota"></div>
      </div>
      <div>
        <label class="k-label">b. Kecamatan Baru <span class="k-req">*</span></label>
        <input name="pindah_kecamatan" class="k-input" value="{{ old('pindah_kecamatan', $response->pindah_kecamatan) }}">
        <div class="k-err" data-field="pindah_kecamatan"></div>
      </div>
      <div>
        <label class="k-label">c. Desa/Kelurahan Baru <span class="k-req">*</span></label>
        <input name="pindah_kelurahan_desa" class="k-input" value="{{ old('pindah_kelurahan_desa', $response->pindah_kelurahan_desa) }}">
        <div class="k-err" data-field="pindah_kelurahan_desa"></div>
      </div>
    </div>
    <div style="margin-top:.85rem;">
      <label class="k-label">d. Alamat Baru <span class="k-req">*</span></label>
      <textarea name="pindah_alamat" class="k-textarea" rows="2">{{ old('pindah_alamat', $response->pindah_alamat) }}</textarea>
      <div class="k-err" data-field="pindah_alamat"></div>
      <div class="k-grid-3" style="margin-top:.6rem;">
        <div><label class="k-label">RT</label><input name="pindah_rt" class="k-input" value="{{ old('pindah_rt', $response->pindah_rt) }}"></div>
        <div><label class="k-label">RW</label><input name="pindah_rw" class="k-input" value="{{ old('pindah_rw', $response->pindah_rw) }}"></div>
        <div><label class="k-label">Kode Pos</label><input name="pindah_kode_pos" class="k-input" value="{{ old('pindah_kode_pos', $response->pindah_kode_pos) }}"></div>
      </div>
    </div>
  </div>

  {{--
    Kesediaan Responden is an enumerator code from the paper flow — the petugas
    records whether the company agreed to be interviewed. Filling this form
    online IS the consent, so it is recorded as "Bersedia" automatically rather
    than asked; a responden could never truthfully pick "Menolak" here.
  --}}
  <div class="k-card">
    <p class="k-card-title">2. Kesediaan Responden</p>
    <div class="k-auto-answer">
      <span class="k-chip k-chip-ok">Bersedia</span>
      <span>Terisi otomatis karena Anda mengisi survei ini secara mandiri (daring).</span>
    </div>
  </div>

  <div class="k-card" id="k-proyek-wrap">
    <p class="k-card-title">3. Keberadaan Proyek</p>
    <p class="k-note">
      Bila dijawab <b>Tidak Ada Proyek Konstruksi</b>, Blok III (Kontrak &amp; Realisasi Fisik)
      dilewati — Anda cukup mengisi Blok I, Blok II, lalu Penutup.
    </p>
    <div class="k-radio-row">
      @foreach(K::KEBERADAAN_PROYEK as $code => $label)
        <label>
          <input type="radio" name="keberadaan_proyek" value="{{ $code }}"
                 @checked(old('keberadaan_proyek', $response->keberadaan_proyek) === (string) $code)>
          {{ $code }}. {{ $label }}
        </label>
      @endforeach
    </div>
    <div class="k-err" data-field="keberadaan_proyek"></div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => null,
    'nextLabel' => 'Simpan & lanjut',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var AKTIF     = @json(\App\Models\KonstruksiSurveyResponse::STATUS_AKTIF);
  var PINDAH    = @json(\App\Models\KonstruksiSurveyResponse::STATUS_PINDAH);

  Konstruksi.bindFields({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'status'])),
    csrf: @json(csrf_token())
  });

  var pindahWrap = document.getElementById('k-pindah-wrap');
  var proyekWrap = document.getElementById('k-proyek-wrap');

  function picked(name) {
    var el = document.querySelector('input[name="' + name + '"]:checked');
    return el ? el.value : '';
  }

  // Alamat baru only applies to status 6; keberadaan proyek only matters for a
  // company that is still reporting — mirroring KonstruksiSurveyResponse's
  // isStatusComplete(), so the page never asks for something the server ignores.
  //
  // Kesediaan is not on the form (always Bersedia online), so status alone
  // decides whether the rest of the questionnaire applies.
  function sync() {
    pindahWrap.style.display = picked('status_perusahaan') === PINDAH ? '' : 'none';
    var carriesOn = picked('status_perusahaan') === AKTIF;
    proyekWrap.style.display = carriesOn ? '' : 'none';
    return carriesOn;
  }

  document.querySelectorAll('input[name="status_perusahaan"]')
    .forEach(function (el) { el.addEventListener('change', sync); });
  sync();

  document.getElementById('k-form').addEventListener('submit', function (e) {
    var rules = { status_perusahaan: 'Pilih status perusahaan.' };
    if (sync()) rules.keberadaan_proyek = 'Pilih keberadaan proyek konstruksi.';
    if (picked('status_perusahaan') === PINDAH) {
      rules.pindah_kabupaten_kota = 'Kabupaten/Kota baru wajib diisi.';
      rules.pindah_kecamatan = 'Kecamatan baru wajib diisi.';
      rules.pindah_kelurahan_desa = 'Desa/Kelurahan baru wajib diisi.';
      rules.pindah_alamat = 'Alamat baru wajib diisi.';
    }

    var form = e.currentTarget;
    var firstBad = null;
    Object.keys(rules).forEach(function (name) {
      var els = form.querySelectorAll('[name="' + name + '"]');
      if (!els.length) return;
      var filled = els[0].type === 'radio'
        ? Array.prototype.some.call(els, function (r) { return r.checked; })
        : String(els[0].value).trim() !== '';
      var err = form.querySelector('.k-err[data-field="' + name + '"]');
      if (!filled) {
        if (err) err.textContent = rules[name];
        els[0].classList.add('is-error');
        if (!firstBad) firstBad = els[0];
      } else {
        if (err) err.textContent = '';
        els[0].classList.remove('is-error');
      }
    });
    if (firstBad) {
      e.preventDefault();
      firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
      firstBad.focus({ preventScroll: true });
    }
  });

  document.getElementById('k-form').addEventListener('input', function (e) {
    if (!e.target.name) return;
    var err = document.querySelector('.k-err[data-field="' + e.target.name + '"]');
    if (err) err.textContent = '';
    e.target.classList.remove('is-error');
  });
})();
</script>
@endpush
