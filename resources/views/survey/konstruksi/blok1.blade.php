@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok I: Keterangan Umum Perusahaan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok I — Keterangan Umum Perusahaan',
  'subtitle' => 'Identitas, lokasi, dan badan usaha',
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok1.save', $routeParams) }}" novalidate>
  @csrf

  <div class="k-card">
    <p class="k-card-title">Lokasi Perusahaan</p>
    <div class="k-grid-2">
      <div>
        <label class="k-label">1. Provinsi <span class="k-req">*</span></label>
        <input name="provinsi" class="k-input" value="{{ old('provinsi', $response->provinsi) }}" placeholder="Kepulauan Riau">
        <div class="k-err" data-field="provinsi"></div>
      </div>
      <div>
        <label class="k-label">2. Kabupaten / Kota <span class="k-req">*</span></label>
        <input name="kabupaten_kota" class="k-input" value="{{ old('kabupaten_kota', $response->kabupaten_kota) }}" placeholder="Kota Batam">
        <div class="k-err" data-field="kabupaten_kota"></div>
      </div>
      <div>
        <label class="k-label">3. Kecamatan <span class="k-req">*</span></label>
        <input name="kecamatan" class="k-input" value="{{ old('kecamatan', $response->kecamatan) }}">
        <div class="k-err" data-field="kecamatan"></div>
      </div>
      <div>
        <label class="k-label">4. Desa / Kelurahan <span class="k-req">*</span></label>
        <input name="kelurahan_desa" class="k-input" value="{{ old('kelurahan_desa', $response->kelurahan_desa) }}">
        <div class="k-err" data-field="kelurahan_desa"></div>
      </div>
      <div>
        <label class="k-label">5. Nomor Urut Sampel (NUS) <span class="k-req">*</span></label>
        <input name="nus" class="k-input" value="{{ old('nus', $response->nus) }}">
        <div class="k-err" data-field="nus"></div>
      </div>
      <div>
        <label class="k-label">Kode Identitas Perusahaan (KIP)</label>
        <input name="kip" class="k-input" value="{{ old('kip', $response->kip) }}" placeholder="Diisi oleh petugas bila ada">
        <div class="k-err" data-field="kip"></div>
      </div>
    </div>
  </div>

  <div class="k-card">
    <p class="k-card-title">6. Identitas Perusahaan</p>

    <div style="margin-bottom:1rem;">
      <label class="k-label">a. Nama Perusahaan <span class="k-req">*</span></label>
      <input name="nama_perusahaan" class="k-input" value="{{ old('nama_perusahaan', $response->nama_perusahaan) }}"
             placeholder="Tuliskan lengkap beserta status badan usaha">
      <div class="k-err" data-field="nama_perusahaan"></div>
    </div>

    <div style="margin-bottom:1rem;">
      <label class="k-label">b. Apakah memiliki Nomor Induk Berusaha (NIB)? <span class="k-req">*</span></label>
      <div class="k-radio-row">
        <label><input type="radio" name="punya_nib" value="ya" @checked(old('punya_nib', $response->punya_nib) === 'ya')> 1. Ya</label>
        <label><input type="radio" name="punya_nib" value="tidak" @checked(old('punya_nib', $response->punya_nib) === 'tidak')> 2. Tidak</label>
      </div>
      <div class="k-err" data-field="punya_nib"></div>
      <div id="k-nib-wrap" style="margin-top:.6rem;">
        <label class="k-label">Jika Ya, tuliskan NIB</label>
        <input name="nib" class="k-input" value="{{ old('nib', $response->nib) }}">
        <div class="k-err" data-field="nib"></div>
      </div>
    </div>

    <div class="k-grid-2" style="margin-bottom:1rem;">
      <div>
        <label class="k-label">c. Nama Pengusaha / Penanggung Jawab <span class="k-req">*</span></label>
        <input name="nama_pengusaha" class="k-input" value="{{ old('nama_pengusaha', $response->nama_pengusaha) }}">
        <div class="k-err" data-field="nama_pengusaha"></div>
      </div>
      <div>
        <label class="k-label">No. HP <span class="k-req">*</span></label>
        <input name="hp_pengusaha" class="k-input" value="{{ old('hp_pengusaha', $response->hp_pengusaha) }}">
        <div class="k-err" data-field="hp_pengusaha"></div>
      </div>
    </div>

    <div style="margin-bottom:1rem;">
      <label class="k-label">d. Alamat Perusahaan <span class="k-req">*</span></label>
      <textarea name="alamat_perusahaan" class="k-textarea" rows="2">{{ old('alamat_perusahaan', $response->alamat_perusahaan) }}</textarea>
      <div class="k-err" data-field="alamat_perusahaan"></div>
      <div class="k-grid-3" style="margin-top:.6rem;">
        <div><label class="k-label">RT</label><input name="rt" class="k-input" value="{{ old('rt', $response->rt) }}"></div>
        <div><label class="k-label">RW</label><input name="rw" class="k-input" value="{{ old('rw', $response->rw) }}"></div>
        <div><label class="k-label">Kode Pos</label><input name="kode_pos" class="k-input" value="{{ old('kode_pos', $response->kode_pos) }}"></div>
      </div>
      <div class="k-grid-3" style="margin-top:.6rem;">
        <div><label class="k-label">Telepon</label><input name="nomor_telepon" class="k-input" value="{{ old('nomor_telepon', $response->nomor_telepon) }}"></div>
        <div><label class="k-label">Website</label><input name="website" class="k-input" value="{{ old('website', $response->website) }}"></div>
        <div><label class="k-label">E-mail</label><input name="email_perusahaan" type="email" class="k-input" value="{{ old('email_perusahaan', $response->email_perusahaan) }}"></div>
      </div>
    </div>

    <div>
      <label class="k-label">e. Alamat Operasional</label>
      <p class="k-hint" style="margin-top:0;margin-bottom:.35rem;">Kosongkan bila sama dengan alamat perusahaan.</p>
      <textarea name="alamat_operasional" class="k-textarea" rows="2">{{ old('alamat_operasional', $response->alamat_operasional) }}</textarea>
      <div class="k-grid-3" style="margin-top:.6rem;">
        <div><label class="k-label">RT</label><input name="rt_operasional" class="k-input" value="{{ old('rt_operasional', $response->rt_operasional) }}"></div>
        <div><label class="k-label">RW</label><input name="rw_operasional" class="k-input" value="{{ old('rw_operasional', $response->rw_operasional) }}"></div>
        <div><label class="k-label">Kode Pos</label><input name="kode_pos_operasional" class="k-input" value="{{ old('kode_pos_operasional', $response->kode_pos_operasional) }}"></div>
      </div>
    </div>
  </div>

  <div class="k-card">
    <p class="k-card-title">Badan Usaha, Permodalan &amp; Kualifikasi</p>

    <div style="margin-bottom:1.15rem;">
      <label class="k-label">f. Badan Hukum / Badan Usaha <span class="k-req">*</span></label>
      <div class="k-radio-row">
        @foreach(\App\Models\KonstruksiSurveyResponse::BADAN_HUKUM as $code => $label)
          <label><input type="radio" name="badan_hukum" value="{{ $code }}"
                        @checked(old('badan_hukum', $response->badan_hukum) === (string) $code)> {{ $code }}. {{ $label }}</label>
        @endforeach
      </div>
      <div class="k-err" data-field="badan_hukum"></div>
      <div id="k-bh-wrap" style="margin-top:.6rem;">
        <label class="k-label">Jika Lainnya, tuliskan</label>
        <input name="badan_hukum_lainnya" class="k-input" value="{{ old('badan_hukum_lainnya', $response->badan_hukum_lainnya) }}">
        <div class="k-err" data-field="badan_hukum_lainnya"></div>
      </div>
    </div>

    <div style="margin-bottom:1.15rem;">
      <label class="k-label">g. Status Penanaman Modal <span class="k-req">*</span></label>
      <div class="k-radio-row">
        @foreach(\App\Models\KonstruksiSurveyResponse::STATUS_MODAL as $code => $label)
          <label><input type="radio" name="status_penanaman_modal" value="{{ $code }}"
                        @checked(old('status_penanaman_modal', $response->status_penanaman_modal) === (string) $code)> {{ $code }}. {{ $label }}</label>
        @endforeach
      </div>
      <div class="k-err" data-field="status_penanaman_modal"></div>
    </div>

    <div>
      <label class="k-label">h. Kualifikasi Perusahaan <span class="k-req">*</span></label>
      <div class="k-radio-row">
        @foreach(\App\Models\KonstruksiSurveyResponse::KUALIFIKASI as $code => $label)
          <label><input type="radio" name="kualifikasi" value="{{ $code }}"
                        @checked(old('kualifikasi', $response->kualifikasi) === (string) $code)> {{ $code }}. {{ $label }}</label>
        @endforeach
      </div>
      <div class="k-err" data-field="kualifikasi"></div>
    </div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    {{-- Blok I is no longer the first page: the preamble is. --}}
    'prevUrl'   => route('survey.konstruksi.status', $routeParams),
    'nextLabel' => 'Simpan & lanjut ke Blok II',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  Konstruksi.bindFields({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok1'])),
    csrf: @json(csrf_token())
  });

  Konstruksi.requireFields('#k-form', {
    provinsi: 'Provinsi wajib diisi.',
    kabupaten_kota: 'Kabupaten/Kota wajib diisi.',
    kecamatan: 'Kecamatan wajib diisi.',
    kelurahan_desa: 'Desa/Kelurahan wajib diisi.',
    nus: 'Nomor Urut Sampel wajib diisi.',
    nama_perusahaan: 'Nama perusahaan wajib diisi.',
    punya_nib: 'Pilih salah satu.',
    nama_pengusaha: 'Nama penanggung jawab wajib diisi.',
    hp_pengusaha: 'Nomor HP wajib diisi.',
    alamat_perusahaan: 'Alamat perusahaan wajib diisi.',
    badan_hukum: 'Pilih badan hukum/badan usaha.',
    status_penanaman_modal: 'Pilih status penanaman modal.',
    kualifikasi: 'Pilih kualifikasi perusahaan.'
  });

  // The two conditional follow-ups: NIB only matters when "Ya", and the free
  // text for badan hukum only when code 9 (Lainnya) is picked.
  function toggle(wrapId, on) {
    var el = document.getElementById(wrapId);
    if (el) el.style.display = on ? '' : 'none';
  }
  function sync() {
    var nib = document.querySelector('input[name="punya_nib"]:checked');
    toggle('k-nib-wrap', !!nib && nib.value === 'ya');
    var bh = document.querySelector('input[name="badan_hukum"]:checked');
    toggle('k-bh-wrap', !!bh && bh.value === '9');
  }
  document.querySelectorAll('input[name="punya_nib"], input[name="badan_hukum"]')
    .forEach(function (el) { el.addEventListener('change', sync); });
  sync();
})();
</script>
@endpush
