@extends('layouts.app')

@section('title', $categoryName . ' - Data Statistik - DataKita')
@section('description', 'Data ' . $categoryName . ' dari BPS Kota Batam')

@section('content')
    <div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
        <div class="mb-8 space-y-4">
            <div class="flex items-center gap-2" data-aos="fade-right">
                <a href="{{ route('data') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold tracking-tight">Data {{ $categoryName }}</h1>
            </div>
            <p class="text-gray-500 dark:text-gray-400" data-aos="fade-right" data-aos-delay="100">
                {{ $categoryDescription }}
            </p>
            <div class="relative" data-aos="fade-right" data-aos-delay="200">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-2.5 top-2.5 h-4 w-4 text-gray-500 dark:text-gray-400">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="search" placeholder="Cari data {{ strtolower($categoryName) }}..." class="w-full rounded-md border border-gray-200 bg-white px-8 py-2 text-sm ring-offset-white placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-800 dark:bg-gray-950 dark:ring-offset-gray-950 dark:placeholder:text-gray-400 dark:focus-visible:ring-blue-600 md:max-w-lg">
            </div>
        </div>

        <div class="mb-8" data-aos="fade-up" data-aos-delay="300">
            <div class="flex border-b border-gray-200 dark:border-gray-800">
                <button class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-500 border-b-2 border-blue-600 dark:border-blue-500 focus:outline-none">
                    Semua
                </button>
                @if($category === 'ekonomi')
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                    Inflasi
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                    PDRB
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                    Kemiskinan
                </button>
                <button class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 border-b-2 border-transparent hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                    Ketenagakerjaan
                </button>
                @endif
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($categoryData as $data)
            <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-sm hover:shadow-xl transition-all duration-300 hover:border-blue-600 dark:hover:border-blue-500 transform hover:-translate-y-1" data-aos="fade-up" data-aos-delay="{{ 100 + $loop->index * 100 }}">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold">{{ $data['title'] }}</h3>
                        <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-blue-600 dark:text-blue-500">
                                <path d="M3 3v18h18"></path>
                                <path d="M18 17V9"></path>
                                <path d="M13 17V5"></path>
                                <path d="M8 17v-3"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Terakhir diperbarui: {{ $data['updated'] }}</p>
                    <div class="aspect-video mb-4 overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <div class="chart-placeholder w-full h-full flex items-center justify-center" data-chart-id="{{ $loop->index }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-10 w-10 text-gray-400 dark:text-gray-600">
                                <path d="M3 3v18h18"></path>
                                <path d="M18 17V9"></path>
                                <path d="M13 17V5"></path>
                                <path d="M8 17v-3"></path>
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        {{ $data['description'] }}
                    </p>
                    <div class="flex justify-between">
                        <a href="{{ route('data.show', ['category' => $category, 'id' => $data['id']]) }}" class="inline-flex items-center text-blue-600 dark:text-blue-500 hover:underline group">
                            <span>Lihat Detail</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4 group-hover:translate-x-1 transition-transform duration-300">
                                <path d="M5 12h14"></path>
                                <path d="m12 5 7 7-7 7"></path>
                            </svg>
                        </a>
                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" x2="12" y1="15" y2="3"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-12 flex justify-center" data-aos="fade-up" data-aos-delay="800">
            <a href="https://batamkota.bps.go.id/subject/3/ekonomi.html" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2 group">
                <span>Lihat Semua Data {{ $categoryName }} di Website BPS Kota Batam</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2 h-4 w-4">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" x2="21" y1="14" y2="3"></line>
                </svg>
            </a>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Generate random charts for placeholders
        const chartPlaceholders = document.querySelectorAll('.chart-placeholder');
        
        chartPlaceholders.forEach((placeholder, index) => {
            const canvas = document.createElement('canvas');
            canvas.id = 'chart-' + index;
            placeholder.innerHTML = '';
            placeholder.appendChild(canvas);
            
            const ctx = canvas.getContext('2d');
            
            // Generate random data
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            const data = Array.from({length: 6}, () => Math.floor(Math.random() * 100));
            
            // Create chart based on index
            if (index % 3 === 0) {
                // Bar chart
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Data ' + (index + 1),
                            data: data,
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else if (index % 3 === 1) {
                // Line chart
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Data ' + (index + 1),
                            data: data,
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            } else {
                // Pie chart
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: months,
                        datasets: [{
                            label: 'Data ' + (index + 1),
                            data: data,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.7)',
                                'rgba(99, 102, 241, 0.7)',
                                'rgba(139, 92, 246, 0.7)',
                                'rgba(168, 85, 247, 0.7)',
                                'rgba(217, 70, 239, 0.7)',
                                'rgba(236, 72, 153, 0.7)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        }
                    }
                });
            }
        });
        
        // Tab functionality
        const tabButtons = document.querySelectorAll('.flex.border-b button');
        
        tabButtons.forEach((button) => {
            button.addEventListener('click', function() {
                // Reset all tabs
                tabButtons.forEach(btn => {
                    btn.classList.remove('text-blue-600', 'dark:text-blue-500', 'border-blue-600', 'dark:border-blue-500');
                    btn.classList.add('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                });
                
                // Set active tab
                this.classList.remove('text-gray-500', 'dark:text-gray-400', 'border-transparent');
                this.classList.add('text-blue-600', 'dark:text-blue-500', 'border-blue-600', 'dark:border-blue-500');
            });
        });
    });
</script>
@endpush
