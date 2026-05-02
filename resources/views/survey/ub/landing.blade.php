@extends('layouts.user-dashboard')

@section('title', 'Survei UB SE2026 — Pendataan Lengkap Usaha/Perusahaan')

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

{{-- Page Header --}}
<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">Survei UB — SE2026</h1>
    <p class="ud-page-description">Sensus Ekonomi 2026 — Pendataan Lengkap Usaha/Perusahaan (SE2026-L.UB)</p>
  </div>
</div>

@if(session('success'))
<div class="mt-4 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-300 text-sm flex items-start gap-2">
  <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
  {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mt-4 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-300 text-sm flex items-start gap-2">
  <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
  {{ session('error') }}
</div>
@endif

{{-- Info Banner --}}
<div class="mt-6 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-700 rounded-2xl px-6 py-4 text-sm text-blue-800 dark:text-blue-300">
  <div class="flex items-start gap-3">
    <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div>
      <p class="font-semibold mb-1">Tentang Survei UB SE2026</p>
      <p>Sensus Ekonomi 2026 bertujuan untuk menyediakan data dasar yang terpercaya untuk seluruh kegiatan ekonomi. Kerahasiaan data dijamin oleh UU No. 16 Tahun 1997 Pasal 21. Untuk bantuan: <strong>halose2026@bps.go.id</strong></p>
    </div>
  </div>
</div>

@php
  $blocks = [
    ['label' => 'Blok I-A', 'sub' => 'Identitas & Lokasi',       'route' => 'survey.ub.blok1a', 'done' => $response?->blok1a_completed],
    ['label' => 'Blok I-B', 'sub' => 'Kegiatan & Digital',       'route' => 'survey.ub.blok1b', 'done' => $response?->blok1b_completed],
    ['label' => 'Blok I-C', 'sub' => 'Sertifikasi & Kemitraan',  'route' => 'survey.ub.blok1c', 'done' => $response?->blok1c_completed],
    ['label' => 'Blok I-D', 'sub' => 'Pekerja & Keuangan',       'route' => 'survey.ub.blok1d', 'done' => $response?->blok1d_completed],
    ['label' => 'Blok II',  'sub' => 'Catatan',                   'route' => 'survey.ub.blok2',  'done' => $response?->blok2_completed],
    ['label' => 'Blok III', 'sub' => 'Keterangan Petugas',        'route' => 'survey.ub.blok3',  'done' => $response?->blok3_completed],
  ];
  $completedCount = collect($blocks)->filter(fn($b) => $b['done'])->count();
  $totalBlocks    = count($blocks);
  $pct            = $totalBlocks ? round($completedCount / $totalBlocks * 100) : 0;
  $isCompleted    = $response?->is_completed;
@endphp

{{-- Progress --}}
<div class="mt-6 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm px-6 py-5">
  <div class="flex items-center justify-between mb-2">
    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Progress Pengisian</span>
    <span class="text-sm font-bold {{ $isCompleted ? 'text-green-600' : 'text-blue-600' }}">{{ $pct }}%</span>
  </div>
  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
    <div class="h-2.5 rounded-full {{ $isCompleted ? 'bg-green-500' : 'bg-blue-500' }} transition-all duration-500" style="width: {{ $pct }}%"></div>
  </div>
  @if($isCompleted)
  <div class="mt-2 flex items-center justify-between flex-wrap gap-2">
    <p class="text-sm text-green-700 dark:text-green-400 font-semibold flex items-center gap-1">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
      Survei telah diselesaikan
    </p>
    <a href="{{ route('survey.ub.pdf.download') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-600 hover:bg-green-700 text-white shadow-sm transition">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Unduh PDF
    </a>
  </div>
  @else
  <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $completedCount }} dari {{ $totalBlocks }} bagian selesai</p>
  @endif
</div>

{{-- Block Status (read-only progress indicators) --}}
<div class="mt-4 grid grid-cols-3 sm:grid-cols-6 gap-2">
  @foreach($blocks as $blk)
  <div class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border
              {{ $blk['done'] ? 'border-green-200 bg-green-50 dark:border-green-700/50 dark:bg-green-950/20' : 'border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800' }}">
    @if($blk['done'])
    <div class="w-7 h-7 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
      <svg class="w-3.5 h-3.5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    @else
    <div class="w-7 h-7 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
      <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500">{{ $loop->index + 1 }}</span>
    </div>
    @endif
    <p class="text-[10px] font-bold text-center {{ $blk['done'] ? 'text-green-700 dark:text-green-300' : 'text-gray-500 dark:text-gray-400' }}">{{ $blk['label'] }}</p>
    <p class="text-[9px] text-center leading-tight {{ $blk['done'] ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-600' }}">{{ $blk['done'] ? 'Selesai' : 'Belum' }}</p>
  </div>
  @endforeach
</div>

{{-- Completed Banner with PDF Download --}}
@if($isCompleted)
<div class="mt-6 bg-green-50 dark:bg-green-950/30 border border-green-300 dark:border-green-700 rounded-2xl px-6 py-5">
  <div class="flex items-start gap-4">
    <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center flex-shrink-0">
      <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    <div class="flex-1 min-w-0">
      <h3 class="text-base font-bold text-green-800 dark:text-green-300">PENDATAAN SELESAI</h3>
      <p class="text-sm text-green-700 dark:text-green-400 mt-1">
        Terima kasih! Seluruh data SE2026-L.UB telah berhasil disimpan. Unduh PDF sebagai bukti pengisian.
      </p>
      <div class="mt-3 flex flex-wrap gap-3">
        <a href="{{ route('survey.ub.pdf.download') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-semibold shadow-md transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Unduh PDF Formulir SE2026-L.UB
        </a>
        <form method="POST" action="{{ route('survey.ub.start-edit') }}" onsubmit="return confirm('Anda akan membuka kembali survei untuk diedit ulang secara menyeluruh dari Blok I-A. Data yang telah diisi tetap tersimpan. Lanjutkan?')">
          @csrf
          <button type="submit"
                  class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold shadow-md transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Survei
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endif

{{-- Quick Start --}}
@if(!$isCompleted)
@php
  $nextBlock = collect($blocks)->first(fn($b) => !$b['done']);
@endphp
@if($nextBlock)
<div class="mt-6 text-center">
  <a href="{{ route($nextBlock['route']) }}"
     class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-md transition">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ $completedCount === 0 ? 'Mulai Survei' : ('Lanjutkan: ' . $nextBlock['label'] . ' — ' . $nextBlock['sub']) }}
  </a>
</div>
@endif
@endif

@endsection
