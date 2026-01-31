<?php
/**
 * Universal Form Webhook - Simplified Version
 * Basic lead capture without extra dependencies
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Suppress errors to prevent JSON corruption
ini_set('display_errors', 0);
error_reporting(0);

// Log file
$logFile = __DIR__ . '/../../logs/universal-webhook.log';
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

function logWebhook($message) {
    global $logFile;
    file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

try {
    logWebhook("=== New webhook request ===");
    logWebhook("Query string: " . ($_SERVER['QUERY_STRING'] ?? 'none'));
    
    // Simple require - no complex dependencies
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    
    // Start with GET parameters (from URL)
    $data = $_GET;
    
    // Get POST data
    $rawData = file_get_contents('php://input');
    logWebhook("Raw data: " . $rawData);
    
    $postData = json_decode($rawData, true);
    
    if (!$postData) {
        parse_str($rawData, $postData);
    }
    
    if (!empty($_POST)) {
        logWebhook("Merging _POST: " . json_encode($_POST));
        $postData = array_merge($postData ?: [], $_POST);
    }
    
    // Merge POST data with GET data (POST overwrites GET except for project_id and source)
    if ($postData) {
        $data = array_merge($postData, $data);
    }
    
    logWebhook("GET params: " . json_encode($_GET));
    
    logWebhook("Final parsed data: " . json_encode($data));
    
    if (empty($data)) {
        throw new Exception('No data received');
    }
    
    // Extract fields (handle ALL possible variations)
    $name = $data['name'] ?? $data['your-name'] ?? $data['full-name'] ?? $data['fullname'] ?? 'Unknown';
    $email = $data['email'] ?? $data['your-email'] ?? $data['user-email'] ?? null;
    $phone = $data['phone'] ?? $data['your-phone'] ?? $data['mobile'] ?? $data['telephone'] ?? null;
    $message = $data['message'] ?? $data['your-message'] ?? $data['your-comment'] ?? '';
    $projectId = $data['project_id'] ?? 1;
    $source = $data['source'] ?? 'landing_page';
    
    logWebhook("Extracted - Name: '$name', Email: '$email', Phone: '$phone', Project: $projectId, Source: '$source'");
    
    // Validate
    if (!$email && !$phone) {
        throw new Exception('Either email or phone is required');
    }
    
    // Clean phone
    if ($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (!str_starts_with($phone, '+') && !str_starts_with($phone, '91')) {
            $phone = '91' . $phone;
        }
    }
    
    // Build notes
    $notes = "Source: $source\nReceived: " . date('Y-m-d H:i:s') . "\n";
    if ($message) {
        $notes .= "\nMessage: $message";
    }
    
    // Direct database insert - no model dependencies
    $db = getDatabaseConnection();
    
    // Check for duplicate first (same phone + project)
    if ($phone) {
        $checkStmt = $db->prepare("SELECT id FROM leads WHERE phone = ? AND project_id = ?");
        $checkStmt->execute([$phone, $projectId]);
        $existing = $checkStmt->fetch();
        
        if ($existing) {
            // Lead already exists - return success anyway
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Thank you! We already have your details.',
                'lead_id' => $existing['id'],
                'note' => 'duplicate'
            ]);
            exit;
        }
    }
    
    $stmt = $db->prepare("
        INSERT INTO leads (project_id, name, phone, email, source, status, is_subscribed, notes, created_at)
        VALUES (?, ?, ?, ?, ?, 'new', 1, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $projectId,
        trim($name),
        $phone,
        $email,
        $source,
        trim($notes)
    ]);
    
    if ($result) {
        $leadId = $db->lastInsertId();
        logWebhook("✅ Lead created successfully! ID: $leadId");
        
        // Enroll in AiSensy campaign if phone is available
        if ($phone) {
            try {
                // Check if AiSensy service exists
                if (file_exists(__DIR__ . '/../../services/AiSensyService.php')) {
                    require_once __DIR__ . '/../../services/AiSensyService.php';
                    require_once __DIR__ . '/../../config/aisensy.php';
                    
                    $aisensy = new AiSensyService();
                    
                    // Get lead details
                    $leadStmt = $db->prepare("SELECT * FROM leads WHERE id = ?");
                    $leadStmt->execute([$leadId]);
                    $lead = $leadStmt->fetch();
                    
                    // Use LeadNotificationService (same as manual lead creation)
                    require_once __DIR__ . '/../../services/LeadNotificationService.php';
                    
                    // Get project details for notification
                    $projectStmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
                    $projectStmt->execute([$projectId]);
                    $project = $projectStmt->fetch();
                    
                    if ($project) {
                        $notificationService = new LeadNotificationService();
                        $result = $notificationService->sendNewLeadAlert($lead, $project);
                        
                        if ($result['success']) {
                            logWebhook("✅ Lead notification sent via LeadNotificationService");
                        } else {
                            logWebhook("⚠️ Failed to send notification: " . ($result['message'] ?? 'Unknown error'));
                        }
                    } else {
                        logWebhook("⚠️ Project not found for ID: $projectId");
                    }
                }
            } catch (Exception $e) {
                logWebhook("⚠️ Warning: Failed to enroll in AiSensy campaign - " . $e->getMessage());
            }
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Thank you! We will contact you soon.',
            'lead_id' => $leadId
        ]);
    } else {
        throw new Exception('Failed to create lead');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
