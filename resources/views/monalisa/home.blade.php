@extends('layouts.app')

@section('title', 'MONALISA - Monitoring dan Evaluasi Statistik Sektoral')
@section('description', 'Meningkatkan kualitas statistik di Batam melalui sistem monitoring real-time yang dirancang khusus untuk mendukung Evaluasi Penyelenggaraan Statistik Sektoral (EPSS)')

@section('content')
    <!-- Hero Section -->
    <section class="hero-pattern py-8 md:py-12 lg:py-14 relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute -top-10 -left-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute top-0 right-0 w-60 h-60 bg-purple-500/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/3 w-60 h-60 bg-yellow-500/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="container mx-auto px-4 md:px-6 relative z-10">
            <div class="flex flex-col items-center space-y-6 text-center">
                <div class="space-y-3">
                    <h1 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl lg:text-6xl">
                        <span class="text-blue-600 dark:text-blue-500 relative inline-block group">
                            MONALISA
                            <span class="absolute -bottom-1 left-0 w-full h-1 bg-blue-600 dark:bg-blue-500 transform scale-x-0 transition-transform duration-500 group-hover:scale-x-100"></span>
                        </span>
                    </h1>
                    <h2 class="text-xl md:text-2xl lg:text-3xl font-semibold text-gray-700 dark:text-gray-300">
                        Monitoring dan Evaluasi Statistik Sektoral
                    </h2>
                    <p class="mx-auto max-w-[700px] text-gray-500 dark:text-gray-400 text-base md:text-xl px-2">
                        Meningkatkan kualitas statistik di Batam melalui sistem monitoring real-time yang dirancang khusus untuk mendukung Evaluasi Penyelenggaraan Statistik Sektoral (EPSS)
                    </p>
                </div>
                <div class="flex flex-row gap-4 w-full justify-center">
                    @auth
                        @if(auth()->user()->is_bps || auth()->user()->is_kominfo_user)
                            <a href="{{ route('monalisa.dashboard') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-11 px-6 py-2 transform hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                                <span>Dashboard</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-11 px-6 py-2 transform hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                            <span>Mulai Sekarang</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-1 h-4 w-4">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </a>
                    @endauth
                    <a href="#tentang" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-11 px-6 py-2 transform hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
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

    <!-- Statistics Overview Section -->
    <section class="py-8 md:py-12 lg:py-16 bg-gray-50 dark:bg-gray-800 transition-colors duration-300">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-8 md:mb-10">
                <div class="space-y-3">
                    <h2 class="text-2xl font-bold tracking-tighter sm:text-3xl md:text-4xl lg:text-5xl relative inline-block">
                        Statistik Overview
                        <span class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></span>
                    </h2>
                    <p class="mx-auto max-w-[700px] text-gray-500 dark:text-gray-400 text-base md:text-xl px-2 py-2">
                        Ringkasan data dan indikator utama dalam sistem MONALISA
                    </p>
                </div>
            </div>

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                <!-- Total Domain -->
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6 md:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-3 w-12 h-12 md:w-14 md:h-14 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8 text-blue-600 dark:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl md:text-4xl font-bold text-blue-600 dark:text-blue-400">5</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Domain</p>
                            </div>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-2">Total Domain</h3>
                        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400">
                            Domain utama dalam evaluasi statistik sektoral
                        </p>
                    </div>
                </div>

                <!-- Total Aspek -->
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6 md:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-full bg-purple-600/10 dark:bg-purple-500/10 p-3 w-12 h-12 md:w-14 md:h-14 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8 text-purple-600 dark:text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl md:text-4xl font-bold text-purple-600 dark:text-purple-400">19</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Aspek</p>
                            </div>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-2">Total Aspek</h3>
                        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400">
                            Aspek yang dinilai dalam setiap domain
                        </p>
                    </div>
                </div>

                <!-- Total Indikator -->
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6 md:p-8">
                        <div class="flex items-center justify-between mb-4">
                            <div class="rounded-full bg-green-600/10 dark:bg-green-500/10 p-3 w-12 h-12 md:w-14 md:h-14 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 md:h-8 md:w-8 text-green-600 dark:text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl md:text-4xl font-bold text-green-600 dark:text-green-400">38</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Indikator</p>
                            </div>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-2">Total Indikator</h3>
                        <p class="text-sm md:text-base text-gray-500 dark:text-gray-400">
                            Indikator terukur untuk evaluasi kualitas
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="tentang" class="py-12 md:py-16 lg:py-20 bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
        <div class="container mx-auto px-4 md:px-6">
            <div class="text-center mb-10 md:mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 relative inline-block">
                    Tentang Statistik Sektoral dan EPSS
                    <span class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></span>
                </h2>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="bg-white dark:bg-gray-900 rounded-xl p-6 md:p-8 shadow-lg">
                    <p class="text-base md:text-lg text-gray-600 dark:text-gray-400 leading-relaxed text-center">
                        Statistik Sektoral adalah elemen penting untuk mendukung tugas pembangunan dan pemerintahan melalui data yang akurat dan terpercaya. Dengan kerangka <strong>Evaluasi Penyelenggaraan Statistik Sektoral (EPSS)</strong>, setiap instansi pemerintah dapat meningkatkan efisiensi, transparansi, dan akuntabilitas penyelenggaraan statistik sesuai prinsip Satu Data Indonesia.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-8 md:py-12 lg:py-16 bg-white dark:bg-gray-900 transition-colors duration-300">
        <div class="container mx-auto px-4 md:px-6">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-8 md:mb-10">
                <div class="space-y-3">
                    <h2 class="text-2xl font-bold tracking-tighter sm:text-3xl md:text-4xl lg:text-5xl relative inline-block">
                        Fitur Utama
                        <span class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></span>
                    </h2>
                    <p class="mx-auto max-w-[700px] text-gray-500 dark:text-gray-400 text-base md:text-xl px-2 py-2">
                        Fitur-fitur unggulan dalam sistem MONALISA untuk monitoring dan evaluasi statistik sektoral
                    </p>
                </div>
            </div>

            <div class="grid gap-8 md:grid-cols-3">
                <!-- Upload Dokumen -->
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <div class="rounded-full bg-blue-600/10 dark:bg-blue-500/10 p-4 w-16 h-16 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold mb-3">Upload Dokumen</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm md:text-base leading-relaxed">
                                Unggah dokumen evaluasi statistik sektoral dengan mudah dan aman. Sistem mendukung berbagai format file untuk kemudahan pengguna.
                            </p>
                        </div>
                        <div class="flex items-center text-blue-600 dark:text-blue-400 text-sm font-medium">
                            <span>Pelajari lebih lanjut</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Monitoring Real-time -->
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <div class="rounded-full bg-green-600/10 dark:bg-green-500/10 p-4 w-16 h-16 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600 dark:text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold mb-3">Monitoring Real-time</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm md:text-base leading-relaxed">
                                Pantau progress evaluasi secara real-time dengan dashboard interaktif yang menampilkan status terkini dari setiap tahapan evaluasi.
                            </p>
                        </div>
                        <div class="flex items-center text-green-600 dark:text-green-400 text-sm font-medium">
                            <span>Pelajari lebih lanjut</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Evaluasi Otomatis -->
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 shadow-md hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="p-6 md:p-8">
                        <div class="mb-6">
                            <div class="rounded-full bg-purple-600/10 dark:bg-purple-500/10 p-4 w-16 h-16 flex items-center justify-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-purple-600 dark:text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl md:text-2xl font-bold mb-3">Evaluasi Otomatis</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm md:text-base leading-relaxed">
                                Sistem evaluasi otomatis yang menganalisis dokumen berdasarkan 38 indikator kualitas statistik sektoral yang telah ditetapkan.
                            </p>
                        </div>
                        <div class="flex items-center text-purple-600 dark:text-purple-400 text-sm font-medium">
                            <span>Pelajari lebih lanjut</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Principles Section -->
    <section class="py-12 md:py-16 lg:py-20 bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
        <div class="container mx-auto px-4 md:px-6">
            <div class="text-center mb-10 md:mb-12">
                <h2 class="text-3xl md:text-4xl font-bold mb-4 relative inline-block">
                    Prinsip Statistik Sektoral
                    <span class="absolute -bottom-2 left-1/4 right-1/4 h-1 bg-blue-600 dark:bg-blue-500 rounded-full"></span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Satu Data Indonesia -->
                <div class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-center mb-2">Satu Data Indonesia</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Mewujudkan interoperabilitas dan keterpaduan data melalui standar, metadata, dan kode referensi yang mendukung tata kelola data limas instansi.
                    </p>
                </div>

                <!-- Kualitas Data -->
                <div class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-center mb-2">Kualitas Data</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Menjamin akurasi dan keandalan data melalui penerapan Quality Gates yang sistematis untuk mendukung kebijakan publik berbasis bukti.
                    </p>
                </div>

                <!-- Proses Bisnis Statistik -->
                <div class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-center mb-2">Proses Bisnis Statistik</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Mengoptimalkan alur kerja statistik melalui model Generic Statistical Business Process Model (GSBPM) untuk efisiensi di setiap tahapan, mulai dari perencanaan hingga diseminasi data.
                    </p>
                </div>

                <!-- Kelembagaan -->
                <div class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-yellow-100 dark:bg-yellow-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-center mb-2">Kelembagaan</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Memastikan independensi, profesionalitas, dan koordinasi antar instansi untuk menyelenggarakan statistik yang berkualitas dan berdaya guna.
                    </p>
                </div>

                <!-- Statistik Nasional -->
                <div class="bg-white dark:bg-gray-900 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-lg flex items-center justify-center mb-4 mx-auto">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-center mb-2">Statistik Nasional</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                        Memberikan gambaran menyeluruh dan terintegrasi dari data statistik nasional untuk mendukung kebijakan strategis yang efektif.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-8 md:py-12 lg:py-16 bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-700 dark:to-purple-700 relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600/20 to-purple-600/20"></div>
        
        <div class="container mx-auto px-4 md:px-6 relative z-10">
            <div class="flex flex-col items-center justify-center space-y-4 text-center">
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold tracking-tighter sm:text-3xl md:text-4xl lg:text-5xl text-white">
                        Siap Meningkatkan Kualitas Statistik Anda?
                    </h2>
                    <p class="mx-auto max-w-[700px] text-blue-100 text-base md:text-xl px-2 py-2">
                        Bergabunglah dengan MONALISA untuk monitoring dan evaluasi statistik sektoral yang lebih efektif dan terstruktur
                    </p>
                </div>
                
                <div class="pt-4">
                    @auth
                        @if(auth()->user()->is_bps || auth()->user()->is_kominfo_user)
                            <a href="{{ route('monalisa.dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-medium text-blue-600 bg-white hover:bg-gray-100 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                                Akses Dashboard
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        @else
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 max-w-md mx-auto">
                                <p class="text-white text-lg">Anda tidak memiliki akses ke sistem MONALISA. Silakan hubungi administrator.</p>
                            </div>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-medium text-blue-600 bg-white hover:bg-gray-100 rounded-lg transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                            Login untuk Memulai
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
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

    @keyframes blob {
        0%, 100% {
            transform: translate(0, 0) scale(1);
        }
        25% {
            transform: translate(20px, -50px) scale(1.1);
        }
        50% {
            transform: translate(-20px, 20px) scale(0.9);
        }
        75% {
            transform: translate(50px, 50px) scale(1.05);
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
