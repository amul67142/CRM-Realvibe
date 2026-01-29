<?php
/**
 * Notification Controller
 * Handles AJAX requests for in-app notifications
 */

require_once __DIR__ . '/../models/Notification.php';

class NotificationController {
    private $notificationModel;
    
    public function __construct() {
        $this->notificationModel = new Notification();
    }
    
    /**
     * Get unread notifications (AJAX)
     */
    public function getUnread() {
        requireLogin();
        
        $userId = $_SESSION['user_id'];
        
        // Get unread count
        $count = $this->notificationModel->getUnreadCount($userId);
        
        // Get recent notifications
        $notifications = $this->notificationModel->getRecent($userId, 10);
        
        // Format timestamps
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
        }
        
        json([
            'success' => true,
            'count' => $count,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Mark notification as read (AJAX)
     */
    public function markAsRead() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json(['success' => false, 'error' => 'Invalid request'], 405);
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            json(['success' => false, 'error' => 'Missing notification ID'], 400);
        }
        
        if ($this->notificationModel->markAsRead($id)) {
            json(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            json(['success' => false, 'error' => 'Failed to mark notification as read'], 500);
        }
    }
    
    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllAsRead() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json(['success' => false, 'error' => 'Invalid request'], 405);
        }
        
        $userId = $_SESSION['user_id'];
        
        if ($this->notificationModel->markAllAsRead($userId)) {
            json(['success' => true, 'message' => 'All notifications marked as read']);
        } else {
            json(['success' => false, 'error' => 'Failed to mark notifications as read'], 500);
        }
    }
    
    /**
     * Delete notification (AJAX)
     */
    public function delete() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json(['success' => false, 'error' => 'Invalid request'], 405);
        }
        
        $id = $_POST['id'] ?? null;
        
        if (!$id) {
            json(['success' => false, 'error' => 'Missing notification ID'], 400);
        }
        
        if ($this->notificationModel->delete($id)) {
            json(['success' => true, 'message' => 'Notification deleted']);
        } else {
            json(['success' => false, 'error' => 'Failed to delete notification'], 500);
        }
    }
    
    /**
     * Convert timestamp to "time ago" format
     * 
     * @param string $timestamp
     * @return string
     */
    private function timeAgo($timestamp) {
        $time = strtotime($timestamp);
        $diff = time() - $time;
        
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
        } else {
            return date('M j, Y', $time);
        }
    }
}
