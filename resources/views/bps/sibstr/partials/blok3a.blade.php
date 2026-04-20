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
                $lainnya  = $surveyResponse->blok3a_lainnya ?? [];
                $totals   = $surveyResponse->blok3a_totals ?? [];
                $pl       = $surveyResponse->blok3a_pendapatan_lainnya ?? [];
                $tahun3a  = (int)($surveyResponse->tahun ?? 2025);
                $tw3a     = (int)($surveyResponse->triwulan ?? 0);
                $isTw3a   = $tw3a > 0;
                $prevYear = $tahun3a - 1;

                // Build month keys exactly like getBlok3aMonthKeys() in the model
                $twMonthMap3a = [
                    1 => ['jan','feb','mar'], 2 => ['apr','mei','jun'],
                    3 => ['jul','agu','sep'], 4 => ['okt','nov','des'],
                ];
                if ($isTw3a) {
                    $months = ["{$prevYear}_des"];
                    foreach (($twMonthMap3a[$tw3a] ?? []) as $_m) { $months[] = "{$tahun3a}_{$_m}"; }
                } else {
                    $allM   = ['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'];
                    $months = ["{$prevYear}_des"];
                    foreach ($allM as $_m) { $months[] = "{$tahun3a}_{$_m}"; }
                }

                $mlMap = ['jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei',
                          'jun'=>'Jun','jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt','nov'=>'Nov','des'=>'Des'];
                $monthLabels = [];
                foreach ($months as $_mk) {
                    preg_match('/^(\d{4})_(\w+)$/', $_mk, $_mm);
                    $_mo = $mlMap[$_mm[2] ?? ''] ?? strtoupper($_mm[2] ?? $_mk);
                    // Always show year for Dec baseline and for all triwulanan columns
                    $monthLabels[] = ($_mm[2] === 'des' || $isTw3a) ? $_mo.' '.($_mm[1] ?? '') : $_mo;
                }

                if (!function_exists('_bps3a_nf')) {
                    function _bps3a_nf($v) { return ($v !== null && $v !== '') ? number_format((float)$v, 0, ',', '.') : '-'; }
                    function _bps3a_pf($v) { return ($v !== null && $v !== '') ? number_format((float)$v, 2, ',', '.') : '-'; }
                }
            @endphp

            {{-- ── Table 3a: Ringkasan Data Produksi ── --}}
            <div class="special-section" id="preview-section" style="margin-bottom:1.5rem;">
                <h3 class="special-title">
                    Ringkasan Data Produksi
                    <span style="font-size:0.75rem;font-weight:400;color:#6b7280;margin-left:0.5rem;">(Pratinjau - diperbarui secara otomatis)</span>
                </h3>
                <div id="blok3a-preview-table">
                    @if(count($products) > 0 || !empty($lainnya) || !empty($totals))
                    <div class="table-responsive" style="overflow-x:auto;padding:0.5rem 0;">
                        <table class="preview-table-el" style="width:100%;border-collapse:collapse;min-width:{{ $isTw3a ? '600px' : '1100px' }};">
                            <thead>
                                <tr>
                                    <th class="sticky-col" style="text-align:left;background:#f9fafb;border:1px solid #e5e7eb;min-width:120px;">Kode/Nama</th>
                                    @if(!$isTw3a)
                                    <th style="text-align:left;background:#f9fafb;border:1px solid #e5e7eb;min-width:160px;">Detail Produk</th>
                                    @endif
                                    <th style="background:#f9fafb;border:1px solid #e5e7eb;min-width:90px;">Uraian</th>
                                    @foreach($monthLabels as $ml)
                                        <th style="text-align:center;background:#f9fafb;border:1px solid #e5e7eb;min-width:72px;">{{ $ml }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $i => $p)
                                    @php $productName = $p['jenis_barang'] ?? ($p['name'] ?? ('Produk '.($i+1))); @endphp
                                    <tr>
                                        <td class="sticky-col" rowspan="3" style="border:1px solid #e5e7eb;vertical-align:top;padding:0.5rem;">
                                            <div class="code" style="font-weight:700;font-size:0.8125rem;">{{ '301.'.($i+1) }}</div>
                                            <div class="name" style="font-size:0.875rem;">{{ $productName }}</div>
                                        </td>
                                        @if(!$isTw3a)
                                        <td rowspan="3" style="border:1px solid #e5e7eb;vertical-align:top;padding:0.5rem;font-size:0.8125rem;line-height:1.6;">
                                            <div><span style="font-weight:600;color:#374151;">KBLI 5 Digit:</span><br>{{ !empty($p['kbli_5digit']) ? e($p['kbli_5digit']) : '-' }}</div>
                                            <div style="margin-top:0.3rem;"><span style="font-weight:600;color:#374151;">% Ekspor (*):</span><br>{{ (isset($p['persen_ekspor']) && $p['persen_ekspor'] !== '') ? number_format((float)$p['persen_ekspor'],2,',','.').' %' : '-' }}</div>
                                            <div style="margin-top:0.3rem;"><span style="font-weight:600;color:#374151;">Negara Ekspor (**):</span><br>{{ !empty($p['negara_ekspor']) ? e($p['negara_ekspor']) : '-' }}</div>
                                        </td>
                                        @endif
                                        <td style="border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">Banyaknya</td>
                                        @foreach($months as $m)
                                            @php
                                                $qty = $p['banyaknya'][$m] ?? null;
                                                $unit = $p['satuan'] ?? '';
                                                $qtyText = ($qty !== null) ? number_format((float)$qty, 0, ',', '.') : null;
                                            @endphp
                                            <td style="text-align:right;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">
                                                {{ $qtyText !== null ? ($qtyText.($unit ? ' '.e($unit) : '')) : '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">Nilai (Rp)</td>
                                        @foreach($months as $m)
                                            <td style="text-align:right;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ _bps3a_nf($p['nilai'][$m] ?? null) }}</td>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        <td style="border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">Harga/Satuan</td>
                                        @foreach($months as $m)
                                            @php
                                                $qty   = $p['banyaknya'][$m] ?? null;
                                                $nilai = $p['nilai'][$m] ?? null;
                                                $comp  = ($qty !== null && (float)$qty > 0 && $nilai !== null) ? ((float)$nilai / (float)$qty) : null;
                                                $price = $comp ?? ($p['harga_satuan'][$m] ?? null);
                                            @endphp
                                            <td style="text-align:right;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ _bps3a_pf($price) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach

                                @if($isTw3a)
                                    <tr>
                                        <td class="sticky-col" style="border:1px solid #e5e7eb;vertical-align:middle;padding:0.5rem;">
                                            <div class="code" style="font-weight:700;font-size:0.8125rem;">302.</div>
                                            <div class="name" style="font-size:0.875rem;">Lainnya</div>
                                        </td>
                                        <td style="border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">Nilai (Rp)</td>
                                        @foreach($months as $m)
                                            <td style="text-align:right;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ _bps3a_nf($lainnya['nilai'][$m] ?? null) }}</td>
                                        @endforeach
                                    </tr>
                                @endif

                                @if(!empty($totals))
                                    <tr style="background:#f0fdf4;font-weight:600;">
                                        <td class="sticky-col" style="border:1px solid #e5e7eb;padding:0.5rem;" colspan="{{ $isTw3a ? 1 : 2 }}">
                                            <div class="code" style="font-weight:700;">303.</div>
                                            <div class="name">Total</div>
                                        </td>
                                        <td style="border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">Nilai (Rp)</td>
                                        @foreach($months as $m)
                                            <td style="text-align:right;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ _bps3a_nf($totals[$m] ?? null) }}</td>
                                        @endforeach
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @else
                        <div style="text-align:center;padding:1rem;color:#6b7280;">Belum ada data produk untuk ditampilkan.</div>
                    @endif
                </div>
                <div style="margin-top:0.5rem;font-size:0.75rem;color:#9ca3af;">
                    (*) Termasuk yang diekspor oleh eksportir umum atau pihak lain. &nbsp;(**) Jika negara tujuan ekspor lebih dari satu, tuliskan yang terbesar.
                </div>
            </div>

            {{-- ── Q302: Pendapatan Lainnya (tahunan only) ── --}}
            @if(!$isTw3a && (!empty($pl) || !empty($surveyResponse->blok3a_q305a_maklun_nilai) || !empty($surveyResponse->blok3a_q305_online)))
            <div class="form-section" style="margin-top:1rem;">
                <div class="section-header">
                    <h3 class="section-title">PENDAPATAN LAINNYA & JASA INDUSTRI (TAHUN 2025)</h3>
                </div>
                <div class="form-grid">
                    @if(!empty($pl))
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">302.</span> Pendapatan lainnya selama tahun 2025 (Rp)</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Keuntungan/kerugian penjualan barang tanpa proses</label><input type="text" value="{{ _bps3a_nf($pl['q302a'] ?? null) }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Penjualan kekayaan intelektual</label><input type="text" value="{{ _bps3a_nf($pl['q302b'] ?? null) }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Nilai jasa tidak berkaitan proses produksi</label><input type="text" value="{{ _bps3a_nf($pl['q302c'] ?? null) }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Tenaga listrik yang dijual</label><input type="text" value="{{ _bps3a_nf($pl['q302d'] ?? null) }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">e. Pendapatan non operasional</label><input type="text" value="{{ _bps3a_nf($pl['q302e'] ?? null) }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">f. Lainnya</label><input type="text" value="{{ _bps3a_nf($pl['q302f'] ?? null) }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    @endif

                    <div class="form-row">
                        <label class="form-label"><span class="question-number">305.</span> Pendapatan dari jasa industri (maklun) tahun 2025</label>
                        <div class="form-subgrid">
                            <div class="form-subrow">
                                <label class="form-sublabel">a. Nilai pendapatan maklun (Rp)</label>
                                <input type="text" value="{{ _bps3a_nf($surveyResponse->blok3a_q305a_maklun_nilai ?? null) }}" class="form-control" readonly disabled>
                            </div>
                            <div class="form-subrow">
                                <label class="form-sublabel">b. % dari luar negeri</label>
                                <input type="text" value="{{ ($surveyResponse->blok3a_q305b_maklun_pct ?? '') !== '' ? $surveyResponse->blok3a_q305b_maklun_pct.' %' : '-' }}" class="form-control" readonly disabled>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label"><span class="question-number">306.</span> Persentase pendapatan dari usaha online (%)</label>
                        <input type="text" value="{{ ($surveyResponse->blok3a_q305_online ?? '') !== '' ? $surveyResponse->blok3a_q305_online.' %' : '-' }}" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </form>
</div>
