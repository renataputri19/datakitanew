<header class="sticky top-0 z-50 w-full border-b bg-white/95 dark:bg-gray-900/95 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:supports-[backdrop-filter]:bg-gray-900/60 transition-colors duration-300">
    <div class="container mx-auto px-4 flex h-16 items-center justify-between">
        <div class="flex items-center">
            <a href="{{ route('home') }}" class="mr-6 flex items-center space-x-2">
                <span class="font-bold text-lg">
                    <span class="text-blue-600 dark:text-blue-500">Data</span>Kita
                </span>
            </a>
            <nav class="hidden gap-6 md:flex">
                <a href="{{ route('home') }}" class="flex items-center text-sm font-medium transition-colors hover:text-blue-600 dark:hover:text-blue-500 {{ request()->routeIs('home') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                    Beranda
                </a>
                <a href="{{ route('data') }}" class="flex items-center text-sm font-medium transition-colors hover:text-blue-600 dark:hover:text-blue-500 {{ request()->routeIs('data*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                    Data Statistik
                </a>
                <a href="{{ route('news') }}" class="flex items-center text-sm font-medium transition-colors hover:text-blue-600 dark:hover:text-blue-500 {{ request()->routeIs('news*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                    Berita & Update
                </a>
                <a href="{{ route('systems') }}" class="flex items-center text-sm font-medium transition-colors hover:text-blue-600 dark:hover:text-blue-500 {{ request()->routeIs('systems*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                    Sistem Terintegrasi
                </a>
            </nav>
        </div>
        <div class="flex items-center gap-2">
            @auth
                <span class="hidden md:inline-flex items-center mr-2 text-sm text-gray-700 dark:text-gray-300">
                    Halo, {{ Auth::user()->name }}
                </span>
                <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 px-4 py-2">
                        Keluar
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="hidden md:inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 px-4 py-2">
                    Masuk
                </a>
                <a href="{{ route('register') }}" class="hidden md:inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-9 px-4 py-2">
                    Daftar
                </a>
            @endauth
            <button id="theme-toggle" type="button" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0">
                    <circle cx="12" cy="12" r="4"></circle>
                    <path d="M12 2v2"></path>
                    <path d="M12 20v2"></path>
                    <path d="m4.93 4.93 1.41 1.41"></path>
                    <path d="m17.66 17.66 1.41 1.41"></path>
                    <path d="M2 12h2"></path>
                    <path d="M20 12h2"></path>
                    <path d="m6.34 17.66-1.41 1.41"></path>
                    <path d="m19.07 4.93-1.41 1.41"></path>
                </svg>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                </svg>
                <span class="sr-only">Toggle theme</span>
            </button>
            <button type="button" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0 md:hidden" id="mobile-menu-button">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                    <line x1="4" x2="20" y1="12" y2="12"></line>
                    <line x1="4" x2="20" y1="6" y2="6"></line>
                    <line x1="4" x2="20" y1="18" y2="18"></line>
                </svg>
                <span class="sr-only">Toggle menu</span>
            </button>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="fixed inset-0 z-50 hidden flex-col bg-white dark:bg-gray-900 p-6 overflow-y-auto">
        <div class="flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                <span class="font-bold text-xl">
                    <span class="text-blue-600 dark:text-blue-500">Data</span>Kita
                </span>
            </a>
            <button type="button" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0" id="mobile-menu-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
                <span class="sr-only">Close menu</span>
            </button>
        </div>
        <nav class="mt-8 flex flex-col gap-5">
            <a href="{{ route('home') }}" class="flex items-center text-lg font-medium transition-colors hover:text-blue-600 dark:hover:text-blue-500 {{ request()->routeIs('home') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                Beranda
            </a>
            <a href="{{ route('data') }}" class="flex items-center text-lg font-medium transition-colors hover:text-blue-600 dark:hover:text-blue-500 {{ request()->routeIs('data*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                Data Statistik
            </a>
            <a href="{{ route('news') }}" class="flex items-center text-lg font-medium transition-colors hover:text-blue-600 dark:hover:text-blue-500 {{ request()->routeIs('news*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                Berita & Update
            </a>
            <a href="{{ route('systems') }}" class="flex items-center text-lg font-medium transition-colors hover:text-blue-600 dark:hover:text-blue-500 {{ request()->routeIs('systems*') ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">
                Sistem Terintegrasi
            </a>
        </nav>
        <div class="mt-auto pt-8">
            <div class="flex flex-col gap-3">
                @auth
                    <span class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                        Halo, {{ Auth::user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2">
                            Keluar
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-10 px-4 py-2">
                        Masuk
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:text-white dark:hover:bg-blue-700 dark:focus-visible:ring-blue-600 h-10 px-4 py-2 mt-2">
                        Daftar
                    </a>
                @endauth
            </div>
        </div>
    </div>
</header>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenuClose = document.getElementById('mobile-menu-close');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileMenuLinks = mobileMenu.querySelectorAll('a');

        function openMobileMenu() {
            mobileMenu.classList.remove('hidden');
            mobileMenu.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            // Add animation
            setTimeout(() => {
                mobileMenu.style.opacity = '1';
            }, 10);
        }

        function closeMobileMenu() {
            mobileMenu.style.opacity = '0';

            setTimeout(() => {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }, 300);
        }

        mobileMenuButton.addEventListener('click', openMobileMenu);
        mobileMenuClose.addEventListener('click', closeMobileMenu);

        // Close menu when clicking on links
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });

        // Close menu when pressing escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !mobileMenu.classList.contains('hidden')) {
                closeMobileMenu();
            }
        });
    });
</script>

<style>
    #mobile-menu {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    /* Dropdown positioning fix */
    @media (max-width: 768px) {
        .dropdown-menu {
            position: absolute;
            max-height: 80vh;
            overflow-y: auto;
            width: 100%;
            top: 100%;
            z-index: 50;
        }
    }
</style>


