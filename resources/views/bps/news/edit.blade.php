@extends('layouts.bps')

@section('title', 'Edit Berita - BPS Dashboard')
@section('description', 'Halaman edit berita BPS Kota Batam')

@section('content')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Berita</h1>
                <a href="{{ route('bps.news.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-gray-600 active:bg-gray-900 dark:active:bg-gray-800 focus:outline-none focus:border-gray-900 dark:focus:border-gray-800 focus:ring ring-gray-300 dark:ring-gray-700 disabled:opacity-25 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <div class="p-6">
            <form action="{{ route('bps.news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul Berita</label>
                        <input type="text" name="title" id="title" value="{{ old('title', $news->title) }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 shadow-sm" required>
                        @error('title')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                        <select name="category" id="category" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 shadow-sm" required>
                            <option value="">Pilih Kategori</option>
                            <option value="BRS" {{ old('category', $news->category) == 'BRS' ? 'selected' : '' }}>BRS</option>
                            <option value="Event" {{ old('category', $news->category) == 'Event' ? 'selected' : '' }}>Event</option>
                            <option value="Publikasi" {{ old('category', $news->category) == 'Publikasi' ? 'selected' : '' }}>Publikasi</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal</label>
                        <input type="date" name="date" id="date" value="{{ old('date', $news->date->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 shadow-sm" required>
                        @error('date')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="thumbnail" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Thumbnail</label>
                        <input type="file" name="thumbnail" id="thumbnail" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 shadow-sm">
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Format: JPG, PNG, GIF. Maks: 2MB</p>
                        @if($news->thumbnail)
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Thumbnail saat ini:</p>
                                <img src="{{ Storage::url($news->thumbnail) }}" alt="{{ $news->title }}" class="mt-1 h-20 w-auto object-cover rounded-md">
                            </div>
                        @endif
                        @error('thumbnail')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mb-6">
                    <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ringkasan</label>
                    <textarea name="excerpt" id="excerpt" rows="3" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 shadow-sm" required>{{ old('excerpt', $news->excerpt) }}</textarea>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maksimal 500 karakter</p>
                    @error('excerpt')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="source_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL Sumber (BPS Website)</label>
                    <input type="url" name="source_url" id="source_url" value="{{ old('source_url', $news->source_url) }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 shadow-sm" required>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Contoh: https://batamkota.bps.go.id/pressrelease/2025/05/02/701/...</p>
                    @error('source_url')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" name="has_video" id="has_video" value="1" {{ old('has_video', $news->has_video) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-700 text-blue-600 dark:text-blue-500 shadow-sm focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600">
                        <label for="has_video" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Memiliki Video</label>
                    </div>
                </div>

                <div id="video_url_container" class="mb-6 {{ old('has_video', $news->has_video) ? '' : 'hidden' }}">
                    <label for="video_url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">URL Video</label>
                    <input type="url" name="video_url" id="video_url" value="{{ old('video_url', $news->video_url) }}" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-600 focus:ring-blue-500 dark:focus:ring-blue-600 shadow-sm">
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Contoh: https://www.youtube.com/watch?v=example</p>
                    @error('video_url')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 focus:outline-none focus:border-blue-800 focus:ring ring-blue-300 disabled:opacity-25 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Perbarui Berita
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
