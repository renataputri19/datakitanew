{{-- Blok IIIA: Kondisi Perekonomian - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK IIIA - KONDISI PEREKONOMIAN (PELAKU USAHA)</h3>
                <p class="section-subtitle">Barang-barang yang diproduksi dan pendapatan perusahaan per bulan</p>
            </div>

            {{-- Display Products Table Data --}}
            @php
                $products = $surveyResponse->blok3a_products ?? [];
                $lainnya = $surveyResponse->blok3a_lainnya ?? [];
                $totals = $surveyResponse->blok3a_totals ?? [];
                // Match keys used in survey-blok3a.js
                $months = ['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'];
                $monthLabels = ['Des 2024', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            @endphp

            @if(count($products) > 0 || !empty($lainnya) || !empty($totals))
            <div class="table-responsive" style="overflow-x: auto; padding: 1rem;">
                <table class="products-table" style="width: 100%; border-collapse: collapse; min-width: 1200px;">
                    <thead>
                        <tr>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; background: #f3f4f6; text-align: center; min-width: 40px;">No.</th>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; background: #f3f4f6; text-align: left; min-width: 150px;">Jenis Barang</th>
                            <th style="border: 1px solid #e5e7eb; padding: 8px; background: #f3f4f6; text-align: center; min-width: 60px;">Uraian</th>
                            @foreach($monthLabels as $ml)
                            <th style="border: 1px solid #e5e7eb; padding: 8px; background: #f3f4f6; text-align: center; min-width: 90px;">{{ $ml }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $index => $product)
                        @php
                            // Check for 'jenis_barang' (JS name) or 'name' (potential old name)
                            $productName = $product['jenis_barang'] ?? ($product['name'] ?? 'Produk ' . ($index + 1));
                        @endphp
                        {{-- Quantity row --}}
                        <tr>
                            <td rowspan="2" style="border: 1px solid #e5e7eb; padding: 8px; text-align: center; vertical-align: middle;">{{ $index + 1 }}</td>
                            <td rowspan="2" style="border: 1px solid #e5e7eb; padding: 8px; vertical-align: middle;">{{ $productName }}</td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: center; font-size: 0.8125rem;">Qty</td>
                            @foreach($months as $m)
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right; font-size: 0.875rem;">
                                {{-- Access 'banyaknya' instead of 'qty' --}}
                                {{ isset($product['banyaknya'][$m]) && $product['banyaknya'][$m] !== null ? number_format((float)$product['banyaknya'][$m], 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                        </tr>
                        {{-- Value row --}}
                        <tr>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: center; font-size: 0.8125rem;">Nilai</td>
                            @foreach($months as $m)
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right; font-size: 0.875rem;">
                                {{-- Access 'nilai' instead of 'value' --}}
                                {{ isset($product['nilai'][$m]) && $product['nilai'][$m] !== null ? number_format((float)$product['nilai'][$m], 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                        </tr>
                        @endforeach

                        {{-- Lainnya row --}}
                        @if(!empty($lainnya))
                        <tr style="background: #fefce8;">
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: center;">302.</td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px;"><strong>Lainnya*)</strong></td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: center; font-size: 0.8125rem;">Nilai</td>
                            @foreach($months as $m)
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right; font-size: 0.875rem;">
                                {{-- Check 'nilai' key within lainnya, or direct access depending on structure.
                                   JS: name="blok3a_lainnya[nilai][${month}]" -> structure: lainnya: { nilai: { month: val } }
                                --}}
                                {{ isset($lainnya['nilai'][$m]) && $lainnya['nilai'][$m] !== null ? number_format((float)$lainnya['nilai'][$m], 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                        </tr>
                        @endif

                        {{-- Total row --}}
                        @if(!empty($totals))
                        <tr style="background: #eff6ff; font-weight: 600;">
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: center;">303.</td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px;"><strong>Total</strong></td>
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: center; font-size: 0.8125rem;">Nilai</td>
                            @foreach($months as $m)
                            <td style="border: 1px solid #e5e7eb; padding: 8px; text-align: right; font-size: 0.875rem;">
                                {{ isset($totals[$m]) && $totals[$m] !== null ? number_format((float)$totals[$m], 0, ',', '.') : '-' }}
                            </td>
                            @endforeach
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div style="padding: 0 1rem 1rem;">
                <p style="font-size: 0.8125rem; color: #6b7280; font-style: italic;">
                    <strong>*) Yang termasuk dalam lainnya atau pendapatan lainnya dalam R302 antara lain keuntungan/kerugian dari penjualan barang yang sama, menyewakan gedung/ruangan/tempat, menyewakan gudang, menyewakan kendaraan/mesin/dan peralatan (tanpa operator), pendapatan dari ongkos kirim barang, penjualan energi sampingan (listrik, steam, gas), jasa pengemasan, dan jasa perbaikan kecil</strong>
                </p>
            </div>
            @else
            <div style="text-align: center; padding: 3rem 2rem; color: #6b7280;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 1rem; color: #d1d5db;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p style="font-size: 0.9375rem;">Belum ada data produksi untuk Blok IIIA</p>
            </div>
            @endif
        </div>
    </form>
</div>
