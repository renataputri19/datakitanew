<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - DATA KITA BPS Kota Batam</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- AOS Animation -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Custom CSS -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #3b82f6;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        .navbar-fixed {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Update the navigation links color */
        .nav-link {
            color: #374151;
            /* text-gray-700 */
            font-weight: 500;
            transition: color 0.3s ease;
        }


        .nav-link:hover {
            color: #2563eb;
            /* text-blue-600 */
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-color);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .hero-gradient {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--accent-color) 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .footer {
            background: #1a1a1a;
            color: white;
            padding: 4rem 0;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 4rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--primary-color);
            transform: translateY(-3px);
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @media (max-width: 768px) {
            .footer-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }


        .bg-grid-pattern {
            background-image: radial-gradient(#2563eb 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>

    @yield('additional_css')
</head>

<body class="min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center space-x-3 group">
                    <img src="{{ asset('img/Logo BPS 1.png') }}" alt="Logo BPS"
                        class="h-10 w-auto transition-transform duration-300 group-hover:scale-105">
                    <img src="{{ asset('img/Logo SE2026.png') }}" alt="Logo SE"
                        class="h-10 w-auto transition-transform duration-300 group-hover:scale-105">
                    {{-- <div class="font-bold text-xl text-gray-800 tracking-wide">DATA KITA</div> --}}
                </a>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ url('/') }}"
                        class="nav-link text-gray-700 hover:text-blue-600 font-medium">Beranda</a>
                    <a href="{{ url('/brs') }}" class="nav-link text-gray-700 hover:text-blue-600 font-medium">Berita
                        Resmi Statistik</a>
                </div>

                <button class="md:hidden text-gray-700" id="mobile-menu-button">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>

            <div class="md:hidden hidden" id="mobile-menu">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="{{ url('/') }}"
                        class="block px-3 py-2 rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-600">Beranda</a>
                    <a href="{{ url('/brs') }}"
                        class="block px-3 py-2 rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-600">Berita
                        Resmi Statistik</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')

        <!-- CTA Section -->
        <section class="py-16 bg-blue-600 text-white">
            <div class="container mx-auto px-4 text-center">
                <div data-aos="fade-up" class="max-w-2xl mx-auto">
                    <h2 class="text-2xl md:text-3xl font-bold mb-4">Butuh Data Statistik?</h2>
                    <p class="text-lg text-blue-100 mb-6">Konsultasikan kebutuhan data Anda dengan Encik Bot</p>
                    <a href="http://wa.me/6281319992171"
                        class="inline-flex items-center bg-white text-blue-600 px-6 py-3 rounded-full font-semibold hover:bg-blue-50 transition-all transform hover:scale-105">
                        <i class="fab fa-whatsapp mr-2 text-xl"></i>
                        Chat dengan Encik Bot
                    </a>
                </div>
            </div>
        </section>
    </main>


    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div>
                <div class="flex items-center space-x-3 mb-6">
                    <img src="{{ asset('img/Logo BPS 1.png') }}" alt="Logo BPS" class="h-12 w-auto">
                    {{-- <div class="font-bold text-xl">DATA KITA</div> --}}
                </div>
                <p class="text-gray-400 mb-6">Portal Data dan Visualisasi BPS Kota Batam</p>
                <div class="flex space-x-4">
                    <a href="https://www.facebook.com/BPS.KOTA.BATAM/?locale=id_ID" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://www.instagram.com/bps.batam/" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.youtube.com/channel/UCjNSyjtj4Y9fBhxcJG3Xaag" class="social-link">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Kontak</h3>
                <ul class="space-y-3 text-gray-400">
                    <li>Jl. Abulyatama</li>
                    <li>Batam Centre, Kota Batam</li>
                    <li>Kepulauan Riau</li>
                </ul>
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-4">Link Terkait</h3>
                <ul class="space-y-3">
                    <li><a href="https://www.bps.go.id" class="text-gray-400 hover:text-white transition-colors">BPS
                            RI</a></li>
                    <li><a href="https://kepri.bps.go.id" class="text-gray-400 hover:text-white transition-colors">BPS
                            Provinsi Kepri</a></li>
                    <li><a href="https://batamkota.bps.go.id"
                            class="text-gray-400 hover:text-white transition-colors">BPS Kota Batam</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });

        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-fixed', 'shadow-md');
            } else {
                navbar.classList.remove('navbar-fixed', 'shadow-md');
            }
        });

        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    </script>

    @yield('additional_js')
</body>

</html>
