<?php
/**
 * Contact Form 7 Webhook Handler
 * Receives form submissions from WordPress CF7 and creates leads in CRM
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow WordPress to send requests
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Lead.php';

// Log file
$logFile = __DIR__ . '/../../logs/cf7-webhook.log';

// Ensure log directory exists
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

try {
    // Get raw POST data
    $rawData = file_get_contents('php://input');
    file_put_contents($logFile, "\n=== [" . date('Y-m-d H:i:s') . "] POST /api/webhooks/cf7 ===\n", FILE_APPEND);
    file_put_contents($logFile, "Raw data: $rawData\n", FILE_APPEND);
    
    // Parse JSON data
    $data = json_decode($rawData, true);
    
    // If not JSON, try parsing as form data
    if (!$data) {
        parse_str($rawData, $data);
    }
    
    // Also check $_POST
    if (empty($data)) {
        $data = $_POST;
    }
    
    file_put_contents($logFile, "Parsed data: " . json_encode($data) . "\n", FILE_APPEND);
    
    if (empty($data)) {
        throw new Exception('No data received');
    }
    
    // Extract fields (CF7 field names are like: your-name, your-email, your-phone, etc.)
    $name = $data['your-name'] ?? $data['name'] ?? $data['full-name'] ?? 'Unknown';
    $email = $data['your-email'] ?? $data['email'] ?? null;
    $phone = $data['your-phone'] ?? $data['phone'] ?? $data['mobile'] ?? null;
    $message = $data['your-message'] ?? $data['message'] ?? '';
    
    // Get project_id from query parameter or default to project 1
    $projectId = $_GET['project_id'] ?? 1;
    
    file_put_contents($logFile, "Extracted - Name: $name, Email: $email, Phone: $phone, Project: $projectId\n", FILE_APPEND);
    
    // Validate required fields
    if (!$name && !$email && !$phone) {
        throw new Exception('Missing required fields (name, email, or phone)');
    }
    
    // Clean phone number
    if ($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
    }
    
    // Build notes from all fields
    $notes = "Source: WordPress CF7\n\n";
    foreach ($data as $key => $value) {
        if (!in_array($key, ['your-name', 'your-email', 'your-phone', 'name', 'email', 'phone']) && $value) {
            $notes .= ucfirst(str_replace(['-', '_'], ' ', $key)) . ": $value\n";
        }
    }
    
    // Create lead
    $db = getDatabaseConnection();
    $leadModel = new Lead($db);
    
    $leadData = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'project_id' => $projectId,
        'source' => 'wordpress_cf7',
        'status' => 'new',
        'notes' => $notes
    ];
    
    $leadId = $leadModel->create($leadData);
    
    if ($leadId) {
        file_put_contents($logFile, "✅ Lead created successfully! ID: $leadId\n", FILE_APPEND);
        
        // TODO: Trigger WhatsApp notification if configured
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Lead captured successfully',
            'lead_id' => $leadId
        ]);
    } else {
        throw new Exception('Failed to create lead');
    }
    
} catch (Exception $e) {
    file_put_contents($logFile, "❌ Error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
