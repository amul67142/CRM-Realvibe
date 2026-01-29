<?php
/**
 * Notification Model
 * Handles in-app notifications for the CRM
 */

class Notification {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Create a new notification
     * 
     * @param array $data Notification data
     * @return int Notification ID
     */
    public function create($data) {
        $sql = "INSERT INTO notifications (user_id, type, title, message, link, icon) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['user_id'] ?? null,
            $data['type'],
            $data['title'],
            $data['message'],
            $data['link'] ?? null,
            $data['icon'] ?? 'bell'
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get unread notification count for a user
     * 
     * @param int|null $userId User ID (null = count global notifications)
     * @return int Unread count
     */
    public function getUnreadCount($userId = null) {
        if ($userId) {
            $sql = "SELECT COUNT(*) FROM notifications 
                    WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$userId]);
        } else {
            $sql = "SELECT COUNT(*) FROM notifications WHERE user_id IS NULL AND is_read = 0";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
        }
        
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get recent notifications for a user
     * 
     * @param int|null $userId User ID
     * @param int $limit Number of notifications to fetch
     * @param bool $unreadOnly Fetch only unread notifications
     * @return array Notifications
     */
    public function getRecent($userId = null, $limit = 10, $unreadOnly = false) {
        $conditions = [];
        $params = [];
        
        if ($userId) {
            $conditions[] = "(user_id = ? OR user_id IS NULL)";
            $params[] = $userId;
        } else {
            $conditions[] = "user_id IS NULL";
        }
        
        if ($unreadOnly) {
            $conditions[] = "is_read = 0";
        }
        
        $where = implode(' AND ', $conditions);
        
        $sql = "SELECT * FROM notifications 
                WHERE $where 
                ORDER BY created_at DESC 
                LIMIT ?";
        
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Mark a notification as read
     * 
     * @param int $id Notification ID
     * @return bool Success
     */
    public function markAsRead($id) {
        $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Mark all notifications as read for a user
     * 
     * @param int|null $userId User ID
     * @return bool Success
     */
    public function markAllAsRead($userId = null) {
        if ($userId) {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                    WHERE (user_id = ? OR user_id IS NULL) AND is_read = 0";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$userId]);
        } else {
            $sql = "UPDATE notifications SET is_read = 1, read_at = NOW() 
                    WHERE user_id IS NULL AND is_read = 0";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute();
        }
    }
    
    /**
     * Delete a notification
     * 
     * @param int $id Notification ID
     * @return bool Success
     */
    public function delete($id) {
        $sql = "DELETE FROM notifications WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    /**
     * Delete old read notifications (cleanup)
     * 
     * @param int $daysOld Delete notifications older than X days
     * @return int Number of deleted notifications
     */
    public function deleteOld($daysOld = 30) {
        $sql = "DELETE FROM notifications 
                WHERE is_read = 1 
                AND read_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$daysOld]);
        
        return $stmt->rowCount();
    }
}
