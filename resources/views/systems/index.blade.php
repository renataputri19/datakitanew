@extends('layouts.app')

@section('title', 'Sistem Terintegrasi - DataKita')
@section('description', 'Akses sistem terintegrasi BPS Kota Batam')

@section('content')
    <div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
        <div class="mb-8 space-y-4">
            <h1 class="text-3xl font-bold tracking-tight" data-aos="fade-up">Sistem Terintegrasi</h1>
            <p class="text-gray-500 dark:text-gray-400" data-aos="fade-up" data-aos-delay="100">Akses sistem terintegrasi BPS Kota Batam</p>
            <div class="relative" data-aos="fade-up" data-aos-delay="200">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="search" placeholder="Cari sistem..." class="w-full rounded-md border border-gray-200 bg-white px-8 py-2 text-sm ring-offset-white placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800 dark:bg-gray-950 dark:ring-offset-gray-950 dark:placeholder:text-gray-400 dark:focus-visible:ring-blue-600 md:max-w-lg">
            </div>
        </div>

        <div class="mb-8" data-aos="fade-up" data-aos-delay="300">
            <div class="flex border-b border-gray-200 dark:border-gray-800">
                <button class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-500 border-b-2 border-blue-600 dark:border-blue-500 focus:outline-none" data-tab="all">
                    Semua
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" data-tab="monitoring">
                    Monitoring
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" data-tab="data">
                    Data
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" data-tab="other">
                    Lainnya
                </button>
            </div>
        </div>

        <!-- All Systems Tab Content -->
        <div id="tab-all" class="tab-content">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systems as $index => $system)
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1 flex flex-col" data-aos="fade-up" data-aos-delay="{{ 400 + $index * 100 }}">
                    <div class="p-6 flex-1">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-bold">{{ $system->name }}</h3>
                            <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-600 dark:text-blue-500">
                                    @if($system->icon == 'bar-chart-3')
                                    <path d="M3 3v18h18"></path>
                                    <path d="M18 17V9"></path>
                                    <path d="M13 17V5"></path>
                                    <path d="M8 17v-3"></path>
                                    @elseif($system->icon == 'line-chart')
                                    <path d="M3 3v18h18"></path>
                                    <path d="m3 8 4-4 5 5 5-5 4 4"></path>
                                    <path d="m3 14 4-4 5 5 5-5 4 4"></path>
                                    <path d="m3 20 4-4 5 5 5-5 4 4"></path>
                                    @elseif($system->icon == 'database')
                                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                    <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                    @elseif($system->icon == 'share')
                                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                    <polyline points="16 6 12 2 8 6"></polyline>
                                    <line x1="12" x2="12" y1="2" y2="15"></line>
                                    @elseif($system->icon == 'activity')
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                    @endif
                                </svg>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $system->description }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            {{ $system->detail }}
                        </p>
                        <div class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-500/30">
                            {{ ucfirst($system->category) }}
                        </div>
                    </div>
                    <div class="p-6 pt-0 mt-auto">
                        <div class="flex flex-col gap-2">
                            {{-- <a href="{{ route('systems.show', $system->slug) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 w-full">
                                Akses {{ $system->name }}
                            </a> --}}
                            {{-- <a href="{{ $system->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 w-full">
                                <span>Buka di Tab Baru</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" x2="21" y1="14" y2="3"></line>
                                </svg>
                            </a> --}}
                            <a href="{{ $system->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 w-full">
                                <span>Akses {{ $system->name }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                    <polyline points="15 3 21 3 21 9"></polyline>
                                    <line x1="10" x2="21" y1="14" y2="3"></line>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Monitoring Tab Content -->
        <div id="tab-monitoring" class="tab-content hidden">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systems as $system)
                    @if($system['category'] === 'monitoring')
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1 flex flex-col" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="p-6 flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold">{{ $system['name'] }}</h3>
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-600 dark:text-blue-500">
                                        @if($system['icon'] == 'bar-chart-3')
                                        <path d="M3 3v18h18"></path>
                                        <path d="M18 17V9"></path>
                                        <path d="M13 17V5"></path>
                                        <path d="M8 17v-3"></path>
                                        @elseif($system['icon'] == 'activity')
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                        @endif
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $system['description'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                {{ $system['detail'] }}
                            </p>
                        </div>
                        <div class="p-6 pt-0 mt-auto">
                            <div class="flex flex-col gap-2">
                                {{-- <a href="{{ route('systems.show', $system['slug']) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 w-full">
                                    Akses {{ $system['name'] }}
                                </a>
                                <a href="{{ $system['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 w-full">
                                    <span>Buka di Tab Baru</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" x2="21" y1="14" y2="3"></line>
                                    </svg>
                                </a> --}}
                                <a href="{{ $system->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 w-full">
                                    <span>Akses {{ $system->name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" x2="21" y1="14" y2="3"></line>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Data Tab Content -->
        <div id="tab-data" class="tab-content hidden">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systems as $system)
                    @if($system['category'] === 'data')
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1 flex flex-col" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="p-6 flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold">{{ $system['name'] }}</h3>
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-600 dark:text-blue-500">
                                        @if($system['icon'] == 'database')
                                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                                        @elseif($system['icon'] == 'share')
                                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                                        <polyline points="16 6 12 2 8 6"></polyline>
                                        <line x1="12" x2="12" y1="2" y2="15"></line>
                                        @endif
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $system['description'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                {{ $system['detail'] }}
                            </p>
                        </div>
                        <div class="p-6 pt-0 mt-auto">
                            <div class="flex flex-col gap-2">
                                {{-- <a href="{{ route('systems.show', $system['slug']) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 w-full">
                                    Akses {{ $system['name'] }}
                                </a>
                                <a href="{{ $system['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 w-full">
                                    <span>Buka di Tab Baru</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" x2="21" y1="14" y2="3"></line>
                                    </svg>
                                </a> --}}
                                <a href="{{ $system->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 w-full">
                                    <span>Akses {{ $system->name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" x2="21" y1="14" y2="3"></line>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Other Tab Content -->
        <div id="tab-other" class="tab-content hidden">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($systems as $system)
                    @if($system['category'] === 'other')
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1 flex flex-col" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="p-6 flex-1">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-xl font-bold">{{ $system['name'] }}</h3>
                                <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-600 dark:text-blue-500">
                                        @if($system['icon'] == 'line-chart')
                                        <path d="M3 3v18h18"></path>
                                        <path d="m3 8 4-4 5 5 5-5 4 4"></path>
                                        <path d="m3 14 4-4 5 5 5-5 4 4"></path>
                                        <path d="m3 20 4-4 5 5 5-5 4 4"></path>
                                        @elseif($system['icon'] == 'bar-chart-3')
                                        <path d="M3 3v18h18"></path>
                                        <path d="M18 17V9"></path>
                                        <path d="M13 17V5"></path>
                                        <path d="M8 17v-3"></path>
                                        @endif
                                    </svg>
                                </div>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ $system['description'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                {{ $system['detail'] }}
                            </p>
                        </div>
                        <div class="p-6 pt-0 mt-auto">
                            <div class="flex flex-col gap-2">
                                {{-- <a href="{{ route('systems.show', $system['slug']) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 w-full">
                                    Akses {{ $system['name'] }}
                                </a>
                                <a href="{{ $system['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 w-full">
                                    <span>Buka di Tab Baru</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" x2="21" y1="14" y2="3"></line>
                                    </svg>
                                </a> --}}
                                <a href="{{ $system->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 w-full">
                                    <span>Akses {{ $system->name }}</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                        <polyline points="15 3 21 3 21 9"></polyline>
                                        <line x1="10" x2="21" y1="14" y2="3"></line>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
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
    });
</script>
@endpush
