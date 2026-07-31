{{--
  Blok IIIB — Pendapatan, Persediaan & Pengeluaran, read-only.

  Four different question sets share this partial, exactly as the survey does:
  industri/non-industri × tahunan/triwulanan. The keys and the question numbers
  below are mirrored from survey/sibstr/blok3b-industri.blade.php and
  blok3b-nonindustri.blade.php — when a question moves there, move it here too.

  Note: for the Industri Tahunan path, Nilai Aset and Kepemilikan Modal are
  reported under Blok IIIC, not here — same as the survey form.

  Expects: $surveyResponse, $showIndustri, $showNonIndustri
--}}
@php
    use App\Support\SibstrFormat as F;

    $isInd    = !empty($showIndustri);
    $tw3b     = (int) ($surveyResponse->triwulan ?? 0);
    $yr3b     = (int) ($surveyResponse->tahun ?? 2025);
    $isTw3b   = $tw3b > 0;
    $d        = $isInd
                    ? ($surveyResponse->blok3b_industri_data ?? [])
                    : ($surveyResponse->blok3b_nonindustri_data ?? []);

    $twWord   = ['satu', 'dua', 'tiga', 'empat'][$tw3b - 1] ?? 'satu';
    $twAwal   = match ($tw3b) { 1 => "1 Januari {$yr3b}", 2 => "1 April {$yr3b}", 3 => "1 Juli {$yr3b}", 4 => "1 Oktober {$yr3b}", default => '' };
    $twAkhir  = match ($tw3b) { 1 => "31 Maret {$yr3b}", 2 => "30 Juni {$yr3b}", 3 => "30 September {$yr3b}", 4 => "31 Desember {$yr3b}", default => '' };
@endphp

@if(empty($d))
    <div class="empty">Belum ada data Blok IIIB untuk ditampilkan.</div>
@elseif($isTw3b)

    {{-- ══ TRIWULANAN ═════════════════════════════════════════════════════ --}}
    <div class="sub">Pendapatan — Triwulan {{ $twWord }} {{ $yr3b }}</div>
    <table class="kv">
        @unless($isInd)
        <tr><td class="k">303. Nilai pendapatan dari penjualan barang dan jasa perusahaan (Rp)</td><td class="v num">{{ F::idr($d['q303'] ?? null) }}</td></tr>
        @endunless
        <tr><td class="k">304. Pendapatan royalti, bunga, deviden dan lainnya (Rp)</td><td class="v num">{{ F::idr($d['q304'] ?? null) }}</td></tr>
        @unless($isInd)
        <tr class="r-total"><td class="k">305. Total pendapatan <span class="hint">(303 + 304)</span></td><td class="v num">{{ F::idr($d['q305'] ?? null) }}</td></tr>
        @endunless
    </table>

    <div class="sub">Persediaan (Inventori) — Awal ({{ $twAwal }}) vs Akhir ({{ $twAkhir }})</div>
    <div class="sx">
        <table class="dt">
            <thead>
                <tr>
                    <th class="ta-l">Jenis Persediaan</th>
                    <th>Awal Triwulan (Rp)</th>
                    <th>Akhir Triwulan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ta-l">306. Bahan baku, bahan bakar, dan sebagainya</td>
                    <td class="num">{{ F::idr($d['q306_awal'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q306_akhir'] ?? null) }}</td>
                </tr>
                <tr>
                    <td class="ta-l">307. Barang dalam proses (BDP)</td>
                    <td class="num">{{ F::idr($d['q307_awal'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q307_akhir'] ?? null) }}</td>
                </tr>
                <tr>
                    <td class="ta-l">308. Barang jadi <span class="hint">(termasuk untuk dijual kembali)</span></td>
                    <td class="num">{{ F::idr($d['q308_awal'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q308_akhir'] ?? null) }}</td>
                </tr>
                <tr class="r-total">
                    <td class="ta-l">309. Total persediaan <span class="hint">(306 s.d. 308)</span></td>
                    <td class="num">{{ F::idr($d['q309_awal'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q309_akhir'] ?? null) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @php
        // The Industri form keeps the quarterly wage/asset figures under q310 and
        // q311; the Non-Industri form suffixes them with _tw.
        $q310Tw = $isInd ? ($d['q310'] ?? null) : ($d['q310_tw'] ?? null);
        $q311Tw = $isInd ? ($d['q311'] ?? null) : ($d['q311_tw'] ?? null);
    @endphp
    <div class="sub">Item Pengeluaran Perusahaan — Triwulan {{ $twWord }} {{ $yr3b }}</div>
    <table class="kv">
        <tr><td class="k">310. Total upah &amp; gaji, serta jaminan sosial pegawai (Rp)</td><td class="v num">{{ F::idr($q310Tw) }}</td></tr>
        <tr><td class="k">311. Penambahan aset tetap <span class="hint">(kecuali pembelian tanah)</span> (Rp)</td><td class="v num">{{ F::idr($q311Tw) }}</td></tr>
        <tr><td class="k">312. Biaya produksi <span class="hint">(pemakaian bahan baku &amp; penolong)</span> (Rp)</td><td class="v num">{{ F::idr($d['q312_tw'] ?? null) }}</td></tr>
        <tr><td class="k">313. Biaya operasional <span class="hint">(air, listrik, gas, pemeliharaan, angkutan)</span> (Rp)</td><td class="v num">{{ F::idr($d['q313_tw'] ?? null) }}</td></tr>
    </table>

    <div class="sub">Ekspor Impor Luar Negeri</div>
    <table class="kv">
        <tr><td class="k">314. Persentase nilai produksi yang dijual sebagai produk ekspor luar negeri</td><td class="v num">{{ F::pct($d['q314_tw'] ?? null) }}</td></tr>
        <tr><td class="k">315. Persentase bahan baku &amp; penolong yang diperoleh melalui impor luar negeri</td><td class="v num">{{ F::pct($d['q315_tw'] ?? null) }}</td></tr>
    </table>

@else

    {{-- ══ TAHUNAN ════════════════════════════════════════════════════════ --}}
    @unless($isInd)
    <div class="sub">Pendapatan Perusahaan</div>
    <div class="sx">
        <table class="dt">
            <thead>
                <tr>
                    <th class="ta-l">Uraian</th>
                    <th>a. Satu triwulan lalu (Rp)</th>
                    <th>b. Selama tahun {{ $yr3b }} (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ta-l">303. Pendapatan dari penjualan barang dan jasa</td>
                    <td class="num">{{ F::idr($d['q303'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q303_year'] ?? null) }}</td>
                </tr>
                <tr>
                    <td class="ta-l">304. Pendapatan royalti, bunga, deviden dan lainnya</td>
                    <td class="num">{{ F::idr($d['q304'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q304_year'] ?? null) }}</td>
                </tr>
                <tr class="r-total">
                    <td class="ta-l">305. Total pendapatan <span class="hint">(303 + 304)</span></td>
                    <td class="num">{{ F::idr($d['q305'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q305_year'] ?? null) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <table class="kv">
        <tr><td class="k">306. Persentase pendapatan yang diperoleh dari usaha online</td><td class="v num">{{ F::pct($d['q306_online'] ?? null) }}</td></tr>
    </table>
    @endunless

    <div class="sub">Persediaan (Inventori) — 1 Januari {{ $yr3b }} vs 31 Desember {{ $yr3b }}</div>
    <div class="sx">
        <table class="dt">
            <thead>
                <tr>
                    <th class="ta-l">Jenis Persediaan</th>
                    <th>a. Kondisi 1 Januari {{ $yr3b }} (Rp)</th>
                    <th>b. Kondisi 31 Desember {{ $yr3b }} (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ta-l">307. Stok bahan baku, bahan penolong, bahan bakar, bahan pembungkus, dan lain-lain</td>
                    <td class="num">{{ F::idr($d['q306_year_awal'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q306_year_akhir'] ?? null) }}</td>
                </tr>
                <tr>
                    <td class="ta-l">308. Stok barang produksi setengah jadi</td>
                    <td class="num">{{ F::idr($d['q307_year_awal'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q307_year_akhir'] ?? null) }}</td>
                </tr>
                <tr>
                    <td class="ta-l">309. Stok barang jadi yang dihasilkan</td>
                    <td class="num">{{ F::idr($d['q308_year_awal'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q308_year_akhir'] ?? null) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="sub">Barang Modal Tetap — Tahun {{ $yr3b }}</div>
    <table class="kv">
        <tr><td class="k">310. Pembelian/penambahan &amp; pembuatan/perbaikan besar barang modal tetap (Rp)</td><td class="v num">{{ F::idr($d['q310_beli_modal'] ?? null) }}</td></tr>
        <tr><td class="k">311. Penjualan/pengurangan barang modal tetap (Rp)</td><td class="v num">{{ F::idr($d['q311_jual_modal'] ?? null) }}</td></tr>
        <tr><td class="k">312. Taksiran barang modal tetap menurut harga berlaku per 31 Desember {{ $yr3b }} (Rp)</td><td class="v num">{{ F::idr($d['q312_taksir_modal'] ?? null) }}</td></tr>
    </table>

    <div class="sub">Pengeluaran untuk Pekerja/Karyawan — Tahun {{ $yr3b }}</div>
    <div class="sx">
        <table class="dt">
            <thead>
                <tr>
                    <th class="ta-l">Komponen</th>
                    <th>313. Pekerja/karyawan <span class="hint">(non-outsourcing)</span> (Rp)</th>
                    <th>314. Pekerja/karyawan outsourcing (Rp)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ta-l">a.1 Upah/gaji, lembur, dan tunjangan pekerja produksi</td>
                    <td class="num">{{ F::idr($d['q313_a1'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q314_a1'] ?? null) }}</td>
                </tr>
                <tr>
                    <td class="ta-l">a.2 Pengeluaran lain untuk pekerja produksi</td>
                    <td class="num">{{ F::idr($d['q313_a2'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q314_a2'] ?? null) }}</td>
                </tr>
                <tr>
                    <td class="ta-l">b.1 Upah/gaji, lembur, dan tunjangan pekerja lainnya</td>
                    <td class="num">{{ F::idr($d['q313_b1'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q314_b1'] ?? null) }}</td>
                </tr>
                <tr>
                    <td class="ta-l">b.2 Pengeluaran lain untuk pekerja lainnya</td>
                    <td class="num">{{ F::idr($d['q313_b2'] ?? null) }}</td>
                    <td class="num">{{ F::idr($d['q314_b2'] ?? null) }}</td>
                </tr>
                <tr class="r-total">
                    <td class="ta-l">c. Total <span class="hint">(a.1 + a.2 + b.1 + b.2)</span></td>
                    <td class="num">{{ F::idr(F::sumOrStored($d, 'q313_c', ['q313_a1','q313_a2','q313_b1','q313_b2'])) }}</td>
                    <td class="num">{{ F::idr(F::sumOrStored($d, 'q314_c', ['q314_a1','q314_a2','q314_b1','q314_b2'])) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="sub">315. Penggunaan Listrik yang Dipakai Perusahaan</div>
    <table class="kv">
        <tr><td class="k">315a. Daya tersambung dari PLN (VA)</td><td class="v num">{{ F::idr($d['q315_a'] ?? null) }}</td></tr>
        <tr><td class="k">315b. Daya tersambung dari Non PLN (VA)</td><td class="v num">{{ F::idr($d['q315_b'] ?? null) }}</td></tr>
        <tr><td class="k">315c. Banyaknya penggunaan listrik dari PLN (kWh)</td><td class="v num">{{ F::dec($d['q315_c'] ?? null) }}</td></tr>
        <tr><td class="k">315d. Banyaknya penggunaan listrik dari Non PLN (kWh)</td><td class="v num">{{ F::dec($d['q315_d'] ?? null) }}</td></tr>
        <tr><td class="k">315e. Pengeluaran listrik yang dipakai perusahaan (Rp)</td><td class="v num">{{ F::idr($d['q315_e'] ?? null) }}</td></tr>
    </table>

    @unless($isInd)
    <div class="sub">316. Biaya Produksi <span class="hint">(pemakaian bahan baku dan penolong)</span></div>
    <table class="kv">
        <tr><td class="k">316a. Satu triwulan yang lalu (Rp)</td><td class="v num">{{ F::idr($d['q312'] ?? null) }}</td></tr>
        <tr><td class="k">316b. Selama tahun {{ $yr3b }} (Rp)</td><td class="v num">{{ F::idr($d['q312_year'] ?? null) }}</td></tr>
    </table>
    @endunless

    @php
        // 317 is one question in both forms, but Industri drops the two summary
        // rows (a. operasional / b. non-operasional) that Non-Industri asks for.
        $q317 = array_values(array_filter([
            $isInd ? null : ['a.', 'Biaya operasional (air, listrik, gas, pemeliharaan, biaya angkutan)', 'q317_a'],
            $isInd ? null : ['b.', 'Biaya non operasional (bunga pinjaman, pajak, premi asuransi, hadiah/sumbangan)', 'q317_b'],
            [$isInd ? 'a.1' : 'c.1', 'Sewa/kontrak gedung, mesin, serta alat-alat',        'q317_c1'],
            [$isInd ? 'a.2' : 'c.2', 'Sewa/kontrak tanah',                                 'q317_c2'],
            [$isInd ? 'b.'  : 'd.',  'Pajak/Tax',                                          'q317_d'],
            [$isInd ? 'c.'  : 'e.',  'Nilai bunga atas pinjaman',                          'q317_e'],
            [$isInd ? 'd.'  : 'f.',  'Nilai hadiah, sumbangan, derma dan sejenisnya',      'q317_f'],
            [$isInd ? 'e.'  : 'g.',  'Nilai dividen/laba yang dibagikan',                  'q317_g'],
            [$isInd ? 'f.'  : 'h.',  'Nilai premi asuransi kerugian yang dibayarkan',      'q317_h'],
            [$isInd ? 'g.'  : 'i.',  'Nilai jasa industri (maklun) yang dibayarkan ke pihak lain', 'q317_i'],
            [$isInd ? 'h.'  : 'j.',  'Air (selain untuk bahan baku dan penolong)',         'q317_j'],
            [$isInd ? 'i.'  : 'k.',  'Pengeluaran lainnya',                                'q317_k'],
        ]));
    @endphp
    <div class="sub">317. Pengeluaran Perusahaan Selama Tahun {{ $yr3b }} (Rp)</div>
    <table class="kv">
        @foreach($q317 as [$no, $label, $key])
        <tr><td class="k">317{{ $no }} {{ $label }}</td><td class="v num">{{ F::idr($d[$key] ?? null) }}</td></tr>
        @endforeach
    </table>

    @if($isInd)
        @php
            $moda = [
                'a' => ['Angkutan jalan', 'Truk, pick up, mobil, dan sepeda motor'],
                'b' => ['Angkutan kereta api', ''],
                'c' => ['Angkutan air sungai, danau, dan penyeberangan', ''],
                'd' => ['Angkutan air laut', ''],
                'e' => ['Angkutan udara', ''],
            ];
        @endphp
        <div class="sub">318. Moda Transportasi untuk Pengangkutan Barang — Tahun {{ $yr3b }}</div>
        <div class="sx">
            <table class="dt">
                <thead>
                    <tr>
                        <th class="ta-l">(1) Jenis Angkutan</th>
                        <th>(2) Frekuensi Penggunaan (kali)</th>
                        <th>(3) Total Biaya Pengangkutan (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($moda as $k => [$label, $note])
                    <tr>
                        <td class="ta-l">318{{ $k }}. {{ $label }}@if($note) <span class="hint">({{ $note }})</span>@endif</td>
                        <td class="num">{{ F::idr($d['q318' . $k . '_freq'] ?? null) }}</td>
                        <td class="num">{{ F::idr($d['q318' . $k . '_biaya'] ?? null) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <table class="kv">
            <tr><td class="k">319. Persentase moda angkutan yang menggunakan jasa pihak ketiga</td><td class="v num">{{ F::pct($d['q319_persen_pihak_ketiga'] ?? null) }}</td></tr>
        </table>
    @else
        @php
            $ni318a = F::num($d['q318a'] ?? null);
            $ni318b = F::num($d['q318b'] ?? null);
            $ni318c = F::sumOrStored($d, 'q318c', ['q318a', 'q318b']);
            $ni319i = F::sumOrStored($d, 'q319i', ['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h']);
        @endphp
        <div class="sub">Ekspor Impor Luar Negeri</div>
        <table class="kv">
            <tr><td class="k">319. Persentase nilai produksi yang dijual sebagai produk ekspor luar negeri</td><td class="v num">{{ F::pct($d['q314'] ?? null) }}</td></tr>
            <tr><td class="k">320. Persentase bahan baku &amp; penolong yang diperoleh melalui impor luar negeri</td><td class="v num">{{ F::pct($d['q315'] ?? null) }}</td></tr>
        </table>

        <div class="sub">321. Nilai Aset per 31 Desember {{ $yr3b }}</div>
        <table class="kv">
            <tr><td class="k">321a. Tanah dan bangunan (Rp)</td><td class="v num">{{ F::idr($ni318a) }}</td></tr>
            <tr><td class="k">321b. Selain tanah dan bangunan (Rp)</td><td class="v num">{{ F::idr($ni318b) }}</td></tr>
            <tr><td class="k">321c. Nilai total aset — jumlah a + b (Rp)</td><td class="v num">{{ F::idr($ni318c) }}</td></tr>
            <tr><td class="k">321c1. Rentang nilai <span class="hint">(jika c kosong)</span></td><td class="v">{{ F::rentangAset($d['q318c_range'] ?? null) }}</td></tr>
            <tr><td class="k">321d. Luas tanah untuk usaha (m&sup2;)</td><td class="v num">{{ F::dec($d['q318d_area'] ?? null) }}</td></tr>
        </table>

        <div class="sub">322. Susunan Kepemilikan Modal (%)</div>
        <table class="kv">
            <tr><td class="k">322a. Pribadi / Perorangan</td><td class="v num">{{ F::pct($d['q319a'] ?? null) }}</td></tr>
            <tr><td class="k">322b. Lembaga Nonprofit yang Melayani Rumah Tangga</td><td class="v num">{{ F::pct($d['q319b'] ?? null) }}</td></tr>
            <tr><td class="k">322c. Korporasi Publik</td><td class="v num">{{ F::pct($d['q319c'] ?? null) }}</td></tr>
            <tr><td class="k">322d. Korporasi Non Publik</td><td class="v num">{{ F::pct($d['q319d'] ?? null) }}</td></tr>
            <tr><td class="k">322e. Pemerintah Pusat</td><td class="v num">{{ F::pct($d['q319e'] ?? null) }}</td></tr>
            <tr><td class="k">322f. Pemerintah Daerah</td><td class="v num">{{ F::pct($d['q319f'] ?? null) }}</td></tr>
            <tr><td class="k">322g. Perusahaan Swasta Nasional</td><td class="v num">{{ F::pct($d['q319g'] ?? null) }}</td></tr>
            <tr><td class="k">322h. Asing</td><td class="v num">{{ F::pct($d['q319h'] ?? null) }}</td></tr>
            <tr class="r-total"><td class="k">322i. Total <span class="hint">(harus 100%)</span></td><td class="v num">{{ $ni319i !== null ? F::dec($ni319i) . ' %' : F::DASH }}</td></tr>
        </table>
    @endif

@endif

{{-- ── Rincian dari versi kuesioner sebelumnya ─────────────────────────────
     Submissions filled under an older question set still hold answers whose
     keys the form no longer uses. They are listed here rather than dropped,
     so the detail page always shows everything that was actually filled. --}}
@php
    $accounted = $isTw3b
        ? ['q303', 'q304', 'q305',
           'q306_awal', 'q306_akhir', 'q307_awal', 'q307_akhir',
           'q308_awal', 'q308_akhir', 'q309_awal', 'q309_akhir',
           'q310', 'q310_tw', 'q311', 'q311_tw', 'q312_tw', 'q313_tw',
           'q314_tw', 'q315_tw']
        : array_merge(
            ['q306_year_awal', 'q306_year_akhir', 'q307_year_awal', 'q307_year_akhir',
             'q308_year_awal', 'q308_year_akhir',
             'q310_beli_modal', 'q311_jual_modal', 'q312_taksir_modal',
             'q313_a1', 'q313_a2', 'q313_b1', 'q313_b2', 'q313_c',
             'q314_a1', 'q314_a2', 'q314_b1', 'q314_b2', 'q314_c',
             'q315_a', 'q315_b', 'q315_c', 'q315_d', 'q315_e',
             'q317_c1', 'q317_c2', 'q317_d', 'q317_e', 'q317_f',
             'q317_g', 'q317_h', 'q317_i', 'q317_j', 'q317_k'],
            $isInd
                // Aset, modal dan prospek untuk Industri dilaporkan di Blok IIIC.
                ? ['q318a_freq', 'q318a_biaya', 'q318b_freq', 'q318b_biaya',
                   'q318c_freq', 'q318c_biaya', 'q318d_freq', 'q318d_biaya',
                   'q318e_freq', 'q318e_biaya', 'q319_persen_pihak_ketiga',
                   'q318a', 'q318b', 'q318c', 'q318c_range', 'q318d_area',
                   'q319a', 'q319b', 'q319c', 'q319d', 'q319e', 'q319f',
                   'q319g', 'q319h', 'q319i',
                   'q320', 'q321', 'q322', 'q323', 'q324', 'q325', 'q326', 'q327', 'q328']
                : ['q303', 'q303_year', 'q304', 'q304_year', 'q305', 'q305_year',
                   'q306_online', 'q312', 'q312_year', 'q317_a', 'q317_b',
                   'q314', 'q315',
                   'q318a', 'q318b', 'q318c', 'q318c_range', 'q318d_area',
                   'q319a', 'q319b', 'q319c', 'q319d', 'q319e', 'q319f',
                   'q319g', 'q319h', 'q319i']
        );

    $legacyLabels = [
        'q306a'        => 'Persediaan bahan baku dsb. — triwulan lalu, awal',
        'q306b'        => 'Persediaan bahan baku dsb. — triwulan lalu, akhir',
        'q307a'        => 'Persediaan barang dalam proses — triwulan lalu, awal',
        'q307b'        => 'Persediaan barang dalam proses — triwulan lalu, akhir',
        'q308a'        => 'Persediaan barang jadi — triwulan lalu, awal',
        'q308b'        => 'Persediaan barang jadi — triwulan lalu, akhir',
        'q309a'        => 'Total persediaan — triwulan lalu, awal',
        'q309b'        => 'Total persediaan — triwulan lalu, akhir',
        'q310b_awal'   => 'Total persediaan — tahun, awal',
        'q310b_akhir'  => 'Total persediaan — tahun, akhir',
        'q311a'        => 'Total upah & gaji — triwulan lalu',
        'q311b'        => 'Total upah & gaji — selama tahun',
        'q311b1'       => 'Total upah & gaji — pegawai produksi',
        'q311b2'       => 'Total upah & gaji — selain pegawai produksi',
        'q313'         => 'Biaya operasional — triwulan lalu',
        'q313_year'    => 'Biaya operasional — selama tahun',
        'q315a'        => 'Biaya non-operasional — triwulan lalu',
        'q315b'        => 'Biaya non-operasional — selama tahun',
        'q304a'        => 'Pendapatan royalti dsb. — rincian a',
        'q304b'        => 'Pendapatan royalti dsb. — rincian b',
        'q305_online'  => 'Persentase pendapatan dari usaha online',
        'q303'         => 'Pendapatan dari penjualan barang dan jasa',
        'q304'         => 'Pendapatan royalti, bunga, deviden dan lainnya',
        'q305'         => 'Total pendapatan',
        'q306_awal'    => 'Persediaan bahan baku dsb. — triwulan, awal',
        'q306_akhir'   => 'Persediaan bahan baku dsb. — triwulan, akhir',
        'q307_awal'    => 'Persediaan barang dalam proses — triwulan, awal',
        'q307_akhir'   => 'Persediaan barang dalam proses — triwulan, akhir',
        'q308_awal'    => 'Persediaan barang jadi — triwulan, awal',
        'q308_akhir'   => 'Persediaan barang jadi — triwulan, akhir',
        'q309_awal'    => 'Total persediaan — triwulan, awal',
        'q309_akhir'   => 'Total persediaan — triwulan, akhir',
        'q310'         => 'Total upah & gaji, jaminan sosial pegawai — triwulan',
        'q310_tw'      => 'Total upah & gaji, jaminan sosial pegawai — triwulan',
        'q311'         => 'Penambahan aset tetap — triwulan',
        'q311_tw'      => 'Penambahan aset tetap — triwulan',
        'q312'         => 'Biaya produksi — triwulan lalu',
        'q312_year'    => 'Biaya produksi — selama tahun',
        'q312_tw'      => 'Biaya produksi — triwulan',
        'q313_tw'      => 'Biaya operasional — triwulan',
        'q314'         => 'Persentase produksi yang diekspor',
        'q314_tw'      => 'Persentase produksi yang diekspor — triwulan',
        'q315'         => 'Persentase bahan baku dari impor',
        'q315_tw'      => 'Persentase bahan baku dari impor — triwulan',
    ];

    $extra = [];
    foreach ($d as $key => $value) {
        if (in_array($key, $accounted, true) || is_array($value) || $value === null || $value === '') {
            continue;
        }
        // Zeros here are leftover auto-totals, not answers worth surfacing.
        if (F::num($value) === 0.0) {
            continue;
        }
        $extra[$key] = $value;
    }
@endphp

@if(!empty($extra))
<div class="sub">Rincian Lain yang Tersimpan</div>
<table class="kv">
    @foreach($extra as $key => $value)
    <tr>
        <td class="k">{{ $legacyLabels[$key] ?? $key }} <span class="hint">({{ $key }})</span></td>
        <td class="v num">{{ F::num($value) !== null ? F::idr($value) : F::plain($value) }}</td>
    </tr>
    @endforeach
</table>
<p class="note">
    Rincian di atas berasal dari versi kuesioner sebelumnya dan tidak lagi ditanyakan pada formulir saat ini.
</p>
@endif
