@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi Triwulanan — Penutup')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $sisa = array_filter($checklist, fn ($c) => !$c['done']);
  // The questionnaire asks about next year's collection method.
  $tahunDepan = (int) $tahun + 1;
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Penutup — Metode Pendataan, Catatan & Pemberi Jawaban',
  'subtitle' => 'Langkah terakhir',
])

@if($response->stoppedAtStatus())
  <div class="k-card">
    <p class="k-card-title">Pengisian Dihentikan</p>
    <p class="k-note" style="margin-bottom:0;">
      Berdasarkan jawaban pada Updating Status Perusahaan
      (<b>{{ K::STATUS_PERUSAHAAN[$response->status_perusahaan] ?? '—' }}</b>,
      <b>{{ K::KESEDIAAN_RESPONDEN[$response->kesediaan_responden] ?? '—' }}</b>),
      Blok I sampai Blok III tidak perlu diisi. Lengkapi keterangan di bawah ini
      untuk menyelesaikan pencacahan triwulan ini.
    </p>
  </div>
@endif

<div class="k-card">
  <p class="k-card-title">Kelengkapan Pengisian</p>
  <ul class="k-checklist">
    @foreach($checklist as $item)
      <li>
        <span>{{ $item['label'] }}</span>
        @if($item['done'])
          <span class="k-chip k-chip-ok">Lengkap</span>
        @else
          <a href="{{ $item['url'] }}"><span class="k-chip k-chip-bad">Belum lengkap — isi sekarang</span></a>
        @endif
      </li>
    @endforeach
  </ul>
  @if($sisa)
    <p class="k-note" style="margin-top:1rem;margin-bottom:0;border-left-color:var(--k-danger);">
      Masih ada {{ count($sisa) }} bagian yang belum lengkap. Survei baru dapat diselesaikan setelah semuanya lengkap.
    </p>
  @endif
</div>

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.penutup.finish', $routeParams) }}" novalidate>
  @csrf

  <div class="k-card">
    <p class="k-card-title">Metode Pendataan Tahun {{ $tahunDepan }}</p>
    <label class="k-label">
      Menurut perusahaan, metode pendataan Tahun {{ $tahunDepan }} lebih baik dilakukan secara
      <span class="k-req">*</span>
    </label>
    <div class="k-radio-row">
      @foreach(K::METODE_PENDATAAN as $code => $label)
        <label>
          <input type="radio" name="metode_pendataan" value="{{ $code }}"
                 @checked(old('metode_pendataan', $response->metode_pendataan) === (string) $code)>
          {{ $code }}. {{ $label }}
        </label>
      @endforeach
    </div>
    <div class="k-err" data-field="metode_pendataan"></div>
  </div>

  <div class="k-card">
    <p class="k-card-title">Catatan</p>
    <label class="k-label">Catatan tambahan <span class="k-req">*</span></label>
    <textarea name="catatan" class="k-textarea" rows="5"
              placeholder="Tuliskan hal yang perlu diketahui petugas. Jika tidak ada, isi dengan tanda &quot;-&quot;.">{{ old('catatan', $response->catatan) }}</textarea>
    <div class="k-err" data-field="catatan"></div>
  </div>

  <div class="k-card">
    <p class="k-card-title">Keterangan Pemberi Jawaban</p>
    <div class="k-grid-2">
      <div>
        <label class="k-label">1. Nama <span class="k-req">*</span></label>
        <input name="pj_nama" class="k-input" value="{{ old('pj_nama', $response->pj_nama) }}">
        <div class="k-err" data-field="pj_nama"></div>
      </div>
      <div>
        <label class="k-label">2. Nomor Telepon / HP <span class="k-req">*</span></label>
        <input name="pj_hp" class="k-input" value="{{ old('pj_hp', $response->pj_hp) }}">
        <div class="k-err" data-field="pj_hp"></div>
      </div>
      <div>
        <label class="k-label">3. Jabatan <span class="k-req">*</span></label>
        <input name="pj_jabatan" class="k-input" value="{{ old('pj_jabatan', $response->pj_jabatan) }}">
        <div class="k-err" data-field="pj_jabatan"></div>
      </div>
      <div>
        <label class="k-label">4. E-mail</label>
        <input name="pj_email" type="email" class="k-input" value="{{ old('pj_email', $response->pj_email) }}">
        <div class="k-err" data-field="pj_email"></div>
      </div>
    </div>
  </div>

  <div class="k-actions">
    @php
      // proyekBlokApplies(), not hasProyek(): a blank keberadaan proyek still
      // keeps Blok III on the rail, and "Sebelumnya" must agree with it.
      $prevKey = !$response->stoppedAtStatus()
        ? ($response->proyekBlokApplies() ? 'blok3' : 'blok2')
        : 'status';
    @endphp
    <a href="{{ route('survey.konstruksi.' . $prevKey, $routeParams) }}" class="k-btn k-btn-ghost">&larr; Sebelumnya</a>
    <button type="submit" class="k-btn k-btn-primary" @disabled((bool) $sisa)>
      @if($sisa) Lengkapi bagian lain dulu @else Selesaikan Survei @endif
    </button>
  </div>
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  Konstruksi.bindFields({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'penutup'])),
    csrf: @json(csrf_token())
  });

  Konstruksi.requireFields('#k-form', {
    metode_pendataan: 'Pilih salah satu metode pendataan.',
    catatan: 'Catatan wajib diisi. Jika tidak ada, isi dengan tanda "-".',
    pj_nama: 'Nama pemberi jawaban wajib diisi.',
    pj_hp: 'Nomor telepon/HP wajib diisi.',
    pj_jabatan: 'Jabatan wajib diisi.'
  });
})();
</script>
@endpush
