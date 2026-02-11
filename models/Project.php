<?php
/**
 * Project Model
 * Manages real estate projects
 */

class Project {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Get all projects
     */
    public function getAll($filters = [], $limit = null, $offset = 0) {
        $sql = "
            SELECT p.*, c.name as client_name,
                   (SELECT COUNT(*) FROM leads WHERE project_id = p.id) as leads_count
            FROM projects p
            LEFT JOIN clients c ON p.client_id = c.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (isset($filters['client_id'])) {
            $sql .= " AND p.client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND p.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get project by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as client_name, c.email as client_email, c.phone as client_phone
            FROM projects p
            LEFT JOIN clients c ON p.client_id = c.id
            WHERE p.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Count total projects
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM projects WHERE 1=1";
        $params = [];
        
        if (isset($filters['client_id'])) {
            $sql .= " AND client_id = ?";
            $params[] = $filters['client_id'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Create new project
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO projects (client_id, project_name, project_type, location, description, 
                                  welcome_message, whatsapp_provider, aisensy_campaign_name,
                                  price_range, brochure_url, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['client_id'],
            $data['project_name'],
            $data['project_type'] ?? null,
            $data['location'] ?? null,
            $data['description'] ?? null,
            $data['welcome_message'] ?? null,
            $data['whatsapp_provider'] ?? 'default',
            $data['aisensy_campaign_name'] ?? null,
            $data['price_range'] ?? null,
            $data['brochure_url'] ?? null,
            $data['is_active'] ?? 1
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update project
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE projects
            SET client_id = ?, project_name = ?, project_type = ?, location = ?, description = ?,
                welcome_message = ?, whatsapp_provider = ?, aisensy_campaign_name = ?,
                price_range = ?, brochure_url = ?, is_active = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['client_id'],
            $data['project_name'],
            $data['project_type'] ?? null,
            $data['location'] ?? null,
            $data['description'] ?? null,
            $data['welcome_message'] ?? null,
            $data['whatsapp_provider'] ?? 'default',
            $data['aisensy_campaign_name'] ?? null,
            $data['price_range'] ?? null,
            $data['brochure_url'] ?? null,
            $data['is_active'] ?? 1,
            $id
        ]);
    }
    
    /**
     * Delete project
     */
    public function delete($id) {
        // Delete associated leads first
        $deleteLeads = $this->db->prepare("DELETE FROM leads WHERE project_id = ?");
        $deleteLeads->execute([$id]);
        
        // Delete associated meta lead forms
        $deleteMetaForms = $this->db->prepare("DELETE FROM meta_lead_forms WHERE project_id = ?");
        $deleteMetaForms->execute([$id]);

        // Delete the project
        $stmt = $this->db->prepare("DELETE FROM projects WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get projects for dropdown
     */
    public function getForDropdown() {
        $stmt = $this->db->query("
            SELECT p.id, p.project_name, c.name as client_name
            FROM projects p
            LEFT JOIN clients c ON p.client_id = c.id
            WHERE p.is_active = 1
            ORDER BY p.project_name ASC
        ");
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get Meta Lead Forms for this project
     */
    public function getMetaLeadForms($projectId) {
        $stmt = $this->db->prepare("
            SELECT * FROM meta_lead_forms
            WHERE project_id = ?
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add Meta Lead Form to project
     */
    public function addMetaLeadForm($projectId, $formId, $formName = null) {
        require_once __DIR__ . '/MetaLeadForm.php';
        $metaLeadForm = new MetaLeadForm();
        return $metaLeadForm->create($projectId, $formId, $formName);
    }
    
    /**
     * Remove Meta Lead Form from project
     */
    public function removeMetaLeadForm($formId) {
        $stmt = $this->db->prepare("DELETE FROM meta_lead_forms WHERE id = ?");
        return $stmt->execute([$formId]);
    }
    
    /**
     * Get WhatsApp provider for project
     */
    public function getWhatsAppProvider($projectId) {
        $stmt = $this->db->prepare("SELECT whatsapp_provider FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $result = $stmt->fetch();
        return $result ? $result['whatsapp_provider'] : 'default';
    }
    
    /**
     * Get WhatsApp configuration for project
     */
    public function getWhatsAppConfig($projectId) {
        $stmt = $this->db->prepare("
            SELECT whatsapp_provider, aisensy_campaign_name, welcome_message
            FROM projects
            WHERE id = ?
        ");
        
        $stmt->execute([$projectId]);
        return $stmt->fetch();
    }
}
