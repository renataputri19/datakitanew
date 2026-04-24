{{-- Blok IIIC: Bahan Baku & Bahan Penolong (PDF — tahunan DN/LN table, matching BPS detail page) --}}
@php
    $materials = $surveyResponse->blok3a2_materials ?? [];

    if (!function_exists('_pdf3c_nf')) {
        function _pdf3c_nf($v) { return ($v !== null && $v !== '') ? number_format((float)$v, 0, ',', '.') : '-'; }
    }

@endphp

@if(count($materials) > 0)
<div style="page-break-inside: avoid;">
    <table style="width:100%; border-collapse:collapse; font-size:10px;">
        <thead>
            <tr>
                <th style="border:1px solid #e5e7eb; background:#f1f5f9; text-align:center; width:5%; padding:3px 4px;">(1)<br>No.</th>
                <th style="border:1px solid #e5e7eb; background:#f1f5f9; text-align:left; width:25%; padding:3px 4px;">(2)<br>Nama bahan baku &amp; penolong</th>
                <th style="border:1px solid #e5e7eb; background:#f1f5f9; text-align:center; width:10%; padding:3px 4px;">(3)<br>Satuan standar</th>
                <th style="border:1px solid #e5e7eb; background:#fef9c3; text-align:center; width:12%; padding:3px 4px;">(4)<br>Banyaknya<br><span style="font-size:9px;color:#92400e;">Dalam Negeri</span></th>
                <th style="border:1px solid #e5e7eb; background:#fef9c3; text-align:center; width:14%; padding:3px 4px;">(5)<br>Nilai (Rp)<br><span style="font-size:9px;color:#92400e;">Dalam Negeri</span></th>
                <th style="border:1px solid #e5e7eb; background:#dbeafe; text-align:center; width:12%; padding:3px 4px;">(6)<br>Banyaknya<br><span style="font-size:9px;color:#1e40af;">Luar Negeri</span></th>
                <th style="border:1px solid #e5e7eb; background:#dbeafe; text-align:center; width:14%; padding:3px 4px;">(7)<br>Nilai (Rp)<br><span style="font-size:9px;color:#1e40af;">Luar Negeri</span></th>
                <th style="border:1px solid #e5e7eb; background:#f1f5f9; text-align:center; width:18%; padding:3px 4px;">(8)<br>Negara asal **)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($materials as $i => $mat)
            <tr>
                <td style="border:1px solid #e5e7eb; text-align:center; padding:3px 4px;">{{ $i + 1 }}</td>
                <td style="border:1px solid #e5e7eb; padding:3px 4px;">{{ !empty($mat['nama_bahan']) ? $mat['nama_bahan'] : '-' }}</td>
                <td style="border:1px solid #e5e7eb; text-align:center; padding:3px 4px;">{{ !empty($mat['satuan_standar']) ? $mat['satuan_standar'] : '-' }}</td>
                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 4px; background:#fffde7;">{{ _pdf3c_nf($mat['dn_banyaknya'] ?? null) }}</td>
                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 4px; background:#fffde7;">{{ _pdf3c_nf($mat['dn_nilai'] ?? null) }}</td>
                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 4px; background:#eff6ff;">{{ _pdf3c_nf($mat['ln_banyaknya'] ?? null) }}</td>
                <td style="border:1px solid #e5e7eb; text-align:right; padding:3px 4px; background:#eff6ff;">{{ _pdf3c_nf($mat['ln_nilai'] ?? null) }}</td>
                <td style="border:1px solid #e5e7eb; text-align:center; padding:3px 4px;">{{ !empty($mat['negara_asal']) ? $mat['negara_asal'] : '-' }}</td>
            </tr>
            @if(!empty($mat['rincian_asal']) && is_array($mat['rincian_asal']))
            @foreach($mat['rincian_asal'] as $pdfRa)
            @php
                $pdfRaJml = preg_replace('/[^0-9]/', '', $pdfRa['jumlah'] ?? '');
                $pdfRaNil = preg_replace('/[^0-9]/', '', $pdfRa['nilai'] ?? '');
            @endphp
            @if(!empty($pdfRa['provinsi']) || $pdfRaJml !== '' || $pdfRaNil !== '')
            <tr style="background:#f0f9ff;">
                <td style="border:1px solid #dbeafe; text-align:center; padding:2px 4px; color:#93c5fd; font-size:8px;">↳</td>
                <td style="border:1px solid #dbeafe; padding:2px 4px; color:#1d4ed8; font-style:italic; font-size:9px;">{{ !empty($pdfRa['provinsi']) ? $pdfRa['provinsi'] : '—' }}</td>
                <td style="border:1px solid #dbeafe; padding:2px 4px;"></td>
                <td style="border:1px solid #dbeafe; text-align:right; padding:2px 4px; background:#fffde7; font-size:9px;">{{ $pdfRaJml !== '' ? number_format((int)$pdfRaJml, 0, ',', '.') : '—' }}</td>
                <td style="border:1px solid #dbeafe; text-align:right; padding:2px 4px; background:#fffde7; font-size:9px;">{{ $pdfRaNil !== '' ? number_format((int)$pdfRaNil, 0, ',', '.') : '—' }}</td>
                <td style="border:1px solid #dbeafe; padding:2px 4px; background:#eff6ff;"></td>
                <td style="border:1px solid #dbeafe; padding:2px 4px; background:#eff6ff;"></td>
                <td style="border:1px solid #dbeafe; padding:2px 4px;"></td>
            </tr>
            @endif
            @endforeach
            @endif
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:4px; font-size:9px; color:#6b7280;">
        *) Termasuk yang diimpor oleh importir umum atau pihak lain. &nbsp; **) Jika negara asal impor lebih dari satu, tuliskan negara dengan nilai impor terbesar.
    </div>
</div>
@else
    <div style="text-align:center; padding:6px; color:#6b7280; font-size:11px;">Belum ada data bahan baku untuk ditampilkan.</div>
@endif
