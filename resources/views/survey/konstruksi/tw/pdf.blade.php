@php
    use App\Models\KonstruksiSurveyResponse as K;
    use App\Support\SibstrFormat;

    $dash    = fn ($v) => trim((string) $v) === '' ? '—' : $v;
    $pekerja = is_array($response->tw_pekerja) ? $response->tw_pekerja : [];
    $bj      = is_array($response->tw_balas_jasa) ? $response->tw_balas_jasa : [];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  /* Margins live on @page. A `*` / html / body margin reset silently zeroes
     them and breaks every page after the first. */
  @page { margin: 20mm 18mm; }
  * { padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9.5pt; color: #111827; margin: 0; }

  .header {
    background: #fffbeb; border: 2px solid #f59e0b; border-radius: 6px;
    padding: 12px 16px; margin-bottom: 18px; text-align: center;
  }
  .header-org  { font-size: 8pt; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
  .header-main { font-size: 16pt; font-weight: bold; color: #b45309; margin: 4px 0 2px; }
  .header-sub  { font-size: 10pt; color: #374151; }

  .success-wrap { text-align: center; margin: 16px 0; }
  .success-title { font-size: 14pt; font-weight: bold; color: #065f46; }
  .success-desc  { font-size: 9pt; color: #374151; margin-top: 4px; }
  .status-pill {
    display: inline-block; background: #d1fae5; color: #065f46;
    border: 1px solid #6ee7b7; border-radius: 9999px;
    padding: 3px 14px; font-size: 9pt; font-weight: bold; margin-top: 8px;
  }

  .card {
    background: #fffbeb; border: 1px solid #f59e0b; border-radius: 6px;
    padding: 12px 16px; margin-bottom: 14px;
  }
  .card-title {
    font-size: 8pt; font-weight: bold; color: #b45309;
    text-transform: uppercase; letter-spacing: .06em;
    border-bottom: 1px solid #fde68a; padding-bottom: 5px; margin-bottom: 9px;
  }

  table { width: 100%; border-collapse: collapse; }
  td { padding: 3px 0; vertical-align: top; font-size: 9pt; }
  td.label { color: #6b7280; width: 52%; }
  td.value { color: #111827; font-weight: bold; }
  td.num { text-align: right; }

  .foot {
    margin-top: 18px; padding-top: 10px; border-top: 1px solid #e5e7eb;
    font-size: 7.5pt; color: #6b7280; text-align: center; line-height: 1.6;
  }
</style>
</head>
<body>

<div class="header">
  <div class="header-org">Badan Pusat Statistik Kota Batam</div>
  <div class="header-main">Survei Perusahaan Konstruksi Triwulanan</div>
  <div class="header-sub">SKTR — {{ $periode }}</div>
</div>

<div class="success-wrap">
  <div class="success-title">Bukti Pengisian Terkirim</div>
  <div class="success-desc">
    Terima kasih. Isian survei Anda telah kami terima dan tercatat dalam sistem DataKita.
  </div>
  <div class="status-pill">SELESAI &middot; {{ $completedAt }}</div>
</div>

<div class="card">
  <div class="card-title">Updating Status Perusahaan</div>
  <table>
    <tr><td class="label">Status Perusahaan</td><td class="value">{{ K::STATUS_PERUSAHAAN[$response->status_perusahaan] ?? '—' }}</td></tr>
    <tr><td class="label">Kesediaan Responden</td><td class="value">{{ K::KESEDIAAN_RESPONDEN[$response->kesediaan_responden] ?? '—' }}</td></tr>
    @if(!$response->stoppedAtStatus())
      <tr><td class="label">Keberadaan Proyek</td><td class="value">{{ K::KEBERADAAN_PROYEK[$response->keberadaan_proyek] ?? '—' }}</td></tr>
    @endif
    @if((string) $response->status_perusahaan === K::STATUS_PINDAH)
      <tr><td class="label">Alamat Baru</td><td class="value">
        {{ $dash($response->pindah_alamat) }},
        {{ $dash($response->pindah_kelurahan_desa) }},
        {{ $dash($response->pindah_kecamatan) }},
        {{ $dash($response->pindah_kabupaten_kota) }}
      </td></tr>
    @endif
  </table>
</div>

@if(!$response->stoppedAtStatus())
<div class="card">
  <div class="card-title">Identitas Perusahaan</div>
  <table>
    <tr><td class="label">Nama Perusahaan</td><td class="value">{{ $dash($response->nama_perusahaan) }}</td></tr>
    <tr><td class="label">Nomor Urut Sampel (NUS)</td><td class="value">{{ $dash($response->nus) }}</td></tr>
    <tr><td class="label">NIB</td><td class="value">{{ $response->punya_nib === 'ya' ? $dash($response->nib) : 'Tidak memiliki NIB' }}</td></tr>
    <tr><td class="label">Alamat</td><td class="value">{{ $dash($response->alamat_perusahaan) }}</td></tr>
    <tr><td class="label">Kabupaten/Kota</td><td class="value">{{ $dash($response->kabupaten_kota) }}</td></tr>
    <tr><td class="label">Kualifikasi</td><td class="value">{{ K::KUALIFIKASI[$response->kualifikasi] ?? '—' }}</td></tr>
  </table>
</div>

<div class="card">
  <div class="card-title">Ringkasan Isian</div>
  <table>
    @foreach(K::TW_SLOTS as $slot => $delta)
      <tr>
        <td class="label">Pekerja tetap &amp; kontrak — {{ $quarterLabels[$slot]['short'] ?? '' }}</td>
        <td class="value num">{{ SibstrFormat::idr($pekerja[$slot] ?? null) }} orang</td>
      </tr>
    @endforeach
    @foreach(K::TW_SLOTS as $slot => $delta)
      <tr>
        <td class="label">Balas jasa — {{ $quarterLabels[$slot]['short'] ?? '' }}</td>
        <td class="value num">{{ SibstrFormat::idr($bj[$slot] ?? null) }} (000 Rp)</td>
      </tr>
    @endforeach
    <tr>
      <td class="label">Jumlah proyek dilaporkan</td>
      <td class="value num">{{ count($response->proyekTwRows()) }} proyek</td>
    </tr>
    <tr>
      <td class="label">Nilai kontrak seluruh proyek</td>
      <td class="value num">{{ SibstrFormat::idr($proyekTotal['nilai_kontrak']) }} (000 Rp)</td>
    </tr>
    <tr>
      <td class="label">Upah pekerja harian — {{ $quarterLabels['upah_ini']['short'] ?? '' }}</td>
      <td class="value num">{{ SibstrFormat::idr($proyekTotal['upah_ini']) }} (000 Rp)</td>
    </tr>
    <tr>
      <td class="label" style="color:#b45309;">Nilai konstruksi triwulan ini</td>
      <td class="value num" style="color:#b45309;">{{ SibstrFormat::idr($proyekTotal['nilai_konstruksi']) }} (000 Rp)</td>
    </tr>
  </table>
</div>
@endif

<div class="card">
  <div class="card-title">Pemberi Jawaban</div>
  <table>
    <tr><td class="label">Nama</td><td class="value">{{ $dash($response->pj_nama) }}</td></tr>
    <tr><td class="label">Jabatan</td><td class="value">{{ $dash($response->pj_jabatan) }}</td></tr>
    <tr><td class="label">Nomor Telepon / HP</td><td class="value">{{ $dash($response->pj_hp) }}</td></tr>
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
