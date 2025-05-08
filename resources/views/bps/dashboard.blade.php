@extends('layouts.bps')

@section('title', 'Dashboard BPS - DataKita')
@section('description', 'Dashboard pengelolaan konten BPS Kota Batam')

@section('content')
<div class="bps-card">
    <div class="bps-card-header">
        <h1 class="bps-title">Dashboard BPS</h1>
    </div>

    <div class="bps-card-body">
        <div class="bps-info-box mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Selamat Datang</h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                        <p>Selamat datang di Dashboard BPS, {{ $user->name }}. Anda dapat mengelola berita dan video BPS dari sini.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Total Berita</h3>
                        <p class="text-3xl font-bold text-blue-600 dark:text-blue-500">{{ $newsCount }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('bps.news.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium flex items-center">
                        Kelola Berita
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg p-6 shadow-sm">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 mr-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Total Video</h3>
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-500">{{ $videoCount }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('bps.videos.index') }}" class="text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300 text-sm font-medium flex items-center">
                        Kelola Video
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Recent News -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Berita Terbaru</h3>
                    <a href="{{ route('bps.news.create') }}" class="bps-btn-primary bps-btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah
                    </a>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($recentNews as $news)
                        <div class="px-6 py-4">
                            <div class="flex justify-between">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ Str::limit($news->title, 40) }}</h4>
                                    <div class="flex items-center mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="bps-badge {{ $news->category == 'BRS' ? 'bps-badge-blue' : ($news->category == 'Event' ? 'bps-badge-purple' : 'bps-badge-green') }} mr-2">
                                            {{ $news->category }}
                                        </span>
                                        <span>{{ $news->date->format('d M Y') }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('bps.news.edit', $news->id) }}" class="text-indigo-600 dark:text-indigo-500 hover:text-indigo-900 dark:hover:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 p-2 rounded-full hover:bg-indigo-100 dark:hover:bg-indigo-800/30 transition-colors duration-200" title="Edit Berita">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Belum ada berita yang ditambahkan.
                        </div>
                    @endforelse
                </div>
                @if(count($recentNews) > 0)
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 text-center">
                        <a href="{{ route('bps.news.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium">
                            Lihat Semua Berita
                        </a>
                    </div>
                @endif
            </div>

            <!-- Recent Videos -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Video Terbaru</h3>
                    <a href="{{ route('bps.videos.create') }}" class="bps-btn-primary bps-btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Tambah
                    </a>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($recentVideos as $video)
                        <div class="px-6 py-4">
                            <div class="flex gap-4">
                                <div class="flex-shrink-0 w-24 h-16 relative rounded-md overflow-hidden bg-gray-100 dark:bg-gray-800">
                                    @php
                                        $videoId = App\Helpers\YoutubeHelper::extractYoutubeId($video->url);
                                    @endphp

                                    @if($video->thumbnail)
                                        <img src="{{ Storage::url($video->thumbnail) }}" alt="{{ $video->title }}" class="w-full h-full object-cover">
                                    @elseif($videoId)
                                        <img src="https://img.youtube.com/vi/{{ $videoId }}/maxresdefault.jpg"
                                             onerror="this.onerror=null; this.src='https://img.youtube.com/vi/{{ $videoId }}/hqdefault.jpg';"
                                             alt="{{ $video->title }}"
                                             class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif


                                </div>
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ Str::limit($video->title, 40) }}</h4>
                                    <div class="flex items-center mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>{{ $video->date->format('d M Y') }}</span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 self-center">
                                    <a href="{{ route('bps.videos.edit', $video->id) }}" class="text-indigo-600 dark:text-indigo-500 hover:text-indigo-900 dark:hover:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 p-2 rounded-full hover:bg-indigo-100 dark:hover:bg-indigo-800/30 transition-colors duration-200" title="Edit Video">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Belum ada video yang ditambahkan.
                        </div>
                    @endforelse
                </div>
                @if(count($recentVideos) > 0)
                    <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800/50 text-center">
                        <a href="{{ route('bps.videos.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm font-medium">
                            Lihat Semua Video
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
