<?php
/**
 * Send Campaign Messages - Cron Job
 * Run this hourly to send due campaign messages
 * Cron: 0 * * * * /usr/bin/php /path/to/send-campaign-messages.php
 */

// Set execution time limit
set_time_limit(300); // 5 minutes

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/aisensy.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../services/CampaignService.php';

// Log start
$logFile = BASE_PATH . 'logs/cron-campaign-messages.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

logMessage("=== Campaign Message Processing Started ===");

try {
    $campaignService = new CampaignService();
    
    logMessage("Processing due messages...");
    $result = $campaignService->processDueMessages();
    
    if ($result['success']) {
        logMessage("Messages sent: {$result['messages_sent']}");
        logMessage("Messages failed: {$result['messages_failed']}");
        logMessage("Campaigns completed: {$result['campaigns_completed']}");
        
        // Alert admin if too many failures
        if ($result['messages_failed'] > AISENSY_ERROR_THRESHOLD) {
            logMessage("WARNING: High failure rate detected!");
            
            // Send email alert (if email system is configured)
            $errorMsg = "Campaign cron job reported {$result['messages_failed']} failed messages.";
            error_log($errorMsg);
            
            // You could implement email notification here
            // mail(AISENSY_ALERT_EMAIL, 'CRM Alert: High Message Failure Rate', $errorMsg);
        }
    } else {
        logMessage("ERROR: " . ($result['error'] ?? 'Unknown error'));
    }
    
} catch (Exception $e) {
    logMessage("EXCEPTION: " . $e->getMessage());
    error_log("Campaign cron error: " . $e->getMessage());
}

logMessage("=== Campaign Message Processing Completed ===");
logMessage("");

// Exit successfully
exit(0);
