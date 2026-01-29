<?php
/**
 * AiSensy API Configuration
 * WhatsApp Business API Integration
 */

// AiSensy API Credentials
define('AISENSY_API_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY3ZWQzYzI3MWZlZmEzMDdlNTI4ODA0ZSIsIm5hbWUiOiJSZWFsdmliZSBPZmZpY2lhbCBOdW1iZXIgKDEwMCkiLCJhcHBOYW1lIjoiQWlTZW5zeSIsImNsaWVudElkIjoiNjY4MjcyN2IyOGQ2NWIxZTliOGZjMjVkIiwiYWN0aXZlUGxhbiI6IkZSRUVfRk9SRVZFUiIsImlhdCI6MTc0MzY1ODQ4MH0.c0icK6if0zSaKtv-ut0iA2lOFOiqSNlo246E6n8crhA');
define('AISENSY_API_BASE_URL', 'https://backend.aisensy.com/campaign/t1/api/v2');

// Webhook URLs (update after deployment)
define('AISENSY_WEBHOOK_STATUS_URL', BASE_URL . '/api/webhooks/aisensy-status.php');
define('AISENSY_WEBHOOK_INCOMING_URL', BASE_URL . '/api/webhooks/aisensy-incoming.php');

// API Settings
define('AISENSY_TIMEOUT', 30); // Request timeout in seconds
define('AISENSY_MAX_RETRIES', 3); // Maximum retry attempts for failed messages
define('AISENSY_RETRY_DELAY', 300); // Delay between retries in seconds (5 minutes)

// Phone number settings
define('DEFAULT_COUNTRY_CODE', '91'); // India country code

// Error thresholds
define('AISENSY_ERROR_THRESHOLD', 10); // Alert admin after this many failures
define('AISENSY_ALERT_EMAIL', 'admin@realvibe.com'); // Email for critical alerts

// Message templates (pre-approved AiSensy template IDs)
// Update these with your actual approved template IDs from AiSensy dashboard
define('AISENSY_TEMPLATES', [
    'welcome' => 'YOUR_WELCOME_TEMPLATE_ID',
    'followup_day1' => 'YOUR_DAY1_TEMPLATE_ID',
    'followup_day2' => 'YOUR_DAY2_TEMPLATE_ID',
    'followup_day3' => 'YOUR_DAY3_TEMPLATE_ID',
    'followup_day4' => 'YOUR_DAY4_TEMPLATE_ID',
    'followup_day5' => 'YOUR_DAY5_TEMPLATE_ID',
    'unsubscribe_confirmation' => 'YOUR_UNSUBSCRIBE_TEMPLATE_ID',
    'resubscribe_confirmation' => 'YOUR_RESUBSCRIBE_TEMPLATE_ID',
]);

// Rate limiting (to avoid API abuse)
define('AISENSY_RATE_LIMIT_PER_MINUTE', 60); // Max messages per minute
define('AISENSY_RATE_LIMIT_PER_HOUR', 1000); // Max messages per hour

// Logging
define('AISENSY_LOG_ALL_REQUESTS', true); // Log all API requests for debugging
define('AISENSY_LOG_FILE', BASE_PATH . 'logs/aisensy.log');
