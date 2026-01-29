<?php
/**
 * Lead Reply Model
 * Manages incoming messages from leads
 */

class LeadReply {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Store incoming reply
     */
    public function store($data) {
        $stmt = $this->db->prepare("
            INSERT INTO lead_replies (lead_id, message_id, reply_content, media_url, is_read, received_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $data['lead_id'],
            $data['message_id'] ?? null,
            $data['reply_content'],
            $data['media_url'] ?? null,
            $data['is_read'] ?? 0
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Get replies by lead
     */
    public function getByLead($leadId) {
        $stmt = $this->db->prepare("
            SELECT * FROM lead_replies
            WHERE lead_id = ?
            ORDER BY received_at DESC
        ");
        
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Mark reply as read
     */
    public function markAsRead($id) {
        $stmt = $this->db->prepare("UPDATE lead_replies SET is_read = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Mark all replies for a lead as read
     */
    public function markAllAsRead($leadId) {
        $stmt = $this->db->prepare("UPDATE lead_replies SET is_read = 1 WHERE lead_id = ? AND is_read = 0");
        return $stmt->execute([$leadId]);
    }
    
    /**
     * Count unread replies
     */
    public function countUnread($leadId = null) {
        if ($leadId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM lead_replies WHERE lead_id = ? AND is_read = 0");
            $stmt->execute([$leadId]);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) as count FROM lead_replies WHERE is_read = 0");
        }
        
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Get recent unread replies
     */
    public function getRecentUnread($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT lr.*, l.name, l.phone, p.project_name
            FROM lead_replies lr
            LEFT JOIN leads l ON lr.lead_id = l.id
            LEFT JOIN projects p ON l.project_id = p.id
            WHERE lr.is_read = 0
            ORDER BY lr.received_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if reply contains unsubscribe keywords
     */
    public function isUnsubscribeRequest($content) {
        $unsubscribeKeywords = ['stop', 'unsubscribe', 'opt out', 'optout', 'cancel', 'remove'];
        $content = strtolower(trim($content));
        
        foreach ($unsubscribeKeywords as $keyword) {
            if ($content === $keyword || strpos($content, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if reply contains subscribe keywords
     */
    public function isSubscribeRequest($content) {
        $subscribeKeywords = ['start', 'subscribe', 'opt in', 'optin', 'yes'];
        $content = strtolower(trim($content));
        
        foreach ($subscribeKeywords as $keyword) {
            if ($content === $keyword) {
                return true;
            }
        }
        
        return false;
    }
}
