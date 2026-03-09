@php
    $companyName = $surveyResponse->nama_perusahaan ?? '-';
    $submissionAt = optional($surveyResponse->updated_at)->format('d M Y, H:i') ?? '-';

    $labelKondisi = [
        'masih_aktif' => 'Masih Aktif',
        'belum_beroperasi' => 'Belum Beroperasi',
        'tutup' => 'Tutup',
        'pindah' => 'Pindah',
        'tidak_ditemukan' => 'Tidak Ditemukan',
        'double_ganda_duplikat' => 'Double / Ganda / Duplikat',
    ];

    $labelJaringan = [
        'tunggal' => 'Tunggal',
        'pabrik_unit_produksi' => 'Pabrik/Unit Produksi, Cabang atau Perwakilan',
        'pusat_ada_kegiatan_produksi' => 'Pusat ada kegiatan produksi',
        'kantor_pusat_administrasi_perwakilan' => 'Kantor Pusat/Administrasi/Perwakilan',
        'unit_pembantu_penunjang' => 'Unit Pembantu/Penunjang'
    ];

    $rangeLabels = [
        '1' => '1 s.d. Rp 500 juta',
        '2' => 'Lebih dari Rp 500 juta s.d. Rp 1 miliar',
        '3' => 'Lebih dari Rp 1 miliar s.d. Rp 5 miliar',
        '4' => 'Lebih dari Rp 5 miliar s.d. Rp 10 miliar',
        '5' => 'Lebih dari Rp 10 miliar'
    ];

    function nf_idr($v) { return ($v === null || $v === '') ? '-' : number_format((float)$v, 0, ',', '.'); }
    function nf_plain($v) { return ($v === null || $v === '') ? '-' : $v; }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Rekap SIBSTR - {{ $companyName }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #111827; }
        /* Header styling inspired by survey form */
        .header {
            background: #f0f9ff;
            border: 2px solid #3b82f6;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 16px;
        }
        .title { font-size: 18px; font-weight: 700; margin: 0 0 6px; color: #1e40af; }
        .meta { font-size: 12px; color: #1f2937; }
        .section { margin: 18px 0; }
        .section h2 {
            font-size: 15px;
            margin: 0 0 10px;
            padding: 6px 8px;
            color: #1e3a8a;
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        .kv { display: grid; grid-template-columns: 220px 1fr; gap: 8px 14px; }
        .kv .k { color: #374151; background: #f9fafb; padding: 6px 8px; border: 1px solid #e5e7eb; }
        .kv .v { font-weight: 600; padding: 6px 8px; border: 1px solid #e5e7eb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 6px 8px; text-align: left; }
        thead th { background: #eff6ff; color: #1e3a8a; }
        .badge { display: inline-block; padding: 2px 8px; font-size: 11px; border-radius: 9999px; background: #e5e7eb; margin-left: 6px; }
        .muted { color: #6b7280; }
        .num { text-align: right; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mb-1 { margin-bottom: 4px; }
        .mb-2 { margin-bottom: 8px; }
        .small { font-size: 11px; }
        .pill { display: inline-block; padding: 2px 8px; font-size: 11px; border-radius: 9999px; }
        .pill-blue { background: #dbeafe; color: #1e40af; }
        .pill-green { background: #d1fae5; color: #065f46; }
    </style>
    @php
        $isMasihAktif = $isMasihAktif ?? (($surveyResponse->kondisi_perusahaan ?? null) === 'masih_aktif');
        $kbliPrefix = $kbliPrefix ?? null;
        $isIndustri = $isIndustri ?? ($kbliPrefix !== null && $kbliPrefix >= 10 && $kbliPrefix <= 33);
    @endphp
    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_text(520, 820, "Halaman {PAGE_NUM}/{PAGE_COUNT}", null, 9, array(0.4,0.4,0.4));
        }
    </script>
    </head>
<body>
    <div class="header">
        <div class="title">Rekap Isian SIBSTR</div>
        <div class="meta">
            <div><strong>Perusahaan:</strong> {{ $companyName }}</div>
            <div><strong>Dikirim:</strong> {{ $submissionAt }}</div>
        </div>
    </div>

    <div class="section">
        <h2>Informasi Perusahaan</h2>
        <div class="kv">
            <div class="k">KIP</div><div class="v">{{ nf_plain($surveyResponse->kip ?? null) }}</div>
            <div class="k">IDSBR</div><div class="v">{{ nf_plain($surveyResponse->idsbr ?? null) }}</div>
            <div class="k">Nama Perusahaan</div><div class="v">{{ nf_plain($surveyResponse->nama_perusahaan ?? null) }}</div>
            <div class="k">Alamat Pabrik</div><div class="v">{{ nf_plain($surveyResponse->alamat_pabrik ?? null) }}</div>
            <div class="k">Kabupaten/Kota</div><div class="v">{{ nf_plain($surveyResponse->kabupaten_kota ?? null) }}</div>
            <div class="k">Telepon/Fax</div><div class="v">{{ nf_plain($surveyResponse->telepon_fax ?? null) }}</div>
            <div class="k">Penghubung</div><div class="v">{{ nf_plain($surveyResponse->penghubung ?? null) }}</div>
            <div class="k">Email</div><div class="v">{{ nf_plain($surveyResponse->email ?? null) }}</div>
            <div class="k">Homepage</div><div class="v">{{ nf_plain($surveyResponse->homepage ?? null) }}</div>
            <div class="k">Tahun Mulai Operasi</div><div class="v">{{ nf_plain($surveyResponse->tahun_mulai_beroperasi ?? null) }}</div>
            <div class="k">NIB</div><div class="v">{{ nf_plain($surveyResponse->nib ?? null) }}</div>
            <div class="k">Jenis Kawasan</div>
            <div class="v">{{ $jenisKawasanOptions[$surveyResponse->jenis_kawasan ?? ''] ?? ($surveyResponse->jenis_kawasan ?? '-') }}</div>
            <div class="k">Nama Kawasan</div><div class="v">{{ nf_plain($surveyResponse->nama_kawasan ?? null) }}</div>
            <div class="k">Pengelola Kawasan</div><div class="v">{{ nf_plain($surveyResponse->nama_pengelola_kawasan ?? null) }}</div>
            <div class="k">Legalisasi - Nama</div><div class="v">{{ nf_plain($surveyResponse->legalisasi_nama ?? null) }}</div>
            <div class="k">Legalisasi - Jabatan</div><div class="v">{{ nf_plain($surveyResponse->legalisasi_jabatan ?? null) }}</div>
            <div class="k">Legalisasi - Jenis Kelamin</div><div class="v">{{ ($surveyResponse->legalisasi_jenis_kelamin ?? '') === 'laki_laki' ? 'Laki-laki' : (($surveyResponse->legalisasi_jenis_kelamin ?? '') === 'perempuan' ? 'Perempuan' : '-') }}</div>
            <div class="k">Legalisasi - NIK</div><div class="v">{{ nf_plain($surveyResponse->legalisasi_nik ?? null) }}</div>
        </div>
    </div>

    @if(!empty($showBlocks['blok2']))
    <div class="section">
        <h2>Blok II. Pendahuluan</h2>
        <div class="kv">
            <div class="k">201. Kondisi Perusahaan</div>
            <div class="v">{{ $labelKondisi[$surveyResponse->kondisi_perusahaan ?? ''] ?? ($surveyResponse->kondisi_perusahaan ?? '-') }}</div>
            @if(!empty($blok2Visibility['showAfterQ201']))
                <div class="k">202. Jaringan/Unit Kegiatan</div>
                <div class="v">{{ $labelJaringan[$surveyResponse->jaringan_unit_kegiatan ?? ''] ?? ($surveyResponse->jaringan_unit_kegiatan ?? '-') }}</div>

                @if(!empty($blok2Visibility['showQ203']))
                    <div class="k">203. Jumlah cabang & unit usaha</div>
                    <div class="v">{{ nf_plain($surveyResponse->jumlah_cabang_dan_unit_usaha ?? null) }}</div>
                @endif

                @if(!empty($blok2Visibility['showQ204']))
                    <div class="k">204a. Nama Kantor Pusat</div>
                    <div class="v">{{ nf_plain($surveyResponse->info_kantor_pusat_nama ?? null) }}</div>
                    <div class="k">204b. Alamat Kantor Pusat</div>
                    <div class="v">{{ nf_plain($surveyResponse->info_kantor_pusat_alamat ?? null) }}</div>
                    <div class="k">204c. Email Kantor Pusat</div>
                    <div class="v">{{ nf_plain($surveyResponse->info_kantor_pusat_email ?? null) }}</div>
                    <div class="k">204d. Negara</div>
                    <div class="v">{{ nf_plain($surveyResponse->info_kantor_pusat_negara ?? null) }}</div>
                    <div class="k">204e. Provinsi</div>
                    <div class="v">{{ nf_plain($surveyResponse->info_kantor_pusat_provinsi ?? null) }}</div>
                    <div class="k">204f. Kab/Kota</div>
                    <div class="v">{{ nf_plain($surveyResponse->info_kantor_pusat_kabkota ?? null) }}</div>
                @endif

                @if(!empty($blok2Visibility['showQ205to211']))
                    <div class="k">205. Bulan aktif 2025</div>
                    <div class="v">{{ nf_plain($surveyResponse->jumlah_bulan_aktif_2025 ?? null) }}</div>
                    <div class="k">206. Rata-rata hari kerja/bulan 2025</div>
                    <div class="v">{{ nf_plain($surveyResponse->rata_hari_kerja_bulanan_2025 ?? null) }}</div>
                    <div class="k">207a. Jam kerja/hari</div>
                    <div class="v">{{ nf_plain($surveyResponse->rata_jam_kerja_per_hari_2025 ?? null) }}</div>
                    <div class="k">207b. Jumlah shift/hari</div>
                    <div class="v">{{ nf_plain($surveyResponse->rata_shift_per_hari_2025 ?? null) }}</div>

                    <div class="k">208a. Pekerja laki-laki</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_laki_laki ?? null) }}</div>
                    <div class="k">208a. Pekerja perempuan</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_perempuan ?? null) }}</div>
                    <div class="k">208b. Pekerja produksi</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_produksi ?? null) }}</div>
                    <div class="k">208b. Pekerja lainnya</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_lainnya ?? null) }}</div>
                    <div class="k">208c. Tenaga kerja asing</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_asing ?? null) }}</div>
                    <div class="k">208d. Tenaga kerja outsourcing</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_outsourcing ?? null) }}</div>

                    <div class="k">208e. Uraian kegiatan utama</div><div class="v">{{ nf_plain($surveyResponse->kegiatan_utama_perusahaan ?? null) }}</div>
                    <div class="k">208f. KBLI utama</div><div class="v">{{ nf_plain($surveyResponse->kbli_utama ?? null) }} @if(!empty($kbliPrefix)) <span class="badge">{{ ($kbliPrefix >= 10 && $kbliPrefix <= 33) ? 'Industri' : 'Non-Industri' }}</span> @endif</div>

                    <div class="k">209. Memproduksi barang sendiri?</div><div class="v">{{ ($surveyResponse->memproduksi_barang_sendiri ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->memproduksi_barang_sendiri ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                    <div class="k">209. Menyediakan layanan makan minum?</div><div class="v">{{ ($surveyResponse->menyediakan_layanan_makan_minum ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->menyediakan_layanan_makan_minum ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                    <div class="k">209. Penjualan barang pihak lain?</div><div class="v">{{ ($surveyResponse->penjualan_barang_pihak_lain ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->penjualan_barang_pihak_lain ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>

                    <div class="k">210. Menggunakan internet?</div>
                    <div class="v">{{ ($surveyResponse->penggunaan_internet ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->penggunaan_internet ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>

                    @if(!empty($blok2Visibility['showQ210a']))
                        <div class="k">210a. Menerima pesanan barang/jasa</div>
                        <div class="v">{{ ($surveyResponse->internet_a1_menerima_pesanan ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->internet_a1_menerima_pesanan ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                        <div class="k">210a. Produksi barang/jasa</div>
                        <div class="v">{{ ($surveyResponse->internet_a2_produksi ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->internet_a2_produksi ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                        <div class="k">210a. Distribusi barang/jasa</div>
                        <div class="v">{{ ($surveyResponse->internet_a3_distribusi ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->internet_a3_distribusi ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                        <div class="k">210a. Membeli bahan baku online</div>
                        <div class="v">{{ ($surveyResponse->internet_a4_beli_bahan_baku ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->internet_a4_beli_bahan_baku ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                        <div class="k">210a. Promosi</div>
                        <div class="v">{{ ($surveyResponse->internet_a5_promosi ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->internet_a5_promosi ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                        <div class="k">210a. Lainnya</div>
                        <div class="v">{{ ($surveyResponse->internet_a6_lainnya ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->internet_a6_lainnya ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                    @endif

                    @if(!empty($blok2Visibility['showQ210b']))
                        <div class="k">210b. Memanfaatkan teknologi digital</div>
                        <div class="v">{{ ($surveyResponse->pemanfaatan_teknologi_digital ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->pemanfaatan_teknologi_digital ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                    @endif

                    <div class="k">211a. Memproduksi barang/jasa ramah lingkungan</div>
                    <div class="v">
                        @php $prl = $surveyResponse->produksi_ramah_lingkungan ?? ''; @endphp
                        {{ $prl === 'ya_seluruh' ? 'Ya, seluruhnya' : ($prl === 'ya_sebagian' ? 'Ya, sebagian' : ($prl === 'tidak' ? 'Tidak sama sekali' : '-')) }}
                    </div>
                    <div class="k">211b. Menggunakan input untuk perlindungan lingkungan</div>
                    <div class="v">{{ ($surveyResponse->penggunaan_input_ramah_lingkungan ?? '') === 'ya' ? 'Ya' : ((($surveyResponse->penggunaan_input_ramah_lingkungan ?? '') === 'tidak') ? 'Tidak' : '-') }}</div>
                @endif
            @endif
        </div>
    </div>
    @endif

    @if(!empty($showBlocks['blok3a']))
    <div class="section">
        <h2>Blok IIIA. Kondisi Perekonomian</h2>
        {{-- PDF-friendly quarterly tables to prevent width overflow --}}
        @include('bps.sibstr.partials.pdf-blok3a', ['surveyResponse' => $surveyResponse])
    </div>
    @endif

    @if(!empty($showBlocks['blok3bIndustri']) || !empty($showBlocks['blok3bNonIndustri']))
    <div class="section">
        <h2>Blok IIIB. Pendapatan & Pengeluaran @if(!empty($showBlocks['blok3bIndustri']))(Industri)@else(Non-Industri)@endif</h2>
        @php
            $d = !empty($showBlocks['blok3bIndustri']) ? ($surveyResponse->blok3b_industri_data ?? []) : ($surveyResponse->blok3b_nonindustri_data ?? []);
        @endphp
        <div class="kv">
            @if(!empty($showBlocks['blok3bIndustri']))
                <div class="k">304a. Royalti/bunga/dividen (triwulan lalu)</div><div class="v">{{ nf_idr($d['q304a'] ?? null) }}</div>
                <div class="k">304b. Royalti/bunga/dividen (tahun 2025)</div><div class="v">{{ nf_idr($d['q304b'] ?? null) }}</div>
                <div class="k">305. % Pendapatan online</div><div class="v">{{ nf_plain($d['q305_online'] ?? null) }}</div>
            @else
                <div class="k">303a. Pendapatan penjualan (triwulan lalu)</div><div class="v">{{ nf_idr($d['q303'] ?? null) }}</div>
                <div class="k">303b. Pendapatan penjualan (tahun 2025)</div><div class="v">{{ nf_idr($d['q303_year'] ?? null) }}</div>
                <div class="k">304a. Royalti/bunga/dividen (triwulan lalu)</div><div class="v">{{ nf_idr($d['q304'] ?? null) }}</div>
                <div class="k">304b. Royalti/bunga/dividen (tahun 2025)</div><div class="v">{{ nf_idr($d['q304_year'] ?? null) }}</div>
                <div class="k">305a. Total pendapatan (triwulan lalu)</div><div class="v">{{ nf_idr($d['q305'] ?? null) }}</div>
                <div class="k">305b. Total pendapatan (tahun 2025)</div><div class="v">{{ nf_idr($d['q305_year'] ?? null) }}</div>
                <div class="k">306. % Pendapatan online</div><div class="v">{{ nf_plain($d['q306_online'] ?? null) }}</div>
            @endif

            @if(!empty($showBlocks['blok3bIndustri']))
                <div class="k">307a. Persediaan BB, BBk, dsb (awal triwulan)</div><div class="v">{{ nf_idr($d['q306_awal'] ?? null) }}</div>
                <div class="k">307b. Persediaan BB, BBk, dsb (akhir triwulan)</div><div class="v">{{ nf_idr($d['q306_akhir'] ?? null) }}</div>
                <div class="k">307c. Persediaan BB, BBk, dsb (awal 2025)</div><div class="v">{{ nf_idr($d['q306_year_awal'] ?? null) }}</div>
                <div class="k">307d. Persediaan BB, BBk, dsb (akhir 2025)</div><div class="v">{{ nf_idr($d['q306_year_akhir'] ?? null) }}</div>

                <div class="k">308a. Barang dalam proses (awal triwulan)</div><div class="v">{{ nf_idr($d['q307_awal'] ?? null) }}</div>
                <div class="k">308b. Barang dalam proses (akhir triwulan)</div><div class="v">{{ nf_idr($d['q307_akhir'] ?? null) }}</div>
                <div class="k">308c. Barang dalam proses (awal 2025)</div><div class="v">{{ nf_idr($d['q307_year_awal'] ?? null) }}</div>
                <div class="k">308d. Barang dalam proses (akhir 2025)</div><div class="v">{{ nf_idr($d['q307_year_akhir'] ?? null) }}</div>

                <div class="k">309a. Barang jadi (awal triwulan)</div><div class="v">{{ nf_idr($d['q308_awal'] ?? null) }}</div>
                <div class="k">309b. Barang jadi (akhir triwulan)</div><div class="v">{{ nf_idr($d['q308_akhir'] ?? null) }}</div>
                <div class="k">309c. Barang jadi (awal 2025)</div><div class="v">{{ nf_idr($d['q308_year_awal'] ?? null) }}</div>
                <div class="k">309d. Barang jadi (akhir 2025)</div><div class="v">{{ nf_idr($d['q308_year_akhir'] ?? null) }}</div>

                <div class="k">310a. Total persediaan (awal triwulan)</div><div class="v">{{ nf_idr($d['q309_awal'] ?? null) }}</div>
                <div class="k">310b. Total persediaan (akhir triwulan)</div><div class="v">{{ nf_idr($d['q309_akhir'] ?? null) }}</div>
                <div class="k">310c. Total persediaan (awal 2025)</div><div class="v">{{ nf_idr($d['q310b_awal'] ?? null) }}</div>
                <div class="k">310d. Total persediaan (akhir 2025)</div><div class="v">{{ nf_idr($d['q310b_akhir'] ?? null) }}</div>
            @else
                <div class="k">307a. Persediaan BB, BBk, dsb (awal triwulan)</div><div class="v">{{ nf_idr($d['q306a'] ?? null) }}</div>
                <div class="k">307b. Persediaan BB, BBk, dsb (akhir triwulan)</div><div class="v">{{ nf_idr($d['q306b'] ?? null) }}</div>
                <div class="k">307c. Persediaan BB, BBk, dsb (awal 2025)</div><div class="v">{{ nf_idr($d['q306_year_awal'] ?? null) }}</div>
                <div class="k">307d. Persediaan BB, BBk, dsb (akhir 2025)</div><div class="v">{{ nf_idr($d['q306_year_akhir'] ?? null) }}</div>

                <div class="k">308a. Barang dalam proses (awal triwulan)</div><div class="v">{{ nf_idr($d['q307a'] ?? null) }}</div>
                <div class="k">308b. Barang dalam proses (akhir triwulan)</div><div class="v">{{ nf_idr($d['q307b'] ?? null) }}</div>
                <div class="k">308c. Barang dalam proses (awal 2025)</div><div class="v">{{ nf_idr($d['q307_year_awal'] ?? null) }}</div>
                <div class="k">308d. Barang dalam proses (akhir 2025)</div><div class="v">{{ nf_idr($d['q307_year_akhir'] ?? null) }}</div>

                <div class="k">309a. Barang jadi (awal triwulan)</div><div class="v">{{ nf_idr($d['q308a'] ?? null) }}</div>
                <div class="k">309b. Barang jadi (akhir triwulan)</div><div class="v">{{ nf_idr($d['q308b'] ?? null) }}</div>
                <div class="k">309c. Barang jadi (awal 2025)</div><div class="v">{{ nf_idr($d['q308_year_awal'] ?? null) }}</div>
                <div class="k">309d. Barang jadi (akhir 2025)</div><div class="v">{{ nf_idr($d['q308_year_akhir'] ?? null) }}</div>

                <div class="k">310a. Total persediaan (awal triwulan)</div><div class="v">{{ nf_idr($d['q309a'] ?? null) }}</div>
                <div class="k">310b. Total persediaan (akhir triwulan)</div><div class="v">{{ nf_idr($d['q309b'] ?? null) }}</div>
                <div class="k">310c. Total persediaan (awal 2025)</div><div class="v">{{ nf_idr($d['q310b_awal'] ?? null) }}</div>
                <div class="k">310d. Total persediaan (akhir 2025)</div><div class="v">{{ nf_idr($d['q310b_akhir'] ?? null) }}</div>
            @endif

            <div class="k">311a. Total upah & gaji (triwulan lalu)</div><div class="v">{{ nf_idr($d['q311a'] ?? null) }}</div>
            <div class="k">311b. Total upah & gaji (tahun 2025)</div><div class="v">{{ nf_idr($d['q311b'] ?? null) }}</div>
            <div class="k">311b.1 Pegawai produksi</div><div class="v">{{ nf_idr($d['q311b1'] ?? null) }}</div>
            <div class="k">311b.2 Selain produksi</div><div class="v">{{ nf_idr($d['q311b2'] ?? null) }}</div>

            <div class="k">312. Penambahan aset tetap (triwulan lalu)</div><div class="v">{{ nf_idr($d['q311'] ?? null) }}</div>
            <div class="k">313a. Biaya produksi (triwulan lalu)</div><div class="v">{{ nf_idr($d['q312'] ?? null) }}</div>
            <div class="k">313b. Biaya produksi (tahun 2025)</div><div class="v">{{ nf_idr($d['q312_year'] ?? null) }}</div>
            <div class="k">314a. Biaya operasional (triwulan lalu)</div><div class="v">{{ nf_idr($d['q313'] ?? null) }}</div>
            <div class="k">314b. Biaya operasional (tahun 2025)</div><div class="v">{{ nf_idr($d['q313_year'] ?? null) }}</div>
            <div class="k">315a. Biaya non-operasional (triwulan lalu)</div><div class="v">{{ nf_idr($d['q315a'] ?? null) }}</div>
            <div class="k">315b. Biaya non-operasional (tahun 2025)</div><div class="v">{{ nf_idr($d['q315b'] ?? null) }}</div>

            <div class="k">316. % Ekspor</div><div class="v">{{ nf_plain($d['q314'] ?? null) }}</div>
            <div class="k">317. % Impor</div><div class="v">{{ nf_plain($d['q315'] ?? null) }}</div>

            <div class="k">318a. Aset tanah & bangunan (31 Des 2025)</div><div class="v">{{ nf_idr($d['q318a'] ?? null) }}</div>
            <div class="k">318b. Aset selain itu (31 Des 2025)</div><div class="v">{{ nf_idr($d['q318b'] ?? null) }}</div>
            <div class="k">318c. Total aset</div><div class="v">{{ nf_idr($d['q318c'] ?? null) }}</div>
            <div class="k">318c1. Rentang nilai</div><div class="v">{{ $rangeLabels[$d['q318c_range'] ?? ''] ?? ($d['q318c_range'] ?? '-') }}</div>
            <div class="k">318d. Luas tanah (m2)</div><div class="v">{{ nf_plain($d['q318d_area'] ?? null) }}</div>

            <div class="k">319a. Modal Pribadi (%)</div><div class="v">{{ nf_plain($d['q319a'] ?? null) }}</div>
            <div class="k">319b. Modal Nonprofit (%)</div><div class="v">{{ nf_plain($d['q319b'] ?? null) }}</div>
            <div class="k">319c. Modal Korporasi Publik (%)</div><div class="v">{{ nf_plain($d['q319c'] ?? null) }}</div>
            <div class="k">319d. Modal Korporasi Non Publik (%)</div><div class="v">{{ nf_plain($d['q319d'] ?? null) }}</div>
            <div class="k">319e. Modal Pemerintah (%)</div><div class="v">{{ nf_plain($d['q319e'] ?? null) }}</div>
            <div class="k">319f. Modal Asing (%)</div><div class="v">{{ nf_plain($d['q319f'] ?? null) }}</div>
            <div class="k">319g. Total (%)</div><div class="v">{{ nf_plain($d['q319g'] ?? null) }}</div>
        </div>
    </div>
    @endif

    @if(!empty($showBlocks['blok4']))
    <div class="section">
        <h2>Blok IV. Fenomena dan Catatan</h2>
        @php $b4 = $surveyResponse->blok4_data ?? []; @endphp
        <div class="kv">
            <div class="k">401. Triwulan I</div><div class="v">{{ nf_plain($b4['triwulan1'] ?? null) }}</div>
            <div class="k">402. Triwulan II</div><div class="v">{{ nf_plain($b4['triwulan2'] ?? null) }}</div>
            <div class="k">403. Triwulan III</div><div class="v">{{ nf_plain($b4['triwulan3'] ?? null) }}</div>
            <div class="k">404. Triwulan IV</div><div class="v">{{ nf_plain($b4['triwulan4'] ?? null) }}</div>
        </div>
    </div>
    @endif

    @if(!empty($showBlocks['blok5']))
    <div class="section">
        <h2>Blok V. Kondisi dan Prospek Usaha</h2>
        @php
            $b5 = $surveyResponse->blok5_data ?? [];
            $rows = [
                ['key' => '501', 'label' => 'Pesanan', 'type' => 'normal'],
                ['key' => '502', 'label' => 'Produksi', 'type' => 'normal'],
                ['key' => '503', 'label' => 'Kapasitas Produksi', 'type' => 'normal'],
                ['key' => '504', 'label' => 'Tenaga Kerja', 'type' => 'normal'],
                ['key' => '505', 'label' => 'Jam Kerja', 'type' => 'normal'],
                ['key' => '506', 'label' => 'Waktu Pengiriman Pemasok', 'type' => 'delivery'],
                ['key' => '507', 'label' => 'Persediaan Bahan Baku', 'type' => 'normal'],
            ];
            $periods = ['p1','p2','p3','p4','p5','p6'];
            $labelsNormal = ['naik' => 'Naik', 'tetap' => 'Tetap', 'turun' => 'Turun'];
            $labelsDelivery = ['lebih_cepat' => 'Lebih cepat', 'tetap' => 'Tetap', 'lebih_lambat' => 'Lebih lambat'];
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Komponen</th>
                    <th>TW I-2025 vs TW IV-2024</th>
                    <th>TW II-2025 vs TW I-2025</th>
                    <th>TW III-2025 vs TW II-2025</th>
                    <th>Prospek TW IV-2025 vs TW III-2025</th>
                    <th>TW IV-2025 vs TW III-2025</th>
                    <th>Prospek TW I-2026 vs TW IV-2025</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['key'] }}. {{ $row['label'] }}</td>
                        @foreach($periods as $period)
                            @php
                                $val = $b5[$row['key']][$period] ?? null;
                                $labelMap = $row['type'] === 'delivery' ? $labelsDelivery : $labelsNormal;
                                $txt = $labelMap[$val] ?? '-';
                            @endphp
                            <td>{{ $txt }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(!empty($showBlocks['blok6']))
    <div class="section">
        <h2>Blok VI. Catatan</h2>
        <div class="kv">
            <div class="k">601. Catatan</div>
            <div class="v">{{ nf_plain($surveyResponse->catatan ?? null) }}</div>
        </div>
    </div>
    @endif

    <div class="section">
        <h2>Informasi Kontak BPS</h2>
        <div class="small">
            <div><strong>Penghubung:</strong> {{ $bpsRiData['penghubung'] }}</div>
            <div><strong>Telepon:</strong> {{ $bpsRiData['telepon'] }} | <strong>Fax:</strong> {{ $bpsRiData['fax'] }}</div>
            <div><strong>Email:</strong> {{ $bpsRiData['email'] }}</div>
            <div><strong>Alamat:</strong> {{ $bpsRiData['alamat'] }}</div>
        </div>
    </div>
</body>
</html>