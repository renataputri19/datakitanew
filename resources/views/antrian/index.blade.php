@extends('layouts.app')

@section('title', 'Antrian Tamu - DataKita')
@section('description', 'Sistem Antrian Tamu BPS Kota Batam')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/antrian.css') }}">
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
    <div class="antrian-hero" data-aos="fade-up">
        <h1 class="antrian-hero-title">Sistem Antrian Tamu BPS Kota Batam</h1>
        <p class="antrian-hero-subtitle">Selamat datang di sistem antrian tamu BPS Kota Batam. Silahkan ikuti langkah-langkah berikut untuk mendapatkan nomor antrian.</p>
    </div>

    <div class="antrian-container">
        <div class="antrian-timeline" data-aos="fade-up">
            <!-- Step 1: Isi Buku Tamu -->
            <div class="antrian-step" data-aos="fade-up" data-aos-delay="50">
                <div class="antrian-step-number">1</div>
                <div class="antrian-step-content">
                    <h3 class="antrian-step-title">Isi Buku Tamu</h3>
                    <p class="antrian-step-description">
                        Langkah pertama adalah mengisi buku tamu digital untuk merekam kunjungan Anda.
                    </p>

                    <div class="mt-4">
                        <a href="https://perpustakaan.bps.go.id/digilib/guestbook" target="_blank" class="antrian-btn antrian-btn-indigo">
                            Isi Buku Tamu
                        </a>
                    </div>
                </div>
            </div>

            <!-- Step 2: Ambil Nomor Antrian -->
            <div class="antrian-step" data-aos="fade-up" data-aos-delay="100">
                <div class="antrian-step-number">2</div>
                <div class="antrian-step-content">
                    <h3 class="antrian-step-title">Ambil Nomor Antrian</h3>
                    <p class="antrian-step-description">
                        Setelah mengisi buku tamu, ambil nomor antrian Anda untuk mendapatkan layanan. Nomor antrian akan ditampilkan dan dapat dicetak.
                    </p>

                    <div class="mt-4">
                        <a href="{{ route('antrian.nomor') }}" class="antrian-btn antrian-btn-primary">
                            Ambil Nomor Antrian
                        </a>
                    </div>
                </div>
            </div>

            <!-- Step 3: Tunggu Panggilan -->
            <div class="antrian-step" data-aos="fade-up" data-aos-delay="150">
                <div class="antrian-step-number">3</div>
                <div class="antrian-step-content">
                    <h3 class="antrian-step-title">Tunggu Panggilan</h3>
                    <p class="antrian-step-description">
                        Silahkan tunggu hingga nomor antrian Anda dipanggil. Anda dapat memantau status antrian melalui layar monitor.
                    </p>

                    <div class="mt-4">
                        <a href="{{ route('antrian.monitor') }}" class="antrian-btn antrian-btn-purple">
                            Lihat Monitor Antrian
                        </a>
                    </div>
                </div>
            </div>

            <!-- Admin Section -->
            <div class="antrian-step" data-aos="fade-up" data-aos-delay="200">
                <div class="antrian-step-number">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="antrian-step-content">
                    <h3 class="antrian-step-title">Khusus Petugas</h3>
                    <p class="antrian-step-description">
                        Area ini hanya untuk petugas loket dan administrator sistem. Silahkan login terlebih dahulu untuk mengakses fitur ini.
                    </p>

                    @auth
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <button id="panggilAntrianBtn" class="antrian-btn antrian-btn-green">
                                Panggil Antrian
                            </button>

                            <a href="{{ route('antrian.setting') }}" class="antrian-btn antrian-btn-amber">
                                Pengaturan Sistem
                            </a>
                        </div>
                    @else
                        <div class="mt-4">
                            <a href="{{ route('login') }}" class="antrian-btn antrian-btn-primary">
                                Login untuk Akses Petugas
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Loket -->
<div id="modalPilihLoket" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity antrian-modal-backdrop" aria-hidden="true"></div>

        <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-md w-full mx-auto shadow-xl z-10">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Pilih Loket Antrian</h3>
                    <button id="closeModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <select id="loketAntrian" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-700 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white">
                        <option value="" selected>Pilih Loket Antrian</option>
                        <!-- Loket options will be loaded dynamically -->
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button id="cancelModal" class="antrian-btn px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button id="tampilkanAntrian" class="antrian-btn antrian-btn-primary px-4 py-2 text-sm font-medium">
                        Tampilkan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/antrian.js') }}"></script>
<script>
    // Set data attributes for JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const loketSelect = document.getElementById('loketAntrian');
        const tampilkanBtn = document.getElementById('tampilkanAntrian');

        if (loketSelect) {
            loketSelect.setAttribute('data-api-url', "{{ route('antrian.api.loket') }}");
        }

        if (tampilkanBtn) {
            tampilkanBtn.setAttribute('data-url', "{{ route('antrian.panggilan') }}");
        }
    });
</script>
@endpush
