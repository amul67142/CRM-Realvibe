<?php
/**
 * Message Template Model
 * Manages reusable message templates
 */

class MessageTemplate {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Get all templates
     */
    public function getAll($type = null) {
        if ($type) {
            $stmt = $this->db->prepare("
                SELECT mt.*, u.full_name as created_by_name
                FROM message_templates mt
                LEFT JOIN users u ON mt.created_by = u.id
                WHERE mt.template_type = ?
                ORDER BY mt.created_at DESC
            ");
            $stmt->execute([$type]);
        } else {
            $stmt = $this->db->query("
                SELECT mt.*, u.full_name as created_by_name
                FROM message_templates mt
                LEFT JOIN users u ON mt.created_by = u.id
                ORDER BY mt.created_at DESC
            ");
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get template by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM message_templates WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Create template
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO message_templates (template_name, template_content, template_type, variables, created_by)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $variables = isset($data['variables']) ? json_encode($data['variables']) : null;
        
        $result = $stmt->execute([
            $data['template_name'],
            $data['template_content'],
            $data['template_type'] ?? 'other',
            $variables,
            $_SESSION['user_id']
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update template
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE message_templates
            SET template_name = ?, template_content = ?, template_type = ?, variables = ?
            WHERE id = ?
        ");
        
        $variables = isset($data['variables']) ? json_encode($data['variables']) : null;
        
        return $stmt->execute([
            $data['template_name'],
            $data['template_content'],
            $data['template_type'] ?? 'other',
            $variables,
            $id
        ]);
    }
    
    /**
     * Delete template
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM message_templates WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
