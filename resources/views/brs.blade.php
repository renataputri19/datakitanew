@extends('layouts.app')

@section('title', 'Beranda')

@section('content')



    <!-- Hero Section with Video Tabs -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-header">
                <span class="hero-eyebrow">Press Release</span>
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

    <!-- BRS Section -->
    <section class="brs-section">
        <div class="brs-container">
            <div class="brs-grid">
                <!-- IHK/Inflasi -->
                <a href="https://batamkota.bps.go.id/id/pressrelease/2025/04/08/700/perkembangan-indeks-harga-konsumen-kota-batam-maret-2025.html"
                    class="brs-card">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="card-title">Perkembangan IHK</h3>
                        <p class="card-text">Data terkini mengenai Perkembangan Indeks Harga Konsumen Kota Batam</p>
                        <span class="card-link">
                            Baca Selengkapnya
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>

                <!-- Ekspor Impor -->
                <a href="https://batamkota.bps.go.id/id/pressrelease/2025/04/09/712/ekspor-februari-2025-turun-18-32-persen-dibandingkan-ekspor-januari-2025--impor-februari-2025-turun-1-79-persen-dibandingkan-impor-januari-2025-.html"
                    class="brs-card">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-ship"></i>
                        </div>
                        <h3 class="card-title">Ekspor dan Impor</h3>
                        <p class="card-text">Data terkini mengenai Perkembangan Ekspor dan Impor Kota Batam</p>
                        <span class="card-link">
                            Baca Selengkapnya
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>

                <!-- Transportasi -->
                <a href="https://batamkota.bps.go.id/id/pressrelease/2025/04/09/677/jumlah-penumpang-angkutan-udara-domestik-kota-batam-pada-februari-2025-turun-sebesar-16-11-persen.html"
                    class="brs-card">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <h3 class="card-title">Perkembangan Transportasi</h3>
                        <p class="card-text">Data terkini mengenai Perkembangan Transportasi Udara dan Laut Kota Batam</p>
                        <span class="card-link">
                            Baca Selengkapnya
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>

                <!-- Transportasi -->
                <a href="https://batamkota.bps.go.id/id/pressrelease/2025/04/09/653/tingkat-penghuni-kamar--tpk--hotel-bintang-bulan-februari--2025-di-kota-batam--sebesar-48-36-persen.html"
                    class="brs-card">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-bus"></i>
                        </div>
                        <h3 class="card-title">Perkembangan Transportasi</h3>
                        <p class="card-text">Data terkini mengenai Perkembangan Transportasi Udara dan Laut Kota Batam</p>
                        <span class="card-link">
                            Baca Selengkapnya
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>
            </div>
        </div>
    </section>



@endsection
