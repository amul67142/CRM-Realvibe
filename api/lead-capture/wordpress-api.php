<?php
/**
 * WordPress Lead Capture API
 * Receives leads from WordPress contact forms
 * 
 * URL: https://yourdomain.com/Realvibe/api/lead-capture/wordpress-api.php
 * Method: POST
 * 
 * Required Parameters:
 * - name: Lead's full name
 * - phone: Lead's phone number
 * - project_id: Project ID in CRM
 * 
 * Optional Parameters:
 * - email: Lead's email
 * - budget: Lead's budget
 * - message: Additional message/notes
 * - source_url: URL where form was submitted
 */

// Allow CORS for WordPress sites
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

// Include required files
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../helpers/whatsapp.php';
require_once __DIR__ . '/../../models/Lead.php';
require_once __DIR__ . '/../../models/Project.php';
require_once __DIR__ . '/../../services/CampaignService.php';

// Log incoming request
$logFile = BASE_PATH . 'logs/wordpress-api.log';
$logDir = dirname($logFile);
if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

// Get POST data (both form-data and JSON)
$input = file_get_contents('php://input');
if (!empty($input) && strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $data = json_decode($input, true);
} else {
    $data = $_POST;
}

// Log request
file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . json_encode($data) . PHP_EOL, FILE_APPEND);

try {
    // Validate required fields
    $name = sanitizeInput($data['name'] ?? '');
    $phone = sanitizeInput($data['phone'] ?? '');
    $projectId = (int)($data['project_id'] ?? 0);
    
    if (empty($name) || empty($phone) || empty($projectId)) {
        http_response_code(400);
        exit(json_encode([
            'success' => false,
            'error' => 'Missing required fields: name, phone, project_id'
        ]));
    }
    
    // Verify project exists
    $projectModel = new Project();
    $project = $projectModel->getById($projectId);
    
    if (!$project) {
        http_response_code(404);
        exit(json_encode([
            'success' => false,
            'error' => 'Project not found'
        ]));
    }
    
    // Prepare lead data
    $leadData = [
        'project_id' => $projectId,
        'name' => $name,
        'phone' => $phone,
        'email' => sanitizeInput($data['email'] ?? ''),
        'source' => 'wordpress',
        'status' => 'new',
        'is_subscribed' => 1,
        'budget' => sanitizeInput($data['budget'] ?? ''),
        'notes' => sanitizeInput($data['message'] ?? $data['notes'] ?? ''),
        'lead_data' => [
            'source_url' => $data['source_url'] ?? $_SERVER['HTTP_REFERER'] ?? '',
            'form_data' => $data
        ]
    ];
    
    // Create lead
    $leadModel = new Lead();
    $result = $leadModel->create($leadData);
    
    if ($result['success']) {
        $leadId = $result['id'];
        $lead = $leadModel->getById($leadId);
        
        // Send welcome message via WhatsApp using project's provider
        if ($project['welcome_message']) {
            try {
                // Prepare message with merge tags
                $message = $project['welcome_message'];
                $message = str_replace('{{name}}', $lead['name'], $message);
                $message = str_replace('{{project_name}}', $project['project_name'], $message);
                $message = str_replace('{{location}}', $project['location'] ?? '', $message);
                $message = str_replace('{{price_range}}', $project['price_range'] ?? '', $message);
                
                // Send using project-specific provider
                $welcomeResult = sendWhatsAppMessage($lead['phone'], $message, $projectId);
                
                if (!$welcomeResult['success']) {
                    error_log("Failed to send welcome message (Project ID: $projectId): " . ($welcomeResult['error'] ?? 'Unknown error'));
                }
            } catch (Exception $e) {
                error_log("Welcome message error: " . $e->getMessage());
            }
        }
        
        // Send lead alert notifications to client and admin
        try {
            require_once __DIR__ . '/../../services/LeadNotificationService.php';
            $notificationService = new LeadNotificationService();
            $notificationResults = $notificationService->sendNewLeadAlert($lead, $project);
            
            if (!$notificationResults['success']) {
                error_log("Lead notification error: " . ($notificationResults['message'] ?? 'Unknown error'));
            }
        } catch (Exception $e) {
            error_log("Lead notification exception: " . $e->getMessage());
        }
        
        // Create in-app notification
        require_once __DIR__ . '/../../models/Notification.php';
        $notif = new Notification();
        $notif->create([
            'user_id' => null,
            'type' => 'new_lead',
            'title' => 'New Lead from WordPress',
            'message' => "New lead from {$lead['name']} for {$project['project_name']} via WordPress",
            'link' => 'leads/' . $leadId,
            'icon' => 'globe'
        ]);
        
        // Auto-enroll in campaigns
        try {
            $campaignService = new CampaignService();
            $campaignService->autoEnrollLead($leadId);
        } catch (Exception $e) {
            error_log("Campaign enrollment error: " . $e->getMessage());
        }
        
        // Success response
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Lead captured successfully',
            'lead_id' => $leadId,
            'data' => [
                'name' => $lead['name'],
                'phone' => $lead['phone'],
                'project' => $project['project_name']
            ]
        ]);
        
        // Log activity
        logActivity('lead_captured', 'lead', $leadId, "Lead captured from WordPress: {$lead['name']}");
        
    } else {
        // Handle duplicate or other errors
        if ($result['error'] === 'duplicate') {
            http_response_code(409);
            echo json_encode([
                'success' => false,
                'error' => 'duplicate',
                'message' => 'This phone number is already registered for this project'
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'database_error',
                'message' => $result['message'] ?? 'Failed to save lead'
            ]);
        }
    }
    
} catch (Exception $e) {
    error_log("WordPress API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'server_error',
        'message' => 'An error occurred while processing your request'
    ]);
}
