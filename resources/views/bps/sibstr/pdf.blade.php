@php
    $companyName = $surveyResponse->nama_perusahaan ?? '-';
    $submissionAt = optional($surveyResponse->updated_at)->format('d M Y, H:i') ?? '-';
    $surveyTahun  = $surveyResponse->tahun ?? 2025;
    $isAnnualPdf  = (((int)($surveyResponse->triwulan ?? 0)) === 0);
    $finishStatus = $surveyResponse->annual_survey_status ?? null;

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
        <div class="title">
            Kuesioner SIBSTR &mdash; {{ $isAnnualPdf ? 'Tahunan' : 'Triwulanan' }} {{ $surveyTahun }}
        </div>
        <div class="meta" style="margin-top:4px;">
            <div><strong>Survei Industri Besar dan Sedang (SIBSTR)</strong> &mdash; {{ $isAnnualPdf ? 'Pencacahan Tahunan' : 'Pencacahan Triwulanan' }}</div>
            <div style="margin-top:4px;"><strong>Perusahaan:</strong> {{ $companyName }}</div>
            <div><strong>Terakhir Diperbarui:</strong> {{ $submissionAt }}</div>
            @if($isAnnualPdf)
            <div style="margin-top:6px;">
                <strong>Status:</strong>&nbsp;
                @if($finishStatus === 'FINISH_SURVEY')
                    <span style="background:#d1fae5;color:#065f46;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:700;">&#10003; FINISH_SURVEY</span>
                @else
                    <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:600;">Dalam Proses</span>
                @endif
            </div>
            @endif
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

                @if(!empty($blok2Visibility['showQ203']) && $isAnnualPdf)
                    <div class="k">203. Jumlah cabang & unit usaha</div>
                    <div class="v">{{ nf_plain($surveyResponse->jumlah_cabang_dan_unit_usaha ?? null) }}</div>
                @endif

                @if(!empty($blok2Visibility['showQ204']) && $isAnnualPdf)
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
                    @if($isAnnualPdf)
                    <div class="k">205. Bulan aktif 2025</div>
                    <div class="v">{{ nf_plain($surveyResponse->jumlah_bulan_aktif_2025 ?? null) }}</div>
                    <div class="k">206. Rata-rata hari kerja/bulan 2025</div>
                    <div class="v">{{ nf_plain($surveyResponse->rata_hari_kerja_bulanan_2025 ?? null) }}</div>
                    @endif
                    @if(($surveyResponse->triwulan ?? 0) == 0)
                    <div class="k">207a. Jumlah seluruh pekerja</div><div class="v">{{ nf_plain($surveyResponse->jumlah_seluruh_pekerja ?? null) }}</div>
                    <div class="k">207b.1. Pekerja laki-laki</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_laki_laki ?? null) }}</div>
                    <div class="k">207b.2. Pekerja perempuan</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_perempuan ?? null) }}</div>
                    <div class="k">207c.1. Bukan outsourcing produksi</div><div class="v">{{ nf_plain($surveyResponse->pekerja_bukan_outsourcing_produksi ?? null) }}</div>
                    <div class="k">207c.2. Bukan outsourcing lainnya</div><div class="v">{{ nf_plain($surveyResponse->pekerja_bukan_outsourcing_lainnya ?? null) }}</div>
                    <div class="k">207d.1. Outsourcing produksi</div><div class="v">{{ nf_plain($surveyResponse->pekerja_outsourcing_produksi ?? null) }}</div>
                    <div class="k">207d.2. Outsourcing lainnya</div><div class="v">{{ nf_plain($surveyResponse->pekerja_outsourcing_lainnya ?? null) }}</div>
                    <div class="k">207e. Pekerja asing</div><div class="v">{{ nf_plain($surveyResponse->tenaga_kerja_asing ?? null) }}</div>
                    @else
                    <div class="k">207. Rata-rata tenaga kerja (triwulan)</div><div class="v">{{ nf_plain($surveyResponse->rata_rata_tenaga_kerja ?? null) }}</div>
                    @endif

                    <div class="k">208e. Uraian kegiatan utama</div><div class="v">{{ nf_plain($surveyResponse->kegiatan_utama_perusahaan ?? null) }}</div>
                    <div class="k">208f. KBLI utama</div><div class="v">{{ nf_plain($surveyResponse->kbli_utama ?? null) }} @if(!empty($kbliPrefix)) <span class="badge">{{ ($kbliPrefix >= 10 && $kbliPrefix <= 33) ? 'Industri' : 'Non-Industri' }}</span> @endif</div>

                    @if($isAnnualPdf)
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
                    @endif {{-- end isAnnualPdf for Q209-Q211 --}}
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
            $tw3bPdf    = (int)($surveyResponse->triwulan ?? 0);
            $yr3bPdf    = (int)($surveyResponse->tahun ?? 2025);
            $isTw3bPdf  = $tw3bPdf > 0;
            $d          = !empty($showBlocks['blok3bIndustri']) ? ($surveyResponse->blok3b_industri_data ?? []) : ($surveyResponse->blok3b_nonindustri_data ?? []);
            if ($isTw3bPdf) {
                $twLbl3b = ['satu','dua','tiga','empat'][$tw3bPdf - 1] ?? 'satu';
                $twAwal3b  = match($tw3bPdf) { 1=>"1 Januari {$yr3bPdf}", 2=>"1 April {$yr3bPdf}", 3=>"1 Juli {$yr3bPdf}", 4=>"1 Oktober {$yr3bPdf}", default=>'' };
                $twAkhir3b = match($tw3bPdf) { 1=>"31 Maret {$yr3bPdf}", 2=>"30 Juni {$yr3bPdf}", 3=>"30 September {$yr3bPdf}", 4=>"31 Desember {$yr3bPdf}", default=>'' };
            }
            $rangeLabels3b = ['1'=>'1 s.d. Rp 500 juta','2'=>'Lebih dari Rp 500 juta s.d. Rp 1 miliar','3'=>'Lebih dari Rp 1 miliar s.d. Rp 5 miliar','4'=>'Lebih dari Rp 5 miliar s.d. Rp 10 miliar','5'=>'Lebih dari Rp 10 miliar'];
        @endphp
        <div class="kv">
        @if($isTw3bPdf)
            {{-- ── TRIWULANAN ── --}}
            @if(!empty($showBlocks['blok3bNonIndustri']))
                <div class="k">303. Pendapatan penjualan (triwulan {{ $twLbl3b }})</div><div class="v">{{ nf_idr($d['q303'] ?? null) }}</div>
            @endif
            <div class="k">304. Pendapatan royalti, bunga, deviden, dll (triwulan {{ $twLbl3b }})</div><div class="v">{{ nf_idr($d['q304'] ?? null) }}</div>
            @if(!empty($showBlocks['blok3bNonIndustri']))
                <div class="k">305. Total pendapatan (triwulan {{ $twLbl3b }})</div><div class="v">{{ nf_idr($d['q305'] ?? null) }}</div>
            @endif

            <div class="k">306a. Persediaan BB/BBk (awal — {{ $twAwal3b }})</div><div class="v">{{ nf_idr($d['q306_awal'] ?? null) }}</div>
            <div class="k">306b. Persediaan BB/BBk (akhir — {{ $twAkhir3b }})</div><div class="v">{{ nf_idr($d['q306_akhir'] ?? null) }}</div>
            <div class="k">307a. Persediaan BDP (awal — {{ $twAwal3b }})</div><div class="v">{{ nf_idr($d['q307_awal'] ?? null) }}</div>
            <div class="k">307b. Persediaan BDP (akhir — {{ $twAkhir3b }})</div><div class="v">{{ nf_idr($d['q307_akhir'] ?? null) }}</div>
            <div class="k">308a. Persediaan barang jadi (awal — {{ $twAwal3b }})</div><div class="v">{{ nf_idr($d['q308_awal'] ?? null) }}</div>
            <div class="k">308b. Persediaan barang jadi (akhir — {{ $twAkhir3b }})</div><div class="v">{{ nf_idr($d['q308_akhir'] ?? null) }}</div>
            <div class="k">309a. Total persediaan (awal — {{ $twAwal3b }})</div><div class="v">{{ nf_idr($d['q309_awal'] ?? null) }}</div>
            <div class="k">309b. Total persediaan (akhir — {{ $twAkhir3b }})</div><div class="v">{{ nf_idr($d['q309_akhir'] ?? null) }}</div>

            @php
                $q310TwVal = !empty($showBlocks['blok3bIndustri']) ? ($d['q310'] ?? null) : ($d['q310_tw'] ?? null);
                $q311TwVal = !empty($showBlocks['blok3bIndustri']) ? ($d['q311'] ?? null) : ($d['q311_tw'] ?? null);
            @endphp
            <div class="k">310. Total upah & gaji (triwulan {{ $twLbl3b }})</div><div class="v">{{ nf_idr($q310TwVal) }}</div>
            <div class="k">311. Penambahan aset tetap (triwulan {{ $twLbl3b }})</div><div class="v">{{ nf_idr($q311TwVal) }}</div>
            <div class="k">312. Biaya produksi (triwulan {{ $twLbl3b }})</div><div class="v">{{ nf_idr($d['q312_tw'] ?? null) }}</div>
            <div class="k">313. Biaya operasional (triwulan {{ $twLbl3b }})</div><div class="v">{{ nf_idr($d['q313_tw'] ?? null) }}</div>
            <div class="k">314. % produksi diekspor</div><div class="v">{{ nf_plain($d['q314_tw'] ?? null) }}{{ ($d['q314_tw'] ?? '') !== '' ? ' %' : '' }}</div>
            <div class="k">315. % bahan baku dari impor</div><div class="v">{{ nf_plain($d['q315_tw'] ?? null) }}{{ ($d['q315_tw'] ?? '') !== '' ? ' %' : '' }}</div>

        @else
            {{-- ── TAHUNAN ── --}}
            @if(!empty($showBlocks['blok3bNonIndustri']))
                <div class="k">303a. Pendapatan penjualan (triwulan lalu)</div><div class="v">{{ nf_idr($d['q303'] ?? null) }}</div>
                <div class="k">303b. Pendapatan penjualan (tahun {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q303_year'] ?? null) }}</div>
                <div class="k">305a. Total pendapatan (triwulan lalu)</div><div class="v">{{ nf_idr($d['q305'] ?? null) }}</div>
                <div class="k">305b. Total pendapatan (tahun {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q305_year'] ?? null) }}</div>
            @endif

            @if(!empty($showBlocks['blok3bIndustri']))
                <div class="k">307a. Persediaan BB, BBk, dsb (awal triwulan)</div><div class="v">{{ nf_idr($d['q306_awal'] ?? null) }}</div>
                <div class="k">307b. Persediaan BB, BBk, dsb (akhir triwulan)</div><div class="v">{{ nf_idr($d['q306_akhir'] ?? null) }}</div>
                <div class="k">307c. Persediaan BB, BBk, dsb (awal {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q306_year_awal'] ?? null) }}</div>
                <div class="k">307d. Persediaan BB, BBk, dsb (akhir {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q306_year_akhir'] ?? null) }}</div>
                <div class="k">308a. Barang dalam proses (awal triwulan)</div><div class="v">{{ nf_idr($d['q307_awal'] ?? null) }}</div>
                <div class="k">308b. Barang dalam proses (akhir triwulan)</div><div class="v">{{ nf_idr($d['q307_akhir'] ?? null) }}</div>
                <div class="k">308c. Barang dalam proses (awal {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q307_year_awal'] ?? null) }}</div>
                <div class="k">308d. Barang dalam proses (akhir {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q307_year_akhir'] ?? null) }}</div>
                <div class="k">309a. Barang jadi (awal triwulan)</div><div class="v">{{ nf_idr($d['q308_awal'] ?? null) }}</div>
                <div class="k">309b. Barang jadi (akhir triwulan)</div><div class="v">{{ nf_idr($d['q308_akhir'] ?? null) }}</div>
                <div class="k">309c. Barang jadi (awal {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q308_year_awal'] ?? null) }}</div>
                <div class="k">309d. Barang jadi (akhir {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q308_year_akhir'] ?? null) }}</div>
                <div class="k">310a. Total persediaan (awal triwulan)</div><div class="v">{{ nf_idr($d['q309_awal'] ?? null) }}</div>
                <div class="k">310b. Total persediaan (akhir triwulan)</div><div class="v">{{ nf_idr($d['q309_akhir'] ?? null) }}</div>
                <div class="k">310c. Total persediaan (awal {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q310b_awal'] ?? null) }}</div>
                <div class="k">310d. Total persediaan (akhir {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q310b_akhir'] ?? null) }}</div>
            @else
                <div class="k">307a. Persediaan BB, BBk, dsb (awal triwulan)</div><div class="v">{{ nf_idr($d['q306a'] ?? null) }}</div>
                <div class="k">307b. Persediaan BB, BBk, dsb (akhir triwulan)</div><div class="v">{{ nf_idr($d['q306b'] ?? null) }}</div>
                <div class="k">307c. Persediaan BB, BBk, dsb (awal {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q306_year_awal'] ?? null) }}</div>
                <div class="k">307d. Persediaan BB, BBk, dsb (akhir {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q306_year_akhir'] ?? null) }}</div>
                <div class="k">308a. Barang dalam proses (awal triwulan)</div><div class="v">{{ nf_idr($d['q307a'] ?? null) }}</div>
                <div class="k">308b. Barang dalam proses (akhir triwulan)</div><div class="v">{{ nf_idr($d['q307b'] ?? null) }}</div>
                <div class="k">308c. Barang dalam proses (awal {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q307_year_awal'] ?? null) }}</div>
                <div class="k">308d. Barang dalam proses (akhir {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q307_year_akhir'] ?? null) }}</div>
                <div class="k">309a. Barang jadi (awal triwulan)</div><div class="v">{{ nf_idr($d['q308a'] ?? null) }}</div>
                <div class="k">309b. Barang jadi (akhir triwulan)</div><div class="v">{{ nf_idr($d['q308b'] ?? null) }}</div>
                <div class="k">309c. Barang jadi (awal {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q308_year_awal'] ?? null) }}</div>
                <div class="k">309d. Barang jadi (akhir {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q308_year_akhir'] ?? null) }}</div>
                <div class="k">310a. Total persediaan (awal triwulan)</div><div class="v">{{ nf_idr($d['q309a'] ?? null) }}</div>
                <div class="k">310b. Total persediaan (akhir triwulan)</div><div class="v">{{ nf_idr($d['q309b'] ?? null) }}</div>
                <div class="k">310c. Total persediaan (awal {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q310b_awal'] ?? null) }}</div>
                <div class="k">310d. Total persediaan (akhir {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q310b_akhir'] ?? null) }}</div>
            @endif

            <div class="k">311a. Total upah & gaji (triwulan lalu)</div><div class="v">{{ nf_idr($d['q311a'] ?? null) }}</div>
            <div class="k">311b. Total upah & gaji (tahun {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q311b'] ?? null) }}</div>
            <div class="k">311b.1 Pegawai produksi</div><div class="v">{{ nf_idr($d['q311b1'] ?? null) }}</div>
            <div class="k">311b.2 Selain produksi</div><div class="v">{{ nf_idr($d['q311b2'] ?? null) }}</div>
            <div class="k">312. Penambahan aset tetap (triwulan lalu)</div><div class="v">{{ nf_idr($d['q311'] ?? null) }}</div>
            <div class="k">314a. Biaya operasional (triwulan lalu)</div><div class="v">{{ nf_idr($d['q313'] ?? null) }}</div>
            <div class="k">314b. Biaya operasional (tahun {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q313_year'] ?? null) }}</div>
            <div class="k">315a. Biaya non-operasional (triwulan lalu)</div><div class="v">{{ nf_idr($d['q315a'] ?? null) }}</div>
            <div class="k">315b. Biaya non-operasional (tahun {{ $surveyTahun }})</div><div class="v">{{ nf_idr($d['q315b'] ?? null) }}</div>

            @if(!empty($showBlocks['blok3bIndustri']))
            {{-- Q318: Moda Transportasi (industri tahunan) --}}
            <div class="k" style="grid-column:1/-1;font-weight:600;background:#eff6ff;color:#1e3a8a;padding:4px 8px;margin-top:4px;">318. Moda Transportasi — Pengangkutan Barang Tahun {{ $surveyTahun }}</div>
            @foreach(['a'=>'Angkutan jalan','b'=>'Angkutan kereta api','c'=>'Angkutan air sungai, danau, dan penyeberangan','d'=>'Angkutan air laut','e'=>'Angkutan udara'] as $mKey=>$mLabel)
                @if(($d['q318'.$mKey.'_freq'] ?? '') !== '' || ($d['q318'.$mKey.'_biaya'] ?? '') !== '')
                <div class="k">318{{ $mKey }}. {{ $mLabel }} — Frekuensi (kali)</div>
                <div class="v">{{ nf_plain($d['q318'.$mKey.'_freq'] ?? null) }}</div>
                <div class="k">318{{ $mKey }}. {{ $mLabel }} — Biaya (Rp)</div>
                <div class="v">{{ nf_idr($d['q318'.$mKey.'_biaya'] ?? null) }}</div>
                @endif
            @endforeach
            {{-- Q319: Persentase Pihak Ketiga (industri tahunan) --}}
            <div class="k">319. % angkutan menggunakan jasa pihak ketiga</div>
            <div class="v">{{ nf_plain($d['q319_persen_pihak_ketiga'] ?? null) }}{{ ($d['q319_persen_pihak_ketiga'] ?? '') !== '' ? ' %' : '' }}</div>
            @endif

            @if(!empty($showBlocks['blok3bNonIndustri']))
            @php
                $d_ni_318a = (float)($d['q318a'] ?? 0); $d_ni_318b = (float)($d['q318b'] ?? 0);
                $d_ni_318c = (float)($d['q318c'] ?? 0);
                if ($d_ni_318c <= 0) $d_ni_318c = $d_ni_318a + $d_ni_318b;
                $ni_319parts = ['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h'];
                $d_ni_319i = (float)($d['q319i'] ?? 0);
                if ($d_ni_319i <= 0) { foreach ($ni_319parts as $_k) { $d_ni_319i += (float)($d[$_k] ?? 0); } }
            @endphp
            <div class="k">318a. Tanah dan bangunan (Rp)</div><div class="v">{{ nf_idr($d_ni_318a > 0 ? $d_ni_318a : null) }}</div>
            <div class="k">318b. Selain tanah dan bangunan (Rp)</div><div class="v">{{ nf_idr($d_ni_318b > 0 ? $d_ni_318b : null) }}</div>
            <div class="k">318c. Nilai total aset (a + b)</div><div class="v">{{ nf_idr($d_ni_318c > 0 ? $d_ni_318c : null) }}</div>
            <div class="k">318c1. Rentang nilai</div><div class="v">{{ $rangeLabels3b[$d['q318c_range'] ?? ''] ?? (($d['q318c_range'] ?? '') !== '' ? $d['q318c_range'] : '-') }}</div>
            <div class="k">318d. Luas tanah (m²)</div><div class="v">{{ nf_plain($d['q318d_area'] ?? null) }}</div>
            <div class="k">319a. Pribadi/Perorangan (%)</div><div class="v">{{ nf_plain($d['q319a'] ?? null) }}</div>
            <div class="k">319b. Lembaga Nonprofit (%)</div><div class="v">{{ nf_plain($d['q319b'] ?? null) }}</div>
            <div class="k">319c. Korporasi Publik (%)</div><div class="v">{{ nf_plain($d['q319c'] ?? null) }}</div>
            <div class="k">319d. Korporasi Non Publik (%)</div><div class="v">{{ nf_plain($d['q319d'] ?? null) }}</div>
            <div class="k">319e. Pemerintah Pusat (%)</div><div class="v">{{ nf_plain($d['q319e'] ?? null) }}</div>
            <div class="k">319f. Pemerintah Daerah (%)</div><div class="v">{{ nf_plain($d['q319f'] ?? null) }}</div>
            <div class="k">319g. Perusahaan Swasta Nasional (%)</div><div class="v">{{ nf_plain($d['q319g'] ?? null) }}</div>
            <div class="k">319h. Asing (%)</div><div class="v">{{ nf_plain($d['q319h'] ?? null) }}</div>
            <div class="k">319i. Total (%)</div><div class="v">{{ $d_ni_319i > 0 ? number_format($d_ni_319i, 2, ',', '.') : '-' }}</div>
            @endif
        @endif
        </div>
    </div>
    @endif

    @if(!empty($showBlocks['blok3c']))
    <div class="section">
        <h2>Blok IIIC. Bahan Baku dan Bahan Penolong</h2>
        @include('bps.sibstr.partials.pdf-blok3c', ['surveyResponse' => $surveyResponse])

        {{-- Nilai Aset & Kepemilikan Modal (industri only, moved from blok3b) --}}
        @php
            $d3c = $surveyResponse->blok3b_industri_data ?? [];
            $yn  = ['1' => 'Ya', '2' => 'Tidak'];
            $q318a_pdf = (float)($d3c['q318a'] ?? 0);
            $q318b_pdf = (float)($d3c['q318b'] ?? 0);
            $q318c_pdf = (float)($d3c['q318c'] ?? 0);
            if ($q318c_pdf <= 0) $q318c_pdf = $q318a_pdf + $q318b_pdf;
            $q319i_pdf = (float)($d3c['q319i'] ?? 0);
            if ($q319i_pdf <= 0) {
                foreach (['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h'] as $_k) {
                    $q319i_pdf += (float)($d3c[$_k] ?? 0);
                }
            }
        @endphp

        @if($q318a_pdf > 0 || $q318b_pdf > 0 || $q318c_pdf > 0 || !empty($d3c['q318c_range']) || !empty($d3c['q318d_area']))
        <div style="margin-top:12px; page-break-inside:avoid;">
            <div style="font-weight:700; font-size:12px; margin-bottom:6px; color:#1e40af;">Nilai Aset</div>
            <div class="kv">
                <div class="k">318a. Tanah dan bangunan (Rp)</div><div class="v">{{ nf_idr($q318a_pdf > 0 ? $q318a_pdf : null) }}</div>
                <div class="k">318b. Selain tanah dan bangunan (Rp)</div><div class="v">{{ nf_idr($q318b_pdf > 0 ? $q318b_pdf : null) }}</div>
                <div class="k">318c. Nilai total aset — jumlah a + b (Rp)</div><div class="v">{{ nf_idr($q318c_pdf > 0 ? $q318c_pdf : null) }}</div>
                <div class="k">318c1. Rentang nilai (jika c kosong)</div><div class="v">{{ $rangeLabels[$d3c['q318c_range'] ?? ''] ?? (($d3c['q318c_range'] ?? '') !== '' ? $d3c['q318c_range'] : '-') }}</div>
                <div class="k">318d. Luas tanah (m²)</div><div class="v">{{ nf_plain($d3c['q318d_area'] ?? null) }}</div>
            </div>
        </div>
        @endif

        @if(!empty($d3c['q319a']) || !empty($d3c['q319b']) || !empty($d3c['q319c']) || !empty($d3c['q319d']) || !empty($d3c['q319e']) || !empty($d3c['q319f']) || !empty($d3c['q319g']) || !empty($d3c['q319h']))
        <div style="margin-top:12px; page-break-inside:avoid;">
            <div style="font-weight:700; font-size:12px; margin-bottom:6px; color:#1e40af;">Kepemilikan Modal</div>
            <div class="kv">
                <div class="k">319a. Pribadi/Perorangan (%)</div><div class="v">{{ nf_plain($d3c['q319a'] ?? null) }}</div>
                <div class="k">319b. Lembaga Nonprofit yang Melayani Rumah Tangga (%)</div><div class="v">{{ nf_plain($d3c['q319b'] ?? null) }}</div>
                <div class="k">319c. Korporasi Publik (%)</div><div class="v">{{ nf_plain($d3c['q319c'] ?? null) }}</div>
                <div class="k">319d. Korporasi Non Publik (%)</div><div class="v">{{ nf_plain($d3c['q319d'] ?? null) }}</div>
                <div class="k">319e. Pemerintah Pusat (%)</div><div class="v">{{ nf_plain($d3c['q319e'] ?? null) }}</div>
                <div class="k">319f. Pemerintah Daerah (%)</div><div class="v">{{ nf_plain($d3c['q319f'] ?? null) }}</div>
                <div class="k">319g. Perusahaan Swasta Nasional (%)</div><div class="v">{{ nf_plain($d3c['q319g'] ?? null) }}</div>
                <div class="k">319h. Asing (%)</div><div class="v">{{ nf_plain($d3c['q319h'] ?? null) }}</div>
                <div class="k">319i. Total (%)</div><div class="v">{{ $q319i_pdf > 0 ? number_format($q319i_pdf, 2, ',', '.') : '-' }}</div>
            </div>
        </div>
        @endif

        {{-- Prospek & Kendala Usaha --}}
        @if(!empty($d3c['q320']) || !empty($d3c['q324']) || !empty($d3c['q325']))
        <div style="margin-top:12px; page-break-inside:avoid;">
            <div style="font-weight:700; font-size:12px; margin-bottom:6px; color:#1e40af;">Prospek dan Kendala Usaha/Perusahaan</div>
            <div class="kv">
                <div class="k">320a. Kendala: Permodalan</div><div class="v">{{ $yn[$d3c['q320'] ?? ''] ?? ($d3c['q320'] ?? '-') }}</div>
                <div class="k">320b. Kendala: Bahan baku</div><div class="v">{{ $yn[$d3c['q321'] ?? ''] ?? ($d3c['q321'] ?? '-') }}</div>
                <div class="k">320c. Kendala: Pemasaran</div><div class="v">{{ $yn[$d3c['q322'] ?? ''] ?? ($d3c['q322'] ?? '-') }}</div>
                <div class="k">320d. Kendala: Iklim Usaha</div><div class="v">{{ $yn[$d3c['q323'] ?? ''] ?? ($d3c['q323'] ?? '-') }}</div>
                <div class="k">321. Rencana rekrut/kembangkan {{ $surveyTahun + 1 }}</div><div class="v">{{ $yn[$d3c['q324'] ?? ''] ?? ($d3c['q324'] ?? '-') }}</div>
                <div class="k">322a. Strategi: Inovasi</div><div class="v">{{ $yn[$d3c['q325'] ?? ''] ?? ($d3c['q325'] ?? '-') }}</div>
                <div class="k">322b. Strategi: Pengembangan Teknologi</div><div class="v">{{ $yn[$d3c['q326'] ?? ''] ?? ($d3c['q326'] ?? '-') }}</div>
                <div class="k">322c. Strategi: Pemasaran</div><div class="v">{{ $yn[$d3c['q327'] ?? ''] ?? ($d3c['q327'] ?? '-') }}</div>
                <div class="k">322d. Strategi: Kemitraan</div><div class="v">{{ $yn[$d3c['q328'] ?? ''] ?? ($d3c['q328'] ?? '-') }}</div>
            </div>
        </div>
        @endif
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
            $b5      = $surveyResponse->blok5_data ?? [];
            $tw5pdf  = (int)($surveyResponse->triwulan ?? 0);
            $yr5pdf  = (int)($surveyResponse->tahun ?? 2025);
            $isTw5pdf = $tw5pdf > 0;
            $rows = [
                ['key' => '501', 'label' => 'Pesanan',                  'type' => 'normal'],
                ['key' => '502', 'label' => 'Produksi',                 'type' => 'normal'],
                ['key' => '503', 'label' => 'Kapasitas Produksi',       'type' => 'normal'],
                ['key' => '504', 'label' => 'Tenaga Kerja',             'type' => 'normal'],
                ['key' => '505', 'label' => 'Jam Kerja',                'type' => 'normal'],
                ['key' => '506', 'label' => 'Waktu Pengiriman Pemasok', 'type' => 'delivery'],
                ['key' => '507', 'label' => 'Persediaan Bahan Baku',    'type' => 'normal'],
            ];
            $labelsNormal   = ['naik' => 'Naik',        'tetap' => 'Tetap', 'turun' => 'Turun'];
            $labelsDelivery = ['lebih_cepat' => 'Lebih cepat', 'tetap' => 'Tetap', 'lebih_lambat' => 'Lebih lambat'];
            if ($isTw5pdf) {
                $twLbls5 = ['I','II','III','IV'];
                $prevTw5 = $tw5pdf === 1 ? 4 : $tw5pdf - 1;
                $prevYr5 = $tw5pdf === 1 ? $yr5pdf - 1 : $yr5pdf;
                $nextTw5 = $tw5pdf === 4 ? 1 : $tw5pdf + 1;
                $nextYr5 = $tw5pdf === 4 ? $yr5pdf + 1 : $yr5pdf;
                $periods5   = ['p1', 'p2'];
                $headers5   = [
                    "Kondisi TW {$twLbls5[$tw5pdf-1]}-{$yr5pdf} vs TW {$twLbls5[$prevTw5-1]}-{$prevYr5}",
                    "Prospek TW {$twLbls5[$nextTw5-1]}-{$nextYr5} vs TW {$twLbls5[$tw5pdf-1]}-{$yr5pdf}",
                ];
            } else {
                $periods5 = ['p1','p2','p3','p5','p6'];
                $headers5 = [
                    "TW I-{$yr5pdf} vs TW IV-".($yr5pdf-1),
                    "TW II-{$yr5pdf} vs TW I-{$yr5pdf}",
                    "TW III-{$yr5pdf} vs TW II-{$yr5pdf}",
                    "TW IV-{$yr5pdf} vs TW III-{$yr5pdf}",
                    "Prospek TW I-".($yr5pdf+1)." vs TW IV-{$yr5pdf}",
                ];
            }
        @endphp
        <table>
            <thead>
                <tr>
                    <th>Komponen</th>
                    @foreach($headers5 as $h5)
                        <th>{{ $h5 }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row['key'] }}. {{ $row['label'] }}</td>
                        @foreach($periods5 as $period)
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