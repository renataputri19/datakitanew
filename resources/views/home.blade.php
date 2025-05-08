@extends('layouts.app')

@section('title', 'DataKita - BPS Kota Batam')
@section('description', 'Platform terpadu untuk akses data statistik, berita, dan sistem terintegrasi BPS Kota Batam')

@section('content')
    <!-- Hero Section -->
    <section class="hero-pattern py-16 md:py-20 lg:py-28 relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute top-0 right-0 w-60 h-60 bg-purple-500/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/3 w-60 h-60 bg-yellow-500/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="container mx-auto px-4 md:px-6 relative z-10">
            <div class="flex flex-col items-center space-y-6 text-center">
                <div class="space-y-3" data-aos="fade-up">
                    <h1 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl lg:text-6xl">
                        <span class="text-blue-600 dark:text-blue-500 relative inline-block group">
                            Data
                            <span class="absolute -bottom-1 left-0 w-full h-1 bg-blue-600 dark:bg-blue-500 transform scale-x-0 transition-transform duration-500 group-hover:scale-x-100"></span>
                        </span>Kita
                    </h1>
                    <p class="mx-auto max-w-[700px] text-gray-500 dark:text-gray-400 text-base md:text-xl px-2">
                        Platform terpadu untuk akses data statistik, berita, dan sistem terintegrasi BPS Kota Batam
                    </p>
                </div>
                <div class="flex flex-row gap-4 w-full justify-center" data-aos="fade-up" data-aos-delay="200">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-11 px-6 py-2 transform hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                        <span>Mulai Sekarang</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-11 px-6 py-2 transform hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                        Pelajari Lebih Lanjut
                    </a>
                </div>
            </div>
        </div>

        <!-- Animated Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="text-white dark:text-gray-900 w-full h-auto">
                <path fill="currentColor" fill-opacity="1" d="M0,288L48,272C96,256,192,224,288,213.3C384,203,480,213,576,229.3C672,245,768,267,864,261.3C960,256,1056,224,1152,208C1248,192,1344,192,1392,192L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section - Highlighted -->
    <section class="py-12 md:py-16 lg:py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-8 md:mb-10">
                <div class="space-y-3" data-aos="fade-up">
                    <h2 class="text-2xl font-bold tracking-tighter sm:text-3xl md:text-4xl lg:text-5xl relative inline-block">
                        Fitur Utama
                        <span class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></span>
                    </h2>
                    <p class="mx-auto max-w-[700px] text-gray-500 dark:text-gray-400 text-base md:text-xl px-2 py-2">
                        Akses data statistik, berita terbaru, dan sistem terintegrasi dalam satu platform
                    </p>
                </div>
            </div>
            <div class="mx-auto grid max-w-5xl grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 md:gap-8 py-4 md:py-8">
                {{-- <div class="relative overflow-hidden rounded-xl border-2 border-blue-100 dark:border-gray-800 bg-white dark:bg-gray-950 transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 hover:shadow-xl group" data-aos="fade-up" data-aos-delay="100">
                    <div class="absolute top-0 right-0 h-20 w-20 bg-blue-600/10 dark:bg-blue-500/10 rounded-bl-full"></div>
                    <div class="p-5 md:p-6">
                        <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-3 w-12 h-12 md:w-14 md:h-14 flex items-center justify-center mb-4 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 md:h-8 md:w-8 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                <path d="M3 3v18h18"></path>
                                <path d="M18 17V9"></path>
                                <path d="M13 17V5"></path>
                                <path d="M8 17v-3"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-2">Data Statistik</h3>
                        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mb-4">
                            Data ekonomi, sosial, demografi, lingkungan, publikasi, dan tabel dinamis dari BPS Kota Batam
                        </p>
                        <a href="{{ route('data') }}" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group-hover:font-medium transition-all duration-300">
                            <span class="group-hover:mr-2 transition-all duration-300">Lihat Data</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div> --}}

                <div class="relative overflow-hidden rounded-xl border-2 border-blue-100 dark:border-gray-800 bg-white dark:bg-gray-950 transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 hover:shadow-xl group" data-aos="fade-up" data-aos-delay="200">
                    <div class="absolute top-0 right-0 h-20 w-20 bg-blue-600/10 dark:bg-blue-500/10 rounded-bl-full"></div>
                    <div class="p-5 md:p-6">
                        <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-3 w-12 h-12 md:w-14 md:h-14 flex items-center justify-center mb-4 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 md:h-8 md:w-8 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                            </svg>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-2">Berita & Update</h3>
                        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mb-4">
                            Rilis Berita Resmi Statistik bulanan dan update terbaru dari kanal YouTube BPS
                        </p>
                        <a href="{{ route('news') }}" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group-hover:font-medium transition-all duration-300">
                            <span class="group-hover:mr-2 transition-all duration-300">Lihat Berita</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl border-2 border-blue-100 dark:border-gray-800 bg-white dark:bg-gray-950 transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 hover:shadow-xl group sm:col-span-2 lg:col-span-1" data-aos="fade-up" data-aos-delay="300">
                    <div class="absolute top-0 right-0 h-20 w-20 bg-blue-600/10 dark:bg-blue-500/10 rounded-bl-full"></div>
                    <div class="p-5 md:p-6">
                        <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-3 w-12 h-12 md:w-14 md:h-14 flex items-center justify-center mb-4 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 md:h-8 md:w-8 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                <path d="M3 3v18h18"></path>
                                <path d="m3 8 4-4 5 5 5-5 4 4"></path>
                                <path d="m3 14 4-4 5 5 5-5 4 4"></path>
                                <path d="m3 20 4-4 5 5 5-5 4 4"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-2">Sistem Terintegrasi</h3>
                        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400 mb-4">
                            Monitoring dan Evaluasi Statistik Sektoral dan sistem terintegrasi lainnya
                        </p>
                        <a href="{{ route('systems') }}" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group-hover:font-medium transition-all duration-300">
                            <span class="group-hover:mr-2 transition-all duration-300">Akses Sistem</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest News & Updates Section -->
    <section class="py-12 md:py-16 lg:py-20 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-8 md:mb-10">
                <div class="space-y-3" data-aos="fade-up">
                    <h2 class="text-2xl font-bold tracking-tighter sm:text-3xl md:text-4xl lg:text-5xl relative inline-block">
                        Berita & Update Terbaru
                        <span class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></span>
                    </h2>
                    <p class="mx-auto max-w-[700px] text-gray-500 dark:text-gray-400 text-base md:text-xl px-2 py-2">
                        Rilis Berita Resmi Statistik dan video terbaru dari BPS Kota Batam
                    </p>
                </div>
            </div>

            <div class="grid gap-8 md:grid-cols-2">
                <!-- Latest Videos -->
                <div class="space-y-5 md:space-y-6" data-aos="fade-right">
                    <h3 class="text-xl md:text-2xl font-bold mb-4">Video Terbaru</h3>
                    <div class="grid gap-4">
                        @forelse($featuredVideos as $video)
                        <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="relative">
                                <div class="aspect-video bg-gray-200 dark:bg-gray-800 overflow-hidden">
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
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <a href="{{ $video->url }}" target="_blank" class="rounded-full h-10 w-10 md:h-12 md:w-12 bg-blue-600/90 hover:bg-blue-600 text-white flex items-center justify-center transform hover:scale-110 transition-all duration-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 md:h-6 md:w-6">
                                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            <div class="p-3 md:p-4">
                                <h4 class="font-semibold text-sm md:text-base line-clamp-2">{{ $video->title }}</h4>
                                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $video->formatted_date }}</p>
                            </div>
                        </div>
                        @empty
                        <div class="col-span-1 py-6 text-center">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Belum ada video</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Video terbaru akan segera ditambahkan.</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="flex justify-center">
                        <a href="{{ $bpsYoutubeUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group mt-6">
                            <span>Lihat Semua Video</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Latest News -->
                <div class="space-y-5 md:space-y-6" data-aos="fade-left">
                    <h3 class="text-xl md:text-2xl font-bold mb-4">Berita Resmi Statistik</h3>
                    <div class="space-y-4">
                        @forelse($featuredNews as $news)
                        <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                            <div class="p-4 md:p-6">
                                <h4 class="text-base md:text-lg font-semibold mb-1 line-clamp-2">{{ $news->title }}</h4>
                                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mb-2 md:mb-3">{{ $news->formatted_date }}</p>
                                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mb-3 md:mb-4 line-clamp-3">
                                    {{ $news->excerpt }}
                                </p>
                                <a href="{{ $news->source_url }}" target="_blank" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group text-sm">
                                    <span class="group-hover:mr-2 transition-all duration-300">Baca Selengkapnya</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                        <path d="M5 12h14"></path>
                                        <path d="m12 5 7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="py-6 text-center">
                            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">Belum ada berita resmi statistik</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Berita resmi statistik terbaru akan segera ditambahkan.</p>
                        </div>
                        @endforelse
                    </div>

                    <div class="flex justify-center">
                        <a href="{{ $bpsNewsUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group mt-6">
                            <span>Lihat Semua Berita</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Data Categories Section -->
    {{-- <section class="py-12 md:py-16 lg:py-20 bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-8 md:mb-10">
                <div class="space-y-3" data-aos="fade-up">
                    <h2 class="text-2xl font-bold tracking-tighter sm:text-3xl md:text-4xl lg:text-5xl relative inline-block">
                        Kategori Data
                        <span class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></span>
                    </h2>
                    <p class="mx-auto max-w-[700px] text-gray-500 dark:text-gray-400 text-base md:text-xl px-2 py-2">
                        Data statistik terkategorisasi untuk memudahkan pencarian
                    </p>
                </div>
            </div>
            <div class="mx-auto grid max-w-5xl grid-cols-1 gap-4 md:gap-6 py-4 md:py-8 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('data.category', 'ekonomi') }}" class="group" data-aos="zoom-in" data-aos-delay="100">
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                        <div class="p-4 md:p-6">
                            <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2 md:p-3 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 md:h-6 md:w-6 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                        <path d="M3 3v18h18"></path>
                                        <path d="M18 17V9"></path>
                                        <path d="M13 17V5"></path>
                                        <path d="M8 17v-3"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg md:text-xl font-bold">Ekonomi</h3>
                            </div>
                            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Inflasi, kemiskinan, dan indikator ekonomi lainnya
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('data.category', 'sosial') }}" class="group" data-aos="zoom-in" data-aos-delay="200">
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                        <div class="p-4 md:p-6">
                            <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2 md:p-3 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 md:h-6 md:w-6 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg md:text-xl font-bold">Sosial</h3>
                            </div>
                            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                IPM, pendidikan, dan indikator sosial lainnya
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('data.category', 'demografi') }}" class="group" data-aos="zoom-in" data-aos-delay="300">
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                        <div class="p-4 md:p-6">
                            <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2 md:p-3 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 md:h-6 md:w-6 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg md:text-xl font-bold">Demografi</h3>
                            </div>
                            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Populasi, migrasi, dan data kependudukan lainnya
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('data.category', 'lingkungan') }}" class="group" data-aos="zoom-in" data-aos-delay="400">
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                        <div class="p-4 md:p-6">
                            <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2 md:p-3 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 md:h-6 md:w-6 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                        <path d="M11 3a8 8 0 1 0 0 16 8 8 0 0 0 0-16z"></path>
                                        <path d="M21 21-4.35-4.35"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg md:text-xl font-bold">Lingkungan</h3>
                            </div>
                            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Iklim, biodiversitas, dan data lingkungan lainnya
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('data.category', 'publikasi') }}" class="group" data-aos="zoom-in" data-aos-delay="500">
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                        <div class="p-4 md:p-6">
                            <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2 md:p-3 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 md:h-6 md:w-6 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg md:text-xl font-bold">Publikasi</h3>
                            </div>
                            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Buku digital, laporan, dan publikasi lainnya
                            </p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('data.category', 'tabel-dinamis') }}" class="group" data-aos="zoom-in" data-aos-delay="600">
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                        <div class="p-4 md:p-6">
                            <div class="flex items-center gap-3 md:gap-4 mb-3 md:mb-4">
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2 md:p-3 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 md:h-6 md:w-6 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                    </svg>
                                </div>
                                <h3 class="text-lg md:text-xl font-bold">Tabel Dinamis</h3>
                            </div>
                            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                                Tabel statistik interaktif yang dapat disesuaikan
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section> --}}
@endsection

@push('styles')
<style>
    .hero-pattern {
        background-color: #f8fafc;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23e2e8f0' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .dark .hero-pattern {
        background-color: #0f172a;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231e293b' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    /* Blob animation */
    @keyframes blob {
        0% {
            transform: translate(0px, 0px) scale(1);
        }
        33% {
            transform: translate(30px, -50px) scale(1.1);
        }
        66% {
            transform: translate(-20px, 20px) scale(0.9);
        }
        100% {
            transform: translate(0px, 0px) scale(1);
        }
    }

    .animate-blob {
        animation: blob 7s infinite;
    }

    .animation-delay-2000 {
        animation-delay: 2s;
    }

    .animation-delay-4000 {
        animation-delay: 4s;
    }
</style>
@endpush
