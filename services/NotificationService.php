<?php
/**
 * Notification Service
 * Handles internal notifications for agents
 */

class NotificationService {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Create notification for user
     */
    public function create($userId, $title, $message, $type = 'info', $link = null) {
        // This is a placeholder implementation
        // In a full system, you'd have a notifications table
        
        // For now, we'll just log the notification
        logActivity('notification_created', 'user', $userId, "$title: $message");
        
        return true;
    }
    
    /**
     * Notify agents about new lead reply
     */
    public function notifyNewReply($leadId, $replyContent) {
        // Get lead details
        $stmt = $this->db->prepare("
            SELECT l.*, u.id as agent_id, u.email as agent_email
            FROM leads l
            LEFT JOIN users u ON l.assigned_to = u.id
            WHERE l.id = ?
        ");
        
        $stmt->execute([$leadId]);
        $lead = $stmt->fetch();
        
        if ($lead && $lead['agent_id']) {
            $this->create(
                $lead['agent_id'],
                'New Lead Reply',
                "Lead {$lead['name']} has replied: " . truncate($replyContent, 50),
                'info',
                url("leads/{$leadId}/conversation")
            );
        }
        
        return true;
    }
    
    /**
     * Notify admins about system errors
     */
    public function notifyError($errorTitle, $errorMessage) {
        // Get all admins
        $stmt = $this->db->query("SELECT id, email FROM users WHERE role = 'admin' AND is_active = 1");
        $admins = $stmt->fetchAll();
        
        foreach ($admins as $admin) {
            $this->create(
                $admin['id'],
                $errorTitle,
                $errorMessage,
                'error'
            );
            
            // Optionally send email (if email system is implemented)
            // $this->sendEmail($admin['email'], $errorTitle, $errorMessage);
        }
        
        return true;
    }
}
