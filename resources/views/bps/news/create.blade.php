@extends('layouts.bps')

@section('title', 'Tambah Berita - BPS Dashboard')
@section('description', 'Halaman tambah berita BPS Kota Batam')

@section('content')
    <div class="bps-card">
        <div class="bps-card-header">
            <h1 class="bps-title">Tambah Berita</h1>
            <a href="{{ route('bps.news.index') }}" class="bps-btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </a>
        </div>

        <div class="bps-card-body">
            <form action="{{ route('bps.news.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="title" class="bps-label">Judul Berita</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" class="bps-input" required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="bps-label">Kategori</label>
                        <select name="category" id="category" class="bps-input" required>
                            <option value="">Pilih Kategori</option>
                            <option value="BRS" {{ old('category') == 'BRS' ? 'selected' : '' }}>BRS</option>
                            <option value="Event" {{ old('category') == 'Event' ? 'selected' : '' }}>Event</option>
                            <option value="Publikasi" {{ old('category') == 'Publikasi' ? 'selected' : '' }}>Publikasi</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="date" class="bps-label">Tanggal</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}" class="bps-input pl-10" required>
                        </div>
                        @error('date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="thumbnail" class="bps-label">Thumbnail</label>
                        <div class="mt-1 flex items-center">
                            <label class="block w-full">
                                <span class="sr-only">Pilih Thumbnail</span>
                                <input type="file" name="thumbnail" id="thumbnail" class="block w-full text-sm text-gray-500 dark:text-gray-400
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    dark:file:bg-blue-900/20 dark:file:text-blue-400
                                    hover:file:bg-blue-100 dark:hover:file:bg-blue-900/30
                                    transition-colors duration-200">
                            </label>
                        </div>
                        <p class="bps-help-text">Format: JPG, PNG, GIF. Maks: 2MB</p>
                        @error('thumbnail')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="excerpt" class="bps-label">Ringkasan</label>
                    <textarea name="excerpt" id="excerpt" rows="3" class="bps-input" required>{{ old('excerpt') }}</textarea>
                    <p class="bps-help-text">Maksimal 500 karakter</p>
                    @error('excerpt')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="source_url" class="bps-label">URL Sumber (BPS Website)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.172 13.828a4 4 0 015.656 0l4 4a4 4 0 01-5.656 5.656l-1.102-1.101" />
                            </svg>
                        </div>
                        <input type="url" name="source_url" id="source_url" value="{{ old('source_url') }}" class="bps-input pl-10" required placeholder="https://batamkota.bps.go.id/...">
                    </div>
                    <p class="bps-help-text">Contoh: https://batamkota.bps.go.id/pressrelease/2025/05/02/701/...</p>
                    @error('source_url')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="has_video" id="has_video" value="1" {{ old('has_video') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 dark:border-gray-700 text-blue-600 dark:text-blue-500 shadow-sm focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600">
                        <label for="has_video" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Memiliki Video</label>
                    </div>
                </div>

                <div id="video_url_container" class="mb-6 {{ old('has_video') ? '' : 'hidden' }}">
                    <label for="video_url" class="bps-label">URL Video</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input type="url" name="video_url" id="video_url" value="{{ old('video_url') }}" class="bps-input pl-10" placeholder="https://www.youtube.com/watch?v=...">
                    </div>
                    <p class="bps-help-text">Contoh: https://www.youtube.com/watch?v=example</p>
                    @error('video_url')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bps-info-box mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Informasi</h3>
                            <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                                <p>Berita yang ditambahkan akan ditampilkan di halaman utama website. Pastikan data yang dimasukkan sudah benar.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ route('bps.news.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 active:bg-gray-400 dark:active:bg-gray-600 focus:outline-none focus:border-gray-400 dark:focus:border-gray-500 focus:ring ring-gray-200 dark:ring-gray-600 disabled:opacity-25 transition-all duration-300">
                        Batal
                    </a>
                    <button type="submit" class="bps-btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Simpan Berita
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hasVideoCheckbox = document.getElementById('has_video');
        const videoUrlContainer = document.getElementById('video_url_container');

        hasVideoCheckbox.addEventListener('change', function() {
            if (this.checked) {
                videoUrlContainer.classList.remove('hidden');
            } else {
                videoUrlContainer.classList.add('hidden');
            }
        });
    });
</script>
@endpush
