@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi — Blok VI: Pekerjaan Konstruksi')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
<style>
  /* Column widths for the 17-column project grid, which is far wider than the
     other blok tables and scrolls horizontally inside .k-scroll. */
  .k-wide th, .k-wide td { white-space: nowrap; }
  .k-wide .c-nama  { min-width: 17rem; white-space: normal; }
  .k-wide .c-lokasi{ min-width: 11rem; white-space: normal; }
  .k-wide .c-num   { width: 9rem; }
  .k-wide .c-pct   { width: 6.5rem; }
  .k-wide .c-small { width: 6.5rem; }
  .k-wide .c-date  { width: 7rem; }
  .k-wide tfoot td { font-weight: 700; background: var(--k-accent-soft); color: var(--k-accent); }
  .k-rowhead {
    display: flex; align-items: center; justify-content: space-between; gap: .5rem;
    font-size: .72rem; color: var(--k-muted); margin-bottom: .3rem;
  }
</style>
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $rows = $response->proyekRows();
  if ($rows === []) {
      $rows = [K::emptyProyekRow()];
  }
  $sumberModal = is_array($response->blok6_sumber_modal) ? $response->blok6_sumber_modal : [];
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok VI — Pendapatan & Pengeluaran Pekerjaan Konstruksi',
  'subtitle' => 'Seluruh pekerjaan/proyek yang dikerjakan selama tahun ' . $tahun,
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok6.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="data_proyek" value="">
  <input type="hidden" name="blok6_sumber_modal" value="">

  <div class="k-card">
    <p class="k-card-title">1. Daftar Pekerjaan / Proyek Konstruksi</p>
    <p class="k-note">
      Tuliskan <b>semua</b> pekerjaan/proyek konstruksi yang dikerjakan selama tahun {{ $tahun }},
      baik yang dimulai pada tahun sebelumnya maupun yang dimulai pada tahun {{ $tahun }}.
      Blok ini wajib memuat <b>minimal 1 proyek</b> karena Anda menjawab
      <b>Ada Proyek Konstruksi</b>.
      Maksimal {{ K::MAX_PROYEK }} proyek. Nilai dalam <b>ribuan rupiah (000 Rp)</b>.
      Kolom <b>(17) Nilai Konstruksi yang Diselesaikan</b> dihitung otomatis:
      <code>{[(7) − (8)] × (12 − 11) ÷ 100} + (14)</code>.
    </p>

    <div class="k-scroll">
      <table class="k-table k-wide" id="k-proyek">
        <thead>
          <tr>
            <th rowspan="2" style="width:2.5rem;">No.<br>(1)</th>
            <th rowspan="2" class="c-nama">Nama Pekerjaan/Proyek &amp; KBLI<br>(2)</th>
            <th rowspan="2" class="c-lokasi">Lokasi Pekerjaan<br>(3)</th>
            <th rowspan="2" class="c-small">Sumber Dana<br>(4)</th>
            <th rowspan="2" class="c-small">Volume<br>(5)</th>
            <th rowspan="2" class="c-small">Satuan<br>(6)</th>
            <th rowspan="2" class="c-num">Nilai Kontrak<br>(7)</th>
            <th rowspan="2" class="c-num">Nilai Disubkontrakkan<br>(8)</th>
            <th colspan="2">Bulan/Tahun Pelaksanaan</th>
            <th colspan="2">% Kumulatif Realisasi Fisik</th>
            <th colspan="2">Biaya Bahan/Material</th>
            <th rowspan="2" class="c-num">Upah Pekerja Tidak Tetap<br>(15)</th>
            <th rowspan="2" class="c-num">Rata-rata Upah/Orang/Hari<br>(16)</th>
            <th rowspan="2" class="c-num">Nilai Konstruksi Diselesaikan<br>(17)</th>
            <th rowspan="2" style="width:3rem;"></th>
          </tr>
          <tr>
            <th class="c-date">Mulai (9)<br><small>mm/yyyy</small></th>
            <th class="c-date">Selesai (10)<br><small>mm/yyyy</small></th>
            <th class="c-pct">s/d 31/12/{{ substr((string) ($tahun - 1), -2) }} (11)</th>
            <th class="c-pct">s/d 31/12/{{ substr((string) $tahun, -2) }} (12)</th>
            <th class="c-num">Disediakan Perusahaan (13)</th>
            <th class="c-num">Disediakan Pemilik (14)</th>
          </tr>
        </thead>
        <tbody id="k-proyek-body"></tbody>
        <tfoot>
          <tr>
            <td colspan="6" style="text-align:right;">JUMLAH [ 1 + 2 + … ]</td>
            <td class="k-readonly" data-k-sum="p.nilai_kontrak">0</td>
            <td></td>
            <td colspan="4"></td>
            <td class="k-readonly" data-k-sum="p.bahan_perusahaan">0</td>
            <td class="k-readonly" data-k-sum="p.bahan_pemilik">0</td>
            <td class="k-readonly" data-k-sum="p.upah_harian">0</td>
            <td></td>
            <td class="k-readonly" data-k-sum="p.nilai_konstruksi">0</td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div style="margin-top:.85rem;display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;">
      <button type="button" class="k-btn k-btn-ghost k-btn-sm" id="k-add-row">+ Tambah proyek</button>
      <span class="k-hint" id="k-row-count"></span>
    </div>
    <p class="k-hint">
      Sumber dana utama:
      @foreach(K::SUMBER_DANA as $code => $label) {{ $code }}. {{ $label }}@if(!$loop->last) · @endif @endforeach
    </p>
  </div>

  <div class="k-card">
    <p class="k-card-title">2 &amp; 3. Rata-rata Persentase</p>
    <div class="k-grid-2">
      <div>
        <label class="k-label">
          2. Rata-rata persentase penggunaan bahan/material terhadap nilai konstruksi
          yang diselesaikan selama tahun {{ $tahun }} (%) <span class="k-req">*</span>
        </label>
        <input name="pct_bahan" class="k-input k-num" inputmode="decimal"
               value="{{ old('pct_bahan', $response->pct_bahan) }}">
        <div class="k-err" data-field="pct_bahan"></div>
      </div>
      <div>
        <label class="k-label">
          3. Rata-rata persentase upah pekerja tidak tetap/harian terhadap nilai konstruksi
          yang diselesaikan selama tahun {{ $tahun }} (%) <span class="k-req">*</span>
        </label>
        <input name="pct_upah" class="k-input k-num" inputmode="decimal"
               value="{{ old('pct_upah', $response->pct_upah) }}">
        <div class="k-err" data-field="pct_upah"></div>
      </div>
    </div>
  </div>

  <div class="k-card">
    <p class="k-card-title">4. Sumber Modal Pekerjaan/Proyek Tahun {{ $tahun }}</p>
    <p class="k-note">Jumlah ketiga rincian harus tepat <b>100 %</b>. Isi <b>0</b> untuk sumber yang tidak digunakan.</p>

    @include('survey.konstruksi.partials.figure-table', [
      'field'      => 'blok6_sumber_modal',
      'rows'       => K::MODAL_PROYEK,
      'values'     => $sumberModal,
      'valueHead'  => 'Persentase (%)',
      'totalLabel' => 'TOTAL',
      'sumKey'     => 'modal_proyek',
      'decimal'    => true,
    ])
    <p class="k-err" id="k-modal-warn" style="margin-top:.6rem;"></p>
  </div>

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok5', $routeParams),
    'nextLabel' => 'Simpan & lanjut ke Blok VII',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var MAX_ROWS   = @json(\App\Models\KonstruksiSurveyResponse::MAX_PROYEK);
  var EMPTY_ROW  = @json(\App\Models\KonstruksiSurveyResponse::emptyProyekRow());
  var SUMBER_DANA= @json(\App\Models\KonstruksiSurveyResponse::SUMBER_DANA);
  var MODAL_KEYS = @json(array_keys(\App\Models\KonstruksiSurveyResponse::MODAL_PROYEK));
  var TAHUN      = @json((int) $tahun);

  var rows = @json(array_values($rows));
  var body = document.getElementById('k-proyek-body');
  var api  = null;

  function esc(s) {
    return String(s === null || s === undefined ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function numInput(i, col, extra) {
    return '<input class="k-input k-num" inputmode="decimal" ' + (extra || '') +
           ' data-k-field="data_proyek" data-k-path="' + i + '.' + col + '"' +
           ' value="' + esc(rows[i][col]) + '">';
  }

  function textInput(i, col, placeholder) {
    return '<input class="k-input" data-k-field="data_proyek" data-k-path="' + i + '.' + col + '"' +
           ' placeholder="' + esc(placeholder || '') + '" value="' + esc(rows[i][col]) + '">';
  }

  function danaSelect(i) {
    var html = '<select class="k-select" data-k-field="data_proyek" data-k-path="' + i + '.sumber_dana">';
    html += '<option value="">—</option>';
    Object.keys(SUMBER_DANA).forEach(function (code) {
      html += '<option value="' + code + '"' + (String(rows[i].sumber_dana) === code ? ' selected' : '') +
              '>' + code + '. ' + esc(SUMBER_DANA[code]) + '</option>';
    });
    return html + '</select>';
  }

  function renderRow(i) {
    return '<tr data-row="' + i + '">' +
      '<td class="k-no">' + (i + 1) + '</td>' +
      '<td class="c-nama">' +
        textInput(i, 'nama_pekerjaan', 'Nama & jenis pekerjaan, jelas dan rinci') +
        '<div style="margin-top:.3rem;">' + textInput(i, 'kbli', 'Kode KBLI 2025') + '</div>' +
      '</td>' +
      '<td class="c-lokasi">' +
        textInput(i, 'provinsi_lokasi', 'Provinsi') +
        '<div style="margin-top:.3rem;">' + textInput(i, 'kabkota_lokasi', 'Kabupaten/Kota') + '</div>' +
        '<div style="margin-top:.3rem;">' + textInput(i, 'kode_kabkota', 'Kode Kab/Kota') + '</div>' +
      '</td>' +
      '<td class="c-small">' + danaSelect(i) + '</td>' +
      '<td class="c-small">' + numInput(i, 'volume', 'data-k-decimal') + '</td>' +
      '<td class="c-small">' + textInput(i, 'satuan', 'm, m2, km') + '</td>' +
      '<td class="c-num">' + numInput(i, 'nilai_kontrak') + '</td>' +
      '<td class="c-num">' + numInput(i, 'nilai_subkontrak') + '</td>' +
      '<td class="c-date"><input class="k-input" placeholder="mm/yyyy" maxlength="7"' +
        ' data-k-field="data_proyek" data-k-path="' + i + '.mulai" value="' + esc(rows[i].mulai) + '"></td>' +
      '<td class="c-date"><input class="k-input" placeholder="mm/yyyy" maxlength="7"' +
        ' data-k-field="data_proyek" data-k-path="' + i + '.selesai" value="' + esc(rows[i].selesai) + '"></td>' +
      '<td class="c-pct">' + numInput(i, 'realisasi_awal', 'data-k-decimal') + '</td>' +
      '<td class="c-pct">' + numInput(i, 'realisasi_akhir', 'data-k-decimal') + '</td>' +
      '<td class="c-num">' + numInput(i, 'bahan_perusahaan') + '</td>' +
      '<td class="c-num">' + numInput(i, 'bahan_pemilik') + '</td>' +
      '<td class="c-num">' + numInput(i, 'upah_harian') + '</td>' +
      '<td class="c-num">' + numInput(i, 'upah_rata_rata') + '</td>' +
      '<td class="k-readonly" data-k-nk="' + i + '">0</td>' +
      '<td style="text-align:center;">' +
        '<button type="button" class="k-btn k-btn-ghost k-btn-sm" data-remove="' + i + '" title="Hapus proyek">&times;</button>' +
      '</td>' +
    '</tr>';
  }

  function render() {
    body.innerHTML = rows.map(function (_, i) { return renderRow(i); }).join('');
    document.getElementById('k-row-count').textContent =
      rows.length + ' dari maksimal ' + MAX_ROWS + ' proyek.';
    document.getElementById('k-add-row').disabled = rows.length >= MAX_ROWS;
    if (api) api.recompute();
  }

  /** Column (17), mirroring KonstruksiSurveyResponse::nilaiKonstruksi(). */
  function nilaiKonstruksi(row, h) {
    var v = ((h.num(row.nilai_kontrak) - h.num(row.nilai_subkontrak)) *
             (h.num(row.realisasi_akhir) - h.num(row.realisasi_awal)) / 100) +
            h.num(row.bahan_pemilik);
    return v > 0 ? v : 0;
  }

  api = Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok6'])),
    csrf: @json(csrf_token()),
    models: {
      data_proyek: rows,
      blok6_sumber_modal: @json($sumberModal ?: (object) [])
    },
    recompute: function (models, h) {
      var t = { nilai_kontrak: 0, bahan_perusahaan: 0, bahan_pemilik: 0, upah_harian: 0, nilai_konstruksi: 0 };

      models.data_proyek.forEach(function (row, i) {
        var nk = nilaiKonstruksi(row, h);
        var cell = document.querySelector('[data-k-nk="' + i + '"]');
        if (cell) cell.textContent = h.fmt(nk);
        t.nilai_kontrak    += h.num(row.nilai_kontrak);
        t.bahan_perusahaan += h.num(row.bahan_perusahaan);
        t.bahan_pemilik    += h.num(row.bahan_pemilik);
        t.upah_harian      += h.num(row.upah_harian);
        t.nilai_konstruksi += nk;
      });

      Object.keys(t).forEach(function (k) {
        var el = document.querySelector('[data-k-sum="p.' + k + '"]');
        if (el) el.textContent = h.fmt(t[k]);
      });

      var modal = 0;
      MODAL_KEYS.forEach(function (k) { modal += h.num(models.blok6_sumber_modal[k]); });
      var mc = document.querySelector('[data-k-sum="modal_proyek"]');
      if (mc) mc.textContent = h.fmt(modal, true) + ' %';
      document.getElementById('k-modal-warn').textContent =
        Math.abs(modal - 100) < 0.001 ? '' :
        'Jumlah persentase sumber modal saat ini ' + h.fmt(modal, true) + ' % — harus tepat 100 %.';
    }
  });

  Konstruksi.bindFields({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok6'])),
    csrf: @json(csrf_token())
  });

  document.getElementById('k-add-row').addEventListener('click', function () {
    if (rows.length >= MAX_ROWS) return;
    rows.push(JSON.parse(JSON.stringify(EMPTY_ROW)));
    render();
    api.save('data_proyek');
  });

  body.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-remove]');
    if (!btn) return;
    var i = parseInt(btn.getAttribute('data-remove'), 10);
    if (rows.length === 1) {
      rows[0] = JSON.parse(JSON.stringify(EMPTY_ROW));
    } else {
      rows.splice(i, 1);
    }
    render();
    api.save('data_proyek');
  });

  // mm/yyyy is easier to get wrong than to type, so nudge the slashes in.
  body.addEventListener('input', function (e) {
    var path = e.target.getAttribute && e.target.getAttribute('data-k-path');
    if (!path || !/\.(mulai|selesai)$/.test(path)) return;
    var digits = e.target.value.replace(/\D/g, '').slice(0, 6);
    e.target.value = digits.length > 2 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits;
    Konstruksi.setPath(api.models.data_proyek, path, e.target.value);
  });

  /** Does this row carry any answer at all? Mirrors proyekRows()'s filter. */
  function rowTouched(row) {
    return Object.keys(row).some(function (k) { return String(row[k] === null || row[k] === undefined ? '' : row[k]).trim() !== ''; });
  }

  document.getElementById('k-form').addEventListener('submit', function (e) {
    var problems = [];

    // Blok VI is only reached when the preamble said there ARE projects, so at
    // least one has to be described. The grid always renders a blank row, and an
    // all-blank grid is dropped server-side — without this the responden would
    // just get the generic "Blok VI belum lengkap" bounce.
    if (!api.models.data_proyek.some(rowTouched)) {
      e.preventDefault();
      api.status.show('Isikan minimal satu pekerjaan/proyek konstruksi. Bila perusahaan tidak mengerjakan proyek pada tahun ini, ubah jawaban "Keberadaan Proyek" di Updating Status Perusahaan.', 'error');
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    api.models.data_proyek.forEach(function (row, i) {
      var no = i + 1;
      if (!String(row.nama_pekerjaan || '').trim()) problems.push('Proyek ' + no + ': nama pekerjaan belum diisi.');
      if (!row.sumber_dana) problems.push('Proyek ' + no + ': sumber dana belum dipilih.');
      ['mulai', 'selesai'].forEach(function (f) {
        if (!/^(0[1-9]|1[0-2])\/\d{4}$/.test(String(row[f] || ''))) {
          problems.push('Proyek ' + no + ': ' + f + ' harus dalam format mm/yyyy.');
        }
      });
      if (Konstruksi.num(row.realisasi_akhir) < Konstruksi.num(row.realisasi_awal)) {
        problems.push('Proyek ' + no + ': realisasi s/d 31/12/' + TAHUN +
                      ' tidak boleh lebih kecil daripada s/d 31/12/' + (TAHUN - 1) + '.');
      }
    });

    var modal = 0;
    MODAL_KEYS.forEach(function (k) { modal += Konstruksi.num(api.models.blok6_sumber_modal[k]); });
    if (Math.abs(modal - 100) > 0.001) problems.push('Persentase sumber modal proyek harus tepat 100 %.');

    if (problems.length) {
      e.preventDefault();
      api.status.show(problems[0] + (problems.length > 1 ? ' (+' + (problems.length - 1) + ' lainnya)' : ''), 'error');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });

  render();
})();
</script>
@endpush
