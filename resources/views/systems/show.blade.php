@extends('layouts.app')

@section('title', $systemDetail['name'] . ' - Sistem Terintegrasi - DataKita')
@section('description', $systemDetail['description'])

@section('content')
    <div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
        <div class="mb-8 space-y-4">
            <div class="flex items-center gap-2" data-aos="fade-right">
                <a href="{{ route('systems') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold tracking-tight">{{ $systemDetail['name'] }}</h1>
            </div>
            <p class="text-gray-500 dark:text-gray-400" data-aos="fade-right" data-aos-delay="100">{{ $systemDetail['full_name'] }}</p>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
            <div class="md:col-span-2 space-y-8">
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md p-6" data-aos="fade-up">
                    <h2 class="text-xl font-bold mb-4">Tentang {{ $systemDetail['name'] }}</h2>
                    <p class="text-gray-700 dark:text-gray-300 mb-4">
                        {{ $systemDetail['description'] }}
                    </p>
                    <div class="mt-6">
                        <a href="{{ $systemDetail['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2">
                            <span>Akses {{ $systemDetail['name'] }}</span>
                        </a>
                    </div>
                </div>

                @if($systemDetail['slug'] === 'monalisa')
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md" data-aos="fade-up" data-aos-delay="200">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                        <h2 class="text-xl font-bold">Dashboard</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                            @foreach($systemDetail['stats'] as $stat)
                            <div class="overflow-hidden rounded-lg bg-gray-50 dark:bg-gray-900 p-4 transition-all duration-300 hover:shadow-md">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['name'] }}</h3>
                                <div class="mt-2 flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stat['value'] }}</p>
                                    <p class="ml-2 text-xs font-medium text-gray-500 dark:text-gray-400">{{ $stat['description'] }}</p>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            <h3 class="text-lg font-medium mb-4">Kemajuan OPD</h3>
                            <div class="space-y-4">
                                @foreach($systemDetail['progress'] as $progress)
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-medium">{{ $progress['name'] }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $progress['value'] }}%</div>
                                    </div>
                                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-2 rounded-full bg-blue-600 dark:bg-blue-500 transition-all duration-500" style="width: {{ $progress['value'] }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid gap-8 md:grid-cols-2" data-aos="fade-up" data-aos-delay="300">
                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                            <h2 class="text-xl font-bold">Dokumen Terbaru</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @foreach($systemDetail['documents'] as $document)
                                <div class="flex items-center justify-between">
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium">{{ $document['name'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $document['organization'] }}</p>
                                    </div>
                                    <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 px-4 py-2">
                                        Lihat
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-800">
                            <h2 class="text-xl font-bold">Evaluasi EPSS</h2>
                        </div>
                        <div class="p-6">
                            <div class="space-y-4">
                                @foreach($systemDetail['evaluation'] as $evaluation)
                                <div class="space-y-2">
                                    <h3 class="font-medium">{{ $evaluation['name'] }}</h3>
                                    <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-2 rounded-full bg-blue-600 dark:bg-blue-500 transition-all duration-500" style="width: {{ $evaluation['value'] }}%"></div>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $evaluation['description'] }}</p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="space-y-8">
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md p-6" data-aos="fade-left">
                    <h2 class="text-xl font-bold mb-4">Fitur Utama</h2>
                    <div class="space-y-4">
                        @foreach($systemDetail['features'] as $feature)
                        <div class="flex items-start gap-4">
                            <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-600 dark:text-blue-500">
                                    @if($feature['icon'] == 'upload')
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" x2="12" y1="3" y2="15"></line>
                                    @elseif($feature['icon'] == 'bar-chart-3')
                                    <path d="M3 3v18h18"></path>
                                    <path d="M18 17V9"></path>
                                    <path d="M13 17V5"></path>
                                    <path d="M8 17v-3"></path>
                                    @elseif($feature['icon'] == 'file-text')
                                    <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" x2="8" y1="13" y2="13"></line>
                                    <line x1="16" x2="8" y1="17" y2="17"></line>
                                    <line x1="10" x2="8" y1="9" y2="9"></line>
                                    @elseif($feature['icon'] == 'users')
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    @elseif($feature['icon'] == 'pie-chart')
                                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                    <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                    @elseif($feature['icon'] == 'download')
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" x2="12" y1="15" y2="3"></line>
                                    @elseif($feature['icon'] == 'link')
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                    @endif
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium">{{ $feature['name'] }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $feature['description'] }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md p-6" data-aos="fade-left" data-aos-delay="100">
                    <h2 class="text-xl font-bold mb-4">Akses Cepat</h2>
                    <div class="space-y-3">
                        <a href="{{ $systemDetail['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-between rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2">
                            <span>Akses {{ $systemDetail['name'] }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                <polyline points="15 3 21 3 21 9"></polyline>
                                <line x1="10" x2="21" y1="14" y2="3"></line>
                            </svg>
                        </a>
                        <button class="inline-flex w-full items-center justify-between rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2">
                            <span>Panduan Pengguna</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" x2="8" y1="13" y2="13"></line>
                                <line x1="16" x2="8" y1="17" y2="17"></line>
                                <line x1="10" x2="8" y1="9" y2="9"></line>
                            </svg>
                        </button>
                        <button class="inline-flex w-full items-center justify-between rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2">
                            <span>Hubungi Administrator</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md p-6" data-aos="fade-left" data-aos-delay="200">
                    <h2 class="text-xl font-bold mb-4">Kategori</h2>
                    <div class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-500/30">
                        {{ ucfirst($systemDetail['category']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animate progress bars on scroll
        const progressBars = document.querySelectorAll('.h-2.rounded-full.bg-blue-600');
        
        const animateProgressBars = () => {
            progressBars.forEach(bar => {
                const rect = bar.getBoundingClientRect();
                const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;
                
                if (isVisible) {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 100);
                }
            });
        };
        
        // Initial animation
        setTimeout(animateProgressBars, 500);
        
        // Animate on scroll
        window.addEventListener('scroll', () => {
            requestAnimationFrame(animateProgressBars);
        });
    });
</script>
@endpush
