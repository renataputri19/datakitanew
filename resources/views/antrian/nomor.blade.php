@extends('layouts.app')

@section('title', 'Nomor Antrian - DataKita')
@section('description', 'Halaman pengambilan nomor antrian tamu BPS Kota Batam')

@push('styles')
<style>
    .antrian-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .antrian-card {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .antrian-header {
        padding: 1.5rem;
        text-align: center;
        background-color: #3b82f6;
        color: white;
    }

    .dark .antrian-header {
        background-color: #2563eb;
    }

    .antrian-body {
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .antrian-number {
        font-size: 5rem;
        font-weight: 700;
        margin: 1.5rem 0;
        color: #1e40af;
    }

    .dark .antrian-number {
        color: #3b82f6;
    }

    .antrian-button {
        margin-top: 1.5rem;
        padding: 0.75rem 2rem;
        font-size: 1.25rem;
        font-weight: 600;
        border-radius: 0.5rem;
        background-color: #3b82f6;
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .antrian-button:hover {
        background-color: #2563eb;
        transform: translateY(-2px);
    }

    .antrian-button:disabled {
        background-color: #93c5fd;
        cursor: not-allowed;
        transform: none;
    }

    .dark .antrian-button {
        background-color: #2563eb;
    }

    .dark .antrian-button:hover {
        background-color: #1d4ed8;
    }

    .dark .antrian-button:disabled {
        background-color: #1e40af;
        opacity: 0.5;
    }

    .print-section {
        display: none;
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 2rem;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 8rem;
        position: relative;
    }

    .step:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 1rem;
        right: -2.5rem;
        width: 5rem;
        height: 2px;
        background-color: #e2e8f0;
    }

    .step-number {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        background-color: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }

    .step.active .step-number {
        background-color: #3b82f6;
        color: white;
    }

    .step.completed .step-number {
        background-color: #10b981;
        color: white;
    }

    .step-label {
        font-size: 0.875rem;
        color: #64748b;
        text-align: center;
    }

    .step.active .step-label {
        color: #3b82f6;
        font-weight: 600;
    }

    .step.completed .step-label {
        color: #10b981;
        font-weight: 600;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        .print-section, .print-section * {
            visibility: visible;
        }

        .print-section {
            display: block;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('antrian.index') }}" class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="mb-8 text-center" data-aos="fade-up">
        <h1 class="text-3xl font-bold tracking-tight mb-2">Nomor Antrian</h1>
        <p class="text-gray-500 dark:text-gray-400">Silahkan ambil nomor antrian Anda</p>

        <div class="step-indicator mt-8">
            <div class="step completed">
                <div class="step-number">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div class="step-label">Isi Buku Tamu</div>
            </div>
            <div class="step active">
                <div class="step-number">2</div>
                <div class="step-label">Ambil Nomor Antrian</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-label">Tunggu Panggilan</div>
            </div>
        </div>
    </div>

    <div class="antrian-container" data-aos="fade-up" data-aos-delay="100">
        <div class="antrian-card bg-white dark:bg-gray-800">
            <div class="antrian-header">
                <h2 class="text-2xl font-bold">BPS Kota Batam</h2>
                <p>Jl. Abulyatama Batam Center</p>
            </div>
            <div class="antrian-body">
                <div class="text-center">
                    <p class="text-lg text-gray-600 dark:text-gray-300">Nomor Antrian Anda</p>
                    <div id="antrian" class="antrian-number">---</div>
                    <p id="tanggal" class="text-gray-500 dark:text-gray-400"></p>
                </div>
                <button id="ambilAntrian" class="antrian-button">
                    Ambil Nomor
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print Section -->
<div id="print-section" class="print-section">
    <div style="width: 300px; padding: 20px; text-align: center; margin: 0 auto;">
        <h2 style="margin-bottom: 10px; font-size: 18px;">BPS Kota Batam</h2>
        <p style="margin-bottom: 20px; font-size: 14px;">Jl. Abulyatama Batam Center</p>
        <p style="margin-bottom: 5px; font-size: 14px;">Nomor Antrian</p>
        <div id="print-nomor" style="font-size: 60px; font-weight: bold; margin: 10px 0;">---</div>
        <p id="print-tanggal" style="margin-bottom: 5px; font-size: 14px;"></p>
        <p style="margin-top: 20px; font-size: 12px;">Terima kasih atas kunjungan Anda</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ambilAntrianBtn = document.getElementById('ambilAntrian');
        const antrianDisplay = document.getElementById('antrian');
        const tanggalDisplay = document.getElementById('tanggal');
        const printNomor = document.getElementById('print-nomor');
        const printTanggal = document.getElementById('print-tanggal');

        // Format current date
        const today = new Date();
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const formattedDate = today.toLocaleDateString('id-ID', options);

        tanggalDisplay.textContent = formattedDate;
        printTanggal.textContent = formattedDate;

        // Handle ambil antrian button click
        ambilAntrianBtn.addEventListener('click', function() {
            // Disable button while processing
            ambilAntrianBtn.disabled = true;
            ambilAntrianBtn.textContent = 'Memproses...';

            // Call API to generate queue number
            fetch("{{ route('antrian.api.generate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display queue number
                    antrianDisplay.textContent = data.no_antrian;
                    printNomor.textContent = data.no_antrian;

                    // Update step indicator
                    const steps = document.querySelectorAll('.step');
                    steps[1].classList.add('completed');
                    steps[1].classList.remove('active');
                    steps[2].classList.add('active');

                    // Print ticket
                    setTimeout(function() {
                        window.print();

                        // Re-enable button after printing
                        ambilAntrianBtn.disabled = false;
                        ambilAntrianBtn.textContent = 'Ambil Nomor';
                    }, 500);
                } else {
                    alert('Gagal mengambil nomor antrian. Silahkan coba lagi.');
                    ambilAntrianBtn.disabled = false;
                    ambilAntrianBtn.textContent = 'Ambil Nomor';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silahkan coba lagi.');
                ambilAntrianBtn.disabled = false;
                ambilAntrianBtn.textContent = 'Ambil Nomor';
            });
        });
    });
</script>
@endpush
