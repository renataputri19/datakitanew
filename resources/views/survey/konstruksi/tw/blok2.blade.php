@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi Triwulanan — Blok II: Tenaga Kerja & Balas Jasa')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $pekerja   = is_array($response->tw_pekerja) ? $response->tw_pekerja : [];
  $balasJasa = is_array($response->tw_balas_jasa) ? $response->tw_balas_jasa : [];
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok II — Tenaga Kerja & Balas Jasa',
  'subtitle' => 'Dua triwulan terakhir',
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok2.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="tw_pekerja" value="">
  <input type="hidden" name="tw_balas_jasa" value="">

  <div class="k-card">
    <p class="k-card-title">Tenaga Kerja dan Balas Jasa</p>
    <p class="k-note">
      Termasuk pekerja WNI dan WNA, serta <i>outsourcing</i>.
      <b>Tidak termasuk</b> pekerja tidak tetap/harian — pekerja harian dilaporkan pada Blok III.
      Balas jasa dalam <b>ribuan rupiah (000 Rp)</b>. Isi <b>0</b> bila tidak ada.
    </p>

    <div class="k-scroll">
      <table class="k-table">
        <thead>
          <tr>
            <th style="text-align:left;">Triwulan</th>
            <th style="width:14rem;">1. Banyaknya Pekerja Tetap &amp; Kontrak (orang)</th>
            <th style="width:16rem;">2. Jumlah Balas Jasa (000 Rp)</th>
          </tr>
        </thead>
        <tbody>
          @foreach(K::TW_SLOTS as $slot => $delta)
            <tr>
              <td>
                {{ $loop->iteration === 1 ? 'a.' : 'b.' }}
                {{ $quarterLabels[$slot]['range'] ?? $slot }}
              </td>
              <td>
                <input class="k-input k-num" inputmode="numeric"
                       data-k-field="tw_pekerja" data-k-path="{{ $slot }}"
                       aria-label="Pekerja {{ $quarterLabels[$slot]['short'] ?? '' }}"
                       value="{{ $pekerja[$slot] ?? '' }}">
              </td>
              <td>
                <input class="k-input k-num" inputmode="numeric"
                       data-k-field="tw_balas_jasa" data-k-path="{{ $slot }}"
                       aria-label="Balas jasa {{ $quarterLabels[$slot]['short'] ?? '' }}"
                       value="{{ $balasJasa[$slot] ?? '' }}">
              </td>
            </tr>
          @endforeach
          <tr class="k-total-row">
            <td>Perubahan dari triwulan sebelumnya</td>
            <td class="k-readonly" data-k-sum="delta_pekerja">—</td>
            <td class="k-readonly" data-k-sum="delta_balas_jasa">—</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok1', $routeParams),
    {{-- proyekBlokApplies(), not hasProyek(): a blank keberadaan proyek still
         keeps Blok III on the path, so the label must promise the same next
         page the save actually redirects to. --}}
    'nextLabel' => $response->proyekBlokApplies() ? 'Simpan & lanjut ke Blok III' : 'Simpan & lanjut ke Penutup',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  function put(key, text) {
    var el = document.querySelector('[data-k-sum="' + key + '"]');
    if (el) el.textContent = text;
  }

  // A quarter-on-quarter delta is the fastest way for a responden to spot a
  // digit slipped into the wrong column, which is the common error here.
  function delta(prev, cur, h) {
    if (String(prev).trim() === '' || String(cur).trim() === '') return '—';
    var d = h.num(cur) - h.num(prev);
    return (d > 0 ? '+' : '') + h.fmt(d);
  }

  Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok2'])),
    csrf: @json(csrf_token()),
    models: {
      tw_pekerja: @json($pekerja ?: (object) []),
      tw_balas_jasa: @json($balasJasa ?: (object) [])
    },
    recompute: function (models, h) {
      put('delta_pekerja', delta(models.tw_pekerja.prev, models.tw_pekerja.cur, h));
      put('delta_balas_jasa', delta(models.tw_balas_jasa.prev, models.tw_balas_jasa.cur, h));
    }
  });
})();
</script>
@endpush
