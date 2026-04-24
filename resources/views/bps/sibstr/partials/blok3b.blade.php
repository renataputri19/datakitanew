{{-- Blok IIIB: Pendapatan & Pengeluaran - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        @php
            $dataIndustri    = $surveyResponse->blok3b_industri_data    ?? [];
            $dataNonIndustri = $surveyResponse->blok3b_nonindustri_data ?? [];
            $isTriwulanan    = (((int)($surveyResponse->triwulan ?? 0)) > 0);
            $tw              = (int)($surveyResponse->triwulan ?? 0);
            $tahunBps        = (int)($surveyResponse->tahun ?? 2025);

            if (!function_exists('formatCurrencyBps')) {
                function formatCurrencyBps($value) {
                    if ($value === null || $value === '') return '';
                    return number_format((float)$value, 0, ',', '.');
                }
            }

            $twLabels = ['satu','dua','tiga','empat'];
            $twLabel  = $isTriwulanan ? ($twLabels[$tw - 1] ?? 'satu') : '';
            $twAwal   = $isTriwulanan ? match($tw) {
                1 => "1 Januari {$tahunBps}", 2 => "1 April {$tahunBps}",
                3 => "1 Juli {$tahunBps}",    4 => "1 Oktober {$tahunBps}", default => ''
            } : '';
            $twAkhir  = $isTriwulanan ? match($tw) {
                1 => "31 Maret {$tahunBps}",     2 => "30 Juni {$tahunBps}",
                3 => "30 September {$tahunBps}", 4 => "31 Desember {$tahunBps}", default => ''
            } : '';

            $rangeLabels = [
                '1' => '1 s.d. Rp 500 juta',
                '2' => 'Lebih dari Rp 500 juta s.d. Rp 1 miliar',
                '3' => 'Lebih dari Rp 1 miliar s.d. Rp 5 miliar',
                '4' => 'Lebih dari Rp 5 miliar s.d. Rp 10 miliar',
                '5' => 'Lebih dari Rp 10 miliar'
            ];

            $renderIndustri    = isset($showIndustri)    ? (bool)$showIndustri    : true;
            $renderNonIndustri = isset($showNonIndustri) ? (bool)$showNonIndustri : true;
        @endphp

        {{-- ══════════════════════════════════════════════════════════
             INDUSTRI
             ══════════════════════════════════════════════════════════ --}}
        @if($renderIndustri)
        @php $data = $dataIndustri; @endphp

        <div style="padding: 1rem 1.5rem 0;">
            <span style="display:inline-block;padding:0.25rem 0.75rem;background:#dbeafe;color:#1e40af;border-radius:9999px;font-size:0.75rem;font-weight:600;">Industri</span>
        </div>

        @if($isTriwulanan)
        {{-- ── TRIWULANAN INDUSTRI ── --}}

        {{-- PENDAPATAN (Q304) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">PENDAPATAN PERUSAHAAN</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">304.</span> Pendapatan royalti, bunga, deviden dan lainnya yang diterima perusahaan pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q304'] ?? '') }}" class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        {{-- PERSEDIAAN (Q306–Q309) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">PERSEDIAAN (INVENTORI)</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">306.</span> Nilai Persediaan Bahan baku, bahan bakar, dan sebagainya pada triwulan {{ $twLabel }} (rupiah)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label><input type="text" value="{{ formatCurrencyBps($data['q306_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label><input type="text" value="{{ formatCurrencyBps($data['q306_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">307.</span> Nilai Persediaan Barang Dalam Proses pada triwulan {{ $twLabel }} (rupiah)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label><input type="text" value="{{ formatCurrencyBps($data['q307_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label><input type="text" value="{{ formatCurrencyBps($data['q307_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">308.</span> Nilai Persediaan Barang jadi (termasuk persediaan untuk dijual kembali) pada triwulan {{ $twLabel }} (rupiah)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label><input type="text" value="{{ formatCurrencyBps($data['q308_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label><input type="text" value="{{ formatCurrencyBps($data['q308_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">309.</span> Total persediaan pada triwulan {{ $twLabel }}</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label><input type="text" value="{{ formatCurrencyBps($data['q309_awal'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label><input type="text" value="{{ formatCurrencyBps($data['q309_akhir'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PENGELUARAN (Q310–Q313) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">310.</span> Total upah dan gaji, serta jaminan sosial pegawai pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q310'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">311.</span> Penambahan aset tetap (kecuali pembelian tanah) pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q311'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">312.</span> Biaya produksi (pemakaian bahan baku dan penolong) pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q312_tw'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">313.</span> Biaya operasional (air, listrik, gas, pemeliharaan, biaya angkutan) pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q313_tw'] ?? '') }}" class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        {{-- EKSPOR IMPOR (Q314–Q315) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">EKSPOR IMPOR LUAR NEGERI</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">314.</span> Persentase nilai produksi yang dijual sebagai produk ekspor luar negeri (%)</label>
                    <input type="text" value="{{ $data['q314_tw'] ?? '' }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">315.</span> Persentase nilai bahan baku dan bahan penolong yang diperoleh melalui impor luar negeri (%)</label>
                    <input type="text" value="{{ $data['q315_tw'] ?? '' }}" class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        @else
        {{-- ── TAHUNAN INDUSTRI ── --}}

        {{-- PERSEDIAAN (Q307–Q312) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">PERSEDIAAN (INVENTORI)</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">307.</span> Nilai stok bahan baku, bahan penolong, bahan bakar, bahan pembungkus, dan lain-lain</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">308.</span> Nilai stok barang produksi setengah jadi</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">309.</span> Nilai stok barang jadi yang dihasilkan</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">310.</span> Nilai pembelian/penambahan dan pembuatan/perbaikan besar seluruh barang modal tetap pada tahun {{ $tahunBps }}</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q310_beli_modal'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">311.</span> Nilai penjualan/pengurangan seluruh barang modal tetap pada tahun {{ $tahunBps }}</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q311_jual_modal'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">312.</span> Nilai taksiran seluruh barang modal tetap menurut harga berlaku per 31 Desember {{ $tahunBps }}</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q312_taksir_modal'] ?? '') }}" class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        {{-- PENGELUARAN (Q313–Q317) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">313.</span> Pengeluaran untuk pekerja/karyawan (tidak termasuk outsourcing)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a.1 Pekerja produksi: Upah/gaji, lembur, tunjangan</label><input type="text" value="{{ formatCurrencyBps($data['q313_a1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">a.2 Pekerja produksi: Pengeluaran lain</label><input type="text" value="{{ formatCurrencyBps($data['q313_a2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b.1 Pekerja lainnya: Upah/gaji, lembur, tunjangan</label><input type="text" value="{{ formatCurrencyBps($data['q313_b1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b.2 Pekerja lainnya: Pengeluaran lain</label><input type="text" value="{{ formatCurrencyBps($data['q313_b2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Total</label><input type="text" value="{{ formatCurrencyBps($data['q313_c'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">314.</span> Pengeluaran untuk pekerja/karyawan Outsourcing</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a.1 Pekerja produksi: Upah/gaji, lembur, tunjangan</label><input type="text" value="{{ formatCurrencyBps($data['q314_a1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">a.2 Pekerja produksi: Pengeluaran lain</label><input type="text" value="{{ formatCurrencyBps($data['q314_a2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b.1 Pekerja lainnya: Upah/gaji, lembur, tunjangan</label><input type="text" value="{{ formatCurrencyBps($data['q314_b1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b.2 Pekerja lainnya: Pengeluaran lain</label><input type="text" value="{{ formatCurrencyBps($data['q314_b2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Total</label><input type="text" value="{{ formatCurrencyBps($data['q314_c'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">315.</span> Penggunaan listrik yang dipakai oleh perusahaan</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Daya tersambung dari PLN (VA)</label><input type="text" value="{{ $data['q315_a'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Daya tersambung dari Non PLN (VA)</label><input type="text" value="{{ $data['q315_b'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Penggunaan listrik dari PLN (kWh)</label><input type="text" value="{{ $data['q315_c'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">d. Penggunaan listrik dari Non PLN (kWh)</label><input type="text" value="{{ $data['q315_d'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">e. Pengeluaran listrik (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q315_e'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">317.</span> Pengeluaran perusahaan selama tahun {{ $tahunBps }}</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a.1 Sewa/kontrak gedung, mesin, alat-alat (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_c1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">a.2 Sewa/kontrak tanah (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_c2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Pajak/Tax (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_d'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Nilai bunga atas pinjaman (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_e'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">d. Nilai hadiah, sumbangan, derma (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_f'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">e. Nilai dividen/laba yang dibagikan (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_g'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">f. Nilai premi asuransi kerugian (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_h'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">g. Nilai jasa industri/maklun (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_i'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">h. Air (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_j'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">i. Pengeluaran lainnya (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_k'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>

                {{-- Q318: Moda Transportasi --}}
                <div class="form-row">
                    <label class="form-label"><span class="question-number">318.</span> Jenis moda transportasi yang digunakan untuk pengangkutan barang selama tahun {{ $tahunBps }}</label>
                    <div style="overflow-x:auto;margin-top:0.75rem;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;border:1px solid #d1d5db;">
                            <thead>
                                <tr style="background-color:#f9fafb;">
                                    <th style="padding:0.625rem 0.75rem;text-align:left;border:1px solid #d1d5db;font-weight:600;color:#374151;width:45%;">
                                        Jenis Angkutan<br><span style="font-weight:400;color:#6b7280;font-size:0.8rem;">(1)</span>
                                    </th>
                                    <th style="padding:0.625rem 0.75rem;text-align:center;border:1px solid #d1d5db;font-weight:600;color:#374151;width:27.5%;">
                                        Frekuensi Penggunaan Angkutan (kali)<br><span style="font-weight:400;color:#6b7280;font-size:0.8rem;">(2)</span>
                                    </th>
                                    <th style="padding:0.625rem 0.75rem;text-align:center;border:1px solid #d1d5db;font-weight:600;color:#374151;width:27.5%;">
                                        Total Biaya Pengangkutan (Rp)<br><span style="font-weight:400;color:#6b7280;font-size:0.8rem;">(3)</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                    'a' => ['label' => 'a. Angkutan jalan',                                  'note' => 'Contoh: truk, pick up, mobil, dan sepeda motor'],
                                    'b' => ['label' => 'b. Angkutan kereta api',                             'note' => ''],
                                    'c' => ['label' => 'c. Angkutan air sungai, danau, dan penyeberangan',   'note' => 'Contoh: kapal ponton, getek, kapal ferry'],
                                    'd' => ['label' => 'd. Angkutan air laut',                               'note' => 'Contoh: kapal laut, tol laut, dll'],
                                    'e' => ['label' => 'e. Angkutan udara',                                  'note' => 'Contoh: pesawat dan helikopter'],
                                ] as $key => $row)
                                <tr>
                                    <td style="padding:0.625rem 0.75rem;border:1px solid #d1d5db;vertical-align:middle;">
                                        <strong>{{ $row['label'] }}</strong>
                                        @if($row['note'])<br><span style="font-size:0.8rem;color:#6b7280;font-style:italic;">{{ $row['note'] }}</span>@endif
                                    </td>
                                    <td style="padding:0.5rem;border:1px solid #d1d5db;vertical-align:middle;">
                                        <input type="text" value="{{ $data['q318'.$key.'_freq'] ?? '' }}" class="form-control" readonly disabled style="text-align:right;">
                                    </td>
                                    <td style="padding:0.5rem;border:1px solid #d1d5db;vertical-align:middle;">
                                        <input type="text" value="{{ formatCurrencyBps($data['q318'.$key.'_biaya'] ?? '') }}" class="form-control" readonly disabled style="text-align:right;">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Q319: Persentase Pihak Ketiga --}}
                <div class="form-row">
                    <label class="form-label"><span class="question-number">319.</span> Persentase moda angkutan yang menggunakan jasa pihak ketiga (%)</label>
                    <input type="text" value="{{ $data['q319_persen_pihak_ketiga'] ?? '' }}" class="form-control" readonly disabled>
                </div>
            </div>
        </div>
        @endif {{-- end industri triwulanan/tahunan --}}
        @endif {{-- end renderIndustri --}}

        {{-- ══════════════════════════════════════════════════════════
             NON-INDUSTRI
             ══════════════════════════════════════════════════════════ --}}
        @if($renderNonIndustri)
        @php $data = $dataNonIndustri; @endphp

        <div style="padding: 1rem 1.5rem 0;">
            <span style="display:inline-block;padding:0.25rem 0.75rem;background:#d1fae5;color:#065f46;border-radius:9999px;font-size:0.75rem;font-weight:600;">Non-Industri</span>
        </div>

        @if($isTriwulanan)
        {{-- ── TRIWULANAN NON-INDUSTRI ── --}}

        {{-- PENDAPATAN (Q303–Q305) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">PENDAPATAN PERUSAHAAN</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">303.</span> Nilai pendapatan dari penjualan barang dan jasa perusahaan pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q303'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">304.</span> Pendapatan royalti, bunga, deviden dan lainnya yang diterima perusahaan pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q304'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">305.</span> Total pendapatan pada triwulan {{ $twLabel }}</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q305'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef">
                </div>
            </div>
        </div>

        {{-- PERSEDIAAN (Q306–Q309) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">PERSEDIAAN (INVENTORI)</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">306.</span> Nilai Persediaan Bahan baku, bahan bakar, dan sebagainya pada triwulan {{ $twLabel }} (rupiah)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label><input type="text" value="{{ formatCurrencyBps($data['q306_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label><input type="text" value="{{ formatCurrencyBps($data['q306_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">307.</span> Nilai Persediaan Barang Dalam Proses pada triwulan {{ $twLabel }} (rupiah)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label><input type="text" value="{{ formatCurrencyBps($data['q307_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label><input type="text" value="{{ formatCurrencyBps($data['q307_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">308.</span> Nilai Persediaan Barang jadi (termasuk persediaan untuk dijual kembali) pada triwulan {{ $twLabel }} (rupiah)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label><input type="text" value="{{ formatCurrencyBps($data['q308_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label><input type="text" value="{{ formatCurrencyBps($data['q308_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">309.</span> Total persediaan pada triwulan {{ $twLabel }}</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Persediaan Awal Periode ({{ $twAwal }})</label><input type="text" value="{{ formatCurrencyBps($data['q309_awal'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Persediaan Akhir Periode ({{ $twAkhir }})</label><input type="text" value="{{ formatCurrencyBps($data['q309_akhir'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PENGELUARAN (Q310–Q313) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">310.</span> Total upah dan gaji, serta jaminan sosial pegawai pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q310_tw'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">311.</span> Penambahan aset tetap (kecuali pembelian tanah) pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q311_tw'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">312.</span> Biaya produksi (pemakaian bahan baku dan penolong) pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q312_tw'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">313.</span> Biaya operasional (air, listrik, gas, pemeliharaan, biaya angkutan) pada triwulan {{ $twLabel }} (rupiah)</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q313_tw'] ?? '') }}" class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        {{-- EKSPOR IMPOR (Q314–Q315) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">EKSPOR IMPOR LUAR NEGERI</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">314.</span> Persentase nilai produksi yang dijual sebagai produk ekspor luar negeri (%)</label>
                    <input type="text" value="{{ $data['q314_tw'] ?? '' }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">315.</span> Persentase nilai bahan baku dan bahan penolong yang diperoleh melalui impor luar negeri (%)</label>
                    <input type="text" value="{{ $data['q315_tw'] ?? '' }}" class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        @else
        {{-- ── TAHUNAN NON-INDUSTRI ── --}}

        {{-- PENDAPATAN (Q303–Q305) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">PENDAPATAN PERUSAHAAN (NON-INDUSTRI)</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">303.</span> Nilai pendapatan dari penjualan barang dan jasa</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q303'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Selama tahun {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q303_year'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">305.</span> Total Pendapatan</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q305'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Selama tahun {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q305_year'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PERSEDIAAN (Q307–Q312) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">PERSEDIAAN (INVENTORI)</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">307.</span> Nilai stok bahan baku, bahan penolong, bahan bakar, bahan pembungkus, dan lain-lain</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">308.</span> Nilai stok barang produksi setengah jadi</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">309.</span> Nilai stok barang jadi yang dihasilkan</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember {{ $tahunBps }}</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">310.</span> Nilai pembelian/penambahan dan pembuatan/perbaikan besar seluruh barang modal tetap pada tahun {{ $tahunBps }}</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q310_beli_modal'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">311.</span> Nilai penjualan/pengurangan seluruh barang modal tetap pada tahun {{ $tahunBps }}</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q311_jual_modal'] ?? '') }}" class="form-control" readonly disabled>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">312.</span> Nilai taksiran seluruh barang modal tetap menurut harga berlaku per 31 Desember {{ $tahunBps }}</label>
                    <input type="text" value="{{ formatCurrencyBps($data['q312_taksir_modal'] ?? '') }}" class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        {{-- PENGELUARAN (Q313–Q317) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">313.</span> Pengeluaran untuk pekerja/karyawan (tidak termasuk outsourcing)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a.1 Pekerja produksi: Upah/gaji, lembur, tunjangan</label><input type="text" value="{{ formatCurrencyBps($data['q313_a1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">a.2 Pekerja produksi: Pengeluaran lain</label><input type="text" value="{{ formatCurrencyBps($data['q313_a2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b.1 Pekerja lainnya: Upah/gaji, lembur, tunjangan</label><input type="text" value="{{ formatCurrencyBps($data['q313_b1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b.2 Pekerja lainnya: Pengeluaran lain</label><input type="text" value="{{ formatCurrencyBps($data['q313_b2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Total</label><input type="text" value="{{ formatCurrencyBps($data['q313_c'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">314.</span> Pengeluaran untuk pekerja/karyawan Outsourcing</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a.1 Pekerja produksi: Upah/gaji, lembur, tunjangan</label><input type="text" value="{{ formatCurrencyBps($data['q314_a1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">a.2 Pekerja produksi: Pengeluaran lain</label><input type="text" value="{{ formatCurrencyBps($data['q314_a2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b.1 Pekerja lainnya: Upah/gaji, lembur, tunjangan</label><input type="text" value="{{ formatCurrencyBps($data['q314_b1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b.2 Pekerja lainnya: Pengeluaran lain</label><input type="text" value="{{ formatCurrencyBps($data['q314_b2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Total</label><input type="text" value="{{ formatCurrencyBps($data['q314_c'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">315.</span> Penggunaan listrik yang dipakai oleh perusahaan</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Daya tersambung dari PLN (VA)</label><input type="text" value="{{ $data['q315_a'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Daya tersambung dari Non PLN (VA)</label><input type="text" value="{{ $data['q315_b'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Penggunaan listrik dari PLN (kWh)</label><input type="text" value="{{ $data['q315_c'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">d. Penggunaan listrik dari Non PLN (kWh)</label><input type="text" value="{{ $data['q315_d'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">e. Pengeluaran listrik (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q315_e'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
                <div class="form-row">
                    <label class="form-label"><span class="question-number">317.</span> Pengeluaran perusahaan selama tahun {{ $tahunBps }}</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a.1 Sewa/kontrak gedung, mesin, alat-alat (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_c1'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">a.2 Sewa/kontrak tanah (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_c2'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Pajak/Tax (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_d'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Nilai bunga atas pinjaman (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_e'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">d. Nilai hadiah, sumbangan, derma (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_f'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">e. Nilai dividen/laba yang dibagikan (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_g'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">f. Nilai premi asuransi kerugian (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_h'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">g. Nilai jasa industri/maklun (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_i'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">h. Air (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_j'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">i. Pengeluaran lainnya (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_k'] ?? '') }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- NILAI ASET (Q321) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">NILAI ASET</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">321.</span> Nilai aset 31 Des {{ $tahunBps }}</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Tanah & bangunan</label><input type="text" value="{{ formatCurrencyBps($data['q318a'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Selain tanah & bangunan</label><input type="text" value="{{ formatCurrencyBps($data['q318b'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Total Aset</label><input type="text" value="{{ formatCurrencyBps($data['q318c'] ?? '') }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c1. Rentang Nilai (jika c kosong)</label>
                            <input type="text" value="{{ $rangeLabels[$data['q318c_range'] ?? ''] ?? ($data['q318c_range'] ?? '') }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow"><label class="form-sublabel">d. Luas tanah (m2)</label><input type="text" value="{{ $data['q318d_area'] ?? '' }}" class="form-control" readonly disabled></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KEPEMILIKAN MODAL (Q322) --}}
        <div class="form-section">
            <div class="section-header"><h3 class="section-title">KEPEMILIKAN MODAL</h3></div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">322.</span> Susunan Modal (%)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow"><label class="form-sublabel">a. Pribadi</label><input type="text" value="{{ $data['q319a'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">b. Nonprofit</label><input type="text" value="{{ $data['q319b'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">c. Korporasi Publik</label><input type="text" value="{{ $data['q319c'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">d. Korporasi Non Publik</label><input type="text" value="{{ $data['q319d'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">e. Pemerintah</label><input type="text" value="{{ $data['q319e'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">f. Asing</label><input type="text" value="{{ $data['q319f'] ?? '' }}" class="form-control" readonly disabled></div>
                        <div class="form-subrow"><label class="form-sublabel">g. Total</label><input type="text" value="{{ $data['q319g'] ?? '' }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif {{-- end non-industri triwulanan/tahunan --}}
        @endif {{-- end renderNonIndustri --}}
    </form>
</div>
