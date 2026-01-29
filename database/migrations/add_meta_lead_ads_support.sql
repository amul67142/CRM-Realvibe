/**
 * Meta Lead Ads Integration - Database Migration
 * 
 * This migration adds support for Meta Lead Ads integration:
 * 1. Creates meta_lead_forms table for Form ID to Project mapping
 * 2. Adds Meta tracking columns to leads table
 * 3. Adds Meta API settings
 */

-- ============================================================
-- 1. Create meta_lead_forms table
-- ============================================================
CREATE TABLE IF NOT EXISTS meta_lead_forms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    form_id VARCHAR(255) NOT NULL COMMENT 'Meta Lead Form ID',
    form_name VARCHAR(255) NULL COMMENT 'Descriptive name for the form',
    is_active BOOLEAN DEFAULT 1 COMMENT 'Enable/disable lead capture from this form',
    leads_captured INT DEFAULT 0 COMMENT 'Total leads captured from this form',
    last_lead_at TIMESTAMP NULL COMMENT 'When the last lead was captured',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    UNIQUE KEY unique_form (form_id),
    INDEX idx_project_active (project_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Maps Meta Lead Form IDs to CRM Projects';

-- ============================================================
-- 2. Add Meta tracking columns to leads table
-- ============================================================

-- Check if columns exist before adding them
SET @dbname = DATABASE();
SET @tablename = 'leads';

-- Add meta_form_id column
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'meta_form_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE leads ADD COLUMN meta_form_id VARCHAR(255) NULL COMMENT "Meta Lead Form ID" AFTER source',
    'SELECT "Column meta_form_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add meta_lead_id column
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'meta_lead_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE leads ADD COLUMN meta_lead_id VARCHAR(255) NULL COMMENT "Meta Lead ID for tracking" AFTER meta_form_id',
    'SELECT "Column meta_lead_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add meta_ad_id column
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'meta_ad_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE leads ADD COLUMN meta_ad_id VARCHAR(255) NULL COMMENT "Meta Ad ID that generated this lead" AFTER meta_lead_id',
    'SELECT "Column meta_ad_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add meta_campaign_id column
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND COLUMN_NAME = 'meta_campaign_id'
);

SET @sql = IF(@column_exists = 0,
    'ALTER TABLE leads ADD COLUMN meta_campaign_id VARCHAR(255) NULL COMMENT "Meta Campaign ID" AFTER meta_ad_id',
    'SELECT "Column meta_campaign_id already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for Meta lead tracking
SET @index_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @dbname
    AND TABLE_NAME = @tablename
    AND INDEX_NAME = 'idx_meta_form'
);

SET @sql = IF(@index_exists = 0,
    'ALTER TABLE leads ADD INDEX idx_meta_form (meta_form_id, meta_lead_id)',
    'SELECT "Index idx_meta_form already exists" AS message'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================================
-- 3. Add Meta API settings to settings table
-- ============================================================

-- Insert Meta App credentials settings
INSERT INTO settings (setting_key, setting_value, setting_type) 
VALUES 
    ('meta_app_id', '', 'text'),
    ('meta_app_secret', '', 'password'),
    ('meta_access_token', '', 'textarea'),
    ('meta_verify_token', '', 'text')
ON DUPLICATE KEY UPDATE 
    setting_type = VALUES(setting_type);

-- ============================================================
-- 4. Verification Queries
-- ============================================================

-- Show the new table structure
SELECT 'Meta Lead Forms Table Created' AS status;
DESCRIBE meta_lead_forms;

-- Show the updated leads table columns
SELECT 'Leads Table Updated with Meta Columns' AS status;
SHOW COLUMNS FROM leads WHERE Field LIKE 'meta_%';

-- Show Meta settings
SELECT 'Meta Settings Added' AS status;
SELECT setting_key, setting_type, setting_value 
FROM settings 
WHERE setting_key LIKE 'meta_%'
ORDER BY setting_key;

SELECT '✓ Migration completed successfully!' AS result;
