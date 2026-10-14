@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok IV: Pengeluaran Operasional')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $data = is_array($response->blok4_pengeluaran) ? $response->blok4_pengeluaran : [];
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok IV — Pengeluaran Operasional',
  'subtitle' => 'Selama tahun ' . $tahun,
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok4.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="blok4_pengeluaran" value="">

  <div class="k-card">
    <p class="k-card-title">Rincian Pengeluaran Operasional</p>
    <p class="k-note">Nilai dalam <b>ribuan rupiah (000 Rp)</b>. Isi <b>0</b> bila tidak ada pengeluaran pada suatu rincian.</p>

    @include('survey.konstruksi.partials.figure-table', [
      'field'      => 'blok4_pengeluaran',
      'rows'       => K::PENGELUARAN,
      'values'     => $data,
      'valueHead'  => 'Nilai (000 Rp)',
      'totalLabel' => 'JUMLAH [ 1 + 2 + … + 8 ]',
      'sumKey'     => 'pengeluaran',
    ])

    <div class="k-note" style="margin-top:1rem;margin-bottom:0;">
      <b>Rincian 8 — Pengeluaran Lain</b> termasuk: listrik dan air; telepon, internet, dan komunikasi lain;
      biaya penggunaan jasa pihak lain; perjalanan dinas; alat tulis kantor; pembelian suku cadang dan
      pemeliharaan/perbaikan kecil barang modal; penelitian dan pengembangan; administrasi bank dan
      perantara keuangan; serta pengeluaran operasional lain.
    </div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok3', $routeParams),
    'nextLabel' => 'Simpan & lanjut ke Blok V',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var KEYS = @json(array_keys(\App\Models\KonstruksiSurveyResponse::PENGELUARAN));

  Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok4'])),
    csrf: @json(csrf_token()),
    models: { blok4_pengeluaran: @json($data ?: (object) []) },
    recompute: function (models, h) {
      var total = 0;
      KEYS.forEach(function (k) { total += h.num(models.blok4_pengeluaran[k]); });
      var cell = document.querySelector('[data-k-sum="pengeluaran"]');
      if (cell) cell.textContent = h.fmt(total);
    }
  });
})();
</script>
@endpush
