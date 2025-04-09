@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
    <!-- Hero Section -->
    <section class="relative pt-24 pb-20 overflow-hidden">
        <!-- Background Elements -->
        <div class="absolute inset-0 z-0">
            <div class="absolute inset-0 opacity-5 bg-grid-pattern"></div>
            <div class="absolute right-0 top-0 w-1/3 h-48 bg-gradient-to-l from-blue-100/50 to-transparent"></div>
            <div class="absolute left-0 bottom-0 w-1/3 h-48 bg-gradient-to-r from-blue-100/50 to-transparent"></div>
        </div>

        <!-- Main Content -->
        <div class="container relative z-10 mx-auto px-4">
            <!-- Header -->
            <div class="text-center mb-16" data-aos="fade-up" data-aos-delay="100">
                <h1
                    class="text-5xl font-bold mb-6 bg-gradient-to-r from-gray-900 to-gray-700 inline-block text-transparent bg-clip-text">
                    DATA KITA
                </h1>
                <p class="text-xl text-gray-600">Portal Data dan Visualisasi BPS Kota Batam</p>
            </div>

            <!-- Featured Card -->
            <div class="max-w-4xl mx-auto" data-aos="fade-up" data-aos-delay="200">
                <a href="{{ url('/brs') }}" class="block group">
                    <div
                        class="bg-white rounded-2xl p-8 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <div class="flex flex-col md:flex-row items-center gap-8">
                            <!-- Left Content -->
                            <div class="flex-1 text-center md:text-left">
                                <div class="flex items-center justify-center md:justify-start gap-2 text-blue-600 mb-4">
                                    <span class="flex h-2 w-2 relative">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                    </span>
                                    <span class="font-medium">Berita Resmi Statistik</span>
                                </div>

                                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                                    Akses Data Berita Resmi Statistik Terbaru
                                </h2>

                                <p class="text-gray-600 mb-6">
                                    Dapatkan informasi statistik terpercaya dari BPS Kota Batam untuk pengambilan keputusan
                                    yang lebih baik.
                                </p>

                                <div class="inline-flex items-center gap-2 text-blue-600 group-hover:gap-3 transition-all">
                                    <span class="font-medium">Lihat Berita Resmi Statistik</span>
                                    <i class="fas fa-arrow-right transition-transform group-hover:translate-x-1"></i>
                                </div>
                            </div>

                            <!-- Right Content -->
                            <div
                                class="relative w-full md:w-64 aspect-square rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center group-hover:scale-105 transition-transform">
                                <div class="absolute inset-0 bg-blue-600/5 rounded-xl"></div>

                                <img src="img/Logo BPS.png" alt="Logo BPS" class="w-full h-full object-contain">

                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Stats -->
            {{-- <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4" data-aos="fade-up" data-aos-delay="300">
            @php
            $stats = [
                ['icon' => 'chart-line', 'label' => 'Indikator', 'value' => '250+'],
                ['icon' => 'calendar', 'label' => 'Update', 'value' => 'Bulanan'],
                ['icon' => 'users', 'label' => 'Pengguna', 'value' => '10rb+'],
                ['icon' => 'database', 'label' => 'Dataset', 'value' => '100+']
            ];
            @endphp

            @foreach ($stats as $stat)
                <div class="bg-white/80 backdrop-blur rounded-lg p-4 text-center hover:bg-white hover:shadow-md transition-all">
                    <i class="fas fa-{{ $stat['icon'] }} text-blue-600 text-xl mb-2"></i>
                    <div class="font-bold text-xl text-gray-900">{{ $stat['value'] }}</div>
                    <div class="text-sm text-gray-600">{{ $stat['label'] }}</div>
                </div>
            @endforeach
        </div> --}}
        </div>
    </section>

    <!-- Features Section -->
    {{-- <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-10" data-aos="fade-up">Fitur Utama</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-xl p-6 card-hover" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Visualisasi Data</h3>
                    <p class="text-gray-600 text-sm">Akses data statistik dalam bentuk visualisasi yang informatif dan mudah
                        dipahami.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-xl p-6 card-hover" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Berita Resmi Statistik</h3>
                    <p class="text-gray-600 text-sm">Akses berita dan publikasi statistik terkini dari BPS Kota Batam.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-xl p-6 card-hover" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold mb-2">Unduh Data</h3>
                    <p class="text-gray-600 text-sm">Unduh data statistik dalam berbagai format untuk kebutuhan analisis
                        Anda.</p>
                </div>
            </div>
        </div>
    </section> --}}

    <section class="hero">
        <div class="hero-container">
            <div class="hero-header">
                {{-- <span class="hero-eyebrow">Press Release</span> --}}
                <h1 class="hero-title">Berita Resmi Statistik</h1>
                <h2 class="hero-subtitle">April 2025</h2>
            </div>

            <div class="video-tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="video1">Statistik Umum</button>
                    <button class="tab-button" data-tab="video2">Statistik Inflasi</button>
                </div>

                <div class="tab-content active" id="video1">
                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/-ksoIrqBT5I?si=gzSHaLumFvf3pPn9"
                            allowfullscreen></iframe>
                    </div>
                </div>

                <div class="tab-content" id="video2">
                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/YYJLaZYBpPw?si=5TM5-xme8_BdogzJ"
                            allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest BRS Section -->
    <section class="py-16">
        <div class="container mx-auto px-4">
            <h2 class="text-2xl md:text-3xl font-bold text-center mb-10" data-aos="fade-up">Berita Resmi Statistik Terbaru
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- BRS Card 1 -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up"
                    data-aos-delay="100">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                    <div class="p-5">
                        <div class="text-sm text-blue-600 mb-2">9 April 2025</div>
                        <h3 class="text-lg font-semibold mb-2">Perkembangan Ekspor dan Impor Kota Batam, Februari 2025</h3>
                        <p class="text-gray-600 text-sm mb-3">Nilai ekspor Kota Batam Februari 2025 mencapai US$ 1.450,26 juta atau turun sebesar 18,32 persen dibandingkan ekspor Januari 2025. ...</p>
                        <a href="https://batamkota.bps.go.id/id/pressrelease/2025/04/09/712/ekspor-februari-2025-turun-18-32-persen-dibandingkan-ekspor-januari-2025--impor-februari-2025-turun-1-79-persen-dibandingkan-impor-januari-2025-.html"
                            class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                    </div>
                </div>

                <!-- BRS Card 2 -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up"
                    data-aos-delay="200">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                    <div class="p-5">
                        <div class="text-sm text-blue-600 mb-2">8 April 2025</div>
                        <h3 class="text-lg font-semibold mb-2">Perkembangan Indeks Harga Konsumen Kota Batam, Maret 2025
                        </h3>
                        <p class="text-gray-600 text-sm mb-3">Pada Maret 2025, Kota Batam mengalami inflasi year on year (y-on-y) sebesar 2,53 persen dengan Indeks Harga Konsumen (IHK) sebesar 108,80. ....</p>
                        <a href="https://batamkota.bps.go.id/id/pressrelease/2025/04/08/700/perkembangan-indeks-harga-konsumen-kota-batam-maret-2025.html"
                            class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                    </div>
                </div>

                <!-- BRS Card 3 -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up"
                    data-aos-delay="300">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                    <div class="p-5">
                        <div class="text-sm text-blue-600 mb-2">9 April 2025</div>
                        <h3 class="text-lg font-semibold mb-2">Perkembangan Transportasi Kota Batam, Februari 2025</h3>
                        <p class="text-gray-600 text-sm mb-3">Jumlah penumpang angkutan udara dalam negeri (domestik) pada Februari 2025 sebanyak 275.519 orang atau turun sebesar 16,11 persen dibandingkan Januari 2025. .....</p>
                        <a href="https://batamkota.bps.go.id/id/pressrelease/2025/04/09/677/jumlah-penumpang-angkutan-udara-domestik-kota-batam-pada-februari-2025-turun-sebesar-16-11-persen.html"
                            class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-8" data-aos="fade-up">
                <a href="https://batamkota.bps.go.id/id/pressrelease"
                    class="inline-block bg-blue-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-blue-700 transition-all transform hover:scale-105">
                    Lihat Semua Berita
                </a>
            </div>
        </div>
    </section>


@endsection
