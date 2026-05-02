<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #111; }
  .page { padding: 18mm 18mm 12mm 18mm; }
  .header { text-align: center; border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 10px; }
  .header h1 { font-size: 12pt; font-weight: bold; color: #1d4ed8; }
  .header h2 { font-size: 10pt; font-weight: bold; margin-top: 2px; }
  .header p  { font-size: 8pt; color: #555; margin-top: 2px; }
  .badge { display: inline-block; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;
           border-radius: 4px; padding: 2px 8px; font-size: 8pt; font-weight: bold; margin-top: 4px; }
  .section { margin-bottom: 10px; }
  .section-title { font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.05em;
                   color: #1d4ed8; border-bottom: 1px solid #bfdbfe; padding-bottom: 3px; margin-bottom: 6px; }
  .grid2 { display: table; width: 100%; border-collapse: collapse; }
  .col { display: table-cell; width: 50%; vertical-align: top; padding: 2px 6px 2px 0; }
  .row { display: table-row; }
  .label { font-size: 7.5pt; color: #555; margin-bottom: 1px; }
  .value { font-size: 8.5pt; font-weight: bold; color: #111; word-break: break-word; }
  .value.empty { color: #aaa; font-weight: normal; font-style: italic; }
  .table { width: 100%; border-collapse: collapse; font-size: 8.5pt; margin-top: 4px; }
  .table th { background: #eff6ff; color: #1d4ed8; font-weight: bold; padding: 4px 6px;
              border: 1px solid #bfdbfe; text-align: left; }
  .table td { padding: 3px 6px; border: 1px solid #e5e7eb; }
  .table tr:nth-child(even) td { background: #f9fafb; }
  .total-row td { background: #f0fdf4 !important; font-weight: bold; color: #166534; }
  .note { font-size: 7.5pt; color: #666; font-style: italic; margin-top: 4px; }
  .kp-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 6px 10px; margin-top: 6px; }
  .sign-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  .sign-table th { border: 1px solid #bfdbfe; background: #eff6ff; font-size: 8pt; padding: 4px;
                   text-align: center; color: #1d4ed8; }
  .sign-table td { border: 1px solid #e5e7eb; font-size: 8.5pt; padding: 3px 6px; }
  .sign-space { height: 28px; }
  .footer { text-align: center; font-size: 7pt; color: #888; border-top: 1px solid #e5e7eb;
            padding-top: 6px; margin-top: 10px; }
  .page-break { page-break-before: always; }
</style>
</head>
<body>
<div class="page">

{{-- ══ HEADER ════════════════════════════════════════════════════════════ --}}
<div class="header">
  <h1>SENSUS EKONOMI 2026 — SE2026-L.UB</h1>
  <h2>PENDATAAN LENGKAP USAHA/PERUSAHAAN</h2>
  <p>Badan Pusat Statistik — Republik Indonesia</p>
  <div class="badge">&#10003; SURVEI SELESAI — {{ now()->format('d/m/Y H:i') }}</div>
</div>

{{-- ══ BLOK I-A: IDENTITAS & LOKASI ════════════════════════════════════════ --}}
<div class="section">
  <div class="section-title">BLOK I-A — Identitas &amp; Lokasi (R1–R8)</div>

  <div class="grid2">
    <div class="row">
      <div class="col">
        <div class="label">1. Provinsi</div>
        <div class="value">{{ $response->provinsi ?: '—' }}</div>
      </div>
      <div class="col">
        <div class="label">2. Kabupaten/Kota</div>
        <div class="value">{{ $response->kabupaten_kota ?: '—' }}</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">3. Kecamatan</div>
        <div class="value">{{ $response->kecamatan ?: '—' }}</div>
      </div>
      <div class="col">
        <div class="label">4. Kelurahan/Desa/Nagari</div>
        <div class="value">{{ $response->kelurahan_desa ?: '—' }}</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">5a. Nama Perusahaan</div>
        <div class="value">{{ $response->nama_perusahaan ?: '—' }}</div>
      </div>
      <div class="col">
        <div class="label">5b. Nama Komersial</div>
        <div class="value">{{ $response->nama_komersial ?: '—' }}</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">5c. Alamat</div>
        <div class="value">{{ $response->alamat_perusahaan ?: '—' }}
          @if($response->rt || $response->rw) — RT {{ $response->rt }}/RW {{ $response->rw }} @endif
          @if($response->kode_pos) · Kode Pos {{ $response->kode_pos }} @endif</div>
      </div>
      <div class="col">
        <div class="label">5d. Jenis Kawasan</div>
        @php $kawasanMap = [1=>'KEK',2=>'Kawasan Industri',3=>'Stasiun',4=>'Bandara',5=>'Pelabuhan',6=>'Terminal',7=>'Rest Area Tol',8=>'Sentra Ekonomi Desa',9=>'Kawasan Usaha Lain',10=>'Di Luar Kawasan']; @endphp
        <div class="value">{{ isset($kawasanMap[$response->jenis_kawasan]) ? $response->jenis_kawasan.'. '.$kawasanMap[$response->jenis_kawasan] : '—' }}
          @if($response->nama_kawasan) — {{ $response->nama_kawasan }} @endif</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">5c. Nomor HP/WA</div>
        <div class="value">{{ $response->nomor_hp ?: '—' }}</div>
      </div>
      <div class="col">
        <div class="label">5c. Email Perusahaan</div>
        <div class="value">{{ $response->email_perusahaan ?: '—' }}</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">6a. Memiliki NIB?</div>
        <div class="value">{{ $response->has_nib == 1 ? 'Ya — '.$response->nib : ($response->has_nib == 2 ? 'Tidak' : '—') }}</div>
      </div>
      <div class="col">
        <div class="label">7a. Status Badan Usaha</div>
        @php $sbuMap = [1=>'Perseroan (PT/NV dll)',2=>'Yayasan',3=>'Koperasi',4=>'Dana Pensiun',5=>'Perum/Perumda',6=>'BUM Desa',7=>'CV',8=>'Firma (Fa)',9=>'Maatschap',10=>'Kantor Perwakilan LN',11=>'Badan Usaha LN',12=>'Badan Usaha Lainnya',13=>'Bukan Badan Usaha']; @endphp
        <div class="value">{{ isset($sbuMap[$response->status_badan_usaha]) ? $response->status_badan_usaha.'. '.$sbuMap[$response->status_badan_usaha] : '—' }}</div>
      </div>
    </div>
    @if($response->status_badan_usaha == 3)
    <div class="row">
      <div class="col">
        <div class="label">7b. KDKMP?</div>
        <div class="value">{{ $response->is_koperasi_kdkmp == 1 ? 'Ya' : ($response->is_koperasi_kdkmp == 2 ? 'Tidak' : '—') }}</div>
      </div>
      <div class="col">
        <div class="label">7c. Jenis Koperasi</div>
        <div class="value">{{ $response->jenis_koperasi == 1 ? 'Open Loop' : ($response->jenis_koperasi == 2 ? 'Close Loop' : '—') }}</div>
      </div>
    </div>
    @endif
    <div class="row">
      <div class="col">
        <div class="label">7d. Laporan Keuangan?</div>
        <div class="value">{{ $response->has_laporan_keuangan == 1 ? 'Ya' : ($response->has_laporan_keuangan == 2 ? 'Tidak' : '—') }}</div>
      </div>
      <div class="col"></div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">8a. Nama Pengusaha/PJ</div>
        <div class="value">{{ $response->nama_pengusaha ?: '—' }}</div>
      </div>
      <div class="col">
        <div class="label">8b/c/d. Jenis Kelamin / Umur / NIK</div>
        <div class="value">{{ $response->jenis_kelamin == 1 ? 'Laki-laki' : ($response->jenis_kelamin == 2 ? 'Perempuan' : '—') }} / {{ $response->umur ? $response->umur.' thn' : '—' }} / {{ $response->nik ?: '—' }}</div>
      </div>
    </div>
  </div>
</div>

{{-- ══ BLOK I-B: KEGIATAN & DIGITAL ═══════════════════════════════════════ --}}
<div class="section">
  <div class="section-title">BLOK I-B — Kegiatan &amp; Digital (R9–R14)</div>

  <div class="grid2">
    <div class="row">
      <div class="col">
        <div class="label">9a. Kegiatan Utama</div>
        <div class="value">{{ $response->kegiatan_utama ?: '—' }}</div>
      </div>
      <div class="col">
        <div class="label">9f. Produk Utama</div>
        <div class="value">{{ $response->produk_utama ?: '—' }}</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">9b1. Produksi barang di lokasi</div>
        <div class="value">{{ $response->produksi_di_lokasi == 1 ? 'Ya' : ($response->produksi_di_lokasi == 2 ? 'Tidak' : '—') }}</div>
      </div>
      <div class="col">
        <div class="label">9b2. Layanan makan minum</div>
        <div class="value">{{ $response->layanan_makan_minum == 1 ? 'Ya' : ($response->layanan_makan_minum == 2 ? 'Tidak' : '—') }}</div>
      </div>
    </div>
    @if($response->kode_kbli || $response->kategori_lapangan_usaha)
    <div class="row">
      <div class="col">
        <div class="label">9g. Kode KBLI</div>
        <div class="value">{{ $response->kode_kbli ?: '—' }}</div>
      </div>
      <div class="col">
        <div class="label">9h. Kategori Lapangan Usaha</div>
        <div class="value">{{ $response->kategori_lapangan_usaha ?: '—' }}</div>
      </div>
    </div>
    @endif
    <div class="row">
      <div class="col">
        <div class="label">10a. Jaringan Usaha</div>
        @php $jaringanMap = [1=>'Tunggal',2=>'Kantor Pusat',3=>'Cabang',4=>'Perwakilan',5=>'Pabrik',6=>'Unit Pembantu/Penunjang']; @endphp
        <div class="value">{{ isset($jaringanMap[$response->jaringan_usaha]) ? $response->jaringan_usaha.'. '.$jaringanMap[$response->jaringan_usaha] : '—' }}</div>
      </div>
      <div class="col">
        @if($response->jaringan_usaha == 2)
        <div class="label">10b. Jumlah Cabang/Unit</div>
        <div class="value">{{ $response->jumlah_cabang ?? '—' }}</div>
        @endif
      </div>
    </div>
  </div>

  @if(in_array($response->jaringan_usaha, [3,4,5,6]))
  <div class="kp-box">
    <strong>11. Informasi Kantor Pusat</strong><br>
    <div class="grid2" style="margin-top:4px;">
      <div class="row">
        <div class="col"><span class="label">Nama:</span> <span class="value">{{ $response->kp_nama ?: '—' }}</span></div>
        <div class="col"><span class="label">Negara:</span> <span class="value">{{ $response->kp_negara ?: '—' }}</span></div>
      </div>
      <div class="row">
        <div class="col"><span class="label">Alamat:</span> <span class="value">{{ $response->kp_alamat ?: '—' }}</span></div>
        <div class="col"><span class="label">Provinsi:</span> <span class="value">{{ $response->kp_provinsi ?: '—' }}</span></div>
      </div>
      <div class="row">
        <div class="col"><span class="label">Email:</span> <span class="value">{{ $response->kp_email ?: '—' }}</span></div>
        <div class="col"><span class="label">Kab/Kota:</span> <span class="value">{{ $response->kp_kabkota ?: '—' }}</span></div>
      </div>
    </div>
  </div>
  @endif

  @if($response->jaringan_usaha != 6)
  <div class="grid2" style="margin-top:6px;">
    <div class="row">
      <div class="col">
        <div class="label">12a. Menggunakan Internet?</div>
        <div class="value">{{ $response->uses_internet == 1 ? 'Ya' : ($response->uses_internet == 2 ? 'Tidak' : '—') }}</div>
      </div>
      <div class="col">
        <div class="label">12c. Teknologi Digital (AI/IoT/dll)?</div>
        <div class="value">{{ $response->uses_teknologi_digital == 1 ? 'Ya' : ($response->uses_teknologi_digital == 2 ? 'Tidak' : '—') }}</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        @php $lingMap = [1=>'Ya, seluruhnya',2=>'Ya, sebagian',3=>'Tidak sama sekali']; @endphp
        <div class="label">13a. Produk Ramah Lingkungan?</div>
        <div class="value">{{ $lingMap[$response->produk_ramah_lingkungan] ?? '—' }}</div>
      </div>
      <div class="col">
        <div class="label">13b. Input/Pembelian Ramah Lingkungan?</div>
        <div class="value">{{ $response->uses_input_lingkungan == 1 ? 'Ya' : ($response->uses_input_lingkungan == 2 ? 'Tidak' : '—') }}</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">14. Produk Karya Seni/Sastra/Desain/Teknologi/Warisan Budaya?</div>
        <div class="value">{{ $response->uses_karya_seni == 1 ? 'Ya' : ($response->uses_karya_seni == 2 ? 'Tidak' : '—') }}</div>
      </div>
      <div class="col"></div>
    </div>
  </div>
  @else
  <div class="note" style="margin-top:4px;">⚠ Unit Pembantu/Penunjang: Pertanyaan 12–25 tidak berlaku (PENDATAAN SELESAI di R11).</div>
  @endif
</div>

@if($response->jaringan_usaha != 6)
{{-- ══ BLOK I-C: SERTIFIKASI & KEMITRAAN ══════════════════════════════════ --}}
<div class="section">
  <div class="section-title">BLOK I-C — Sertifikasi &amp; Kemitraan (R15–R19)</div>
  <div class="grid2">
    <div class="row">
      <div class="col">
        @php $halalMap = [1=>'Ya, oleh BPJPH',2=>'Ya, bukan BPJPH',3=>'Tidak']; @endphp
        <div class="label">15a. Sertifikat Halal</div>
        <div class="value">{{ $halalMap[$response->sertifikat_halal] ?? '—' }}
          @if($response->sertifikat_halal == 1) ({{ $response->jumlah_produk_halal_bpjph ?? 0 }} varian halal / {{ $response->jumlah_produk_belum_halal_bpjph ?? 0 }} belum) @endif</div>
      </div>
      <div class="col">
        @php $izinMap = [1=>'Ya, oleh BPOM',2=>'Ya, bukan BPOM',3=>'Tidak']; @endphp
        <div class="label">16a. Izin Edar</div>
        <div class="value">{{ $izinMap[$response->izin_edar] ?? '—' }}
          @if($response->izin_edar == 1) ({{ $response->jumlah_produk_izin_edar_bpom ?? 0 }} izin / {{ $response->jumlah_produk_tanpa_izin_edar_bpom ?? 0 }} belum) @endif</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">17. Bermitra KDKMP?</div>
        <div class="value">{{ $response->bermitra_kdkmp == 1 ? 'Ya' : ($response->bermitra_kdkmp == 2 ? 'Tidak' : '—') }}</div>
      </div>
      <div class="col">
        @php $mbgMap = [1=>'Ya (SPPG)',2=>'Ya (Supplier)',3=>'Ya (Penerima Manfaat)',4=>'Ya (Lainnya)',5=>'Tidak terlibat']; @endphp
        <div class="label">18. Program MBG</div>
        <div class="value">{{ $mbgMap[$response->terlibat_mbg] ?? '—' }}</div>
      </div>
    </div>
    <div class="row">
      <div class="col">
        <div class="label">19a. Transaksi Barang dg Bukan Penduduk RI</div>
        <div class="value">{{ $response->ekspor_impor_barang == 1 ? 'Ya' : ($response->ekspor_impor_barang == 2 ? 'Tidak' : '—') }}</div>
      </div>
      <div class="col">
        <div class="label">19b. Transaksi Jasa dg Bukan Penduduk RI</div>
        <div class="value">{{ $response->ekspor_impor_jasa == 1 ? 'Ya' : ($response->ekspor_impor_jasa == 2 ? 'Tidak' : '—') }}</div>
      </div>
    </div>
  </div>
</div>

{{-- ══ BLOK I-D: PEKERJA & KEUANGAN ═══════════════════════════════════════ --}}
<div class="section page-break">
  <div class="section-title">BLOK I-D — Pekerja &amp; Keuangan (R20–R25)</div>

  <div class="grid2">
    <div class="row">
      <div class="col">
        <div class="label">20. Jumlah Pekerja (per 31 Des 2025)</div>
        <div class="value">L: {{ $response->pekerja_laki ?? 0 }} · P: {{ $response->pekerja_perempuan ?? 0 }} · Total: {{ $response->total_pekerja ?? 0 }} orang</div>
      </div>
      <div class="col">
        <div class="label">21. Tahun Mulai Beroperasi</div>
        <div class="value">{{ $response->tahun_beroperasi ?: '—' }}</div>
      </div>
    </div>
  </div>

  @php
    function rp($v) { return 'Rp '.number_format($v ?? 0, 0, ',', '.'); }
  @endphp

  <table class="table" style="margin-top:6px;">
    <tr><th colspan="2">22. Rincian Pengeluaran Tahun 2025</th></tr>
    <tr><td>a. Upah/gaji &amp; jaminan sosial</td><td>{{ rp($response->pengeluaran_upah_gaji) }}</td></tr>
    <tr><td>b. Biaya produksi</td><td>{{ rp($response->pengeluaran_biaya_produksi) }}</td></tr>
    <tr><td>c. Pembelian barang terjual</td><td>{{ rp($response->pengeluaran_pembelian_barang) }}</td></tr>
    <tr><td>d. Biaya operasional</td><td>{{ rp($response->pengeluaran_operasional) }}</td></tr>
    <tr><td>e. Biaya nonoperasional</td><td>{{ rp($response->pengeluaran_nonoperasional) }}</td></tr>
    <tr class="total-row"><td><strong>f. Total Pengeluaran</strong></td><td><strong>{{ rp($response->total_pengeluaran) }}</strong></td></tr>
  </table>

  <table class="table" style="margin-top:6px;">
    <tr><th colspan="2">23. Nilai Produksi/Penjualan/Pendapatan Tahun 2025</th></tr>
    <tr><td>a. Nilai produksi/penjualan/pendapatan barang &amp; jasa</td><td>{{ rp($response->nilai_produksi_barang_jasa) }}</td></tr>
    <tr><td>b. Pendapatan lainnya</td><td>{{ rp($response->pendapatan_lainnya) }}</td></tr>
    <tr class="total-row"><td><strong>c. Total</strong></td><td><strong>{{ rp($response->total_nilai_produksi) }}</strong></td></tr>
    <tr><td>d. % Pendapatan dari usaha online</td><td>{{ $response->persen_pendapatan_online ?? 0 }}%</td></tr>
  </table>

  <table class="table" style="margin-top:6px;">
    <tr><th colspan="2">24. Nilai Aset per 31 Desember 2025</th></tr>
    <tr><td>a. Tanah dan bangunan</td><td>{{ rp($response->nilai_aset_tanah_bangunan) }}</td></tr>
    <tr><td>b. Selain tanah dan bangunan</td><td>{{ rp($response->nilai_aset_lainnya) }}</td></tr>
    <tr class="total-row"><td><strong>c. Total Aset</strong></td><td><strong>{{ rp($response->nilai_total_aset) }}</strong></td></tr>
    <tr><td>d. Luas tanah (m²)</td><td>{{ number_format($response->luas_tanah ?? 0, 0, ',', '.') }} m²</td></tr>
  </table>

  <table class="table" style="margin-top:6px;">
    <tr>
      <th>25. Kepemilikan Modal (31 Des 2025)</th>
      <th style="width:80px;">Persentase</th>
    </tr>
    <tr><td>a. Pribadi/Perorangan</td><td>{{ $response->modal_pribadi ?? 0 }}%</td></tr>
    <tr><td>b. Lembaga Nonprofit</td><td>{{ $response->modal_nonprofit ?? 0 }}%</td></tr>
    <tr><td>c. Korporasi Publik</td><td>{{ $response->modal_korporasi_publik ?? 0 }}%</td></tr>
    <tr><td>d. Korporasi Nonpublik</td><td>{{ $response->modal_korporasi_nonpublik ?? 0 }}%</td></tr>
    <tr><td>e. Pemerintah</td><td>{{ $response->modal_pemerintah ?? 0 }}%</td></tr>
    <tr><td>f. Asing</td><td>{{ $response->modal_asing ?? 0 }}%</td></tr>
    <tr class="total-row">
      <td><strong>g. Total</strong></td>
      <td><strong>{{ array_sum([$response->modal_pribadi ?? 0, $response->modal_nonprofit ?? 0, $response->modal_korporasi_publik ?? 0, $response->modal_korporasi_nonpublik ?? 0, $response->modal_pemerintah ?? 0, $response->modal_asing ?? 0]) }}%</strong></td>
    </tr>
  </table>
</div>
@endif

{{-- ══ BLOK II: CATATAN ═════════════════════════════════════════════════════ --}}
@if($response->catatan)
<div class="section">
  <div class="section-title">BLOK II — Catatan</div>
  <div class="value" style="white-space:pre-wrap;">{{ $response->catatan }}</div>
</div>
@endif

{{-- ══ BLOK III: KETERANGAN PEMBERI JAWABAN ═══════════════════════════════ --}}
<div class="section">
  <div class="section-title">BLOK III — Keterangan Pemberi Jawaban</div>
  <table class="sign-table">
    <tr>
      <th>PPL (Petugas Pencacah Lapangan)</th>
      <th>PML (Pengawas Mula Lapangan)</th>
      <th>Responden</th>
    </tr>
    <tr>
      <td>
        Nama: {{ $response->ppl_nama ?: '—' }}<br>
        NIP/NMS: {{ $response->ppl_nip ?: '—' }}<br>
        Telepon: {{ $response->ppl_telepon ?: '—' }}<br>
        Email: {{ $response->ppl_email ?: '—' }}<br>
        Tanggal: {{ $response->ppl_tanggal?->format('d/m/Y') ?: '—' }}<br>
        <div class="sign-space"></div>
        <span style="font-size:7pt;color:#888;">Tanda Tangan</span>
      </td>
      <td>
        Nama: {{ $response->pml_nama ?: '—' }}<br>
        NIP/NMS: {{ $response->pml_nip ?: '—' }}<br>
        Telepon: {{ $response->pml_telepon ?: '—' }}<br>
        Email: {{ $response->pml_email ?: '—' }}<br>
        Tanggal: {{ $response->pml_tanggal?->format('d/m/Y') ?: '—' }}<br>
        <div class="sign-space"></div>
        <span style="font-size:7pt;color:#888;">Tanda Tangan</span>
      </td>
      <td>
        Nama: {{ $response->resp_nama ?: '—' }}<br>
        NIP/NMS: {{ $response->resp_nip ?: '—' }}<br>
        Telepon: {{ $response->resp_telepon ?: '—' }}<br>
        Email: {{ $response->resp_email ?: '—' }}<br>
        Tanggal: {{ $response->resp_tanggal?->format('d/m/Y') ?: '—' }}<br>
        <div class="sign-space"></div>
        <span style="font-size:7pt;color:#888;">Tanda Tangan</span>
      </td>
    </tr>
  </table>
</div>

<div class="footer">
  Dokumen ini digenerate otomatis oleh sistem survei online SE2026-L.UB &nbsp;|&nbsp;
  Kerahasiaan data dijamin oleh UU No. 16 Tahun 1997 Pasal 21 &nbsp;|&nbsp;
  Dicetak: {{ now()->format('d/m/Y H:i:s') }}
</div>

</div>
</body>
</html>
