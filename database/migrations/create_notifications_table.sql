-- In-App Notifications System
-- Create notifications table for in-app notification bell

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL COMMENT 'NULL = global notification for all users',
    type VARCHAR(50) NOT NULL COMMENT 'new_lead, lead_reply, status_change, etc',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) NULL COMMENT 'Relative URL to navigate to',
    icon VARCHAR(50) DEFAULT 'bell' COMMENT 'Icon class or name',
    is_read BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_type (type),
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Show status
SELECT 'Notifications table created' as status;
