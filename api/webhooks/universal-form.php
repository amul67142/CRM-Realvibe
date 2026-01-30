<?php
/**
 * Universal Form Webhook
 * Works with ANY website - WordPress, HTML, React, Vue, etc.
 * Accepts both JSON and form-data submissions
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow from any domain
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


// Suppress errors in output to prevent JSON corruption
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Lead.php';
require_once __DIR__ . '/../../services/AiSensyService.php';
require_once __DIR__ . '/../../services/CampaignService.php';

// Log file
$logFile = BASE_PATH . 'logs/universal-webhook.log';
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function logWebhook($message) {
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

try {
    logWebhook("=== New webhook request ===");
    
    // Get raw POST data
    $rawData = file_get_contents('php://input');
    logWebhook("Raw data: " . $rawData);
    
    // Try to parse as JSON first
    $data = json_decode($rawData, true);
    
    // If not JSON, try form-data
    if (!$data) {
        parse_str($rawData, $data);
    }
    
    // Also merge $_POST (for standard form submissions)
    if (!empty($_POST)) {
        $data = array_merge($data, $_POST);
    }
    
    // Also check $_GET for query parameters
    if (!empty($_GET)) {
        $data = array_merge($data, $_GET);
    }
    
    logWebhook("Parsed data: " . json_encode($data));
    
    if (empty($data)) {
        throw new Exception('No data received');
    }
    
    // ============================================
    // Intelligent Field Mapping
    // Automatically detects common field variations
    // ============================================
    
    // Name variations
    $name = $data['name'] 
         ?? $data['your-name'] 
         ?? $data['full-name'] 
         ?? $data['fullname'] 
         ?? $data['full_name']
         ?? $data['customer_name']
         ?? $data['user_name']
         ?? $data['firstName'] . ' ' . ($data['lastName'] ?? '')
         ?? $data['first_name'] . ' ' . ($data['last_name'] ?? '')
         ?? 'Unknown';
    
    // Email variations
    $email = $data['email'] 
          ?? $data['your-email'] 
          ?? $data['user-email']
          ?? $data['user_email']
          ?? $data['customer_email']
          ?? $data['emailAddress']
          ?? $data['email_address']
          ?? null;
    
    // Phone variations
    $phone = $data['phone'] 
          ?? $data['your-phone']
          ?? $data['mobile']
          ?? $data['telephone']
          ?? $data['contact']
          ?? $data['contact_number']
          ?? $data['phone_number']
          ?? $data['phoneNumber']
          ?? $data['whatsapp']
          ?? null;
    
    // Message/Notes variations
    $message = $data['message'] 
            ?? $data['your-message']
            ?? $data['comments']
            ?? $data['notes']
            ?? $data['description']
            ?? $data['inquiry']
            ?? '';
    
    // Project ID (can be passed in URL or form)
    $projectId = $data['project_id'] 
              ?? $data['projectId']
              ?? $_GET['project_id']
              ?? $_GET['project']
              ?? 1; // Default to project 1
    
    // Source (can be passed to identify the website)
    $source = $data['source'] 
           ?? $data['form_source']
           ?? $_GET['source']
           ?? 'website_form';
    
    // Clean phone number
    if ($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        // Add country code if missing (assuming India +91)
        if (!str_starts_with($phone, '+') && !str_starts_with($phone, '91')) {
            $phone = '91' . $phone;
        }
    }
    
    logWebhook("Extracted - Name: $name, Email: $email, Phone: $phone, Project: $projectId, Source: $source");
    
    // Validate: Need at least one contact method
    if (!$email && !$phone) {
        throw new Exception('Either email or phone is required');
    }
    
    // Build notes from all fields
    $notes = "Source: $source\n";
    $notes .= "Received: " . date('Y-m-d H:i:s') . "\n\n";
    
    if ($message) {
        $notes .= "Message: $message\n\n";
    }
    
    // Add all other fields as notes
    $skipFields = ['name', 'your-name', 'email', 'your-email', 'phone', 'your-phone', 
                   'message', 'your-message', 'project_id', 'source'];
    
    foreach ($data as $key => $value) {
        if (!in_array($key, $skipFields) && !empty($value) && is_string($value)) {
            $notes .= ucfirst(str_replace(['_', '-'], ' ', $key)) . ": $value\n";
        }
    }
    
    
    // Create lead
    $db = getDatabaseConnection();
    $leadModel = new Lead();
    
    $leadData = [
        'name' => trim($name),
        'email' => $email,
        'phone' => $phone,
        'project_id' => $projectId,
        'source' => $source,
        'status' => 'new',
        'notes' => trim($notes),
        'is_subscribed' => 1
    ];
    
    
    $result = $leadModel->create($leadData);
    
    if ($result['success']) {
        $leadId = $result['id'];
        logWebhook("✅ Lead created successfully! ID: $leadId");
        
        // Send welcome message if phone is available
        if ($phone) {
            try {
                $aisensy = new AiSensyService();
                $lead = $leadModel->getById($leadId);
                
                // Get project details for welcome message
                $projectStmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
                $projectStmt->execute([$projectId]);
                $project = $projectStmt->fetch();
                
                if ($project && $project['welcome_message']) {
                    $aisensy->sendWelcomeMessage($lead, $project);
                    logWebhook("Welcome message sent via WhatsApp");
                }
            } catch (Exception $e) {
                logWebhook("Warning: Failed to send welcome message - " . $e->getMessage());
            }
        }
        
        // Auto-enroll in campaigns
        try {
            $campaignService = new CampaignService();
            $campaignService->autoEnrollLead($leadId);
            logWebhook("Lead enrolled in campaigns");
        } catch (Exception $e) {
            logWebhook("Warning: Failed to enroll in campaigns - " . $e->getMessage());
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Thank you! We will contact you soon.',
            'lead_id' => $leadId
        ]);
    } else {
        throw new Exception($result['message'] ?? 'Failed to create lead');
    }
    
} catch (Exception $e) {
    logWebhook("❌ Error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
