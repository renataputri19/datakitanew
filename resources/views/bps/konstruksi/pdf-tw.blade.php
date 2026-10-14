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
  @page { margin: 12mm 10mm; }
  * { padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 7.5pt; color: #111827; margin: 0; }

  .header { border: 1.5px solid #f59e0b; background: #fffbeb; border-radius: 4px; padding: 8px 12px; margin-bottom: 10px; }
  .header .org { font-size: 6.5pt; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
  .header .main { font-size: 13pt; font-weight: bold; color: #b45309; margin: 2px 0; }
  .header .sub { font-size: 8pt; color: #374151; }

  h2.blok {
    font-size: 8pt; font-weight: bold; color: #b45309;
    background: #fffbeb; border-left: 3px solid #f59e0b;
    padding: 4px 8px; margin: 12px 0 5px;
    page-break-after: avoid;
  }

  table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
  th, td { border: .5px solid #d1d5db; padding: 3px 5px; vertical-align: top; }
  thead th { background: #f3f4f6; font-size: 6.5pt; font-weight: bold; text-align: center; color: #374151; }
  td.num { text-align: right; }
  tr.total { background: #fffbeb; }
  tr.total td { font-weight: bold; color: #b45309; }
  .rencana { background: #eff6ff; }

  table.kv td { border: none; padding: 2px 4px; }
  table.kv td.k { color: #6b7280; width: 20%; }
  table.kv td.v { font-weight: bold; width: 30%; }

  .note { font-size: 6.5pt; color: #6b7280; font-style: italic; margin-bottom: 6px; }
  .warn { font-size: 7pt; color: #92400e; background: #fffbeb; border: .5px solid #fde68a; padding: 5px 8px; border-radius: 3px; margin-bottom: 8px; }
  .foot { margin-top: 10px; padding-top: 6px; border-top: .5px solid #e5e7eb; font-size: 6pt; color: #6b7280; text-align: center; line-height: 1.5; }
</style>
</head>
<body>

<div class="header">
  <div class="org">Badan Pusat Statistik Kota Batam &middot; DataKita</div>
  <div class="main">Data Survei Perusahaan Konstruksi Triwulanan</div>
  <div class="sub">
    {{ $dash($response->nama_perusahaan) }} &middot; SKTR {{ $periode }} &middot;
    {{ $response->is_completed ? 'Selesai' : 'Dalam proses (' . $response->completionPercent() . '%)' }} &middot;
    terakhir disimpan {{ $completedAt }}
  </div>
</div>

<h2 class="blok">UPDATING STATUS PERUSAHAAN</h2>
<table class="kv">
  <tr>
    <td class="k">Status Perusahaan</td><td class="v">{{ K::STATUS_PERUSAHAAN[$response->status_perusahaan] ?? '—' }}</td>
    <td class="k">Kesediaan Responden</td><td class="v">{{ K::KESEDIAAN_RESPONDEN[$response->kesediaan_responden] ?? '—' }}</td>
  </tr>
  <tr>
    <td class="k">Keberadaan Proyek</td>
    <td class="v" colspan="3">{{ K::KEBERADAAN_PROYEK[$response->keberadaan_proyek] ?? '—' }}</td>
  </tr>
  @if((string) $response->status_perusahaan === K::STATUS_PINDAH)
  <tr>
    <td class="k">Alamat Baru</td>
    <td class="v" colspan="3">
      {{ $dash($response->pindah_alamat) }},
      {{ $dash($response->pindah_kelurahan_desa) }},
      {{ $dash($response->pindah_kecamatan) }},
      {{ $dash($response->pindah_kabupaten_kota) }}
      @if($response->pindah_kode_pos) &middot; {{ $response->pindah_kode_pos }} @endif
    </td>
  </tr>
  @endif
</table>

@if($response->stoppedAtStatus())
  <p class="warn">
    Pencacahan berhenti pada Updating Status Perusahaan — perusahaan tidak aktif atau responden
    tidak memberikan jawaban. Blok I sampai Blok III tidak diisi.
  </p>
@endif

@if(!$response->stoppedAtStatus())
<h2 class="blok">BLOK I — KETERANGAN UMUM PERUSAHAAN</h2>
<table class="kv">
  <tr>
    <td class="k">KIP</td><td class="v">{{ $dash($response->kip) }}</td>
    <td class="k">Nomor Urut Sampel</td><td class="v">{{ $dash($response->nus) }}</td>
  </tr>
  <tr>
    <td class="k">Provinsi</td><td class="v">{{ $dash($response->provinsi) }}</td>
    <td class="k">Kabupaten/Kota</td><td class="v">{{ $dash($response->kabupaten_kota) }}</td>
  </tr>
  <tr>
    <td class="k">Kecamatan</td><td class="v">{{ $dash($response->kecamatan) }}</td>
    <td class="k">Desa/Kelurahan</td><td class="v">{{ $dash($response->kelurahan_desa) }}</td>
  </tr>
  <tr>
    <td class="k">NIB</td><td class="v">{{ $response->punya_nib === 'ya' ? $dash($response->nib) : 'Tidak memiliki NIB' }}</td>
    <td class="k">Penanggung Jawab</td><td class="v">{{ $dash($response->nama_pengusaha) }} &middot; {{ $dash($response->hp_pengusaha) }}</td>
  </tr>
  <tr>
    <td class="k">Alamat</td>
    <td class="v" colspan="3">
      {{ $dash($response->alamat_perusahaan) }}
      @if($response->rt || $response->rw) &middot; RT {{ $dash($response->rt) }}/RW {{ $dash($response->rw) }} @endif
      @if($response->kode_pos) &middot; {{ $response->kode_pos }} @endif
    </td>
  </tr>
  <tr>
    <td class="k">Telepon / Website</td><td class="v">{{ $dash($response->nomor_telepon) }} &middot; {{ $dash($response->website) }}</td>
    <td class="k">E-mail</td><td class="v">{{ $dash($response->email_perusahaan) }}</td>
  </tr>
  <tr>
    <td class="k">Badan Hukum / Usaha</td>
    <td class="v">{{ K::BADAN_HUKUM[$response->badan_hukum] ?? '—' }}{{ $response->badan_hukum === '9' ? ' — ' . $dash($response->badan_hukum_lainnya) : '' }}</td>
    <td class="k">Status Permodalan</td><td class="v">{{ K::STATUS_MODAL[$response->status_penanaman_modal] ?? '—' }}</td>
  </tr>
  <tr><td class="k">Kualifikasi</td><td class="v" colspan="3">{{ K::KUALIFIKASI[$response->kualifikasi] ?? '—' }}</td></tr>
</table>

<h2 class="blok">BLOK II — TENAGA KERJA &amp; BALAS JASA</h2>
<p class="note">Termasuk WNI, WNA dan outsourcing. Tidak termasuk pekerja tidak tetap/harian. Balas jasa dalam ribuan rupiah.</p>
<table>
  <thead>
    <tr>
      <th style="text-align:left;">Triwulan</th>
      <th style="width:25%;">Pekerja Tetap &amp; Kontrak (orang)</th>
      <th style="width:25%;">Balas Jasa (000 Rp)</th>
    </tr>
  </thead>
  <tbody>
    @foreach(K::TW_SLOTS as $slot => $delta)
      <tr>
        <td>{{ $quarterLabels[$slot]['range'] ?? $slot }}</td>
        <td class="num">{{ SibstrFormat::idr($pekerja[$slot] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($bj[$slot] ?? null) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<h2 class="blok">BLOK III — NILAI KONTRAK &amp; REALISASI FISIK PEKERJAAN/PROYEK</h2>
@if($response->stoppedAtStatus())
  <p class="warn">Pengisian berhenti di Updating Status Perusahaan, sehingga Blok III tidak berlaku.</p>
@elseif(!$response->proyekBlokApplies())
  <p class="warn">Responden menyatakan <b>tidak ada proyek konstruksi</b> pada triwulan ini, sehingga Blok III tidak diisi.</p>
@elseif($proyekTwRows === [])
  <p class="note">Belum ada proyek yang diisi.</p>
@else
<p class="note">
  {{ count($proyekTwRows) }} proyek. Nilai dalam ribuan rupiah.
  Kolom (12) adalah rencana/perkiraan realisasi triwulan berikutnya.
</p>
<table style="font-size:6pt;">
  <thead>
    <tr>
      <th rowspan="2">No</th>
      <th rowspan="2" style="text-align:left;">Nama Pekerjaan / KBLI (2)</th>
      <th rowspan="2" style="text-align:left;">Lokasi (3)</th>
      <th rowspan="2">Dana (4)</th>
      <th rowspan="2">Kontrak (5)</th>
      <th rowspan="2">% Subkon (6)</th>
      <th rowspan="2">Mulai (7)</th>
      <th rowspan="2">Selesai (8)</th>
      <th colspan="4">% Kumulatif Realisasi Fisik s/d</th>
      <th colspan="2">Upah Harian</th>
      <th colspan="2">Rata Upah/Org/Hari</th>
      <th rowspan="2">Nilai Konstruksi TW Ini</th>
    </tr>
    <tr>
      <th>{{ $quarterLabels['realisasi_2lalu']['short'] ?? '' }} (9)</th>
      <th>{{ $quarterLabels['realisasi_lalu']['short'] ?? '' }} (10)</th>
      <th>{{ $quarterLabels['realisasi_ini']['short'] ?? '' }} (11)</th>
      <th class="rencana">{{ $quarterLabels['realisasi_depan']['short'] ?? '' }} (12)</th>
      <th>{{ $quarterLabels['upah_lalu']['short'] ?? '' }} (13)</th>
      <th>{{ $quarterLabels['upah_ini']['short'] ?? '' }} (14)</th>
      <th>{{ $quarterLabels['upah_rata_lalu']['short'] ?? '' }} (15)</th>
      <th>{{ $quarterLabels['upah_rata_ini']['short'] ?? '' }} (16)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($proyekTwRows as $i => $row)
      <tr>
        <td class="num">{{ $i + 1 }}</td>
        <td>{{ $dash($row['nama_pekerjaan'] ?? '') }}@if(trim((string) ($row['kbli'] ?? '')) !== '')<br><span style="color:#6b7280;">KBLI {{ $row['kbli'] }}</span>@endif</td>
        <td>{{ $dash($row['kabkota_lokasi'] ?? '') }}<br><span style="color:#6b7280;">{{ $row['provinsi_lokasi'] ?? '' }}</span></td>
        <td>{{ $row['sumber_dana'] ?? '—' }}</td>
        <td class="num">{{ SibstrFormat::idr($row['nilai_kontrak'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::pct($row['pct_subkontrak'] ?? null) }}</td>
        <td>{{ $dash($row['mulai'] ?? '') }}</td>
        <td>{{ $dash($row['selesai'] ?? '') }}</td>
        <td class="num">{{ SibstrFormat::pct($row['realisasi_2lalu'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::pct($row['realisasi_lalu'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::pct($row['realisasi_ini'] ?? null) }}</td>
        <td class="num rencana">{{ SibstrFormat::pct($row['realisasi_depan'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['upah_lalu'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['upah_ini'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['upah_rata_lalu'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['upah_rata_ini'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr(K::nilaiKonstruksiTw($row)) }}</td>
      </tr>
    @endforeach
    <tr class="total">
      <td colspan="4" style="text-align:right;">JUMLAH</td>
      <td class="num">{{ SibstrFormat::idr($proyekTwTotal['nilai_kontrak']) }}</td>
      <td colspan="7"></td>
      <td class="num">{{ SibstrFormat::idr($proyekTwTotal['upah_lalu']) }}</td>
      <td class="num">{{ SibstrFormat::idr($proyekTwTotal['upah_ini']) }}</td>
      <td colspan="2"></td>
      <td class="num">{{ SibstrFormat::idr($proyekTwTotal['nilai_konstruksi']) }}</td>
    </tr>
  </tbody>
</table>
<p class="note">
  Sumber dana: @foreach(K::SUMBER_DANA as $code => $label){{ $code }} = {{ $label }}@if(!$loop->last) &middot; @endif @endforeach.
  Nilai konstruksi triwulan ini = nilai kontrak × (1 − % disubkontrakkan) × (kolom 11 − kolom 10) ÷ 100; SKTR tidak mencetak kolom ini.
</p>
@endif
@endif

<h2 class="blok">PENUTUP — METODE PENDATAAN, CATATAN &amp; PEMBERI JAWABAN</h2>
<table class="kv">
  <tr>
    <td class="k">Metode Pendataan Tahun {{ (int) $response->tahun + 1 }}</td>
    <td class="v">{{ K::METODE_PENDATAAN[$response->metode_pendataan] ?? '—' }}</td>
    <td class="k">Nama Pemberi Jawaban</td><td class="v">{{ $dash($response->pj_nama) }}</td>
  </tr>
  <tr>
    <td class="k">Jabatan</td><td class="v">{{ $dash($response->pj_jabatan) }}</td>
    <td class="k">Telepon / E-mail</td><td class="v">{{ $dash($response->pj_hp) }} &middot; {{ $dash($response->pj_email) }}</td>
  </tr>
  <tr><td class="k">Akun Pengisi</td><td class="v" colspan="3">{{ $user->name ?? '—' }} &middot; {{ $user->email ?? '' }}</td></tr>
  <tr><td class="k">Catatan</td><td class="v" colspan="3" style="font-weight:normal;">{{ $dash($response->catatan) }}</td></tr>
</table>

<div class="foot">
  Kerahasiaan data dijamin Undang-Undang Nomor 16 Tahun 1997 tentang Statistik (Pasal 21).<br>
  Dicetak dari DataKita — BPS Kota Batam.
</div>

</body>
</html>
