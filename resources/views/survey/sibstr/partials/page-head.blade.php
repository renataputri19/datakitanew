{{--
  Shared chrome for every SIBSTR blok page: mobile menu, page header, period
  chip, edit-mode banner and the blok-navigation rail. Opens the two wrapper
  <div>s that survey.sibstr.partials.page-foot closes.

  Expects (from the parent view's scope): $surveyResponse, $tahun, $triwulan,
  $period, optionally $isEditMode.
  Pass in: 'blokTitle', 'blokSub'.
--}}
@php
    $_hTw      = (int) ($triwulan ?? 0);
    $_hTahunan = $_hTw === 0;
    $_hYear    = $tahun ?? ($surveyResponse->tahun ?? 2025);
    $_hPeriod  = $_hTahunan
        ? 'Tahunan ' . $_hYear
        : \App\Models\SurveyResponse::triwulanLabel($_hTw) . ' ' . $_hYear;
@endphp

<div class="lg:hidden mb-4 flex items-center justify-between gap-3">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400" type="button" data-open-sidebar>
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
    Menu
  </button>
  <button class="flex items-center gap-1.5 text-xs font-semibold text-blue-600 dark:text-blue-400" type="button" data-open-blok-nav>
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
    Daftar Blok
  </button>
</div>

<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">{{ $blokTitle ?? 'Survei SIBSTR' }}</h1>
    <p class="ud-page-description">
      SIBSTR &middot; {{ $_hPeriod }}@if(!empty($blokSub)) &middot; {{ $blokSub }}@endif
    </p>
  </div>
  <a href="{{ route('survey.sibstr.entry') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
    Kembali
  </a>
</div>

@if(!empty($isEditMode))
  <div class="mt-4">
    @include('survey.partials.edit-mode-banner', ['exitUrl' => route('survey.sibstr.entry')])
  </div>
@endif

<div class="flex gap-5 items-start mt-4">
@include('survey.sibstr.partials.sidebar')
<div class="flex-1 min-w-0 sibstr-page">
