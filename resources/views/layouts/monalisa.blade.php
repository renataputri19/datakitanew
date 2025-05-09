<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'MONALISA')</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/5.11.3/main.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.dhtmlx.com/gantt/edge/dhtmlxgantt.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="{{ asset('css/monalisa/home.css') }}">
    <link rel="stylesheet" href="{{ asset('css/monalisa/footer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/monalisa/navbar.css') }}">
</head>
<body>
    <header class="main-header">
        <div class="container">
            <nav class="navbar">
                <div class="navbar-brand">
                    <a href="{{ url('/monalisa') }}" class="logo">
                        <span class="logo-text">MONALISA</span>
                        <div class="logo-highlight"></div>
                    </a>
                </div>
    
                <div class="navbar-menu">
                    @guest
                        <div class="nav-links">
                            <a href="#about" class="nav-link">
                                <span>Tentang</span>
                                <div class="nav-link-highlight"></div>
                            </a>
                            <a href="#features" class="nav-link">
                                <span>Fitur</span>
                                <div class="nav-link-highlight"></div>
                            </a>
                            <a href="#systems" class="nav-link">
                                <span>Sistem Terintegrasi</span>
                                <div class="nav-link-highlight"></div>
                            </a>
                            <a href="{{ url('/systems/monalisa/login') }}" class="nav-button">
                                <span>Login</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    @else
                        <div class="nav-links">
                            <a href="{{ url('/monalisa') }}" class="nav-link">
                                <span>Home</span>
                                <div class="nav-link-highlight"></div>
                            </a>
                            <a href="{{ url('/systems/monalisa/dashboard') }}" class="nav-link">
                                <span>Dashboard</span>
                                <div class="nav-link-highlight"></div>
                            </a>
                            <div class="profile-dropdown">
                                <button class="profile-toggle">
                                    <div class="profile-info">
                                        <i class="fas fa-user-circle profile-icon"></i>
                                        <span class="profile-name">{{ Auth::user()->name }}</span>
                                    </div>
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                                <div class="dropdown-content">
                                    <a href="{{ route('logout') }}" 
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                       class="dropdown-item">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
    
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @endguest
    
                    <button class="mobile-toggle" aria-label="Toggle Menu">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </nav>
        </div>
    </header>

    <main class="container py-5 mt-6 mb-6">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-waves">
            <svg class="waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path class="wave-1" fill-opacity="0.5" d="M0,96L40,112C80,128,160,160,240,160C320,160,400,128,480,128C560,128,640,160,720,186.7C800,213,880,235,960,224C1040,213,1120,171,1200,138.7C1280,107,1360,85,1400,74.7L1440,64L1440,320L1400,320C1360,320,1280,320,1200,320C1120,320,1040,320,960,320C880,320,800,320,720,320C640,320,560,320,480,320C400,320,320,320,240,320C160,320,80,320,40,320L0,320Z"></path>
                <path class="wave-2" fill-opacity="0.7" d="M0,192L40,181.3C80,171,160,149,240,149.3C320,149,400,171,480,165.3C560,160,640,128,720,128C800,128,880,160,960,181.3C1040,203,1120,213,1200,197.3C1280,181,1360,139,1400,117.3L1440,96L1440,320L1400,320C1360,320,1280,320,1200,320C1120,320,1040,320,960,320C880,320,800,320,720,320C640,320,560,320,480,320C400,320,320,320,240,320C160,320,80,320,40,320L0,320Z"></path>
            </svg>
        </div>
        <div class="footer-content">
            <div class="container">
                <div class="row footer-main">
                    <div class="col-lg-4 footer-info">
                        <div class="footer-logo">
                            <h3>MONALISA</h3>
                            <div class="logo-underline"></div>
                        </div>
                        <p class="footer-desc">Sistem Monitoring dan Evaluasi Statistik Sektoral yang inovatif untuk meningkatkan kualitas data statistik Batam.</p>
                        <div class="footer-social">
                            <a href="https://www.facebook.com/BPS.KOTA.BATAM/?locale=id_ID" class="social-link" aria-label="Facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.instagram.com/bps.batam/" class="social-link" aria-label="Instagram">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://www.youtube.com/channel/UCjNSyjtj4Y9fBhxcJG3Xaag" class="social-link" aria-label="YouTube">
                                <i class="fab fa-youtube"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 footer-links">
                        <h4>Sistem Terintegrasi</h4>
                        <ul>
                            <li><a href="https://romantik.web.bps.go.id/">Sistem Romantik</a></li>
                            <li><a href="https://indah.bps.go.id/">Indonesia Data Hub (INDAH)</a></li>
                            <li><a href="https://epss.web.bps.go.id/home">Sistem Simbatik</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-6 footer-contact">
                        <h4>Hubungi Kami</h4>
                        <div class="contact-info">
                            <div class="contact-item">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:bps2171@bps.go.id">bps2171@bps.go.id</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-globe"></i>
                                <a href="https://batamkota.bps.go.id/">batamkota.bps.go.id</a>
                            </div>
                            <div class="contact-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <p>Batam, Kepulauan Riau</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 footer-newsletter">
                        <h4>Kredit</h4>
                        <p>Sistem ini dikembangkan oleh BPS Kota Batam untuk mendukung pengelolaan statistik sektoral.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p class="copyright">&copy; 2024 BPS Kota Batam. All rights reserved.</p>
                    </div>
                    <div class="col-md-6">
                        <div class="footer-bottom-links">
                            <a href="#">Kebijakan Privasi</a>
                            <a href="#">Syarat dan Ketentuan</a>
                            <a href="https://batamkota.bps.go.id/">Situs Resmi</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="{{ asset('js/monalisa/home.js') }}"></script>
    <script src="{{ asset('js/monalisa/footer.js') }}"></script>
    <script src="{{ asset('js/monalisa/navbar.js') }}"></script>
</body>
</html>
