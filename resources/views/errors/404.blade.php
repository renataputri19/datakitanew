<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 – Halaman Tidak Ditemukan | DataKita</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (localStorage.theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="font-sans antialiased h-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">

    <div class="min-h-screen flex flex-col">
        <!-- Minimal header -->
        <header class="w-full border-b bg-white/95 dark:bg-gray-900/95 backdrop-blur border-gray-200 dark:border-gray-800">
            <div class="container mx-auto px-4 flex h-16 items-center justify-between">
                <a href="/" class="flex items-center space-x-2">
                    <span class="font-bold text-lg">
                        <span class="text-blue-600 dark:text-blue-500">Data</span>Kita
                    </span>
                </a>
                <button id="theme-toggle" type="button"
                    class="inline-flex items-center justify-center rounded-md text-sm font-medium transition-colors border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 h-9 w-9 p-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2M12 20v2m-7.07-14.07 1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2m-3.34-7.07-1.41 1.41M6.34 17.66l-1.41 1.41"></path>
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                    </svg>
                    <span class="sr-only">Toggle tema</span>
                </button>
            </div>
        </header>

        <!-- Error content -->
        <main class="flex-1 flex items-center justify-center px-4 py-16">
            <div class="max-w-md w-full text-center">
                <!-- Icon -->
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600 dark:text-blue-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-4.35-4.35m0 0A7.5 7.5 0 1 0 4.65 16.65a7.5 7.5 0 0 0 12 0Z" />
                    </svg>
                </div>

                <!-- Code -->
                <p class="text-sm font-semibold uppercase tracking-widest text-blue-600 dark:text-blue-400 mb-2">
                    Error 404
                </p>

                <!-- Title -->
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">
                    Halaman Tidak Ditemukan
                </h1>

                <!-- Description -->
                <p class="text-gray-500 dark:text-gray-400 mb-8 leading-relaxed">
                    Maaf, halaman yang Anda cari tidak dapat ditemukan. Mungkin halaman ini sudah dipindahkan,
                    dihapus, atau alamat URL yang Anda masukkan tidak tepat.
                </p>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="/dashboard"
                        class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Kembali ke Dashboard
                    </a>
                    <a href="/"
                        class="inline-flex items-center justify-center rounded-md border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Ke Beranda
                    </a>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 dark:border-gray-800 py-4 text-center text-xs text-gray-400 dark:text-gray-500">
            &copy; {{ date('Y') }} DataKita &mdash; BPS Kota Batam
        </footer>
    </div>

    <script>
        document.getElementById('theme-toggle').addEventListener('click', function () {
            document.documentElement.classList.toggle('dark');
            localStorage.theme = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        });
    </script>
</body>
</html>
