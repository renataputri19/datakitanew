@extends('layouts.user-dashboard')

@section('title', 'Pengaturan - DataKita')
@section('description', 'Pengaturan akun dan profil pengguna')

@push('head')
<meta name="user-email" content="{{ auth()->user()->email }}">
@endpush

@section('dashboard-content')
<!-- Mobile/Tablet Menu Button -->
<div class="lg:hidden mb-4">
  <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200" type="button" data-open-sidebar aria-controls="dashboard-sidebar" aria-expanded="false">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
    </svg>
    Menu
  </button>
</div>

<!-- Page Header -->
<div class="ud-page-header" data-aos="fade-up">
    <div class="ud-page-header-content">
        <h1 class="ud-page-title">Pengaturan</h1>
        <p class="ud-page-description">Kelola informasi akun dan preferensi Anda</p>
    </div>
</div>

<!-- Success/Error Messages -->
@if(session('success'))
    <div class="ud-alert ud-alert-success ud-mb-4" data-aos="fade-up">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="ud-alert ud-alert-error ud-mb-4" data-aos="fade-up">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

<!-- Profile Settings -->
<div id="profile-section" class="ud-card ud-mb-4" data-aos="fade-up" data-aos-delay="100">
    <div class="ud-card-header">
        <h2 class="ud-card-title flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Informasi Profil
        </h2>
    </div>

    <form method="POST" action="{{ route('user.profile.update') }}" id="profile-form">
        @csrf
        @method('PUT')

        <div class="ud-grid ud-grid-cols-2">
            <div class="ud-form-group">
                <label for="name" class="ud-form-label">Nama Lengkap *</label>
                <input type="text" id="name" name="name" class="ud-form-input" value="{{ old('name', auth()->user()->name) }}" required>
                <div class="ud-form-error" id="name-error" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
                @error('name')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="ud-form-group">
                <label for="email" class="ud-form-label">Email *</label>
                <input type="email" id="email" name="email" class="ud-form-input" value="{{ old('email', auth()->user()->email) }}" required>
                <div class="ud-form-error" id="email-error" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
                <div class="success-message" id="email-success" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span></span>
                </div>
                @error('email')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="ud-form-group">
                <label for="user_type" class="ud-form-label">Tipe Pengguna *</label>
                <select id="user_type" name="user_type" class="ud-form-select" required>
                    <option value="">Pilih tipe pengguna</option>
                    <option value="personal" {{ old('user_type', $user->institution ? $user->institution->type : 'personal') == 'personal' ? 'selected' : '' }}>Personal</option>
                    <option value="instansi" {{ old('user_type', $user->institution ? $user->institution->type : 'personal') == 'instansi' ? 'selected' : '' }}>Instansi</option>
                    <option value="akademisi" {{ old('user_type', $user->institution ? $user->institution->type : 'personal') == 'akademisi' ? 'selected' : '' }}>Akademisi</option>
                </select>
                @error('user_type')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="ud-form-group conditional-field" id="institution-type-group">
                <label for="institution_type" class="ud-form-label">Jenis Instansi/Akademisi *</label>
                <select id="institution_type" name="institution_type" class="ud-form-select">
                    <option value="">Pilih jenis instansi</option>
                    @php
                        $currentInstitutionType = old('institution_type');
                        if (!$currentInstitutionType && $user->institution) {
                            $currentInstitutionType = $user->institution->institution_type ?? $user->institution->academic_type;
                            // Map academic types back to form values for akademisi users
                            if ($user->institution->type === 'akademisi' && $user->institution->academic_type) {
                                $academicToFormMap = [
                                    'university' => 'universitas',
                                    'college' => 'sekolah',
                                    'institute' => 'institut',
                                    'polytechnic' => 'politeknik',
                                    'research' => 'lembaga_penelitian',
                                    'other' => 'lainnya',
                                ];
                                $currentInstitutionType = $academicToFormMap[$user->institution->academic_type] ?? $user->institution->academic_type;
                            }
                        }
                    @endphp
                    <option value="pemerintah" {{ $currentInstitutionType == 'pemerintah' ? 'selected' : '' }}>Pemerintah</option>
                    <option value="swasta" {{ $currentInstitutionType == 'swasta' ? 'selected' : '' }}>Swasta</option>
                    <option value="universitas" {{ $currentInstitutionType == 'universitas' ? 'selected' : '' }}>Universitas</option>
                    <option value="sekolah" {{ $currentInstitutionType == 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                    <option value="institut" {{ $currentInstitutionType == 'institut' ? 'selected' : '' }}>Institut</option>
                    <option value="politeknik" {{ $currentInstitutionType == 'politeknik' ? 'selected' : '' }}>Politeknik</option>
                    <option value="lembaga_penelitian" {{ $currentInstitutionType == 'lembaga_penelitian' ? 'selected' : '' }}>Lembaga Penelitian</option>
                    <option value="perusahaan" {{ $currentInstitutionType == 'perusahaan' ? 'selected' : '' }}>Perusahaan</option>
                    <option value="organisasi" {{ $currentInstitutionType == 'organisasi' ? 'selected' : '' }}>Organisasi</option>
                    <option value="lainnya" {{ $currentInstitutionType == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                <div class="ud-form-error" id="institution_type-error" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
                @error('institution_type')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="ud-form-group conditional-field md:col-span-2" id="institution-name-group">
                <label for="institution_name" class="ud-form-label">Nama Instansi/Akademisi *</label>
                <input type="text" id="institution_name" name="institution_name" class="ud-form-input" value="{{ old('institution_name', $user->institution ? $user->institution->name : '') }}">
                <div class="ud-form-error" id="institution_name-error" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
                @error('institution_name')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Institution Address (Only for instansi) -->
            <div class="ud-form-group conditional-field md:col-span-2" id="institution-address-group">
                <label for="institution_address" class="ud-form-label">
                    Alamat Institusi
                </label>
                <textarea id="institution_address" name="institution_address" class="ud-form-textarea" rows="3" placeholder="Masukkan alamat lengkap institusi">{{ old('institution_address', $user->institution ? $user->institution->address : '') }}</textarea>
                <div class="ud-form-error" id="institution_address-error" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
                @error('institution_address')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Institution Phone (Only for instansi) -->
            <div class="ud-form-group conditional-field md:col-span-2" id="institution-phone-group">
                <label for="institution_phone" class="ud-form-label">
                    Nomor Telepon Institusi
                </label>
                <input type="tel" id="institution_phone" name="institution_phone" class="ud-form-input" value="{{ old('institution_phone', $user->institution ? $user->institution->phone : '') }}" placeholder="Masukkan nomor telepon institusi">
                <div class="ud-form-error" id="institution_phone-error" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
                @error('institution_phone')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-4 ud-mt-4">
            <button type="submit" class="ud-btn ud-btn-primary" id="profile-submit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Simpan Perubahan
            </button>
            <a href="{{ route('dashboard.profile') }}" class="ud-btn ud-btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Batal
            </a>
        </div>
    </form>
</div>

<!-- Password Settings -->
<div class="ud-card ud-mb-4" data-aos="fade-up" data-aos-delay="200">
    <div class="ud-card-header">
        <h2 class="ud-card-title flex items-center gap-2">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            Ubah Password
        </h2>
    </div>

    <form method="POST" action="{{ route('user.password.update') }}" id="password-form">
        @csrf
        @method('PUT')

        <div class="ud-grid ud-grid-cols-2">
            <div class="ud-form-group md:col-span-2">
                <label for="current_password" class="ud-form-label">Password Saat Ini *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="current_password" name="current_password" class="ud-form-input" required>
                    <button type="button" class="password-toggle" data-target="current_password">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg class="w-5 h-5 eye-closed" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                        </svg>
                    </button>
                </div>
                @error('current_password')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="ud-form-group">
                <label for="password" class="ud-form-label">Password Baru *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password" name="password" class="ud-form-input" required>
                    <button type="button" class="password-toggle" data-target="password">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg class="w-5 h-5 eye-closed" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                        </svg>
                    </button>
                </div>
                <div class="ud-form-error" id="password-error" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
                @error('password')
                    <div class="ud-form-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <div class="ud-form-group">
                <label for="password_confirmation" class="ud-form-label">Konfirmasi Password Baru *</label>
                <div class="password-input-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="ud-form-input" required>
                    <button type="button" class="password-toggle" data-target="password_confirmation">
                        <svg class="w-5 h-5 eye-open" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <svg class="w-5 h-5 eye-closed" style="display: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                        </svg>
                    </button>
                </div>
                <div class="ud-form-error" id="password-confirmation-error" style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span></span>
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row gap-4 ud-mt-4">
            <button type="submit" class="ud-btn ud-btn-primary" id="password-submit">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Ubah Password
            </button>
            <button type="button" class="ud-btn ud-btn-secondary" onclick="document.getElementById('password-form').reset();">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/user-dashboard-settings.js') }}"></script>
@endpush