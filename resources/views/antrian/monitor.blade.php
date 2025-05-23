@extends('layouts.app')

@section('title', 'Monitor Antrian - DataKita')
@section('description', 'Halaman monitor antrian tamu BPS Kota Batam')

@push('styles')
<style>
    .monitor-container {
        max-width: 1200px;
        margin: 0 auto;
    }

    .monitor-header {
        padding: 1.5rem;
        text-align: center;
        background-color: #8b5cf6;
        color: white;
        border-radius: 0.5rem 0.5rem 0 0;
    }

    .dark .monitor-header {
        background-color: #7c3aed;
    }

    .monitor-body {
        padding: 2rem;
        background-color: white;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    .dark .monitor-body {
        background-color: #1f2937;
    }

    .monitor-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
    }

    @media (max-width: 768px) {
        .monitor-grid {
            grid-template-columns: 1fr;
        }
    }

    .monitor-video {
        aspect-ratio: 16/9;
        width: 100%;
        border-radius: 0.5rem;
        overflow: hidden;
    }

    .loket-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }

    .loket-card {
        padding: 1.5rem;
        border-radius: 0.5rem;
        background-color: #f3f4f6;
        text-align: center;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .dark .loket-card {
        background-color: #374151;
    }

    .loket-card.active {
        transform: scale(1.05);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .loket-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #4b5563;
    }

    .dark .loket-title {
        color: #e5e7eb;
    }

    .loket-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: #8b5cf6;
    }

    .dark .loket-number {
        color: #a78bfa;
    }

    .running-text {
        padding: 1rem;
        background-color: #f3f4f6;
        border-radius: 0.5rem;
        margin-top: 2rem;
        overflow: hidden;
        white-space: nowrap;
    }

    .dark .running-text {
        background-color: #374151;
    }

    .running-text-content {
        display: inline-block;
        animation: marquee 40s linear infinite; /* Slowed down from 20s to 40s */
        font-size: 1.25rem;
        color: #4b5563;
    }

    .dark .running-text-content {
        color: #e5e7eb;
    }

    @keyframes marquee {
        0% { transform: translateX(100%); }
        100% { transform: translateX(-100%); }
    }

    /* Fullscreen button styles */
    .fullscreen-btn {
        position: absolute;
        top: 0.75rem;
        right: 0.75rem;
        z-index: 50;
        background-color: rgba(139, 92, 246, 0.8);
        color: white;
        border-radius: 0.5rem;
        padding: 0.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        width: 2.5rem;
        height: 2.5rem;
    }

    .fullscreen-btn:hover {
        background-color: rgba(139, 92, 246, 1);
        transform: scale(1.05);
    }

    .dark .fullscreen-btn {
        background-color: rgba(124, 58, 237, 0.8);
    }

    .dark .fullscreen-btn:hover {
        background-color: rgba(124, 58, 237, 1);
    }

    /* Fullscreen button position in fullscreen mode */
    .fullscreen-mode .fullscreen-btn {
        position: fixed;
        top: 0.75rem;
        right: 0.75rem;
        opacity: 0.5;
    }

    .fullscreen-mode .fullscreen-btn:hover {
        opacity: 1;
    }

    /* Fullscreen mode styles */
    .fullscreen-mode header,
    .fullscreen-mode footer,
    .fullscreen-mode .page-element {
        display: none !important;
    }

    .fullscreen-mode .monitor-container {
        margin-top: 0 !important;
        padding-top: 0 !important;
    }

    .fullscreen-mode #monitorContainer {
        padding: 0 !important;
        max-width: 100% !important;
    }

    .fullscreen-mode .monitor-header {
        border-radius: 0 !important;
    }

    .fullscreen-mode .monitor-body {
        border-radius: 0 !important;
    }

    .queue-stats {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        background-color: #f3f4f6;
        padding: 1rem;
        border-radius: 0.5rem;
    }

    .dark .queue-stats {
        background-color: #374151;
    }

    .queue-stat-item {
        text-align: center;
        flex: 1;
    }

    .queue-stat-value {
        font-size: 2rem;
        font-weight: 700;
        margin: 0.5rem 0;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8 md:px-6 md:py-12" id="monitorContainer">
    <div class="flex justify-between items-center mb-6 page-element">
        <a href="{{ route('antrian.index') }}" class="inline-flex items-center text-purple-600 dark:text-purple-400 hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Kembali
        </a>
    </div>

    <div class="mb-8 text-center page-element" data-aos="fade-up">
        <h1 class="text-3xl font-bold tracking-tight mb-2">Monitor Antrian</h1>
        <p class="text-gray-500 dark:text-gray-400">BPS Kota Batam</p>
    </div>

    <div class="monitor-container relative" data-aos="fade-up" data-aos-delay="100">
        <!-- Moved Fullscreen Button inside monitor container -->
        <button id="fullscreenBtn" class="fullscreen-btn" title="Tampilkan Layar Penuh">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
            </svg>
        </button>

        <div class="shadow-lg rounded-lg overflow-hidden">
            <div class="monitor-header">
                <h2 class="text-2xl font-bold">Informasi Antrian</h2>
                <p id="current-time" class="text-sm mt-1"></p>
            </div>
            <div class="monitor-body dark:bg-gray-800">
                <div class="queue-stats">
                    <div class="queue-stat-item">
                        <p class="text-gray-500 dark:text-gray-400">Total Antrian</p>
                        <div id="totalAntrian" class="queue-stat-value text-blue-600 dark:text-blue-500">0</div>
                    </div>
                    <div class="queue-stat-item">
                        <p class="text-gray-500 dark:text-gray-400">Sisa Antrian</p>
                        <div id="sisaAntrian" class="queue-stat-value text-amber-600 dark:text-amber-500">0</div>
                    </div>
                    <div class="queue-stat-item">
                        <p class="text-gray-500 dark:text-gray-400">Terlayani</p>
                        <div id="terlayaniAntrian" class="queue-stat-value text-green-600 dark:text-green-500">0</div>
                    </div>
                </div>

                <div class="monitor-grid">
                    <div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">Nomor Antrian</h3>
                        <div id="loket-container" class="loket-container">
                            <!-- Loket cards will be generated dynamically -->
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-200">Video</h3>
                        <div class="monitor-video">
                            <iframe
                                width="100%"
                                height="100%"
                                src="https://www.youtube.com/embed/L86LTnOeXQE?autoplay=1&mute=1&loop=1&playlist=L86LTnOeXQE"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>

                <div class="running-text">
                    <div class="running-text-content">
                        SELAMAT DATANG DI BPS KOTA BATAM - SILAHKAN MENGISI BUKU TAMU DIGITAL TERLEBIH DAHULU SEBELUM MENGAMBIL NOMOR ANTRIAN - TERIMA KASIH ATAS KUNJUNGAN ANDA - MOHON TERTIB DAN SABAR MENUNGGU GILIRAN ANDA
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const totalAntrianDisplay = document.getElementById('totalAntrian');
        const sisaAntrianDisplay = document.getElementById('sisaAntrian');
        const terlayaniAntrianDisplay = document.getElementById('terlayaniAntrian');
        const loketContainer = document.getElementById('loket-container');
        const currentTimeDisplay = document.getElementById('current-time');
        const fullscreenBtn = document.getElementById('fullscreenBtn');
        const monitorContainer = document.getElementById('monitorContainer');

        // Fullscreen functionality
        fullscreenBtn.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                // Enter fullscreen
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen();
                } else if (document.documentElement.webkitRequestFullscreen) { /* Safari */
                    document.documentElement.webkitRequestFullscreen();
                } else if (document.documentElement.msRequestFullscreen) { /* IE11 */
                    document.documentElement.msRequestFullscreen();
                }

                // Change button icon
                fullscreenBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
                fullscreenBtn.title = "Keluar dari Layar Penuh";

                // Add fullscreen-mode class to hide elements
                document.documentElement.classList.add('fullscreen-mode');
            } else {
                // Exit fullscreen
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.webkitExitFullscreen) { /* Safari */
                    document.webkitExitFullscreen();
                } else if (document.msExitFullscreen) { /* IE11 */
                    document.msExitFullscreen();
                }

                // Change button icon
                fullscreenBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                    </svg>
                `;
                fullscreenBtn.title = "Tampilkan Layar Penuh";

                // Remove fullscreen-mode class to show elements
                document.documentElement.classList.remove('fullscreen-mode');
            }
        });

        // Listen for fullscreen change
        document.addEventListener('fullscreenchange', handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', handleFullscreenChange);
        document.addEventListener('MSFullscreenChange', handleFullscreenChange);

        function handleFullscreenChange() {
            if (!document.fullscreenElement &&
                !document.webkitFullscreenElement &&
                !document.mozFullScreenElement &&
                !document.msFullscreenElement) {
                // Reset button when exiting fullscreen
                fullscreenBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                    </svg>
                `;
                fullscreenBtn.title = "Tampilkan Layar Penuh";

                // Remove fullscreen-mode class when exiting fullscreen
                document.documentElement.classList.remove('fullscreen-mode');
            }
        }

        // Update current time
        function updateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            currentTimeDisplay.textContent = now.toLocaleDateString('id-ID', options);
        }

        // Update time every second
        updateTime();
        setInterval(updateTime, 1000);

        // Function to update queue status
        function updateQueueStatus() {
            fetch("{{ route('antrian.api.status') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        totalAntrianDisplay.textContent = data.total;
                        sisaAntrianDisplay.textContent = data.remaining;
                        terlayaniAntrianDisplay.textContent = data.processed;

                        // Update loket cards
                        updateLoketCards(data.latest_calls);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Function to update loket cards
        function updateLoketCards(latestCalls) {
            // Get loket data
            fetch("{{ route('antrian.api.loket') }}")
                .then(response => response.json())
                .then(loketData => {
                    // Clear container
                    loketContainer.innerHTML = '';

                    // If no loket data, use default
                    if (!loketData || loketData.length === 0) {
                        loketData = [
                            { no_loket: "1", nama_loket: "Loket 1" },
                            { no_loket: "2", nama_loket: "Loket 2" },
                            { no_loket: "3", nama_loket: "Loket 3" }
                        ];
                    }

                    // Create a card for each loket
                    loketData.forEach(loket => {
                        const loketCard = document.createElement('div');
                        loketCard.className = 'loket-card';

                        // Find the latest call for this loket
                        const latestCall = latestCalls.find(call => call.loket === loket.no_loket);

                        // Add active class if this loket has the latest call
                        if (latestCall) {
                            loketCard.classList.add('active');
                        }

                        loketCard.innerHTML = `
                            <div class="loket-title">${loket.nama_loket}</div>
                            <div class="loket-number">${latestCall ? latestCall.antrian : '---'}</div>
                        `;

                        loketContainer.appendChild(loketCard);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Use default loket data in case of error
                    const defaultLokets = [
                        { no_loket: "1", nama_loket: "Loket 1" },
                        { no_loket: "2", nama_loket: "Loket 2" },
                        { no_loket: "3", nama_loket: "Loket 3" }
                    ];

                    // Clear container
                    loketContainer.innerHTML = '';

                    // Create a card for each default loket
                    defaultLokets.forEach(loket => {
                        const loketCard = document.createElement('div');
                        loketCard.className = 'loket-card';

                        // Find the latest call for this loket
                        const latestCall = latestCalls.find(call => call.loket === loket.no_loket);

                        // Add active class if this loket has the latest call
                        if (latestCall) {
                            loketCard.classList.add('active');
                        }

                        loketCard.innerHTML = `
                            <div class="loket-title">${loket.nama_loket}</div>
                            <div class="loket-number">${latestCall ? latestCall.antrian : '---'}</div>
                        `;

                        loketContainer.appendChild(loketCard);
                    });
                });
        }

        // Update queue status on page load
        updateQueueStatus();

        // Update queue status every 5 seconds
        setInterval(updateQueueStatus, 5000);
    });
</script>
@endpush
