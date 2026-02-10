<?php
/**
 * Campaign Model
 * Manages nurturing campaigns
 */

class Campaign {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Get all campaigns
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT c.*, p.project_name, cl.name as client_name, u.full_name as created_by_name,
                   (SELECT COUNT(*) FROM campaign_messages WHERE campaign_id = c.id) as messages_count,
                   (SELECT COUNT(*) FROM campaign_leads WHERE campaign_id = c.id) as enrolled_count,
                   (SELECT COUNT(*) FROM campaign_leads WHERE campaign_id = c.id AND status = 'active') as active_count
            FROM campaigns c
            LEFT JOIN projects p ON c.project_id = p.id
            LEFT JOIN clients cl ON p.client_id = cl.id
            LEFT JOIN users u ON c.created_by = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (isset($filters['project_id'])) {
            $sql .= " AND c.project_id = ?";
            $params[] = $filters['project_id'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND c.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get campaign by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, p.project_name, cl.name as client_name, u.full_name as created_by_name
            FROM campaigns c
            LEFT JOIN projects p ON c.project_id = p.id
            LEFT JOIN clients cl ON p.client_id = cl.id
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create new campaign
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO campaigns (project_id, campaign_name, description, duration_days, is_active, auto_enroll, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['project_id'],
            $data['campaign_name'],
            $data['description'] ?? null,
            $data['duration_days'] ?? 5,
            $data['is_active'] ?? 0,
            $data['auto_enroll'] ?? 0,
            $_SESSION['user_id']
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update campaign
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE campaigns
            SET campaign_name = ?, description = ?, duration_days = ?, is_active = ?, auto_enroll = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['campaign_name'],
            $data['description'] ?? null,
            $data['duration_days'] ?? 5,
            $data['is_active'] ?? 0,
            $data['auto_enroll'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Toggle campaign active status
     */
    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE campaigns SET is_active = NOT is_active WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Delete campaign
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM campaigns WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get active campaigns for a project
     */
    public function getActiveByProject($projectId) {
        $stmt = $this->db->prepare("
            SELECT * FROM campaigns 
            WHERE project_id = ? AND is_active = 1 AND auto_enroll = 1
        ");
        
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get campaign analytics
     */
    public function getAnalytics($id) {
        // Total enrolled
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total_enrolled FROM lead_campaigns WHERE campaign_id = ?
        ");
        $stmt->execute([$id]);
        $totalEnrolled = $stmt->fetch()['total_enrolled'];
        
        // Active
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as active FROM lead_campaigns WHERE campaign_id = ? AND status = 'active'
        ");
        $stmt->execute([$id]);
        $active = $stmt->fetch()['active'];
        
        // Completed
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as completed FROM lead_campaigns WHERE campaign_id = ? AND status = 'completed'
        ");
        $stmt->execute([$id]);
        $completed = $stmt->fetch()['completed'];
        
        // Unsubscribed
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as unsubscribed FROM lead_campaigns WHERE campaign_id = ? AND status = 'unsubscribed'
        ");
        $stmt->execute([$id]);
        $unsubscribed = $stmt->fetch()['unsubscribed'];
        
        // Messages sent
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as messages_sent FROM whatsapp_messages WHERE campaign_id = ?
        ");
        $stmt->execute([$id]);
        $messagesSent = $stmt->fetch()['messages_sent'];
        
        // Messages delivered
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as messages_delivered FROM whatsapp_messages 
            WHERE campaign_id = ? AND status IN ('delivered', 'read')
        ");
        $stmt->execute([$id]);
        $messagesDelivered = $stmt->fetch()['messages_delivered'];
        
        // Messages read
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as messages_read FROM whatsapp_messages WHERE campaign_id = ? AND status = 'read'
        ");
        $stmt->execute([$id]);
        $messagesRead = $stmt->fetch()['messages_read'];
        
        return [
            'total_enrolled' => $totalEnrolled,
            'active' => $active,
            'completed' => $completed,
            'unsubscribed' => $unsubscribed,
            'messages_sent' => $messagesSent,
            'messages_delivered' => $messagesDelivered,
            'messages_read' => $messagesRead,
            'delivery_rate' => $messagesSent > 0 ? round(($messagesDelivered / $messagesSent) * 100, 2) : 0,
            'read_rate' => $messagesSent > 0 ? round(($messagesRead / $messagesSent) * 100, 2) : 0,
            'unsubscribe_rate' => $totalEnrolled > 0 ? round(($unsubscribed / $totalEnrolled) * 100, 2) : 0
        ];
    }
    
    /**
     * Count active campaigns
     */
    public function countActive() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM campaigns WHERE is_active = 1");
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Update campaign status
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE campaigns SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * Get campaign leads
     */
    public function getCampaignLeads($campaignId, $status = null) {
        $sql = "
            SELECT cl.*, cl.id AS campaign_lead_id, l.name, l.phone, l.email, l.project_id
            FROM campaign_leads cl
            JOIN leads l ON cl.lead_id = l.id
            WHERE cl.campaign_id = ?
        ";
        
        $params = [$campaignId];
        
        if ($status) {
            $sql .= " AND cl.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY cl.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Add lead to campaign
     */
    public function addLeadToCampaign($campaignId, $leadId) {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO campaign_leads (campaign_id, lead_id, status)
            VALUES (?, ?, 'pending')
        ");
        return $stmt->execute([$campaignId, $leadId]);
    }
    
    /**
     * Remove lead from campaign
     */
    public function removeLeadFromCampaign($campaignId, $leadId) {
        $stmt = $this->db->prepare("
            DELETE FROM campaign_leads 
            WHERE campaign_id = ? AND lead_id = ?
        ");
        return $stmt->execute([$campaignId, $leadId]);
    }
    
   /**
     * Pause lead nurturing
     */
    public function pauseLeadNurturing($campaignId, $leadId) {
        $stmt = $this->db->prepare("
            UPDATE campaign_leads 
            SET status = 'paused', updated_at = NOW()
            WHERE campaign_id = ? AND lead_id = ?
        ");
        return $stmt->execute([$campaignId, $leadId]);
    }
    
    /**
     * Resume lead nurturing
     */
    public function resumeLeadNurturing($campaignId, $leadId) {
        $stmt = $this->db->prepare("
            UPDATE campaign_leads 
            SET status = 'active', updated_at = NOW()
            WHERE campaign_id = ? AND lead_id = ?
        ");
        return $stmt->execute([$campaignId, $leadId]);
    }
    
    /**
     * Get active leads for nurturing
     */
    public function getActiveLeadsForNurturing($campaignId) {
        $stmt = $this->db->prepare("
            SELECT cl.*, l.name, l.phone, l.email
            FROM campaign_leads cl
            JOIN leads l ON cl.lead_id = l.id
            WHERE cl.campaign_id = ?
                AND cl.status = 'active'
            ORDER BY cl.last_message_sent_at ASC
        ");
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }
}
