@php
    // ── Value maps (mirror bps/ub/show.blade.php) ──────────────────────────────
    $yn  = [1 => 'Ya', 2 => 'Tidak'];
    $kawasanMap = [1=>'Kawasan Ekonomi Khusus (KEK)',2=>'Kawasan Industri (KI)',3=>'Stasiun',4=>'Bandara',5=>'Pelabuhan',6=>'Terminal',7=>'Rest area jalan tol',8=>'Kawasan sentra ekonomi perdesaan/kelurahan',9=>'Kawasan usaha lainnya',10=>'Di luar kawasan'];
    $nibAlasanMap = [1=>'Dalam proses pembuatan NIB',2=>'Pengurusan NIB rumit',3=>'Tidak memerlukan NIB',4=>'Tidak tahu tentang NIB',5=>'Lainnya'];
    $sbuMap = [1=>'Perseroan (PT/NV/PT Persero/PT Tbk/Perseroan Daerah/Perseroan Perorangan)',2=>'Yayasan',3=>'Koperasi',4=>'Dana Pensiun',5=>'Perum/Perumda',6=>'BUM Desa',7=>'Persekutuan Komanditer (CV)',8=>'Persekutuan Firma (Fa)',9=>'Persekutuan Perdata (Maatschap)',10=>'Kantor Perwakilan Luar Negeri',11=>'Badan Usaha Luar Negeri',12=>'Badan Usaha Lainnya (BLU, PTN-BH dll)',13=>'Bukan Badan Usaha'];
    $jkMap  = [1=>'Laki-laki', 2=>'Perempuan'];
    $jaringanMap = [1=>'Perusahaan Tunggal',2=>'Kantor Pusat (memiliki cabang)',3=>'Cabang/Unit dari Kantor Pusat dalam negeri',4=>'Perwakilan dari Kantor Pusat luar negeri',5=>'Pabrik/Unit Produksi',6=>'Unit Pembantu/Penunjang'];
    $lokasiMap = [1=>'Apotek',2=>'Swalayan',3=>'Los Pasar',4=>'Toko, ruko, dan sejenisnya',5=>'Kedai, stan, tenda',6=>'Bar',7=>'Kelab malam, diskotek',8=>'Kafe',9=>'Restoran, warung makan',10=>'Keliling',11=>'Daring (online)'];
    $klasAkomodasiMap = [1=>'Hotel Bintang 1',2=>'Hotel Bintang 2',3=>'Hotel Bintang 3',4=>'Hotel Bintang 4',5=>'Hotel Bintang 5',6=>'Lainnya (hotel nonbintang, vila, dll)'];
    $sertHalalMap = [1=>'Ya, oleh BPJPH', 2=>'Ya, bukan oleh BPJPH', 3=>'Belum/tidak', 4=>'Dalam proses'];
    $izinEdarMap  = [1=>'Ya, oleh BPOM', 2=>'Ya, bukan oleh BPOM', 3=>'Tidak'];
    $mbgMap = [1=>'Ya, sebagai SATUAN PELAYANAN PEMENUHAN GIZI (SPPG)',2=>'Ya, sebagai supplier',3=>'Ya, sebagai penerima manfaat MBG (Sekolah, Puskesmas, Posyandu)',4=>'Ya, peran lainnya',5=>'Tidak terlibat MBG'];
    $prlMap = [1=>'Ya, seluruh produk', 2=>'Ya, sebagian produk', 3=>'Tidak'];
    $rangeAsetMap = [1=>'s.d. Rp 500 juta',2=>'Lebih dari Rp 500 juta s.d. Rp 1 miliar',3=>'Lebih dari Rp 1 miliar s.d. Rp 5 miliar',4=>'Lebih dari Rp 5 miliar s.d. Rp 10 miliar',5=>'Lebih dari Rp 10 miliar'];
    $koperasiJenisMap = [1=>'Open Loop (dapat melayani nonanggota)', 2=>'Close Loop (hanya melayani anggota)'];

    // ── Plain-text formatters (escaped automatically by Blade {{ }}) ────────────
    $str = fn($v) => ($v !== null && $v !== '') ? $v : '—';
    $val = fn($v, $map) => ($v !== null && $v !== '' && isset($map[$v])) ? $map[$v] : (($v !== null && $v !== '') ? $v : '—');
    $rp  = fn($v) => ($v !== null && $v !== '') ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '—';
    $num = fn($v, $suffix = '') => ($v !== null && $v !== '') ? $v . $suffix : '—';

    $namaPerusahaan = $response->nama_perusahaan ?: '—';
    $namaUser       = $user->name ?? '—';
    $pct            = $response->completionPercent();

    $totalPengeluaran = (float)($response->pengeluaran_upah_gaji ?? 0)
        + (float)($response->pengeluaran_biaya_produksi ?? 0)
        + (float)($response->pengeluaran_pembelian_barang ?? 0)
        + (float)($response->pengeluaran_operasional ?? 0)
        + (float)($response->pengeluaran_nonoperasional ?? 0);
    $totalPendapatan = (float)($response->nilai_produksi_barang_jasa ?? 0) + (float)($response->pendapatan_lainnya ?? 0);
    $totalAset       = (float)($response->nilai_aset_tanah_bangunan ?? 0) + (float)($response->nilai_aset_lainnya ?? 0);
    $totalPekerja    = ($response->pekerja_laki ?? 0) + ($response->pekerja_perempuan ?? 0);
    $modalRows = [
        'a. Pribadi/Perorangan'                           => $response->modal_pribadi,
        'b. Lembaga Nonprofit yang Melayani Rumah Tangga' => $response->modal_nonprofit,
        'c. Korporasi Publik'                             => $response->modal_korporasi_publik,
        'd. Korporasi Nonpublik'                          => $response->modal_korporasi_nonpublik,
        'e. Pemerintah'                                   => $response->modal_pemerintah,
        'f. Asing'                                        => $response->modal_asing,
    ];
    $totalModal = array_sum(array_filter(array_values($modalRows), fn($v) => $v !== null));
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 9pt; color: #111827; }
  .page { padding: 14mm 14mm 12mm 14mm; }

  .header { background: #eff6ff; border: 2px solid #3b82f6; border-radius: 6px; padding: 10px 14px; margin-bottom: 12px; text-align: center; }
  .header-org  { font-size: 7.5pt; color: #6b7280; text-transform: uppercase; letter-spacing: 0.06em; }
  .header-main { font-size: 15pt; font-weight: bold; color: #1e40af; margin: 3px 0 1px; }
  .header-sub  { font-size: 9pt; color: #374151; }

  .meta { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
  .meta td { padding: 4px 8px; border: 1px solid #bfdbfe; font-size: 8.5pt; vertical-align: top; }
  .meta .lbl { background: #f0f9ff; color: #1e40af; font-weight: bold; width: 18%; }
  .pill { display: inline-block; border-radius: 9999px; padding: 1px 8px; font-size: 7.5pt; font-weight: bold; }
  .pill-done { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
  .pill-prog { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

  .blok-head { background: #1e40af; color: #fff; font-size: 9.5pt; font-weight: bold; padding: 5px 10px; border-radius: 4px; margin: 14px 0 6px; }
  .sec-title { font-size: 8pt; font-weight: bold; color: #1e40af; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #bfdbfe; padding: 6px 0 3px; margin: 8px 0 2px; }

  table.kv { width: 100%; border-collapse: collapse; }
  table.kv td { padding: 3.5px 8px; border-bottom: 1px solid #eef2f7; font-size: 8.5pt; vertical-align: top; }
  table.kv tr:last-child td { border-bottom: none; }
  table.kv .lbl { color: #6b7280; width: 50%; }
  table.kv .val { color: #111827; font-weight: bold; }
  table.kv .val.empty { color: #9ca3af; font-weight: normal; font-style: italic; }
  table.kv .total td { border-top: 1.5px solid #cbd5e1; font-weight: bold; }
  .rp { text-align: right; }

  .note-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 8px 10px; font-size: 8.5pt; white-space: pre-wrap; line-height: 1.45; }
  .person { width: 48%; display: inline-block; vertical-align: top; }

  .footer { text-align: center; font-size: 7pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 6px; margin-top: 14px; }
  .avoid-break { page-break-inside: avoid; }
</style>
</head>
<body>
<div class="page">

{{-- ══ HEADER ══ --}}
<div class="header">
  <div class="header-org">Badan Pusat Statistik &mdash; Republik Indonesia</div>
  <div class="header-main">SENSUS EKONOMI 2026</div>
  <div class="header-sub">SE2026-L.UB &mdash; Pendataan Lengkap Usaha/Perusahaan</div>
</div>

{{-- ══ META ══ --}}
<table class="meta">
  <tr>
    <td class="lbl">Nama Perusahaan</td>
    <td colspan="3">{{ $namaPerusahaan }}@if($response->nama_komersial && $response->nama_komersial !== $response->nama_perusahaan) <span style="color:#6b7280;">({{ $response->nama_komersial }})</span>@endif</td>
  </tr>
  <tr>
    <td class="lbl">Pengguna</td>
    <td>{{ $namaUser }}<br><span style="color:#6b7280;font-size:7.5pt;">{{ $user->email ?? '' }}</span></td>
    <td class="lbl" style="width:18%;">Lokasi</td>
    <td>{{ $response->kabupaten_kota ?: '—' }}@if($response->provinsi)<br><span style="color:#6b7280;font-size:7.5pt;">{{ $response->provinsi }}</span>@endif</td>
  </tr>
  <tr>
    <td class="lbl">Status</td>
    <td>
      @if($response->is_completed)
        <span class="pill pill-done">&#10003; SELESAI</span>
      @else
        <span class="pill pill-prog">DALAM PROSES ({{ $pct }}%)</span>
      @endif
    </td>
    <td class="lbl">Terakhir Disimpan</td>
    <td>{{ $completedAt }}</td>
  </tr>
</table>

{{-- ════════════ BLOK I-A ════════════ --}}
<div class="blok-head">BLOK I-A &mdash; Identitas &amp; Lokasi</div>

<div class="avoid-break">
  <div class="sec-title">1–4. Lokasi Perusahaan</div>
  <table class="kv">
    <tr><td class="lbl">Provinsi</td><td class="val">{{ $str($response->provinsi) }}</td></tr>
    <tr><td class="lbl">Kabupaten/Kota</td><td class="val">{{ $str($response->kabupaten_kota) }}</td></tr>
    <tr><td class="lbl">Kecamatan</td><td class="val">{{ $str($response->kecamatan) }}</td></tr>
    <tr><td class="lbl">Kelurahan/Desa</td><td class="val">{{ $str($response->kelurahan_desa) }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">5. Nama dan Alamat Perusahaan</div>
  <table class="kv">
    <tr><td class="lbl">Nama Perusahaan</td><td class="val">{{ $str($response->nama_perusahaan) }}</td></tr>
    <tr><td class="lbl">Nama Komersial</td><td class="val">{{ $str($response->nama_komersial) }}</td></tr>
    <tr><td class="lbl">Alamat</td><td class="val">{{ $str($response->alamat_perusahaan) }}</td></tr>
    <tr><td class="lbl">RT / RW</td><td class="val">@if($response->rt || $response->rw) RT {{ $response->rt ?: '-' }} / RW {{ $response->rw ?: '-' }} @else — @endif</td></tr>
    <tr><td class="lbl">Kode Pos</td><td class="val">{{ $str($response->kode_pos) }}</td></tr>
    <tr><td class="lbl">Nomor Telepon</td><td class="val">{{ $str($response->nomor_telepon) }}</td></tr>
    <tr><td class="lbl">Nomor HP/WhatsApp</td><td class="val">{{ $str($response->nomor_hp) }}</td></tr>
    <tr><td class="lbl">Email Perusahaan</td><td class="val">{{ $str($response->email_perusahaan) }}</td></tr>
    <tr><td class="lbl">Website</td><td class="val">{{ $str($response->homepage) }}</td></tr>
    <tr><td class="lbl">Jenis Kawasan (d)</td><td class="val">{{ $val($response->jenis_kawasan, $kawasanMap) }}</td></tr>
    <tr><td class="lbl">Nama Kawasan (e)</td><td class="val">{{ $str($response->nama_kawasan) }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">6. Nomor Induk Berusaha (NIB)</div>
  <table class="kv">
    <tr><td class="lbl">Memiliki NIB?</td><td class="val">{{ $val($response->has_nib, $yn) }}</td></tr>
    @if($response->has_nib == 1)
    <tr><td class="lbl">Nomor NIB</td><td class="val">{{ $str($response->nib) }}</td></tr>
    @elseif($response->has_nib == 2)
    <tr><td class="lbl">Alasan tidak memiliki NIB</td><td class="val">{{ $val($response->alasan_tidak_nib, $nibAlasanMap) }}</td></tr>
    @endif
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">7. Status Badan Usaha</div>
  <table class="kv">
    <tr><td class="lbl">Status Badan Usaha (a)</td><td class="val">{{ $val($response->status_badan_usaha, $sbuMap) }}</td></tr>
    @if($response->status_badan_usaha == 3)
    <tr><td class="lbl">Koperasi KDKMP? (b)</td><td class="val">{{ $val($response->is_koperasi_kdkmp, $yn) }}</td></tr>
    <tr><td class="lbl">Jenis Koperasi (c)</td><td class="val">{{ $val($response->jenis_koperasi, $koperasiJenisMap) }}</td></tr>
    @endif
    <tr><td class="lbl">Laporan/Catatan Keuangan (d)</td><td class="val">{{ $val($response->has_laporan_keuangan, $yn) }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">8. Pengusaha / Penanggung Jawab</div>
  <table class="kv">
    <tr><td class="lbl">Nama (a)</td><td class="val">{{ $str($response->nama_pengusaha) }}</td></tr>
    <tr><td class="lbl">Jenis Kelamin (b)</td><td class="val">{{ $val($response->jenis_kelamin, $jkMap) }}</td></tr>
    <tr><td class="lbl">Umur (c)</td><td class="val">{{ $num($response->umur, ' tahun') }}</td></tr>
    <tr><td class="lbl">NIK (d)</td><td class="val">{{ $str($response->nik) }}</td></tr>
  </table>
</div>

{{-- ════════════ BLOK I-B ════════════ --}}
<div class="blok-head">BLOK I-B &mdash; Kegiatan &amp; Digital</div>

<div class="avoid-break">
  <div class="sec-title">9. Kegiatan &amp; Produk Utama</div>
  <table class="kv">
    <tr><td class="lbl">Kegiatan Utama (a)</td><td class="val">{{ $str($response->kegiatan_utama) }}</td></tr>
    <tr><td class="lbl">Produksi barang di lokasi (b1)</td><td class="val">{{ $val($response->produksi_di_lokasi, $yn) }}</td></tr>
    <tr><td class="lbl">Layanan makan/minum di tempat (b2)</td><td class="val">{{ $val($response->layanan_makan_minum, $yn) }}</td></tr>
    @if($response->penjualan_barang !== null)
    <tr><td class="lbl">Penjualan barang (b3)</td><td class="val">{{ $val($response->penjualan_barang, $yn) }}</td></tr>
    @endif
    @if($response->aktivitas_jasa_pertanian !== null)
    <tr><td class="lbl">Aktivitas jasa/pertanian (b4)</td><td class="val">{{ $val($response->aktivitas_jasa_pertanian, $yn) }}</td></tr>
    @endif
    @if($response->lokasi_usaha !== null)
    <tr><td class="lbl">Lokasi usaha (c)</td><td class="val">{{ $val($response->lokasi_usaha, $lokasiMap) }}</td></tr>
    @endif
    @if($response->input_produksi)
    <tr><td class="lbl">Input produksi (d)</td><td class="val">{{ $str($response->input_produksi) }}</td></tr>
    <tr><td class="lbl">Proses produksi (e)</td><td class="val">{{ $str($response->proses_produksi) }}</td></tr>
    @endif
    <tr><td class="lbl">Produk Utama (f)</td><td class="val">{{ $str($response->produk_utama) }}</td></tr>
    <tr><td class="lbl">Kode KBLI (g)</td><td class="val">{{ $str($response->kode_kbli) }}</td></tr>
    <tr><td class="lbl">Kategori Lapangan Usaha (h)</td><td class="val">{{ $str($response->kategori_lapangan_usaha) }}</td></tr>
    @if($response->klasifikasi_akomodasi !== null)
    <tr><td class="lbl">Klasifikasi Akomodasi (i)</td><td class="val">{{ $val($response->klasifikasi_akomodasi, $klasAkomodasiMap) }}</td></tr>
    @endif
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">10–11. Jaringan Usaha</div>
  <table class="kv">
    <tr><td class="lbl">Jaringan Usaha (10)</td><td class="val">{{ $val($response->jaringan_usaha, $jaringanMap) }}</td></tr>
    @if($response->jaringan_usaha == 2)
    <tr><td class="lbl">Jumlah Cabang (10b)</td><td class="val">{{ $num($response->jumlah_cabang) }}</td></tr>
    @endif
    @if(in_array($response->jaringan_usaha, [3,4,5,6]))
    <tr><td class="lbl">Nama Kantor Pusat (11)</td><td class="val">{{ $str($response->kp_nama) }}</td></tr>
    <tr><td class="lbl">Alamat Kantor Pusat</td><td class="val">{{ $str($response->kp_alamat) }}</td></tr>
    <tr><td class="lbl">Negara Kantor Pusat</td><td class="val">{{ $str($response->kp_negara) }}</td></tr>
    <tr><td class="lbl">Provinsi Kantor Pusat</td><td class="val">{{ $str($response->kp_provinsi) }}</td></tr>
    <tr><td class="lbl">Kab/Kota Kantor Pusat</td><td class="val">{{ $str($response->kp_kabkota) }}</td></tr>
    <tr><td class="lbl">Email Kantor Pusat</td><td class="val">{{ $str($response->kp_email) }}</td></tr>
    @endif
  </table>
</div>

@if($response->jaringan_usaha != 6)
<div class="avoid-break">
  <div class="sec-title">12. Penggunaan Internet dan Teknologi Digital</div>
  <table class="kv">
    <tr><td class="lbl">Menggunakan internet dalam menjalankan usaha (12a)</td><td class="val">{{ $val($response->uses_internet, $yn) }}</td></tr>
    @if($response->uses_internet == 1)
    <tr><td class="lbl">Menerima pesanan barang/jasa (12b1)</td><td class="val">{{ $response->internet_pesanan ? 'Ya' : 'Tidak' }}</td></tr>
    <tr><td class="lbl">Produksi barang/jasa (12b2)</td><td class="val">{{ $response->internet_produksi ? 'Ya' : 'Tidak' }}</td></tr>
    <tr><td class="lbl">Distribusi barang/jasa (12b3)</td><td class="val">{{ $response->internet_distribusi ? 'Ya' : 'Tidak' }}</td></tr>
    <tr><td class="lbl">Membeli bahan baku online (12b4)</td><td class="val">{{ $response->internet_beli_bahan_baku ? 'Ya' : 'Tidak' }}</td></tr>
    <tr><td class="lbl">Promosi (12b5)</td><td class="val">{{ $response->internet_promosi ? 'Ya' : 'Tidak' }}</td></tr>
    <tr><td class="lbl">Lainnya (12b6)</td><td class="val">{{ $response->internet_lainnya ? 'Ya' : 'Tidak' }}</td></tr>
    @endif
    <tr><td class="lbl">Memanfaatkan teknologi digital - AI, IoT, big data, dll (12c)</td><td class="val">{{ $val($response->uses_teknologi_digital, $yn) }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">13. Ramah Lingkungan</div>
  <table class="kv">
    <tr><td class="lbl">Memproduksi barang/jasa ramah lingkungan (13a)</td><td class="val">{{ $val($response->produk_ramah_lingkungan, $prlMap) }}</td></tr>
    <tr><td class="lbl">Menggunakan input untuk perlindungan lingkungan (13b)</td><td class="val">{{ $val($response->uses_input_lingkungan, $yn) }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">14. Produk Karya Seni, Sastra, Desain, Teknologi, atau Warisan Budaya</div>
  <table class="kv">
    <tr><td class="lbl">Menggunakan produk karya seni/sastra/desain/teknologi/warisan budaya (14)</td><td class="val">{{ $val($response->uses_karya_seni, $yn) }}</td></tr>
  </table>
</div>
@endif

{{-- ════════════ BLOK I-C ════════════ --}}
<div class="blok-head">BLOK I-C &mdash; Sertifikasi &amp; Kemitraan</div>

<div class="avoid-break">
  <div class="sec-title">15. Sertifikat Halal (BPJPH)</div>
  <table class="kv">
    <tr><td class="lbl">Menghasilkan produk bersertifikat halal? (a)</td><td class="val">{{ $val($response->sertifikat_halal, $sertHalalMap) }}</td></tr>
    @if($response->sertifikat_halal == 1)
    <tr><td class="lbl">Jumlah varian produk sudah bersertifikat halal BPJPH (b)</td><td class="val">{{ $num($response->jumlah_produk_halal_bpjph, ' varian') }}</td></tr>
    @endif
    <tr><td class="lbl">Jumlah varian produk belum bersertifikat halal BPJPH (c)</td><td class="val">{{ $num($response->jumlah_produk_belum_halal_bpjph, ' varian') }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">16. Izin Edar (BPOM)</div>
  <table class="kv">
    <tr><td class="lbl">Memiliki izin edar? (a)</td><td class="val">{{ $val($response->izin_edar, $izinEdarMap) }}</td></tr>
    @if($response->izin_edar == 1)
    <tr><td class="lbl">Jumlah varian produk dengan izin edar BPOM (b)</td><td class="val">{{ $num($response->jumlah_produk_izin_edar_bpom, ' varian') }}</td></tr>
    @endif
    <tr><td class="lbl">Jumlah varian produk tanpa izin edar BPOM (c)</td><td class="val">{{ $num($response->jumlah_produk_tanpa_izin_edar_bpom, ' varian') }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">17–19. Kemitraan, MBG &amp; Transaksi Lintas Negara</div>
  <table class="kv">
    <tr><td class="lbl">Bermitra dengan KDKMP (17)</td><td class="val">{{ $val($response->bermitra_kdkmp, $yn) }}</td></tr>
    <tr><td class="lbl">Keterlibatan dalam program MBG (18)</td><td class="val">{{ $val($response->terlibat_mbg, $mbgMap) }}</td></tr>
    <tr><td class="lbl">Penjualan/pembelian Barang ke bukan penduduk (19a)</td><td class="val">{{ $val($response->ekspor_impor_barang, $yn) }}</td></tr>
    <tr><td class="lbl">Penjualan/pembelian Jasa ke bukan penduduk (19b)</td><td class="val">{{ $val($response->ekspor_impor_jasa, $yn) }}</td></tr>
  </table>
</div>

{{-- ════════════ BLOK I-D ════════════ --}}
<div class="blok-head">BLOK I-D &mdash; Pekerja &amp; Keuangan</div>

<div class="avoid-break">
  <div class="sec-title">20–21. Pekerja &amp; Tahun Beroperasi</div>
  <table class="kv">
    <tr><td class="lbl">Pekerja Laki-laki (20a)</td><td class="val">{{ $num($response->pekerja_laki, ' orang') }}</td></tr>
    <tr><td class="lbl">Pekerja Perempuan (20b)</td><td class="val">{{ $num($response->pekerja_perempuan, ' orang') }}</td></tr>
    <tr class="total"><td class="lbl">Total Pekerja (20c)</td><td class="val">{{ ($response->pekerja_laki !== null || $response->pekerja_perempuan !== null) ? $totalPekerja . ' orang' : '—' }}</td></tr>
    <tr><td class="lbl">Tahun mulai beroperasi komersial (21)</td><td class="val">{{ $str($response->tahun_beroperasi) }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">22. Rincian Pengeluaran Tahun 2025</div>
  <table class="kv">
    <tr><td class="lbl">a. Total upah, gaji &amp; jaminan sosial pegawai</td><td class="val rp">{{ $rp($response->pengeluaran_upah_gaji) }}</td></tr>
    <tr><td class="lbl">b. Biaya produksi (bahan baku &amp; penolong)</td><td class="val rp">{{ $rp($response->pengeluaran_biaya_produksi) }}</td></tr>
    <tr><td class="lbl">c. Biaya pembelian barang terjual (perdagangan)</td><td class="val rp">{{ $rp($response->pengeluaran_pembelian_barang) }}</td></tr>
    <tr><td class="lbl">d. Biaya operasional</td><td class="val rp">{{ $rp($response->pengeluaran_operasional) }}</td></tr>
    <tr><td class="lbl">e. Biaya nonoperasional</td><td class="val rp">{{ $rp($response->pengeluaran_nonoperasional) }}</td></tr>
    <tr class="total"><td class="lbl">f. Total pengeluaran (a+b+c+d+e)</td><td class="val rp">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">23. Nilai Produksi/Penjualan/Pendapatan Tahun 2025</div>
  <table class="kv">
    <tr><td class="lbl">Nilai produksi/penjualan barang &amp; jasa (a)</td><td class="val rp">{{ $rp($response->nilai_produksi_barang_jasa) }}</td></tr>
    <tr><td class="lbl">Pendapatan lainnya (b)</td><td class="val rp">{{ $rp($response->pendapatan_lainnya) }}</td></tr>
    <tr class="total"><td class="lbl">Total nilai produksi (a+b) (c)</td><td class="val rp">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td></tr>
    <tr><td class="lbl">Persentase pendapatan dari usaha online (d)</td><td class="val">{{ $num($response->persen_pendapatan_online, '%') }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">24. Nilai Aset pada 31 Desember 2025</div>
  <table class="kv">
    <tr><td class="lbl">Nilai aset tanah dan bangunan (a)</td><td class="val rp">{{ $rp($response->nilai_aset_tanah_bangunan) }}</td></tr>
    <tr><td class="lbl">Nilai aset selain tanah dan bangunan (b)</td><td class="val rp">{{ $rp($response->nilai_aset_lainnya) }}</td></tr>
    <tr class="total"><td class="lbl">Nilai total aset (a+b) (c)</td><td class="val rp">Rp {{ number_format($totalAset, 0, ',', '.') }}</td></tr>
    @if($response->range_total_aset !== null)
    <tr><td class="lbl">Rentang nilai total aset (c1)</td><td class="val">{{ $val($response->range_total_aset, $rangeAsetMap) }}</td></tr>
    @endif
    <tr><td class="lbl">Luas tanah dikuasai untuk usaha (d)</td><td class="val">{{ $num($response->luas_tanah, ' m²') }}</td></tr>
  </table>
</div>

<div class="avoid-break">
  <div class="sec-title">25. Susunan Kepemilikan Modal pada 31 Desember 2025 (%)</div>
  <table class="kv">
    @foreach($modalRows as $label => $modalPct)
    <tr><td class="lbl">{{ $label }}</td><td class="val">{{ $modalPct !== null ? $modalPct . '%' : '—' }}</td></tr>
    @endforeach
    <tr class="total"><td class="lbl">g. Total (a+b+c+d+e+f)</td><td class="val">{{ $totalModal }}%</td></tr>
  </table>
</div>

{{-- ════════════ BLOK II ════════════ --}}
<div class="blok-head">BLOK II &mdash; Catatan</div>
@if($response->catatan)
<div class="note-box">{{ $response->catatan }}</div>
@else
<table class="kv"><tr><td class="val empty">Catatan belum diisi.</td></tr></table>
@endif

{{-- ════════════ BLOK III ════════════ --}}
<div class="blok-head">BLOK III &mdash; Keterangan Petugas</div>
@foreach([
    ['prefix' => 'ppl', 'title' => 'Pencacah Lapangan (PPL)'],
    ['prefix' => 'pml', 'title' => 'Pengawas/Pemeriksa Lapangan (PML)'],
    ['prefix' => 'resp', 'title' => 'Responden'],
] as $person)
@php $p = $person['prefix']; @endphp
<div class="avoid-break">
  <div class="sec-title">{{ $person['title'] }}</div>
  <table class="kv">
    <tr><td class="lbl">Nama</td><td class="val">{{ $str($response->{$p.'_nama'}) }}</td></tr>
    <tr><td class="lbl">NIP</td><td class="val">{{ $str($response->{$p.'_nip'}) }}</td></tr>
    <tr><td class="lbl">Telepon</td><td class="val">{{ $str($response->{$p.'_telepon'}) }}</td></tr>
    <tr><td class="lbl">Email</td><td class="val">{{ $str($response->{$p.'_email'}) }}</td></tr>
    <tr><td class="lbl">Tanggal</td><td class="val">{{ $response->{$p.'_tanggal'} ? \Carbon\Carbon::parse($response->{$p.'_tanggal'})->format('d M Y') : '—' }}</td></tr>
  </table>
</div>
@endforeach

{{-- ══ FOOTER ══ --}}
<div class="footer">
  Digenerate otomatis oleh sistem survei online DataKita &nbsp;|&nbsp;
  Sensus Ekonomi 2026 &mdash; Badan Pusat Statistik RI &nbsp;|&nbsp;
  Dicetak: {{ now()->format('d/m/Y H:i:s') }}
</div>

</div>
</body>
</html>
