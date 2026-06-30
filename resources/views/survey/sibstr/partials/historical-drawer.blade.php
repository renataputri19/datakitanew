{{--
  Historical Data Drawer
  Slide-over panel showing all prior-period survey data for reference comparison.

  Props (passed via @include):
    $historicalResponses  — Illuminate\Support\Collection<SurveyResponse>
    $blockKey             — 'blok3a' | 'blok3b_industri' | 'blok3b_nonindustri'
--}}
@php
    use App\Models\SurveyResponse;

    $histBlockTitles = [
        'blok3a'             => 'Blok IIIA — Kondisi Perekonomian',
        'blok3a2'            => 'Blok IIIC — Bahan Baku & Penolong',
        'blok3b_industri'    => 'Blok IIIB — Industri',
        'blok3b_nonindustri' => 'Blok IIIB — Non-Industri',
    ];
    $histTitle = $histBlockTitles[$blockKey] ?? 'Data Historis';

    // $histMonthKeys / $histMonthLabels are computed PER historical response inside
    // the Blok 3A section below, because the month columns depend on that response's
    // own period (annual → 13 columns; a quarter → its 3 months), and on its year
    // (2025, 2026, …). A single hardcoded list would only ever match one year.
    $histMonthAbbr = ['jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei','jun'=>'Jun','jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt','nov'=>'Nov','des'=>'Des'];
    $histQuarterMonths = [1=>['jan','feb','mar'], 2=>['apr','mei','jun'], 3=>['jul','agu','sep'], 4=>['okt','nov','des']];

    $histGroupColors = [
        'blue'    => ['wrap'=>'border-blue-200 dark:border-blue-800',    'head'=>'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800',    'title'=>'text-blue-700 dark:text-blue-300'],
        'indigo'  => ['wrap'=>'border-indigo-200 dark:border-indigo-800', 'head'=>'bg-indigo-50 dark:bg-indigo-950/30 border-indigo-200 dark:border-indigo-800', 'title'=>'text-indigo-700 dark:text-indigo-300'],
        'violet'  => ['wrap'=>'border-violet-200 dark:border-violet-800', 'head'=>'bg-violet-50 dark:bg-violet-950/30 border-violet-200 dark:border-violet-800', 'title'=>'text-violet-700 dark:text-violet-300'],
        'cyan'    => ['wrap'=>'border-cyan-200 dark:border-cyan-800',     'head'=>'bg-cyan-50 dark:bg-cyan-950/30 border-cyan-200 dark:border-cyan-800',     'title'=>'text-cyan-700 dark:text-cyan-300'],
        'emerald' => ['wrap'=>'border-emerald-200 dark:border-emerald-800','head'=>'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800','title'=>'text-emerald-700 dark:text-emerald-300'],
        'amber'   => ['wrap'=>'border-amber-200 dark:border-amber-800',   'head'=>'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800',   'title'=>'text-amber-700 dark:text-amber-300'],
    ];

    $hist3bIGroups = [
        ['title'=>'Pendapatan Perusahaan', 'color'=>'blue', 'rows'=>[
            ['label'=>'304. Royalti, bunga, dividen, dll', 'key'=>'q304', 'type'=>'currency'],
        ]],
        ['title'=>'Persediaan & Barang Modal', 'color'=>'indigo', 'rows'=>[
            ['label'=>'307.a Stok bahan baku/penolong/bakar — 1 Jan 2025',   'key'=>'q306_year_awal',   'type'=>'currency'],
            ['label'=>'307.b Stok bahan baku/penolong/bakar — 31 Des 2025',  'key'=>'q306_year_akhir',  'type'=>'currency'],
            ['label'=>'308.a Stok barang setengah jadi — 1 Jan 2025',        'key'=>'q307_year_awal',   'type'=>'currency'],
            ['label'=>'308.b Stok barang setengah jadi — 31 Des 2025',       'key'=>'q307_year_akhir',  'type'=>'currency'],
            ['label'=>'309.a Stok barang jadi — 1 Jan 2025',                 'key'=>'q308_year_awal',   'type'=>'currency'],
            ['label'=>'309.b Stok barang jadi — 31 Des 2025',                'key'=>'q308_year_akhir',  'type'=>'currency'],
            ['label'=>'310. Pembelian/penambahan barang modal tetap (2025)', 'key'=>'q310_beli_modal',  'type'=>'currency'],
            ['label'=>'311. Penjualan/pengurangan barang modal tetap (2025)','key'=>'q311_jual_modal',  'type'=>'currency'],
            ['label'=>'312. Taksiran barang modal tetap per 31 Des 2025',    'key'=>'q312_taksir_modal','type'=>'currency'],
        ]],
        ['title'=>'Pengeluaran Pekerja', 'color'=>'cyan', 'rows'=>[
            ['label'=>'313.a1 Upah/gaji/tunjangan pekerja produksi',    'key'=>'q313_a1', 'type'=>'currency'],
            ['label'=>'313.a2 Pengeluaran lain pekerja produksi',       'key'=>'q313_a2', 'type'=>'currency'],
            ['label'=>'313.b1 Upah/gaji/tunjangan pekerja lainnya',     'key'=>'q313_b1', 'type'=>'currency'],
            ['label'=>'313.b2 Pengeluaran lain pekerja lainnya',        'key'=>'q313_b2', 'type'=>'currency'],
            ['label'=>'313.c Total pengeluaran pekerja',                'key'=>'q313_c',  'type'=>'currency'],
            ['label'=>'314.a1 Outsourcing — upah pekerja produksi',     'key'=>'q314_a1', 'type'=>'currency'],
            ['label'=>'314.a2 Outsourcing — pengeluaran lain produksi', 'key'=>'q314_a2', 'type'=>'currency'],
            ['label'=>'314.b1 Outsourcing — upah pekerja lainnya',      'key'=>'q314_b1', 'type'=>'currency'],
            ['label'=>'314.b2 Outsourcing — pengeluaran lain lainnya',  'key'=>'q314_b2', 'type'=>'currency'],
            ['label'=>'314.c Total pengeluaran outsourcing',            'key'=>'q314_c',  'type'=>'currency'],
        ]],
        ['title'=>'Listrik', 'color'=>'amber', 'rows'=>[
            ['label'=>'315.a Daya tersambung PLN (VA)',         'key'=>'q315_a', 'type'=>'raw'],
            ['label'=>'315.b Daya tersambung Non-PLN (VA)',     'key'=>'q315_b', 'type'=>'raw'],
            ['label'=>'315.c Penggunaan listrik PLN (kWh)',     'key'=>'q315_c', 'type'=>'raw'],
            ['label'=>'315.d Penggunaan listrik Non-PLN (kWh)', 'key'=>'q315_d', 'type'=>'raw'],
            ['label'=>'315.e Pengeluaran listrik (Rp)',         'key'=>'q315_e', 'type'=>'currency'],
        ]],
        ['title'=>'Pengeluaran Perusahaan (2025)', 'color'=>'violet', 'rows'=>[
            ['label'=>'317.a1 Sewa/kontrak gedung, mesin, alat',  'key'=>'q317_c1', 'type'=>'currency'],
            ['label'=>'317.a2 Sewa/kontrak tanah',                'key'=>'q317_c2', 'type'=>'currency'],
            ['label'=>'317.b Pajak',                              'key'=>'q317_d',  'type'=>'currency'],
            ['label'=>'317.c Bunga atas pinjaman',                'key'=>'q317_e',  'type'=>'currency'],
            ['label'=>'317.d Hadiah, sumbangan, derma',           'key'=>'q317_f',  'type'=>'currency'],
            ['label'=>'317.e Dividen/laba yang dibagikan',        'key'=>'q317_g',  'type'=>'currency'],
            ['label'=>'317.f Premi asuransi kerugian',            'key'=>'q317_h',  'type'=>'currency'],
            ['label'=>'317.g Jasa industri (maklun) dibayarkan',  'key'=>'q317_i',  'type'=>'currency'],
            ['label'=>'317.h Air (selain bahan baku & penolong)', 'key'=>'q317_j',  'type'=>'currency'],
            ['label'=>'317.i Pengeluaran lainnya',                'key'=>'q317_k',  'type'=>'currency'],
        ]],
        ['title'=>'Moda Transportasi Barang (2025)', 'color'=>'emerald', 'rows'=>[
            ['label'=>'318.a Angkutan jalan — frekuensi (kali)',  'key'=>'q318a_freq',  'type'=>'raw'],
            ['label'=>'318.a Angkutan jalan — biaya (Rp)',        'key'=>'q318a_biaya', 'type'=>'currency'],
            ['label'=>'318.b Kereta api — frekuensi (kali)',      'key'=>'q318b_freq',  'type'=>'raw'],
            ['label'=>'318.b Kereta api — biaya (Rp)',            'key'=>'q318b_biaya', 'type'=>'currency'],
            ['label'=>'318.c Air sungai/danau — frekuensi (kali)','key'=>'q318c_freq',  'type'=>'raw'],
            ['label'=>'318.c Air sungai/danau — biaya (Rp)',      'key'=>'q318c_biaya', 'type'=>'currency'],
            ['label'=>'318.d Air laut — frekuensi (kali)',        'key'=>'q318d_freq',  'type'=>'raw'],
            ['label'=>'318.d Air laut — biaya (Rp)',              'key'=>'q318d_biaya', 'type'=>'currency'],
            ['label'=>'318.e Udara — frekuensi (kali)',           'key'=>'q318e_freq',  'type'=>'raw'],
            ['label'=>'318.e Udara — biaya (Rp)',                 'key'=>'q318e_biaya', 'type'=>'currency'],
            ['label'=>'319. % moda menggunakan jasa pihak ketiga','key'=>'q319_persen_pihak_ketiga', 'type'=>'percent'],
        ]],
    ];

    $hist3bNGroups = [
        ['title'=>'Pendapatan Perusahaan', 'color'=>'blue', 'rows'=>[
            ['label'=>'303.a Penjualan barang & jasa — triwulan lalu',     'key'=>'q303',        'type'=>'currency'],
            ['label'=>'303.b Penjualan barang & jasa — tahun 2025',        'key'=>'q303_year',   'type'=>'currency'],
            ['label'=>'304.a Royalti, bunga, dividen, dll — triwulan lalu','key'=>'q304',        'type'=>'currency'],
            ['label'=>'304.b Royalti, bunga, dividen, dll — tahun 2025',   'key'=>'q304_year',   'type'=>'currency'],
            ['label'=>'305.a Total pendapatan — triwulan lalu',           'key'=>'q305',        'type'=>'currency'],
            ['label'=>'305.b Total pendapatan — tahun 2025',              'key'=>'q305_year',   'type'=>'currency'],
            ['label'=>'306. % pendapatan dari usaha online',              'key'=>'q306_online', 'type'=>'percent'],
        ]],
        ['title'=>'Persediaan & Barang Modal', 'color'=>'indigo', 'rows'=>[
            ['label'=>'307.a Stok bahan baku/penolong/bakar — 1 Jan 2025',   'key'=>'q306_year_awal',   'type'=>'currency'],
            ['label'=>'307.b Stok bahan baku/penolong/bakar — 31 Des 2025',  'key'=>'q306_year_akhir',  'type'=>'currency'],
            ['label'=>'308.a Stok barang setengah jadi — 1 Jan 2025',        'key'=>'q307_year_awal',   'type'=>'currency'],
            ['label'=>'308.b Stok barang setengah jadi — 31 Des 2025',       'key'=>'q307_year_akhir',  'type'=>'currency'],
            ['label'=>'309.a Stok barang jadi — 1 Jan 2025',                 'key'=>'q308_year_awal',   'type'=>'currency'],
            ['label'=>'309.b Stok barang jadi — 31 Des 2025',                'key'=>'q308_year_akhir',  'type'=>'currency'],
            ['label'=>'310. Pembelian/penambahan barang modal tetap (2025)', 'key'=>'q310_beli_modal',  'type'=>'currency'],
            ['label'=>'311. Penjualan/pengurangan barang modal tetap (2025)','key'=>'q311_jual_modal',  'type'=>'currency'],
            ['label'=>'312. Taksiran barang modal tetap per 31 Des 2025',    'key'=>'q312_taksir_modal','type'=>'currency'],
        ]],
        ['title'=>'Pengeluaran Pekerja', 'color'=>'cyan', 'rows'=>[
            ['label'=>'313.a1 Upah/gaji/tunjangan pekerja produksi',    'key'=>'q313_a1', 'type'=>'currency'],
            ['label'=>'313.a2 Pengeluaran lain pekerja produksi',       'key'=>'q313_a2', 'type'=>'currency'],
            ['label'=>'313.b1 Upah/gaji/tunjangan pekerja lainnya',     'key'=>'q313_b1', 'type'=>'currency'],
            ['label'=>'313.b2 Pengeluaran lain pekerja lainnya',        'key'=>'q313_b2', 'type'=>'currency'],
            ['label'=>'313.c Total pengeluaran pekerja',                'key'=>'q313_c',  'type'=>'currency'],
            ['label'=>'314.a1 Outsourcing — upah pekerja produksi',     'key'=>'q314_a1', 'type'=>'currency'],
            ['label'=>'314.a2 Outsourcing — pengeluaran lain produksi', 'key'=>'q314_a2', 'type'=>'currency'],
            ['label'=>'314.b1 Outsourcing — upah pekerja lainnya',      'key'=>'q314_b1', 'type'=>'currency'],
            ['label'=>'314.b2 Outsourcing — pengeluaran lain lainnya',  'key'=>'q314_b2', 'type'=>'currency'],
            ['label'=>'314.c Total pengeluaran outsourcing',            'key'=>'q314_c',  'type'=>'currency'],
        ]],
        ['title'=>'Listrik & Biaya Produksi', 'color'=>'amber', 'rows'=>[
            ['label'=>'315.a Daya tersambung PLN (VA)',         'key'=>'q315_a', 'type'=>'raw'],
            ['label'=>'315.b Daya tersambung Non-PLN (VA)',     'key'=>'q315_b', 'type'=>'raw'],
            ['label'=>'315.c Penggunaan listrik PLN (kWh)',     'key'=>'q315_c', 'type'=>'raw'],
            ['label'=>'315.d Penggunaan listrik Non-PLN (kWh)', 'key'=>'q315_d', 'type'=>'raw'],
            ['label'=>'315.e Pengeluaran listrik (Rp)',         'key'=>'q315_e', 'type'=>'currency'],
            ['label'=>'316.a Biaya produksi (bahan baku & penolong) — triwulan lalu', 'key'=>'q312',      'type'=>'currency'],
            ['label'=>'316.b Biaya produksi (bahan baku & penolong) — tahun 2025',    'key'=>'q312_year', 'type'=>'currency'],
        ]],
        ['title'=>'Pengeluaran Perusahaan (2025)', 'color'=>'violet', 'rows'=>[
            ['label'=>'317.a Biaya operasional (air, listrik, gas, dll)', 'key'=>'q317_a',  'type'=>'currency'],
            ['label'=>'317.b Biaya non-operasional',                     'key'=>'q317_b',  'type'=>'currency'],
            ['label'=>'317.c1 Sewa/kontrak gedung, mesin, alat',         'key'=>'q317_c1', 'type'=>'currency'],
            ['label'=>'317.c2 Sewa/kontrak tanah',                       'key'=>'q317_c2', 'type'=>'currency'],
            ['label'=>'317.d Pajak',                                     'key'=>'q317_d',  'type'=>'currency'],
            ['label'=>'317.e Bunga atas pinjaman',                       'key'=>'q317_e',  'type'=>'currency'],
            ['label'=>'317.f Hadiah, sumbangan, derma',                  'key'=>'q317_f',  'type'=>'currency'],
            ['label'=>'317.g Dividen/laba yang dibagikan',               'key'=>'q317_g',  'type'=>'currency'],
            ['label'=>'317.h Premi asuransi kerugian',                   'key'=>'q317_h',  'type'=>'currency'],
            ['label'=>'317.i Jasa industri (maklun) dibayarkan',         'key'=>'q317_i',  'type'=>'currency'],
            ['label'=>'317.j Air (selain bahan baku & penolong)',        'key'=>'q317_j',  'type'=>'currency'],
            ['label'=>'317.k Pengeluaran lainnya',                       'key'=>'q317_k',  'type'=>'currency'],
        ]],
        ['title'=>'Ekspor & Impor Luar Negeri', 'color'=>'emerald', 'rows'=>[
            ['label'=>'319. % produksi dijual sebagai ekspor',   'key'=>'q314', 'type'=>'percent'],
            ['label'=>'320. % bahan baku & penolong dari impor', 'key'=>'q315', 'type'=>'percent'],
        ]],
        ['title'=>'Nilai Aset (31 Des 2025)', 'color'=>'blue', 'rows'=>[
            ['label'=>'321.a Tanah dan bangunan',          'key'=>'q318a',       'type'=>'currency'],
            ['label'=>'321.b Selain tanah dan bangunan',   'key'=>'q318b',       'type'=>'currency'],
            ['label'=>'321.c Total nilai aset',            'key'=>'q318c',       'type'=>'currency'],
            ['label'=>'321.c1 Rentang nilai aset (1–5)',   'key'=>'q318c_range', 'type'=>'raw'],
            ['label'=>'321.d Luas tanah untuk usaha (m²)', 'key'=>'q318d_area',  'type'=>'raw'],
        ]],
        ['title'=>'Kepemilikan Modal (31 Des 2025)', 'color'=>'indigo', 'rows'=>[
            ['label'=>'322.a Pribadi/Perorangan',               'key'=>'q319a', 'type'=>'percent'],
            ['label'=>'322.b Lembaga Nonprofit (Rumah Tangga)', 'key'=>'q319b', 'type'=>'percent'],
            ['label'=>'322.c Korporasi Publik',                 'key'=>'q319c', 'type'=>'percent'],
            ['label'=>'322.d Korporasi Non Publik',             'key'=>'q319d', 'type'=>'percent'],
            ['label'=>'322.e Pemerintah Pusat',                 'key'=>'q319e', 'type'=>'percent'],
            ['label'=>'322.f Pemerintah Daerah',                'key'=>'q319f', 'type'=>'percent'],
            ['label'=>'322.g Perusahaan Swasta Nasional',       'key'=>'q319g', 'type'=>'percent'],
            ['label'=>'322.h Asing',                            'key'=>'q319h', 'type'=>'percent'],
            ['label'=>'322.i Total kepemilikan',                'key'=>'q319i', 'type'=>'percent'],
        ]],
    ];

    // ── Triwulanan field sets ──────────────────────────────────────────────────
    // The quarterly Blok 3B forms use a different (smaller) set of keys than the
    // annual forms above, so quarter-to-quarter comparison needs its own mapping.
    // Selected per response below based on $hResp->triwulan.
    $hist3bIGroupsTw = [
        ['title'=>'Pendapatan Perusahaan', 'color'=>'blue', 'rows'=>[
            ['label'=>'304. Pendapatan royalti, bunga, dividen, dll', 'key'=>'q304', 'type'=>'currency'],
        ]],
        ['title'=>'Persediaan (Inventori)', 'color'=>'indigo', 'rows'=>[
            ['label'=>'306. Bahan baku & bahan bakar — Awal',  'key'=>'q306_awal',  'type'=>'currency'],
            ['label'=>'306. Bahan baku & bahan bakar — Akhir', 'key'=>'q306_akhir', 'type'=>'currency'],
            ['label'=>'307. Barang dalam proses — Awal',       'key'=>'q307_awal',  'type'=>'currency'],
            ['label'=>'307. Barang dalam proses — Akhir',      'key'=>'q307_akhir', 'type'=>'currency'],
            ['label'=>'308. Barang jadi — Awal',               'key'=>'q308_awal',  'type'=>'currency'],
            ['label'=>'308. Barang jadi — Akhir',              'key'=>'q308_akhir', 'type'=>'currency'],
            ['label'=>'309. Total persediaan — Awal',          'key'=>'q309_awal',  'type'=>'currency'],
            ['label'=>'309. Total persediaan — Akhir',         'key'=>'q309_akhir', 'type'=>'currency'],
        ]],
        ['title'=>'Item Pengeluaran Perusahaan', 'color'=>'cyan', 'rows'=>[
            ['label'=>'310. Upah, gaji & jaminan sosial pegawai',    'key'=>'q310',     'type'=>'currency'],
            ['label'=>'311. Penambahan aset tetap (kecuali tanah)',  'key'=>'q311',     'type'=>'currency'],
            ['label'=>'312. Biaya produksi (bahan baku & penolong)', 'key'=>'q312_tw',  'type'=>'currency'],
            ['label'=>'313. Biaya operasional (air, listrik, dll)',  'key'=>'q313_tw',  'type'=>'currency'],
        ]],
        ['title'=>'Ekspor & Impor Luar Negeri', 'color'=>'emerald', 'rows'=>[
            ['label'=>'314. % produksi dijual sebagai ekspor',      'key'=>'q314_tw', 'type'=>'percent'],
            ['label'=>'315. % bahan baku & penolong dari impor',    'key'=>'q315_tw', 'type'=>'percent'],
        ]],
    ];

    $hist3bNGroupsTw = [
        ['title'=>'Pendapatan Perusahaan', 'color'=>'blue', 'rows'=>[
            ['label'=>'303. Pendapatan penjualan barang & jasa',     'key'=>'q303', 'type'=>'currency'],
            ['label'=>'304. Pendapatan royalti, bunga, dividen, dll','key'=>'q304', 'type'=>'currency'],
            ['label'=>'305. Total pendapatan (303 + 304)',           'key'=>'q305', 'type'=>'currency'],
        ]],
        ['title'=>'Persediaan (Inventori)', 'color'=>'indigo', 'rows'=>[
            ['label'=>'306. Bahan baku & bahan bakar — Awal',  'key'=>'q306_awal',  'type'=>'currency'],
            ['label'=>'306. Bahan baku & bahan bakar — Akhir', 'key'=>'q306_akhir', 'type'=>'currency'],
            ['label'=>'307. Barang dalam proses — Awal',       'key'=>'q307_awal',  'type'=>'currency'],
            ['label'=>'307. Barang dalam proses — Akhir',      'key'=>'q307_akhir', 'type'=>'currency'],
            ['label'=>'308. Barang jadi — Awal',               'key'=>'q308_awal',  'type'=>'currency'],
            ['label'=>'308. Barang jadi — Akhir',              'key'=>'q308_akhir', 'type'=>'currency'],
            ['label'=>'309. Total persediaan — Awal',          'key'=>'q309_awal',  'type'=>'currency'],
            ['label'=>'309. Total persediaan — Akhir',         'key'=>'q309_akhir', 'type'=>'currency'],
        ]],
        ['title'=>'Item Pengeluaran Perusahaan', 'color'=>'cyan', 'rows'=>[
            ['label'=>'310. Upah, gaji & jaminan sosial pegawai',    'key'=>'q310_tw',  'type'=>'currency'],
            ['label'=>'311. Penambahan aset tetap (kecuali tanah)',  'key'=>'q311_tw',  'type'=>'currency'],
            ['label'=>'312. Biaya produksi (bahan baku & penolong)', 'key'=>'q312_tw',  'type'=>'currency'],
            ['label'=>'313. Biaya operasional (air, listrik, dll)',  'key'=>'q313_tw',  'type'=>'currency'],
        ]],
        ['title'=>'Ekspor & Impor Luar Negeri', 'color'=>'emerald', 'rows'=>[
            ['label'=>'314. % produksi dijual sebagai ekspor',   'key'=>'q314_tw', 'type'=>'percent'],
            ['label'=>'315. % bahan baku & penolong dari impor', 'key'=>'q315_tw', 'type'=>'percent'],
        ]],
    ];
@endphp

{{-- ──────────────────────────────────────────────────────── --}}
{{-- FLOATING TRIGGER BUTTON (always rendered, shown when    --}}
{{-- historical data exists)                                 --}}
{{-- ──────────────────────────────────────────────────────── --}}
@if($historicalResponses->isNotEmpty())
<div class="hist-trigger-wrap" style="position:fixed;bottom:1.75rem;right:1.75rem;z-index:39;">
    <button type="button"
            id="hist-open-btn"
            onclick="openHistDrawer()"
            title="Lihat data historis periode sebelumnya"
            aria-label="Buka panel data historis"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.1rem;border-radius:9999px;
                   background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;
                   font-size:0.8125rem;font-weight:600;letter-spacing:0.01em;
                   box-shadow:0 4px 14px rgba(37,99,235,0.45);border:none;cursor:pointer;
                   transition:box-shadow 0.2s,transform 0.2s;">
        <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Lihat Data Historis</span>
        <span style="display:inline-flex;align-items:center;justify-content:center;width:1.2rem;height:1.2rem;border-radius:9999px;background:rgba(255,255,255,0.25);font-size:0.7rem;font-weight:700;">
            {{ $historicalResponses->count() }}
        </span>
    </button>
</div>
@endif

{{-- ──────────────────────────────────────────────────────── --}}
{{-- BACKDROP                                                --}}
{{-- ──────────────────────────────────────────────────────── --}}
<div id="hist-backdrop"
     onclick="closeHistDrawer()"
     style="display:none;position:fixed;inset:0;top:4rem;background:rgba(0,0,0,0.45);backdrop-filter:blur(2px);z-index:48;
            opacity:0;transition:opacity 0.3s ease;"
     aria-hidden="true"></div>

{{-- ──────────────────────────────────────────────────────── --}}
{{-- DRAWER                                                  --}}
{{-- ──────────────────────────────────────────────────────── --}}
<aside id="hist-drawer"
       role="complementary"
       aria-label="Panel data historis untuk referensi"
       style="position:fixed;top:4rem;bottom:0;right:0;z-index:49;
              width:100%;max-width:36rem;display:flex;flex-direction:column;
              background:#fff;box-shadow:-8px 0 32px rgba(0,0,0,0.18);
              transform:translateX(100%);transition:transform 0.32s cubic-bezier(0.4,0,0.2,1);">

    {{-- ── Header ── --}}
    <div style="flex-shrink:0;display:flex;align-items:center;justify-content:space-between;
                padding:1rem 1.25rem;
                background:linear-gradient(135deg,#1e40af,#2563eb);
                border-bottom:1px solid rgba(255,255,255,0.1);">
        <div style="display:flex;align-items:center;gap:0.75rem;min-width:0;">
            <div style="flex-shrink:0;display:flex;align-items:center;justify-content:center;
                        width:2.25rem;height:2.25rem;border-radius:0.625rem;
                        background:rgba(255,255,255,0.18);">
                <svg style="width:1.125rem;height:1.125rem;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div style="min-width:0;">
                <p style="margin:0;font-size:0.9375rem;font-weight:700;color:#fff;line-height:1.2;">Data Historis</p>
                <p style="margin:0.15rem 0 0;font-size:0.7rem;color:#bfdbfe;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $histTitle }} — Hanya untuk referensi
                </p>
            </div>
        </div>
        <button type="button"
                onclick="closeHistDrawer()"
                aria-label="Tutup panel data historis"
                style="flex-shrink:0;margin-left:0.75rem;padding:0.4rem;border-radius:0.5rem;
                       background:rgba(255,255,255,0.15);border:none;cursor:pointer;color:#e0f2fe;
                       transition:background 0.15s,color 0.15s;">
            <svg style="width:1.125rem;height:1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Period Tabs ── --}}
    @if($historicalResponses->count() > 1)
    <div style="flex-shrink:0;display:flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;
                border-bottom:1px solid #e5e7eb;background:#f9fafb;overflow-x:auto;">
        <span style="flex-shrink:0;font-size:0.7rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-right:0.25rem;">
            Periode:
        </span>
        @foreach($historicalResponses as $hIdx => $hResp)
        @php
            $hShortLabel = $hResp->triwulan == 0
                ? (string) $hResp->tahun
                : 'TW' . $hResp->triwulan . ' ' . $hResp->tahun;
            $hFullLabel = $hResp->triwulan == 0
                ? 'Tahunan ' . $hResp->tahun
                : SurveyResponse::triwulanLabel($hResp->triwulan) . ' ' . $hResp->tahun;
        @endphp
        <button type="button"
                class="hist-period-tab"
                data-period-idx="{{ $hIdx }}"
                onclick="switchHistPeriod({{ $hIdx }})"
                title="{{ $hFullLabel }}"
                style="flex-shrink:0;padding:0.3rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-weight:600;
                       cursor:pointer;transition:all 0.15s;white-space:nowrap;
                       {{ $hIdx === 0 ? 'background:#2563eb;color:#fff;border:1px solid #2563eb;box-shadow:0 1px 4px rgba(37,99,235,0.35);' : 'background:#fff;color:#374151;border:1px solid #d1d5db;' }}">
            {{ $hShortLabel }}
        </button>
        @endforeach
    </div>
    @else
    <div style="flex-shrink:0;padding:0.625rem 1.25rem;border-bottom:1px solid #e5e7eb;background:#f9fafb;display:flex;align-items:center;gap:0.5rem;">
        @php
            $hOnly = $historicalResponses->first();
            if ($hOnly) {
                $hOnlyLabel = $hOnly->triwulan == 0
                    ? 'Tahunan ' . $hOnly->tahun
                    : SurveyResponse::triwulanLabel($hOnly->triwulan) . ' ' . $hOnly->tahun;
            } else {
                $hOnlyLabel = 'Tidak ada data historis';
            }
        @endphp
        <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.3rem 0.75rem;border-radius:9999px;
                     background:#dbeafe;color:#1d4ed8;font-size:0.75rem;font-weight:700;">
            <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ $hOnlyLabel }}
        </span>
        <span style="font-size:0.7rem;color:#9ca3af;">@if($hOnly) Periode historis tersedia @else Tidak ada data historis @endif</span>
    </div>
    @endif

    {{-- ── Scrollable Content ── --}}
    <div style="flex:1;overflow-y:auto;-webkit-overflow-scrolling:touch;">

        @forelse($historicalResponses as $hIdx => $hResp)

        @php
            $hPeriodLabel = $hResp->triwulan == 0
                ? 'Tahunan ' . $hResp->tahun
                : SurveyResponse::triwulanLabel($hResp->triwulan) . ' ' . $hResp->tahun;
            $hIsComplete = (bool) $hResp->is_completed;
        @endphp

        <div class="hist-period-panel"
             data-period-idx="{{ $hIdx }}"
             style="{{ $hIdx > 0 ? 'display:none;' : '' }}padding:1.25rem;display:{{ $hIdx > 0 ? 'none' : 'block' }};">

            {{-- Completion badge --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <span style="font-size:0.8rem;font-weight:600;color:#374151;">{{ $hPeriodLabel }}</span>
                @if($hIsComplete)
                <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.2rem 0.6rem;border-radius:9999px;
                             background:#d1fae5;color:#065f46;font-size:0.7rem;font-weight:700;">
                    <svg style="width:0.7rem;height:0.7rem;" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Selesai
                </span>
                @else
                <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.2rem 0.6rem;border-radius:9999px;
                             background:#fef9c3;color:#713f12;font-size:0.7rem;font-weight:700;">
                    Draft
                </span>
                @endif
            </div>

            {{-- ─── BLOK 3A ─── --}}
            @if($blockKey === 'blok3a')
            @php
                $hProducts = $hResp->blok3a_products ?? [];
                $hLainnya  = $hResp->blok3a_lainnya ?? [];
                $hTotals   = $hResp->blok3a_totals ?? [];

                // Month columns for THIS response's period (mirrors how Blok 3A stores
                // them: a leading "Des {prevYear}" carry-over column, then either all
                // 12 months of the year (annual) or the quarter's 3 months.
                $hPrevYear       = (int) $hResp->tahun - 1;
                $histMonthKeys   = ["{$hPrevYear}_des"];
                $histMonthLabels = ["Des {$hPrevYear}"];
                $hMonths = ((int) $hResp->triwulan === 0)
                    ? array_keys($histMonthAbbr)
                    : ($histQuarterMonths[(int) $hResp->triwulan] ?? []);
                foreach ($hMonths as $hAbbr) {
                    $histMonthKeys[]   = "{$hResp->tahun}_{$hAbbr}";
                    $histMonthLabels[] = $histMonthAbbr[$hAbbr];
                }
            @endphp

            @if(empty($hProducts) && empty($hLainnya) && empty($hTotals))
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p style="font-size:0.85rem;margin:0;">Tidak ada data Blok IIIA untuk periode ini.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:1rem;">

                {{-- Products --}}
                @foreach($hProducts as $hPIdx => $hProduct)
                @php $hPName = $hProduct['jenis_barang'] ?? ($hProduct['name'] ?? ('Produk ' . ($hPIdx + 1))); @endphp
                <div style="border:1px solid #e5e7eb;border-radius:0.625rem;overflow:hidden;">
                    <div style="padding:0.5rem 0.875rem;background:#eff6ff;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:0.7rem;font-weight:700;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.04em;">
                            301.{{ $hPIdx + 1 }} — {{ $hPName }}
                        </span>
                        @if(!empty($hProduct['satuan']))
                        <span style="font-size:0.7rem;color:#6b7280;">Satuan: {{ $hProduct['satuan'] }}</span>
                        @endif
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.75rem;min-width:480px;">
                            <thead>
                                <tr style="background:#f9fafb;">
                                    <th style="text-align:left;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;width:5.5rem;">Bulan</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Banyaknya</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Nilai (Rp)</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Harga/Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($histMonthKeys as $mIdx => $mKey)
                                @php
                                    $hQty   = $hProduct['banyaknya'][$mKey] ?? null;
                                    $hNilai = $hProduct['nilai'][$mKey] ?? null;
                                    $hHarga = ($hQty && (float)$hQty > 0 && $hNilai !== null)
                                                ? ((float)$hNilai / (float)$hQty)
                                                : ($hProduct['harga_satuan'][$mKey] ?? null);
                                    $hRowBg = $mIdx % 2 === 0 ? '#ffffff' : '#f9fafb';
                                @endphp
                                <tr style="background:{{ $hRowBg }};">
                                    <td style="padding:0.35rem 0.75rem;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6;">{{ $histMonthLabels[$mIdx] }}</td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#374151;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ $hQty !== null ? number_format((float)$hQty, 0, ',', '.') : '—' }}
                                    </td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#374151;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ $hNilai !== null ? number_format((float)$hNilai, 0, ',', '.') : '—' }}
                                    </td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#374151;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ $hHarga !== null ? number_format((float)$hHarga, 2, ',', '.') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach

                {{-- Lainnya --}}
                @if(!empty($hLainnya))
                <div style="border:1px solid #fde68a;border-radius:0.625rem;overflow:hidden;">
                    <div style="padding:0.5rem 0.875rem;background:#fef9c3;border-bottom:1px solid #fde68a;">
                        <span style="font-size:0.7rem;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.04em;">302 — Lainnya</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.75rem;min-width:300px;">
                            <thead>
                                <tr style="background:#f9fafb;">
                                    <th style="text-align:left;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Bulan</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Nilai (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($histMonthKeys as $mIdx => $mKey)
                                <tr style="background:{{ $mIdx % 2 === 0 ? '#ffffff' : '#f9fafb' }};">
                                    <td style="padding:0.35rem 0.75rem;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6;">{{ $histMonthLabels[$mIdx] }}</td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#374151;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ isset($hLainnya['nilai'][$mKey]) && $hLainnya['nilai'][$mKey] !== null ? number_format((float)$hLainnya['nilai'][$mKey], 0, ',', '.') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Totals --}}
                @if(!empty($hTotals))
                <div style="border:1px solid #bbf7d0;border-radius:0.625rem;overflow:hidden;">
                    <div style="padding:0.5rem 0.875rem;background:#f0fdf4;border-bottom:1px solid #bbf7d0;">
                        <span style="font-size:0.7rem;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:0.04em;">303 — Total Pendapatan</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.75rem;min-width:300px;">
                            <thead>
                                <tr style="background:#f9fafb;">
                                    <th style="text-align:left;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Bulan</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Total Nilai (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($histMonthKeys as $mIdx => $mKey)
                                <tr style="background:{{ $mIdx % 2 === 0 ? '#ffffff' : '#f0fdf4' }};font-weight:500;">
                                    <td style="padding:0.35rem 0.75rem;color:#374151;border-bottom:1px solid #f3f4f6;">{{ $histMonthLabels[$mIdx] }}</td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#065f46;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ isset($hTotals[$mKey]) && $hTotals[$mKey] !== null ? number_format((float)$hTotals[$mKey], 0, ',', '.') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                </div>{{-- end flex column --}}
            @endif

            {{-- ─── BLOK 3B INDUSTRI ─── --}}
            @endif
            @if($blockKey === 'blok3b_industri')
            @php $hData = $hResp->blok3b_industri_data ?? []; @endphp

            @if(empty($hData))
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p style="font-size:0.85rem;margin:0;">Tidak ada data Blok IIIB Industri untuk periode ini.</p>
                </div>
            @else
                @php $hGroups3bI = ((int) $hResp->triwulan > 0) ? $hist3bIGroupsTw : $hist3bIGroups; @endphp
                <div style="display:flex;flex-direction:column;gap:1rem;">
                @foreach($hGroups3bI as $hGroup)
                @php
                    $hC = $histGroupColors[$hGroup['color']] ?? $histGroupColors['blue'];
                    $hHasVal = false;
                    foreach ($hGroup['rows'] as $hRow) {
                        $v = $hData[$hRow['key']] ?? null;
                        if ($v !== null && $v !== '') { $hHasVal = true; break; }
                    }
                @endphp
                @if($hHasVal)
                <div class="{{ $hC['wrap'] }}" style="border:1px solid;border-radius:0.625rem;overflow:hidden;">
                    <div class="{{ $hC['head'] }}" style="padding:0.5rem 0.875rem;border-bottom:1px solid;border-color:inherit;">
                        <span class="{{ $hC['title'] }}" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">{{ $hGroup['title'] }}</span>
                    </div>
                    <div>
                        @foreach($hGroup['rows'] as $hRow)
                        @php
                            $hVal = $hData[$hRow['key']] ?? null;
                            if ($hRow['type'] === 'currency') {
                                $hDisp = ($hVal !== null && $hVal !== '') ? 'Rp ' . number_format((float)$hVal, 0, ',', '.') : null;
                            } elseif ($hRow['type'] === 'percent') {
                                $hDisp = ($hVal !== null && $hVal !== '') ? number_format((float)$hVal, 2, ',', '.') . '%' : null;
                            } else {
                                $hDisp = ($hVal !== null && $hVal !== '') ? $hVal : null;
                            }
                        @endphp
                        @if($hDisp !== null)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;
                                    padding:0.55rem 0.875rem;border-bottom:1px solid #f3f4f6;">
                            <span style="font-size:0.75rem;color:#4b5563;flex:1;min-width:0;">{{ $hRow['label'] }}</span>
                            <span style="font-size:0.775rem;font-weight:700;color:#111827;flex-shrink:0;font-variant-numeric:tabular-nums;">{{ $hDisp }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
                </div>
            @endif

            {{-- ─── BLOK 3A-2 ─── --}}
            @endif
            @if($blockKey === 'blok3a2')
            @php $hMats = $hResp->blok3a2_materials ?? []; @endphp
            @if(empty($hMats))
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p style="font-size:0.85rem;margin:0;">Tidak ada data Blok IIIA-2 untuk periode ini.</p>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.75rem;min-width:600px;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:center;color:#6b7280;">No.</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:left;color:#6b7280;">Nama Bahan</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:center;color:#6b7280;">Satuan</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:right;background:#fef9c3;color:#92400e;">DN Banyaknya</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:right;background:#fef9c3;color:#92400e;">DN Nilai (Rp)</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:right;background:#dbeafe;color:#1e40af;">LN Banyaknya</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:right;background:#dbeafe;color:#1e40af;">LN Nilai (Rp)</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:center;color:#6b7280;">Negara Asal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hMats as $hMi => $hMat)
                            <tr style="{{ $hMi % 2 === 1 ? 'background:#fafafa;' : '' }}">
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:center;">{{ $hMi + 1 }}</td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;"><strong>{{ $hMat['nama_bahan'] ?? '' }}</strong></td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:center;">{{ $hMat['satuan_standar'] ?? '' }}</td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:right;">
                                    {{ isset($hMat['dn_banyaknya']) && $hMat['dn_banyaknya'] !== '' ? number_format((int)$hMat['dn_banyaknya'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:right;">
                                    {{ isset($hMat['dn_nilai']) && $hMat['dn_nilai'] !== '' ? number_format((int)$hMat['dn_nilai'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:right;">
                                    {{ isset($hMat['ln_banyaknya']) && $hMat['ln_banyaknya'] !== '' ? number_format((int)$hMat['ln_banyaknya'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:right;">
                                    {{ isset($hMat['ln_nilai']) && $hMat['ln_nilai'] !== '' ? number_format((int)$hMat['ln_nilai'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:center;">{{ $hMat['negara_asal'] ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ─── BLOK 3B NON-INDUSTRI ─── --}}
            @endif
            @if($blockKey === 'blok3b_nonindustri')
            @php $hData = $hResp->blok3b_nonindustri_data ?? []; @endphp

            @if(empty($hData))
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p style="font-size:0.85rem;margin:0;">Tidak ada data Blok IIIB Non-Industri untuk periode ini.</p>
                </div>
            @else
                @php $hGroups3bN = ((int) $hResp->triwulan > 0) ? $hist3bNGroupsTw : $hist3bNGroups; @endphp
                <div style="display:flex;flex-direction:column;gap:1rem;">
                @foreach($hGroups3bN as $hGroup)
                @php
                    $hC = $histGroupColors[$hGroup['color']] ?? $histGroupColors['blue'];
                    $hHasVal = false;
                    foreach ($hGroup['rows'] as $hRow) {
                        $v = $hData[$hRow['key']] ?? null;
                        if ($v !== null && $v !== '') { $hHasVal = true; break; }
                    }
                @endphp
                @if($hHasVal)
                <div class="{{ $hC['wrap'] }}" style="border:1px solid;border-radius:0.625rem;overflow:hidden;">
                    <div class="{{ $hC['head'] }}" style="padding:0.5rem 0.875rem;border-bottom:1px solid;border-color:inherit;">
                        <span class="{{ $hC['title'] }}" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">{{ $hGroup['title'] }}</span>
                    </div>
                    <div>
                        @foreach($hGroup['rows'] as $hRow)
                        @php
                            $hVal = $hData[$hRow['key']] ?? null;
                            if ($hRow['type'] === 'currency') {
                                $hDisp = ($hVal !== null && $hVal !== '') ? 'Rp ' . number_format((float)$hVal, 0, ',', '.') : null;
                            } elseif ($hRow['type'] === 'percent') {
                                $hDisp = ($hVal !== null && $hVal !== '') ? number_format((float)$hVal, 2, ',', '.') . '%' : null;
                            } else {
                                $hDisp = ($hVal !== null && $hVal !== '') ? $hVal : null;
                            }
                        @endphp
                        @if($hDisp !== null)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;
                                    padding:0.55rem 0.875rem;border-bottom:1px solid #f3f4f6;">
                            <span style="font-size:0.75rem;color:#4b5563;flex:1;min-width:0;">{{ $hRow['label'] }}</span>
                            <span style="font-size:0.775rem;font-weight:700;color:#111827;flex-shrink:0;font-variant-numeric:tabular-nums;">{{ $hDisp }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
                </div>
            @endif
            @endif

        </div>{{-- end .hist-period-panel --}}

        @empty
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4rem 1.5rem;color:#9ca3af;">
            <svg style="width:3.5rem;height:3.5rem;margin-bottom:1rem;opacity:0.3;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p style="font-size:0.9rem;font-weight:600;margin:0;">Tidak ada data historis</p>
            <p style="font-size:0.8rem;margin:0.35rem 0 0;text-align:center;">Belum ada periode sebelumnya yang tersimpan untuk akun ini.</p>
        </div>
        @endforelse

    </div>{{-- end scrollable --}}

    {{-- ── Footer ── --}}
    <div style="flex-shrink:0;padding:0.75rem 1.25rem;border-top:1px solid #e5e7eb;background:#f9fafb;text-align:center;">
        <p style="margin:0;font-size:0.7rem;color:#9ca3af;">
            Data di atas hanya untuk perbandingan referensi dan tidak dapat diedit dari panel ini.
        </p>
    </div>

</aside>

{{-- ──────────────────────────────────────────────────────── --}}
{{-- JAVASCRIPT                                              --}}
{{-- ──────────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    function openHistDrawer() {
        var drawer   = document.getElementById('hist-drawer');
        var backdrop = document.getElementById('hist-backdrop');
        if (!drawer || !backdrop) return;

        backdrop.style.display = 'block';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                backdrop.style.opacity = '1';
                drawer.style.transform = 'translateX(0)';
            });
        });
        document.body.style.overflow = 'hidden';
        drawer.focus && drawer.focus();
    }

    function closeHistDrawer() {
        var drawer   = document.getElementById('hist-drawer');
        var backdrop = document.getElementById('hist-backdrop');
        if (!drawer || !backdrop) return;

        drawer.style.transform = 'translateX(100%)';
        backdrop.style.opacity = '0';
        setTimeout(function () {
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }, 320);

        var btn = document.getElementById('hist-open-btn');
        if (btn) btn.focus();
    }

    function switchHistPeriod(idx) {
        document.querySelectorAll('.hist-period-panel').forEach(function (p) {
            p.style.display = 'none';
        });
        var target = document.querySelector('.hist-period-panel[data-period-idx="' + idx + '"]');
        if (target) target.style.display = 'block';

        document.querySelectorAll('.hist-period-tab').forEach(function (tab) {
            var isActive = parseInt(tab.getAttribute('data-period-idx'), 10) === idx;
            if (isActive) {
                tab.style.background  = '#2563eb';
                tab.style.color       = '#fff';
                tab.style.borderColor = '#2563eb';
                tab.style.boxShadow   = '0 1px 4px rgba(37,99,235,0.35)';
            } else {
                tab.style.background  = '#fff';
                tab.style.color       = '#374151';
                tab.style.borderColor = '#d1d5db';
                tab.style.boxShadow   = 'none';
            }
        });
    }

    /* Keyboard: Escape closes drawer */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var drawer = document.getElementById('hist-drawer');
            if (drawer && drawer.style.transform !== 'translateX(100%)') {
                closeHistDrawer();
            }
        }
    });

    /* Hover effect for open button */
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('hist-open-btn');
        if (btn) {
            btn.addEventListener('mouseenter', function () {
                btn.style.boxShadow = '0 6px 20px rgba(37,99,235,0.55)';
                btn.style.transform = 'translateY(-1px)';
            });
            btn.addEventListener('mouseleave', function () {
                btn.style.boxShadow = '0 4px 14px rgba(37,99,235,0.45)';
                btn.style.transform = 'translateY(0)';
            });
        }
    });

    window.openHistDrawer   = openHistDrawer;
    window.closeHistDrawer  = closeHistDrawer;
    window.switchHistPeriod = switchHistPeriod;
}());
</script>
