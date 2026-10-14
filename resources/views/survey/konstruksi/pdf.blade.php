@php
    use App\Models\KonstruksiSurveyResponse as K;
    use App\Support\SibstrFormat;

    $dash = fn ($v) => trim((string) $v) === '' ? '—' : $v;
    $proyek = $response->totalProyek();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  /* Page margins live on @page. A `*` / html / body margin reset silently
     zeroes them and breaks every page after the first. */
  @page { margin: 20mm 18mm; }
  * { padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5pt; color: #111827; margin: 0; }

  .header {
    background: #fffbeb;
    border: 2px solid #f59e0b;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 18px;
    text-align: center;
  }
  .header-org  { font-size: 8pt; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
  .header-main { font-size: 16pt; font-weight: bold; color: #b45309; margin: 4px 0 2px; }
  .header-sub  { font-size: 10pt; color: #374151; }

  .success-wrap { text-align: center; margin: 16px 0; }
  .success-title { font-size: 14pt; font-weight: bold; color: #065f46; }
  .success-desc  { font-size: 9pt; color: #374151; margin-top: 4px; }
  .status-pill {
    display: inline-block;
    background: #d1fae5; color: #065f46;
    border: 1px solid #6ee7b7; border-radius: 9999px;
    padding: 3px 14px; font-size: 9pt; font-weight: bold;
    margin-top: 8px;
  }

  .card {
    background: #fffbeb;
    border: 1px solid #f59e0b;
    border-radius: 6px;
    padding: 12px 16px;
    margin-bottom: 14px;
  }
  .card-title {
    font-size: 8pt; font-weight: bold; color: #b45309;
    text-transform: uppercase; letter-spacing: .06em;
    border-bottom: 1px solid #fde68a; padding-bottom: 5px; margin-bottom: 9px;
  }

  table { width: 100%; border-collapse: collapse; }
  td { padding: 3px 0; vertical-align: top; font-size: 9pt; }
  td.label { color: #6b7280; width: 42%; }
  td.value { color: #111827; font-weight: bold; }
  td.num { text-align: right; }

  .sum { border-top: 1px solid #fde68a; }
  .sum td { padding-top: 6px; font-weight: bold; color: #b45309; }

  .foot {
    margin-top: 18px; padding-top: 10px;
    border-top: 1px solid #e5e7eb;
    font-size: 7.5pt; color: #6b7280; text-align: center; line-height: 1.6;
  }
</style>
</head>
<body>

<div class="header">
  <div class="header-org">Badan Pusat Statistik Kota Batam</div>
  <div class="header-main">Survei Perusahaan Konstruksi</div>
  <div class="header-sub">SKTH — {{ $periode }}</div>
</div>

<div class="success-wrap">
  <div class="success-title">Bukti Pengisian Terkirim</div>
  <div class="success-desc">
    Terima kasih. Isian survei Anda telah kami terima dan tercatat dalam sistem DataKita.
  </div>
  <div class="status-pill">SELESAI &middot; {{ $completedAt }}</div>
</div>

<div class="card">
  <div class="card-title">Identitas Perusahaan</div>
  <table>
    <tr><td class="label">Nama Perusahaan</td><td class="value">{{ $dash($response->nama_perusahaan) }}</td></tr>
    <tr><td class="label">Nomor Urut Sampel (NUS)</td><td class="value">{{ $dash($response->nus) }}</td></tr>
    <tr><td class="label">NIB</td><td class="value">{{ $response->punya_nib === 'ya' ? $dash($response->nib) : 'Tidak memiliki NIB' }}</td></tr>
    <tr><td class="label">Alamat</td><td class="value">{{ $dash($response->alamat_perusahaan) }}</td></tr>
    <tr><td class="label">Kelurahan/Desa</td><td class="value">{{ $dash($response->kelurahan_desa) }}</td></tr>
    <tr><td class="label">Kecamatan</td><td class="value">{{ $dash($response->kecamatan) }}</td></tr>
    <tr><td class="label">Kabupaten/Kota</td><td class="value">{{ $dash($response->kabupaten_kota) }}</td></tr>
    <tr><td class="label">Provinsi</td><td class="value">{{ $dash($response->provinsi) }}</td></tr>
    <tr><td class="label">Badan Hukum / Usaha</td><td class="value">
      {{ K::BADAN_HUKUM[$response->badan_hukum] ?? '—' }}{{ $response->badan_hukum === '9' ? ' — ' . $dash($response->badan_hukum_lainnya) : '' }}
    </td></tr>
    <tr><td class="label">Status Penanaman Modal</td><td class="value">{{ K::STATUS_MODAL[$response->status_penanaman_modal] ?? '—' }}</td></tr>
    <tr><td class="label">Kualifikasi</td><td class="value">{{ K::KUALIFIKASI[$response->kualifikasi] ?? '—' }}</td></tr>
  </table>
</div>

<div class="card">
  <div class="card-title">Ringkasan Isian</div>
  <table>
    <tr>
      <td class="label">Jumlah pekerja tetap &amp; kontrak</td>
      <td class="value num">{{ SibstrFormat::idr($response->totalPekerja()) }} orang</td>
    </tr>
    <tr>
      <td class="label">Jumlah proyek dilaporkan</td>
      <td class="value num">{{ count($response->proyekRows()) }} proyek</td>
    </tr>
    <tr>
      <td class="label">Nilai kontrak seluruh proyek</td>
      <td class="value num">{{ SibstrFormat::idr($proyek['nilai_kontrak']) }} (000 Rp)</td>
    </tr>
    <tr>
      <td class="label">Nilai konstruksi yang diselesaikan</td>
      <td class="value num">{{ SibstrFormat::idr($proyek['nilai_konstruksi']) }} (000 Rp)</td>
    </tr>
    <tr class="sum">
      <td>Jumlah pengeluaran (Blok IX–A)</td>
      <td class="num">{{ SibstrFormat::idr($ringkasan['total_a']) }} (000 Rp)</td>
    </tr>
    <tr>
      <td class="label" style="font-weight:bold;color:#b45309;">Jumlah pendapatan (Blok IX–B)</td>
      <td class="num" style="font-weight:bold;color:#b45309;">{{ SibstrFormat::idr($ringkasan['total_b']) }} (000 Rp)</td>
    </tr>
    <tr>
      <td class="label" style="font-weight:bold;color:#b45309;">Persentase keuntungan terhadap pengeluaran</td>
      <td class="num" style="font-weight:bold;color:#b45309;">
        {{ $ringkasan['margin_pct'] === null ? '—' : number_format($ringkasan['margin_pct'], 2, ',', '.') . ' %' }}
      </td>
    </tr>
  </table>
</div>

<div class="card">
  <div class="card-title">Pemberi Jawaban</div>
  <table>
    <tr><td class="label">Nama</td><td class="value">{{ $dash($response->pj_nama) }}</td></tr>
    <tr><td class="label">Jabatan</td><td class="value">{{ $dash($response->pj_jabatan) }}</td></tr>
    <tr><td class="label">Nomor HP / Telepon</td><td class="value">{{ $dash($response->pj_hp) }}</td></tr>
    <tr><td class="label">E-mail</td><td class="value">{{ $dash($response->pj_email) }}</td></tr>
    <tr><td class="label">Akun pengisi</td><td class="value">{{ $user->name ?? '—' }}</td></tr>
  </table>
</div>

<div class="foot">
  Dokumen ini adalah bukti bahwa isian survei telah terkirim, bukan salinan lengkap jawaban.<br>
  Kerahasiaan data dijamin Undang-Undang Nomor 16 Tahun 1997 tentang Statistik (Pasal 21).<br>
  Dicetak dari DataKita — BPS Kota Batam.
</div>

</body>
</html>
