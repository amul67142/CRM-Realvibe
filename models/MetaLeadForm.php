<?php
/**
 * MetaLeadForm Model
 * Manages Meta Lead Form to Project mappings
 */

class MetaLeadForm {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Create a new form-to-project mapping
     */
    public function create($projectId, $formId, $formName = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO meta_lead_forms (project_id, form_id, form_name, is_active)
                VALUES (?, ?, ?, 1)
            ");
            
            return $stmt->execute([$projectId, $formId, $formName]);
        } catch (PDOException $e) {
            error_log("Error creating Meta Lead Form mapping: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find project by Form ID
     */
    public function findProjectByFormId($formId) {
        $stmt = $this->db->prepare("
            SELECT mlf.*, p.project_name, p.whatsapp_provider, p.aisensy_campaign_name, p.welcome_message
            FROM meta_lead_forms mlf
            JOIN projects p ON mlf.project_id = p.id
            WHERE mlf.form_id = ? AND mlf.is_active = 1 AND p.is_active = 1
        ");
        
        $stmt->execute([$formId]);
        return $stmt->fetch();
    }
    
    /**
     * Get all forms for a specific project
     */
    public function getByProject($projectId) {
        $stmt = $this->db->prepare("
            SELECT * FROM meta_lead_forms
            WHERE project_id = ?
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get form by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM meta_lead_forms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Update form mapping
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE meta_lead_forms
            SET form_name = ?, is_active = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['form_name'] ?? null,
            $data['is_active'] ?? 1,
            $id
        ]);
    }
    
    /**
     * Delete form mapping
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM meta_lead_forms WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Toggle form active status
     */
    public function toggleActive($id, $status) {
        $stmt = $this->db->prepare("
            UPDATE meta_lead_forms
            SET is_active = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * Update lead capture statistics
     */
    public function incrementLeadCount($formId) {
        $stmt = $this->db->prepare("
            UPDATE meta_lead_forms
            SET leads_captured = leads_captured + 1,
                last_lead_at = NOW()
            WHERE form_id = ?
        ");
        
        return $stmt->execute([$formId]);
    }
    
    /**
     * Get all active form mappings
     */
    public function getAllActive() {
        $stmt = $this->db->query("
            SELECT mlf.*, p.project_name
            FROM meta_lead_forms mlf
            JOIN projects p ON mlf.project_id = p.id
            WHERE mlf.is_active = 1 AND p.is_active = 1
            ORDER BY mlf.created_at DESC
        ");
        
        return $stmt->fetchAll();
    }
    
    /**
     * Check if form ID already exists
     */
    public function formIdExists($formId, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM meta_lead_forms
                WHERE form_id = ? AND id != ?
            ");
            $stmt->execute([$formId, $excludeId]);
        } else {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count FROM meta_lead_forms
                WHERE form_id = ?
            ");
            $stmt->execute([$formId]);
        }
        
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
    
    /**
     * Get statistics for a form
     */
    public function getFormStats($formId) {
        $stmt = $this->db->prepare("
            SELECT 
                mlf.*,
                COUNT(l.id) as total_leads,
                COUNT(CASE WHEN l.status = 'new' THEN 1 END) as new_leads,
                COUNT(CASE WHEN l.status = 'contacted' THEN 1 END) as contacted_leads,
                COUNT(CASE WHEN l.status = 'qualified' THEN 1 END) as qualified_leads,
                COUNT(CASE WHEN l.status = 'converted' THEN 1 END) as converted_leads
            FROM meta_lead_forms mlf
            LEFT JOIN leads l ON l.meta_form_id = mlf.form_id
            WHERE mlf.form_id = ?
            GROUP BY mlf.id
        ");
        
        $stmt->execute([$formId]);
        return $stmt->fetch();
    }
}
