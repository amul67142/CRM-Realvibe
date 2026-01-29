-- Add WhatsApp provider configuration columns to projects table
-- This allows each project to have its own WhatsApp messaging provider

ALTER TABLE projects 
ADD COLUMN whatsapp_provider VARCHAR(50) DEFAULT 'default' 
    COMMENT 'WhatsApp provider: default, aisensy, whatsapp_api, twilio' 
    AFTER welcome_message;

ALTER TABLE projects 
ADD COLUMN aisensy_campaign_name VARCHAR(100) NULL 
    COMMENT 'AiSensy campaign name (required when provider is aisensy)' 
    AFTER whatsapp_provider;

-- Update existing projects to use default provider
UPDATE projects 
SET whatsapp_provider = 'default' 
WHERE whatsapp_provider IS NULL OR whatsapp_provider = '';

-- Show updated table structure
DESCRIBE projects;
