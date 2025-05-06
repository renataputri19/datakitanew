@extends('layouts.app')

@section('title', 'Data Statistik - DataKita')
@section('description', 'Akses data statistik terkategorisasi dari BPS Kota Batam')

@section('content')
    <div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
        <div class="mb-8 space-y-4">
            <h1 class="text-3xl font-bold tracking-tight" data-aos="fade-up">Data Statistik</h1>
            <p class="text-gray-500 dark:text-gray-400" data-aos="fade-up" data-aos-delay="100">Akses data statistik terkategorisasi dari BPS Kota Batam</p>
            <div class="relative" data-aos="fade-up" data-aos-delay="200">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="search" placeholder="Cari data statistik..." class="w-full rounded-md border border-gray-200 bg-white px-8 py-2 text-sm ring-offset-white placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800 dark:bg-gray-950 dark:ring-offset-gray-950 dark:placeholder:text-gray-400 dark:focus-visible:ring-blue-600 md:max-w-lg">
            </div>
        </div>

        <div class="mb-8" data-aos="fade-up" data-aos-delay="300">
            <div class="flex border-b border-gray-200 dark:border-gray-800">
                <button class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-500 border-b-2 border-blue-600 dark:border-blue-500 focus:outline-none">
                    Kategori
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                    Populer
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                    Terbaru
                </button>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3" data-aos="fade-up" data-aos-delay="400">
            @foreach($categories as $category)
            <a href="{{ route('data.category', $category['slug']) }}" class="group">
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex flex-row items-center gap-4 mb-4">
                            <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-3 group-hover:bg-blue-600 dark:group-hover:bg-blue-500 transition-colors duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-blue-600 dark:text-blue-500 group-hover:text-white transition-colors duration-300">
                                    @if($category['icon'] == 'bar-chart-3')
                                    <path d="M3 3v18h18"></path>
                                    <path d="M18 17V9"></path>
                                    <path d="M13 17V5"></path>
                                    <path d="M8 17v-3"></path>
                                    @elseif($category['icon'] == 'users')
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    @elseif($category['icon'] == 'pie-chart')
                                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                    <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                    @elseif($category['icon'] == 'book-open')
                                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                                    @elseif($category['icon'] == 'database')
                                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold">{{ $category['name'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $category['description'] }}</p>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $category['detail'] }}
                        </p>
                        <div class="inline-flex items-center text-blue-600 dark:text-blue-500 group-hover:font-medium transition-all duration-300">
                            <span class="group-hover:mr-2 transition-all duration-300">Lihat Data {{ $category['name'] }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="mt-12 space-y-8" id="popular-data" style="display: none;">
            <h2 class="text-2xl font-bold">Data Populer</h2>
            <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
                @foreach($popularData as $data)
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-1">{{ $data['title'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $data['category'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $data['description'] }}
                        </p>
                        <a href="{{ route('data.show', ['category' => strtolower($data['category']), 'id' => $data['id']]) }}" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group">
                            <span>Lihat Data</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mt-12 space-y-8" id="recent-data" style="display: none;">
            <h2 class="text-2xl font-bold">Data Terbaru</h2>
            <div class="grid gap-6 sm:grid-cols-2 md:grid-cols-3">
                @foreach($recentData as $data)
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1">
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-1">{{ $data['title'] }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $data['category'] }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $data['description'] }}
                        </p>
                        <a href="{{ route('data.show', ['category' => strtolower($data['category']), 'id' => $data['id']]) }}" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group">
                            <span>Lihat Data</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.flex.border-b button');
        const categoryContent = document.querySelector('.grid.gap-6.sm\\:grid-cols-2.md\\:grid-cols-3');
        const popularContent = document.getElementById('popular-data');
        const recentContent = document.getElementById('recent-data');
        
        tabButtons.forEach((button, index) => {
            button.addEventListener('click', function() {
                // Reset all tabs
                tabButtons.forEach(btn => {
                    btn.classList.remove('text-blue-600', 'dark:text-blue-500', 'border-blue-600', 'dark:border-blue-500');
                    btn.classList.add('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                });
                
                // Set active tab
                this.classList.remove('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                this.classList.add('text-blue-600', 'dark:text-blue-500', 'border-blue-600', 'dark:border-blue-500');
                
                // Show appropriate content
                categoryContent.style.display = 'none';
                popularContent.style.display = 'none';
                recentContent.style.display = 'none';
                
                if (index === 0) {
                    categoryContent.style.display = 'grid';
                } else if (index === 1) {
                    popularContent.style.display = 'block';
                } else if (index === 2) {
                    recentContent.style.display = 'block';
                }
            });
        });
    });
</script>
@endpush
