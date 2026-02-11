{{-- Blok IIIB: Pendapatan & Pengeluaran - Read-only partial for BPS detail view --}}
{{-- This partial conditionally shows industri or non-industri data --}}
<div class="survey-container">
    <form class="survey-form">
        @php
            $hasIndustri = !empty($surveyResponse->blok3b_industri_data);
            $hasNonIndustri = !empty($surveyResponse->blok3b_nonindustri_data);
            $dataSource = $hasIndustri ? $surveyResponse->blok3b_industri_data : ($hasNonIndustri ? $surveyResponse->blok3b_nonindustri_data : []);
            $typeLabel = $hasIndustri ? 'Industri' : ($hasNonIndustri ? 'Non-Industri' : '');

            // Helper to format currency
            if (!function_exists('formatCurrencyBps')) {
                function formatCurrencyBps($value) {
                    if ($value === null || $value === '') return '';
                    return number_format((float)$value, 0, ',', '.');
                }
            }
        @endphp

        @if(!empty($dataSource))
        <!-- Type Badge -->
        <div style="padding: 1rem 1.5rem 0;">
            <span style="display: inline-block; padding: 0.25rem 0.75rem; background: #dbeafe; color: #1e40af; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                {{ $typeLabel }}
            </span>
        </div>

        <!-- PENDAPATAN PERUSAHAAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">PENDAPATAN PERUSAHAAN</h3>
                <p class="section-subtitle">Mencatat semua pendapatan selain PPN dan setelah diskon/retur</p>
            </div>
            <div class="form-grid">
                <!-- Q304 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">304.</span>
                        <span>Pendapatan royalti, bunga, dividen dan lainnya (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Pendapatan yang diterima perusahaan satu triwulan yang lalu</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource['q304a'] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Pendapatan yang diterima perusahaan selama tahun 2025</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource['q304b'] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>

                <!-- Q305 -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">305.</span>
                        <span>Berapa persentase pendapatan yang diperoleh dari usaha online (%)</span>
                    </label>
                    <input type="text" value="{{ ($dataSource['q305_online'] ?? '') !== '' ? ($dataSource['q305_online'] . '%') : '' }}"
                           class="form-control" readonly disabled>
                </div>
            </div>
        </div>

        <!-- PERSEDIAAN (INVENTORI) -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">PERSEDIAAN (INVENTORI)</h3>
                <p class="section-subtitle">Barang yang dikuasai dan ditahan untuk digunakan, dijual, atau diberikan</p>
            </div>
            <div class="form-grid">
                <!-- Q306 / Q307: Persediaan Bahan Baku -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">307.</span>
                        <span>Nilai Persediaan Bahan baku, bahan bakar, dsb (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q306_awal' => 'a. Persediaan Awal Periode (triwulan)',
                            'q306_akhir' => 'b. Persediaan Akhir Periode (triwulan)',
                            'q306_year_awal' => 'c. Tahun 2025 - Persediaan Awal Periode',
                            'q306_year_akhir' => 'd. Tahun 2025 - Persediaan Akhir Periode',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Q307/Q308: Persediaan Barang Dalam Proses -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">308.</span>
                        <span>Nilai Persediaan Barang Dalam Proses (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q307_awal' => 'a. Persediaan Awal Periode (triwulan)',
                            'q307_akhir' => 'b. Persediaan Akhir Periode (triwulan)',
                            'q307_year_awal' => 'c. Tahun 2025 - Persediaan Awal Periode',
                            'q307_year_akhir' => 'd. Tahun 2025 - Persediaan Akhir Periode',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Q308/Q309: Persediaan Barang Jadi -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">309.</span>
                        <span>Nilai Persediaan Barang jadi (termasuk untuk dijual kembali) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q308_awal' => 'a. Persediaan Awal Periode (triwulan)',
                            'q308_akhir' => 'b. Persediaan Akhir Periode (triwulan)',
                            'q308_year_awal' => 'c. Tahun 2025 - Persediaan Awal Periode',
                            'q308_year_akhir' => 'd. Tahun 2025 - Persediaan Akhir Periode',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Q310: Total Persediaan (auto-calculated) -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">310.</span>
                        <span>Total persediaan (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q309_awal' => 'a. Satu triwulan yang lalu - Persediaan Awal Periode',
                            'q309_akhir' => 'b. Satu triwulan yang lalu - Persediaan Akhir Periode',
                            'q310b_awal' => 'c. Tahun 2025 - Persediaan Awal Periode',
                            'q310b_akhir' => 'd. Tahun 2025 - Persediaan Akhir Periode',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled style="background-color: #e9ecef;">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- ITEM PENGELUARAN PERUSAHAAN -->
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">ITEM PENGELUARAN PERUSAHAAN</h3>
                <p class="section-subtitle">Biaya pengeluaran tanpa PPN dan diskon neto yang diberikan</p>
            </div>
            <div class="form-grid">
                <!-- Q311: Upah dan Gaji -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">311.</span>
                        <span>Total upah dan gaji serta jaminan sosial pegawai (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q311a' => 'a. Total upah dan gaji satu triwulan yang lalu',
                            'q311b' => 'b. Total upah dan gaji selama tahun 2025',
                            'q311b1' => 'b.1 Pegawai produksi selama tahun 2025',
                            'q311b2' => 'b.2 Selain pegawai produksi selama tahun 2025',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Q312: Penambahan Aset Tetap -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">312.</span>
                        <span>Penambahan aset tetap (kecuali pembelian tanah) satu triwulan yang lalu (rupiah)</span>
                    </label>
                    <input type="text" value="{{ formatCurrencyBps($dataSource['q311'] ?? '') }}"
                           class="form-control" readonly disabled>
                </div>

                <!-- Q313: Biaya Produksi -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">313.</span>
                        <span>Biaya produksi (pemakaian bahan baku dan penolong) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q312' => 'a. Satu triwulan yang lalu',
                            'q312_year' => 'b. Selama tahun 2025',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Q314: Biaya Operasional -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">314.</span>
                        <span>Biaya operasional (air, listrik, gas, pemeliharaan, biaya angkutan) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q313' => 'a. Satu triwulan yang lalu',
                            'q313_year' => 'b. Selama tahun 2025',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Q315: Jasa -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">315.</span>
                        <span>Biaya jasa (ongkos kirim, sewa, asuransi, dll) (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q314' => 'a. Satu triwulan yang lalu',
                            'q314_year' => 'b. Selama tahun 2025',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Q316: Pengeluaran Lainnya -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">316.</span>
                        <span>Pengeluaran lainnya (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q315' => 'a. Satu triwulan yang lalu',
                            'q315_year' => 'b. Selama tahun 2025',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrencyBps($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Q317: Total Pengeluaran -->
                <div class="form-row">
                    <label class="form-label">
                        <span class="question-number">317.</span>
                        <span>Total pengeluaran (rupiah)</span>
                    </label>
                    <div class="form-subgrid">
                        @foreach([
                            'q316' => 'a. Satu triwulan yang lalu',
                            'q316_year' => 'b. Selama tahun 2025',
                        ] as $key => $label)
                        <div class="form-subrow">
                            <label class="form-sublabel">{{ $label }}</label>
                            <input type="text" value="{{ formatCurrency($dataSource[$key] ?? '') }}"
                                   class="form-control" readonly disabled style="background-color: #e9ecef;">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        @else
        <div class="blok3b-empty-state" style="text-align: center; padding: 3rem 2rem; color: #6b7280;">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 1rem; display: block; color: #d1d5db;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <p style="font-size: 0.9375rem;">Belum ada data Blok IIIB</p>
        </div>
        @endif
    </form>
</div>
