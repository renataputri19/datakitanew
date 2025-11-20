@extends('layouts.monalisa-dashboard')

@section('title', 'Notifikasi - MONALISA')

@section('monalisa-content')
    <!-- Mobile/Tablet Menu Button -->
    <div class="lg:hidden mb-4">
        <button class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200" type="button" data-open-sidebar aria-controls="monalisa-sidebar" aria-expanded="false">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
            Menu
        </button>
    </div>

<div class="max-w-4xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Notifikasi</h1>
        <p class="text-gray-600 dark:text-gray-400">Pantau semua pembaruan dan aktivitas assessment Anda</p>
    </div>

    <!-- Actions Bar -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 md:gap-0 mb-6">
        <div class="text-sm text-gray-600 dark:text-gray-400">
            <span id="notification-count">{{ $notifications->count() }}</span> notifikasi
        </div>
        @if($notifications->where('is_read', false)->count() > 0)
        <button type="button"
                onclick="markAllAsRead()"
                class="monalisa-btn monalisa-btn-secondary text-sm w-full md:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Tandai Semua Dibaca
        </button>
        @endif
    </div>

    <!-- Notifications List -->
    <div class="space-y-3" id="notifications-list">
        @forelse($notifications as $notification)
        <div class="ud-card notification-item {{ $notification->is_read ? 'opacity-75' : '' }}"
             data-notification-id="{{ $notification->id }}"
             data-is-read="{{ $notification->is_read ? 'true' : 'false' }}">
            <div class="flex flex-col md:flex-row gap-3 md:gap-4">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    @if($notification->type === 'assessment_submitted')
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    @elseif($notification->type === 'assessment_updated')
                    <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                    @elseif($notification->type === 'assessment_verified')
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    @elseif($notification->type === 'bps_score_updated')
                    <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    @else
                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                    </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 md:gap-4">
                        <div class="flex-1">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                                {{ $notification->title }}
                                @if(!$notification->is_read)
                                <span class="inline-block w-2 h-2 bg-blue-600 rounded-full ml-2"></span>
                                @endif
                            </h3>
                            <p class="notification-message text-sm text-gray-600 dark:text-gray-400 mb-2 break-words whitespace-normal">
                                {{ $notification->message }}
                            </p>
                            <div class="flex flex-col md:flex-row md:flex-wrap gap-1 md:gap-4 text-xs text-gray-500 dark:text-gray-500">
                                <span>{{ $notification->created_at->diffForHumans() }}</span>
                                @if($notification->triggeredBy)
                                <span>oleh {{ $notification->triggeredBy->name }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-row flex-wrap items-center gap-2 mt-3 md:mt-0">
                            @if($notification->assessment_id)
                            <a href="{{ auth()->user()->is_bps ? route('monalisa.bps.assessment.show', $notification->assessment_id) : route('monalisa.kominfo.assessment.show', $notification->assessment->indikator_id) }}"
                               class="p-2 md:p-2 text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                               title="Lihat Assessment">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            @endif
                            @if(!$notification->is_read)
                            <button type="button"
                                    onclick="markAsRead('{{ $notification->id }}')"
                                    class="p-2 md:p-2 text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                                    title="Tandai Dibaca">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            @endif
                            <button type="button"
                                    onclick="deleteNotification('{{ $notification->id }}')"
                                    class="p-2 md:p-2 text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                                    title="Hapus">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="ud-card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Tidak Ada Notifikasi</h3>
            <p class="text-gray-600 dark:text-gray-400">Anda belum memiliki notifikasi</p>
        </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    const userType = '{{ auth()->user()->is_kominfo_user ? "kominfo" : "bps" }}';

    function markAsRead(notificationId) {
        fetch(`/monalisa/${userType}/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (item) {
                    item.classList.add('opacity-75');
                    item.setAttribute('data-is-read', 'true');
                    const badge = item.querySelector('.inline-block.w-2.h-2');
                    if (badge) badge.remove();
                    const button = item.querySelector('button[onclick*="markAsRead"]');
                    if (button) button.remove();
                }
                updateNotificationCount();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function markAllAsRead() {
        fetch(`/monalisa/${userType}/notifications/read-all`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function deleteNotification(notificationId) {
        if (!confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')) {
            return;
        }

        fetch(`/monalisa/${userType}/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const item = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (item) {
                    item.remove();
                    updateNotificationCount();
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function updateNotificationCount() {
        fetch(`/monalisa/${userType}/notifications/unread-count`)
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('sidebar-notification-badge');
                if (data.count > 0) {
                    if (badge) {
                        badge.textContent = data.count;
                    }
                } else {
                    if (badge) {
                        badge.remove();
                    }
                }
                
                // Update count in page
                const countElement = document.getElementById('notification-count');
                if (countElement) {
                    const totalCount = document.querySelectorAll('.notification-item').length;
                    countElement.textContent = totalCount;
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // Poll for new notifications every 60 seconds
    setInterval(updateNotificationCount, 60000);
</script>
@endpush
@endsection

