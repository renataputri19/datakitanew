{{--
  Blok V — Kondisi & Prospek Usaha, read-only.

  Rendered as answered values instead of disabled radio pills: the pill grid
  needed ~920px minimum and was the main reason this table came out cramped,
  while showing three options per cell to read one answer.

  Expects: $surveyResponse
--}}
@php
    use App\Support\SibstrFormat as F;

    $tw5    = (int) ($surveyResponse->triwulan ?? 0);
    $yr5    = (int) ($surveyResponse->tahun ?? 2025);
    $isTw5  = $tw5 > 0;
    $roman  = ['I', 'II', 'III', 'IV'];
    $data5  = $surveyResponse->blok5_data ?? [];

    $rows5 = [
        ['key' => '501', 'label' => 'Pesanan',                  'type' => 'normal',   'desc' => 'Jumlah pesanan barang produksi yang diterima perusahaan, domestik dan ekspor'],
        ['key' => '502', 'label' => 'Produksi',                 'type' => 'normal',   'desc' => 'Jumlah produksi barang yang dihasilkan perusahaan'],
        ['key' => '503', 'label' => 'Kapasitas Produksi',       'type' => 'normal',   'desc' => 'Keluaran maksimum yang mampu dihasilkan mesin produksi utama'],
        ['key' => '504', 'label' => 'Tenaga Kerja',             'type' => 'normal',   'desc' => 'Rata-rata jumlah tenaga kerja'],
        ['key' => '505', 'label' => 'Jam Kerja',                'type' => 'normal',   'desc' => 'Rata-rata jam kerja per hari'],
        ['key' => '506', 'label' => 'Waktu Pengiriman Pemasok', 'type' => 'delivery', 'desc' => 'Waktu pengiriman bahan baku dari pemasok'],
        ['key' => '507', 'label' => 'Persediaan Bahan Baku',    'type' => 'normal',   'desc' => 'Jumlah persediaan bahan baku yang disimpan perusahaan'],
    ];

    if ($isTw5) {
        $prevTw = $tw5 === 1 ? 4 : $tw5 - 1;
        $prevYr = $tw5 === 1 ? $yr5 - 1 : $yr5;
        $nextTw = $tw5 === 4 ? 1 : $tw5 + 1;
        $nextYr = $tw5 === 4 ? $yr5 + 1 : $yr5;

        $periods5 = ['p1', 'p2'];
        $headers5 = [
            "TW {$roman[$tw5 - 1]}-{$yr5} vs TW {$roman[$prevTw - 1]}-{$prevYr}",
            "TW {$roman[$nextTw - 1]}-{$nextYr} vs TW {$roman[$tw5 - 1]}-{$yr5}",
        ];
        $prospectIdx = [1];
    } else {
        $periods5 = ['p1', 'p2', 'p3', 'p5', 'p6'];
        $headers5 = [
            'TW I-' . $yr5 . ' vs TW IV-' . ($yr5 - 1),
            "TW II-{$yr5} vs TW I-{$yr5}",
            "TW III-{$yr5} vs TW II-{$yr5}",
            "TW IV-{$yr5} vs TW III-{$yr5}",
            'TW I-' . ($yr5 + 1) . " vs TW IV-{$yr5}",
        ];
        $prospectIdx = [4];
    }

    $labelNormal   = ['naik' => 'Naik', 'tetap' => 'Tetap', 'turun' => 'Turun'];
    $labelDelivery = ['lebih_cepat' => 'Lebih cepat', 'tetap' => 'Tetap', 'lebih_lambat' => 'Lebih lambat'];
@endphp

<p class="note">
    Kolom terakhir adalah <strong>prospek</strong> (perkiraan triwulan berikutnya);
    kolom lainnya adalah <strong>kondisi</strong> yang sudah terjadi.
</p>

<div class="sx">
    <table class="dt">
        <thead>
            <tr>
                <th class="ta-l">Komponen</th>
                @foreach($headers5 as $i => $h)
                <th class="{{ in_array($i, $prospectIdx, true) ? 'th-prospect' : '' }}">
                    {{ in_array($i, $prospectIdx, true) ? 'Prospek' : 'Kondisi' }}<br>{{ $h }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($rows5 as $row)
            <tr>
                <td class="ta-l">
                    <strong>{{ $row['key'] }}. {{ $row['label'] }}</strong>
                    <span class="hint block">{{ $row['desc'] }}</span>
                </td>
                @foreach($periods5 as $i => $pKey)
                    @php
                        $val = $data5[$row['key']][$pKey] ?? null;
                        $map = $row['type'] === 'delivery' ? $labelDelivery : $labelNormal;
                    @endphp
                <td class="ta-c {{ in_array($i, $prospectIdx, true) ? 'c-prospect' : '' }}">
                    {{ $map[$val] ?? F::DASH }}
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
