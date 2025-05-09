@extends('layouts.monalisa')

@section('title', 'MONALISA')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section d-flex align-items-center mt-6 py-5" data-section="hero">
        <div class="container">
            <div class="row align-items-center hero-content">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="hero-title fade-in-left" data-animation="left">
                        MONALISA
                        <span class="text-gradient">Monitoring dan Evaluasi Statistik Sektoral</span>
                    </h1>
                    <p class="hero-subtitle fade-in" data-animation="up">
                        Meningkatkan kualitas statistik di Batam melalui sistem monitoring real-time yang dirancang khusus
                        untuk mendukung Evaluasi Penyelenggaraan Statistik Sektoral (EPSS).
                    </p>
                    <div class="fade-in-up" data-animation="up">
                        <a href="{{ url('/epss/nilai') }}" class="hero-cta hover-effect">
                            Mulai Sekarang
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="{{ asset('img/data tes.png') }}" alt="Ilustrasi"
                        class="hero-illustration img-fluid fade-in-right" data-animation="right">
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Overview -->
    <section class="statistics-section py-5" data-section="statistics">
        <div class="container">
            <h2 class="section-title text-center fade-in" data-animation="up">
                Statistik Overview
                <div class="title-decoration"></div>
            </h2>
            <div class="stats-grid">
                <div class="stat-card-modern fade-in" data-animation="left">
                    <div class="stat-card-content">
                        {{-- <div class="stat-icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                        <div class="stat-icon-bg"></div>
                    </div> --}}
                        <div class="stat-info">
                            <h3>Total Domain</h3>
                            <div class="stat-number" data-value="5">0</div>
                            <p>Domain Utama</p>
                        </div>
                        <div class="stat-decoration"></div>
                    </div>
                </div>

                <div class="stat-card-modern fade-in" data-animation="up">
                    <div class="stat-card-content">
                        {{-- <div class="stat-icon-wrapper">
                        <i class="fas fa-file-upload"></i>
                        <div class="stat-icon-bg"></div>
                    </div> --}}
                        <div class="stat-info">
                            <h3>Total Aspek</h3>
                            <div class="stat-number" data-value="19">0</div>
                            <p>Aspek yang Dinilai</p>
                        </div>
                        <div class="stat-decoration"></div>
                    </div>
                </div>

                <div class="stat-card-modern fade-in" data-animation="right">
                    <div class="stat-card-content">
                        {{-- <div class="stat-icon-wrapper">
                        <i class="fas fa-users"></i>
                        <div class="stat-icon-bg"></div>
                    </div> --}}
                        <div class="stat-info">
                            <h3>Total Indikator</h3>
                            <div class="stat-number" data-value="38">0</div>
                            <p>Indikator Terukur</p>
                        </div>
                        <div class="stat-decoration"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about-section py-5 bg-light" id="about" data-section="about">
        <div class="container">
            <h2 class="section-title text-center fade-in" data-animation="up">Tentang Statistik Sektoral dan EPSS
                <div class="title-decoration"></div>
            </h2>

            <p class="section-subtitle text-center fade-in" data-animation="up">
                Statistik Sektoral adalah elemen penting untuk mendukung tugas pembangunan dan pemerintahan melalui data
                yang akurat dan terpercaya. Dengan kerangka Evaluasi Penyelenggaraan Statistik Sektoral (EPSS), setiap
                instansi pemerintah dapat meningkatkan efisiensi, transparansi, dan akuntabilitas penyelenggaraan statistik
                sesuai prinsip Satu Data Indonesia
            </p>

            <div class="about-icon-grid">
                <div class="about-icon-card">
                    <div class="about-icon-wrapper">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Satu Data Indonesia</h3>
                    <p>Mewujudkan interoperabilitas dan keterpaduan data melalui standar, metadata, dan kode referensi yang
                        mendukung tata kelola data lintas instansi.</p>
                </div>

                <div class="about-icon-card">
                    <div class="about-icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Kualitas Data</h3>
                    <p>Menjamin akurasi dan keandalan data melalui penerapan Quality Gates yang sistematis untuk mendukung
                        kebijakan publik berbasis bukti.</p>
                </div>

                <div class="about-icon-card">
                    <div class="about-icon-wrapper">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>Proses Bisnis Statistik</h3>
                    <p>Mengoptimalkan alur kerja statistik melalui model Generic Statistical Business Process Model (GSBPM)
                        untuk efisiensi di setiap tahapan, mulai dari perencanaan hingga diseminasi data.</p>
                </div>

                <div class="about-icon-card">
                    <div class="about-icon-wrapper">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3>Kelembagaan</h3>
                    <p>Memastikan independensi, profesionalitas, dan koordinasi antar instansi untuk menyelenggarakan
                        statistik yang berkualitas dan berdaya guna.</p>
                </div>

                <div class="about-icon-card">
                    <div class="about-icon-wrapper">
                        <i class="fas fa-flag"></i>
                    </div>
                    <h3>Statistik Nasional</h3>
                    <p>Memberikan gambaran menyeluruh dan terintegrasi dari data statistik nasional untuk mendukung
                        kebijakan strategis yang efektif.</p>
                </div>
            </div>
        </div>
    </section>



    <!-- Features Section -->
    <section class="features-section py-5" id="features" data-section="features">
        <div class="container">
            <h2 class="section-title text-center fade-in" data-animation="up">
                Fitur Utama
                <div class="title-decoration"></div>
            </h2>
            <div class="features-grid">
                <!-- Upload Data Feature -->
                <div class="feature-card fade-in" data-animation="left">
                    <div class="feature-card-inner">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-upload"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Upload Dokumen</h3>
                            <p>Unggah dokumen sesuai domain, aspek, dan indikator dengan mudah dan aman</p>
                            <div class="feature-hover-content">
                                <ul class="feature-details">
                                    <li>Drag & drop file</li>
                                    <li>Validasi format otomatis</li>
                                    <li>Penyimpanan terjamin</li>
                                </ul>
                            </div>
                        </div>
                        <div class="feature-card-decoration"></div>
                    </div>
                </div>

                <!-- Monitoring Feature -->
                <div class="feature-card fade-in" data-animation="up">
                    <div class="feature-card-inner">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Monitoring Real-time</h3>
                            <p>Pantau perkembangan indikator secara langsung melalui dashboard</p>
                            <div class="feature-hover-content">
                                <ul class="feature-details">
                                    <li>Pembaruan data real-time</li>
                                    <li>Visualisasi progres indikator</li>
                                    <li>Notifikasi otomatis</li>
                                </ul>
                            </div>
                        </div>
                        <div class="feature-card-decoration"></div>
                    </div>
                </div>

                <!-- Evaluation Feature -->
                <div class="feature-card fade-in" data-animation="right">
                    <div class="feature-card-inner">
                        <div class="feature-icon-wrapper">
                            <div class="feature-icon-bg"></div>
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div class="feature-content">
                            <h3>Evaluasi Otomatis</h3>
                            <p>Menghitung nilai dan tingkat kematangan indikator secara instan</p>
                            <div class="feature-hover-content">
                                <ul class="feature-details">
                                    <li>Penilaian berbasis indikator EPSS</li>
                                    <li>Nilai kematangan terintegrasi</li>
                                    <li>Hasil evaluasi siap diakses</li>
                                </ul>
                            </div>
                        </div>
                        <div class="feature-card-decoration"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="features-bg-decoration"></div>
    </section>

    <!-- Integrated Systems Section -->
    <!-- Integrated Systems Section -->
    <section class="integration-section py-5" id="systems" data-section="systems">
        <div class="container">
            <h2 class="section-title text-center fade-in" data-animation="up">Sistem Terintegrasi
                <div class="title-decoration"></div>
            </h2>
            <div class="systems-grid">
                <div class="system-card fade-in" data-animation="up">
                    <div class="system-card-inner">
                        <div class="system-content">
                            {{-- <div class="system-icon-wrapper">
                            <i class="fas fa-heart"></i>
                        </div> --}}
                            <h3>Sistem Romantik</h3>
                            <p>Platform pelaporan dan rekomendasi kegiatan statistik, memastikan seluruh aktivitas statistik terdokumentasi secara tepat dan terstandar.</p>
                            <div class="system-features">
                                <span><i class="fas fa-check-circle"></i> Pelaporan Real-time</span>
                                <span><i class="fas fa-check-circle"></i> Rekomendasi Otomatis</span>
                            </div>
                        </div>
                        <div class="system-action">
                            <a href="https://romantik.web.bps.go.id/" target="_blank" class="system-button">
                                <span>Kunjungi Romantik</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="system-card fade-in" data-animation="up">
                    <div class="system-card-inner">
                        <div class="system-content">
                            {{-- <div class="system-icon-wrapper">
                            <i class="fas fa-database"></i>
                        </div> --}}
                            <h3>Indonesia Data Hub (INDAH)</h3>
                            <p>Platform kolaborasi lintas instansi yang meningkatkan literasi dan nilai data melalui integrasi dan interoperabilitas.</p>
                            <div class="system-features">
                                <span><i class="fas fa-check-circle"></i> Kolaborasi Terpadu</span>
                                <span><i class="fas fa-check-circle"></i> Interoperabilitas Data</span>
                            </div>
                        </div>
                        <div class="system-action">
                            <a href="https://indah.bps.go.id/" target="_blank" class="system-button">
                                <span>Kunjungi INDAH</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="system-card fade-in" data-animation="up">
                    <div class="system-card-inner">
                        <div class="system-content">
                            {{-- <div class="system-icon-wrapper">
                            <i class="fas fa-chart-line"></i>
                        </div> --}}
                            <h3>Sistem Simbatik</h3>
                            <p>Platform pengelolaan dokumen dan indikator EPSS yang mempermudah dokumentasi dan validasi statistik sektoral.</p>
                            <div class="system-features">
                                <span><i class="fas fa-check-circle"></i> Dokumentasi Terstruktur</span>
                                <span><i class="fas fa-check-circle"></i> Verifikasi Otomatis</span>
                            </div>
                        </div>
                        <div class="system-action">
                            <a href="https://epss.web.bps.go.id/home" target="_blank" class="system-button">
                                <span>Kunjungi Simbatik</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
