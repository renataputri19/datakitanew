@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok V: Barang Modal')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $data = is_array($response->blok5_barang_modal) ? $response->blok5_barang_modal : [];
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok V — Penambahan & Pengurangan Barang Modal',
  'subtitle' => 'Selama tahun ' . $tahun . ' (000 Rp)',
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok5.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="blok5_barang_modal" value="">

  <div class="k-card">
    <p class="k-card-title">Jenis Barang Modal</p>
    <p class="k-note">
      <b>Penambahan</b> mencakup pembelian, barter, produksi sendiri, sewa beli (<i>financial lease</i>),
      pemberian/transfer/hibah dari pihak lain, dan perbaikan besar barang modal.<br>
      <b>Pengurangan</b> mencakup penjualan, barter, dan pemberian/transfer/hibah ke pihak lain.
      Kehilangan karena pencurian, bencana alam, dan kebakaran <b>tidak</b> dicatat sebagai pengurangan.
      Nilai dalam ribuan rupiah. Isi <b>0</b> bila tidak ada.
    </p>

    <div class="k-scroll">
      <table class="k-table">
        <thead>
          <tr>
            <th style="text-align:left;">Jenis Barang Modal (1)</th>
            <th style="width:13rem;">Penambahan (2)</th>
            <th style="width:13rem;">Pengurangan (3)</th>
          </tr>
        </thead>
        <tbody>
          @foreach(K::BARANG_MODAL as $key => $label)
            <tr>
              <td>{{ $loop->iteration }}. {{ $label }}</td>
              @foreach(['tambah' => 'Penambahan', 'kurang' => 'Pengurangan'] as $arah => $arahLabel)
                <td>
                  <input class="k-input k-num" inputmode="numeric"
                         data-k-field="blok5_barang_modal" data-k-path="{{ $key }}.{{ $arah }}"
                         aria-label="{{ $label }} — {{ $arahLabel }}"
                         value="{{ $data[$key][$arah] ?? '' }}">
                </td>
              @endforeach
            </tr>
          @endforeach
          <tr class="k-total-row">
            <td>JUMLAH [ 1 + 2 + … + 6 ]</td>
            <td class="k-readonly" data-k-sum="tambah">0</td>
            <td class="k-readonly" data-k-sum="kurang">0</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div id="k-lainnya-wrap" style="margin-top:1rem;">
      <label class="k-label">Rincian 6 (Lainnya) — tuliskan jenis barang modalnya <span class="k-req">*</span></label>
      <input name="barang_modal_lainnya" class="k-input"
             value="{{ old('barang_modal_lainnya', $response->barang_modal_lainnya) }}"
             placeholder="Contoh: mesin las, perancah, alat ukur">
      <div class="k-err" data-field="barang_modal_lainnya"></div>
    </div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok4', $routeParams),
    'nextLabel' => 'Simpan & lanjut ke Blok VI',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var KEYS = @json(array_keys(\App\Models\KonstruksiSurveyResponse::BARANG_MODAL));
  var lainnyaWrap = document.getElementById('k-lainnya-wrap');

  Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok5'])),
    csrf: @json(csrf_token()),
    models: { blok5_barang_modal: @json($data ?: (object) []) },
    recompute: function (models, h) {
      var tambah = 0, kurang = 0;
      KEYS.forEach(function (k) {
        var row = models.blok5_barang_modal[k] || {};
        tambah += h.num(row.tambah);
        kurang += h.num(row.kurang);
      });
      var a = document.querySelector('[data-k-sum="tambah"]');
      var b = document.querySelector('[data-k-sum="kurang"]');
      if (a) a.textContent = h.fmt(tambah);
      if (b) b.textContent = h.fmt(kurang);

      // The free-text description is only required when "Lainnya" carries a value.
      var l = models.blok5_barang_modal.lainnya || {};
      lainnyaWrap.style.display = (h.num(l.tambah) + h.num(l.kurang)) > 0 ? '' : 'none';
    }
  });

  Konstruksi.bindFields({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok5'])),
    csrf: @json(csrf_token())
  });
})();
</script>
@endpush
