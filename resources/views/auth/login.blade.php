<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - DataKita</title>
    <link rel="stylesheet" href="{{ asset('css/auth-login.css') }}">
    <style>
        /* Smooth transitions for theme switching */
        * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Theme Toggle Button -->
    <div style="position: fixed; top: 1rem; right: 1rem; z-index: 1000;">
        <button id="theme-toggle" 
                style="background: rgba(255, 255, 255, 0.9); border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.5rem; cursor: pointer; backdrop-filter: blur(10px);"
                class="theme-toggle-btn">
            <svg id="sun-icon" class="w-5 h-5" style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <svg id="moon-icon" class="w-5 h-5" style="width: 20px; height: 20px; display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
        </button>
    </div>

    <div class="auth-container">
        <!-- Header -->
        <div class="auth-header">
            <div class="logo-container">
                <div class="logo-item">
                    <img src="{{ asset('img/Logo BPS 1.png') }}" alt="Logo BPS" class="bps-logo">
                </div>
                <div class="logo-item">
                    <img src="{{ asset('img/Logo SE2026.png') }}" alt="Logo SE2026" class="se2026-logo">
                </div>
            </div>
            <h1 class="auth-title">Masuk ke Akun</h1>
            <div class="auth-divider"></div>
            <p class="auth-subtitle">Masuk untuk melanjutkan ke DataKita</p>
        </div>

        <!-- Server Errors -->
        @if ($errors->any())
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem;">
                <div style="display: flex; gap: 0.75rem;">
                    <div style="flex-shrink: 0; color: #ef4444;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <div style="color: #dc2626; font-size: 0.9rem;">
                        <ul style="margin: 0; padding-left: 1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Login Form -->
        <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="form-input"
                       placeholder="Masukkan alamat email"
                       autocomplete="email">
                <div id="emailError" class="error-message"></div>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Kata Sandi</label>
                <div class="password-wrapper">
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-input"
                           placeholder="Masukkan kata sandi"
                           autocomplete="current-password"
                           style="padding-right: 2.75rem;">
                    <button type="button"
                            id="togglePassword"
                            class="password-toggle">
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <div id="passwordError" class="error-message"></div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    id="submitBtn"
                    class="submit-btn"
                    disabled>
                <span id="submitText">Masuk</span>
                <div id="loadingSpinner" class="loading-spinner"></div>
            </button>
        </form>

        <!-- Footer Links -->
        <div class="auth-footer">
            <div class="footer-link">
                <a href="{{ route('password.request') }}">Lupa kata sandi?</a>
            </div>
            <div class="footer-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </div>
            <div class="footer-link">
                <a href="{{ route('home') }}">← Kembali ke Beranda</a>
            </div>
        </div>
    </div>

    <script>
        // Theme functionality - must be loaded before other scripts
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const sunIcon = document.getElementById('sun-icon');
            const moonIcon = document.getElementById('moon-icon');

            // Initialize theme from localStorage
            function initializeTheme() {
                if (localStorage.theme === 'dark') {
                    document.documentElement.classList.add('dark');
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                } else {
                    document.documentElement.classList.remove('dark');
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                    localStorage.theme = 'light';
                }
            }

            // Toggle theme
            function toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.theme = 'light';
                    sunIcon.style.display = 'block';
                    moonIcon.style.display = 'none';
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.theme = 'dark';
                    sunIcon.style.display = 'none';
                    moonIcon.style.display = 'block';
                }
            }

            // Initialize theme on page load
            initializeTheme();

            // Add event listener
            if (themeToggle) {
                themeToggle.addEventListener('click', toggleTheme);
            }
        });
    </script>
    <script src="{{ asset('js/auth-login.js') }}"></script>
</body>
</html>
