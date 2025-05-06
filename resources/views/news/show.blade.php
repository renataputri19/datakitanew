@extends('layouts.app')

@section('title', $newsItem['title'] . ' - Berita & Update - DataKita')
@section('description', $newsItem['excerpt'])

@section('content')
    <div class="container mx-auto px-4 py-8 md:px-6 md:py-12">
        <div class="mb-8 space-y-4">
            <div class="flex items-center gap-2" data-aos="fade-right">
                <a href="{{ route('news') }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold tracking-tight">{{ $newsItem['title'] }}</h1>
            </div>
            <div class="flex items-center gap-4" data-aos="fade-right" data-aos-delay="100">
                <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-500/30">
                    {{ $newsItem['category'] }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $newsItem['date'] }}
                </span>
            </div>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
            <div class="md:col-span-2 space-y-8">
                @if($newsItem['has_video'])
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md" data-aos="fade-up">
                    <div class="relative">
                        <div class="aspect-video bg-gray-200 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-12 w-12 text-gray-400 dark:text-gray-600">
                                <path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"></path>
                                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"></polygon>
                            </svg>
                        </div>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <a href="{{ $newsItem['video_url'] }}" target="_blank" class="rounded-full h-16 w-16 bg-blue-600/90 hover:bg-blue-600 text-white flex items-center justify-center transform hover:scale-110 transition-all duration-300" data-video-url="{{ $newsItem['video_url'] }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-8 w-8">
                                    <polygon points="5 3 19 12 5 21 5 3"></polygon>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md p-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="prose prose-blue max-w-none dark:prose-invert">
                        {!! $newsItem['content'] !!}
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md p-6" data-aos="fade-up" data-aos-delay="300">
                    <h2 class="text-xl font-bold mb-4">Bagikan</h2>
                    <div class="flex space-x-2">
                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-[#1877F2] text-white hover:bg-[#1877F2]/90 h-9 w-9 p-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                            </svg>
                            <span class="sr-only">Facebook</span>
                        </button>
                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-[#1DA1F2] text-white hover:bg-[#1DA1F2]/90 h-9 w-9 p-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                            </svg>
                            <span class="sr-only">Twitter</span>
                        </button>
                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-[#0A66C2] text-white hover:bg-[#0A66C2]/90 h-9 w-9 p-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path>
                                <rect width="4" height="12" x="2" y="9"></rect>
                                <circle cx="4" cy="4" r="2"></circle>
                            </svg>
                            <span class="sr-only">LinkedIn</span>
                        </button>
                        <button class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-[#25D366] text-white hover:bg-[#25D366]/90 h-9 w-9 p-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                            </svg>
                            <span class="sr-only">WhatsApp</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="space-y-8">
                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md p-6" data-aos="fade-left">
                    <h2 class="text-xl font-bold mb-4">Berita Terkait</h2>
                    <div class="space-y-4">
                        @foreach($newsItem['related_news'] as $relatedNews)
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-sm font-medium">{{ $relatedNews['title'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $relatedNews['date'] }}</p>
                            </div>
                            <a href="{{ route('news.show', $relatedNews['id']) }}" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 w-9 p-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4">
                                    <path d="M5 12h14"></path>
                                    <path d="m12 5 7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="overflow-hidden rounded-xl bg-white dark:bg-gray-950 border border-gray-200 dark:border-gray-800 shadow-md p-6" data-aos="fade-left" data-aos-delay="100">
                    <h2 class="text-xl font-bold mb-4">Kategori</h2>
                    <div class="space-y-2">
                        <a href="{{ route('news') }}?category=BRS" class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20 dark:bg-blue-900/20 dark:text-blue-400 dark:ring-blue-500/30 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors duration-200">
                            Berita Resmi Statistik
                        </a>
                        <a href="{{ route('news') }}?category=Event" class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-600/20 dark:bg-purple-900/20 dark:text-purple-400 dark:ring-purple-500/30 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors duration-200">
                            Event
                        </a>
                        <a href="{{ route('news') }}?category=Publikasi" class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-900/20 dark:text-green-400 dark:ring-green-500/30 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors duration-200">
                            Publikasi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Video Modal -->
    <div id="video-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="relative w-full max-w-4xl p-4">
            <div class="rounded-xl bg-white dark:bg-gray-950 overflow-hidden">
                <div class="aspect-video">
                    <iframe id="video-frame" class="w-full h-full" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                </div>
                <div class="p-4 flex justify-end">
                    <button id="close-modal" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-white transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-gray-200 bg-white hover:bg-gray-100 hover:text-gray-900 dark:border-gray-800 dark:bg-gray-950 dark:hover:bg-gray-800 dark:hover:text-gray-50 dark:focus-visible:ring-gray-300 h-9 px-4 py-2">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Video modal functionality
        const videoButtons = document.querySelectorAll('[data-video-url]');
        const videoModal = document.getElementById('video-modal');
        const videoFrame = document.getElementById('video-frame');
        const closeModal = document.getElementById('close-modal');
        
        if (videoModal && videoFrame && closeModal) {
            videoButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const videoUrl = this.getAttribute('data-video-url');
                    videoFrame.src = videoUrl.replace('watch?v=', 'embed/');
                    videoModal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                });
            });
            
            closeModal.addEventListener('click', function() {
                videoModal.classList.add('hidden');
                videoFrame.src = '';
                document.body.classList.remove('overflow-hidden');
            });
            
            // Close modal when clicking outside
            videoModal.addEventListener('click', function(e) {
                if (e.target === videoModal) {
                    videoModal.classList.add('hidden');
                    videoFrame.src = '';
                    document.body.classList.remove('overflow-hidden');
                }
            });
        }
    });
</script>
@endpush
