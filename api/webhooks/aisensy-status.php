<?php
/**
 * AiSensy Status Webhook
 * Updates message delivery status
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/WhatsAppMessage.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log status update
$logFile = BASE_PATH . 'logs/aisensy-status.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $input . PHP_EOL, FILE_APPEND);

try {
    $messageId = $data['messageId'] ?? '';
    $status = strtolower($data['status'] ?? '');
    $errorMessage = $data['error'] ?? null;
    $errorCode = $data['errorCode'] ?? null;
    
    if (empty($messageId)) {
        http_response_code(200);
        exit(json_encode(['success' => false, 'error' => 'Missing messageId']));
    }
    
    // Map AiSensy status to our status
    $statusMap = [
        'sent' => 'sent',
        'delivered' => 'delivered',
        'read' => 'read',
        'failed' => 'failed',
        'error' => 'failed'
    ];
    
    $ourStatus = $statusMap[$status] ?? $status;
    
    // Update message status
    $messageModel = new WhatsAppMessage();
    $messageModel->updateStatus($messageId, $ourStatus, $errorMessage, $errorCode);
    
    http_response_code(200);
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("AiSensy status webhook error: " . $e->getMessage());
    http_response_code(200);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
