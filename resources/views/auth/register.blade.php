<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar - DataKita</title>
    <link rel="stylesheet" href="{{ asset('css/auth-register.css') }}">
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
            <h1 class="auth-title">Buat Akun</h1>
            <div class="auth-divider"></div>
            <p class="auth-subtitle">Daftar untuk mengakses layanan dan data statistik DataKita</p>
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

        <!-- Register Form -->
        <form id="registerForm" method="POST" action="{{ route('register') }}" novalidate>
            @csrf

            <!-- Nama Lengkap -->
            <div class="form-group">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       class="form-input"
                       placeholder="Masukkan nama lengkap"
                       autocomplete="name">
                <div id="nameError" class="error-message"></div>
                <div id="nameSuccess" class="success-message"></div>
            </div>

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
                <div id="emailSuccess" class="success-message"></div>
            </div>

            <!-- User Type Field -->
            <div class="form-group">
                <label for="user_type" class="form-label">Jenis Pengguna</label>
                <select id="user_type"
                        name="user_type"
                        class="form-select">
                    <option value="">Pilih jenis pengguna</option>
                    <option value="personal" {{ old('user_type') == 'personal' ? 'selected' : '' }}>Personal</option>
                    <option value="instansi" {{ old('user_type') == 'instansi' ? 'selected' : '' }}>Instansi</option>
                    <option value="akademisi" {{ old('user_type') == 'akademisi' ? 'selected' : '' }}>Akademisi</option>
                </select>
                <div class="helper-text">Pilih kategori yang sesuai dengan status Anda.</div>
                <div id="userTypeError" class="error-message"></div>
                <div id="userTypeSuccess" class="success-message"></div>
            </div>

            <!-- Institution Fields (Hidden by default) -->
            <div id="institutionFields" class="institution-fields">
                <!-- Institution Type -->
                <div class="form-group">
                    <label for="institution_type" class="form-label">
                        Jenis Institusi
                        <span class="required-asterisk" style="color: #ef4444;">*</span>
                    </label>
                    <select id="institution_type"
                            name="institution_type"
                            class="form-select">
                        <option value="">Pilih jenis institusi</option>
                        <option value="pemerintah" {{ old('institution_type') == 'pemerintah' ? 'selected' : '' }}>Pemerintah</option>
                        <option value="swasta" {{ old('institution_type') == 'swasta' ? 'selected' : '' }}>Swasta</option>
                        <option value="universitas" {{ old('institution_type') == 'universitas' ? 'selected' : '' }}>Universitas</option>
                        <option value="sekolah" {{ old('institution_type') == 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                        <option value="institut" {{ old('institution_type') == 'institut' ? 'selected' : '' }}>Institut</option>
                        <option value="politeknik" {{ old('institution_type') == 'politeknik' ? 'selected' : '' }}>Politeknik</option>
                        <option value="lembaga_penelitian" {{ old('institution_type') == 'lembaga_penelitian' ? 'selected' : '' }}>Lembaga Penelitian</option>
                        <option value="perusahaan" {{ old('institution_type') == 'perusahaan' ? 'selected' : '' }}>Perusahaan</option>
                        <option value="organisasi" {{ old('institution_type') == 'organisasi' ? 'selected' : '' }}>Organisasi</option>
                        <option value="lainnya" {{ old('institution_type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    <div id="institutionTypeError" class="error-message"></div>
                </div>

                <!-- Institution Name -->
                <div class="form-group">
                    <label for="institution_name" class="form-label">
                        Nama Institusi
                        <span class="required-asterisk" style="color: #ef4444;">*</span>
                    </label>
                    <input type="text"
                           id="institution_name"
                           name="institution_name"
                           value="{{ old('institution_name') }}"
                           class="form-input"
                           placeholder="Masukkan nama institusi">
                    <div id="institutionNameError" class="error-message"></div>
                </div>

                <!-- Institution Address (Only for instansi) -->
                <div class="form-group" id="institutionAddressGroup" style="display: none;">
                    <label for="institution_address" class="form-label">
                        Alamat Institusi
                    </label>
                    <textarea id="institution_address"
                              name="institution_address"
                              class="form-input"
                              rows="3"
                              placeholder="Masukkan alamat lengkap institusi">{{ old('institution_address') }}</textarea>
                    <div id="institutionAddressError" class="error-message"></div>
                </div>

                <!-- Institution Phone (Only for instansi) -->
                <div class="form-group" id="institutionPhoneGroup" style="display: none;">
                    <label for="institution_phone" class="form-label">
                        Nomor Telepon Institusi
                    </label>
                    <input type="tel"
                           id="institution_phone"
                           name="institution_phone"
                           value="{{ old('institution_phone') }}"
                           class="form-input"
                           placeholder="Masukkan nomor telepon institusi">
                    <div id="institutionPhoneError" class="error-message"></div>
                </div>
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Kata Sandi</label>
                <div class="password-wrapper">
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-input"
                           placeholder="Buat kata sandi yang kuat"
                           autocomplete="new-password"
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
                <div class="password-strength">
                    <div class="strength-bar">
                        <div id="strengthFill" class="strength-fill"></div>
                    </div>
                    <div id="strengthText" style="font-size: 0.8rem; color: #6b7280;"></div>
                </div>
                <div id="passwordError" class="error-message"></div>
            </div>

            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                <div class="password-wrapper">
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-input"
                           placeholder="Ulangi kata sandi"
                           autocomplete="new-password"
                           style="padding-right: 2.75rem;">
                    <button type="button"
                            id="togglePasswordConfirm"
                            class="password-toggle">
                        <svg id="eyeIconConfirm" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <div class="helper-text">Konfirmasi kata sandi harus sama dengan kata sandi.</div>
                <div id="password_confirmationError" class="error-message"></div>
                <div id="password_confirmationSuccess" class="success-message"></div>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    id="submitBtn"
                    class="submit-btn"
                    disabled>
                <span id="submitText">Buat Akun</span>
                <div id="loadingSpinner" class="loading-spinner"></div>
            </button>
        </form>

        <!-- Footer Links -->
        <div class="auth-footer">
            <div class="footer-link">
                Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
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
    <script src="{{ asset('js/auth-register.js') }}"></script>
</body>
</html>
