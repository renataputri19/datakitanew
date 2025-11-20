@extends('layouts.user-dashboard')

@section('title', 'Video - DataKita')
@section('description', 'Video terbaru dari BPS Kota Batam')




@section('dashboard-content')
    <!-- Mobile/Tablet Menu Button -->
    <div class="lg:hidden mb-4">
        <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200" type="button" data-open-sidebar aria-controls="dashboard-sidebar" aria-expanded="false">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            Menu
        </button>
    </div>

    <!-- Professional Page Header -->
<div class="ud-page-header">
        <div class="ud-page-header-content">
            <h1 class="ud-page-title">Video</h1>
            <p class="ud-page-description mb-4">Akses lengkap video terbaru dari BPS Kota Batam</p>
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 002 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ $videos->total() }} Video Tersedia</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Section -->
<section class="ud-mb-6">
        <div class="ud-section-header">
            <div class="ud-section-title">
                <div class="ud-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 002 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
                Video Terbaru
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Halaman {{ $videos->currentPage() }} dari {{ $videos->lastPage() }}
            </div>
        </div>

        @if($videos->count() > 0)
            <div class="ud-grid ud-grid-cols-2 ud-mb-5">
                @foreach($videos as $video)
<article class="ud-video-card">
                        <div class="ud-video-thumbnail">
                            @php
                                $videoId = App\Helpers\YoutubeHelper::extractYoutubeId($video->url);
                            @endphp
                            @if($video->thumbnail)
                                <img src="{{ Storage::url($video->thumbnail) }}" alt="{{ $video->title }}">
                            @elseif($videoId)
                                <img src="https://img.youtube.com/vi/{{ $videoId }}/maxresdefault.jpg"
                                     onerror="this.onerror=null; this.src='https://img.youtube.com/vi/{{ $videoId }}/hqdefault.jpg';"
                                     alt="{{ $video->title }}">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-500 to-blue-600">
                                    <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 002 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif

                            <a href="{{ $video->url }}" target="_blank" class="ud-video-play-button">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </a>
                        </div>

                        <div class="ud-video-content">
                            <h4 class="ud-video-title">{{ $video->title }}</h4>
                            <div class="ud-video-date">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                {{ $video->date ? $video->date->format('d M Y') : $video->created_at->format('d M Y') }}
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Video Pagination -->
            @if($videos->hasPages())
<div class="ud-pagination-wrapper">
                    <div class="ud-pagination">
                        {{-- Previous Page Link --}}
                        @if ($videos->onFirstPage())
                            <span class="disabled">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $videos->previousPageUrl() }}" rel="prev">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($videos->getUrlRange(1, $videos->lastPage()) as $page => $url)
                            @if ($page == $videos->currentPage())
                                <span class="active"><span>{{ $page }}</span></span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($videos->hasMorePages())
                            <a href="{{ $videos->nextPageUrl() }}" rel="next">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        @else
                            <span class="disabled">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State for Videos -->
<div class="ud-empty-state">
                <svg class="ud-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 002 2v8a2 2 0 002 2z"></path>
                </svg>
                <h3 class="ud-empty-title">Belum Ada Video</h3>
                <p class="ud-empty-description">Video akan muncul di sini ketika tersedia.</p>
            </div>
        @endif
    </section>

@endsection