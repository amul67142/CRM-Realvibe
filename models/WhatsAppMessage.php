<?php
/**
 * WhatsApp Message Model
 * Tracks all outbound WhatsApp messages and their delivery status
 */

class WhatsAppMessage {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Log WhatsApp message
     */
    public function log($data) {
        $stmt = $this->db->prepare("
            INSERT INTO whatsapp_messages (lead_id, campaign_id, message_id, message_type, 
                                           message_content, media_url, status, direction, sent_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $result = $stmt->execute([
            $data['lead_id'],
            $data['campaign_id'] ?? null,
            $data['message_id'] ?? null,
            $data['message_type'] ?? 'text',
            $data['message_content'],
            $data['media_url'] ?? null,
            $data['status'] ?? 'queued',
            $data['direction'] ?? 'outbound'
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update message status
     */
    public function updateStatus($messageId, $status, $errorMessage = null, $errorCode = null) {
        $fields = ['status = ?'];
        $params = [$status];
        
        // Set timestamp based on status
        switch ($status) {
            case 'delivered':
                $fields[] = 'delivered_at = NOW()';
                break;
            case 'read':
                $fields[] = 'read_at = NOW()';
                $fields[] = 'delivered_at = IFNULL(delivered_at, NOW())';
                break;
            case 'failed':
                $fields[] = 'failed_at = NOW()';
                if ($errorMessage) {
                    $fields[] = 'error_message = ?';
                    $params[] = $errorMessage;
                }
                if ($errorCode) {
                    $fields[] = 'error_code = ?';
                    $params[] = $errorCode;
                }
                break;
        }
        
        $params[] = $messageId;
        
        $sql = "UPDATE whatsapp_messages SET " . implode(', ', $fields) . " WHERE message_id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    /**
     * Get messages by lead
     */
    public function getByLead($leadId) {
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_messages
            WHERE lead_id = ?
            ORDER BY sent_at DESC
        ");
        
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get conversation for lead (sent + received)
     */
    public function getConversation($leadId) {
        // Get sent messages
        $stmt = $this->db->prepare("
            SELECT 'sent' as type, message_content as content, media_url, status, sent_at as timestamp
            FROM whatsapp_messages
            WHERE lead_id = ? AND direction = 'outbound'
            
            UNION ALL
            
            SELECT 'received' as type, reply_content as content, media_url, NULL as status, received_at as timestamp
            FROM lead_replies
            WHERE lead_id = ?
            
            ORDER BY timestamp ASC
        ");
        
        $stmt->execute([$leadId, $leadId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get pending messages (for status checking)
     */
    public function getPending($limit = 100) {
        $stmt = $this->db->prepare("
            SELECT * FROM whatsapp_messages
            WHERE status IN ('queued', 'sent')
              AND message_id IS NOT NULL
              AND sent_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY sent_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get failed messages for retry
     */
    public function getFailedForRetry($maxRetries = 3) {
        $stmt = $this->db->prepare("
            SELECT wm.*, COUNT(wm.id) as retry_count
            FROM whatsapp_messages wm
            WHERE wm.status = 'failed'
              AND wm.failed_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            GROUP BY wm.lead_id, wm.message_content
            HAVING retry_count < ?
            LIMIT 50
        ");
        
        $stmt->execute([$maxRetries]);
        return $stmt->fetchAll();
    }
    
    /**
     * Count messages sent today
     */
    public function countToday() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as count FROM whatsapp_messages
            WHERE DATE(sent_at) = CURDATE()
        ");
        
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Get message statistics
     */
    public function getStats($filters = []) {
        $sql = "SELECT status, COUNT(*) as count FROM whatsapp_messages WHERE 1=1";
        $params = [];
        
        if (isset($filters['campaign_id'])) {
            $sql .= " AND campaign_id = ?";
            $params[] = $filters['campaign_id'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND sent_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        $sql .= " GROUP BY status";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
