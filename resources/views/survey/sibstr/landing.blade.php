@extends('layouts.user-dashboard')

@section('title', 'Survei SIBSTR — Panduan Pengisian')

@section('dashboard-content')

  {{-- Mobile sidebar toggle --}}
  <div class="lg:hidden mb-4">
    <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400"
            type="button" data-open-sidebar aria-controls="dashboard-sidebar" aria-expanded="false">
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
      Menu
    </button>
  </div>

  {{-- ── Page Header ── --}}
  <div class="ud-page-header">
    <div class="ud-page-header-content">
      <h1 class="ud-page-title">Survei SIBSTR</h1>
      <p class="ud-page-description">Panduan pengisian Survei Industri Besar &amp; Sedang Triwulanan</p>
    </div>
    @if(!empty($arsipYears))
    <a href="#arsip-survei"
       class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
      </svg>
      Arsip Survei
    </a>
    @endif
  </div>

  @if(session('error'))
  <div class="mt-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-300 text-sm flex items-start gap-2">
    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
    {{ session('error') }}
  </div>
  @endif

  @if(session('success'))
  <div class="mt-4 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-300 text-sm flex items-start gap-2">
    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
  </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- STEPPER                                               --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  <div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-8 py-6">
    <div class="relative flex items-start justify-between max-w-sm mx-auto">

      {{-- Connector line (sits behind the bubbles) --}}
      <div class="absolute top-4 left-10 right-10 h-0.5
        {{ $annualDone ? 'bg-green-400' : 'bg-gray-200 dark:bg-gray-700' }}">
      </div>

      {{-- Step 1 --}}
      <div class="relative flex flex-col items-center text-center w-20">
        @if($annualDone)
          <div class="w-9 h-9 rounded-full bg-green-500 shadow-md flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <p class="mt-2 text-xs font-bold text-gray-800 dark:text-gray-200 leading-tight">Tahunan<br>{{ $annualYear }}</p>
          <span class="mt-1 text-[10px] font-semibold text-green-600 dark:text-green-400">Selesai</span>
        @elseif($annualInProgress)
          <div class="w-9 h-9 rounded-full bg-blue-500 shadow-md flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
            <span class="text-white font-bold text-sm">1</span>
          </div>
          <p class="mt-2 text-xs font-bold text-gray-800 dark:text-gray-200 leading-tight">Tahunan<br>{{ $annualYear }}</p>
          <span class="mt-1 text-[10px] font-semibold text-yellow-600 dark:text-yellow-400">Draft</span>
        @else
          <div class="w-9 h-9 rounded-full bg-blue-600 shadow-md flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
            <span class="text-white font-bold text-sm">1</span>
          </div>
          <p class="mt-2 text-xs font-bold text-gray-800 dark:text-gray-200 leading-tight">Tahunan<br>{{ $annualYear }}</p>
          <span class="mt-1 text-[10px] text-gray-400 dark:text-gray-500">Belum dimulai</span>
        @endif
      </div>

      {{-- Step 2 --}}
      <div class="relative flex flex-col items-center text-center w-20">
        <div class="w-9 h-9 rounded-full bg-blue-600 shadow-md flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
          <span class="text-white font-bold text-sm">2</span>
        </div>
        <p class="mt-2 text-xs font-bold text-gray-800 dark:text-gray-200 leading-tight">Triwulanan<br>{{ $triwulanYear }}</p>
        <span class="mt-1 text-[10px] font-semibold text-blue-600 dark:text-blue-400">Terbuka</span>
      </div>

    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- STEP 1 — SURVEI TAHUNAN                               --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  <div class="mt-5 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">

    {{-- Colored left border via a flex wrapper --}}
    <div class="flex">
      <div class="w-1 shrink-0 rounded-l-2xl
        {{ $annualDone
          ? 'bg-green-500'
          : ($annualInProgress ? 'bg-yellow-400' : 'bg-blue-500') }}">
      </div>

      <div class="flex-1 min-w-0 p-6 sm:p-8">

        {{-- Header row --}}
        <div class="flex items-start justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-3 min-w-0">
            {{-- Step number badge --}}
            <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm
              {{ $annualDone
                ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400'
                : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400' }}">
              @if($annualDone)
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
              @else
                01
              @endif
            </div>
            <div>
              <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Langkah 1</p>
              <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Survei Tahunan {{ $annualYear }}</h2>
            </div>
          </div>

          {{-- Status pill --}}
          @if($annualDone)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 border border-green-200 dark:border-green-800">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
              </svg>
              Selesai
            </span>
          @elseif($annualInProgress)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-yellow-50 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800">
              <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 inline-block"></span>
              Belum Selesai
            </span>
          @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 border border-gray-200 dark:border-gray-600">
              Belum Dimulai
            </span>
          @endif
        </div>

        {{-- Description --}}
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
          Formulir Blok I–VI mencakup identitas perusahaan, tenaga kerja, bahan baku, produksi, energi, dan pendapatan selama tahun {{ $annualYear }}.
        </p>

        {{-- Company info block --}}
        @if($annualResponse)
          <div class="mt-5 rounded-xl bg-gray-50 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700 p-4">
            <div class="grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));">
              @if(!empty($annualResponse->nama_perusahaan))
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Perusahaan</p>
                  <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $annualResponse->nama_perusahaan }}</p>
                </div>
              @endif
              @if(!empty($annualResponse->kabupaten_kota))
                <div>
                  <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Kabupaten / Kota</p>
                  <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $annualResponse->kabupaten_kota }}</p>
                </div>
              @endif
              <div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">Terakhir Disimpan</p>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                  {{ $annualResponse->last_saved_at ? $annualResponse->last_saved_at->format('d M Y, H:i') : '—' }}
                </p>
              </div>
            </div>
          </div>
        @endif

        {{-- Actions --}}
        <div class="mt-6 flex flex-wrap items-center gap-3">
          @if($annualDone)
            <a href="{{ route('survey.sibstr.edit.blok1', ['year' => $annualYear, 'period' => 'tahunan']) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Edit Survei
            </a>
            <a href="{{ route('dashboard.surveys.sibstr.download-certificate') . '?tahun=' . $annualYear }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold border border-gray-200 dark:border-gray-600 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              Unduh Bukti
            </a>
          @elseif($annualInProgress)
            <a href="{{ route('survey.sibstr.blok1', ['year' => $annualYear, 'period' => 'tahunan']) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
              Lanjutkan Pengisian
            </a>
            <p class="text-xs text-gray-400">Lanjut dari Blok I</p>
          @else
            <a href="{{ route('survey.sibstr.blok1', ['year' => $annualYear, 'period' => 'tahunan']) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition-colors shadow-sm">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Mulai Isi Survei Tahunan
            </a>
            <p class="text-xs text-gray-400">Blok I–VI · ±30–45 menit</p>
          @endif
        </div>
      </div>
    </div>

  </div>

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- STEP 2 — SURVEI TRIWULANAN                            --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  <div class="mt-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden">

    <div class="flex">
      <div class="w-1 shrink-0 rounded-l-2xl bg-indigo-500"></div>

      <div class="flex-1 min-w-0 p-6 sm:p-8">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-3 flex-wrap">
          <div class="flex items-center gap-3 min-w-0">
            <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center font-bold text-sm
              bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400">
              02
            </div>
            <div>
              <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Langkah 2</p>
              <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">
                Survei Triwulanan {{ $triwulanYear }}
              </h2>
            </div>
          </div>
        </div>

        <p class="mt-3 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
          Data produksi, tenaga kerja, dan kegiatan usaha per kuartal sepanjang {{ $triwulanYear }}.
        </p>

        {{-- ── TW cards ──────────────────────────── --}}
        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
          @foreach($triwulanCards as $card)
            @php
              $tw                = $card['triwulan'];
              $resp              = $card['response'];
              $completed         = $card['is_completed'];
              $inProgress        = $card['is_in_progress'];
              $available         = $card['is_available'];
              $locked            = $card['is_locked'];
              $romans            = ['I','II','III','IV'];
              $surveyUrl         = route('survey.sibstr.blok1', ['year' => $triwulanYear, 'period' => $tw]);
              $editUrl           = route('survey.sibstr.edit.blok1', ['year' => $triwulanYear, 'period' => $tw]);
            @endphp

            <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 flex flex-col
              {{ $locked ? 'opacity-50' : '' }}">
              <div class="p-4 flex-1 flex flex-col">

                {{-- TW header --}}
                <div class="flex items-center justify-between mb-3">
                  <div>
                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200">Triwulan {{ $romans[$tw - 1] }}</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $card['label'] }}</p>
                  </div>
                  @if($completed)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                      </svg>
                      Selesai
                    </span>
                  @elseif($inProgress)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">Draft</span>
                  @elseif($available)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Tersedia</span>
                  @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500">Belum buka</span>
                  @endif
                </div>

                @if($resp && $resp->last_saved_at)
                  <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-3">
                    {{ $resp->last_saved_at->format('d M Y') }}
                  </p>
                @endif

                {{-- Action button --}}
                <div class="mt-auto">
                  @if($completed)
                    <div class="flex gap-2">
                      <a href="{{ $editUrl }}"
                         class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-colors">
                        Edit
                      </a>
                      <a href="{{ route('dashboard.surveys.sibstr.download-certificate') . '?tahun=' . $triwulanYear . '&triwulan=' . $tw }}"
                         class="flex items-center justify-center px-2.5 py-2 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition-colors" title="Unduh Bukti">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                      </a>
                    </div>
                  @elseif($inProgress)
                    <a href="{{ $surveyUrl }}"
                       class="flex items-center justify-center gap-1.5 w-full py-2 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M12 5l7 7-7 7"/>
                      </svg>
                      Lanjutkan
                    </a>
                  @elseif($available)
                    <a href="{{ $surveyUrl }}"
                       class="flex items-center justify-center gap-1.5 w-full py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-colors">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      Mulai Isi
                    </a>
                  @else
                    <button disabled class="flex items-center justify-center gap-1.5 w-full py-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500 text-xs font-bold cursor-not-allowed border border-dashed border-gray-200 dark:border-gray-600">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                      </svg>
                      Belum tersedia
                    </button>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>

      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════ --}}
  {{-- ARSIP — periode lain yang pernah diisi                  --}}
  {{-- ═══════════════════════════════════════════════════════ --}}
  @if(!empty($arsipYears))
  <div id="arsip-survei" class="mt-4 rounded-2xl border border-gray-100 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden" style="scroll-margin-top:5rem;">
    <div class="flex">
      <div class="w-1 shrink-0 rounded-l-2xl bg-gray-300 dark:bg-gray-600"></div>

      <div class="flex-1 min-w-0 p-6 sm:p-8">
        <div class="flex items-center gap-3 min-w-0">
          <div class="shrink-0 w-10 h-10 rounded-xl flex items-center justify-center bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div>
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Arsip</p>
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100">Periode Lain yang Pernah Diisi</h2>
          </div>
        </div>

        <p class="mt-3 text-sm leading-relaxed text-gray-500 dark:text-gray-400">
          Semua periode SIBSTR di luar dua langkah di atas. Buka untuk meninjau atau memperbaiki data,
          atau unduh bukti pengisian untuk periode yang sudah selesai.
        </p>

        @foreach($arsipYears as $arsipTahun => $arsipRows)
        <div class="mt-5">
          <p class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">Tahun {{ $arsipTahun }}</p>
          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
            @foreach($arsipRows as $arsipRow)
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30 p-4 flex flex-col">
              <div class="flex items-center justify-between gap-2 mb-2">
                <p class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $arsipRow['label'] }}</p>
                @if($arsipRow['is_completed'])
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    Selesai
                  </span>
                @else
                  <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">Draft</span>
                @endif
              </div>

              <p class="text-[11px] text-gray-400 dark:text-gray-500 mb-3">
                {{ $arsipRow['last_saved_at'] ? $arsipRow['last_saved_at']->format('d M Y') : 'Belum tersimpan' }}
              </p>

              <div class="mt-auto flex gap-2">
                <a href="{{ route($arsipRow['is_completed'] ? 'survey.sibstr.edit.blok1' : 'survey.sibstr.blok1', ['year' => $arsipRow['tahun'], 'period' => $arsipRow['period']]) }}"
                   class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-600 text-xs font-bold transition-colors">
                  {{ $arsipRow['is_completed'] ? 'Tinjau / Edit' : 'Lanjutkan' }}
                </a>
                @if($arsipRow['is_completed'])
                <a href="{{ route('dashboard.surveys.sibstr.download-certificate') . '?tahun=' . $arsipRow['tahun'] . '&triwulan=' . $arsipRow['triwulan'] }}"
                   class="flex items-center justify-center px-2.5 py-2 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition-colors" title="Unduh Bukti">
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                  </svg>
                </a>
                @endif
              </div>
            </div>
            @endforeach
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif

  <div class="h-8"></div>

@endsection
