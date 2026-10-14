@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok VIII: Pendapatan Lain')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $data = is_array($response->blok8_pendapatan_lain) ? $response->blok8_pendapatan_lain : [];
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok VIII — Pendapatan Lain',
  'subtitle' => 'Selama tahun ' . $tahun,
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok8.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="blok8_pendapatan_lain" value="">

  <div class="k-card">
    <p class="k-card-title">Jenis Pendapatan</p>
    <p class="k-note">
      Pendapatan di luar nilai konstruksi yang diselesaikan. Nilai dalam <b>ribuan rupiah (000 Rp)</b>.
      Isi <b>0</b> bila tidak ada.
    </p>

    @include('survey.konstruksi.partials.figure-table', [
      'field'      => 'blok8_pendapatan_lain',
      'rows'       => K::PENDAPATAN_LAIN,
      'values'     => $data,
      'valueHead'  => 'Nilai (000 Rp)',
      'totalLabel' => 'JUMLAH',
      'sumKey'     => 'pendapatan_lain',
    ])

    <div id="k-lainnya-wrap" style="margin-top:1rem;">
      <label class="k-label">Rincian Lainnya — tuliskan jenis pendapatannya <span class="k-req">*</span></label>
      <input name="pendapatan_lain_lainnya" class="k-input"
             value="{{ old('pendapatan_lain_lainnya', $response->pendapatan_lain_lainnya) }}">
      <div class="k-err" data-field="pendapatan_lain_lainnya"></div>
    </div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok7', $routeParams),
    'nextLabel' => 'Simpan & lanjut ke Blok IX',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var KEYS = @json(array_keys(\App\Models\KonstruksiSurveyResponse::PENDAPATAN_LAIN));
  var lainnyaWrap = document.getElementById('k-lainnya-wrap');

  Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok8'])),
    csrf: @json(csrf_token()),
    models: { blok8_pendapatan_lain: @json($data ?: (object) []) },
    recompute: function (models, h) {
      var total = 0;
      KEYS.forEach(function (k) { total += h.num(models.blok8_pendapatan_lain[k]); });
      var cell = document.querySelector('[data-k-sum="pendapatan_lain"]');
      if (cell) cell.textContent = h.fmt(total);

      lainnyaWrap.style.display = h.num(models.blok8_pendapatan_lain.lainnya) > 0 ? '' : 'none';
    }
  });

  Konstruksi.bindFields({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok8'])),
    csrf: @json(csrf_token())
  });
})();
</script>
@endpush
