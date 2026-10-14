@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok II: Tenaga Kerja & Balas Jasa')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $pekerja   = is_array($response->blok2_pekerja) ? $response->blok2_pekerja : [];
  $balasJasa = is_array($response->blok2_balas_jasa) ? $response->blok2_balas_jasa : [];
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok II — Tenaga Kerja & Pengeluaran Balas Jasa',
  'subtitle' => 'Selama tahun ' . $tahun,
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok2.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="blok2_pekerja" value="">
  <input type="hidden" name="blok2_balas_jasa" value="">

  <div class="k-card">
    <p class="k-card-title">A. Pekerja Tetap &amp; Kontrak menurut Pendidikan dan Jenis Kelamin</p>
    <p class="k-note">
      <b>Pekerja tetap</b> mendapatkan upah/gaji secara tetap, tidak tergantung kehadiran, dan biasanya
      mendapat pesangon bila diberhentikan — pemilik perusahaan termasuk pekerja tetap.
      <b>Pekerja kontrak</b> bekerja dengan perjanjian tertentu dan tidak mengikuti jenjang karir perusahaan.
      Termasuk pekerja WNI maupun WNA, dan pekerja <i>outsourcing</i>.
      Kondisi per 31 Desember {{ $tahun }}. Isi <b>0</b> bila tidak ada.
    </p>

    <div class="k-scroll">
      <table class="k-table">
        <thead>
          <tr>
            <th style="text-align:left;">Jenjang Pendidikan yang Ditamatkan</th>
            <th style="width:11rem;">Laki-Laki (orang)</th>
            <th style="width:11rem;">Perempuan (orang)</th>
            <th style="width:9rem;">Jumlah</th>
          </tr>
        </thead>
        <tbody>
          @foreach(K::PENDIDIKAN as $key => $label)
            <tr>
              <td>{{ $loop->iteration }}. {{ $label }}</td>
              @foreach(['l' => 'Laki-laki', 'p' => 'Perempuan'] as $jk => $jkLabel)
                <td>
                  <input class="k-input k-num" inputmode="numeric"
                         data-k-field="blok2_pekerja" data-k-path="{{ $key }}.{{ $jk }}"
                         aria-label="{{ $label }} — {{ $jkLabel }}"
                         value="{{ $pekerja[$key][$jk] ?? '' }}">
                </td>
              @endforeach
              <td class="k-readonly" data-k-sum="pekerja.{{ $key }}">0</td>
            </tr>
          @endforeach
          <tr class="k-total-row">
            <td>JUMLAH PEKERJA TETAP DAN KONTRAK</td>
            <td class="k-readonly" data-k-sum="pekerja.l">0</td>
            <td class="k-readonly" data-k-sum="pekerja.p">0</td>
            <td class="k-readonly" data-k-sum="pekerja.total">0</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <div class="k-card">
    <p class="k-card-title">B. Pengeluaran Balas Jasa Pekerja Tetap &amp; Kontrak</p>
    <p class="k-note">
      Balas jasa pekerja adalah kompensasi yang diberikan perusahaan kepada pekerja/karyawannya,
      <b>sebelum dikurangi pajak</b>, baik dalam bentuk uang maupun barang. Nilai dalam <b>ribuan rupiah (000 Rp)</b>.
      Isi <b>0</b> bila tidak ada.
    </p>

    <div class="k-scroll">
      <table class="k-table">
        <thead>
          <tr>
            <th style="text-align:left;">Rincian (1)</th>
            <th style="width:16rem;">Nilai (000 Rp) (2)</th>
          </tr>
        </thead>
        <tbody>
          @foreach(K::BALAS_JASA as $key => $label)
            <tr>
              <td>{{ $loop->iteration }}. {{ $label }}</td>
              <td>
                <input class="k-input k-num" inputmode="numeric"
                       data-k-field="blok2_balas_jasa" data-k-path="{{ $key }}"
                       aria-label="{{ $label }}"
                       value="{{ $balasJasa[$key] ?? '' }}">
              </td>
            </tr>
          @endforeach
          <tr class="k-total-row">
            <td>JUMLAH [ 1 + 2 + … + 6 ]</td>
            <td class="k-readonly" data-k-sum="balas_jasa">0</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok1', $routeParams),
    {{-- proyekBlokApplies(), not hasProyek(): a blank keberadaan proyek still
         keeps the rest of the form on the rail, and the label must promise the
         same page the save actually redirects to. --}}
    'nextLabel' => $response->proyekBlokApplies() ? 'Simpan & lanjut ke Blok III' : 'Simpan & lanjut ke Blok X',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var PENDIDIKAN = @json(array_keys(\App\Models\KonstruksiSurveyResponse::PENDIDIKAN));
  var BALAS_JASA = @json(array_keys(\App\Models\KonstruksiSurveyResponse::BALAS_JASA));

  function cell(key) { return document.querySelector('[data-k-sum="' + key + '"]'); }
  function put(key, value) { var el = cell(key); if (el) el.textContent = Konstruksi.fmt(value); }

  Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok2'])),
    csrf: @json(csrf_token()),
    models: {
      blok2_pekerja: @json($pekerja ?: (object) []),
      blok2_balas_jasa: @json($balasJasa ?: (object) [])
    },
    recompute: function (models, h) {
      var totalL = 0, totalP = 0;
      PENDIDIKAN.forEach(function (j) {
        var row = models.blok2_pekerja[j] || {};
        var l = h.num(row.l), p = h.num(row.p);
        totalL += l;
        totalP += p;
        put('pekerja.' + j, l + p);
      });
      put('pekerja.l', totalL);
      put('pekerja.p', totalP);
      put('pekerja.total', totalL + totalP);

      var bj = 0;
      BALAS_JASA.forEach(function (k) { bj += h.num(models.blok2_balas_jasa[k]); });
      put('balas_jasa', bj);
    }
  });
})();
</script>
@endpush
