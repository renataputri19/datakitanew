{{--
  Compact toolbar that replaces the old blue gradient .survey-header block.
  Shows the period chip plus whichever comparison drawer this page provides.
  Both buttons are the same triggers the old header used (openRefDrawer /
  openHistDrawer), so the drawer scripts stay untouched.

  Optional: 'instruction' — a short petunjuk line shown under the toolbar.
--}}
@php
    $_tbTw   = (int) ($triwulan ?? 0);
    $_tbHasRef  = isset($referenceResponse) && $referenceResponse && $_tbTw !== 1;
    $_tbHasHist = !empty($historicalResponses)
                  && (!($historicalResponses instanceof \Illuminate\Support\Collection) || $historicalResponses->isNotEmpty())
                  && $_tbTw !== 1;
@endphp

@if($_tbTw > 0 || $_tbHasRef || $_tbHasHist)
<div class="sibstr-toolbar">
    @if($_tbTw > 0)
    <span class="sibstr-period-chip">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        Mengisi untuk {{ \App\Models\SurveyResponse::triwulanLabel($_tbTw) }} {{ $tahun ?? '' }}
    </span>
    @endif

    @if($_tbHasRef)
    <button type="button" class="sibstr-toolbar-btn" onclick="openRefDrawer()"
            aria-label="Buka panel data referensi untuk perbandingan">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Lihat Data Referensi
    </button>
    @endif

    @if($_tbHasHist)
    <button type="button" class="sibstr-toolbar-btn" onclick="openHistDrawer()"
            aria-label="Buka panel data periode sebelumnya">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Lihat Data Historis
        <span class="inline-flex items-center justify-center min-w-[1.2rem] h-[1.2rem] px-1 rounded-full bg-amber-400 text-amber-900 text-[0.7rem] font-extrabold">
            {{ is_countable($historicalResponses) ? count($historicalResponses) : $historicalResponses->count() }}
        </span>
    </button>
    @endif
</div>
@endif

@if(!empty($instruction))
<div class="mb-4 flex items-start gap-2.5 px-3.5 py-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-800">
    <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-xs leading-relaxed text-blue-800 dark:text-blue-300">{!! $instruction !!}</p>
</div>
@endif
