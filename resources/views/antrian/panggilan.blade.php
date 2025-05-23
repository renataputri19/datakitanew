@extends('layouts.app')

@section('title', 'Panggilan Antrian - DataKita')
@section('description', 'Halaman panggilan nomor antrian tamu BPS Kota Batam')

@push('styles')
<style>
    .panggilan-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .panggilan-card {
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .panggilan-header {
        padding: 1.5rem;
        text-align: center;
        background-color: #10b981;
        color: white;
    }

    .dark .panggilan-header {
        background-color: #059669;
    }

    .panggilan-body {
        padding: 2rem;
    }

    .panggilan-info {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
    }

    .panggilan-info-item {
        text-align: center;
        flex: 1;
    }

    .panggilan-info-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }

    .panggilan-number {
        font-size: 5rem;
        font-weight: 700;
        text-align: center;
        margin: 1.5rem 0;
        color: #047857;
    }

    .dark .panggilan-number {
        color: #10b981;
    }

    .panggilan-button {
        width: 100%;
        padding: 0.75rem 2rem;
        font-size: 1.25rem;
        font-weight: 600;
        border-radius: 0.5rem;
        background-color: #10b981;
        color: white;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .panggilan-button:hover {
        background-color: #059669;
        transform: translateY(-2px);
    }

    .panggilan-button:disabled {
        background-color: #6ee7b7;
        cursor: not-allowed;
        transform: none;
    }

    .dark .panggilan-button {
        background-color: #059669;
    }

    .dark .panggilan-button:hover {
        background-color: #047857;
    }

    .dark .panggilan-button:disabled {
        background-color: #047857;
        opacity: 0.5;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('antrian.index') }}" class="inline-flex items-center text-green-600 dark:text-green-400 hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="mb-8 text-center" data-aos="fade-up">
        <h1 class="text-3xl font-bold tracking-tight mb-2">Panggilan Antrian</h1>
        <p class="text-gray-500 dark:text-gray-400">Loket: <span id="loketDisplay" class="font-semibold">-</span></p>
    </div>

    <div class="panggilan-container" data-aos="fade-up" data-aos-delay="100">
        <div class="panggilan-card bg-white dark:bg-gray-800">
            <div class="panggilan-header">
                <h2 class="text-2xl font-bold">BPS Kota Batam</h2>
                <p>Jl. Abulyatama Batam Center</p>
            </div>
            <div class="panggilan-body">
                <div class="panggilan-info">
                    <div class="panggilan-info-item">
                        <p class="text-gray-500 dark:text-gray-400">Total Antrian</p>
                        <div id="totalAntrian" class="panggilan-info-value text-blue-600 dark:text-blue-500">0</div>
                    </div>
                    <div class="panggilan-info-item">
                        <p class="text-gray-500 dark:text-gray-400">Sisa Antrian</p>
                        <div id="sisaAntrian" class="panggilan-info-value text-amber-600 dark:text-amber-500">0</div>
                    </div>
                    <div class="panggilan-info-item">
                        <p class="text-gray-500 dark:text-gray-400">Terlayani</p>
                        <div id="terlayaniAntrian" class="panggilan-info-value text-green-600 dark:text-green-500">0</div>
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-lg text-gray-600 dark:text-gray-300">Nomor Antrian Saat Ini</p>
                    <div id="nomorAntrian" class="panggilan-number">---</div>
                </div>

                <button id="panggilAntrian" class="panggilan-button">
                    Panggil Antrian Berikutnya
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const panggilAntrianBtn = document.getElementById('panggilAntrian');
        const nomorAntrianDisplay = document.getElementById('nomorAntrian');
        const loketDisplay = document.getElementById('loketDisplay');
        const totalAntrianDisplay = document.getElementById('totalAntrian');
        const sisaAntrianDisplay = document.getElementById('sisaAntrian');
        const terlayaniAntrianDisplay = document.getElementById('terlayaniAntrian');

        // Get loket from localStorage
        const loket = localStorage.getItem('_loket');

        // If no loket is set, redirect back to index
        if (!loket) {
            alert('Silahkan pilih loket terlebih dahulu');
            window.location.href = "{{ route('antrian.index') }}";
            return;
        }

        // Display loket number
        loketDisplay.textContent = loket;

        // Function to update queue status
        function updateQueueStatus() {
            fetch("{{ route('antrian.api.status') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        totalAntrianDisplay.textContent = data.total;
                        sisaAntrianDisplay.textContent = data.remaining;
                        terlayaniAntrianDisplay.textContent = data.processed;

                        // Find the latest call for current loket
                        const latestCall = data.latest_calls.find(call => call.loket === loket);
                        if (latestCall) {
                            nomorAntrianDisplay.textContent = latestCall.antrian;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Update queue status on page load
        updateQueueStatus();

        // Handle panggil antrian button click
        panggilAntrianBtn.addEventListener('click', function() {
            // Disable button while processing
            panggilAntrianBtn.disabled = true;
            panggilAntrianBtn.textContent = 'Memproses...';

            // Call API to get next queue number
            fetch("{{ route('antrian.api.next') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ loket: loket })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display queue number
                    nomorAntrianDisplay.textContent = data.no_antrian;

                    // Play sound
                    playAntrianSound(data.no_antrian, data.loket);

                    // Update queue status
                    updateQueueStatus();
                } else {
                    alert(data.message || 'Gagal memanggil antrian berikutnya.');
                }

                // Re-enable button
                panggilAntrianBtn.disabled = false;
                panggilAntrianBtn.textContent = 'Panggil Antrian Berikutnya';
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan. Silahkan coba lagi.');
                panggilAntrianBtn.disabled = false;
                panggilAntrianBtn.textContent = 'Panggil Antrian Berikutnya';
            });
        });

        // Function to play antrian sound
        function playAntrianSound(nomor, loket) {
            try {
                // Create audio elements
                const audioQueue = [];

                // Add intro sound
                audioQueue.push(new Audio("{{ asset('sounds/bell.mp3') }}"));

                // Add nomor antrian sound
                audioQueue.push(new Audio("{{ asset('sounds/nomor-antrian.mp3') }}"));

                // Add individual digits
                const digits = nomor.toString().split('');
                digits.forEach(digit => {
                    audioQueue.push(new Audio("{{ asset('sounds/') }}/" + digit + ".mp3"));
                });

                // Add loket sound
                audioQueue.push(new Audio("{{ asset('sounds/loket.mp3') }}"));

                // Add loket number
                audioQueue.push(new Audio("{{ asset('sounds/') }}/" + loket + ".mp3"));

                // Play sounds sequentially
                let currentIndex = 0;

                function playNextSound() {
                    if (currentIndex < audioQueue.length) {
                        const sound = audioQueue[currentIndex];
                        sound.onended = playNextSound;
                        sound.play().catch(e => {
                            console.error('Error playing sound:', e);
                            // Move to next sound if there's an error
                            currentIndex++;
                            playNextSound();
                        });
                        currentIndex++;
                    }
                }

                // Start playing
                playNextSound();
            } catch (error) {
                console.error('Error in playAntrianSound:', error);
                // Continue with the application flow even if sound fails
            }
        }
    });
</script>
@endpush
