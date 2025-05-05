@extends('layouts.app')

@section('title', 'Beranda')

@section('content')



    <!-- Hero Section with Video Tabs -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-header">
                <span class="hero-eyebrow">Press Release</span>
                <h1 class="hero-title">Berita Resmi Statistik</h1>
                <h2 class="hero-subtitle">Mei 2025</h2>
            </div>

            <div class="video-tabs">
                <div class="tab-buttons">
                    <button class="tab-button active" data-tab="video2">Statistik Inflasi</button>
                    <button class="tab-button" data-tab="video1">Statistik Umum</button>
                </div>

                <div class="tab-content active" id="video2">
                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/cTjssxScosc?si=jtozLr4XNEhYtxrC"
                            allowfullscreen></iframe>
                    </div>
                </div>
                
                <div class="tab-content" id="video1">
                    <div class="video-container">
                        <iframe src="https://www.youtube.com/embed/MNP-nRpTX1U?si=uXpPlAW6zgZUWJb6"
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


                <!-- BRS Card 2 -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up"
                    data-aos-delay="200">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                    <div class="p-5">
                        <div class="text-sm text-blue-600 mb-2">2 Mei 2025</div>
                        <h3 class="text-lg font-semibold mb-2">Perkembangan Indeks Harga Konsumen Kota Batam, April 2025
                        </h3>
                        <p class="text-gray-600 text-sm mb-3">IHK Kota Batam pada April 2025 tercatat 109,21 atau mengalami
                            inflasi Year on Year (y-o-y) sebesar 2,81 persen ....</p>
                        <a href="https://batamkota.bps.go.id/id/pressrelease/2025/05/02/701/pada-april-2025--kota-batam-mengalami-inflasi-year-on-year--y-on-y--sebesar-2-81-persen-dengan-indeks-harga-konsumen--ihk--sebesar-109-21-.html"
                            class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                    </div>
                </div>

                <!-- BRS Card 1 -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up"
                    data-aos-delay="100">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                    <div class="p-5">
                        <div class="text-sm text-blue-600 mb-2">5 Mei 2025</div>
                        <h3 class="text-lg font-semibold mb-2">Perkembangan Ekspor dan Impor Kota Batam, Maret 2025</h3>
                        <p class="text-gray-600 text-sm mb-3">Ekspor Maret 2025 sebesar US$ 1.496,46 juta atau naik 3,19
                            persen dibandingkan ekspor Februari 2025. ...</p>
                        <a href="https://batamkota.bps.go.id/id/pressrelease/2025/05/05/713/ekspor-maret-2025--naik-3-19-persen-dibandingkan-bulan-lalu-namun-impor-maret-2025--turun-1-09-persen-.html"
                            class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                    </div>
                </div>
                <!-- BRS Card 3 -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up"
                    data-aos-delay="300">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                    <div class="p-5">
                        <div class="text-sm text-blue-600 mb-2">5 Mei 2025</div>
                        <h3 class="text-lg font-semibold mb-2">Perkembangan Transportasi Kota Batam, Maret 2025</h3>
                        <p class="text-gray-600 text-sm mb-3">Jumlah Penumpang angkutan udara domestik pada Maret 2025 sebanyak 293.416 orang sedangkan jumlah penumpang angkutan laut domestik pada Maret 2025 sebanyak 342.982 orang
                            .....</p>
                        <a href="https://batamkota.bps.go.id/id/pressrelease/2025/05/05/678/maret-2025--jumlah-penumpang-angkutan-udara-domestik-naik-sebesar-6-50-persen-.html"
                            class="text-blue-600 font-medium hover:text-blue-700 text-sm">Baca Selengkapnya →</a>
                    </div>
                </div>

                <!-- BRS Card 3 -->
                <div class="bg-white rounded-xl shadow-md overflow-hidden card-hover" data-aos="fade-up"
                    data-aos-delay="300">
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100"></div>
                    <div class="p-5">
                        <div class="text-sm text-blue-600 mb-2">5 Mei 2025</div>
                        <h3 class="text-lg font-semibold mb-2">Perkembangan Wisman Mancanegara Kota Batam, Maret 2025</h3>
                        <p class="text-gray-600 text-sm mb-3">Pada Maret 2025 jumlah wisman mancanegara ke KotaBatam sebanyak 100.279 kunjungan, dan TPK hotel bintang di Kota Batam sebesar 46,25 persen.
                            .....</p>
                        <a href="https://batamkota.bps.go.id/id/pressrelease/2025/05/05/654/pada-maret-2025-jumlah-wisman-mancanegara-ke-kotabatam-sebanyak-100-279-kunjungan--.html"
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
