@php
    use App\Support\SibstrFormat as F;

    $companyName    = $surveyResponse->nama_perusahaan ?: '—';
    $submissionAt   = optional($surveyResponse->updated_at)->format('d M Y, H:i') ?? '—';
    $surveyTahun    = $surveyResponse->tahun ?? 2025;
    $isAnnualPdf    = ((int) ($surveyResponse->triwulan ?? 0)) === 0;
    $finishStatus   = $surveyResponse->annual_survey_status ?? null;
    $periodLabel    = $isAnnualPdf
                        ? 'Tahunan ' . $surveyTahun
                        : \App\Models\SurveyResponse::triwulanLabel((int) $surveyResponse->triwulan) . ' ' . $surveyTahun;
    $isFinished     = $isAnnualPdf ? ($finishStatus === 'FINISH_SURVEY') : (bool) $surveyResponse->is_completed;

    $sections = array_values(array_filter([
        ['key' => 'blok1',  'no' => 'I',    'title' => 'Keterangan Umum',             'show' => (bool) ($showBlocks['blok1'] ?? true)],
        ['key' => 'blok2',  'no' => 'II',   'title' => 'Keterangan Perusahaan',       'show' => (bool) ($showBlocks['blok2'] ?? true)],
        ['key' => 'blok3a', 'no' => 'IIIA', 'title' => 'Barang yang Diproduksi',      'show' => !empty($showBlocks['blok3a'])],
        ['key' => 'blok3b', 'no' => 'IIIB', 'title' => 'Pendapatan & Pengeluaran'
                                                       . (!empty($showBlocks['blok3bIndustri']) ? ' (Industri)' : (!empty($showBlocks['blok3bNonIndustri']) ? ' (Non-Industri)' : '')),
                            'show' => !empty($showBlocks['blok3bIndustri']) || !empty($showBlocks['blok3bNonIndustri'])],
        ['key' => 'blok3c', 'no' => 'IIIC', 'title' => 'Bahan Baku & Bahan Penolong', 'show' => !empty($showBlocks['blok3c'])],
        ['key' => 'blok4',  'no' => 'IV',   'title' => 'Fenomena & Catatan',          'show' => !empty($showBlocks['blok4'])],
        ['key' => 'blok5',  'no' => 'V',    'title' => 'Kondisi & Prospek Usaha',     'show' => !empty($showBlocks['blok5'])],
        ['key' => 'blok6',  'no' => 'VI',   'title' => 'Catatan',                     'show' => !empty($showBlocks['blok6'])],
    ], fn ($s) => $s['show']));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rekap SIBSTR — {{ $companyName }}</title>
<style>
  /* Page margins must come from @page: padding on a wrapper only indents the
     start of the flow, so pages after a break start flush against the edge.
     Two dompdf traps when editing this file:
       1. a universal `* { margin: 0 }` also matches the page box and silently
          wipes these margins out — reset margins per element instead;
       2. dompdf honours a single unselectored @page rule only. */
  @page { margin: 12mm 10mm 14mm 10mm; }

  /* NB: html/body are deliberately absent — a margin on either also resets the
     page box in dompdf, which is what wipes out @page above. */
  * { box-sizing: border-box; }
  div, table, thead, tbody, tr, th, td, p, h1, h2 { margin: 0; padding: 0; }

  body { font-family: 'DejaVu Sans', sans-serif; font-size: 8pt; color: #111827; }

  /* ── Header ─────────────────────────────────────────────────────────────── */
  .header {
    background: #f0f9ff;
    border: 2px solid #3b82f6;
    border-radius: 5px;
    padding: 8px 12px;
    margin-bottom: 12px;
    text-align: center;
  }
  .header-org  { font-size: 7pt; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
  .header-main { font-size: 14pt; font-weight: bold; color: #1e40af; margin: 3px 0 1px; }
  .header-sub  { font-size: 8.5pt; color: #374151; }

  .idkv { width: 100%; border-collapse: collapse; margin-top: 7px; }
  .idkv td { border: 1px solid #bfdbfe; padding: 3px 6px; font-size: 7.5pt; text-align: left; }
  .idkv .l { background: #eff6ff; color: #1e40af; width: 17%; }
  .idkv .r { font-weight: bold; width: 33%; }

  .pill { display: inline-block; padding: 1px 7px; border-radius: 9px; font-size: 7pt; font-weight: bold; }
  .pill-done { background: #d1fae5; color: #065f46; }
  .pill-wip  { background: #fef3c7; color: #92400e; }

  /* ── Block sections ─────────────────────────────────────────────────────── */
  .block { margin-bottom: 14px; }
  .block-head {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 5px 9px;
    margin-bottom: 8px;
  }
  .block-head-t { font-size: 10pt; font-weight: bold; color: #1e3a8a; }

  .sub {
    font-size: 8pt;
    font-weight: bold;
    color: #1e40af;
    text-transform: uppercase;
    letter-spacing: .04em;
    border-bottom: 1.5px solid #dbeafe;
    padding-bottom: 3px;
    margin: 10px 0 5px;
  }

  .note  { font-size: 6.8pt; color: #6b7280; margin: 3px 0 6px; }
  .empty { font-size: 7.5pt; color: #9ca3af; font-style: italic; padding: 6px 0; text-align: center; }
  .hint  { font-size: 6.8pt; color: #9ca3af; font-weight: normal; }

  /* ── Fact tables. Real <table> rows, not CSS grid: dompdf does not support
        grid, which is what collapsed the old two-column layout. ──────────── */
  table.kv { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
  table.kv td {
    border: 1px solid #e5e7eb;
    padding: 3px 6px;
    font-size: 7.5pt;
    vertical-align: top;
    text-align: left;
  }
  table.kv td.k { width: 52%; background: #f9fafb; color: #374151; }
  table.kv td.v { font-weight: bold; color: #111827; }
  table.kv tr.r-total td { background: #f0fdf4; color: #065f46; }

  /* ── Data tables ────────────────────────────────────────────────────────── */
  .sx { margin-bottom: 8px; }
  .sx.keep { page-break-inside: avoid; }

  table.dt { width: 100%; border-collapse: collapse; font-size: 6.8pt; }
  table.dt th, table.dt td {
    border: 1px solid #d1d5db;
    padding: 2px 3px;
    text-align: center;
    vertical-align: top;
  }
  table.dt thead th { background: #eff6ff; color: #1e3a8a; font-weight: bold; }
  table.dt .ta-l { text-align: left; }
  table.dt .ta-c { text-align: center; }
  table.dt .w-no { width: 24px; }

  /* Tables carrying several rupiah columns. A 15-digit figure formatted with
     thousand separators has no break opportunity, so dompdf would push it past
     the right margin rather than wrap it — these tables are therefore set small
     enough that four such columns still fit inside the A4 portrait text width. */
  table.dt.wide { font-size: 6pt; }
  table.dt.wide th, table.dt.wide td { padding: 1.5px 2px; }

  .num { text-align: right; }

  table.dt tr.r-sub td   { background: #f0f9ff; color: #1d4ed8; font-size: 6.4pt; font-style: italic; }
  table.dt tr.r-alt td   { background: #fefce8; }
  table.dt tr.r-total td { background: #f0fdf4; font-weight: bold; color: #065f46; }

  table.dt th.th-dn { background: #fef9c3; color: #92400e; }
  table.dt th.th-ln { background: #dbeafe; color: #1e40af; }
  table.dt td.c-dn  { background: #fffdf2; }
  table.dt td.c-ln  { background: #f5faff; }

  table.dt th.th-prospect { background: #e0f2fe; color: #0c4a6e; }
  table.dt td.c-prospect  { background: #f7fbff; font-weight: bold; }

  .badge { display: inline-block; padding: 0 5px; border-radius: 8px; background: #dbeafe; color: #1e40af; font-size: 6.5pt; font-weight: bold; }

  .footer {
    text-align: center;
    font-size: 6.5pt;
    color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    padding-top: 5px;
    margin-top: 10px;
  }
</style>
<script type="text/php">
  if (isset($pdf)) {
      $pdf->page_text(520, 812, "Halaman {PAGE_NUM} / {PAGE_COUNT}", null, 8, array(0.55, 0.55, 0.55));
  }
</script>
</head>
<body>

{{-- ══ HEADER ═══════════════════════════════════════════════════════════════ --}}
<div class="header">
  <div class="header-org">Badan Pusat Statistik &mdash; Republik Indonesia</div>
  <div class="header-main">REKAP DATA SIBSTR</div>
  <div class="header-sub">
    Survei Industri Besar dan Sedang &mdash; {{ $isAnnualPdf ? 'Pencacahan Tahunan' : 'Pencacahan Triwulanan' }} {{ $surveyTahun }}
  </div>

  <table class="idkv">
    <tr>
      <td class="l">Perusahaan</td><td class="r">{{ $companyName }}</td>
      <td class="l">Periode</td><td class="r">{{ $periodLabel }}</td>
    </tr>
    <tr>
      <td class="l">KIP</td><td class="r">{{ F::plain($surveyResponse->kip) }}</td>
      <td class="l">IDSBR</td><td class="r">{{ F::plain($surveyResponse->idsbr) }}</td>
    </tr>
    <tr>
      <td class="l">Terakhir Diperbarui</td><td class="r">{{ $submissionAt }}</td>
      <td class="l">Status</td>
      <td class="r">
        @if($isFinished)
          <span class="pill pill-done">&#10003; {{ $isAnnualPdf ? 'FINISH_SURVEY' : 'Selesai' }}</span>
        @else
          <span class="pill pill-wip">Dalam Proses</span>
        @endif
      </td>
    </tr>
  </table>
</div>

{{-- ══ BLOCKS ═══════════════════════════════════════════════════════════════ --}}
@foreach($sections as $section)
<div class="block">
  <div class="block-head">
    <div class="block-head-t">Blok {{ $section['no'] }}. {{ $section['title'] }}</div>
  </div>

  @switch($section['key'])
    @case('blok3b')
      @include('bps.sibstr.data.blok3b', [
          'showIndustri'    => !empty($showBlocks['blok3bIndustri']),
          'showNonIndustri' => !empty($showBlocks['blok3bNonIndustri']),
      ])
      @break
    @default
      @include('bps.sibstr.data.' . $section['key'])
  @endswitch
</div>
@endforeach

<div class="footer">
  Dokumen ini dihasilkan otomatis dari basis data DataKita &mdash; BPS Kota Batam.
  Kerahasiaan data dijamin UU No. 16 Tahun 1997 tentang Statistik, Pasal 21.
</div>

</body>
</html>
