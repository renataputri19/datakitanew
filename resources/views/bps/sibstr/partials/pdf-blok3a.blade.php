{{-- Blok IIIA: Kondisi Perekonomian (PDF-friendly, per triwulan) --}}
@php
    $products  = $surveyResponse->blok3a_products ?? [];
    $lainnya   = $surveyResponse->blok3a_lainnya  ?? [];
    $totals    = $surveyResponse->blok3a_totals    ?? [];
    $tw3a      = (int)($surveyResponse->triwulan ?? 0);
    $isTw3a    = $tw3a > 0;
    $tahun3a   = (int)($surveyResponse->tahun ?? 2025);
    $prev3a    = $tahun3a - 1;

    $twMonthNames3a = ['jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei',
                       'jun'=>'Jun','jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt','nov'=>'Nov','des'=>'Des'];
    $twMonthMap3a   = [1=>['jan','feb','mar'],2=>['apr','mei','jun'],3=>['jul','agu','sep'],4=>['okt','nov','des']];
    $twLabelMap3a   = [1=>'Triwulan I',2=>'Triwulan II',3=>'Triwulan III',4=>'Triwulan IV'];

    if ($isTw3a) {
        $qMths = [];
        foreach (($twMonthMap3a[$tw3a] ?? []) as $_m) {
            $qMths["{$tahun3a}_{$_m}"] = $twMonthNames3a[$_m] . " {$tahun3a}";
        }
        $sections = [
            "Des {$prev3a}" => ["{$prev3a}_des" => "Des {$prev3a}"],
            ($twLabelMap3a[$tw3a] ?? 'Triwulan') . " ({$tahun3a})" => $qMths,
        ];
    } else {
        $sections = [
            "Des {$prev3a}" => ["{$prev3a}_des" => "Des {$prev3a}"],
            "Triwulan I ({$tahun3a})"   => ["{$tahun3a}_jan"=>'Jan', "{$tahun3a}_feb"=>'Feb', "{$tahun3a}_mar"=>'Mar'],
            "Triwulan II ({$tahun3a})"  => ["{$tahun3a}_apr"=>'Apr', "{$tahun3a}_mei"=>'Mei', "{$tahun3a}_jun"=>'Jun'],
            "Triwulan III ({$tahun3a})" => ["{$tahun3a}_jul"=>'Jul', "{$tahun3a}_agu"=>'Agu', "{$tahun3a}_sep"=>'Sep'],
            "Triwulan IV ({$tahun3a})"  => ["{$tahun3a}_okt"=>'Okt', "{$tahun3a}_nov"=>'Nov', "{$tahun3a}_des"=>'Des'],
        ];
    }

    if (!function_exists('_nf_zero')) {
        function _nf_zero($v) { return ($v !== null && $v !== '') ? number_format((float)$v, 0, ',', '.') : '-'; }
    }
    if (!function_exists('_nf_price')) {
        function _nf_price($v) { return ($v !== null && $v !== '') ? number_format((float)$v, 2, ',', '.') : '-'; }
    }
@endphp

@if(count($products) > 0 || !empty($lainnya) || !empty($totals))
    @foreach($sections as $secTitle => $secMonths)
        <div style="page-break-inside: avoid; margin-bottom: 10px;">
            <div style="font-weight: 700; margin: 6px 0;">{{ $secTitle }}</div>
            <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                <thead>
                    <tr>
                        <th style="border:1px solid #e5e7eb; background:#f9fafb; text-align:left; width:{{ $isTw3a ? '20%' : '16%' }}; padding:3px 5px;">Kode/Nama</th>
                        @if(!$isTw3a)
                        <th style="border:1px solid #e5e7eb; background:#f9fafb; text-align:left; width:20%; padding:3px 5px;">Detail Produk</th>
                        @endif
                        <th style="border:1px solid #e5e7eb; background:#f9fafb; text-align:left; width:12%; padding:3px 5px;">Uraian</th>
                        @foreach($secMonths as $label)
                            <th style="border:1px solid #e5e7eb; background:#f9fafb; text-align:center; padding:3px 5px;">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $i => $p)
                        @php $productName = $p['jenis_barang'] ?? ($p['name'] ?? ('Produk ' . ($i+1))); @endphp
                        <tr>
                            <td style="border:1px solid #e5e7eb; vertical-align:top; padding:3px 5px;" rowspan="3">
                                <div><strong>{{ '301.'.($i+1) }}</strong></div>
                                <div>{{ $productName }}</div>
                            </td>
                            @if(!$isTw3a)
                            <td style="border:1px solid #e5e7eb; vertical-align:top; padding:3px 5px; font-size:9px;" rowspan="3">
                                KBLI: {{ !empty($p['kbli_5digit']) ? $p['kbli_5digit'] : '-' }}<br>
                                % Ekspor: {{ (isset($p['persen_ekspor']) && $p['persen_ekspor'] !== '') ? number_format((float)$p['persen_ekspor'],2,',','.').' %' : '-' }}<br>
                                Negara: {{ !empty($p['negara_ekspor']) ? $p['negara_ekspor'] : '-' }}
                            </td>
                            @endif
                            <td style="border:1px solid #e5e7eb; padding:3px 5px;">Banyaknya</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                @php
                                    $qty = $p['banyaknya'][$mKey] ?? null;
                                    $unit = $p['satuan'] ?? '';
                                    $qtyText = $qty !== null ? number_format((float)$qty, 0, ',', '.') : null;
                                @endphp
                                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 5px;">{{ $qtyText !== null ? ($qtyText . ($unit ? ' ' . e($unit) : '')) : '-' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="border:1px solid #e5e7eb; padding:3px 5px;">Nilai (Rp)</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 5px;">{{ isset($p['nilai'][$mKey]) && $p['nilai'][$mKey] !== null ? _nf_zero($p['nilai'][$mKey]) : '-' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="border:1px solid #e5e7eb; padding:3px 5px;">Harga/Satuan</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                @php
                                    $qty = $p['banyaknya'][$mKey] ?? null;
                                    $nilai = $p['nilai'][$mKey] ?? null;
                                    $computed = ($qty !== null && (float)$qty > 0 && $nilai !== null) ? ((float)$nilai / (float)$qty) : null;
                                    $price = $computed ?? ($p['harga_satuan'][$mKey] ?? null);
                                @endphp
                                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 5px;">{{ $price !== null ? _nf_price($price) : '-' }}</td>
                            @endforeach
                        </tr>
                        @if(!$isTw3a && !empty($p['rincian_ekspor']) && is_array($p['rincian_ekspor']))
                        @foreach($p['rincian_ekspor'] as $pdfReIdx => $pdfRe)
                        @php
                            $pdfReJml = preg_replace('/[^0-9]/', '', $pdfRe['jumlah'] ?? '');
                            $pdfReNil = preg_replace('/[^0-9]/', '', $pdfRe['nilai'] ?? '');
                            $pdfReHas = !empty($pdfRe['provinsi']) || $pdfReJml !== '' || $pdfReNil !== '';
                        @endphp
                        @if($pdfReHas)
                        <tr style="background:#f0fdf4;">
                            <td style="border:1px solid #bbf7d0; vertical-align:top; padding:2px 5px; font-size:8px; color:#15803d; font-weight:700;">↳{{ $pdfReIdx+1 }}</td>
                            @if(!$isTw3a)
                            <td style="border:1px solid #bbf7d0; padding:2px 5px; color:#15803d; font-size:9px; font-style:italic;">{{ !empty($pdfRe['provinsi']) ? $pdfRe['provinsi'] : '—' }}</td>
                            @endif
                            <td style="border:1px solid #bbf7d0; padding:2px 5px; font-size:8px; color:#6b7280;">Prov. Tujuan</td>
                            <td colspan="{{ count($secMonths) }}" style="border:1px solid #bbf7d0; padding:2px 5px; font-size:9px;">
                                Banyaknya: {{ $pdfReJml !== '' ? number_format((int)$pdfReJml, 0, ',', '.') : '—' }}
                                &nbsp;&nbsp; Nilai: Rp {{ $pdfReNil !== '' ? number_format((int)$pdfReNil, 0, ',', '.') : '—' }}
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        @endif
                    @endforeach

                    @if($isTw3a && !empty($lainnya))
                        <tr style="background:#fefce8;">
                            <td style="border:1px solid #e5e7eb; padding:3px 5px;" colspan="{{ $isTw3a ? 1 : 2 }}">
                                <div><strong>302.</strong></div>
                                <div>Lainnya</div>
                            </td>
                            <td style="border:1px solid #e5e7eb; padding:3px 5px;">Nilai (Rp)</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 5px;">{{ isset($lainnya['nilai'][$mKey]) && $lainnya['nilai'][$mKey] !== null ? _nf_zero($lainnya['nilai'][$mKey]) : '-' }}</td>
                            @endforeach
                        </tr>
                    @endif

                    @if(!empty($totals))
                        <tr style="background:#f0fdf4; font-weight:600;">
                            <td style="border:1px solid #e5e7eb; padding:3px 5px;" colspan="{{ $isTw3a ? 1 : 2 }}">
                                <div><strong>303.</strong></div>
                                <div>Total</div>
                            </td>
                            <td style="border:1px solid #e5e7eb; padding:3px 5px;">Nilai (Rp)</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 5px;">{{ isset($totals[$mKey]) && $totals[$mKey] !== null ? _nf_zero($totals[$mKey]) : '-' }}</td>
                            @endforeach
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    @endforeach
@else
    <div style="text-align:center; padding: 8px; color:#6b7280;">Belum ada data untuk ditampilkan.</div>
@endif

{{-- Q302 Pendapatan Lainnya / Q305 Maklun / Q306 Online (tahunan only, not for triwulanan) --}}
@if(!$isTw3a)
@php
    $plPdf  = $surveyResponse->blok3a_pendapatan_lainnya ?? [];
    $maklun = $surveyResponse->blok3a_q305a_maklun_nilai ?? null;
    $pct    = $surveyResponse->blok3a_q305b_maklun_pct ?? null;
    $online = $surveyResponse->blok3a_q305_online ?? null;
@endphp
@if(!empty($plPdf) || $maklun !== null || $online !== null)
<div style="margin-top:12px; page-break-inside:avoid;">
    <div style="font-weight:700; font-size:12px; margin-bottom:5px; color:#1e40af;">Pendapatan Lainnya & Jasa Industri (Tahun 2025)</div>
    <table style="width:100%; border-collapse:collapse; font-size:10px;">
        @if(!empty($plPdf))
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px; width:55%;">302a. Penjualan barang tanpa proses</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ _nf_zero($plPdf['q302a'] ?? null) }}</td></tr>
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px;">302b. Penjualan kekayaan intelektual</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ _nf_zero($plPdf['q302b'] ?? null) }}</td></tr>
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px;">302c. Jasa tidak berkaitan produksi</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ _nf_zero($plPdf['q302c'] ?? null) }}</td></tr>
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px;">302d. Tenaga listrik dijual</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ _nf_zero($plPdf['q302d'] ?? null) }}</td></tr>
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px;">302e. Pendapatan non operasional</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ _nf_zero($plPdf['q302e'] ?? null) }}</td></tr>
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px;">302f. Lainnya</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ _nf_zero($plPdf['q302f'] ?? null) }}</td></tr>
        @endif
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px;">305a. Pendapatan maklun (Rp)</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ _nf_zero($maklun) }}</td></tr>
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px;">305b. % Maklun dari luar negeri</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ ($pct !== null && $pct !== '') ? $pct.' %' : '-' }}</td></tr>
        <tr><td style="border:1px solid #e5e7eb; padding:3px 8px;">306. % Pendapatan usaha online</td><td style="border:1px solid #e5e7eb; text-align:right; padding:3px 8px;">{{ ($online !== null && $online !== '') ? $online.' %' : '-' }}</td></tr>
    </table>
</div>
@endif
@endif