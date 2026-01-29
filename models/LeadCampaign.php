<?php
/**
 * Lead Campaign Model
 * Manages lead enrollment in campaigns and progress tracking
 */

class LeadCampaign {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Enroll lead in campaign
     */
    public function enroll($leadId, $campaignId) {
        // Check if already enrolled
        if ($this->isEnrolled($leadId, $campaignId)) {
            return ['success' => false, 'error' => 'already_enrolled', 'message' => 'Lead already enrolled in this campaign'];
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO lead_campaigns (lead_id, campaign_id, current_day, status, started_at)
            VALUES (?, ?, 0, 'active', NOW())
        ");
        
        if ($stmt->execute([$leadId, $campaignId])) {
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'error' => 'database', 'message' => 'Failed to enroll lead'];
    }
    
    /**
     * Check if lead is enrolled in campaign
     */
    public function isEnrolled($leadId, $campaignId) {
        $stmt = $this->db->prepare("
            SELECT id FROM lead_campaigns WHERE lead_id = ? AND campaign_id = ?
        ");
        
        $stmt->execute([$leadId, $campaignId]);
        return $stmt->fetch() !== false;
    }
    
    /**
     * Get enrollment for lead and campaign
     */
    public function getEnrollment($leadId, $campaignId) {
        $stmt = $this->db->prepare("
            SELECT * FROM lead_campaigns WHERE lead_id = ? AND campaign_id = ?
        ");
        
        $stmt->execute([$leadId, $campaignId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all enrollments for a lead
     */
    public function getByLead($leadId) {
        $stmt = $this->db->prepare("
            SELECT lc.*, c.campaign_name, c.duration_days
            FROM lead_campaigns lc
            LEFT JOIN campaigns c ON lc.campaign_id = c.id
            WHERE lc.lead_id = ?
            ORDER BY lc.started_at DESC
        ");
        
        $stmt->execute([$leadId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get all enrollments for a campaign
     */
    public function getByCampaign($campaignId) {
        $stmt = $this->db->prepare("
            SELECT lc.*, l.name, l.phone, l.email
            FROM lead_campaigns lc
            LEFT JOIN leads l ON lc.lead_id = l.id
            WHERE lc.campaign_id = ?
            ORDER BY lc.started_at DESC
        ");
        
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update campaign progress
     */
    public function updateProgress($leadId, $campaignId, $currentDay) {
        $stmt = $this->db->prepare("
            UPDATE lead_campaigns
            SET current_day = ?, last_message_sent_at = NOW()
            WHERE lead_id = ? AND campaign_id = ?
        ");
        
        return $stmt->execute([$currentDay, $leadId, $campaignId]);
    }
    
    /**
     * Mark campaign as completed
     */
    public function markCompleted($leadId, $campaignId) {
        $stmt = $this->db->prepare("
            UPDATE lead_campaigns
            SET status = 'completed', completed_at = NOW()
            WHERE lead_id = ? AND campaign_id = ?
        ");
        
        return $stmt->execute([$leadId, $campaignId]);
    }
    
    /**
     * Mark campaign as unsubscribed
     */
    public function markUnsubscribed($leadId, $campaignId) {
        $stmt = $this->db->prepare("
            UPDATE lead_campaigns
            SET status = 'unsubscribed', unsubscribed_at = NOW()
            WHERE lead_id = ? AND campaign_id = ?
        ");
        
        return $stmt->execute([$leadId, $campaignId]);
    }
    
    /**
     * Unsubscribe lead from all campaigns
     */
    public function unsubscribeAll($leadId) {
        $stmt = $this->db->prepare("
            UPDATE lead_campaigns
            SET status = 'unsubscribed', unsubscribed_at = NOW()
            WHERE lead_id = ? AND status = 'active'
        ");
        
        return $stmt->execute([$leadId]);
    }
    
    /**
     * Get due messages (for cron job)
     * Returns leads that need to receive their next campaign message
     */
    public function getDueMessages() {
        $stmt = $this->db->query("
            SELECT lc.*, l.name, l.phone, l.email, l.project_id, l.is_subscribed,
                   c.campaign_name, c.duration_days,
                   cm.id as message_id, cm.message_type, cm.message_content, cm.media_url, 
                   cm.template_id, cm.button_config, cm.send_time,
                   p.project_name, p.location as project_location, p.project_type, p.price_range, p.brochure_url,
                   cl.name as client_name
            FROM lead_campaigns lc
            INNER JOIN leads l ON lc.lead_id = l.id
            INNER JOIN campaigns c ON lc.campaign_id = c.id
            INNER JOIN campaign_messages cm ON c.id = cm.campaign_id AND cm.day_number = lc.current_day + 1
            INNER JOIN projects p ON l.project_id = p.id
            INNER JOIN clients cl ON p.client_id = cl.id
            WHERE lc.status = 'active'
              AND l.is_subscribed = 1
              AND c.is_active = 1
              AND lc.current_day < c.duration_days
              AND (
                  lc.last_message_sent_at IS NULL 
                  OR DATE(lc.last_message_sent_at) < CURDATE()
              )
              AND CURTIME() >= cm.send_time
              AND NOT EXISTS (
                  SELECT 1 FROM whatsapp_messages wm
                  WHERE wm.lead_id = lc.lead_id 
                    AND wm.campaign_id = lc.campaign_id
                    AND DATE(wm.sent_at) = CURDATE()
              )
            ORDER BY cm.send_time ASC
        ");
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get active enrollment count
     */
    public function countActive() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM lead_campaigns WHERE status = 'active'");
        $result = $stmt->fetch();
        return $result['count'];
    }
}
