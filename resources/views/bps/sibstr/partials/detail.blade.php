{{--
  Shared read-only SIBSTR submission view — used by the BPS detail page and the
  Mitra detail page so the two can't drift apart. Styling lives in
  public/css/sibstr-detail.css; the block content comes from bps/sibstr/data/*,
  the same partials the PDF renders.

  Expects: $surveyResponse, $showBlocks, $blok2Visibility, $bpsRiData,
           $jenisKawasanOptions, $kbliPrefix
  Pass in: 'downloadUrl', 'backUrl', 'backLabel'
--}}
@php
    use App\Support\SibstrFormat as F;

    $isTahunanRecord  = ((int) ($surveyResponse->triwulan ?? 0)) === 0;
    $periodLabel      = $isTahunanRecord
                            ? 'Tahunan ' . ($surveyResponse->tahun ?? '')
                            : \App\Models\SurveyResponse::triwulanLabel((int) $surveyResponse->triwulan) . ' ' . ($surveyResponse->tahun ?? '');
    $isFinishedRecord = $isTahunanRecord
                            ? ($surveyResponse->annual_survey_status === 'FINISH_SURVEY')
                            : (bool) $surveyResponse->is_completed;

    // Ordered list of blocks that apply to this submission. Keys match both the
    // section anchors and the partial names in bps/sibstr/data/.
    $sections = array_values(array_filter([
        ['key' => 'blok1',  'no' => 'I',    'title' => 'Keterangan Umum',        'sub' => 'Identitas & legalisasi perusahaan',            'show' => (bool) ($showBlocks['blok1'] ?? true)],
        ['key' => 'blok2',  'no' => 'II',   'title' => 'Keterangan Perusahaan',  'sub' => 'Kondisi, jaringan unit, KBLI & tenaga kerja',  'show' => (bool) ($showBlocks['blok2'] ?? true)],
        ['key' => 'blok3a', 'no' => 'IIIA', 'title' => 'Barang yang Diproduksi', 'sub' => 'Produksi & pendapatan per bulan',              'show' => !empty($showBlocks['blok3a'])],
        ['key' => 'blok3b', 'no' => 'IIIB',
         'title' => 'Pendapatan & Pengeluaran'
                    . (!empty($showBlocks['blok3bIndustri']) ? ' (Industri)' : (!empty($showBlocks['blok3bNonIndustri']) ? ' (Non-Industri)' : '')),
         'sub' => 'Pendapatan, persediaan dan pengeluaran',                                                                              'show' => !empty($showBlocks['blok3bIndustri']) || !empty($showBlocks['blok3bNonIndustri'])],
        ['key' => 'blok3c', 'no' => 'IIIC', 'title' => 'Bahan Baku & Penolong',  'sub' => 'Pemakaian DN/LN, aset, modal & prospek',       'show' => !empty($showBlocks['blok3c'])],
        ['key' => 'blok4',  'no' => 'IV',   'title' => 'Fenomena & Catatan',     'sub' => 'Peristiwa penting per triwulan',               'show' => !empty($showBlocks['blok4'])],
        ['key' => 'blok5',  'no' => 'V',    'title' => 'Kondisi & Prospek',      'sub' => 'Indikator naik / tetap / turun',               'show' => !empty($showBlocks['blok5'])],
        ['key' => 'blok6',  'no' => 'VI',   'title' => 'Catatan',                'sub' => 'Catatan tambahan responden',                   'show' => !empty($showBlocks['blok6'])],
    ], fn ($s) => $s['show']));
@endphp

<div class="sd-layout">

    {{-- ═══════════════════════ MAIN ═══════════════════════ --}}
    <div class="sd-main">

        <div class="sd-head">
            <div class="sd-head-text">
                <h1>Detail Survei SIBSTR</h1>
                <p class="sd-company">{{ $surveyResponse->nama_perusahaan ?: 'Nama perusahaan belum diisi' }}</p>
                <div class="sd-meta">
                    <div>
                        <span class="sd-meta-label">Periode</span>
                        <span class="sd-meta-value">{{ $periodLabel }}</span>
                    </div>
                    <div>
                        <span class="sd-meta-label">Pengguna</span>
                        <span class="sd-meta-value">{{ $surveyResponse->user->name ?? '—' }}</span>
                        <span class="sd-meta-note">{{ $surveyResponse->user->email ?? '' }}</span>
                    </div>
                    <div>
                        <span class="sd-meta-label">Terakhir Diperbarui</span>
                        <span class="sd-meta-value">
                            {{ $surveyResponse->updated_at ? $surveyResponse->updated_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '—' }}
                        </span>
                    </div>
                    @if($surveyResponse->kip)
                    <div>
                        <span class="sd-meta-label">KIP</span>
                        <span class="sd-meta-value">{{ $surveyResponse->kip }}</span>
                    </div>
                    @endif
                    @if($surveyResponse->idsbr)
                    <div>
                        <span class="sd-meta-label">IDSBR</span>
                        <span class="sd-meta-value">{{ $surveyResponse->idsbr }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="sd-head-side">
                @if($isFinishedRecord)
                    <span class="sd-badge done">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Selesai
                    </span>
                @else
                    <span class="sd-badge wip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Dalam Proses
                    </span>
                @endif

                <div class="sd-actions">
                    <a href="{{ $downloadUrl }}" class="sd-btn primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                        Unduh PDF
                    </a>
                    <a href="{{ $backUrl }}" class="sd-btn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        {{ $backLabel }}
                    </a>
                </div>
            </div>
        </div>

        <p class="sd-viewnote">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Mode tampilan baca-saja &mdash; menampilkan {{ count($sections) }} blok yang relevan untuk submisi ini.
        </p>

        @foreach($sections as $section)
        <section class="sd-card" id="section-{{ $section['key'] }}">
            <header class="sd-card-head">
                <span class="sd-card-no">{{ $section['no'] }}</span>
                <span class="sd-card-title">
                    Blok {{ $section['no'] }} &mdash; {{ $section['title'] }}
                    <small>{{ $section['sub'] }}</small>
                </span>
            </header>
            <div class="sd-card-body">
                @switch($section['key'])
                    @case('blok3b')
                        @include('bps.sibstr.data.blok3b', [
                            'showIndustri'    => !empty($showBlocks['blok3bIndustri']),
                            'showNonIndustri' => !empty($showBlocks['blok3bNonIndustri']),
                        ])
                        @break
                    @default
                        @include('bps.sibstr.data.' . $section['key'])
                @endswitch
            </div>
        </section>
        @endforeach

        <div class="sd-foot">
            <a href="{{ $backUrl }}" class="sd-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ $backLabel }}
            </a>
            <a href="{{ $downloadUrl }}" class="sd-btn primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                Unduh PDF Data
            </a>
        </div>
    </div>

    {{-- ═══════════════════════ TOC ═══════════════════════ --}}
    <nav class="sd-toc" id="sd-toc" aria-label="Daftar isi blok">
        <div class="sd-toc-head">Daftar Blok</div>
        <ul>
            @foreach($sections as $i => $section)
            <li>
                <a href="#section-{{ $section['key'] }}"
                   class="sd-toc-link {{ $i === 0 ? 'active' : '' }}"
                   data-section="section-{{ $section['key'] }}">
                    <span class="dot"></span>
                    Blok {{ $section['no'] }} &middot; {{ $section['title'] }}
                </a>
            </li>
            @endforeach
        </ul>
    </nav>
</div>

<div class="sd-toc-overlay" id="sd-toc-overlay"></div>

<button class="sd-toc-fab" id="sd-toc-fab" type="button" title="Daftar Blok" aria-label="Buka daftar blok">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
</button>

<button class="sd-top" id="sd-top" type="button" title="Kembali ke atas" aria-label="Kembali ke atas">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
</button>
