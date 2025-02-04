@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<!-- Hero Section -->
<section class="pt-28 pb-16 bg-white">
    <div class="container mx-auto px-4 text-center">
        <div data-aos="fade-up" data-aos-delay="100">
            <h1 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">DATA KITA</h1>
            <p class="text-lg md:text-xl mb-6 text-gray-600">Portal Data dan Visualisasi BPS Kota Batam</p>
            <a href="{{ url('/brs') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-blue-700 transition-all transform hover:scale-105">
                Lihat Berita Resmi Statistik
            </a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-10" data-aos="fade-up">Fitur Utama</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white rounded-xl p-6 card-hover" data-aos="fade-up" data-aos-delay="100">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Visualisasi Data</h3>
                <p class="text-gray-600 text-sm">Akses data statistik dalam bentuk visualisasi yang informatif dan mudah dipahami.</p>
            </div>

            <!-- Feature 2 -->
            <div class="bg-white rounded-xl p-6 card-hover" data-aos="fade-up" data-aos-delay="200">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Berita Resmi Statistik</h3>
                <p class="text-gray-600 text-sm">Akses berita dan publikasi statistik terkini dari BPS Kota Batam.</p>
            </div>

            <!-- Feature 3 -->
            <div class="bg-white rounded-xl p-6 card-hover" data-aos="fade-up" data-aos-delay="300">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold mb-2">Unduh Data</h3>
                <p class="text-gray-600 text-sm">Unduh data statistik dalam berbagai format untuk kebutuhan analisis Anda.</p>
            </div>
        </div>
    </div>
</section>

<!-- Latest BRS Section -->
<section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-center mb-10" data-aos="fade-up">Berita Resmi Statistik Terbaru</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- BRS Card 1 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up" data-aos-delay="100">
                <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                <div class="p-5">
                    <div class="text-sm text-blue-600 mb-2">12 Februari 2025</div>
                    <h3 class="text-lg font-semibold mb-2">Perkembangan Indeks Harga Konsumen Januari 2025</h3>
                    <p class="text-gray-600 text-sm mb-3">Inflasi Kota Batam pada Januari 2025 sebesar 0,32 persen...</p>
                    <a href="#" class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                </div>
            </div>

            <!-- BRS Card 2 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up" data-aos-delay="200">
                <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                <div class="p-5">
                    <div class="text-sm text-blue-600 mb-2">5 Februari 2025</div>
                    <h3 class="text-lg font-semibold mb-2">Pertumbuhan Ekonomi Kota Batam Q4 2024</h3>
                    <p class="text-gray-600 text-sm mb-3">Ekonomi Kota Batam tumbuh sebesar 5,8 persen pada triwulan IV-2024...</p>
                    <a href="#" class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                </div>
            </div>

            <!-- BRS Card 3 -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up" data-aos-delay="300">
                <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                <div class="p-5">
                    <div class="text-sm text-blue-600 mb-2">1 Februari 2025</div>
                    <h3 class="text-lg font-semibold mb-2">Ekspor Impor Kota Batam Desember 2024</h3>
                    <p class="text-gray-600 text-sm mb-3">Nilai ekspor Kota Batam Desember 2024 mencapai USD 1,2 miliar...</p>
                    <a href="#" class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-8" data-aos="fade-up">
            <a href="{{ url('/brs') }}" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-full font-semibold hover:bg-blue-700 transition-all transform hover:scale-105">
                Lihat Semua Berita
            </a>
        </div>
    </div>
</section>


@endsection