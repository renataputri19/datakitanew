@extends('layouts.user-dashboard')

@section('title', 'Survei Konstruksi Triwulanan — Blok III: Kontrak & Realisasi Fisik')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/konstruksi.css') }}">
<style>
  /* The 16-column grid scrolls horizontally inside .k-scroll. */
  .k-wide th, .k-wide td { white-space: nowrap; }
  .k-wide .c-nama  { min-width: 17rem; white-space: normal; }
  .k-wide .c-lokasi{ min-width: 11rem; white-space: normal; }
  .k-wide .c-num   { width: 9rem; }
  .k-wide .c-pct   { width: 7rem; }
  .k-wide .c-small { width: 7rem; }
  .k-wide .c-date  { width: 7rem; }
  .k-wide tfoot td { font-weight: 700; background: var(--k-accent-soft); color: var(--k-accent); }
  /* The forecast column is the one respondents most often misread as actual. */
  .k-wide th.c-rencana, .k-wide td.c-rencana { background: rgba(59, 130, 246, .08); }
</style>
@endpush

@php
  use App\Models\KonstruksiSurveyResponse as K;
  $rows = $response->proyekTwRows();
  if ($rows === []) {
      $rows = [K::emptyProyekTwRow()];
  }

  // Kept in one place so the page's own validation asks for exactly what
  // isBlok3TwComplete() asks for.
  $numCols = array_merge(
      ['nilai_kontrak', 'pct_subkontrak'],
      array_keys(K::REALISASI_SLOTS),
      array_keys(K::UPAH_SLOTS),
      array_keys(K::UPAH_RATA_SLOTS)
  );
@endphp

@section('dashboard-content')
@include('survey.konstruksi.partials.page-open', [
  'title'    => 'Blok III — Nilai Kontrak & Realisasi Fisik Pekerjaan/Proyek',
  'subtitle' => 'Proyek yang dilaksanakan selama triwulan berjalan',
])

<form id="k-form" method="POST" action="{{ route('survey.konstruksi.blok3.save', $routeParams) }}" novalidate>
  @csrf
  <input type="hidden" name="data_proyek_tw" value="">

  <div class="k-card">
    <p class="k-card-title">Daftar Pekerjaan / Proyek Konstruksi</p>
    <p class="k-note">
      Cakup <b>semua</b> pekerjaan/proyek konstruksi yang dilaksanakan selama triwulan berjalan,
      termasuk pekerjaan yang belum selesai pada triwulan sebelumnya.
      Blok ini wajib memuat <b>minimal 1 proyek</b> karena Anda menjawab
      <b>Ada Proyek Konstruksi</b>; seluruh kolomnya wajib diisi.
      Maksimal {{ K::MAX_PROYEK_TW }} proyek.
      Nilai dalam <b>ribuan rupiah (000 Rp)</b>.
      Kolom <b>{{ $quarterLabels['realisasi_depan']['short'] ?? '' }}</b> adalah
      <b>rencana/perkiraan</b> realisasi fisik pada triwulan selanjutnya, bukan realisasi yang sudah terjadi.
    </p>

    <div class="k-scroll">
      <table class="k-table k-wide" id="k-proyek">
        <thead>
          <tr>
            <th rowspan="2" style="width:2.5rem;">No.<br>(1)</th>
            <th rowspan="2" class="c-nama">Nama Pekerjaan/Proyek &amp; KBLI<br>(2)</th>
            <th rowspan="2" class="c-lokasi">Lokasi Pekerjaan<br>(3)</th>
            <th rowspan="2" class="c-small">Sumber Dana<br>(4)</th>
            <th rowspan="2" class="c-num">Nilai Kontrak<br>(5)</th>
            <th rowspan="2" class="c-pct">% Disubkontrakkan<br>(6)</th>
            <th colspan="2">Bulan/Tahun Pelaksanaan</th>
            <th colspan="4">% Kumulatif Realisasi Fisik yang Diselesaikan s/d</th>
            <th colspan="2">Jumlah Upah Pekerja Tidak Tetap/Harian</th>
            <th colspan="2">Rata-rata Upah per Orang per Hari</th>
            <th rowspan="2" class="c-num">Nilai Konstruksi Triwulan Ini<br><small>(dihitung otomatis)</small></th>
            <th rowspan="2" style="width:3rem;"></th>
          </tr>
          <tr>
            <th class="c-date">Mulai (7)<br><small>mm/yyyy</small></th>
            <th class="c-date">Selesai (8)<br><small>mm/yyyy</small></th>
            <th class="c-pct">{{ $quarterLabels['realisasi_2lalu']['end'] ?? '' }}<br>(9)</th>
            <th class="c-pct">{{ $quarterLabels['realisasi_lalu']['end'] ?? '' }}<br>(10)</th>
            <th class="c-pct">{{ $quarterLabels['realisasi_ini']['end'] ?? '' }}<br>(11)</th>
            <th class="c-pct c-rencana">{{ $quarterLabels['realisasi_depan']['end'] ?? '' }}<br>(12) rencana</th>
            <th class="c-num">{{ $quarterLabels['upah_lalu']['range'] ?? '' }}<br>(13)</th>
            <th class="c-num">{{ $quarterLabels['upah_ini']['range'] ?? '' }}<br>(14)</th>
            <th class="c-num">{{ $quarterLabels['upah_rata_lalu']['range'] ?? '' }}<br>(15)</th>
            <th class="c-num">{{ $quarterLabels['upah_rata_ini']['range'] ?? '' }}<br>(16)</th>
          </tr>
        </thead>
        <tbody id="k-proyek-body"></tbody>
        <tfoot>
          <tr>
            <td colspan="4" style="text-align:right;">JUMLAH</td>
            <td class="k-readonly" data-k-sum="p.nilai_kontrak">0</td>
            <td colspan="7"></td>
            <td class="k-readonly" data-k-sum="p.upah_lalu">0</td>
            <td class="k-readonly" data-k-sum="p.upah_ini">0</td>
            <td colspan="2"></td>
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

  @include('survey.konstruksi.partials.actions', [
    'prevUrl'   => route('survey.konstruksi.blok2', $routeParams),
    'nextLabel' => 'Simpan & lanjut ke Penutup',
  ])
</form>

@include('survey.konstruksi.partials.page-close')
@endsection

@push('scripts')
<script src="{{ asset('js/konstruksi.js') }}"></script>
<script>
(function () {
  var MAX_ROWS    = @json(\App\Models\KonstruksiSurveyResponse::MAX_PROYEK_TW);
  var EMPTY_ROW   = @json(\App\Models\KonstruksiSurveyResponse::emptyProyekTwRow());
  var SUMBER_DANA = @json(\App\Models\KonstruksiSurveyResponse::SUMBER_DANA);
  var LABELS      = @json($quarterLabels);

  // The figure columns isBlok3TwComplete() requires, in questionnaire order.
  var NUM_COLS = @json($numCols);
  var NUM_LABELS = {
    nilai_kontrak:  'nilai kontrak (5)',
    pct_subkontrak: '% disubkontrakkan (6)',
    realisasi_2lalu: 'realisasi kumulatif (9)',
    realisasi_lalu:  'realisasi kumulatif (10)',
    realisasi_ini:   'realisasi kumulatif (11)',
    realisasi_depan: 'rencana realisasi (12)',
    upah_lalu:       'jumlah upah (13)',
    upah_ini:        'jumlah upah (14)',
    upah_rata_lalu:  'rata-rata upah (15)',
    upah_rata_ini:   'rata-rata upah (16)'
  };

  var rows = @json(array_values($rows));
  var body = document.getElementById('k-proyek-body');
  var api  = null;

  function esc(s) {
    return String(s === null || s === undefined ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function numInput(i, col, extra, cls) {
    return '<input class="k-input k-num" inputmode="decimal" ' + (extra || '') +
           ' data-k-field="data_proyek_tw" data-k-path="' + i + '.' + col + '"' +
           ' value="' + esc(rows[i][col]) + '">';
  }

  function textInput(i, col, placeholder) {
    return '<input class="k-input" data-k-field="data_proyek_tw" data-k-path="' + i + '.' + col + '"' +
           ' placeholder="' + esc(placeholder || '') + '" value="' + esc(rows[i][col]) + '">';
  }

  function danaSelect(i) {
    var html = '<select class="k-select" data-k-field="data_proyek_tw" data-k-path="' + i + '.sumber_dana">';
    html += '<option value="">—</option>';
    Object.keys(SUMBER_DANA).forEach(function (code) {
      html += '<option value="' + code + '"' + (String(rows[i].sumber_dana) === code ? ' selected' : '') +
              '>' + code + '. ' + esc(SUMBER_DANA[code]) + '</option>';
    });
    return html + '</select>';
  }

  function dateInput(i, col) {
    return '<input class="k-input" placeholder="mm/yyyy" maxlength="7"' +
           ' data-k-field="data_proyek_tw" data-k-path="' + i + '.' + col + '"' +
           ' value="' + esc(rows[i][col]) + '">';
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
      '<td class="c-num">' + numInput(i, 'nilai_kontrak') + '</td>' +
      '<td class="c-pct">' + numInput(i, 'pct_subkontrak', 'data-k-decimal') + '</td>' +
      '<td class="c-date">' + dateInput(i, 'mulai') + '</td>' +
      '<td class="c-date">' + dateInput(i, 'selesai') + '</td>' +
      '<td class="c-pct">' + numInput(i, 'realisasi_2lalu', 'data-k-decimal') + '</td>' +
      '<td class="c-pct">' + numInput(i, 'realisasi_lalu', 'data-k-decimal') + '</td>' +
      '<td class="c-pct">' + numInput(i, 'realisasi_ini', 'data-k-decimal') + '</td>' +
      '<td class="c-pct c-rencana">' + numInput(i, 'realisasi_depan', 'data-k-decimal') + '</td>' +
      '<td class="c-num">' + numInput(i, 'upah_lalu') + '</td>' +
      '<td class="c-num">' + numInput(i, 'upah_ini') + '</td>' +
      '<td class="c-num">' + numInput(i, 'upah_rata_lalu') + '</td>' +
      '<td class="c-num">' + numInput(i, 'upah_rata_ini') + '</td>' +
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

  /** Mirrors KonstruksiSurveyResponse::nilaiKonstruksiTw(). */
  function nilaiKonstruksi(row, h) {
    var netto = h.num(row.nilai_kontrak) * Math.max(0, 1 - (h.num(row.pct_subkontrak) / 100));
    var v = netto * (h.num(row.realisasi_ini) - h.num(row.realisasi_lalu)) / 100;
    return v > 0 ? v : 0;
  }

  api = Konstruksi.bind({
    form: '#k-form',
    autoSaveUrl: @json(route('survey.konstruksi.autosave', $routeParams + ['section' => 'blok3'])),
    csrf: @json(csrf_token()),
    models: { data_proyek_tw: rows },
    recompute: function (models, h) {
      var t = { nilai_kontrak: 0, upah_lalu: 0, upah_ini: 0, nilai_konstruksi: 0 };

      models.data_proyek_tw.forEach(function (row, i) {
        var nk = nilaiKonstruksi(row, h);
        var cell = document.querySelector('[data-k-nk="' + i + '"]');
        if (cell) cell.textContent = h.fmt(nk);
        t.nilai_kontrak += h.num(row.nilai_kontrak);
        t.upah_lalu     += h.num(row.upah_lalu);
        t.upah_ini      += h.num(row.upah_ini);
        t.nilai_konstruksi += nk;
      });

      Object.keys(t).forEach(function (k) {
        var el = document.querySelector('[data-k-sum="p.' + k + '"]');
        if (el) el.textContent = h.fmt(t[k]);
      });
    }
  });

  document.getElementById('k-add-row').addEventListener('click', function () {
    if (rows.length >= MAX_ROWS) return;
    rows.push(JSON.parse(JSON.stringify(EMPTY_ROW)));
    render();
    api.save('data_proyek_tw');
  });

  body.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-remove]');
    if (!btn) return;
    var i = parseInt(btn.getAttribute('data-remove'), 10);
    if (rows.length === 1) rows[0] = JSON.parse(JSON.stringify(EMPTY_ROW));
    else rows.splice(i, 1);
    render();
    api.save('data_proyek_tw');
  });

  // mm/yyyy is easier to get wrong than to type, so nudge the slash in.
  body.addEventListener('input', function (e) {
    var path = e.target.getAttribute && e.target.getAttribute('data-k-path');
    if (!path || !/\.(mulai|selesai)$/.test(path)) return;
    var digits = e.target.value.replace(/\D/g, '').slice(0, 6);
    e.target.value = digits.length > 2 ? digits.slice(0, 2) + '/' + digits.slice(2) : digits;
    Konstruksi.setPath(api.models.data_proyek_tw, path, e.target.value);
  });

  /** Does this row carry any answer at all? Mirrors proyekTwRows()'s filter. */
  function rowTouched(row) {
    return Object.keys(row).some(function (k) { return String(row[k] === null || row[k] === undefined ? '' : row[k]).trim() !== ''; });
  }

  document.getElementById('k-form').addEventListener('submit', function (e) {
    var problems = [];

    // The block is reached only when the preamble said there ARE projects, so
    // at least one project has to be described. The grid always renders a blank
    // row, and an all-blank grid is dropped server-side — without this the
    // responden would just get the generic "Blok III belum lengkap" bounce.
    if (!api.models.data_proyek_tw.some(rowTouched)) {
      e.preventDefault();
      api.status.show('Isikan minimal satu pekerjaan/proyek konstruksi. Bila perusahaan tidak mengerjakan proyek pada triwulan ini, ubah jawaban "Keberadaan Proyek" di Updating Status Perusahaan.', 'error');
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }

    api.models.data_proyek_tw.forEach(function (row, i) {
      var no = i + 1;
      if (!String(row.nama_pekerjaan || '').trim()) problems.push('Proyek ' + no + ': nama pekerjaan belum diisi.');
      if (!row.sumber_dana) problems.push('Proyek ' + no + ': sumber dana belum dipilih.');
      ['mulai', 'selesai'].forEach(function (f) {
        if (!/^(0[1-9]|1[0-2])\/\d{4}$/.test(String(row[f] || ''))) {
          problems.push('Proyek ' + no + ': ' + f + ' harus dalam format mm/yyyy.');
        }
      });
      // Every figure column is required — the same list isBlok3TwComplete()
      // runs through, so the page and the server agree on "lengkap".
      NUM_COLS.forEach(function (col) {
        if (String(row[col] === null || row[col] === undefined ? '' : row[col]).trim() === '') {
          problems.push('Proyek ' + no + ': ' + (NUM_LABELS[col] || col) + ' belum diisi.');
        }
      });
      // The four realisation columns are cumulative, so they can only rise.
      var seq = ['realisasi_2lalu', 'realisasi_lalu', 'realisasi_ini', 'realisasi_depan'];
      for (var s = 1; s < seq.length; s++) {
        var prevV = Konstruksi.num(row[seq[s - 1]]);
        var curV  = Konstruksi.num(row[seq[s]]);
        if (String(row[seq[s]]).trim() !== '' && curV < prevV) {
          problems.push('Proyek ' + no + ': realisasi ' + (LABELS[seq[s]] ? LABELS[seq[s]].short : seq[s]) +
                        ' tidak boleh lebih kecil daripada ' + (LABELS[seq[s - 1]] ? LABELS[seq[s - 1]].short : seq[s - 1]) +
                        ' — angkanya kumulatif.');
          break;
        }
      }
      if (Konstruksi.num(row.pct_subkontrak) > 100) {
        problems.push('Proyek ' + no + ': persentase disubkontrakkan tidak boleh lebih dari 100%.');
      }
    });

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
