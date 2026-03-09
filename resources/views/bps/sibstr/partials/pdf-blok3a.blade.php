{{-- Blok IIIA: Kondisi Perekonomian (PDF-friendly, per triwulan) --}}
@php
    $products = $surveyResponse->blok3a_products ?? [];
    $lainnya = $surveyResponse->blok3a_lainnya ?? [];
    $totals = $surveyResponse->blok3a_totals ?? [];

    // Build sections: Split Triwulan I into two tables (Des 2024 vs Jan–Mar 2025)
    $sections = [
        'Triwulan I — Perbandingan (Des 2024)' => [
            '2024_des' => 'Des 2024',
        ],
        'Triwulan I (Jan–Mar 2025)' => [
            '2025_jan' => 'Jan',
            '2025_feb' => 'Feb',
            '2025_mar' => 'Mar',
        ],
        'Triwulan II (Apr–Jun 2025)' => [
            '2025_apr' => 'Apr',
            '2025_mei' => 'Mei',
            '2025_jun' => 'Jun',
        ],
        'Triwulan III (Jul–Sep 2025)' => [
            '2025_jul' => 'Jul',
            '2025_agu' => 'Agu',
            '2025_sep' => 'Sep',
        ],
        'Triwulan IV (Okt–Des 2025)' => [
            '2025_okt' => 'Okt',
            '2025_nov' => 'Nov',
            '2025_des' => 'Des',
        ],
    ];

    function _nf_zero($v) {
        return ($v !== null && $v !== '') ? number_format((float)$v, 0, ',', '.') : '-';
    }
    function _nf_price($v) {
        return ($v !== null && $v !== '') ? number_format((float)$v, 2, ',', '.') : '-';
    }
@endphp

@if(count($products) > 0 || !empty($lainnya) || !empty($totals))
    @foreach($sections as $secTitle => $secMonths)
        <div style="page-break-inside: avoid; margin-bottom: 10px;">
            <div style="font-weight: 700; margin: 6px 0;">{{ $secTitle }}</div>
            <table style="width: 100%; border-collapse: collapse; font-size: 11px;">
                <thead>
                    <tr>
                        <th style="border:1px solid #e5e7eb; background:#f9fafb; text-align:left; width: 22%;">Kode/Nama</th>
                        <th style="border:1px solid #e5e7eb; background:#f9fafb; text-align:left; width: 14%;">Uraian</th>
                        @foreach($secMonths as $label)
                            <th style="border:1px solid #e5e7eb; background:#f9fafb; text-align:center;">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $i => $p)
                        @php $productName = $p['jenis_barang'] ?? ($p['name'] ?? ('Produk ' . ($i+1))); @endphp
                        <tr>
                            <td style="border:1px solid #e5e7eb;" rowspan="3">
                                <div><strong>{{ '301.'.($i+1) }}</strong></div>
                                <div>{{ $productName }}</div>
                            </td>
                            <td style="border:1px solid #e5e7eb;">Banyaknya</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                @php
                                    $qty = $p['banyaknya'][$mKey] ?? null;
                                    $unit = $p['satuan'] ?? '';
                                    $qtyText = $qty !== null ? number_format((float)$qty, 0, ',', '.') : null;
                                @endphp
                                <td style="border:1px solid #e5e7eb; text-align:right;">{{ $qtyText !== null ? ($qtyText . ($unit ? ' ' . e($unit) : '')) : '-' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="border:1px solid #e5e7eb;">Nilai</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                <td style="border:1px solid #e5e7eb; text-align:right;">{{ isset($p['nilai'][$mKey]) && $p['nilai'][$mKey] !== null ? _nf_zero($p['nilai'][$mKey]) : '-' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td style="border:1px solid #e5e7eb;">Harga/Satuan</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                @php
                                    $qty = $p['banyaknya'][$mKey] ?? null;
                                    $nilai = $p['nilai'][$mKey] ?? null;
                                    $computed = ($qty !== null && (float)$qty > 0 && $nilai !== null) ? ((float)$nilai / (float)$qty) : null;
                                    $price = $computed ?? ($p['harga_satuan'][$mKey] ?? null);
                                @endphp
                                <td style="border:1px solid #e5e7eb; text-align:right;">{{ $price !== null ? _nf_price($price) : '-' }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                    @if(!empty($lainnya))
                        <tr style="background:#fefce8;">
                            <td style="border:1px solid #e5e7eb;">
                                <div><strong>302.</strong></div>
                                <div>Lainnya</div>
                            </td>
                            <td style="border:1px solid #e5e7eb;">Nilai</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                <td style="border:1px solid #e5e7eb; text-align:right;">{{ isset($lainnya['nilai'][$mKey]) && $lainnya['nilai'][$mKey] !== null ? _nf_zero($lainnya['nilai'][$mKey]) : '-' }}</td>
                            @endforeach
                        </tr>
                    @endif

                    @if(!empty($totals))
                        <tr style="background:#f0fdf4; font-weight:600;">
                            <td style="border:1px solid #e5e7eb;">
                                <div><strong>303.</strong></div>
                                <div>Total</div>
                            </td>
                            <td style="border:1px solid #e5e7eb;">Nilai</td>
                            @foreach($secMonths as $mKey => $mLbl)
                                <td style="border:1px solid #e5e7eb; text-align:right;">{{ isset($totals[$mKey]) && $totals[$mKey] !== null ? _nf_zero($totals[$mKey]) : '-' }}</td>
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