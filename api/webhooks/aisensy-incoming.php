<?php
/**
 * AiSensy Incoming Message Webhook
 * Receives incoming WhatsApp messages from leads
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Lead.php';
require_once __DIR__ . '/../../models/LeadReply.php';
require_once __DIR__ . '/../../services/AiSensyService.php';
require_once __DIR__ . '/../../services/CampaignService.php';
require_once __DIR__ . '/../../services/NotificationService.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log incoming webhook
$logFile = BASE_PATH . 'logs/aisensy-incoming.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $input . PHP_EOL, FILE_APPEND);

try {
    // Parse AiSensy incoming message data
    $phone = $data['phone'] ?? $data['from'] ?? '';
    $message = $data['message'] ?? $data['text'] ?? '';
    $messageId = $data['messageId'] ?? null;
    $mediaUrl = $data['mediaUrl'] ?? null;
    
    if (empty($phone) || empty($message)) {
        http_response_code(200);
        exit(json_encode(['success' => false, 'error' => 'Missing phone or message']));
    }
    
    // Format phone number
    $aisensy = new AiSensyService();
    $phone = $aisensy->formatPhoneNumber($phone);
    
    // Find lead by phone number
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT * FROM leads WHERE phone = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$phone]);
    $lead = $stmt->fetch();
    
    if (!$lead) {
        http_response_code(200);
        exit(json_encode(['success' => false, 'error' => 'Lead not found']));
    }
    
    // Store reply
    $replyModel = new LeadReply();
    $replyId = $replyModel->store([
        'lead_id' => $lead['id'],
        'message_id' => $messageId,
        'reply_content' => $message,
        'media_url' => $mediaUrl
    ]);
    
    // Check for STOP/UNSUBSCRIBE keywords
    if ($replyModel->isUnsubscribeRequest($message)) {
        $campaignService = new CampaignService();
        $campaignService->unsubscribeLead($lead['id']);
        
        // Send confirmation
        $aisensy->sendUnsubscribeConfirmation($phone, $lead['name']);
        
        logActivity('lead_unsubscribed_via_reply', 'lead', $lead['id'], "Lead unsubscribed via STOP keyword");
    }
    // Check for START keyword
    elseif ($replyModel->isSubscribeRequest($message)) {
        $campaignService = new CampaignService();
        $campaignService->resubscribeLead($lead['id']);
        
        // Send confirmation
        $aisensy->sendResubscribeConfirmation($phone, $lead['name']);
        
        logActivity('lead_resubscribed_via_reply', 'lead', $lead['id'], "Lead resubscribed via START keyword");
    }
    // Regular reply - notify agent
    else {
        $notificationService = new NotificationService();
        $notificationService->notifyNewReply($lead['id'], $message);
    }
    
    http_response_code(200);
    echo json_encode(['success' => true, 'reply_id' => $replyId]);
    
} catch (Exception $e) {
    error_log("AiSensy incoming webhook error: " . $e->getMessage());
    http_response_code(200); // Return 200 to avoid retries
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
