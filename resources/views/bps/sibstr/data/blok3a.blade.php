{{--
  Blok IIIA — Barang yang Diproduksi & Pendapatan, read-only.

  The monthly figures are split into one table per triwulan instead of a single
  13-column sheet — that is what kept the old view cramped and clipped the
  rupiah figures. Per-product attributes (KBLI, % ekspor, negara) are listed
  once in "Daftar Produk" rather than repeated as extra columns.

  Expects: $surveyResponse
--}}
@php
    use App\Support\SibstrFormat as F;

    $products = $surveyResponse->blok3a_products ?? [];
    $lainnya  = $surveyResponse->blok3a_lainnya  ?? [];
    $totals   = $surveyResponse->blok3a_totals   ?? [];
    $pl       = $surveyResponse->blok3a_pendapatan_lainnya ?? [];

    $tahun3a = (int) ($surveyResponse->tahun ?? 2025);
    $tw3a    = (int) ($surveyResponse->triwulan ?? 0);
    $isTw3a  = $tw3a > 0;
    $prev3a  = $tahun3a - 1;

    $mName = ['jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei','jun'=>'Jun',
              'jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt','nov'=>'Nov','des'=>'Des'];
    $twMonths = [1=>['jan','feb','mar'], 2=>['apr','mei','jun'], 3=>['jul','agu','sep'], 4=>['okt','nov','des']];
    $twRoman  = [1=>'I', 2=>'II', 3=>'III', 4=>'IV'];

    // Each section is at most 4 columns wide so nothing has to be squeezed.
    // The Des-previous-year baseline rides along with the first section.
    $sections = [];
    $baseline = ["{$prev3a}_des" => "Des {$prev3a}"];

    if ($isTw3a) {
        $cols = $baseline;
        foreach ($twMonths[$tw3a] ?? [] as $m) {
            $cols["{$tahun3a}_{$m}"] = $mName[$m] . " {$tahun3a}";
        }
        $sections["Triwulan {$twRoman[$tw3a]} {$tahun3a}"] = $cols;
    } else {
        foreach ([1, 2, 3, 4] as $q) {
            $cols = $q === 1 ? $baseline : [];
            foreach ($twMonths[$q] as $m) {
                $cols["{$tahun3a}_{$m}"] = $mName[$m];
            }
            $sections["Triwulan {$twRoman[$q]} {$tahun3a}"] = $cols;
        }
    }

    $hasAny = count($products) > 0 || !empty($lainnya) || !empty($totals);
@endphp

@if(!$hasAny)
    <div class="empty">Belum ada data produksi untuk ditampilkan.</div>
@else

    {{-- ── 301. Daftar produk: attributes listed once ──────────────────────── --}}
    @if(count($products) > 0)
    <div class="sub">301. Daftar Barang yang Diproduksi</div>
    <div class="sx">
        <table class="dt">
            <thead>
                <tr>
                    <th class="w-no">No.</th>
                    <th class="ta-l">Nama Barang</th>
                    <th>Satuan</th>
                    @if(!$isTw3a)
                    <th>KBLI 5 Digit</th>
                    <th>% Ekspor *)</th>
                    <th class="ta-l">Negara Ekspor **)</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($products as $i => $p)
                <tr>
                    <td class="ta-c">301.{{ $i + 1 }}</td>
                    <td class="ta-l">{{ $p['jenis_barang'] ?? ($p['name'] ?? 'Produk ' . ($i + 1)) }}</td>
                    <td class="ta-c">{{ F::plain($p['satuan'] ?? null) }}</td>
                    @if(!$isTw3a)
                    <td class="ta-c">{{ F::plain($p['kbli_5digit'] ?? null) }}</td>
                    <td class="num">{{ isset($p['persen_ekspor']) && $p['persen_ekspor'] !== '' ? F::dec($p['persen_ekspor']) . ' %' : F::DASH }}</td>
                    <td class="ta-l">{{ F::plain($p['negara_ekspor'] ?? null) }}</td>
                    @endif
                </tr>

                @if(!$isTw3a && !empty($p['rincian_ekspor']) && is_array($p['rincian_ekspor']))
                    @foreach($p['rincian_ekspor'] as $re)
                        @php
                            $reJml = F::num($re['jumlah'] ?? null);
                            $reNil = F::num($re['nilai'] ?? null);
                        @endphp
                        @if(!empty($re['provinsi']) || $reJml !== null || $reNil !== null)
                        <tr class="r-sub">
                            <td class="ta-c">&#8627;</td>
                            <td class="ta-l" colspan="5">
                                Prov. tujuan: <strong>{{ F::plain($re['provinsi'] ?? null) }}</strong>
                                &nbsp;&middot;&nbsp; Banyaknya: <strong>{{ F::idr($reJml) }}</strong>
                                &nbsp;&middot;&nbsp; Nilai: <strong>Rp {{ F::idr($reNil) }}</strong>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="note">
        *) Termasuk yang diekspor oleh eksportir umum atau pihak lain.
        **) Jika negara tujuan ekspor lebih dari satu, dituliskan yang terbesar.
    </p>
    @endif

    {{-- ── Monthly figures, one table per triwulan ─────────────────────────── --}}
    @foreach($sections as $secTitle => $secMonths)
    <div class="sub">{{ $secTitle }} — Banyaknya, Nilai &amp; Harga per Satuan</div>
    <div class="sx keep">
        <table class="dt wide">
            <thead>
                <tr>
                    <th class="ta-l">Kode / Barang</th>
                    <th class="ta-l">Uraian</th>
                    @foreach($secMonths as $label)
                    <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($products as $i => $p)
                    @php
                        $pName = $p['jenis_barang'] ?? ($p['name'] ?? 'Produk ' . ($i + 1));
                        $unit  = $p['satuan'] ?? '';
                    @endphp
                    <tr>
                        <td class="ta-l" rowspan="3"><strong>301.{{ $i + 1 }}</strong><br>{{ $pName }}</td>
                        <td class="ta-l">Banyaknya{{ $unit ? ' (' . $unit . ')' : '' }}</td>
                        @foreach($secMonths as $mKey => $label)
                        <td class="num">{{ F::idr($p['banyaknya'][$mKey] ?? null) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="ta-l">Nilai (Rp)</td>
                        @foreach($secMonths as $mKey => $label)
                        <td class="num">{{ F::idr($p['nilai'][$mKey] ?? null) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="ta-l">Harga per Satuan (Rp)</td>
                        @foreach($secMonths as $mKey => $label)
                            @php
                                $qty   = F::num($p['banyaknya'][$mKey] ?? null);
                                $nilai = F::num($p['nilai'][$mKey] ?? null);
                                $price = ($qty !== null && $qty > 0 && $nilai !== null)
                                            ? ($nilai / $qty)
                                            : ($p['harga_satuan'][$mKey] ?? null);
                            @endphp
                        <td class="num">{{ F::dec($price) }}</td>
                        @endforeach
                    </tr>
                @endforeach

                @if(!empty($lainnya['nilai'] ?? null))
                <tr class="r-alt">
                    <td class="ta-l"><strong>302.</strong><br>Lainnya</td>
                    <td class="ta-l">Nilai (Rp)</td>
                    @foreach($secMonths as $mKey => $label)
                    <td class="num">{{ F::idr($lainnya['nilai'][$mKey] ?? null) }}</td>
                    @endforeach
                </tr>
                @endif

                @if(!empty($totals))
                <tr class="r-total">
                    <td class="ta-l"><strong>303.</strong><br>Total</td>
                    <td class="ta-l">Nilai (Rp)</td>
                    @foreach($secMonths as $mKey => $label)
                    <td class="num">{{ F::idr($totals[$mKey] ?? null) }}</td>
                    @endforeach
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    @endforeach

    {{-- ── Tahunan-only: pendapatan lainnya & jasa industri ────────────────── --}}
    @if(!$isTw3a)
        @php
            $maklun    = $surveyResponse->blok3a_q305a_maklun_nilai ?? null;
            $maklunPct = $surveyResponse->blok3a_q305b_maklun_pct ?? null;
            $online    = $surveyResponse->blok3a_q305_online ?? null;
        @endphp
        @if(!empty($pl) || $maklun !== null || $online !== null)
        <div class="sub">Pendapatan Lainnya &amp; Jasa Industri — Tahun {{ $tahun3a }}</div>
        <table class="kv">
            @if(!empty($pl))
            <tr><td class="k">302a. Keuntungan/kerugian penjualan barang tanpa proses</td><td class="v num">{{ F::idr($pl['q302a'] ?? null) }}</td></tr>
            <tr><td class="k">302b. Penjualan kekayaan intelektual</td><td class="v num">{{ F::idr($pl['q302b'] ?? null) }}</td></tr>
            <tr><td class="k">302c. Nilai jasa tidak berkaitan proses produksi</td><td class="v num">{{ F::idr($pl['q302c'] ?? null) }}</td></tr>
            <tr><td class="k">302d. Tenaga listrik yang dijual</td><td class="v num">{{ F::idr($pl['q302d'] ?? null) }}</td></tr>
            <tr><td class="k">302e. Pendapatan non operasional</td><td class="v num">{{ F::idr($pl['q302e'] ?? null) }}</td></tr>
            <tr><td class="k">302f. Lainnya</td><td class="v num">{{ F::idr($pl['q302f'] ?? null) }}</td></tr>
            @endif
            <tr><td class="k">305a. Nilai pendapatan maklun (Rp)</td><td class="v num">{{ F::idr($maklun) }}</td></tr>
            <tr><td class="k">305b. Persentase maklun dari luar negeri</td><td class="v num">{{ F::pct($maklunPct) }}</td></tr>
            <tr><td class="k">306. Persentase pendapatan dari usaha online</td><td class="v num">{{ F::pct($online) }}</td></tr>
        </table>
        @endif
    @endif

@endif
