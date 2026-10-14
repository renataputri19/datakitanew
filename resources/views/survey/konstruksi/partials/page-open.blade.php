{{--
  Opens a blok page: header, flash messages, the blok rail and the main column.
  Every blok view pairs this with partials.page-close.

  Expects: $title, $subtitle, $navBlocks, $tahun, $triwulan, $routeParams.
--}}
@php
  $periodeLabel = \App\Support\KonstruksiBlokPath::periodLabel($tahun, $triwulan);
@endphp

<div class="ud-page-header">
  <div class="ud-page-header-content">
    <h1 class="ud-page-title">{{ $title }}</h1>
    <p class="ud-page-description">Survei Perusahaan Konstruksi {{ $periodeLabel }} · {{ $subtitle }}</p>
  </div>
  <a href="{{ route('survey.konstruksi.entry') }}" class="ud-btn ud-btn-secondary text-sm hidden sm:inline-flex shrink-0">Kembali</a>
</div>

@if(session('error'))
  <div class="k-flash k-flash-error">{{ session('error') }}</div>
@endif
@if(session('info'))
  <div class="k-flash k-flash-info">{{ session('info') }}</div>
@endif
@if(session('success'))
  <div class="k-flash k-flash-ok">{{ session('success') }}</div>
@endif

<div class="k-layout">
  @include('survey.konstruksi.partials.sidebar')
  <div class="k-main">
    <div class="k-status" id="k-status" hidden></div>
