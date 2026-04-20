{{-- Blok V: Kondisi dan Prospek Usaha - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK V - Kondisi dan Prospek Usaha</h3>
                <p class="section-subtitle">Indikator kondisi dan prospek usaha per triwulan.</p>
            </div>

            @php
                $tw5        = (int)($surveyResponse->triwulan ?? 0);
                $tahun5     = (int)($surveyResponse->tahun ?? 2025);
                $isTw5      = $tw5 > 0;
                $twLabels5  = ['I','II','III','IV'];

                if ($isTw5) {
                    $prevTw5   = $tw5 === 1 ? 4 : $tw5 - 1;
                    $prevYear5 = $tw5 === 1 ? $tahun5 - 1 : $tahun5;
                    $nextTw5   = $tw5 === 4 ? 1 : $tw5 + 1;
                    $nextYear5 = $tw5 === 4 ? $tahun5 + 1 : $tahun5;
                    $twKondisiHeader5 = "Kondisi TW {$twLabels5[$tw5-1]}-{$tahun5} vs TW {$twLabels5[$prevTw5-1]}-{$prevYear5}";
                    $twProspekHeader5 = "Prospek TW {$twLabels5[$nextTw5-1]}-{$nextYear5} vs TW {$twLabels5[$tw5-1]}-{$tahun5}";
                }

                $rows5 = [
                    ['key' => '501', 'label' => 'Pesanan',                  'type' => 'normal',   'desc' => 'Jumlah pesanan barang produksi yang diterima perusahaan baik domestik dan ekspor'],
                    ['key' => '502', 'label' => 'Produksi',                 'type' => 'normal',   'desc' => 'Jumlah produksi barang yang dihasilkan oleh perusahaan'],
                    ['key' => '503', 'label' => 'Kapasitas Produksi',       'type' => 'normal',   'desc' => 'Besaran keluaran (output produksi) maksimum yang mampu dihasilkan oleh mesin produksi utama'],
                    ['key' => '504', 'label' => 'Tenaga Kerja',             'type' => 'normal',   'desc' => 'Rata-rata jumlah tenaga kerja'],
                    ['key' => '505', 'label' => 'Jam Kerja',                'type' => 'normal',   'desc' => 'Rata-rata jam kerja per hari'],
                    ['key' => '506', 'label' => 'Waktu Pengiriman Pemasok', 'type' => 'delivery', 'desc' => 'Waktu pengiriman bahan baku dari pemasok'],
                    ['key' => '507', 'label' => 'Persediaan Bahan Baku',    'type' => 'normal',   'desc' => 'Jumlah persediaan bahan baku yang disimpan perusahaan'],
                ];
                $periods5        = $isTw5 ? ['p1','p2'] : ['p1','p2','p3','p4','p5','p6'];
                $prospectIdx5    = $isTw5 ? [1]         : [3, 5];
                $labelsNormal5   = [['value'=>'naik','text'=>'Naik'],['value'=>'tetap','text'=>'Tetap'],['value'=>'turun','text'=>'Turun']];
                $labelsDelivery5 = [['value'=>'lebih_cepat','text'=>'Lebih cepat'],['value'=>'tetap','text'=>'Tetap'],['value'=>'lebih_lambat','text'=>'Lebih lambat']];
                $data5 = $surveyResponse->blok5_data ?? [];
            @endphp

            <p style="padding: 0 1rem; color: #6b7280; font-size: 0.8125rem; font-style: italic;">
                {{ $isTw5 ? 'Kondisi triwulan ini dan prospek triwulan berikutnya.' : 'Gunakan geser horizontal pada tabel.' }}
            </p>
            <div class="table-responsive" style="padding: 0 1rem 1rem;">
                <table class="survey-table">
                    <thead>
                        @if($isTw5)
                        <tr>
                            <th class="sticky-col">Komponen</th>
                            <th>{{ $twKondisiHeader5 }}</th>
                            <th class="prospect">{{ $twProspekHeader5 }}</th>
                        </tr>
                        <tr>
                            <th class="sticky-col"></th>
                            <th class="col-subtype">Kondisi</th>
                            <th class="col-subtype prospect">Prospek</th>
                        </tr>
                        @else
                        <tr>
                            <th class="sticky-col">Komponen</th>
                            <th>Kondisi TW I-{{ $tahun5 }} vs TW IV-{{ $tahun5 - 1 }}</th>
                            <th>Kondisi TW II-{{ $tahun5 }} vs TW I-{{ $tahun5 }}</th>
                            <th>Kondisi TW III-{{ $tahun5 }} vs TW II-{{ $tahun5 }}</th>
                            <th class="prospect">Prospek TW IV-{{ $tahun5 }} vs TW III-{{ $tahun5 }}</th>
                            <th>Kondisi TW IV-{{ $tahun5 }} vs TW III-{{ $tahun5 }}</th>
                            <th class="prospect">Prospek TW I-{{ $tahun5 + 1 }} vs TW IV-{{ $tahun5 }}</th>
                        </tr>
                        <tr>
                            <th class="sticky-col"></th>
                            <th class="col-subtype">Triwulan</th>
                            <th class="col-subtype">Triwulan</th>
                            <th class="col-subtype">Triwulan</th>
                            <th class="col-subtype prospect">Prospek</th>
                            <th class="col-subtype">Triwulan</th>
                            <th class="col-subtype prospect">Prospek</th>
                        </tr>
                        @endif
                    </thead>
                    <tbody>
                        @foreach($rows5 as $row)
                            <tr>
                                <td class="row-label sticky-col">
                                    <span class="question-number">{{ $row['key'] }}.</span>
                                    <span>{{ $row['label'] }}</span>
                                    <small class="component-desc">{{ $row['desc'] }}</small>
                                </td>
                                @foreach($periods5 as $index => $pKey)
                                    @php $isProspect = in_array($index, $prospectIdx5); @endphp
                                    <td class="{{ $isProspect ? 'prospect-col' : '' }}">
                                        <div class="radio-group">
                                            @foreach(($row['type']==='delivery' ? $labelsDelivery5 : $labelsNormal5) as $opt)
                                                @php $checked = isset($data5[$row['key']][$pKey]) && $data5[$row['key']][$pKey] === $opt['value']; @endphp
                                                <label class="radio-pill">
                                                    <input type="radio" name="blok5_view[{{ $row['key'] }}][{{ $pKey }}]" value="{{ $opt['value'] }}" {{ $checked ? 'checked' : '' }} disabled>
                                                    <span>{{ $opt['text'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>
