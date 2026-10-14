@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok III: Kepemilikan Modal')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $modal = is_array($response->blok3_modal) ? $response->blok3_modal : [];
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok III — Kepemilikan Modal Perusahaan',
  'subtitle' => 'Kondisi 31 Desember ' . $tahun,
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok3.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="blok3_modal" value="">

  <div class="k-card">
    <p class="k-card-title">Sumber Modal</p>
    <p class="k-note">
      Tuliskan persentase kepemilikan modal per sumber. <b>Jumlah keempat rincian harus tepat 100 %</b>.
      Isi <b>0</b> untuk sumber yang tidak ada.
    </p>

    @include('survey.konstruksi.partials.figure-table', [
      'field'      => 'blok3_modal',
      'rows'       => K::SUMBER_MODAL,
      'values'     => $modal,
      'valueHead'  => 'Persentase (%)',
      'totalLabel' => 'JUMLAH [ 1 + 2 + 3 + 4 ]',
      'sumKey'     => 'modal',
      'decimal'    => true,
    ])

    <p class="k-err" id="k-modal-warn" style="margin-top:.6rem;"></p>

    <div id="k-negara-wrap" style="margin-top:1rem;">
      <label class="k-label">
        Jika memiliki modal asing, tuliskan negara dengan kepemilikan modal terbesar <span class="k-req">*</span>
      </label>
      <input name="modal_asing_negara" class="k-input"
             value="{{ old('modal_asing_negara', $response->modal_asing_negara) }}"
             placeholder="Nama negara">
      <div class="k-err" data-field="modal_asing_negara"></div>
    </div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok2', $routeParams),
    'nextLabel' => 'Simpan & lanjut ke Blok IV',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var KEYS = @json(array_keys(\App\Models\KonstruksiSurveyResponse::SUMBER_MODAL));
  var warn = document.getElementById('k-modal-warn');
  var negaraWrap = document.getElementById('k-negara-wrap');
  var form = document.getElementById('k-form');

  var api = Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok3'])),
    csrf: @json(csrf_token()),
    models: { blok3_modal: @json($modal ?: (object) []) },
    recompute: function (models, h) {
      var total = 0;
      KEYS.forEach(function (k) { total += h.num(models.blok3_modal[k]); });

      var cell = document.querySelector('[data-k-sum="modal"]');
      if (cell) cell.textContent = h.fmt(total, true) + ' %';

      // Off-100 is the single most common error on this blok, so say so live
      // rather than letting the server bounce the form back.
      warn.textContent = Math.abs(total - 100) < 0.001
        ? ''
        : 'Jumlah persentase saat ini ' + h.fmt(total, true) + ' % — harus tepat 100 %.';

      // Negara only applies when there is foreign capital.
      negaraWrap.style.display = h.num(models.blok3_modal.asing) > 0 ? '' : 'none';
    }
  });

  // Keep the country field autosaving; it is a plain column, not part of the model.
  Konstruksi.bindFields({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok3'])),
    csrf: @json(csrf_token())
  });

  form.addEventListener('submit', function (e) {
    var total = 0;
    KEYS.forEach(function (k) { total += Konstruksi.num(api.models.blok3_modal[k]); });
    if (Math.abs(total - 100) > 0.001) {
      e.preventDefault();
      api.status.show('Jumlah persentase kepemilikan modal harus tepat 100 %.', 'error');
      warn.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
})();
</script>
@endpush
