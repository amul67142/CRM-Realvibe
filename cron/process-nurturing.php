<?php
/**
 * Process Nurturing Campaigns - Cron Job
 * 
 * Schedule: Every hour during business hours (10 AM - 6 PM)
 * Cron: 0 10-18 * * * cd /path/to/Realvibe && php cron/process-nurturing.php
 * 
 * Windows Task Scheduler: Run hourly between 10 AM and 6 PM:
 * c:\xampp\php\php.exe c:\xampp\htdocs\Realvibe\cron\process-nurturing.php
 */

// Set up environment
define('IS_CRON', true);
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/services/NurturingService.php';

// Log file
$logFile = dirname(__DIR__) . '/logs/cron-nurturing.log';
$logDir = dirname($logFile);

// Ensure log directory exists
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

/**
 * Log helper
 */
function logCron($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(
        $logFile,
        "[{$timestamp}] {$message}" . PHP_EOL,
        FILE_APPEND
    );
    
    // Also echo for manual runs
    if (php_sapi_name() === 'cli') {
        echo "[{$timestamp}] {$message}" . PHP_EOL;
    }
}

// Start
logCron("==================== Cron Job Started ====================");

try {
    // Initialize nurturing service
    $nurturingService = new NurturingService();
    
    // Process all active campaigns
    $messagesSent = $nurturingService->processNurturing();
    
    logCron("Completed successfully. Messages sent: $messagesSent");
    logCron("==================== Cron Job Finished ====================");
    
    exit(0); // Success
    
} catch (Exception $e) {
    logCron("ERROR: " . $e->getMessage());
    logCron("==================== Cron Job Failed ====================");
    
    exit(1); // Failure
}
