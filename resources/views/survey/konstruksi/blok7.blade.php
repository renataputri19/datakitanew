@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok VII: Struktur Bahan/Material')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $bahan = $response->bahanRows();
  if ($bahan === []) {
      $bahan = [['nama' => '', 'nilai' => '']];
  }
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok VII — Struktur Bahan/Material Proyek Utama',
  'subtitle' => 'Untuk pekerjaan/proyek utama tahun ' . $tahun,
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok7.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="blok7_bahan" value="">

  <div class="k-card">
    <p class="k-card-title">Jenis Bahan / Material</p>
    <p class="k-note">
      Tuliskan semua bahan/material yang digunakan untuk <b>pekerjaan/proyek utama</b> — yaitu proyek dengan
      nilai konstruksi yang diselesaikan terbesar — yang mulai dan selesai pada tahun {{ $tahun }}.
      Contoh: hotmix, tiang pancang, keramik, baja ringan, wallpaper, eskalator, plafon, trafo, kabel, steker.
      Jika lebih dari 10 jenis, jumlahkan sisanya pada satu baris <b>Lainnya</b>.
      Nilai dalam <b>ribuan rupiah (000 Rp)</b>.
    </p>

    <div class="k-scroll">
      <table class="k-table">
        <thead>
          <tr>
            <th style="width:2.5rem;">No.</th>
            <th style="text-align:left;">Jenis Bahan / Material (1)</th>
            <th style="width:14rem;">Nilai (000 Rp) (2)</th>
            <th style="width:3rem;"></th>
          </tr>
        </thead>
        <tbody id="k-bahan-body"></tbody>
        <tfoot>
          <tr class="k-total-row">
            <td colspan="2" style="text-align:right;">JUMLAH</td>
            <td class="k-readonly" data-k-sum="bahan">0</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div style="margin-top:.85rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;">
      <button type="button" class="k-btn k-btn-ghost k-btn-sm" id="k-add-bahan">+ Tambah bahan</button>
      <span class="k-hint" id="k-bahan-count"></span>
    </div>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok6', $routeParams),
    'nextLabel' => 'Simpan & lanjut ke Blok VIII',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var MAX = @json(\App\Models\KonstruksiSurveyResponse::MAX_BAHAN);
  var rows = @json(array_values($bahan));
  var body = document.getElementById('k-bahan-body');
  var api = null;

  function esc(s) {
    return String(s === null || s === undefined ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function render() {
    body.innerHTML = rows.map(function (row, i) {
      // The last available slot is the questionnaire's "Lainnya" catch-all.
      var isLast = i === MAX - 1;
      return '<tr>' +
        '<td class="k-no">' + (i + 1) + '</td>' +
        '<td><input class="k-input" data-k-field="blok7_bahan" data-k-path="' + i + '.nama"' +
          ' placeholder="' + (isLast ? 'Lainnya — gabungan bahan selain di atas' : 'Nama bahan/material') + '"' +
          ' value="' + esc(row.nama) + '"></td>' +
        '<td><input class="k-input k-num" inputmode="numeric" data-k-field="blok7_bahan"' +
          ' data-k-path="' + i + '.nilai" value="' + esc(row.nilai) + '"></td>' +
        '<td style="text-align:center;">' +
          '<button type="button" class="k-btn k-btn-ghost k-btn-sm" data-remove="' + i + '" title="Hapus">&times;</button>' +
        '</td>' +
      '</tr>';
    }).join('');
    document.getElementById('k-bahan-count').textContent = rows.length + ' dari maksimal ' + MAX + ' baris.';
    document.getElementById('k-add-bahan').disabled = rows.length >= MAX;
    if (api) api.recompute();
  }

  api = Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok7'])),
    csrf: @json(csrf_token()),
    models: { blok7_bahan: rows },
    recompute: function (models, h) {
      var total = 0;
      models.blok7_bahan.forEach(function (r) { total += h.num(r.nilai); });
      var cell = document.querySelector('[data-k-sum="bahan"]');
      if (cell) cell.textContent = h.fmt(total);
    }
  });

  document.getElementById('k-add-bahan').addEventListener('click', function () {
    if (rows.length >= MAX) return;
    rows.push({ nama: '', nilai: '' });
    render();
    api.save('blok7_bahan');
  });

  body.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-remove]');
    if (!btn) return;
    var i = parseInt(btn.getAttribute('data-remove'), 10);
    if (rows.length === 1) rows[0] = { nama: '', nilai: '' };
    else rows.splice(i, 1);
    render();
    api.save('blok7_bahan');
  });

  document.getElementById('k-form').addEventListener('submit', function (e) {
    var bad = api.models.blok7_bahan.findIndex(function (r) {
      return !String(r.nama || '').trim() || String(r.nilai || '').trim() === '';
    });
    if (bad !== -1) {
      e.preventDefault();
      api.status.show('Baris ' + (bad + 1) + ': nama bahan dan nilainya harus terisi. Hapus baris yang tidak dipakai.', 'error');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });

  render();
})();
</script>
@endpush
