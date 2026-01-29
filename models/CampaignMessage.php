<?php
/**
 * Campaign Message Model
 * Manages individual messages in campaigns (day-by-day)
 */

class CampaignMessage {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Get all messages for a campaign
     */
    public function getByCampaign($campaignId) {
        $stmt = $this->db->prepare("
            SELECT * FROM campaign_messages
            WHERE campaign_id = ?
            ORDER BY day_number ASC
        ");
        
        $stmt->execute([$campaignId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get message by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM campaign_messages WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get message for specific day
     */
    public function getByDay($campaignId, $dayNumber) {
        $stmt = $this->db->prepare("
            SELECT * FROM campaign_messages
            WHERE campaign_id = ? AND day_number = ?
        ");
        
        $stmt->execute([$campaignId, $dayNumber]);
        return $stmt->fetch();
    }
    
    /**
     * Create campaign message
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO campaign_messages (campaign_id, day_number, message_type, message_content, 
                                           media_url, template_id, button_config, send_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $buttonConfig = isset($data['button_config']) ? json_encode($data['button_config']) : null;
        
        $result = $stmt->execute([
            $data['campaign_id'],
            $data['day_number'],
            $data['message_type'] ?? 'text',
            $data['message_content'],
            $data['media_url'] ?? null,
            $data['template_id'] ?? null,
            $buttonConfig,
            $data['send_time'] ?? '10:00:00'
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update campaign message
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE campaign_messages
            SET day_number = ?, message_type = ?, message_content = ?, media_url = ?, 
                template_id = ?, button_config = ?, send_time = ?
            WHERE id = ?
        ");
        
        $buttonConfig = isset($data['button_config']) ? json_encode($data['button_config']) : null;
        
        return $stmt->execute([
            $data['day_number'],
            $data['message_type'] ?? 'text',
            $data['message_content'],
            $data['media_url'] ?? null,
            $data['template_id'] ?? null,
            $buttonConfig,
            $data['send_time'] ?? '10:00:00',
            $id
        ]);
    }
    
    /**
     * Delete campaign message
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM campaign_messages WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Delete all messages for a campaign
     */
    public function deleteByCampaign($campaignId) {
        $stmt = $this->db->prepare("DELETE FROM campaign_messages WHERE campaign_id = ?");
        return $stmt->execute([$campaignId]);
    }
    
    /**
     * Bulk create messages
     */
    public function bulkCreate($campaignId, $messages) {
        $this->db->beginTransaction();
        
        try {
            // First, delete existing messages
            $this->deleteByCampaign($campaignId);
            
            // Insert new messages
            foreach ($messages as $message) {
                $message['campaign_id'] = $campaignId;
                $this->create($message);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Bulk create messages error: " . $e->getMessage());
            return false;
        }
    }
}
