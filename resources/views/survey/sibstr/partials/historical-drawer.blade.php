{{--
  Historical Data Drawer
  Slide-over panel showing all prior-period survey data for reference comparison.

  Props (passed via @include):
    $historicalResponses  — Illuminate\Support\Collection<SurveyResponse>
    $blockKey             — 'blok3a' | 'blok3b_industri' | 'blok3b_nonindustri'
--}}
@php
    use App\Models\SurveyResponse;

    $histBlockTitles = [
        'blok3a'             => 'Blok IIIA — Kondisi Perekonomian',
        'blok3a2'            => 'Blok IIIC — Bahan Baku & Penolong',
        'blok3b_industri'    => 'Blok IIIB — Industri',
        'blok3b_nonindustri' => 'Blok IIIB — Non-Industri',
    ];
    $histTitle = $histBlockTitles[$blockKey] ?? 'Data Historis';

    $histMonthKeys   = ['2024_des','2025_jan','2025_feb','2025_mar','2025_apr','2025_mei','2025_jun','2025_jul','2025_agu','2025_sep','2025_okt','2025_nov','2025_des'];
    $histMonthLabels = ['Des 2024','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    $histGroupColors = [
        'blue'    => ['wrap'=>'border-blue-200 dark:border-blue-800',    'head'=>'bg-blue-50 dark:bg-blue-950/30 border-blue-200 dark:border-blue-800',    'title'=>'text-blue-700 dark:text-blue-300'],
        'indigo'  => ['wrap'=>'border-indigo-200 dark:border-indigo-800', 'head'=>'bg-indigo-50 dark:bg-indigo-950/30 border-indigo-200 dark:border-indigo-800', 'title'=>'text-indigo-700 dark:text-indigo-300'],
        'violet'  => ['wrap'=>'border-violet-200 dark:border-violet-800', 'head'=>'bg-violet-50 dark:bg-violet-950/30 border-violet-200 dark:border-violet-800', 'title'=>'text-violet-700 dark:text-violet-300'],
        'cyan'    => ['wrap'=>'border-cyan-200 dark:border-cyan-800',     'head'=>'bg-cyan-50 dark:bg-cyan-950/30 border-cyan-200 dark:border-cyan-800',     'title'=>'text-cyan-700 dark:text-cyan-300'],
        'emerald' => ['wrap'=>'border-emerald-200 dark:border-emerald-800','head'=>'bg-emerald-50 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800','title'=>'text-emerald-700 dark:text-emerald-300'],
        'amber'   => ['wrap'=>'border-amber-200 dark:border-amber-800',   'head'=>'bg-amber-50 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800',   'title'=>'text-amber-700 dark:text-amber-300'],
    ];

    $hist3bIGroups = [
        ['title'=>'Pendapatan Perusahaan', 'color'=>'blue', 'rows'=>[
            ['label'=>'Q304a. Pendapatan royalti/bunga/dividen — triwulan lalu',   'key'=>'q304a',       'type'=>'currency'],
            ['label'=>'Q304b. Pendapatan royalti/bunga/dividen — tahun berjalan',  'key'=>'q304b',       'type'=>'currency'],
            ['label'=>'Q305.  % Pendapatan dari usaha online',                     'key'=>'q305_online', 'type'=>'percent'],
        ]],
        ['title'=>'Persediaan (Inventori) — Periode', 'color'=>'indigo', 'rows'=>[
            ['label'=>'Q306. Bahan baku — Awal periode',   'key'=>'q306_awal',  'type'=>'currency'],
            ['label'=>'Q306. Bahan baku — Akhir periode',  'key'=>'q306_akhir', 'type'=>'currency'],
            ['label'=>'Q307. WIP — Awal periode',          'key'=>'q307_awal',  'type'=>'currency'],
            ['label'=>'Q307. WIP — Akhir periode',         'key'=>'q307_akhir', 'type'=>'currency'],
            ['label'=>'Q308. Barang Jadi — Awal periode',  'key'=>'q308_awal',  'type'=>'currency'],
            ['label'=>'Q308. Barang Jadi — Akhir periode', 'key'=>'q308_akhir', 'type'=>'currency'],
            ['label'=>'Q309. Total Persediaan — Awal',     'key'=>'q309_awal',  'type'=>'currency'],
            ['label'=>'Q309. Total Persediaan — Akhir',    'key'=>'q309_akhir', 'type'=>'currency'],
        ]],
        ['title'=>'Persediaan (Inventori) — Tahunan', 'color'=>'violet', 'rows'=>[
            ['label'=>'Q306. Bahan baku — Awal tahun',          'key'=>'q306_year_awal',  'type'=>'currency'],
            ['label'=>'Q306. Bahan baku — Akhir tahun',         'key'=>'q306_year_akhir', 'type'=>'currency'],
            ['label'=>'Q307. WIP — Awal tahun',                 'key'=>'q307_year_awal',  'type'=>'currency'],
            ['label'=>'Q307. WIP — Akhir tahun',                'key'=>'q307_year_akhir', 'type'=>'currency'],
            ['label'=>'Q308. Barang Jadi — Awal tahun',         'key'=>'q308_year_awal',  'type'=>'currency'],
            ['label'=>'Q308. Barang Jadi — Akhir tahun',        'key'=>'q308_year_akhir', 'type'=>'currency'],
            ['label'=>'Q310b. Total Persediaan — Awal tahun',   'key'=>'q310b_awal',      'type'=>'currency'],
            ['label'=>'Q310b. Total Persediaan — Akhir tahun',  'key'=>'q310b_akhir',     'type'=>'currency'],
        ]],
        ['title'=>'Pembelian & Pengeluaran', 'color'=>'cyan', 'rows'=>[
            ['label'=>'Q310.   Pembelian bahan baku — triwulan lalu',   'key'=>'q310',      'type'=>'currency'],
            ['label'=>'Q310.   Pembelian bahan baku — tahun berjalan',  'key'=>'q310_year', 'type'=>'currency'],
            ['label'=>'Q311a.  Upah/gaji — triwulan lalu',              'key'=>'q311a',     'type'=>'currency'],
            ['label'=>'Q311b.  Upah/gaji — tahun berjalan',             'key'=>'q311b',     'type'=>'currency'],
            ['label'=>'Q311b.1 Upah produksi (tahun)',                  'key'=>'q311b1',    'type'=>'currency'],
            ['label'=>'Q311b.2 Upah non-produksi (tahun)',              'key'=>'q311b2',    'type'=>'currency'],
            ['label'=>'Q312.   Biaya listrik — triwulan lalu',          'key'=>'q312',      'type'=>'currency'],
            ['label'=>'Q312.   Biaya listrik — tahun berjalan',         'key'=>'q312_year', 'type'=>'currency'],
            ['label'=>'Q313.   Biaya bahan bakar — triwulan lalu',      'key'=>'q313',      'type'=>'currency'],
            ['label'=>'Q313.   Biaya bahan bakar — tahun berjalan',     'key'=>'q313_year', 'type'=>'currency'],
        ]],
        ['title'=>'Subkontrak', 'color'=>'emerald', 'rows'=>[
            ['label'=>'Q314.  % Pekerjaan disubkontrakkan keluar',    'key'=>'q314',  'type'=>'percent'],
            ['label'=>'Q315.  % Pekerjaan subkontrak diterima',       'key'=>'q315',  'type'=>'percent'],
            ['label'=>'Q315a. Nilai pekerjaan disubkontrak keluar',   'key'=>'q315a', 'type'=>'currency'],
            ['label'=>'Q315b. Nilai pekerjaan subkontrak diterima',   'key'=>'q315b', 'type'=>'currency'],
        ]],
        ['title'=>'Aset Tetap & Kepemilikan Modal', 'color'=>'amber', 'rows'=>[
            ['label'=>'Q318a. Nilai aset tetap sendiri',          'key'=>'q318a',       'type'=>'currency'],
            ['label'=>'Q318b. Nilai aset tetap sewa guna usaha',  'key'=>'q318b',       'type'=>'currency'],
            ['label'=>'Q318c. Total aset tetap',                  'key'=>'q318c',       'type'=>'currency'],
            ['label'=>'Q318c. Skala range aset tetap (1–5)',      'key'=>'q318c_range', 'type'=>'raw'],
            ['label'=>'Q318d. Luas area (m²)',                    'key'=>'q318d_area',  'type'=>'raw'],
            ['label'=>'Q319a. Pribadi/Perorangan',                  'key'=>'q319a',       'type'=>'percent'],
            ['label'=>'Q319b. Lembaga Nonprofit (Rumah Tangga)',   'key'=>'q319b',       'type'=>'percent'],
            ['label'=>'Q319c. Korporasi Publik',                   'key'=>'q319c',       'type'=>'percent'],
            ['label'=>'Q319d. Korporasi Non Publik',               'key'=>'q319d',       'type'=>'percent'],
            ['label'=>'Q319e. Pemerintah Pusat',                   'key'=>'q319e',       'type'=>'percent'],
            ['label'=>'Q319f. Pemerintah Daerah',                  'key'=>'q319f',       'type'=>'percent'],
            ['label'=>'Q319g. Perusahaan Swasta Nasional',         'key'=>'q319g',       'type'=>'percent'],
            ['label'=>'Q319h. Asing',                              'key'=>'q319h',       'type'=>'percent'],
            ['label'=>'Q319i. Total kepemilikan',                  'key'=>'q319i',       'type'=>'percent'],
        ]],
    ];

    $hist3bNGroups = [
        ['title'=>'Pendapatan Perusahaan', 'color'=>'blue', 'rows'=>[
            ['label'=>'Q303.  Pendapatan penjualan barang/jasa — triwulan lalu',  'key'=>'q303',        'type'=>'currency'],
            ['label'=>'Q303.  Pendapatan penjualan barang/jasa — tahun berjalan', 'key'=>'q303_year',   'type'=>'currency'],
            ['label'=>'Q304.  Pendapatan lainnya — triwulan lalu',                'key'=>'q304',        'type'=>'currency'],
            ['label'=>'Q304.  Pendapatan lainnya — tahun berjalan',               'key'=>'q304_year',   'type'=>'currency'],
            ['label'=>'Q305.  Total pendapatan — triwulan lalu',                  'key'=>'q305',        'type'=>'currency'],
            ['label'=>'Q305.  Total pendapatan — tahun berjalan',                 'key'=>'q305_year',   'type'=>'currency'],
            ['label'=>'Q306.  % Pendapatan dari usaha online',                    'key'=>'q306_online', 'type'=>'percent'],
        ]],
        ['title'=>'Persediaan (Inventori)', 'color'=>'indigo', 'rows'=>[
            ['label'=>'Q307. Barang dagangan — Awal periode',  'key'=>'q306a',        'type'=>'currency'],
            ['label'=>'Q307. Barang dagangan — Akhir periode', 'key'=>'q306b',        'type'=>'currency'],
            ['label'=>'Q307. Barang dagangan — Awal tahun',    'key'=>'q306_year_awal',  'type'=>'currency'],
            ['label'=>'Q307. Barang dagangan — Akhir tahun',   'key'=>'q306_year_akhir', 'type'=>'currency'],
            ['label'=>'Q308. Bahan baku — Awal periode',       'key'=>'q307a',        'type'=>'currency'],
            ['label'=>'Q308. Bahan baku — Akhir periode',      'key'=>'q307b',        'type'=>'currency'],
            ['label'=>'Q309. Persediaan lainnya — Awal',       'key'=>'q308a',        'type'=>'currency'],
            ['label'=>'Q309. Persediaan lainnya — Akhir',      'key'=>'q308b',        'type'=>'currency'],
            ['label'=>'Q310. Total persediaan — Awal',         'key'=>'q309a',        'type'=>'currency'],
            ['label'=>'Q310. Total persediaan — Akhir',        'key'=>'q309b',        'type'=>'currency'],
        ]],
        ['title'=>'Pembelian & Pengeluaran', 'color'=>'cyan', 'rows'=>[
            ['label'=>'Q311.   Pembelian/pengadaan — triwulan lalu',   'key'=>'q310',      'type'=>'currency'],
            ['label'=>'Q311.   Pembelian/pengadaan — tahun berjalan',  'key'=>'q310_year', 'type'=>'currency'],
            ['label'=>'Q312a.  Upah/gaji — triwulan lalu',             'key'=>'q311a',     'type'=>'currency'],
            ['label'=>'Q312b.  Upah/gaji — tahun berjalan',            'key'=>'q311b',     'type'=>'currency'],
            ['label'=>'Q312b.1 Upah produksi (tahun)',                  'key'=>'q311b1',    'type'=>'currency'],
            ['label'=>'Q312b.2 Upah non-produksi (tahun)',              'key'=>'q311b2',    'type'=>'currency'],
            ['label'=>'Q313.   Biaya listrik',                          'key'=>'q312',      'type'=>'currency'],
            ['label'=>'Q313.   Biaya listrik — tahun berjalan',         'key'=>'q312_year', 'type'=>'currency'],
            ['label'=>'Q314.   Biaya bahan bakar',                      'key'=>'q313',      'type'=>'currency'],
            ['label'=>'Q314.   Biaya bahan bakar — tahun berjalan',     'key'=>'q313_year', 'type'=>'currency'],
        ]],
        ['title'=>'Subkontrak', 'color'=>'emerald', 'rows'=>[
            ['label'=>'Q315.  % Pekerjaan disubkontrakkan keluar',    'key'=>'q314',  'type'=>'percent'],
            ['label'=>'Q316.  % Pekerjaan subkontrak diterima',       'key'=>'q315',  'type'=>'percent'],
            ['label'=>'Q315a. Nilai pekerjaan disubkontrak keluar',   'key'=>'q315a', 'type'=>'currency'],
            ['label'=>'Q315b. Nilai pekerjaan subkontrak diterima',   'key'=>'q315b', 'type'=>'currency'],
        ]],
    ];
@endphp

{{-- ──────────────────────────────────────────────────────── --}}
{{-- FLOATING TRIGGER BUTTON (always rendered, shown when    --}}
{{-- historical data exists)                                 --}}
{{-- ──────────────────────────────────────────────────────── --}}
@if($historicalResponses->isNotEmpty())
<div class="hist-trigger-wrap" style="position:fixed;bottom:1.75rem;right:1.75rem;z-index:39;">
    <button type="button"
            id="hist-open-btn"
            onclick="openHistDrawer()"
            title="Lihat data historis periode sebelumnya"
            aria-label="Buka panel data historis"
            style="display:inline-flex;align-items:center;gap:0.5rem;padding:0.65rem 1.1rem;border-radius:9999px;
                   background:linear-gradient(135deg,#1d4ed8,#2563eb);color:#fff;
                   font-size:0.8125rem;font-weight:600;letter-spacing:0.01em;
                   box-shadow:0 4px 14px rgba(37,99,235,0.45);border:none;cursor:pointer;
                   transition:box-shadow 0.2s,transform 0.2s;">
        <svg style="width:1rem;height:1rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Lihat Data Historis</span>
        <span style="display:inline-flex;align-items:center;justify-content:center;width:1.2rem;height:1.2rem;border-radius:9999px;background:rgba(255,255,255,0.25);font-size:0.7rem;font-weight:700;">
            {{ $historicalResponses->count() }}
        </span>
    </button>
</div>
@endif

{{-- ──────────────────────────────────────────────────────── --}}
{{-- BACKDROP                                                --}}
{{-- ──────────────────────────────────────────────────────── --}}
<div id="hist-backdrop"
     onclick="closeHistDrawer()"
     style="display:none;position:fixed;inset:0;top:4rem;background:rgba(0,0,0,0.45);backdrop-filter:blur(2px);z-index:48;
            opacity:0;transition:opacity 0.3s ease;"
     aria-hidden="true"></div>

{{-- ──────────────────────────────────────────────────────── --}}
{{-- DRAWER                                                  --}}
{{-- ──────────────────────────────────────────────────────── --}}
<aside id="hist-drawer"
       role="complementary"
       aria-label="Panel data historis untuk referensi"
       style="position:fixed;top:4rem;bottom:0;right:0;z-index:49;
              width:100%;max-width:36rem;display:flex;flex-direction:column;
              background:#fff;box-shadow:-8px 0 32px rgba(0,0,0,0.18);
              transform:translateX(100%);transition:transform 0.32s cubic-bezier(0.4,0,0.2,1);">

    {{-- ── Header ── --}}
    <div style="flex-shrink:0;display:flex;align-items:center;justify-content:space-between;
                padding:1rem 1.25rem;
                background:linear-gradient(135deg,#1e40af,#2563eb);
                border-bottom:1px solid rgba(255,255,255,0.1);">
        <div style="display:flex;align-items:center;gap:0.75rem;min-width:0;">
            <div style="flex-shrink:0;display:flex;align-items:center;justify-content:center;
                        width:2.25rem;height:2.25rem;border-radius:0.625rem;
                        background:rgba(255,255,255,0.18);">
                <svg style="width:1.125rem;height:1.125rem;color:#fff;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div style="min-width:0;">
                <p style="margin:0;font-size:0.9375rem;font-weight:700;color:#fff;line-height:1.2;">Data Historis</p>
                <p style="margin:0.15rem 0 0;font-size:0.7rem;color:#bfdbfe;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $histTitle }} — Hanya untuk referensi
                </p>
            </div>
        </div>
        <button type="button"
                onclick="closeHistDrawer()"
                aria-label="Tutup panel data historis"
                style="flex-shrink:0;margin-left:0.75rem;padding:0.4rem;border-radius:0.5rem;
                       background:rgba(255,255,255,0.15);border:none;cursor:pointer;color:#e0f2fe;
                       transition:background 0.15s,color 0.15s;">
            <svg style="width:1.125rem;height:1.125rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ── Period Tabs ── --}}
    @if($historicalResponses->count() > 1)
    <div style="flex-shrink:0;display:flex;align-items:center;gap:0.5rem;padding:0.625rem 1.25rem;
                border-bottom:1px solid #e5e7eb;background:#f9fafb;overflow-x:auto;">
        <span style="flex-shrink:0;font-size:0.7rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-right:0.25rem;">
            Periode:
        </span>
        @foreach($historicalResponses as $hIdx => $hResp)
        @php
            $hShortLabel = $hResp->triwulan == 0
                ? (string) $hResp->tahun
                : 'TW' . $hResp->triwulan . ' ' . $hResp->tahun;
            $hFullLabel = $hResp->triwulan == 0
                ? 'Tahunan ' . $hResp->tahun
                : SurveyResponse::triwulanLabel($hResp->triwulan) . ' ' . $hResp->tahun;
        @endphp
        <button type="button"
                class="hist-period-tab"
                data-period-idx="{{ $hIdx }}"
                onclick="switchHistPeriod({{ $hIdx }})"
                title="{{ $hFullLabel }}"
                style="flex-shrink:0;padding:0.3rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-weight:600;
                       cursor:pointer;transition:all 0.15s;white-space:nowrap;
                       {{ $hIdx === 0 ? 'background:#2563eb;color:#fff;border:1px solid #2563eb;box-shadow:0 1px 4px rgba(37,99,235,0.35);' : 'background:#fff;color:#374151;border:1px solid #d1d5db;' }}">
            {{ $hShortLabel }}
        </button>
        @endforeach
    </div>
    @else
    <div style="flex-shrink:0;padding:0.625rem 1.25rem;border-bottom:1px solid #e5e7eb;background:#f9fafb;display:flex;align-items:center;gap:0.5rem;">
        @php
            $hOnly = $historicalResponses->first();
            if ($hOnly) {
                $hOnlyLabel = $hOnly->triwulan == 0
                    ? 'Tahunan ' . $hOnly->tahun
                    : SurveyResponse::triwulanLabel($hOnly->triwulan) . ' ' . $hOnly->tahun;
            } else {
                $hOnlyLabel = 'Tidak ada data historis';
            }
        @endphp
        <span style="display:inline-flex;align-items:center;gap:0.3rem;padding:0.3rem 0.75rem;border-radius:9999px;
                     background:#dbeafe;color:#1d4ed8;font-size:0.75rem;font-weight:700;">
            <svg style="width:0.75rem;height:0.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            {{ $hOnlyLabel }}
        </span>
        <span style="font-size:0.7rem;color:#9ca3af;">@if($hOnly) Periode historis tersedia @else Tidak ada data historis @endif</span>
    </div>
    @endif

    {{-- ── Scrollable Content ── --}}
    <div style="flex:1;overflow-y:auto;-webkit-overflow-scrolling:touch;">

        @forelse($historicalResponses as $hIdx => $hResp)

        @php
            $hPeriodLabel = $hResp->triwulan == 0
                ? 'Tahunan ' . $hResp->tahun
                : SurveyResponse::triwulanLabel($hResp->triwulan) . ' ' . $hResp->tahun;
            $hIsComplete = (bool) $hResp->is_completed;
        @endphp

        <div class="hist-period-panel"
             data-period-idx="{{ $hIdx }}"
             style="{{ $hIdx > 0 ? 'display:none;' : '' }}padding:1.25rem;display:{{ $hIdx > 0 ? 'none' : 'block' }};">

            {{-- Completion badge --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                <span style="font-size:0.8rem;font-weight:600;color:#374151;">{{ $hPeriodLabel }}</span>
                @if($hIsComplete)
                <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.2rem 0.6rem;border-radius:9999px;
                             background:#d1fae5;color:#065f46;font-size:0.7rem;font-weight:700;">
                    <svg style="width:0.7rem;height:0.7rem;" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    Selesai
                </span>
                @else
                <span style="display:inline-flex;align-items:center;gap:0.25rem;padding:0.2rem 0.6rem;border-radius:9999px;
                             background:#fef9c3;color:#713f12;font-size:0.7rem;font-weight:700;">
                    Draft
                </span>
                @endif
            </div>

            {{-- ─── BLOK 3A ─── --}}
            @if($blockKey === 'blok3a')
            @php
                $hProducts = $hResp->blok3a_products ?? [];
                $hLainnya  = $hResp->blok3a_lainnya ?? [];
                $hTotals   = $hResp->blok3a_totals ?? [];
            @endphp

            @if(empty($hProducts) && empty($hLainnya) && empty($hTotals))
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p style="font-size:0.85rem;margin:0;">Tidak ada data Blok IIIA untuk periode ini.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:1rem;">

                {{-- Products --}}
                @foreach($hProducts as $hPIdx => $hProduct)
                @php $hPName = $hProduct['jenis_barang'] ?? ($hProduct['name'] ?? ('Produk ' . ($hPIdx + 1))); @endphp
                <div style="border:1px solid #e5e7eb;border-radius:0.625rem;overflow:hidden;">
                    <div style="padding:0.5rem 0.875rem;background:#eff6ff;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;">
                        <span style="font-size:0.7rem;font-weight:700;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.04em;">
                            301.{{ $hPIdx + 1 }} — {{ $hPName }}
                        </span>
                        @if(!empty($hProduct['satuan']))
                        <span style="font-size:0.7rem;color:#6b7280;">Satuan: {{ $hProduct['satuan'] }}</span>
                        @endif
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.75rem;min-width:480px;">
                            <thead>
                                <tr style="background:#f9fafb;">
                                    <th style="text-align:left;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;width:5.5rem;">Bulan</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Banyaknya</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Nilai (Rp)</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Harga/Satuan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($histMonthKeys as $mIdx => $mKey)
                                @php
                                    $hQty   = $hProduct['banyaknya'][$mKey] ?? null;
                                    $hNilai = $hProduct['nilai'][$mKey] ?? null;
                                    $hHarga = ($hQty && (float)$hQty > 0 && $hNilai !== null)
                                                ? ((float)$hNilai / (float)$hQty)
                                                : ($hProduct['harga_satuan'][$mKey] ?? null);
                                    $hRowBg = $mIdx % 2 === 0 ? '#ffffff' : '#f9fafb';
                                @endphp
                                <tr style="background:{{ $hRowBg }};">
                                    <td style="padding:0.35rem 0.75rem;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6;">{{ $histMonthLabels[$mIdx] }}</td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#374151;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ $hQty !== null ? number_format((float)$hQty, 0, ',', '.') : '—' }}
                                    </td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#374151;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ $hNilai !== null ? number_format((float)$hNilai, 0, ',', '.') : '—' }}
                                    </td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#374151;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ $hHarga !== null ? number_format((float)$hHarga, 2, ',', '.') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endforeach

                {{-- Lainnya --}}
                @if(!empty($hLainnya))
                <div style="border:1px solid #fde68a;border-radius:0.625rem;overflow:hidden;">
                    <div style="padding:0.5rem 0.875rem;background:#fef9c3;border-bottom:1px solid #fde68a;">
                        <span style="font-size:0.7rem;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:0.04em;">302 — Lainnya</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.75rem;min-width:300px;">
                            <thead>
                                <tr style="background:#f9fafb;">
                                    <th style="text-align:left;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Bulan</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Nilai (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($histMonthKeys as $mIdx => $mKey)
                                <tr style="background:{{ $mIdx % 2 === 0 ? '#ffffff' : '#f9fafb' }};">
                                    <td style="padding:0.35rem 0.75rem;color:#374151;font-weight:500;border-bottom:1px solid #f3f4f6;">{{ $histMonthLabels[$mIdx] }}</td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#374151;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ isset($hLainnya['nilai'][$mKey]) && $hLainnya['nilai'][$mKey] !== null ? number_format((float)$hLainnya['nilai'][$mKey], 0, ',', '.') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Totals --}}
                @if(!empty($hTotals))
                <div style="border:1px solid #bbf7d0;border-radius:0.625rem;overflow:hidden;">
                    <div style="padding:0.5rem 0.875rem;background:#f0fdf4;border-bottom:1px solid #bbf7d0;">
                        <span style="font-size:0.7rem;font-weight:700;color:#065f46;text-transform:uppercase;letter-spacing:0.04em;">303 — Total Pendapatan</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;font-size:0.75rem;min-width:300px;">
                            <thead>
                                <tr style="background:#f9fafb;">
                                    <th style="text-align:left;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Bulan</th>
                                    <th style="text-align:right;padding:0.4rem 0.75rem;color:#6b7280;font-weight:600;border-bottom:1px solid #e5e7eb;">Total Nilai (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($histMonthKeys as $mIdx => $mKey)
                                <tr style="background:{{ $mIdx % 2 === 0 ? '#ffffff' : '#f0fdf4' }};font-weight:500;">
                                    <td style="padding:0.35rem 0.75rem;color:#374151;border-bottom:1px solid #f3f4f6;">{{ $histMonthLabels[$mIdx] }}</td>
                                    <td style="padding:0.35rem 0.75rem;text-align:right;color:#065f46;font-variant-numeric:tabular-nums;border-bottom:1px solid #f3f4f6;">
                                        {{ isset($hTotals[$mKey]) && $hTotals[$mKey] !== null ? number_format((float)$hTotals[$mKey], 0, ',', '.') : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                </div>{{-- end flex column --}}
            @endif

            {{-- ─── BLOK 3B INDUSTRI ─── --}}
            @endif
            @if($blockKey === 'blok3b_industri')
            @php $hData = $hResp->blok3b_industri_data ?? []; @endphp

            @if(empty($hData))
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p style="font-size:0.85rem;margin:0;">Tidak ada data Blok IIIB Industri untuk periode ini.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:1rem;">
                @foreach($hist3bIGroups as $hGroup)
                @php
                    $hC = $histGroupColors[$hGroup['color']] ?? $histGroupColors['blue'];
                    $hHasVal = false;
                    foreach ($hGroup['rows'] as $hRow) {
                        $v = $hData[$hRow['key']] ?? null;
                        if ($v !== null && $v !== '') { $hHasVal = true; break; }
                    }
                @endphp
                @if($hHasVal)
                <div class="{{ $hC['wrap'] }}" style="border:1px solid;border-radius:0.625rem;overflow:hidden;">
                    <div class="{{ $hC['head'] }}" style="padding:0.5rem 0.875rem;border-bottom:1px solid;border-color:inherit;">
                        <span class="{{ $hC['title'] }}" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">{{ $hGroup['title'] }}</span>
                    </div>
                    <div>
                        @foreach($hGroup['rows'] as $hRow)
                        @php
                            $hVal = $hData[$hRow['key']] ?? null;
                            if ($hRow['type'] === 'currency') {
                                $hDisp = ($hVal !== null && $hVal !== '') ? 'Rp ' . number_format((float)$hVal, 0, ',', '.') : null;
                            } elseif ($hRow['type'] === 'percent') {
                                $hDisp = ($hVal !== null && $hVal !== '') ? number_format((float)$hVal, 2, ',', '.') . '%' : null;
                            } else {
                                $hDisp = ($hVal !== null && $hVal !== '') ? $hVal : null;
                            }
                        @endphp
                        @if($hDisp !== null)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;
                                    padding:0.55rem 0.875rem;border-bottom:1px solid #f3f4f6;">
                            <span style="font-size:0.75rem;color:#4b5563;flex:1;min-width:0;">{{ $hRow['label'] }}</span>
                            <span style="font-size:0.775rem;font-weight:700;color:#111827;flex-shrink:0;font-variant-numeric:tabular-nums;">{{ $hDisp }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
                </div>
            @endif

            {{-- ─── BLOK 3A-2 ─── --}}
            @endif
            @if($blockKey === 'blok3a2')
            @php $hMats = $hResp->blok3a2_materials ?? []; @endphp
            @if(empty($hMats))
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p style="font-size:0.85rem;margin:0;">Tidak ada data Blok IIIA-2 untuk periode ini.</p>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.75rem;min-width:600px;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:center;color:#6b7280;">No.</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:left;color:#6b7280;">Nama Bahan</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:center;color:#6b7280;">Satuan</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:right;background:#fef9c3;color:#92400e;">DN Banyaknya</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:right;background:#fef9c3;color:#92400e;">DN Nilai (Rp)</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:right;background:#dbeafe;color:#1e40af;">LN Banyaknya</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:right;background:#dbeafe;color:#1e40af;">LN Nilai (Rp)</th>
                                <th style="padding:0.45rem 0.6rem;border:1px solid #e5e7eb;text-align:center;color:#6b7280;">Negara Asal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($hMats as $hMi => $hMat)
                            <tr style="{{ $hMi % 2 === 1 ? 'background:#fafafa;' : '' }}">
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:center;">{{ $hMi + 1 }}</td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;"><strong>{{ $hMat['nama_bahan'] ?? '' }}</strong></td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:center;">{{ $hMat['satuan_standar'] ?? '' }}</td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:right;">
                                    {{ isset($hMat['dn_banyaknya']) && $hMat['dn_banyaknya'] !== '' ? number_format((int)$hMat['dn_banyaknya'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:right;">
                                    {{ isset($hMat['dn_nilai']) && $hMat['dn_nilai'] !== '' ? number_format((int)$hMat['dn_nilai'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:right;">
                                    {{ isset($hMat['ln_banyaknya']) && $hMat['ln_banyaknya'] !== '' ? number_format((int)$hMat['ln_banyaknya'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:right;">
                                    {{ isset($hMat['ln_nilai']) && $hMat['ln_nilai'] !== '' ? number_format((int)$hMat['ln_nilai'], 0, ',', '.') : '-' }}
                                </td>
                                <td style="padding:0.4rem 0.6rem;border:1px solid #e5e7eb;text-align:center;">{{ $hMat['negara_asal'] ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ─── BLOK 3B NON-INDUSTRI ─── --}}
            @endif
            @if($blockKey === 'blok3b_nonindustri')
            @php $hData = $hResp->blok3b_nonindustri_data ?? []; @endphp

            @if(empty($hData))
                <div style="text-align:center;padding:3rem 1rem;color:#9ca3af;">
                    <svg style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;opacity:0.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p style="font-size:0.85rem;margin:0;">Tidak ada data Blok IIIB Non-Industri untuk periode ini.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:1rem;">
                @foreach($hist3bNGroups as $hGroup)
                @php
                    $hC = $histGroupColors[$hGroup['color']] ?? $histGroupColors['blue'];
                    $hHasVal = false;
                    foreach ($hGroup['rows'] as $hRow) {
                        $v = $hData[$hRow['key']] ?? null;
                        if ($v !== null && $v !== '') { $hHasVal = true; break; }
                    }
                @endphp
                @if($hHasVal)
                <div class="{{ $hC['wrap'] }}" style="border:1px solid;border-radius:0.625rem;overflow:hidden;">
                    <div class="{{ $hC['head'] }}" style="padding:0.5rem 0.875rem;border-bottom:1px solid;border-color:inherit;">
                        <span class="{{ $hC['title'] }}" style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;">{{ $hGroup['title'] }}</span>
                    </div>
                    <div>
                        @foreach($hGroup['rows'] as $hRow)
                        @php
                            $hVal = $hData[$hRow['key']] ?? null;
                            if ($hRow['type'] === 'currency') {
                                $hDisp = ($hVal !== null && $hVal !== '') ? 'Rp ' . number_format((float)$hVal, 0, ',', '.') : null;
                            } elseif ($hRow['type'] === 'percent') {
                                $hDisp = ($hVal !== null && $hVal !== '') ? number_format((float)$hVal, 2, ',', '.') . '%' : null;
                            } else {
                                $hDisp = ($hVal !== null && $hVal !== '') ? $hVal : null;
                            }
                        @endphp
                        @if($hDisp !== null)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;
                                    padding:0.55rem 0.875rem;border-bottom:1px solid #f3f4f6;">
                            <span style="font-size:0.75rem;color:#4b5563;flex:1;min-width:0;">{{ $hRow['label'] }}</span>
                            <span style="font-size:0.775rem;font-weight:700;color:#111827;flex-shrink:0;font-variant-numeric:tabular-nums;">{{ $hDisp }}</span>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
                </div>
            @endif
            @endif

        </div>{{-- end .hist-period-panel --}}

        @empty
        <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:4rem 1.5rem;color:#9ca3af;">
            <svg style="width:3.5rem;height:3.5rem;margin-bottom:1rem;opacity:0.3;" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p style="font-size:0.9rem;font-weight:600;margin:0;">Tidak ada data historis</p>
            <p style="font-size:0.8rem;margin:0.35rem 0 0;text-align:center;">Belum ada periode sebelumnya yang tersimpan untuk akun ini.</p>
        </div>
        @endforelse

    </div>{{-- end scrollable --}}

    {{-- ── Footer ── --}}
    <div style="flex-shrink:0;padding:0.75rem 1.25rem;border-top:1px solid #e5e7eb;background:#f9fafb;text-align:center;">
        <p style="margin:0;font-size:0.7rem;color:#9ca3af;">
            Data di atas hanya untuk perbandingan referensi dan tidak dapat diedit dari panel ini.
        </p>
    </div>

</aside>

{{-- ──────────────────────────────────────────────────────── --}}
{{-- JAVASCRIPT                                              --}}
{{-- ──────────────────────────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    function openHistDrawer() {
        var drawer   = document.getElementById('hist-drawer');
        var backdrop = document.getElementById('hist-backdrop');
        if (!drawer || !backdrop) return;

        backdrop.style.display = 'block';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                backdrop.style.opacity = '1';
                drawer.style.transform = 'translateX(0)';
            });
        });
        document.body.style.overflow = 'hidden';
        drawer.focus && drawer.focus();
    }

    function closeHistDrawer() {
        var drawer   = document.getElementById('hist-drawer');
        var backdrop = document.getElementById('hist-backdrop');
        if (!drawer || !backdrop) return;

        drawer.style.transform = 'translateX(100%)';
        backdrop.style.opacity = '0';
        setTimeout(function () {
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
        }, 320);

        var btn = document.getElementById('hist-open-btn');
        if (btn) btn.focus();
    }

    function switchHistPeriod(idx) {
        document.querySelectorAll('.hist-period-panel').forEach(function (p) {
            p.style.display = 'none';
        });
        var target = document.querySelector('.hist-period-panel[data-period-idx="' + idx + '"]');
        if (target) target.style.display = 'block';

        document.querySelectorAll('.hist-period-tab').forEach(function (tab) {
            var isActive = parseInt(tab.getAttribute('data-period-idx'), 10) === idx;
            if (isActive) {
                tab.style.background  = '#2563eb';
                tab.style.color       = '#fff';
                tab.style.borderColor = '#2563eb';
                tab.style.boxShadow   = '0 1px 4px rgba(37,99,235,0.35)';
            } else {
                tab.style.background  = '#fff';
                tab.style.color       = '#374151';
                tab.style.borderColor = '#d1d5db';
                tab.style.boxShadow   = 'none';
            }
        });
    }

    /* Keyboard: Escape closes drawer */
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var drawer = document.getElementById('hist-drawer');
            if (drawer && drawer.style.transform !== 'translateX(100%)') {
                closeHistDrawer();
            }
        }
    });

    /* Hover effect for open button */
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('hist-open-btn');
        if (btn) {
            btn.addEventListener('mouseenter', function () {
                btn.style.boxShadow = '0 6px 20px rgba(37,99,235,0.55)';
                btn.style.transform = 'translateY(-1px)';
            });
            btn.addEventListener('mouseleave', function () {
                btn.style.boxShadow = '0 4px 14px rgba(37,99,235,0.45)';
                btn.style.transform = 'translateY(0)';
            });
        }
    });

    window.openHistDrawer   = openHistDrawer;
    window.closeHistDrawer  = closeHistDrawer;
    window.switchHistPeriod = switchHistPeriod;
}());
</script>
