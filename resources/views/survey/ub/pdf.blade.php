@php
    $namaPerusahaan = $response->nama_perusahaan ?? '—';
    $namaKomersial  = $response->nama_komersial  ?? '—';
    $namaUser       = $user->name ?? '—';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #111827; }
  .page { padding: 22mm 22mm 18mm 22mm; }

  /* ── Header ─────────────────────────────────────────────────────────────── */
  .header {
    background: #eff6ff;
    border: 2px solid #3b82f6;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 22px;
    text-align: center;
  }
  .header-org  { font-size: 8pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; }
  .header-main { font-size: 17pt; font-weight: bold; color: #1e40af; margin: 4px 0 2px; }
  .header-sub  { font-size: 10pt; color: #374151; }

  /* ── Success block ───────────────────────────────────────────────────────── */
  .success-wrap { text-align: center; margin: 20px 0 18px; }
  .check-circle {
    display: inline-block;
    width: 54px; height: 54px;
    background: #d1fae5;
    border: 3px solid #10b981;
    border-radius: 27px;
    font-size: 26pt;
    color: #065f46;
    line-height: 50px;
    text-align: center;
  }
  .success-title { font-size: 16pt; font-weight: bold; color: #065f46; margin-top: 10px; }
  .success-desc  { font-size: 9.5pt; color: #374151; margin-top: 5px; }
  .status-pill {
    display: inline-block;
    background: #d1fae5; color: #065f46;
    border: 1px solid #6ee7b7; border-radius: 9999px;
    padding: 3px 14px; font-size: 9pt; font-weight: bold;
    margin-top: 8px;
  }

  /* ── Info card ───────────────────────────────────────────────────────────── */
  .info-card {
    background: #f0f9ff;
    border: 2px solid #3b82f6;
    border-radius: 6px;
    padding: 14px 18px;
    margin-bottom: 16px;
  }
  .card-title {
    font-size: 8.5pt; font-weight: bold;
    text-transform: uppercase; letter-spacing: 0.05em;
    color: #1e40af;
    border-bottom: 1px solid #bfdbfe;
    padding-bottom: 6px; margin-bottom: 12px;
  }
  .kv { width: 100%; border-collapse: collapse; }
  .kv td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 9.5pt; }
  .kv tr:last-child td { border-bottom: none; }
  .kv .lbl { color: #6b7280; width: 42%; }
  .kv .val { font-weight: bold; color: #111827; }

  /* ── Reference box ───────────────────────────────────────────────────────── */
  .ref-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 4px;
    padding: 10px 14px;
    text-align: center;
    margin-bottom: 14px;
  }
  .ref-label { font-size: 7.5pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; }
  .ref-code  { font-size: 11pt; font-weight: bold; color: #1e40af; margin-top: 4px; }

  /* ── Notice ──────────────────────────────────────────────────────────────── */
  .notice {
    background: #fefce8;
    border: 1px solid #fde68a;
    border-radius: 4px;
    padding: 8px 12px;
    font-size: 8pt; color: #78350f;
    margin-bottom: 14px;
  }

  /* ── Responden block ─────────────────────────────────────────────────────── */
  .resp-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9pt; }
  .resp-table th {
    background: #eff6ff; color: #1e3a8a;
    border: 1px solid #bfdbfe;
    padding: 5px 8px; text-align: center;
  }
  .resp-table td { border: 1px solid #e5e7eb; padding: 4px 8px; }
  .sign-space { height: 30px; }

  /* ── Footer ──────────────────────────────────────────────────────────────── */
  .footer {
    text-align: center; font-size: 7.5pt; color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    padding-top: 8px; margin-top: 16px;
  }
</style>
</head>
<body>
<div class="page">

{{-- ══ HEADER ══════════════════════════════════════════════════════════════ --}}
<div class="header">
  <div class="header-org">Badan Pusat Statistik &mdash; Republik Indonesia</div>
  <div class="header-main">SENSUS EKONOMI 2026</div>
  <div class="header-sub">SE2026-L.UB &mdash; Pendataan Lengkap Usaha/Perusahaan</div>
</div>

{{-- ══ SUCCESS BADGE ════════════════════════════════════════════════════════ --}}
<div class="success-wrap">
  <div class="check-circle">&#10003;</div>
  <div class="success-title">Bukti Pengisian Terkirim</div>
  <div class="success-desc">Data Anda telah berhasil tersimpan dalam sistem survei SE2026</div>
  <div><span class="status-pill">&#10003;&nbsp; SURVEI SELESAI</span></div>
</div>

{{-- ══ DETAIL PENGISIAN ═════════════════════════════════════════════════════ --}}
<div class="info-card">
  <div class="card-title">Informasi Pengisian</div>
  <table class="kv">
    <tr>
      <td class="lbl">Nama Perusahaan</td>
      <td class="val">{{ $namaPerusahaan }}</td>
    </tr>
    <tr>
      <td class="lbl">Nama Komersial / Merek</td>
      <td class="val">{{ $namaKomersial }}</td>
    </tr>
    <tr>
      <td class="lbl">Nama Pengisi / Pengguna</td>
      <td class="val">{{ $namaUser }}</td>
    </tr>
    @if($response->has_nib == 1 && $response->nib)
    <tr>
      <td class="lbl">NIB</td>
      <td class="val">{{ $response->nib }}</td>
    </tr>
    @endif
    <tr>
      <td class="lbl">Tanggal &amp; Waktu Pengisian</td>
      <td class="val">{{ $completedAt }}</td>
    </tr>
    <tr>
      <td class="lbl">Tahun Survei</td>
      <td class="val">2026</td>
    </tr>
  </table>
</div>

{{-- ══ KETERANGAN RESPONDEN ════════════════════════════════════════════════ --}}
@if($response->resp_nama || $response->resp_nip || $response->resp_telepon)
<div class="info-card" style="margin-top:0;">
  <div class="card-title">Keterangan Pemberi Jawaban</div>
  <table class="kv">
    @if($response->resp_nama)
    <tr><td class="lbl">Nama Responden</td><td class="val">{{ $response->resp_nama }}</td></tr>
    @endif
    @if($response->resp_nip)
    <tr><td class="lbl">NIP / NMS</td><td class="val">{{ $response->resp_nip }}</td></tr>
    @endif
    @if($response->resp_telepon)
    <tr><td class="lbl">Telepon</td><td class="val">{{ $response->resp_telepon }}</td></tr>
    @endif
    @if($response->resp_tanggal)
    <tr><td class="lbl">Tanggal Responden</td><td class="val">{{ $response->resp_tanggal?->format('d/m/Y') }}</td></tr>
    @endif
  </table>
</div>
@endif

{{-- ══ NOTICE ═══════════════════════════════════════════════════════════════ --}}
<div class="notice">
  <strong>Catatan:</strong> Dokumen ini merupakan bukti bahwa pengisian kuesioner SE2026-L.UB
  telah dilakukan dan data telah tersimpan dalam sistem. Kerahasiaan data dijamin berdasarkan
  UU No. 16 Tahun 1997 tentang Statistik, Pasal 21. Simpan dokumen ini sebagai arsip pengisian survei Anda.
</div>

{{-- ══ FOOTER ═══════════════════════════════════════════════════════════════ --}}
<div class="footer">
  Digenerate otomatis oleh sistem survei online DataKita &nbsp;|&nbsp;
  Sensus Ekonomi 2026 &mdash; Badan Pusat Statistik RI &nbsp;|&nbsp;
  Dicetak: {{ now()->format('d/m/Y H:i:s') }}
</div>

</div>
</body>
</html>
