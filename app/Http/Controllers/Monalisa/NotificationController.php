<?php

namespace App\Http\Controllers\Monalisa;

use App\Http\Controllers\Controller;
use App\Models\MonalisaNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user.
     */
    public function index()
    {
        $notifications = MonalisaNotification::getAllForUser(auth()->id());
        
        return view('monalisa.notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount()
    {
        $count = MonalisaNotification::getUnreadCountForUser(auth()->id());
        
        return response()->json([
            'count' => $count,
        ]);
    }

    /**
     * Get recent notifications (for dropdown/sidebar).
     */
    public function getRecent()
    {
        $notifications = MonalisaNotification::getAllForUser(auth()->id(), 10);
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => MonalisaNotification::getUnreadCountForUser(auth()->id()),
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead($notificationId)
    {
        $notification = MonalisaNotification::where('user_id', auth()->id())
            ->findOrFail($notificationId);
        
        $notification->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        MonalisaNotification::markAllAsReadForUser(auth()->id());
        
        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy($notificationId)
    {
        $notification = MonalisaNotification::where('user_id', auth()->id())
            ->findOrFail($notificationId);
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }
}

