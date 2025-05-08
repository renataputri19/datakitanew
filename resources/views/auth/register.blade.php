@extends('layouts.app')

@section('title', 'Register - DataKita')
@section('description', 'Daftar akun baru di DataKita untuk mengakses fitur lengkap')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-50 dark:bg-gray-900 px-4">
    <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-blue-600 dark:text-blue-500">DataKita</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">
                    Buat akun baru untuk mengakses DataKita
                </p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-800/20 text-red-600 dark:text-red-500 p-4 rounded-md mb-6">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                        placeholder="Nama Lengkap">
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                        placeholder="nama@contoh.com">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                        placeholder="••••••••">
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                        placeholder="••••••••">
                </div>

                <div class="mb-4">
                    <label for="user_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Pengguna</label>
                    <select id="user_type" name="user_type"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white appearance-none">
                        <option value="" selected disabled>Pilih tipe pengguna</option>
                        <option value="personal" {{ old('user_type') == 'personal' ? 'selected' : '' }}>Personal</option>
                        <option value="instansi" {{ old('user_type') == 'instansi' ? 'selected' : '' }}>Instansi</option>
                        <option value="akademisi" {{ old('user_type') == 'akademisi' ? 'selected' : '' }}>Akademisi</option>
                    </select>
                </div>

                <!-- Institution fields (shown conditionally) -->
                <div id="institution-fields" class="hidden">
                    <!-- Instansi fields -->
                    <div id="instansi-fields">
                        <div class="mb-4">
                            <label for="institution_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipe Instansi</label>
                            <select id="institution_type" name="institution_type"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white appearance-none">
                                <option value="" selected disabled>Pilih tipe instansi</option>
                                <option value="pemerintah" {{ old('institution_type') == 'pemerintah' ? 'selected' : '' }}>Pemerintah</option>
                                <option value="swasta" {{ old('institution_type') == 'swasta' ? 'selected' : '' }}>Swasta</option>
                                <option value="bumn" {{ old('institution_type') == 'bumn' ? 'selected' : '' }}>BUMN</option>
                                <option value="bumd" {{ old('institution_type') == 'bumd' ? 'selected' : '' }}>BUMD</option>
                                <option value="lainnya" {{ old('institution_type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="institution_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Instansi</label>
                            <input type="text" id="institution_name" name="institution_name" value="{{ old('institution_name') }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                                placeholder="Nama instansi">
                        </div>
                    </div>

                    <!-- Akademisi fields -->
                    <div id="akademisi-fields" class="hidden">
                        <div class="mb-4">
                            <label for="academic_institution" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Institusi Akademik</label>
                            <select id="academic_institution" name="institution_name"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white appearance-none">
                                <option value="" selected disabled>Pilih institusi akademik</option>

                                <!-- Perguruan Tinggi di Batam -->
                                <optgroup label="Perguruan Tinggi di Batam">
                                    <option value="Universitas Internasional Batam" {{ old('institution_name') == 'Universitas Internasional Batam' ? 'selected' : '' }}>Universitas Internasional Batam (UIB)</option>
                                    <option value="Politeknik Negeri Batam" {{ old('institution_name') == 'Politeknik Negeri Batam' ? 'selected' : '' }}>Politeknik Negeri Batam</option>
                                    <option value="Universitas Batam" {{ old('institution_name') == 'Universitas Batam' ? 'selected' : '' }}>Universitas Batam (UNIBA)</option>
                                    <option value="Universitas Putera Batam" {{ old('institution_name') == 'Universitas Putera Batam' ? 'selected' : '' }}>Universitas Putera Batam (UPB)</option>
                                    <option value="Universitas Riau Kepulauan" {{ old('institution_name') == 'Universitas Riau Kepulauan' ? 'selected' : '' }}>Universitas Riau Kepulauan (UNRIKA)</option>
                                    <option value="Universitas Universal" {{ old('institution_name') == 'Universitas Universal' ? 'selected' : '' }}>Universitas Universal</option>
                                    <option value="Universitas Ibnu Sina" {{ old('institution_name') == 'Universitas Ibnu Sina' ? 'selected' : '' }}>Universitas Ibnu Sina</option>
                                    <option value="STMIK GICI" {{ old('institution_name') == 'STMIK GICI' ? 'selected' : '' }}>STMIK GICI</option>
                                    <option value="Akademi Akuntansi Permata Harapan" {{ old('institution_name') == 'Akademi Akuntansi Permata Harapan' ? 'selected' : '' }}>Akademi Akuntansi Permata Harapan</option>
                                    <option value="Akademi Bahasa Asing Permata Harapan" {{ old('institution_name') == 'Akademi Bahasa Asing Permata Harapan' ? 'selected' : '' }}>Akademi Bahasa Asing Permata Harapan</option>
                                    <option value="Akademi Kebidanan Permata Harapan" {{ old('institution_name') == 'Akademi Kebidanan Permata Harapan' ? 'selected' : '' }}>Akademi Kebidanan Permata Harapan</option>
                                    <option value="Akademi Keperawatan Hang Tuah" {{ old('institution_name') == 'Akademi Keperawatan Hang Tuah' ? 'selected' : '' }}>Akademi Keperawatan Hang Tuah</option>
                                </optgroup>

                                <!-- Perguruan Tinggi di Kepulauan Riau (di luar Batam) -->
                                <optgroup label="Perguruan Tinggi di Kepulauan Riau (di luar Batam)">
                                    <option value="Universitas Maritim Raja Ali Haji" {{ old('institution_name') == 'Universitas Maritim Raja Ali Haji' ? 'selected' : '' }}>Universitas Maritim Raja Ali Haji (UMRAH)</option>
                                    <option value="Sekolah Tinggi Ilmu Sosial dan Ilmu Politik Raja Haji" {{ old('institution_name') == 'Sekolah Tinggi Ilmu Sosial dan Ilmu Politik Raja Haji' ? 'selected' : '' }}>STISIP Raja Haji</option>
                                    <option value="STAIN Sultan Abdurrahman Kepri" {{ old('institution_name') == 'STAIN Sultan Abdurrahman Kepri' ? 'selected' : '' }}>STAIN Sultan Abdurrahman Kepri</option>
                                    <option value="Politeknik Bintan Cakrawala" {{ old('institution_name') == 'Politeknik Bintan Cakrawala' ? 'selected' : '' }}>Politeknik Bintan Cakrawala</option>
                                    <option value="Sekolah Tinggi Ilmu Ekonomi Pembangunan Tanjungpinang" {{ old('institution_name') == 'Sekolah Tinggi Ilmu Ekonomi Pembangunan Tanjungpinang' ? 'selected' : '' }}>STIE Pembangunan Tanjungpinang</option>
                                </optgroup>

                                <!-- Perguruan Tinggi di Luar Kepulauan Riau -->
                                <optgroup label="Perguruan Tinggi di Luar Kepulauan Riau">
                                    <option value="Universitas Indonesia" {{ old('institution_name') == 'Universitas Indonesia' ? 'selected' : '' }}>Universitas Indonesia</option>
                                    <option value="Institut Teknologi Bandung" {{ old('institution_name') == 'Institut Teknologi Bandung' ? 'selected' : '' }}>Institut Teknologi Bandung</option>
                                    <option value="Universitas Gadjah Mada" {{ old('institution_name') == 'Universitas Gadjah Mada' ? 'selected' : '' }}>Universitas Gadjah Mada</option>
                                    <option value="Politeknik Statistika STIS" {{ old('institution_name') == 'Politeknik Statistika STIS' ? 'selected' : '' }}>Politeknik Statistika STIS</option>
                                </optgroup>

                                <option value="Lainnya" {{ old('institution_name') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        <div id="other-academic-institution" class="mb-4 hidden">
                            <label for="other_institution_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Institusi Akademik Lainnya</label>
                            <input type="text" id="other_institution_name" name="other_institution_name" value="{{ old('other_institution_name') }}"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                                placeholder="Nama institusi akademik">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="institution_address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Instansi/Institusi</label>
                        <input type="text" id="institution_address" name="institution_address" value="{{ old('institution_address') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                            placeholder="Alamat instansi/institusi">
                    </div>

                    <div class="mb-4">
                        <label for="institution_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telepon Instansi/Institusi</label>
                        <input type="text" id="institution_phone" name="institution_phone" value="{{ old('institution_phone') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                            placeholder="Nomor telepon instansi/institusi">
                    </div>

                    <div class="mb-4">
                        <label for="institution_website" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Website Instansi/Institusi</label>
                        <input type="url" id="institution_website" name="institution_website" value="{{ old('institution_website') }}"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-600 dark:focus-visible:ring-blue-600 dark:bg-gray-900 dark:text-white"
                            placeholder="https://example.com">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md transition-colors duration-300">
                    Daftar
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Sudah memiliki akun? <a href="{{ route('login') }}" class="text-blue-600 dark:text-blue-500 hover:underline">Masuk</a>
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const userTypeSelect = document.getElementById('user_type');
        const institutionFields = document.getElementById('institution-fields');
        const instansiFields = document.getElementById('instansi-fields');
        const akademisiFields = document.getElementById('akademisi-fields');
        const academicInstitutionSelect = document.getElementById('academic_institution');
        const otherAcademicInstitution = document.getElementById('other-academic-institution');

        // Function to toggle institution fields visibility
        function toggleInstitutionFields() {
            const selectedValue = userTypeSelect.value;

            // Hide all fields first
            institutionFields.classList.add('hidden');
            instansiFields.classList.add('hidden');
            akademisiFields.classList.add('hidden');

            if (selectedValue === 'instansi') {
                // Show instansi fields
                institutionFields.classList.remove('hidden');
                instansiFields.classList.remove('hidden');
                document.getElementById('institution_name').setAttribute('required', 'required');
            } else if (selectedValue === 'akademisi') {
                // Show akademisi fields
                institutionFields.classList.remove('hidden');
                akademisiFields.classList.remove('hidden');
                document.getElementById('academic_institution').setAttribute('required', 'required');
            }
        }

        // Function to toggle "other" academic institution field
        function toggleOtherAcademicInstitution() {
            if (academicInstitutionSelect.value === 'Lainnya') {
                otherAcademicInstitution.classList.remove('hidden');
                document.getElementById('other_institution_name').setAttribute('required', 'required');
            } else {
                otherAcademicInstitution.classList.add('hidden');
                document.getElementById('other_institution_name').removeAttribute('required');
            }
        }

        // Initial check
        toggleInstitutionFields();
        toggleOtherAcademicInstitution();

        // Listen for changes
        userTypeSelect.addEventListener('change', toggleInstitutionFields);
        academicInstitutionSelect.addEventListener('change', toggleOtherAcademicInstitution);

        // Check if there are validation errors and show fields if needed
        @if($errors->any())
            @if(old('user_type') == 'instansi')
                institutionFields.classList.remove('hidden');
                instansiFields.classList.remove('hidden');
            @elseif(old('user_type') == 'akademisi')
                institutionFields.classList.remove('hidden');
                akademisiFields.classList.remove('hidden');

                @if(old('institution_name') == 'Lainnya')
                    otherAcademicInstitution.classList.remove('hidden');
                @endif
            @endif
        @endif
    });
</script>
@endpush
@endsection
