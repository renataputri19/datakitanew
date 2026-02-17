{{-- Blok IIIA: Kondisi Perekonomian - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK IIIA - KONDISI PEREKONOMIAN (PELAKU USAHA)</h3>
                <p class="section-subtitle">Barang-barang yang diproduksi dan pendapatan perusahaan per bulan</p>
            </div>

            {{-- Data & Preview Setup --}}
            @php
                $products = $surveyResponse->blok3a_products ?? [];
                $lainnya = $surveyResponse->blok3a_lainnya ?? [];
                $totals = $surveyResponse->blok3a_totals ?? [];
                // Match keys used in survey-blok3a.js
                $months = ['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'];
                $monthLabels = ['Des 2024', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            @endphp

            <!-- Excel-Style Preview (Read-Only) -->
            <div class="special-section" id="preview-section">
                <h3 class="special-title">Pratinjau Excel (Ringkasan Baca-Saja)</h3>
                <div id="blok3a-preview-table">
                    @if(count($products) > 0 || !empty($lainnya) || !empty($totals))
                    <div class="table-responsive" style="overflow-x:auto; padding: 0.5rem 0;">
                        <table class="preview-table-el" style="width:100%; border-collapse: collapse; min-width: 980px;">
                            <thead>
                                <tr>
                                    <th class="sticky-col" style="text-align:left; background:#f9fafb; border:1px solid #e5e7eb;">Kode/Nama</th>
                                    <th style="background:#f9fafb; border:1px solid #e5e7eb;">Uraian</th>
                                    @foreach($monthLabels as $ml)
                                        <th style="text-align:center; background:#f9fafb; border:1px solid #e5e7eb;">{{ $ml }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $i => $p)
                                    @php $productName = $p['jenis_barang'] ?? ($p['name'] ?? ('Produk ' . ($i+1))); @endphp
                                    <tr>
                                        <td class="sticky-col" rowspan="3" style="border:1px solid #e5e7eb;">
                                            <div class="code">{{ '301.'.($i+1) }}</div>
                                            <div class="name">{{ $productName }}</div>
                                        </td>
                                        <td style="border:1px solid #e5e7eb;">Banyaknya</td>
                                        @foreach($months as $m)
                                            <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                                {{ isset($p['banyaknya'][$m]) && $p['banyaknya'][$m] !== null ? number_format((float)$p['banyaknya'][$m], 0, ',', '.') : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #e5e7eb;">Nilai (Jutaan Rp)</td>
                                        @foreach($months as $m)
                                            <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                                {{ isset($p['nilai'][$m]) && $p['nilai'][$m] !== null ? number_format((float)$p['nilai'][$m], 0, ',', '.') : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #e5e7eb;">Harga/Satuan (Ribu Rp)</td>
                                        @foreach($months as $m)
                                            <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                                {{ isset($p['harga_satuan'][$m]) && $p['harga_satuan'][$m] !== null ? number_format((float)$p['harga_satuan'][$m], 0, ',', '.') : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach

                                @if(!empty($lainnya))
                                    <tr style="background:#fefce8;">
                                        <td class="sticky-col" style="border:1px solid #e5e7eb;">
                                            <div class="code">302.</div>
                                            <div class="name">Lainnya</div>
                                        </td>
                                        <td style="border:1px solid #e5e7eb;">Nilai</td>
                                        @foreach($months as $m)
                                            <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                                {{ isset($lainnya['nilai'][$m]) && $lainnya['nilai'][$m] !== null ? number_format((float)$lainnya['nilai'][$m], 0, ',', '.') : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif

                                @if(!empty($totals))
                                    <tr class="total-row" style="background:#f0fdf4; font-weight:600;">
                                        <td class="sticky-col" style="border:1px solid #e5e7eb;">
                                            <div class="code">303.</div>
                                            <div class="name">Total</div>
                                        </td>
                                        <td style="border:1px solid #e5e7eb;">Nilai</td>
                                        @foreach($months as $m)
                                            <td class="num" style="text-align:right; border:1px solid #e5e7eb;">
                                                {{ isset($totals[$m]) && $totals[$m] !== null ? number_format((float)$totals[$m], 0, ',', '.') : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div style="text-align:center; padding: 1rem; color:#6b7280;">Belum ada data untuk ditampilkan.</div>
                    @endif
                </div>
                <div class="mt-3 text-sm text-gray-600" style="color:#6b7280;">
                    Ringkasan ini tidak dapat diedit. Untuk mengubah, silakan isi di form survei.
                </div>
            </div>
        </div>
    </form>
</div>
