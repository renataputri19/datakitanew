/**
 * MONALISA Notification System
 * Handles real-time notification polling and updates
 */

(function() {
    'use strict';

    // Configuration
    const POLL_INTERVAL = 60000; // 60 seconds
    const NOTIFICATION_DURATION = 5000; // 5 seconds for toast notifications
    
    let lastNotificationCount = 0;
    let pollTimer = null;

    /**
     * Get user type from the page
     */
    function getUserType() {
        // Try to get from meta tag or data attribute
        const metaUserType = document.querySelector('meta[name="monalisa-user-type"]');
        if (metaUserType) {
            return metaUserType.content;
        }
        
        // Fallback: check URL
        if (window.location.pathname.includes('/monalisa/kominfo')) {
            return 'kominfo';
        } else if (window.location.pathname.includes('/monalisa/bps')) {
            return 'bps';
        }
        
        return null;
    }

    /**
     * Update notification badge in sidebar
     */
    function updateNotificationBadge(count) {
        const badge = document.getElementById('sidebar-notification-badge');
        
        if (count > 0) {
            if (badge) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                // Create badge if it doesn't exist
                const notificationLink = document.querySelector('a[href*="/notifications"]');
                if (notificationLink) {
                    const newBadge = document.createElement('span');
                    newBadge.id = 'sidebar-notification-badge';
                    newBadge.className = 'text-xs bg-red-500 text-white px-2 py-0.5 rounded-full font-semibold';
                    newBadge.textContent = count;
                    notificationLink.appendChild(newBadge);
                }
            }
        } else {
            if (badge) {
                badge.remove();
            }
        }
    }

    /**
     * Show toast notification
     */
    function showToast(title, message, type = 'info') {
        // Check if toast container exists, create if not
        let toastContainer = document.getElementById('monalisa-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'monalisa-toast-container';
            toastContainer.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `ud-alert ud-alert-${type} max-w-sm shadow-lg animate-slide-in-right`;
        toast.innerHTML = `
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <div class="flex-1">
                <strong>${title}</strong>
                <p class="text-sm mt-1">${message}</p>
            </div>
            <button type="button" class="ml-auto" onclick="this.parentElement.remove()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        toastContainer.appendChild(toast);

        // Auto-remove after duration
        setTimeout(() => {
            toast.classList.add('animate-slide-out-right');
            setTimeout(() => toast.remove(), 300);
        }, NOTIFICATION_DURATION);
    }

    /**
     * Fetch unread notification count
     */
    function fetchNotificationCount() {
        const userType = getUserType();
        if (!userType) return;

        fetch(`/monalisa/${userType}/notifications/unread-count`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            const currentCount = data.count || 0;
            
            // Update badge
            updateNotificationBadge(currentCount);
            
            // Show toast if count increased
            if (currentCount > lastNotificationCount && lastNotificationCount > 0) {
                const newNotifications = currentCount - lastNotificationCount;
                showToast(
                    'Notifikasi Baru',
                    `Anda memiliki ${newNotifications} notifikasi baru`,
                    'info'
                );
            }
            
            lastNotificationCount = currentCount;
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
        });
    }

    /**
     * Start polling for notifications
     */
    function startPolling() {
        // Initial fetch
        fetchNotificationCount();
        
        // Set up polling interval
        if (pollTimer) {
            clearInterval(pollTimer);
        }
        pollTimer = setInterval(fetchNotificationCount, POLL_INTERVAL);
    }

    /**
     * Stop polling
     */
    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    /**
     * Initialize notification system
     */
    function init() {
        // Only initialize if we're on a MONALISA page
        const userType = getUserType();
        if (!userType) return;

        // Start polling
        startPolling();

        // Stop polling when page is hidden (tab switched, etc.)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopPolling();
            } else {
                startPolling();
            }
        });

        // Clean up on page unload
        window.addEventListener('beforeunload', stopPolling);
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose functions globally for manual control if needed
    window.MonalisaNotifications = {
        start: startPolling,
        stop: stopPolling,
        refresh: fetchNotificationCount,
        showToast: showToast
    };
})();

