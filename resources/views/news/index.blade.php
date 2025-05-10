@extends('layouts.app')

@section('title', 'Berita & Update - DataKita')
@section('description', 'Rilis Berita Resmi Statistik dan update terbaru dari BPS Kota Batam')

@section('content')
    <div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
        <div class="mb-8 space-y-4">
            <h1 class="text-3xl font-bold tracking-tight" data-aos="fade-up">Berita & Update</h1>
            <p class="text-gray-500 dark:text-gray-400" data-aos="fade-up" data-aos-delay="100">Rilis Berita Resmi Statistik dan update terbaru dari BPS Kota Batam</p>
            <div class="relative" data-aos="fade-up" data-aos-delay="200">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="search" placeholder="Cari berita..." class="w-full rounded-md border border-gray-200 bg-white px-8 py-2 text-sm ring-offset-white placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800 dark:bg-gray-950 dark:ring-offset-gray-950 dark:placeholder:text-gray-400 dark:focus-visible:ring-blue-600 md:max-w-lg">
            </div>
        </div>

        <div class="mb-8" data-aos="fade-up" data-aos-delay="300">
            <div class="flex border-b border-gray-200 dark:border-gray-800">
                <button class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-500 border-b-2 border-blue-600 dark:border-blue-500 focus:outline-none" data-tab="all">
                    Semua
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" data-tab="brs">
                    Berita Resmi Statistik
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" data-tab="videos">
                    Press Release
                </button>
                {{-- <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" data-tab="events">
                    Event
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" data-tab="publications">
                    Publikasi
                </button> --}}
            </div>
        </div>

        <!-- All News Tab Content -->
        <div id="tab-all" class="tab-content">
            <div class="flex items-center justify-between mb-6" data-aos="fade-up" data-aos-delay="400">
                <h2 class="text-2xl font-bold tracking-tight">Berita Terbaru</h2>
                <a href="{{ $bpsYoutubeUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4 text-red-600 dark:text-red-500">
                        <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                        <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                    </svg>
                    <span>Kanal YouTube BPS</span>
                </a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($newsItems as $index => $news)
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="{{ 500 + $index * 100 }}">
                    <div class="relative">
                        <div class="absolute top-2 right-2">
                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-500/30">
                                {{ $news->category }}
                            </span>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                <line x1="16" x2="16" y1="2" y2="6"></line>
                                <line x1="8" x2="8" y1="2" y2="6"></line>
                                <line x1="3" x2="21" y1="10" y2="10"></line>
                            </svg>
                            {{ $news->formatted_date }}
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ $news->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $news->excerpt }}
                        </p>
                        <div class="flex justify-between">
                            <a href="{{ $news->source_url }}" target="_blank" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group">
                                <span>Baca Selengkapnya</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                            </a>
                            @if($news->has_video && $news->category !== 'BRS')
                            <a href="{{ $news->video_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" x2="21" y1="14" y2="3"></line>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-3 py-12 text-center">
                    <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" x2="8" y1="13" y2="13"></line>
                            <line x1="16" x2="8" y1="17" y2="17"></line>
                            <line x1="10" x2="8" y1="9" y2="9"></line>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Belum ada berita</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Berita dan update terbaru akan segera ditambahkan.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8 flex justify-center" data-aos="fade-up" data-aos-delay="800">
                <a href="{{ $bpsNewsUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group">
                    <span>Lihat Lebih Banyak</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <!-- BRS Tab Content -->
        <div id="tab-brs" class="tab-content hidden">
            <h2 class="text-2xl font-bold tracking-tight mb-6" data-aos="fade-up">Berita Resmi Statistik</h2>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($brsNews as $index => $news)
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                <line x1="16" x2="16" y1="2" y2="6"></line>
                                <line x1="8" x2="8" y1="2" y2="6"></line>
                                <line x1="3" x2="21" y1="10" y2="10"></line>
                            </svg>
                            {{ $news->formatted_date }}
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ $news->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $news->excerpt }}
                        </p>
                        <a href="{{ $news->source_url }}" target="_blank" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group">
                            <span>Baca Selengkapnya</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-3 py-12 text-center">
                    <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" x2="8" y1="13" y2="13"></line>
                            <line x1="16" x2="8" y1="17" y2="17"></line>
                            <line x1="10" x2="8" y1="9" y2="9"></line>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Belum ada berita resmi statistik</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Berita resmi statistik terbaru akan segera ditambahkan.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8 flex justify-center" data-aos="fade-up">
                <a href="{{ $bpsNewsUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group">
                    <span>Lihat Lebih Banyak</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Videos Tab Content -->
        <div id="tab-videos" class="tab-content hidden">
            <div class="flex items-center justify-between mb-6" data-aos="fade-up">
                <h2 class="text-2xl font-bold tracking-tight">Video</h2>
                <a href="{{ $bpsYoutubeUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 h-4 w-4 text-red-600 dark:text-red-500">
                        <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                        <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                    </svg>
                    <span>Kanal YouTube BPS</span>
                </a>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($videos as $index => $video)
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="relative">
                        <div class="aspect-video bg-gray-200 dark:bg-gray-800 overflow-hidden">
                            @php
                                $videoId = App\Helpers\YoutubeHelper::extractYoutubeId($video->url);
                            @endphp

                            @if($video->thumbnail)
                                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full h-full object-cover">
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
                    </div>
                    <div class="p-4">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                <line x1="16" x2="16" y1="2" y2="6"></line>
                                <line x1="8" x2="8" y1="2" y2="6"></line>
                                <line x1="3" x2="21" y1="10" y2="10"></line>
                            </svg>
                            {{ $video->formatted_date }}
                        </div>
                        <h4 class="font-semibold">{{ $video->title }}</h4>
                    </div>
                    <div class="px-4 pb-4 flex justify-end">
                        <a href="{{ $video->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <line x1="10" x2="21" y1="14" y2="3"></line>
                            </svg>
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-3 py-12 text-center">
                    <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Belum ada video</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Video terbaru akan segera ditambahkan.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8 flex justify-center" data-aos="fade-up">
                <a href="{{ $bpsYoutubeUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group">
                    <span>Lihat Lebih Banyak</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Events Tab Content -->
        <div id="tab-events" class="tab-content hidden">
            <h2 class="text-2xl font-bold tracking-tight mb-6" data-aos="fade-up">Event</h2>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($eventNews as $index => $news)
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                <line x1="16" x2="16" y1="2" y2="6"></line>
                                <line x1="8" x2="8" y1="2" y2="6"></line>
                                <line x1="3" x2="21" y1="10" y2="10"></line>
                            </svg>
                            {{ $news->formatted_date }}
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ $news->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $news->excerpt }}
                        </p>
                        <div class="flex justify-between">
                            <a href="{{ $news->source_url }}" target="_blank" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group">
                                <span>Baca Selengkapnya</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                            </a>
                            @if($news->has_video)
                            <a href="{{ $news->video_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" x2="21" y1="14" y2="3"></line>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-3 py-12 text-center">
                    <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" x2="8" y1="13" y2="13"></line>
                            <line x1="16" x2="8" y1="17" y2="17"></line>
                            <line x1="10" x2="8" y1="9" y2="9"></line>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Belum ada event</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Event terbaru akan segera ditambahkan.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8 flex justify-center" data-aos="fade-up">
                <a href="{{ $bpsNewsUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group">
                    <span>Lihat Lebih Banyak</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Publications Tab Content -->
        <div id="tab-publications" class="tab-content hidden">
            <h2 class="text-2xl font-bold tracking-tight mb-6" data-aos="fade-up">Publikasi</h2>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @forelse($publicationNews as $index => $news)
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
                    <div class="p-6">
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                <line x1="16" x2="16" y1="2" y2="6"></line>
                                <line x1="8" x2="8" y1="2" y2="6"></line>
                                <line x1="3" x2="21" y1="10" y2="10"></line>
                            </svg>
                            {{ $news->formatted_date }}
                        </div>
                        <h3 class="text-lg font-bold mb-2">{{ $news->title }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $news->excerpt }}
                        </p>
                        <div class="flex justify-between">
                            <a href="{{ $news->source_url }}" target="_blank" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group">
                                <span>Baca Selengkapnya</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                            </a>
                            <a href="{{ $news->source_url }}" target="_blank" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" x2="12" y1="15" y2="3"></line>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-3 py-12 text-center">
                    <div class="mx-auto w-24 h-24 mb-4 text-gray-300 dark:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-full h-full">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" x2="8" y1="13" y2="13"></line>
                            <line x1="16" x2="8" y1="17" y2="17"></line>
                            <line x1="10" x2="8" y1="9" y2="9"></line>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Belum ada publikasi</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">Publikasi terbaru akan segera ditambahkan.</p>
                </div>
                @endforelse
            </div>

            <div class="mt-8 flex justify-center" data-aos="fade-up">
                <a href="{{ $bpsNewsUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group">
                    <span>Lihat Lebih Banyak</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab functionality
        const tabButtons = document.querySelectorAll('[data-tab]');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');

                // Reset all tabs
                tabButtons.forEach(btn => {
                    btn.classList.remove('text-blue-600', 'dark:text-blue-500', 'border-blue-600', 'dark:border-blue-500');
                    btn.classList.add('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                });

                // Set active tab
                this.classList.remove('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                this.classList.add('text-blue-600', 'dark:text-blue-500', 'border-blue-600', 'dark:border-blue-500');

                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });

                // Show selected tab content
                document.getElementById(`tab-${tabName}`).classList.remove('hidden');
            });
        });

        // Video modal functionality
        const videoButtons = document.querySelectorAll('[data-video-url]');
        const videoModal = document.getElementById('video-modal');
        const videoFrame = document.getElementById('video-frame');
        const closeModal = document.getElementById('close-modal');

        if (videoModal && videoFrame && closeModal) {
            videoButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const videoUrl = this.getAttribute('data-video-url');
                    videoFrame.src = videoUrl;
                    videoModal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                });
            });

            closeModal.addEventListener('click', function() {
                videoModal.classList.add('hidden');
                videoFrame.src = '';
                document.body.classList.remove('overflow-hidden');
            });
        }
    });
</script>
@endpush
