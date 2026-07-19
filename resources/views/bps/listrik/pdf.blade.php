@php
    use App\Models\ListrikSurveyResponse;

    $cats = ListrikSurveyResponse::CATEGORIES;
    $fmt  = fn ($v) => number_format((float) $v, 0, ',', '.');

    $namaPerusahaan = $response->nama_perusahaan ?: '—';
    $lokasi = collect([
        $response->kelurahan_desa, $response->kecamatan,
        $response->kabupaten_kota, $response->provinsi,
    ])->filter()->join(', ') ?: '—';

    $grandKwh = collect($quarters)->sum(fn ($q) => $q['totals']['kwh']);
    $grandRp  = collect($quarters)->sum(fn ($q) => $q['totals']['rp']);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  /* Page margins must come from @page: padding on a wrapper only indents the
     start of the flow, so every page after a page-break starts flush against
     the top edge. Two dompdf traps to keep in mind when editing this:
       1. a universal `* { margin: 0 }` also matches the page box and silently
          wipes these margins out — reset margins per element instead;
       2. dompdf honours a single unselectored @page rule only. */
  @page { margin: 10mm 10mm 12mm 10mm; }

  /* NB: html/body are deliberately absent — margin on either also resets the
     page box in dompdf, which is what wipes out @page above. */
  * { box-sizing: border-box; }
  div, table, thead, tbody, tr, th, td, p { margin: 0; padding: 0; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 8pt; color: #111827; }
  .page { padding: 0; }

  .header {
    background: #fffbeb;
    border: 2px solid #f59e0b;
    border-radius: 5px;
    padding: 8px 12px;
    margin-bottom: 12px;
    text-align: center;
  }
  .header-org  { font-size: 7pt; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
  .header-main { font-size: 14pt; font-weight: bold; color: #b45309; margin: 3px 0 1px; }
  .header-sub  { font-size: 8.5pt; color: #374151; }

  .section-title {
    font-size: 9pt; font-weight: bold; color: #b45309;
    text-transform: uppercase; letter-spacing: .05em;
    border-bottom: 2px solid #fde68a;
    padding-bottom: 4px; margin: 0 0 8px;
  }

  .kv { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  .kv td { padding: 3px 6px; border: 1px solid #e5e7eb; font-size: 8pt; vertical-align: top; }
  .kv .lbl { color: #6b7280; width: 17%; background: #f9fafb; }
  .kv .val { font-weight: bold; color: #111827; width: 33%; }

  /* ── Blok II grid ────────────────────────────────────────────────────────── */
  .qhead {
    background: #fef3c7;
    border: 1px solid #f59e0b;
    border-radius: 4px;
    padding: 5px 9px;
    margin-bottom: 6px;
  }
  .qhead-title { font-size: 10pt; font-weight: bold; color: #92400e; }
  .qhead-sum   { font-size: 7.5pt; color: #78350f; }

  .month-label {
    font-size: 8pt; font-weight: bold; color: #1e40af;
    margin: 7px 0 2px;
  }

  .grid { width: 100%; border-collapse: collapse; font-size: 6.8pt; }
  .grid th, .grid td { border: 1px solid #d1d5db; padding: 2px 3px; }
  .grid thead th {
    background: #eff6ff; color: #1e3a8a; font-weight: bold; text-align: center;
  }
  .grid td { text-align: right; }
  .grid td.wil { text-align: left; font-weight: bold; }
  .grid tr.total td { background: #f3f4f6; font-weight: bold; }
  .grid tr.qtotal td { background: #d1fae5; font-weight: bold; color: #065f46; }
  .empty { font-size: 7pt; color: #9ca3af; font-style: italic; padding: 2px 0; }

  .page-break { page-break-before: always; }

  .notice {
    background: #fefce8;
    border: 1px solid #fde68a;
    border-radius: 4px;
    padding: 6px 10px;
    font-size: 7pt; color: #78350f;
    margin-top: 10px;
  }
  .footer {
    text-align: center; font-size: 6.5pt; color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    padding-top: 5px; margin-top: 10px;
  }
</style>
</head>
<body>
<div class="page">

{{-- ══ HEADER ══════════════════════════════════════════════════════════════ --}}
<div class="header">
  <div class="header-org">Badan Pusat Statistik &mdash; Republik Indonesia</div>
  <div class="header-main">DATA SURVEI LISTRIK</div>
  <div class="header-sub">Produksi &amp; Nilai Produksi Listrik Bulanan &mdash; Rekap per Triwulan</div>
</div>

{{-- ══ BLOK I ═══════════════════════════════════════════════════════════════ --}}
<div class="section-title">Blok I &mdash; Identitas &amp; Lokasi</div>
<table class="kv">
  <tr>
    <td class="lbl">Nama Perusahaan</td><td class="val">{{ $namaPerusahaan }}</td>
    <td class="lbl">Nama Komersial</td><td class="val">{{ $response->nama_komersial ?: '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Alamat</td><td class="val">{{ $response->alamat_perusahaan ?: '—' }}</td>
    <td class="lbl">RT / RW</td><td class="val">{{ ($response->rt ?: '—') . ' / ' . ($response->rw ?: '—') }}</td>
  </tr>
  <tr>
    <td class="lbl">Lokasi</td><td class="val">{{ $lokasi }}</td>
    <td class="lbl">Kode Pos</td><td class="val">{{ $response->kode_pos ?: '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Telepon / HP</td><td class="val">{{ ($response->nomor_telepon ?: '—') . ' / ' . ($response->nomor_hp ?: '—') }}</td>
    <td class="lbl">Email Perusahaan</td><td class="val">{{ $response->email_perusahaan ?: '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Jenis Pembangkit</td><td class="val">{{ $response->jenis_pembangkit ?: '—' }}</td>
    <td class="lbl">Daya Terpasang</td>
    <td class="val">{{ $response->daya_terpasang_kw ? $fmt($response->daya_terpasang_kw) . ' kW' : '—' }}</td>
  </tr>
  <tr>
    <td class="lbl">Nama Pengusaha</td><td class="val">{{ $response->nama_pengusaha ?: '—' }}</td>
    <td class="lbl">Jenis Kelamin / Umur</td>
    <td class="val">
      {{ $response->jenis_kelamin == 1 ? 'Laki-laki' : ($response->jenis_kelamin == 2 ? 'Perempuan' : '—') }}
      / {{ $response->umur ? $response->umur . ' th' : '—' }}
    </td>
  </tr>
  <tr>
    <td class="lbl">NIK</td><td class="val">{{ $response->nik ?: '—' }}</td>
    <td class="lbl">Pengguna Sistem</td>
    <td class="val">{{ $user->name ?? '—' }} ({{ $user->email ?? '—' }})</td>
  </tr>
  <tr>
    <td class="lbl">Status Survei</td>
    <td class="val">{{ $response->is_completed ? 'Selesai' : 'Dalam Proses (' . $response->completionPercent() . '%)' }}</td>
    <td class="lbl">Terakhir Disimpan</td><td class="val">{{ $completedAt }}</td>
  </tr>
</table>

{{-- ══ BLOK II — one section per quarter ════════════════════════════════════ --}}
@foreach($quarters as $qi => $q)
<div class="{{ $qi > 0 ? 'page-break' : '' }}">
  @if($qi === 0)
  <div class="section-title">Blok II &mdash; Produksi &amp; Nilai Produksi Listrik Bulanan</div>
  @endif

  <div class="qhead">
    <div class="qhead-title">{{ $q['label'] }}</div>
    <div class="qhead-sum">
      Total triwulan: {{ $fmt($q['totals']['kwh']) }} kWh &nbsp;|&nbsp; Rp {{ $fmt($q['totals']['rp']) }}
    </div>
  </div>

  @foreach($q['months'] as $m)
    <div class="month-label">{{ $m['label'] }}</div>
    @if(count($m['rows']) === 0)
      <div class="empty">Belum ada data untuk bulan ini.</div>
    @else
    <table class="grid">
      <thead>
        <tr>
          <th rowspan="2" style="width:13%;">Wilayah Tujuan</th>
          @foreach($cats as $label)
          <th colspan="2">{{ $label }}</th>
          @endforeach
          <th colspan="2">Jumlah</th>
        </tr>
        <tr>
          @foreach($cats as $label)
          <th>KWH</th><th>Rp</th>
          @endforeach
          <th>KWH</th><th>Rp</th>
        </tr>
      </thead>
      <tbody>
        @foreach($m['rows'] as $row)
        @php
            $rowKwh = collect($row['cells'])->sum('kwh');
            $rowRp  = collect($row['cells'])->sum('rp');
        @endphp
        <tr>
          <td class="wil">{{ $row['wilayah'] }}</td>
          @foreach(array_keys($cats) as $cat)
          <td>{{ $fmt($row['cells'][$cat]['kwh']) }}</td>
          <td>{{ $fmt($row['cells'][$cat]['rp']) }}</td>
          @endforeach
          <td>{{ $fmt($rowKwh) }}</td>
          <td>{{ $fmt($rowRp) }}</td>
        </tr>
        @endforeach
        <tr class="total">
          <td class="wil">Total {{ $m['label'] }}</td>
          @foreach(array_keys($cats) as $cat)
          <td>{{ $fmt($m['totals'][$cat]['kwh']) }}</td>
          <td>{{ $fmt($m['totals'][$cat]['rp']) }}</td>
          @endforeach
          <td>{{ $fmt($m['totals']['kwh']) }}</td>
          <td>{{ $fmt($m['totals']['rp']) }}</td>
        </tr>
      </tbody>
    </table>
    @endif
  @endforeach

  {{-- Quarter subtotal --}}
  <div class="month-label">Subtotal {{ $q['label'] }}</div>
  <table class="grid">
    <thead>
      <tr>
        <th rowspan="2" style="width:13%;">Periode</th>
        @foreach($cats as $label)
        <th colspan="2">{{ $label }}</th>
        @endforeach
        <th colspan="2">Jumlah</th>
      </tr>
      <tr>
        @foreach($cats as $label)
        <th>KWH</th><th>Rp</th>
        @endforeach
        <th>KWH</th><th>Rp</th>
      </tr>
    </thead>
    <tbody>
      <tr class="qtotal">
        <td class="wil">{{ $q['label'] }}</td>
        @foreach(array_keys($cats) as $cat)
        <td>{{ $fmt($q['totals'][$cat]['kwh']) }}</td>
        <td>{{ $fmt($q['totals'][$cat]['rp']) }}</td>
        @endforeach
        <td>{{ $fmt($q['totals']['kwh']) }}</td>
        <td>{{ $fmt($q['totals']['rp']) }}</td>
      </tr>
    </tbody>
  </table>
</div>
@endforeach

{{-- ══ REKAP & BLOK III ═════════════════════════════════════════════════════ --}}
<div class="page-break">
  <div class="section-title">Rekapitulasi Seluruh Periode</div>
  <table class="grid">
    <thead>
      <tr>
        <th style="width:20%;">Triwulan</th>
        <th>Total KWH</th>
        <th>Total Nilai Produksi (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($quarters as $q)
      <tr>
        <td class="wil">{{ $q['label'] }}</td>
        <td>{{ $fmt($q['totals']['kwh']) }}</td>
        <td>{{ $fmt($q['totals']['rp']) }}</td>
      </tr>
      @endforeach
      <tr class="qtotal">
        <td class="wil">TOTAL KESELURUHAN</td>
        <td>{{ $fmt($grandKwh) }}</td>
        <td>{{ $fmt($grandRp) }}</td>
      </tr>
    </tbody>
  </table>

  <div class="section-title" style="margin-top:14px;">Blok III &mdash; Catatan</div>
  <table class="kv">
    <tr>
      <td class="val" colspan="4" style="width:100%; font-weight:normal;">{{ $response->catatan ?: '—' }}</td>
    </tr>
  </table>

  <div class="notice">
    <strong>Catatan:</strong> Dokumen ini berisi data hasil pengisian Survei Listrik dan diperuntukkan bagi
    petugas BPS. Kerahasiaan data dijamin berdasarkan UU No. 16 Tahun 1997 tentang Statistik, Pasal 21.
  </div>

  <div class="footer">
    Digenerate otomatis oleh sistem survei online DataKita &nbsp;|&nbsp;
    Survei Listrik &mdash; Badan Pusat Statistik RI &nbsp;|&nbsp;
    Dicetak: {{ now()->format('d/m/Y H:i:s') }}
  </div>
</div>

</div>
</body>
</html>
