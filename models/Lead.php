<?php
/**
 * Lead Model
 * Manages leads with duplicate detection and subscription management
 */

class Lead {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Get all leads with filters
     */
    public function getAll($filters = [], $limit = null, $offset = 0) {
        $sql = "
            SELECT l.*, p.project_name, c.name as client_name, u.full_name as assigned_to_name,
                   (SELECT COUNT(*) FROM lead_replies WHERE lead_id = l.id AND is_read = 0) as unread_replies
            FROM leads l
            LEFT JOIN projects p ON l.project_id = p.id
            LEFT JOIN clients c ON p.client_id = c.id
            LEFT JOIN users u ON l.assigned_to = u.id
            WHERE 1=1
        ";
        
        $params = [];
        
        if (isset($filters['project_id'])) {
            $sql .= " AND l.project_id = ?";
            $params[] = $filters['project_id'];
        }
        
        if (isset($filters['source'])) {
            $sql .= " AND l.source = ?";
            $params[] = $filters['source'];
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['is_subscribed'])) {
            $sql .= " AND l.is_subscribed = ?";
            $params[] = $filters['is_subscribed'];
        }
        
        if (isset($filters['assigned_to'])) {
            $sql .= " AND l.assigned_to = ?";
            $params[] = $filters['assigned_to'];
        }
        
        if (isset($filters['search'])) {
            $sql .= " AND (l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND l.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND l.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY l.id DESC, l.created_at DESC";
        
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
     * Get lead by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT l.*, p.project_name, p.location as project_location, p.project_type, p.price_range,
                   c.name as client_name, c.company_name,
                   u.full_name as assigned_to_name
            FROM leads l
            LEFT JOIN projects p ON l.project_id = p.id
            LEFT JOIN clients c ON p.client_id = c.id
            LEFT JOIN users u ON l.assigned_to = u.id
            WHERE l.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Count leads
     */
    public function count($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM leads l WHERE 1=1";
        $params = [];
        
        if (isset($filters['project_id'])) {
            $sql .= " AND l.project_id = ?";
            $params[] = $filters['project_id'];
        }
        
        if (isset($filters['source'])) {
            $sql .= " AND l.source = ?";
            $params[] = $filters['source'];
        }
        
        if (isset($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND l.created_at >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND l.created_at <= ?";
            $params[] = $filters['date_to'];
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Check for duplicate lead (same phone + project)
     */
    public function checkDuplicate($phone, $projectId, $excludeId = null) {
        $sql = "SELECT id FROM leads WHERE phone = ? AND project_id = ?";
        $params = [$phone, $projectId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Create new lead
     */
    public function create($data) {
        // Format phone number
        $phone = formatPhoneNumber($data['phone']);
        
        // Check for duplicate
        if ($this->checkDuplicate($phone, $data['project_id'])) {
            return ['success' => false, 'error' => 'duplicate', 'message' => 'Lead with this phone number already exists for this project'];
        }
        
        $fields = "project_id, name, phone, email, source, status, is_subscribed, budget, notes, lead_data, assigned_to";
        $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
        $params = [
            $data['project_id'],
            $data['name'],
            $phone,
            $data['email'] ?? null,
            $data['source'] ?? 'manual',
            $data['status'] ?? 'new',
            $data['is_subscribed'] ?? 1,
            $data['budget'] ?? null,
            $data['notes'] ?? null,
            $leadData,
            $data['assigned_to'] ?? null
        ];

        if (isset($data['created_at'])) {
            $fields .= ", created_at";
            $placeholders .= ", ?";
            $params[] = $data['created_at'];
        }
        
        $stmt = $this->db->prepare("INSERT INTO leads ($fields) VALUES ($placeholders)");
        
        $result = $stmt->execute($params);
        
        if ($result) {
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        }
        
        return ['success' => false, 'error' => 'database', 'message' => 'Failed to create lead'];
    }
    
    /**
     * Update lead
     */
    public function update($id, $data) {
        // If phone is being updated, check for duplicates
        if (isset($data['phone'])) {
            $data['phone'] = formatPhoneNumber($data['phone']);
            $lead = $this->getById($id);
            
            if ($this->checkDuplicate($data['phone'], $lead['project_id'], $id)) {
                return ['success' => false, 'error' => 'duplicate', 'message' => 'Lead with this phone number already exists for this project'];
            }
        }
        
        $fields = [];
        $params = [];
        
        $allowedFields = ['name', 'phone', 'email', 'status', 'is_subscribed', 'budget', 'notes', 'assigned_to'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (isset($data['lead_data'])) {
            $fields[] = "lead_data = ?";
            $params[] = json_encode($data['lead_data']);
        }
        
        if (empty($fields)) {
            return ['success' => false, 'error' => 'no_data', 'message' => 'No data to update'];
        }
        
        $params[] = $id;
        
        $sql = "UPDATE leads SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt->execute($params)) {
            return ['success' => true];
        }
        
        return ['success' => false, 'error' => 'database', 'message' => 'Failed to update lead'];
    }
    
    /**
     * Update lead status
     */
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE leads SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    /**
     * Subscribe/Unsubscribe lead
     */
    public function updateSubscription($id, $isSubscribed) {
        $stmt = $this->db->prepare("UPDATE leads SET is_subscribed = ? WHERE id = ?");
        return $stmt->execute([$isSubscribed, $id]);
    }
    
    /**
     * Delete lead
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM leads WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get lead by phone and project
     */
    public function getByPhoneAndProject($phone, $projectId) {
        $phone = formatPhoneNumber($phone);
        
        $stmt = $this->db->prepare("
            SELECT * FROM leads WHERE phone = ? AND project_id = ?
        ");
        
        $stmt->execute([$phone, $projectId]);
        return $stmt->fetch();
    }
    
    /**
     * Get leads created today
     */
    public function getCountToday() {
        $stmt = $this->db->query("
            SELECT COUNT(*) as count FROM leads 
            WHERE DATE(created_at) = CURDATE()
        ");
        
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Get leads count by source
     */
    public function getCountBySource() {
        $stmt = $this->db->query("
            SELECT source, COUNT(*) as count
            FROM leads
            GROUP BY source
        ");
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get leads count by status
     */
    public function getCountByStatus() {
        $stmt = $this->db->query("
            SELECT status, COUNT(*) as count
            FROM leads
            GROUP BY status
        ");
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get recent leads
     */
    public function getRecent($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT l.*, p.project_name
            FROM leads l
            LEFT JOIN projects p ON l.project_id = p.id
            ORDER BY l.id DESC, l.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
