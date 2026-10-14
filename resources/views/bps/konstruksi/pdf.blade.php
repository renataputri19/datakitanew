@php
    use App\Models\KonstruksiSurveyResponse as K;
    use App\Support\SibstrFormat;

    $dash    = fn ($v) => trim((string) $v) === '' ? '—' : $v;
    $pekerja = is_array($response->blok2_pekerja) ? $response->blok2_pekerja : [];
    $bm      = $response->totalBarangModal();
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
  }
  /* Keep a block's heading with at least the start of its table. */
  h2.blok { page-break-after: avoid; }

  table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
  th, td { border: 0.5px solid #d1d5db; padding: 3px 5px; vertical-align: top; }
  thead th { background: #f3f4f6; font-size: 6.5pt; font-weight: bold; text-align: center; color: #374151; }
  td.num { text-align: right; }
  tr.total { background: #fffbeb; }
  tr.total td { font-weight: bold; color: #b45309; }

  table.kv td { border: none; padding: 2px 4px; }
  table.kv td.k { color: #6b7280; width: 20%; }
  table.kv td.v { font-weight: bold; width: 30%; }

  .note { font-size: 6.5pt; color: #6b7280; font-style: italic; margin-bottom: 6px; }
  .warn { font-size: 7pt; color: #92400e; background: #fffbeb; border: .5px solid #fde68a; padding: 5px 8px; border-radius: 3px; margin-bottom: 8px; }
  .foot { margin-top: 10px; padding-top: 6px; border-top: 0.5px solid #e5e7eb; font-size: 6pt; color: #6b7280; text-align: center; line-height: 1.5; }
</style>
</head>
<body>

<div class="header">
  <div class="org">Badan Pusat Statistik Kota Batam &middot; DataKita</div>
  <div class="main">Data Survei Perusahaan Konstruksi</div>
  <div class="sub">
    {{ $dash($response->nama_perusahaan) }} &middot; {{ $periode }} &middot;
    {{ $response->is_completed ? 'Selesai' : 'Dalam proses (' . $response->completionPercent() . '%)' }} &middot;
    terakhir disimpan {{ $completedAt }}
  </div>
</div>

<h2 class="blok">UPDATING STATUS PERUSAHAAN</h2>
<table class="kv">
  <tr>
    <th>Status Perusahaan</th><td>{{ K::STATUS_PERUSAHAAN[$response->status_perusahaan] ?? '—' }}</td>
    <th>Kesediaan Responden</th><td>{{ K::KESEDIAAN_RESPONDEN[$response->kesediaan_responden] ?? '—' }}</td>
  </tr>
  <tr>
    <th>Keberadaan Proyek</th><td colspan="3">{{ K::KEBERADAAN_PROYEK[$response->keberadaan_proyek] ?? '—' }}</td>
  </tr>
</table>

@if($response->stoppedAtStatus())
  <p class="warn">
    Pencacahan berhenti pada blok ini — perusahaan tidak aktif atau responden tidak memberikan
    jawaban. Blok I sampai Blok IX tidak diisi.
  </p>
@elseif(!$response->proyekBlokApplies())
  <p class="warn">
    Responden menyatakan <b>tidak ada proyek konstruksi</b> sepanjang tahun ini, sehingga Blok III
    sampai Blok IX dilewati. Blok II tetap diisi karena menanyakan pekerja tetap dan kontrak,
    yang tidak bergantung pada ada tidaknya proyek.
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
    <td class="k">Alamat Perusahaan</td>
    <td class="v" colspan="3">
      {{ $dash($response->alamat_perusahaan) }}
      @if($response->rt || $response->rw) &middot; RT {{ $dash($response->rt) }}/RW {{ $dash($response->rw) }} @endif
      @if($response->kode_pos) &middot; {{ $response->kode_pos }} @endif
    </td>
  </tr>
  @if(trim((string) $response->alamat_operasional) !== '')
  <tr><td class="k">Alamat Operasional</td><td class="v" colspan="3">{{ $response->alamat_operasional }}</td></tr>
  @endif
  <tr>
    <td class="k">Telepon / Website</td><td class="v">{{ $dash($response->nomor_telepon) }} &middot; {{ $dash($response->website) }}</td>
    <td class="k">E-mail</td><td class="v">{{ $dash($response->email_perusahaan) }}</td>
  </tr>
  <tr>
    <td class="k">Badan Hukum / Usaha</td>
    <td class="v">{{ K::BADAN_HUKUM[$response->badan_hukum] ?? '—' }}{{ $response->badan_hukum === '9' ? ' — ' . $dash($response->badan_hukum_lainnya) : '' }}</td>
    <td class="k">Status Penanaman Modal</td><td class="v">{{ K::STATUS_MODAL[$response->status_penanaman_modal] ?? '—' }}</td>
  </tr>
  <tr><td class="k">Kualifikasi</td><td class="v" colspan="3">{{ K::KUALIFIKASI[$response->kualifikasi] ?? '—' }}</td></tr>
</table>

<h2 class="blok">BLOK II — TENAGA KERJA &amp; PENGELUARAN BALAS JASA</h2>
<p class="note">Kondisi 31 Desember {{ $response->tahun }}. Nilai balas jasa dalam ribuan rupiah.</p>
<table>
  <thead><tr><th style="text-align:left;">Jenjang Pendidikan</th><th>Laki-Laki</th><th>Perempuan</th><th>Jumlah</th></tr></thead>
  <tbody>
    @php $totL = 0; $totP = 0; @endphp
    @foreach(K::PENDIDIKAN as $key => $label)
      @php
        $l = K::n($pekerja[$key]['l'] ?? null);
        $p = K::n($pekerja[$key]['p'] ?? null);
        $totL += $l; $totP += $p;
      @endphp
      <tr>
        <td>{{ $loop->iteration }}. {{ $label }}</td>
        <td class="num">{{ SibstrFormat::idr($l) }}</td>
        <td class="num">{{ SibstrFormat::idr($p) }}</td>
        <td class="num">{{ SibstrFormat::idr($l + $p) }}</td>
      </tr>
    @endforeach
    <tr class="total">
      <td>JUMLAH PEKERJA TETAP DAN KONTRAK</td>
      <td class="num">{{ SibstrFormat::idr($totL) }}</td>
      <td class="num">{{ SibstrFormat::idr($totP) }}</td>
      <td class="num">{{ SibstrFormat::idr($totL + $totP) }}</td>
    </tr>
  </tbody>
</table>

<table>
  <thead><tr><th style="text-align:left;">Rincian Balas Jasa</th><th style="width:20%;">Nilai (000 Rp)</th></tr></thead>
  <tbody>
    @foreach(K::BALAS_JASA as $key => $label)
      <tr><td>{{ $loop->iteration }}. {{ $label }}</td><td class="num">{{ SibstrFormat::idr(($response->blok2_balas_jasa ?? [])[$key] ?? null) }}</td></tr>
    @endforeach
    <tr class="total"><td>JUMLAH [ 1 + 2 + … + 6 ]</td><td class="num">{{ SibstrFormat::idr($response->totalBalasJasa()) }}</td></tr>
  </tbody>
</table>

@if($response->proyekBlokApplies())
<h2 class="blok">BLOK III — KEPEMILIKAN MODAL PERUSAHAAN</h2>
<table>
  <thead><tr><th style="text-align:left;">Sumber Modal</th><th style="width:20%;">Persentase</th></tr></thead>
  <tbody>
    @foreach(K::SUMBER_MODAL as $key => $label)
      <tr><td>{{ $loop->iteration }}. {{ $label }}</td><td class="num">{{ SibstrFormat::pct(($response->blok3_modal ?? [])[$key] ?? null) }}</td></tr>
    @endforeach
    <tr class="total"><td>JUMLAH</td><td class="num">{{ SibstrFormat::pct($response->totalModal()) }}</td></tr>
  </tbody>
</table>
@if(trim((string) $response->modal_asing_negara) !== '')
  <p class="note">Negara dengan kepemilikan modal asing terbesar: <strong>{{ $response->modal_asing_negara }}</strong></p>
@endif

<h2 class="blok">BLOK IV — PENGELUARAN OPERASIONAL</h2>
<table>
  <thead><tr><th style="text-align:left;">Rincian</th><th style="width:20%;">Nilai (000 Rp)</th></tr></thead>
  <tbody>
    @foreach(K::PENGELUARAN as $key => $label)
      <tr><td>{{ $loop->iteration }}. {{ $label }}</td><td class="num">{{ SibstrFormat::idr(($response->blok4_pengeluaran ?? [])[$key] ?? null) }}</td></tr>
    @endforeach
    <tr class="total"><td>JUMLAH [ 1 + 2 + … + 8 ]</td><td class="num">{{ SibstrFormat::idr($response->totalPengeluaran()) }}</td></tr>
  </tbody>
</table>

<h2 class="blok">BLOK V — PENAMBAHAN &amp; PENGURANGAN BARANG MODAL</h2>
<table>
  <thead><tr><th style="text-align:left;">Jenis Barang Modal</th><th style="width:18%;">Penambahan</th><th style="width:18%;">Pengurangan</th></tr></thead>
  <tbody>
    @foreach(K::BARANG_MODAL as $key => $label)
      <tr>
        <td>
          {{ $loop->iteration }}. {{ $label }}@if($key === 'lainnya' && trim((string) $response->barang_modal_lainnya) !== '') — {{ $response->barang_modal_lainnya }}@endif
        </td>
        <td class="num">{{ SibstrFormat::idr(($response->blok5_barang_modal ?? [])[$key]['tambah'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr(($response->blok5_barang_modal ?? [])[$key]['kurang'] ?? null) }}</td>
      </tr>
    @endforeach
    <tr class="total">
      <td>JUMLAH [ 1 + 2 + … + 6 ]</td>
      <td class="num">{{ SibstrFormat::idr($bm['tambah']) }}</td>
      <td class="num">{{ SibstrFormat::idr($bm['kurang']) }}</td>
    </tr>
  </tbody>
</table>

<h2 class="blok">BLOK VI — PENDAPATAN &amp; PENGELUARAN PEKERJAAN KONSTRUKSI</h2>
<p class="note">
  {{ count($proyekRows) }} proyek. Nilai dalam ribuan rupiah.
  Kolom (17) = {[(7) − (8)] × (12 − 11) ÷ 100} + (14).
</p>
@if($proyekRows === [])
  <p class="note">Belum ada proyek yang diisi.</p>
@else
<table style="font-size:6pt;">
  <thead>
    <tr>
      <th>No</th>
      <th style="text-align:left;">Nama Pekerjaan / KBLI (2)</th>
      <th style="text-align:left;">Lokasi (3)</th>
      <th>Dana (4)</th>
      <th>Vol (5)</th>
      <th>Sat (6)</th>
      <th>Kontrak (7)</th>
      <th>Subkon (8)</th>
      <th>Mulai (9)</th>
      <th>Selesai (10)</th>
      <th>% s/d {{ (int) $response->tahun - 1 }} (11)</th>
      <th>% s/d {{ $response->tahun }} (12)</th>
      <th>Bahan Persh. (13)</th>
      <th>Bahan Pemilik (14)</th>
      <th>Upah Harian (15)</th>
      <th>Rata Upah (16)</th>
      <th>Nilai Konstruksi (17)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($proyekRows as $i => $row)
      <tr>
        <td class="num">{{ $i + 1 }}</td>
        <td>{{ $dash($row['nama_pekerjaan'] ?? '') }}@if(trim((string) ($row['kbli'] ?? '')) !== '')<br><span style="color:#6b7280;">KBLI {{ $row['kbli'] }}</span>@endif</td>
        <td>{{ $dash($row['kabkota_lokasi'] ?? '') }}<br><span style="color:#6b7280;">{{ $row['provinsi_lokasi'] ?? '' }}</span></td>
        <td>{{ $row['sumber_dana'] ?? '—' }}</td>
        <td class="num">{{ SibstrFormat::dec($row['volume'] ?? null) }}</td>
        <td>{{ $dash($row['satuan'] ?? '') }}</td>
        <td class="num">{{ SibstrFormat::idr($row['nilai_kontrak'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['nilai_subkontrak'] ?? null) }}</td>
        <td>{{ $dash($row['mulai'] ?? '') }}</td>
        <td>{{ $dash($row['selesai'] ?? '') }}</td>
        <td class="num">{{ SibstrFormat::pct($row['realisasi_awal'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::pct($row['realisasi_akhir'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['bahan_perusahaan'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['bahan_pemilik'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['upah_harian'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr($row['upah_rata_rata'] ?? null) }}</td>
        <td class="num">{{ SibstrFormat::idr(K::nilaiKonstruksi($row)) }}</td>
      </tr>
    @endforeach
    <tr class="total">
      <td colspan="6" style="text-align:right;">JUMLAH</td>
      <td class="num">{{ SibstrFormat::idr($proyekTotal['nilai_kontrak']) }}</td>
      <td colspan="5"></td>
      <td class="num">{{ SibstrFormat::idr($proyekTotal['bahan_perusahaan']) }}</td>
      <td class="num">{{ SibstrFormat::idr($proyekTotal['bahan_pemilik']) }}</td>
      <td class="num">{{ SibstrFormat::idr($proyekTotal['upah_harian']) }}</td>
      <td></td>
      <td class="num">{{ SibstrFormat::idr($proyekTotal['nilai_konstruksi']) }}</td>
    </tr>
  </tbody>
</table>
<p class="note">
  Sumber dana:
  @foreach(K::SUMBER_DANA as $code => $label){{ $code }} = {{ $label }}@if(!$loop->last) &middot; @endif @endforeach
</p>
@endif

<table class="kv">
  <tr>
    <td class="k">Rata-rata % bahan/material</td><td class="v">{{ SibstrFormat::pct($response->pct_bahan) }}</td>
    <td class="k">Rata-rata % upah harian</td><td class="v">{{ SibstrFormat::pct($response->pct_upah) }}</td>
  </tr>
  <tr>
    <td class="k">Sumber modal proyek</td>
    <td class="v" colspan="3">
      @foreach(K::MODAL_PROYEK as $key => $label){{ $label }}: {{ SibstrFormat::pct(($response->blok6_sumber_modal ?? [])[$key] ?? null) }}@if(!$loop->last) &middot; @endif @endforeach
    </td>
  </tr>
</table>

<h2 class="blok">BLOK VII — STRUKTUR BAHAN / MATERIAL PROYEK UTAMA</h2>
@if($bahanRows === [])
  <p class="note">Belum ada bahan/material yang diisi.</p>
@else
<table>
  <thead><tr><th style="width:5%;">No</th><th style="text-align:left;">Jenis Bahan / Material</th><th style="width:20%;">Nilai (000 Rp)</th></tr></thead>
  <tbody>
    @foreach($bahanRows as $i => $row)
      <tr><td class="num">{{ $i + 1 }}</td><td>{{ $dash($row['nama'] ?? '') }}</td><td class="num">{{ SibstrFormat::idr($row['nilai'] ?? null) }}</td></tr>
    @endforeach
    <tr class="total"><td colspan="2" style="text-align:right;">JUMLAH</td><td class="num">{{ SibstrFormat::idr($response->totalBahan()) }}</td></tr>
  </tbody>
</table>
@endif

<h2 class="blok">BLOK VIII — PENDAPATAN LAIN</h2>
<table>
  <thead><tr><th style="text-align:left;">Jenis Pendapatan</th><th style="width:20%;">Nilai (000 Rp)</th></tr></thead>
  <tbody>
    @foreach(K::PENDAPATAN_LAIN as $key => $label)
      <tr>
        <td>{{ $loop->iteration }}. {{ $label }}@if($key === 'lainnya' && trim((string) $response->pendapatan_lain_lainnya) !== '') — {{ $response->pendapatan_lain_lainnya }}@endif</td>
        <td class="num">{{ SibstrFormat::idr(($response->blok8_pendapatan_lain ?? [])[$key] ?? null) }}</td>
      </tr>
    @endforeach
    <tr class="total"><td>JUMLAH</td><td class="num">{{ SibstrFormat::idr($response->totalPendapatanLain()) }}</td></tr>
  </tbody>
</table>

<h2 class="blok">BLOK IX — RINGKASAN PENGELUARAN &amp; PENDAPATAN</h2>
<p class="note">Dihitung dari Blok II, IV, VI, VII dan VIII — tidak diisi responden.</p>
<table>
  <thead><tr><th style="text-align:left;">Rincian</th><th style="width:22%;">Nilai (000 Rp)</th></tr></thead>
  <tbody>
    <tr class="total"><td colspan="2">A. RINCIAN PENGELUARAN</td></tr>
    <tr><td>1. Balas Jasa dan Upah Pekerja</td><td class="num">{{ SibstrFormat::idr($ringkasan['pengeluaran']['balas_jasa']) }}</td></tr>
    <tr><td>2. Nilai Pemakaian Bahan/Material</td><td class="num">{{ SibstrFormat::idr($ringkasan['pengeluaran']['bahan']) }}</td></tr>
    <tr><td>3. Pengeluaran Operasional</td><td class="num">{{ SibstrFormat::idr($ringkasan['pengeluaran']['operasional']) }}</td></tr>
    <tr class="total"><td>JUMLAH A [ 1 + 2 + 3 ]</td><td class="num">{{ SibstrFormat::idr($ringkasan['total_a']) }}</td></tr>
    <tr class="total"><td colspan="2">B. RINCIAN PENDAPATAN</td></tr>
    <tr><td>1. Nilai Konstruksi yang Diselesaikan</td><td class="num">{{ SibstrFormat::idr($ringkasan['pendapatan']['nilai_konstruksi']) }}</td></tr>
    <tr><td>2. Nilai dari Pendapatan Lain</td><td class="num">{{ SibstrFormat::idr($ringkasan['pendapatan']['pendapatan_lain']) }}</td></tr>
    <tr class="total"><td>JUMLAH B [ 1 + 2 ]</td><td class="num">{{ SibstrFormat::idr($ringkasan['total_b']) }}</td></tr>
    <tr class="total">
      <td>C. Persentase Keuntungan Terhadap Pengeluaran (B/A − 1) × 100 %</td>
      <td class="num">{{ $ringkasan['margin_pct'] === null ? '—' : number_format($ringkasan['margin_pct'], 2, ',', '.') . ' %' }}</td>
    </tr>
  </tbody>
</table>

@endif {{-- proyekBlokApplies: Blok III–IX --}}
@endif {{-- continuesPastStatus: Blok I–IX --}}

<h2 class="blok">BLOK X — METODE PENDATAAN, CATATAN &amp; PEMBERI JAWABAN</h2>
<table class="kv">
  <tr>
    <td class="k">Metode Pendataan Diinginkan</td><td class="v">{{ K::METODE_PENDATAAN[$response->metode_pendataan] ?? '—' }}</td>
    <td class="k">Nama Pemberi Jawaban</td><td class="v">{{ $dash($response->pj_nama) }}</td>
  </tr>
  <tr>
    <td class="k">Jabatan</td><td class="v">{{ $dash($response->pj_jabatan) }}</td>
    <td class="k">HP / E-mail</td><td class="v">{{ $dash($response->pj_hp) }} &middot; {{ $dash($response->pj_email) }}</td>
  </tr>
  <tr>
    <td class="k">Akun Pengisi</td>
    <td class="v" colspan="3">{{ $user->name ?? '—' }} &middot; {{ $user->email ?? '' }}</td>
  </tr>
  <tr><td class="k">Catatan</td><td class="v" colspan="3" style="font-weight:normal;">{{ $dash($response->catatan) }}</td></tr>
</table>

<div class="foot">
  Kerahasiaan data dijamin Undang-Undang Nomor 16 Tahun 1997 tentang Statistik (Pasal 21).<br>
  Dicetak dari DataKita — BPS Kota Batam.
</div>

</body>
</html>
