{{-- Blok IIIB: Pendapatan & Pengeluaran - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        @php
            // Prepare both data sets; default to empty arrays when missing so fields render blank
            $dataIndustri = $surveyResponse->blok3b_industri_data ?? [];
            $dataNonIndustri = $surveyResponse->blok3b_nonindustri_data ?? [];

            // Helper to format currency
            if (!function_exists('formatCurrencyBps')) {
                function formatCurrencyBps($value) {
                    if ($value === null || $value === '') return '';
                    return number_format((float)$value, 0, ',', '.');
                }
            }

            $rangeLabels = [
                '1' => '1 s.d. Rp 500 juta',
                '2' => 'Lebih dari Rp 500 juta s.d. Rp 1 miliar',
                '3' => 'Lebih dari Rp 1 miliar s.d. Rp 5 miliar',
                '4' => 'Lebih dari Rp 5 miliar s.d. Rp 10 miliar',
                '5' => 'Lebih dari Rp 10 miliar'
            ];
        @endphp

        @php
            // Determine which variant(s) to render; default to both for backward compatibility
            $renderIndustri = isset($showIndustri) ? (bool)$showIndustri : true;
            $renderNonIndustri = isset($showNonIndustri) ? (bool)$showNonIndustri : true;
        @endphp

        {{-- Render Industri section when requested --}}
        @if($renderIndustri)
            @php $data = $dataIndustri; @endphp
            
            <div style="padding: 1rem 1.5rem 0;">
                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #dbeafe; color: #1e40af; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                    Industri
                </span>
            </div>

            {{-- 1. PENDAPATAN PERUSAHAAN (Industri) --}}
            <div class="form-section">
                <div class="section-header">
                    <h3 class="section-title">PENDAPATAN PERUSAHAAN (INDUSTRI)</h3>
                </div>
                <div class="form-grid">
                    {{-- Q304 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">304.</span> Pendapatan royalti, bunga, dividen dan lainnya</label>
                        <div class="form-subgrid">
                            <div class="form-subrow">
                                <label class="form-sublabel">a. Satu triwulan yang lalu</label>
                                <input type="text" value="{{ formatCurrencyBps($data['q304a'] ?? '') }}" class="form-control" readonly disabled>
                            </div>
                            <div class="form-subrow">
                                <label class="form-sublabel">b. Selama tahun 2025</label>
                                <input type="text" value="{{ formatCurrencyBps($data['q304b'] ?? '') }}" class="form-control" readonly disabled>
                            </div>
                        </div>
                    </div>
                    {{-- Q305 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">305.</span> Persentase pendapatan usaha online (%)</label>
                        <input type="text" value="{{ $data['q305_online'] ?? '' }}" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>

            {{-- 2. PERSEDIAAN (Industri) - Tahunan 2025 --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">PERSEDIAAN (INVENTORI)</h3></div>
                <div class="form-grid">
                    {{-- Q307 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">307.</span> Nilai stok bahan baku, bahan penolong, bahan bakar, bahan pembungkus, dan lain-lain</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari 2025</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember 2025</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q308 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">308.</span> Nilai stok barang produksi setengah jadi</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari 2025</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember 2025</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q309 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">309.</span> Nilai stok barang jadi yang dihasilkan</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari 2025</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember 2025</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q310 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">310.</span> Nilai pembelian/penambahan dan pembuatan/perbaikan besar seluruh barang modal tetap pada tahun 2025</label>
                        <input type="text" value="{{ formatCurrencyBps($data['q310_beli_modal'] ?? '') }}" class="form-control" readonly disabled>
                    </div>
                    {{-- Q311 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">311.</span> Nilai penjualan/pengurangan seluruh barang modal tetap pada tahun 2025</label>
                        <input type="text" value="{{ formatCurrencyBps($data['q311_jual_modal'] ?? '') }}" class="form-control" readonly disabled>
                    </div>
                    {{-- Q312 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">312.</span> Nilai taksiran seluruh barang modal tetap menurut harga berlaku per 31 Desember 2025</label>
                        <input type="text" value="{{ formatCurrencyBps($data['q312_taksir_modal'] ?? '') }}" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>

            {{-- 3. PENGELUARAN (Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3></div>
                <div class="form-grid">
                    {{-- Q313 --}}
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
                    {{-- Q314 --}}
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
                    {{-- Q315 --}}
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
                    {{-- Q316 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">316.</span> Biaya produksi</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q312'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q312_year'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q317 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">317.</span> Pengeluaran perusahaan selama tahun 2025</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Biaya operasional</label><input type="text" value="{{ formatCurrencyBps($data['q317_a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Biaya Non operasional</label><input type="text" value="{{ formatCurrencyBps($data['q317_b'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c.1 Sewa/kontrak gedung, mesin, alat-alat (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_c1'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c.2 Sewa/kontrak tanah (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_c2'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Pajak/Tax (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_d'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">e. Nilai bunga atas pinjaman (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_e'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">f. Nilai hadiah, sumbangan, derma (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_f'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">g. Nilai dividen/laba yang dibagikan (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_g'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">h. Nilai premi asuransi kerugian (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_h'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">i. Nilai jasa industri/maklun (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_i'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">j. Air (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_j'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">k. Pengeluaran lainnya (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_k'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. EKSPOR IMPOR (Industri) --}}
            <div class="form-section">
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">319.</span> % Ekspor</label>
                        <input type="text" value="{{ $data['q314'] ?? '' }}%" class="form-control" readonly disabled>
                    </div>
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">320.</span> % Impor</label>
                        <input type="text" value="{{ $data['q315'] ?? '' }}%" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>

            {{-- 5. NILAI ASET (Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">NILAI ASET</h3></div>
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">321.</span> Nilai aset 31 Des 2025</label>
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
            
             {{-- 6. KEPEMILIKAN MODAL (Industri) --}}
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
        @endif

        {{-- Render Non-Industri section when requested --}}
        @if($renderNonIndustri)
            @php $data = $dataNonIndustri; @endphp

            <div style="padding: 1rem 1.5rem 0;">
                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #d1fae5; color: #065f46; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                    Non-Industri
                </span>
            </div>

            {{-- 1. PENDAPATAN PERUSAHAAN (Non-Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">PENDAPATAN PERUSAHAAN (NON-INDUSTRI)</h3></div>
                <div class="form-grid">
                    {{-- Q303 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">303.</span> Nilai pendapatan dari penjualan barang dan jasa</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q303'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q303_year'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q304 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">304.</span> Pendapatan royalti, bunga, dividen dan lainnya</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q304'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q304_year'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q305 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">305.</span> Total Pendapatan</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q305'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q305_year'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                        </div>
                    </div>
                    {{-- Q306 Online --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">306.</span> Persentase pendapatan usaha online (%)</label>
                        <input type="text" value="{{ $data['q306_online'] ?? '' }}" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>

            {{-- 2. PERSEDIAAN (Non-Industri) - Tahunan 2025 --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">PERSEDIAAN (INVENTORI)</h3></div>
                <div class="form-grid">
                    {{-- Q307 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">307.</span> Nilai stok bahan baku, bahan penolong, bahan bakar, bahan pembungkus, dan lain-lain</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari 2025</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember 2025</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q308 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">308.</span> Nilai stok barang produksi setengah jadi</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari 2025</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember 2025</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q309 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">309.</span> Nilai stok barang jadi yang dihasilkan</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Kondisi 1 Januari 2025</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Kondisi 31 Desember 2025</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q310 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">310.</span> Nilai pembelian/penambahan dan pembuatan/perbaikan besar seluruh barang modal tetap pada tahun 2025</label>
                        <input type="text" value="{{ formatCurrencyBps($data['q310_beli_modal'] ?? '') }}" class="form-control" readonly disabled>
                    </div>
                    {{-- Q311 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">311.</span> Nilai penjualan/pengurangan seluruh barang modal tetap pada tahun 2025</label>
                        <input type="text" value="{{ formatCurrencyBps($data['q311_jual_modal'] ?? '') }}" class="form-control" readonly disabled>
                    </div>
                    {{-- Q312 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">312.</span> Nilai taksiran seluruh barang modal tetap menurut harga berlaku per 31 Desember 2025</label>
                        <input type="text" value="{{ formatCurrencyBps($data['q312_taksir_modal'] ?? '') }}" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>

            {{-- 3. PENGELUARAN (Non-Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3></div>
                 <div class="form-grid">
                    {{-- Q313 --}}
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
                    {{-- Q314 --}}
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
                    {{-- Q315 --}}
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
                    {{-- Q316 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">316.</span> Biaya produksi</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q312'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q312_year'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q317 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">317.</span> Pengeluaran perusahaan selama tahun 2025</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Biaya operasional</label><input type="text" value="{{ formatCurrencyBps($data['q317_a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Biaya Non operasional</label><input type="text" value="{{ formatCurrencyBps($data['q317_b'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c.1 Sewa/kontrak gedung, mesin, alat-alat (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_c1'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c.2 Sewa/kontrak tanah (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_c2'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Pajak/Tax (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_d'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">e. Nilai bunga atas pinjaman (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_e'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">f. Nilai hadiah, sumbangan, derma (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_f'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">g. Nilai dividen/laba yang dibagikan (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_g'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">h. Nilai premi asuransi kerugian (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_h'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">i. Nilai jasa industri/maklun (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_i'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">j. Air (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_j'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">k. Pengeluaran lainnya (Rp)</label><input type="text" value="{{ formatCurrencyBps($data['q317_k'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. EKSPOR IMPOR (Non-Industri) --}}
            <div class="form-section">
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">319.</span> % Ekspor</label>
                        <input type="text" value="{{ $data['q314'] ?? '' }}%" class="form-control" readonly disabled>
                    </div>
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">320.</span> % Impor</label>
                        <input type="text" value="{{ $data['q315'] ?? '' }}%" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>

             {{-- 5. NILAI ASET (Non-Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">NILAI ASET</h3></div>
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">321.</span> Nilai aset 31 Des 2025</label>
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

             {{-- 6. KEPEMILIKAN MODAL (Non-Industri) --}}
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

        @endif
    </form>
</div>
