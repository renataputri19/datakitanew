@extends('layouts.app')

@section('title', 'Beranda')


@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    :root {
        --primary-color: #2563eb;
        --secondary-color: #1e40af;
        --accent-color: #3b82f6;
        --text-primary: #1f2937;
        --text-secondary: #4b5563;
        --bg-light: #f8fafc;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Poppins', sans-serif;
        line-height: 1.6;
        color: var(--text-primary);
        background: var(--bg-light);
    }

    /* Navbar Styles */
    .navbar {
        position: fixed;
        width: 100%;
        top: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .navbar.scrolled {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
    }

    .nav-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 1rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nav-brand {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .nav-logo {
        height: 40px;
        transition: transform 0.3s ease;
    }

    .nav-logo:hover {
        transform: scale(1.05);
    }

    .nav-menu {
        display: flex;
        gap: 2rem;
        align-items: center;
    }

    .nav-link {
        color: var(--text-primary);
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 5px;
        transition: all 0.3s ease;
        position: relative;
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        width: 0;
        height: 2px;
        background: var(--primary-color);
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .nav-link:hover::after {
        width: 80%;
    }

    /* Hero Section */
    .hero {
    padding-top: 7rem;  /* Reduced from original */
    min-height: auto;   /* Remove fixed height */
    display: flex;
    align-items: center;
    background: white;  /* Changed to white background */
    margin-bottom: 2rem;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;         /* Reduced gap */
    align-items: center;
}

    .hero-content {
        animation: slideInLeft 1s ease;
    }

    .hero-title-wrapper {
        margin-bottom: 2rem;
    }

    .hero-eyebrow {
        color: var(--primary-color);
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        opacity: 0;
        animation: fadeIn 0.5s ease forwards;
        animation-delay: 0.3s;
    }

    .hero-title {
    font-size: 2.5rem;  /* Slightly reduced */
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 0.5rem;
    color: #1f2937;     /* Added text color */
}

.hero-subtitle {
    font-size: 1.75rem; /* Slightly reduced */
    color: #4b5563;
    margin-bottom: 1.5rem;
}

.hero-description {
    color: #6b7280;
}

    .youtube-container {
        position: relative;
        width: 100%;
        padding-top: 56.25%;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        opacity: 0;
        animation: slideInRight 1s ease forwards;
        animation-delay: 0.5s;
    }

    .youtube-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
    }

    /* Cards Section */
    .brs-section {
        padding: 5rem 2rem;
        background: white;
    }

    .brs-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .brs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }

    .brs-card {
        background: white;
        border-radius: 15px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        transition: all 0.4s ease;
        text-decoration: none;
        color: inherit;
        position: relative;
        overflow: hidden;
    }

    .brs-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
        opacity: 0;
        transition: all 0.4s ease;
        z-index: 1;
    }

    .brs-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
    }

    .brs-card:hover::before {
        opacity: 0.05;
    }

    .card-content {
        position: relative;
        z-index: 2;
    }

    .card-icon {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 1.5rem;
    }

    .card-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--text-primary);
    }

    .card-text {
        color: var(--text-secondary);
        margin-bottom: 1.5rem;
    }

    .card-link {
        color: var(--primary-color);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: gap 0.3s ease;
    }

    .brs-card:hover .card-link {
        gap: 1rem;
    }

    /* Animations */
    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .footer-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .hero-container {
            grid-template-columns: 1fr;
            text-align: center;
        }

        .nav-menu {
            display: none;
        }

        .hero-title {
            font-size: 2.5rem;
        }

        .footer-container {
            grid-template-columns: 1fr;
        }
    }
</style>
    <!-- Hero Section -->
    <section class="pt-28 pb-16 bg-white">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-title-wrapper">
                    <span class="hero-eyebrow">Press Release</span>
                    <h1 class="hero-title">Berita Resmi Statistik</h1>
                    <h2 class="hero-subtitle">Maret 2024</h2>
                </div>
                <p class="hero-description">Sumber data statistik terpercaya untuk Kota Batam</p>
            </div>
            <div class="youtube-container">
                <iframe src="https://www.youtube.com/embed/YPGAplKbMYc?si=CuHmfJCUiHpxnJMJ" allowfullscreen>
                </iframe>
            </div>
        </div>
    </section>

    <!-- BRS Section -->
    <section class="brs-section">
        <div class="brs-container">
            <div class="brs-grid">
                <!-- IHK/Inflasi -->
                <a href="https://batamkota.bps.go.id/id/pressrelease/2025/03/04/640/perkembangan--indeks-harga-konsumen-kota-batam--februari-2025.html" class="brs-card">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="card-title">Perkembangan IHK</h3>
                        <p class="card-text">Data terkini mengenai Perkembangan Indeks Harga Konsumen Kota Batam
                        </p>
                        <span class="card-link">
                            Baca Selengkapnya
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a>

                <!-- Pariwisata -->
                {{-- <a href="https://batamkota.bps.go.id/id/pressrelease/2025/02/04/689/perkembangan-pariwisata-kota-batam--desember-2024.html" class="brs-card">
                    <div class="card-content">
                        <div class="card-icon">
                            <i class="fas fa-plane-departure"></i>
                        </div>
                        <h3 class="card-title">Perkembangan Pariwisata</h3>
                        <p class="card-text">Data terkini mengenai Perkembangan Pariwisata Kota Batam
                        </p>
                        <span class="card-link">
                            Baca Selengkapnya
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </a> --}}

                <!-- Ekspor Impor -->
                <a href="https://batamkota.bps.go.id/id/pressrelease/2025/03/04/664/perkembangan-ekspor--dan-impor-kota-batam---januari-2025.html" class="brs-card">
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
                <a href="https://batamkota.bps.go.id/id/pressrelease/2025/03/04/676/perkembangan-transportasi--kota-batam--januari-2025.html" class="brs-card">
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



    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>

@endsection