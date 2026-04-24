<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'DataKita - BPS Kota Batam')</title>
    <meta name="description" content="@yield('description', 'Platform terpadu untuk akses data statistik, berita, dan sistem terintegrasi BPS Kota Batam')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-version" content="{{ config('app.version', '1.0.0') }}">

    @stack('head')

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- AOS Animation Library (excluded on /dashboard routes) -->
    @unless (request()->is('dashboard*'))
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    @endunless

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 transition-colors duration-300">
    <div id="app" class="flex flex-col min-h-screen">
        @include('partials.header')

        <main class="flex-1">
            @if(session('warning'))
            <div role="alert"
                 style="display:flex;align-items:flex-start;gap:0.75rem;
                        margin:0.75rem 1rem 0;padding:0.875rem 1.125rem;
                        background:#fffbeb;border:1px solid #fbbf24;border-radius:0.625rem;
                        color:#92400e;font-size:0.875rem;line-height:1.5;">
                <svg style="flex-shrink:0;width:1.125rem;height:1.125rem;margin-top:0.0625rem;"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span>{{ session('warning') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div role="alert"
                 style="display:flex;align-items:flex-start;gap:0.75rem;
                        margin:0.75rem 1rem 0;padding:0.875rem 1.125rem;
                        background:#fef2f2;border:1px solid #f87171;border-radius:0.625rem;
                        color:#991b1b;font-size:0.875rem;line-height:1.5;">
                <svg style="flex-shrink:0;width:1.125rem;height:1.125rem;margin-top:0.0625rem;"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
            @endif
            @if(session('success'))
            <div role="alert"
                 style="display:flex;align-items:flex-start;gap:0.75rem;
                        margin:0.75rem 1rem 0;padding:0.875rem 1.125rem;
                        background:#f0fdf4;border:1px solid #4ade80;border-radius:0.625rem;
                        color:#166534;font-size:0.875rem;line-height:1.5;">
                <svg style="flex-shrink:0;width:1.125rem;height:1.125rem;margin-top:0.0625rem;"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
            @endif
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    <!-- AOS Animation Library (excluded on /dashboard routes) -->
    @unless (request()->is('dashboard*'))
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    duration: 800,
                    easing: 'ease-in-out',
                    once: true
                });
            }

            // Theme toggle functionality
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', function() {
                    document.documentElement.classList.toggle('dark');

                    // Save preference to localStorage
                    if (document.documentElement.classList.contains('dark')) {
                        localStorage.theme = 'dark';
                    } else {
                        localStorage.theme = 'light';
                    }
                });
            }

            // Only use dark mode if explicitly set in localStorage
            // Default to light mode
            if (localStorage.theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
                // Set light mode as default
                localStorage.theme = 'light';
            }
        });
    </script>
    @endunless

    @stack('scripts')
    <script>
    /* Auto-trigger "Simpan dan Lanjutkan" when redirected from Blok-6 finish validation.
     * The backend appends ?show_validation=1 to the redirect URL so this snippet knows
     * it should fire. It waits for window.load + 400 ms to ensure every block's
     * clone-replace of #save-complete has finished before the synthetic click. */
    (function () {
        if (new URLSearchParams(window.location.search).get('show_validation') !== '1') return;

        function fireValidation() {
            var btn = document.getElementById('save-complete');
            if (!btn) return;
            btn.scrollIntoView({ behavior: 'smooth', block: 'end' });
            /* Give the smooth-scroll a moment, then click */
            setTimeout(function () { btn.click(); }, 150);
        }

        if (document.readyState === 'complete') {
            setTimeout(fireValidation, 400);
        } else {
            window.addEventListener('load', function () { setTimeout(fireValidation, 400); });
        }
    })();
    </script>
</body>
</html>


