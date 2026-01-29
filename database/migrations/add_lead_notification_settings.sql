-- Lead Notification System Setup
-- Add admin profile settings and ensure clients have phone field

-- Add admin profile settings
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
('admin_name', 'Admin', 'string'),
('admin_email', 'admin@realvibe.com', 'string'),
('admin_phone', '', 'string'),
('admin_notification_enabled', '1', 'boolean')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- Verify clients table has phone column (should already exist)
-- If it doesn't exist, add it
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'realvibe' 
    AND TABLE_NAME = 'clients' 
    AND COLUMN_NAME = 'phone');

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE clients ADD COLUMN phone VARCHAR(20) NULL AFTER email',
    'SELECT "Phone column already exists" as status');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Show results
SELECT 'Admin settings added' as status;
SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'admin_%';
