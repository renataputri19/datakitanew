@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok X: Catatan & Selesai')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $sisa = array_filter($checklist, fn ($c) => !$c['done']);
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok X — Metode Pendataan, Catatan & Keterangan Pemberi Jawaban',
  'subtitle' => 'Langkah terakhir',
])

{{-- Say why the questionnaire was short, so a responden who arrives here after
     three pages does not think blocks went missing. --}}
@if($response->stoppedAtStatus())
  <div class="k-card">
    <p class="k-card-title">Pengisian Dihentikan</p>
    <p class="k-note" style="margin-bottom:0;">
      Berdasarkan jawaban pada Updating Status Perusahaan
      (<b>{{ K::STATUS_PERUSAHAAN[$response->status_perusahaan] ?? '—' }}</b>),
      Blok I sampai Blok IX tidak perlu diisi. Lengkapi keterangan di bawah ini
      untuk menyelesaikan pencacahan tahun {{ $tahun }}.
    </p>
  </div>
@elseif(!$response->proyekBlokApplies())
  <div class="k-card">
    <p class="k-card-title">Tidak Ada Proyek Konstruksi</p>
    <p class="k-note" style="margin-bottom:0;">
      Anda menjawab <b>Tidak Ada Proyek Konstruksi</b> pada tahun {{ $tahun }},
      sehingga Blok III sampai Blok IX dilewati. Lengkapi keterangan di bawah ini
      untuk menyelesaikan survei. Bila ternyata ada proyek yang dikerjakan,
      ubah jawaban tersebut di
      <a href="{{ route('survey.konstruksi.status', $routeParams) }}">Updating Status Perusahaan</a>.
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
      Masih ada {{ count($sisa) }} blok yang belum lengkap. Survei baru dapat diselesaikan setelah semuanya lengkap.
    </p>
  @endif
</div>

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok10.finish', $routeParams) }}" novalidate>
  @csrf

  <div class="k-card">
    <p class="k-card-title">Metode Pendataan Selanjutnya</p>
    <label class="k-label">
      Menurut perusahaan, metode pendataan selanjutnya lebih baik dilakukan secara <span class="k-req">*</span>
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
        <label class="k-label">Nama <span class="k-req">*</span></label>
        <input name="pj_nama" class="k-input" value="{{ old('pj_nama', $response->pj_nama) }}">
        <div class="k-err" data-field="pj_nama"></div>
      </div>
      <div>
        <label class="k-label">Nomor HP / Telepon <span class="k-req">*</span></label>
        <input name="pj_hp" class="k-input" value="{{ old('pj_hp', $response->pj_hp) }}">
        <div class="k-err" data-field="pj_hp"></div>
      </div>
      <div>
        <label class="k-label">Jabatan <span class="k-req">*</span></label>
        <input name="pj_jabatan" class="k-input" value="{{ old('pj_jabatan', $response->pj_jabatan) }}">
        <div class="k-err" data-field="pj_jabatan"></div>
      </div>
      <div>
        <label class="k-label">E-mail</label>
        <input name="pj_email" type="email" class="k-input" value="{{ old('pj_email', $response->pj_email) }}">
        <div class="k-err" data-field="pj_email"></div>
      </div>
    </div>
  </div>

  <div class="k-actions">
    @php
      // Blok IX is only the previous page when the full questionnaire applies.
      // A nil year runs status → Blok I → here, and a company that stopped at
      // the preamble runs status → here.
      $prevKey = $response->stoppedAtStatus()
        ? 'status'
        : ($response->proyekBlokApplies() ? 'blok9' : 'blok2');
    @endphp
    <a href="{{ route('survey.konstruksi.' . $prevKey, $routeParams) }}" class="k-btn k-btn-ghost">&larr; Blok sebelumnya</a>
    <button type="submit" class="k-btn k-btn-primary" @disabled((bool) $sisa)>
      @if($sisa) Lengkapi blok lain dulu @else Selesaikan Survei @endif
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
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok10'])),
    csrf: @json(csrf_token())
  });

  Konstruksi.requireFields('#k-form', {
    metode_pendataan: 'Pilih salah satu metode pendataan.',
    catatan: 'Catatan wajib diisi. Jika tidak ada, isi dengan tanda "-".',
    pj_nama: 'Nama pemberi jawaban wajib diisi.',
    pj_hp: 'Nomor HP/telepon wajib diisi.',
    pj_jabatan: 'Jabatan wajib diisi.'
  });
})();
</script>
@endpush
