<?php
/**
 * User Model
 * Handles user authentication and management
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Authenticate user
     */
    public function authenticate($username, $password) {
        $stmt = $this->db->prepare("
            SELECT id, username, email, password, full_name, role, is_active
            FROM users
            WHERE (username = ? OR email = ?) AND is_active = 1
        ");
        
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from user array
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT id, username, email, full_name, role, is_active, created_at, updated_at
            FROM users
            WHERE id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get all users
     */
    public function getAll($filters = []) {
        $sql = "SELECT id, username, email, full_name, role, is_active, created_at FROM users WHERE 1=1";
        $params = [];
        
        if (isset($filters['role'])) {
            $sql .= " AND role = ?";
            $params[] = $filters['role'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Create new user
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (username, email, password, full_name, role, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        return $stmt->execute([
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['full_name'],
            $data['role'] ?? 'agent',
            $data['is_active'] ?? 1
        ]);
    }
    
    /**
     * Update user
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $params[] = $data['username'];
        }
        
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $params[] = $data['full_name'];
        }
        
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }
        
        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $params[] = $data['is_active'];
        }
        
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }
    
    /**
     * Delete user
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
