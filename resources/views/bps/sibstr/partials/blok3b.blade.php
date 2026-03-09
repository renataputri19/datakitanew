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

            {{-- 2. PERSEDIAAN (Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">PERSEDIAAN (INVENTORI)</h3></div>
                <div class="form-grid">
                    {{-- Q307 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">307.</span> Persediaan Bahan baku, bahan bakar, dsb</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Awal Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q306_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Akhir Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q306_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Awal Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Akhir Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q308 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">308.</span> Persediaan Barang Dalam Proses</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Awal Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q307_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Akhir Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q307_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Awal Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Akhir Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q309 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">309.</span> Persediaan Barang jadi</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Awal Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q308_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Akhir Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q308_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Awal Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Akhir Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q310 Total --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">310.</span> Total Persediaan</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Awal Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q309_awal'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Akhir Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q309_akhir'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Awal Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q310b_awal'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Akhir Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q310b_akhir'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. PENGELUARAN (Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3></div>
                <div class="form-grid">
                    {{-- Q311 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">311.</span> Total upah dan gaji</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q311a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q311b'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b.1 Pegawai produksi</label><input type="text" value="{{ formatCurrencyBps($data['q311b1'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b.2 Selain pegawai produksi</label><input type="text" value="{{ formatCurrencyBps($data['q311b2'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q312 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">312.</span> Penambahan aset tetap (1 triwulan lalu)</label>
                        <input type="text" value="{{ formatCurrencyBps($data['q311'] ?? '') }}" class="form-control" readonly disabled>
                    </div>
                    {{-- Q313 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">313.</span> Biaya produksi</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q312'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q312_year'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q314 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">314.</span> Biaya operasional</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q313'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q313_year'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q315 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">315.</span> Biaya Non operasional (bunga pinjaman, pajak, premi asuransi, nilai hadiah/sumbangan) (rupiah)</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q315a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q315b'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. EKSPOR IMPOR (Industri) --}}
            <div class="form-section">
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">316.</span> % Ekspor</label>
                        <input type="text" value="{{ $data['q314'] ?? '' }}%" class="form-control" readonly disabled>
                    </div>
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">317.</span> % Impor</label>
                        <input type="text" value="{{ $data['q315'] ?? '' }}%" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>

            {{-- 5. NILAI ASET (Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">NILAI ASET</h3></div>
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">318.</span> Nilai aset 31 Des 2025</label>
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
                        <label class="form-label"><span class="question-number">319.</span> Susunan Modal (%)</label>
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

            {{-- 2. PERSEDIAAN (Non-Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">PERSEDIAAN (INVENTORI)</h3></div>
                <div class="form-grid">
                    {{-- Q307 (Stored as q306a/b...) --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">307.</span> Persediaan Bahan baku, bahan bakar, dsb</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Awal Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q306a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Akhir Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q306b'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Awal Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Akhir Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q306_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q308 (Stored as q307a/b...) --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">308.</span> Persediaan Barang Dalam Proses</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Awal Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q307a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Akhir Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q307b'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Awal Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Akhir Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q307_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q309 (Stored as q308a/b...) --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">309.</span> Persediaan Barang Jadi</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Awal Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q308a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Akhir Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q308b'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Awal Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_awal'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Akhir Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q308_year_akhir'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q310 Total (Stored as q309a/b... and q310b_awal/akhir for years) --}}
                     <div class="form-row">
                        <label class="form-label"><span class="question-number">310.</span> Total Persediaan</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Awal Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q309a'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Akhir Triwulan</label><input type="text" value="{{ formatCurrencyBps($data['q309b'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                            <div class="form-subrow"><label class="form-sublabel">c. Awal Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q310b_awal'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                            <div class="form-subrow"><label class="form-sublabel">d. Akhir Tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q310b_akhir'] ?? '') }}" class="form-control" readonly disabled style="background-color:#e9ecef"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. PENGELUARAN (Non-Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3></div>
                 <div class="form-grid">
                    {{-- Q311 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">311.</span> Total upah dan gaji</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q311a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q311b'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b.1 Pegawai produksi</label><input type="text" value="{{ formatCurrencyBps($data['q311b1'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b.2 Selain pegawai produksi</label><input type="text" value="{{ formatCurrencyBps($data['q311b2'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q312 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">312.</span> Penambahan aset tetap (1 triwulan lalu)</label>
                        <input type="text" value="{{ formatCurrencyBps($data['q311'] ?? '') }}" class="form-control" readonly disabled>
                    </div>
                    {{-- Q313 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">313.</span> Biaya produksi</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q312'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q312_year'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q314 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">314.</span> Biaya operasional</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q313'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q313_year'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                    {{-- Q315 --}}
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">315.</span> Biaya Non operasional (bunga pinjaman, pajak, premi asuransi, nilai hadiah/sumbangan) (rupiah)</label>
                        <div class="form-subgrid">
                            <div class="form-subrow"><label class="form-sublabel">a. Satu triwulan yang lalu</label><input type="text" value="{{ formatCurrencyBps($data['q315a'] ?? '') }}" class="form-control" readonly disabled></div>
                            <div class="form-subrow"><label class="form-sublabel">b. Selama tahun 2025</label><input type="text" value="{{ formatCurrencyBps($data['q315b'] ?? '') }}" class="form-control" readonly disabled></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. EKSPOR IMPOR (Non-Industri) --}}
            <div class="form-section">
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">316.</span> % Ekspor</label>
                        <input type="text" value="{{ $data['q314'] ?? '' }}%" class="form-control" readonly disabled>
                    </div>
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">317.</span> % Impor</label>
                        <input type="text" value="{{ $data['q315'] ?? '' }}%" class="form-control" readonly disabled>
                    </div>
                </div>
            </div>

             {{-- 5. NILAI ASET (Non-Industri) --}}
            <div class="form-section">
                <div class="section-header"><h3 class="section-title">NILAI ASET</h3></div>
                <div class="form-grid">
                    <div class="form-row">
                        <label class="form-label"><span class="question-number">318.</span> Nilai aset 31 Des 2025</label>
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
                        <label class="form-label"><span class="question-number">319.</span> Susunan Modal (%)</label>
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
