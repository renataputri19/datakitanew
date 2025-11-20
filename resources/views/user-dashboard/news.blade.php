@extends('layouts.user-dashboard')

@section('title', 'Berita - DataKita')
@section('description', 'Berita terbaru dari BPS Kota Batam')




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
            <h1 class="ud-page-title">Berita</h1>
            <p class="ud-page-description mb-4">Akses lengkap berita resmi statistik dari BPS Kota Batam</p>
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                    <span>{{ $news->total() }} Berita Tersedia</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Berita Section -->
<section class="ud-mb-6">
        <div class="ud-section-header">
            <div class="ud-section-title">
                <div class="ud-section-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                    </svg>
                </div>
                Berita Resmi Statistik
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Halaman {{ $news->currentPage() }} dari {{ $news->lastPage() }}
            </div>
        </div>

        @if($news->count() > 0)
            <div class="ud-grid ud-grid-cols-2 ud-mb-5">
                @foreach($news as $item)
<article class="ud-news-card">
                        @if($item->thumbnail)
                            <img src="{{ asset('storage/' . $item->thumbnail) }}" alt="{{ $item->title }}" class="ud-news-image">
                        @else
                            <div class="ud-news-image bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                                </svg>
                            </div>
                        @endif

                        <div class="ud-news-content">
                            @if($item->category)
                                <span class="ud-news-category">{{ $item->category }}</span>
                            @endif

                            <h3 class="ud-news-title">{{ $item->title }}</h3>

                            <p class="ud-news-excerpt">
                                {{ Str::limit($item->excerpt ?? strip_tags($item->content ?? ''), 120) }}
                            </p>

                            <div class="ud-news-meta">
                                <div class="ud-news-date">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    {{ $item->date ? $item->date->format('d M Y') : $item->created_at->format('d M Y') }}
                                </div>

                                @if(!empty($item->source_url))
                                    <a href="{{ $item->source_url }}" target="_blank" rel="noopener noreferrer" class="ud-btn ud-btn-primary">
                                        <span>Baca Lengkap</span>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- News Pagination -->
            @if($news->hasPages())
<div class="ud-pagination-wrapper">
                    <div class="ud-pagination">
                        {{-- Previous Page Link --}}
                        @if ($news->onFirstPage())
                            <span class="disabled">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </span>
                        @else
                            <a href="{{ $news->previousPageUrl() }}" rel="prev">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </a>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($news->getUrlRange(1, $news->lastPage()) as $page => $url)
                            @if ($page == $news->currentPage())
                                <span class="active"><span>{{ $page }}</span></span>
                            @else
                                <a href="{{ $url }}">{{ $page }}</a>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($news->hasMorePages())
                            <a href="{{ $news->nextPageUrl() }}" rel="next">
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
            <!-- Empty State for News -->
<div class="ud-empty-state">
                <svg class="ud-empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path>
                </svg>
                <h3 class="ud-empty-title">Belum Ada Berita</h3>
                <p class="ud-empty-description">Berita akan muncul di sini ketika tersedia.</p>
            </div>
        @endif
    </section>

@endsection