{{-- Blok V: Kondisi dan Prospek Usaha - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK V - Kondisi dan Prospek Usaha</h3>
                <p class="section-subtitle">Indikator kondisi dan prospek usaha per triwulan.</p>
            </div>

            <p style="padding: 0 1rem; color: #6b7280; font-size: 0.8125rem; font-style: italic;">Gunakan geser horizontal pada tabel.</p>
            <div class="table-responsive" style="padding: 0 1rem 1rem;">
                <table class="survey-table">
                    <thead>
                        <tr>
                            <th class="sticky-col">Komponen</th>
                            <th>Kondisi TW I-2025 vs TW IV-2024</th>
                            <th>Kondisi TW II-2025 vs TW I-2025</th>
                            <th>Kondisi TW III-2025 vs TW II-2025</th>
                            <th class="prospect">Prospek TW IV-2025 vs TW III-2025</th>
                            <th>Kondisi TW IV-2025 vs TW III-2025</th>
                            <th class="prospect">Prospek TW I-2026 vs TW IV-2025</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rows = [
                                ['key' => '501', 'label' => 'Pesanan', 'type' => 'normal', 'desc' => 'Jumlah pesanan barang produksi yang diterima perusahaan baik domestik dan ekspor'],
                                ['key' => '502', 'label' => 'Produksi', 'type' => 'normal', 'desc' => 'Jumlah produksi barang yang dihasilkan oleh perusahaan'],
                                ['key' => '503', 'label' => 'Kapasitas Produksi', 'type' => 'normal', 'desc' => 'Besaran keluaran (output produksi) maksimum yang mampu dihasilkan oleh mesin produksi utama'],
                                ['key' => '504', 'label' => 'Tenaga Kerja', 'type' => 'normal', 'desc' => 'Rata-rata jumlah tenaga kerja'],
                                ['key' => '505', 'label' => 'Jam Kerja', 'type' => 'normal', 'desc' => 'Rata-rata jam kerja per hari'],
                                ['key' => '506', 'label' => 'Waktu Pengiriman Pemasok', 'type' => 'delivery', 'desc' => 'Waktu pengiriman bahan baku dari pemasok'],
                                ['key' => '507', 'label' => 'Persediaan Bahan Baku', 'type' => 'normal', 'desc' => 'Jumlah persediaan bahan baku yang disimpan perusahaan'],
                            ];
                            $periods = ['p1','p2','p3','p4','p5','p6'];
                            $labelsNormal = [
                                ['value'=>'naik','text'=>'Naik'],
                                ['value'=>'tetap','text'=>'Tetap'],
                                ['value'=>'turun','text'=>'Turun']
                            ];
                            $labelsDelivery = [
                                ['value'=>'lebih_cepat','text'=>'Lebih cepat'],
                                ['value'=>'tetap','text'=>'Tetap'],
                                ['value'=>'lebih_lambat','text'=>'Lebih lambat']
                            ];
                            $data = $surveyResponse->blok5_data ?? [];
                        @endphp

                        @foreach($rows as $row)
                            <tr>
                                <td class="row-label sticky-col">
                                    <span class="question-number">{{ $row['key'] }}.</span>
                                    <span>{{ $row['label'] }}</span>
                                    <small class="component-desc">{{ $row['desc'] }}</small>
                                </td>
                                @foreach($periods as $index => $period)
                                    @php $isProspect = in_array($index, [3,5]); @endphp
                                    <td class="{{ $isProspect ? 'prospect-col' : '' }}">
                                        <div class="radio-group">
                                            @foreach(($row['type']==='delivery' ? $labelsDelivery : $labelsNormal) as $opt)
                                                @php
                                                    $name = "blok5_view[{$row['key']}][$period]";
                                                    $checked = isset($data[$row['key']][$period]) && $data[$row['key']][$period] === $opt['value'];
                                                @endphp
                                                <label class="radio-pill">
                                                    <input type="radio" name="{{ $name }}" value="{{ $opt['value'] }}" {{ $checked ? 'checked' : '' }} disabled>
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
