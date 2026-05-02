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
            @php
                $firstError  = $errors->first();
                $isThrottled = str_contains($firstError, 'seconds') || str_contains($firstError, 'menit') || str_contains($firstError, 'Too many');
            @endphp

            @if ($isThrottled)
                {{-- ── Throttle / Account-locked state ── --}}
                <div id="throttleBox" style="background: #fff7ed; border: 1px solid #fed7aa; border-radius: 0.75rem; padding: 1rem; margin-bottom: 1.5rem;">
                    <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                        <div style="flex-shrink: 0; color: #ea580c; margin-top: 2px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <div>
                            <div style="color: #9a3412; font-weight: 600; font-size: 0.9rem; margin-bottom: 0.25rem;">Terlalu banyak percobaan</div>
                            <div style="color: #c2410c; font-size: 0.85rem;">Akun sementara terkunci. Silakan tunggu sebelum mencoba lagi.</div>
                            <div id="loginCountdown" style="color: #ea580c; font-size: 0.85rem; margin-top: 0.4rem; font-weight: 500;"></div>
                        </div>
                    </div>
                </div>
            @else
                {{-- ── Regular error ── --}}
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
        @endif

        <!-- Attempt warning injected by JS -->
        <div id="attemptWarning" style="display:none; background: #fffbeb; border: 1px solid #fde68a; border-radius: 0.75rem; padding: 0.75rem 1rem; margin-bottom: 1rem;">
            <div style="display: flex; gap: 0.6rem; align-items: center;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="flex-shrink:0;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span id="attemptWarningText" style="color: #92400e; font-size: 0.85rem;"></span>
            </div>
        </div>

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
    <script>
    (function () {
        var MAX_ATTEMPTS  = 5;
        var LOCK_MINUTES  = 15; // must match FortifyServiceProvider perMinutes(15, 5)
        var LOCK_MS       = LOCK_MINUTES * 60 * 1000;
        var KEY_ATTEMPTS  = 'datakita_login_attempts';
        var KEY_LOCKED_AT = 'datakita_login_locked_at';

        var hadServerError = {{ $errors->any() && !($errors->first() && (str_contains($errors->first(), 'seconds') || str_contains($errors->first(), 'Too many'))) ? 'true' : 'false' }};
        var wasThrottled   = {{ ($errors->any() && (str_contains($errors->first(), 'seconds') || str_contains($errors->first(), 'Too many'))) ? 'true' : 'false' }};

        var attempts  = parseInt(localStorage.getItem(KEY_ATTEMPTS) || '0', 10);
        var lockedAt  = parseInt(localStorage.getItem(KEY_LOCKED_AT) || '0', 10);

        // If throttled by server, record lockout start time
        if (wasThrottled && !lockedAt) {
            lockedAt = Date.now();
            localStorage.setItem(KEY_LOCKED_AT, lockedAt.toString());
        }

        // If a regular login error occurred, increment attempt counter
        if (hadServerError) {
            attempts++;
            localStorage.setItem(KEY_ATTEMPTS, attempts.toString());
        }

        // Clear stale lockout
        if (lockedAt && (Date.now() - lockedAt) >= LOCK_MS) {
            localStorage.removeItem(KEY_LOCKED_AT);
            localStorage.removeItem(KEY_ATTEMPTS);
            lockedAt = 0;
            attempts = 0;
        }

        // Show attempt warning after 3 failures (but not when throttled — throttle box is already shown)
        if (!wasThrottled && attempts >= 3 && attempts < MAX_ATTEMPTS) {
            var remaining = MAX_ATTEMPTS - attempts;
            var warn = document.getElementById('attemptWarning');
            var txt  = document.getElementById('attemptWarningText');
            if (warn && txt) {
                txt.textContent = remaining + ' percobaan tersisa sebelum akun dikunci sementara.';
                warn.style.display = 'block';
            }
        }

        // Countdown on throttle box
        var countdownEl = document.getElementById('loginCountdown');
        if (wasThrottled && lockedAt && countdownEl) {
            function tickLock() {
                var elapsed = Math.floor((Date.now() - lockedAt) / 1000);
                var left    = Math.max(0, LOCK_MINUTES * 60 - elapsed);
                if (left === 0) {
                    clearInterval(lockTimer);
                    localStorage.removeItem(KEY_LOCKED_AT);
                    localStorage.removeItem(KEY_ATTEMPTS);
                    countdownEl.textContent = 'Anda dapat mencoba lagi sekarang. Silakan refresh halaman.';
                    return;
                }
                var m = Math.floor(left / 60);
                var s = left % 60;
                countdownEl.textContent = 'Coba lagi dalam ' + m + ':' + (s < 10 ? '0' : '') + s;
            }
            tickLock();
            var lockTimer = setInterval(tickLock, 1000);
        }

        // Reset attempt counter on successful form submit (optimistic)
        var form = document.getElementById('loginForm');
        if (form) {
            form.addEventListener('submit', function () {
                // Counter reset happens on next page load only if no errors appear
            });
        }

        // Clear counter if user navigated here fresh (no errors at all)
        if (!hadServerError && !wasThrottled) {
            // Don't clear — preserve across navigations so the counter is meaningful
        }
    })();
    </script>
</body>
</html>
