{{-- Blok IIIC: Bahan Baku & Bahan Penolong - Read-only partial for BPS detail view --}}
<div class="survey-container">
    <form class="survey-form">
        @php
            $materials  = $surveyResponse->blok3a2_materials ?? [];
            $totals3a   = $surveyResponse->blok3a_totals ?? [];
            $d3b        = $surveyResponse->blok3b_industri_data ?? [];
            $tahun3c    = (int)($surveyResponse->tahun ?? 2025);
            $prevYear3c = $tahun3c - 1;
            $allM3c     = ['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'];
            $months     = ["{$prevYear3c}_des"];
            foreach ($allM3c as $_m) { $months[] = "{$tahun3c}_{$_m}"; }
            $mlMap3c    = ['jan'=>'Jan','feb'=>'Feb','mar'=>'Mar','apr'=>'Apr','mei'=>'Mei',
                           'jun'=>'Jun','jul'=>'Jul','agu'=>'Agu','sep'=>'Sep','okt'=>'Okt','nov'=>'Nov','des'=>'Des'];
            $mLabels    = ["Des {$prevYear3c}"];
            foreach ($allM3c as $_m) { $mLabels[] = $mlMap3c[$_m]; }
            $rangeLabels3c = [
                '1' => '1 s.d. Rp 500 juta',
                '2' => 'Lebih dari Rp 500 juta s.d. Rp 1 miliar',
                '3' => 'Lebih dari Rp 1 miliar s.d. Rp 5 miliar',
                '4' => 'Lebih dari Rp 5 miliar s.d. Rp 10 miliar',
                '5' => 'Lebih dari Rp 10 miliar',
            ];

            if (!function_exists('_bps3c_nf')) {
                function _bps3c_nf($v) { return ($v !== null && $v !== '') ? number_format((float)$v, 0, ',', '.') : '-'; }
                function _bps3c_pf($v) { return ($v !== null && $v !== '') ? number_format((float)$v, 2, ',', '.') : '-'; }
            }

            $yesNoLabels = ['1' => 'Ya', '2' => 'Tidak'];

            // Auto-compute q318c (Total Aset) from a+b if stored value is 0/empty
            $q318a_val = (float)($d3b['q318a'] ?? 0);
            $q318b_val = (float)($d3b['q318b'] ?? 0);
            $q318c_stored = (float)($d3b['q318c'] ?? 0);
            $q318c_val = ($q318c_stored > 0) ? $q318c_stored : ($q318a_val + $q318b_val);

            // Auto-compute q319i (Total Modal) from a-h if stored value is 0/empty
            $q319_parts = ['q319a','q319b','q319c','q319d','q319e','q319f','q319g','q319h'];
            $q319i_stored = (float)($d3b['q319i'] ?? 0);
            if ($q319i_stored <= 0) {
                $q319i_stored = 0;
                foreach ($q319_parts as $_k) { $q319i_stored += (float)($d3b[$_k] ?? 0); }
            }
            $q319i_val = $q319i_stored;
        @endphp

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- TABLE 3c: Ringkasan Data Bahan Baku (matching survey form preview)  --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        <div class="form-section">
            <div class="section-header">
                <h3 class="section-title">BLOK IIIC – BAHAN BAKU DAN BAHAN PENOLONG</h3>
                <p class="section-subtitle">Bahan baku dan bahan penolong yang digunakan dalam proses produksi</p>
            </div>

            <div class="special-section" id="blok3c-preview-section" style="margin-bottom:1.5rem;">
                <h3 class="special-title">
                    Ringkasan Data Bahan Baku
                    <span style="font-size:0.75rem;font-weight:400;color:#6b7280;margin-left:0.5rem;">(Pratinjau - diperbarui secara otomatis)</span>
                </h3>

                @if(count($materials) > 0)
                @php
                    $totalDnNilai = 0; $totalLnNilai = 0;
                    foreach ($materials as $mat) {
                        $totalDnNilai += (float)($mat['dn_nilai'] ?? 0);
                        $totalLnNilai += (float)($mat['ln_nilai'] ?? 0);
                    }
                @endphp
                <div class="table-responsive" style="overflow-x:auto;padding:0.5rem 0;">
                    <table class="preview-table-el" style="width:100%;border-collapse:collapse;min-width:780px;">
                        <thead>
                            <tr>
                                <th style="text-align:center;background:#f1f5f9;border:1px solid #e5e7eb;min-width:38px;">(1)<br>No.</th>
                                <th style="text-align:left;background:#f1f5f9;border:1px solid #e5e7eb;min-width:180px;">(2)<br>Nama bahan baku &amp; penolong</th>
                                <th style="text-align:center;background:#f1f5f9;border:1px solid #e5e7eb;min-width:80px;">(3)<br>Satuan standar</th>
                                <th style="text-align:center;background:#fef9c3;border:1px solid #e5e7eb;min-width:90px;">(4)<br>Banyaknya</th>
                                <th style="text-align:center;background:#fef9c3;border:1px solid #e5e7eb;min-width:110px;">(5)<br>Nilai (Rp)</th>
                                <th style="text-align:center;background:#dbeafe;border:1px solid #e5e7eb;min-width:90px;">(6)<br>Banyaknya</th>
                                <th style="text-align:center;background:#dbeafe;border:1px solid #e5e7eb;min-width:110px;">(7)<br>Nilai (Rp)</th>
                                <th style="text-align:center;background:#f1f5f9;border:1px solid #e5e7eb;min-width:120px;">(8)<br>Negara asal **)</th>
                            </tr>
                            <tr>
                                <th colspan="3" style="background:#f8fafc;border:1px solid #e5e7eb;padding:0.3rem 0.6rem;font-size:0.73rem;text-align:center;color:#6b7280;"></th>
                                <th colspan="2" style="background:#fef9c3;border:1px solid #e5e7eb;padding:0.3rem 0.6rem;font-size:0.73rem;text-align:center;color:#92400e;">Dalam Negeri</th>
                                <th colspan="2" style="background:#dbeafe;border:1px solid #e5e7eb;padding:0.3rem 0.6rem;font-size:0.73rem;text-align:center;color:#1e40af;">Luar Negeri *)</th>
                                <th style="background:#f8fafc;border:1px solid #e5e7eb;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materials as $i => $mat)
                            <tr>
                                <td style="text-align:center;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ $i + 1 }}</td>
                                <td style="border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ !empty($mat['nama_bahan']) ? e($mat['nama_bahan']) : '-' }}</td>
                                <td style="text-align:center;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ !empty($mat['satuan_standar']) ? e($mat['satuan_standar']) : '-' }}</td>
                                <td style="text-align:right;background:#fffde7;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ _bps3c_nf($mat['dn_banyaknya'] ?? null) }}</td>
                                <td style="text-align:right;background:#fffde7;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ _bps3c_nf($mat['dn_nilai'] ?? null) }}</td>
                                <td style="text-align:right;background:#eff6ff;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ _bps3c_nf($mat['ln_banyaknya'] ?? null) }}</td>
                                <td style="text-align:right;background:#eff6ff;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ _bps3c_nf($mat['ln_nilai'] ?? null) }}</td>
                                <td style="text-align:center;border:1px solid #e5e7eb;padding:0.4rem 0.5rem;">{{ !empty($mat['negara_asal']) ? e($mat['negara_asal']) : '-' }}</td>
                            </tr>
                            @if(!empty($mat['rincian_asal']) && is_array($mat['rincian_asal']))
                            @foreach($mat['rincian_asal'] as $ra)
                            @php
                                $raJml = preg_replace('/[^0-9]/', '', $ra['jumlah'] ?? '');
                                $raNil = preg_replace('/[^0-9]/', '', $ra['nilai'] ?? '');
                                $raHasData = !empty($ra['provinsi']) || $raJml !== '' || $raNil !== '';
                            @endphp
                            @if($raHasData)
                            <tr style="background:#f0f9ff;">
                                <td style="text-align:center;border:1px solid #dbeafe;padding:0.3rem 0.5rem;color:#93c5fd;font-size:0.7rem;">↳</td>
                                <td style="border:1px solid #dbeafe;padding:0.3rem 0.75rem;color:#1d4ed8;font-size:0.78rem;font-style:italic;">
                                    {{ !empty($ra['provinsi']) ? e($ra['provinsi']) : '—' }}
                                </td>
                                <td style="border:1px solid #dbeafe;padding:0.3rem 0.5rem;"></td>
                                <td style="text-align:right;background:#fffde7;border:1px solid #dbeafe;padding:0.3rem 0.5rem;font-size:0.78rem;color:#374151;">
                                    {{ $raJml !== '' ? number_format((int)$raJml, 0, ',', '.') : '—' }}
                                </td>
                                <td style="text-align:right;background:#fffde7;border:1px solid #dbeafe;padding:0.3rem 0.5rem;font-size:0.78rem;color:#374151;">
                                    {{ $raNil !== '' ? number_format((int)$raNil, 0, ',', '.') : '—' }}
                                </td>
                                <td style="background:#eff6ff;border:1px solid #dbeafe;padding:0.3rem 0.5rem;"></td>
                                <td style="background:#eff6ff;border:1px solid #dbeafe;padding:0.3rem 0.5rem;"></td>
                                <td style="border:1px solid #dbeafe;padding:0.3rem 0.5rem;"></td>
                            </tr>
                            @endif
                            @endforeach
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:0.5rem;font-size:0.75rem;color:#9ca3af;">
                    *) Termasuk yang diimpor oleh importir umum atau pihak lain. &nbsp;**) Jika negara asal impor lebih dari satu, tuliskan negara dengan nilai impor terbesar.
                </div>
                @else
                    <div style="text-align:center;padding:1rem;color:#6b7280;">Belum ada data bahan baku untuk ditampilkan.</div>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- NILAI ASET (dari blok3b_industri_data, dipindah ke Blok IIIC)      --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if($q318a_val > 0 || $q318b_val > 0 || $q318c_val > 0 || !empty($d3b['q318c_range']) || !empty($d3b['q318d_area']))
        <div class="form-section" style="margin-top:1rem;">
            <div class="section-header">
                <h3 class="section-title">NILAI ASET</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">318.</span> Nilai aset per 31 Des {{ $tahun3c }}</label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Tanah dan bangunan (Rp)</label>
                            <input type="text" value="{{ _bps3c_nf($q318a_val > 0 ? $q318a_val : null) }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Selain tanah dan bangunan (Rp)</label>
                            <input type="text" value="{{ _bps3c_nf($q318b_val > 0 ? $q318b_val : null) }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Nilai total aset — jumlah a + b (Rp)</label>
                            <input type="text" value="{{ _bps3c_nf($q318c_val > 0 ? $q318c_val : null) }}" class="form-control" readonly disabled style="background-color:#e9ecef;">
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c1. Rentang nilai (jika c kosong)</label>
                            <input type="text" value="{{ $rangeLabels3c[$d3b['q318c_range'] ?? ''] ?? (($d3b['q318c_range'] ?? '') !== '' ? $d3b['q318c_range'] : '-') }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Luas tanah yang digunakan untuk usaha (m²)</label>
                            <input type="text" value="{{ ($d3b['q318d_area'] ?? '') !== '' ? $d3b['q318d_area'] : '-' }}" class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- KEPEMILIKAN MODAL (dari blok3b_industri_data, dipindah ke Blok IIIC) --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if(!empty($d3b['q319a']) || !empty($d3b['q319b']) || !empty($d3b['q319c']) || !empty($d3b['q319d']) || !empty($d3b['q319e']) || !empty($d3b['q319f']) || !empty($d3b['q319g']) || !empty($d3b['q319h']))
        <div class="form-section" style="margin-top:1rem;">
            <div class="section-header">
                <h3 class="section-title">KEPEMILIKAN MODAL</h3>
            </div>
            <div class="form-grid">
                <div class="form-row">
                    <label class="form-label"><span class="question-number">319.</span> Susunan Modal (%)</label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Pribadi/Perorangan</label>
                            <input type="text" value="{{ ($d3b['q319a'] ?? '') !== '' ? $d3b['q319a'].' %' : '-' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Lembaga Nonprofit yang Melayani Rumah Tangga</label>
                            <input type="text" value="{{ ($d3b['q319b'] ?? '') !== '' ? $d3b['q319b'].' %' : '-' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Korporasi Publik</label>
                            <input type="text" value="{{ ($d3b['q319c'] ?? '') !== '' ? $d3b['q319c'].' %' : '-' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Korporasi Non Publik</label>
                            <input type="text" value="{{ ($d3b['q319d'] ?? '') !== '' ? $d3b['q319d'].' %' : '-' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">e. Pemerintah Pusat</label>
                            <input type="text" value="{{ ($d3b['q319e'] ?? '') !== '' ? $d3b['q319e'].' %' : '-' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">f. Pemerintah Daerah</label>
                            <input type="text" value="{{ ($d3b['q319f'] ?? '') !== '' ? $d3b['q319f'].' %' : '-' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">g. Perusahaan Swasta Nasional</label>
                            <input type="text" value="{{ ($d3b['q319g'] ?? '') !== '' ? $d3b['q319g'].' %' : '-' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">h. Asing</label>
                            <input type="text" value="{{ ($d3b['q319h'] ?? '') !== '' ? $d3b['q319h'].' %' : '-' }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">i. Total (otomatis) — harus 100%</label>
                            <input type="text" value="{{ $q319i_val > 0 ? number_format($q319i_val, 2, ',', '.').' %' : '-' }}" class="form-control" readonly disabled style="background-color:#e9ecef;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        {{-- Q320–Q322: Prospek & Kendala Usaha (tahunan only)                  --}}
        {{-- ═══════════════════════════════════════════════════════════════════ --}}
        @if(!empty($d3b['q320']) || !empty($d3b['q324']) || !empty($d3b['q325']))
        <div class="form-section" style="margin-top:1rem;">
            <div class="section-header">
                <h3 class="section-title">PROSPEK DAN KENDALA USAHA/PERUSAHAAN</h3>
            </div>
            <div class="form-grid">

                {{-- Q320: Kendala --}}
                <div class="form-row">
                    <label class="form-label"><span class="question-number">320.</span> Selama tahun {{ $tahun3c }}, apakah perusahaan mengalami kendala/kesulitan berikut?</label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Permodalan</label>
                            <input type="text" value="{{ $yesNoLabels[$d3b['q320'] ?? ''] ?? ($d3b['q320'] ?? '-') }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Bahan baku</label>
                            <input type="text" value="{{ $yesNoLabels[$d3b['q321'] ?? ''] ?? ($d3b['q321'] ?? '-') }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Pemasaran</label>
                            <input type="text" value="{{ $yesNoLabels[$d3b['q322'] ?? ''] ?? ($d3b['q322'] ?? '-') }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Iklim Usaha</label>
                            <input type="text" value="{{ $yesNoLabels[$d3b['q323'] ?? ''] ?? ($d3b['q323'] ?? '-') }}" class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>

                {{-- Q321: Rencana Rekrut --}}
                <div class="form-row">
                    <label class="form-label"><span class="question-number">321.</span> Apakah tahun {{ $tahun3c + 1 }} perusahaan berencana merekrut pegawai atau mengembangkan/memperluas usaha?</label>
                    <input type="text" value="{{ $yesNoLabels[$d3b['q324'] ?? ''] ?? ($d3b['q324'] ?? '-') }}" class="form-control" readonly disabled>
                </div>

                {{-- Q322: Strategi Daya Saing --}}
                <div class="form-row">
                    <label class="form-label"><span class="question-number">322.</span> Strategi perusahaan untuk peningkatan daya saing?</label>
                    <div class="form-subgrid">
                        <div class="form-subrow">
                            <label class="form-sublabel">a. Inovasi (barang dan jasa)</label>
                            <input type="text" value="{{ $yesNoLabels[$d3b['q325'] ?? ''] ?? ($d3b['q325'] ?? '-') }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">b. Pengembangan Teknologi</label>
                            <input type="text" value="{{ $yesNoLabels[$d3b['q326'] ?? ''] ?? ($d3b['q326'] ?? '-') }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">c. Pemasaran (marketing)</label>
                            <input type="text" value="{{ $yesNoLabels[$d3b['q327'] ?? ''] ?? ($d3b['q327'] ?? '-') }}" class="form-control" readonly disabled>
                        </div>
                        <div class="form-subrow">
                            <label class="form-sublabel">d. Kemitraan (UMKM, pemerintah, dll)</label>
                            <input type="text" value="{{ $yesNoLabels[$d3b['q328'] ?? ''] ?? ($d3b['q328'] ?? '-') }}" class="form-control" readonly disabled>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif

    </form>
</div>
