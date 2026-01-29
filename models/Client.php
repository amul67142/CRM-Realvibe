<?php
/**
 * Client Model
 * Manages real estate clients/developers
 */

class Client {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Get all clients
     */
    public function getAll($limit = null, $offset = 0) {
        $sql = "
            SELECT c.*, u.full_name as created_by_name,
                   (SELECT COUNT(*) FROM projects WHERE client_id = c.id) as projects_count
            FROM clients c
            LEFT JOIN users u ON c.created_by = u.id
            ORDER BY c.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get client by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.full_name as created_by_name
            FROM clients c
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Count total clients
     */
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM clients");
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Create new client
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO clients (name, email, phone, company_name, address, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['company_name'] ?? null,
            $data['address'] ?? null,
            $data['notes'] ?? null,
            $_SESSION['user_id']
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Update client
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE clients
            SET name = ?, email = ?, phone = ?, company_name = ?, address = ?, notes = ?
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['company_name'] ?? null,
            $data['address'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);
    }
    
    /**
     * Delete client
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM clients WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Search clients
     */
    public function search($query) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.full_name as created_by_name
            FROM clients c
            LEFT JOIN users u ON c.created_by = u.id
            WHERE c.name LIKE ? OR c.email LIKE ? OR c.phone LIKE ? OR c.company_name LIKE ?
            ORDER BY c.name ASC
        ");
        
        $searchTerm = "%$query%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
}
