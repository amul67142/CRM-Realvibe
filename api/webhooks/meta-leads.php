<?php
/**
 * Meta Lead Ads Webhook Endpoint
 * Receives lead data from Facebook/Instagram Lead Ads
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../services/MetaLeadsService.php';

// Set headers
header('Content-Type: application/json');

// Log incoming request
$logFile = __DIR__ . '/../../logs/meta-webhook.log';
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];
$timestamp = date('Y-m-d H:i:s');

// Create log directory if it doesn't exist
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

file_put_contents($logFile, "\n=== [$timestamp] $requestMethod $requestUri ===\n", FILE_APPEND);

try {
    $metaService = new MetaLeadsService();
    
    // Handle GET request (Webhook Verification)
    if ($requestMethod === 'GET') {
        handleWebhookVerification($logFile);
        exit;
    }
    
    // Handle POST request (Lead Data)
    if ($requestMethod === 'POST') {
        handleLeadData($metaService, $logFile);
        exit;
    }
    
    // Method not allowed
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    
} catch (Exception $e) {
    error_log("Meta Webhook Error: " . $e->getMessage());
    file_put_contents($logFile, "ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

/**
 * Handle webhook verification (GET request)
 * Meta will send: hub.mode, hub.verify_token, hub.challenge
 */
function handleWebhookVerification($logFile) {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    
    file_put_contents($logFile, "Verification request - Mode: $mode, Token: $token\n", FILE_APPEND);
    
    // Get verify token from settings
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'meta_verify_token'");
    $stmt->execute();
    $result = $stmt->fetch();
    $verifyToken = $result ? $result['setting_value'] : null;
    
    // Check if mode and token are correct
    if ($mode === 'subscribe' && $token === $verifyToken) {
        file_put_contents($logFile, "Verification successful! Returning challenge: $challenge\n", FILE_APPEND);
        
        // Respond with the challenge to verify webhook
        http_response_code(200);
        echo $challenge;
    } else {
        file_put_contents($logFile, "Verification failed! Expected token: $verifyToken\n", FILE_APPEND);
        
        // Verification failed
        http_response_code(403);
        echo json_encode(['error' => 'Verification failed']);
    }
}

/**
 * Handle lead data (POST request)
 */
function handleLeadData($metaService, $logFile) {
    // Get raw POST data
    $rawData = file_get_contents('php://input');
    file_put_contents($logFile, "Raw payload:\n$rawData\n", FILE_APPEND);
    
    // Get signature header
    $signature = $_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? $_SERVER['HTTP_X_HUB_SIGNATURE'] ?? '';
    file_put_contents($logFile, "Signature: $signature\n", FILE_APPEND);
    
    // Verify signature if app secret is configured
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'meta_app_secret'");
    $stmt->execute();
    $result = $stmt->fetch();
    $appSecret = $result ? $result['setting_value'] : null;
    
    if ($appSecret && $signature) {
        if (!$metaService->verifyWebhookSignature($rawData, $signature, $appSecret)) {
            file_put_contents($logFile, "Signature verification FAILED!\n", FILE_APPEND);
            http_response_code(403);
            echo json_encode(['error' => 'Invalid signature']);
            return;
        }
        file_put_contents($logFile, "Signature verification PASSED\n", FILE_APPEND);
    } else {
        file_put_contents($logFile, "WARNING: Signature verification skipped (no app secret configured)\n", FILE_APPEND);
    }
    
    // Parse JSON data
    $data = json_decode($rawData, true);
    
    if (!$data) {
        file_put_contents($logFile, "Invalid JSON data\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        return;
    }
    
    // Process the webhook
    $result = $metaService->processWebhook($data);
    
    file_put_contents($logFile, "Processing result: " . json_encode($result) . "\n", FILE_APPEND);
    
    // Always return 200 OK to Meta, even if processing had errors
    // This prevents Meta from retrying and overwhelming the server
    http_response_code(200);
    echo json_encode([
        'status' => 'received',
        'processed' => $result['processed'] ?? 0
    ]);
}
