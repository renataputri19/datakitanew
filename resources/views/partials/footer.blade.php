<footer class="border-t bg-white dark:bg-gray-900 transition-colors duration-300">
    <div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-4">
            <div class="space-y-4" data-aos="fade-up" data-aos-delay="100">
                <h3 class="text-lg font-medium">
                    <span class="text-blue-600 dark:text-blue-500">Data</span>Kita
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Platform terpadu untuk akses data statistik, berita, dan sistem terintegrasi BPS Kota Batam
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                        <span class="sr-only">Facebook</span>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                        </svg>
                        <span class="sr-only">Twitter</span>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line>
                        </svg>
                        <span class="sr-only">Instagram</span>
                    </a>
                    <a href="#" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                            <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                        </svg>
                        <span class="sr-only">YouTube</span>
                    </a>
                </div>
            </div>
            <div class="space-y-4" data-aos="fade-up" data-aos-delay="200">
                <h3 class="text-lg font-medium">Navigasi</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Beranda
                        </a>
                    </li>
                    {{-- <li>
                        <a href="{{ route('data') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Data Statistik
                        </a>
                    </li> --}}
                    <li>
                        <a href="{{ route('news') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Berita & Update
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('systems') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Sistem Terintegrasi
                        </a>
                    </li>
                </ul>
            </div>
            <div class="space-y-4" data-aos="fade-up" data-aos-delay="300">
                <h3 class="text-lg font-medium">Kategori Data</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        {{-- <a href="{{ route('data.category', 'ekonomi') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200"> --}}
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Ekonomi
                        </a>
                    </li>
                    <li>
                        {{-- <a href="{{ route('data.category', 'sosial') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200"> --}}
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Sosial
                        </a>
                    </li>
                    <li>
                        {{-- <a href="{{ route('data.category', 'demografi') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200"> --}}
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Demografi
                        </a>
                    </li>
                    <li>
                        {{-- <a href="{{ route('data.category', 'lingkungan') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200"> --}}
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Lingkungan
                        </a>
                    </li>
                    <li>
                        {{-- <a href="{{ route('data.category', 'publikasi') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200"> --}}
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Publikasi
                        </a>
                    </li>
                    <li>
                        {{-- <a href="{{ route('data.category', 'tabel-dinamis') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200"> --}}
                        <a href="{{ route('home') }}" class="text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-500 transition-colors duration-200">
                            Tabel Dinamis
                        </a>
                    </li>
                </ul>
            </div>
            <div class="space-y-4" data-aos="fade-up" data-aos-delay="400">
                <h3 class="text-lg font-medium">Kontak</h3>
                <address class="not-italic text-sm text-gray-500 dark:text-gray-400">
                    <p>BPS Kota Batam</p>
                    <p>Jl. Abulyatama Batam Center</p>
                    <p>Kota Batam, Kepulauan Riau</p>
                    <p>Indonesia</p>
                </address>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Email:
                    <a href="mailto:bps2171@bps.go.id" class="hover:text-blue-600 dark:hover:text-blue-500 transition-colors duration-200">
                        bps2171@bps.go.id
                    </a>
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Telp. (0778) 5508877
                </p>
            </div>
        </div>
        <div class="mt-8 border-t pt-8 text-center text-sm text-gray-500 dark:text-gray-400">
            <p>© {{ date('Y') }} BPS Kota Batam. Hak Cipta Dilindungi.</p>
        </div>
    </div>
</footer>
