<?php
/**
 * Meta/Facebook Webhook Handler
 * Captures leads from Facebook/Instagram Lead Ads
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/aisensy.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../models/Lead.php';
require_once __DIR__ . '/../../models/Project.php';
require_once __DIR__ . '/../../services/AiSensyService.php';
require_once __DIR__ . '/../../services/CampaignService.php';

// Handle GET request (webhook verification)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $verifyToken = 'YOUR_WEBHOOK_VERIFY_TOKEN'; // Set this in Meta Developer Console
    
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    
    if ($mode === 'subscribe' && $token === $verifyToken) {
        echo $challenge;
        http_response_code(200);
    } else {
        http_response_code(403);
    }
    exit;
}

// Handle POST request (lead data)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Log webhook request
    $logFile = BASE_PATH . 'logs/meta-webhook.log';
    $logDir = dirname($logFile);
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $input . PHP_EOL, FILE_APPEND);
    
    try {
        // Parse Meta lead data
        if (isset($data['entry'][0]['changes'][0]['value']['leadgen_id'])) {
            $leadData = $data['entry'][0]['changes'][0]['value'];
            
            // Extract lead information
            $name = $leadData['field_data'][0]['values'][0] ?? '';
            $phone = $leadData['field_data'][1]['values'][0] ?? '';
            $email = $leadData['field_data'][2]['values'][0] ?? '';
            
            // Determine project (you may need to customize this logic)
            // For now, using a default project ID or from custom field
            $projectId = $leadData['custom_field']['project_id'] ?? 1;
            
            // Create lead
            $leadModel = new Lead();
            $result = $leadModel->create([
                'project_id' => $projectId,
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'source' => 'meta',
                'status' => 'new',
                'is_subscribed' => 1,
                'lead_data' => $leadData
            ]);
            
            if ($result['success']) {
                $leadId = $result['id'];
                $lead = $leadModel->getById($leadId);
                
                // Send welcome message
                $projectModel = new Project();
                $project = $projectModel->getById($projectId);
                
                if ($project && $project['welcome_message']) {
                    $aisensy = new AiSensyService();
                    $aisensy->sendWelcomeMessage($lead, $project);
                }
                
                // Auto-enroll in campaigns
                $campaignService = new CampaignService();
                $campaignService->autoEnrollLead($leadId);
                
                http_response_code(200);
                echo json_encode(['success' => true]);
            } else {
                http_response_code(200); // Still return 200 to Meta
                echo json_encode(['success' => false, 'error' => $result['error']]);
            }
        } else {
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'No lead data']);
        }
        
    } catch (Exception $e) {
        error_log("Meta webhook error: " . $e->getMessage());
        http_response_code(200); // Return 200 to avoid retries
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit;
}

// Invalid method
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
