@extends('layouts.bps')

@section('title', 'Edit Video - BPS Dashboard')
@section('description', 'Halaman edit video BPS Kota Batam')

@section('content')
    <div class="bps-card">
        <div class="bps-card-header">
            <h1 class="bps-title">Edit Video</h1>
            <a href="{{ route('bps.videos.index') }}" class="bps-btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        <div class="bps-card-body">
            <form action="{{ route('bps.videos.update', $video->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="title" class="bps-label">Judul Video</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $video->title) }}" class="bps-input" required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date" class="bps-label">Tanggal</label>
                        <input type="date" name="date" id="date" value="{{ old('date', $video->date->format('Y-m-d')) }}" class="bps-input" required>
                        @error('date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="url" class="bps-label">URL Video</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="url" name="url" id="url" value="{{ old('url', $video->url) }}" class="bps-input pl-10" required placeholder="https://www.youtube.com/watch?v=..." onchange="previewYouTubeThumbnail(this.value)">
                    </div>
                    <p class="bps-help-text">Contoh: https://www.youtube.com/watch?v=example</p>
                    @error('url')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror

                    <!-- YouTube Thumbnail Preview -->
                    <div id="youtube-thumbnail-preview" class="mt-3 hidden">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preview Thumbnail YouTube:</p>
                        <div class="relative w-full max-w-md aspect-video bg-gray-100 dark:bg-gray-800 rounded-md overflow-hidden">
                            <img id="youtube-thumbnail" src="" alt="YouTube Thumbnail Preview" class="w-full h-full object-cover">
                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-20 opacity-0 hover:opacity-100 transition-opacity">
                                <div class="rounded-full h-12 w-12 bg-blue-600/90 hover:bg-blue-600 text-white flex items-center justify-center transform hover:scale-110 transition-all duration-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Thumbnail ini akan digunakan jika Anda tidak mengunggah thumbnail kustom.</p>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="thumbnail" class="bps-label">Thumbnail</label>
                    <input type="file" name="thumbnail" id="thumbnail" class="bps-input">
                    <p class="bps-help-text">Format: JPG, PNG, GIF. Maks: 2MB</p>
                    @if($video->thumbnail)
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Thumbnail saat ini:</p>
                            <img src="{{ Storage::url($video->thumbnail) }}" alt="{{ $video->title }}" class="mt-1 h-20 w-auto object-cover rounded-md">
                        </div>
                    @endif
                    @error('thumbnail')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <div class="bps-info-box">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Informasi</h3>
                                <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                    <p>Jika Anda tidak mengunggah thumbnail baru, sistem akan tetap menggunakan thumbnail yang sudah ada.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bps-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Perbarui Video
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function extractYoutubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    function previewYouTubeThumbnail(url) {
        const videoId = extractYoutubeId(url);
        const previewContainer = document.getElementById('youtube-thumbnail-preview');
        const thumbnailImg = document.getElementById('youtube-thumbnail');

        if (videoId) {
            // Try maxresdefault first, with fallback to hqdefault
            thumbnailImg.src = `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`;
            thumbnailImg.onerror = function() {
                this.src = `https://img.youtube.com/vi/${videoId}/hqdefault.jpg`;
            };
            previewContainer.classList.remove('hidden');
        } else {
            previewContainer.classList.add('hidden');
        }
    }

    // Check if there's a URL already on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlInput = document.getElementById('url');
        if (urlInput.value) {
            previewYouTubeThumbnail(urlInput.value);
        }
    });
</script>
@endpush
