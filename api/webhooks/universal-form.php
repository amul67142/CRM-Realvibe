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
                    
                    // Enroll in your AiSensy campaign
                    // This campaign handles: welcome message, admin notification, follow-ups
                    $campaignName = 'New_Leads_Reminder'; // Your AiSensy campaign name
                    
                    // Prepare data for AiSensy API
                    $aiSensyData = [
                        'apiKey' => AISENSY_API_KEY,
                        'campaignName' => $campaignName,
                        'destination' => $aisensy->formatPhoneNumber($phone),
                        'userName' => $name,
                        'templateParams' => [
                            $name,                    // {{1}} - Name
                            $source,                  // {{2}} - Source
                            $name,                    // {{3}} - Name again
                            $email ?? 'Not provided', // {{4}} - Email
                            $phone,                   // {{5}} - Phone
                            $phone                    // {{6}} - WhatsApp (same as phone)
                        ],
                        'source' => 'realvibe-crm'
                    ];
                    
                    // Make direct API call
                    $ch = curl_init(AISENSY_API_BASE_URL);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($aiSensyData));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json',
                        'Accept: application/json'
                    ]);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    
                    $apiResponse = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    logWebhook("AiSensy API request: " . json_encode($aiSensyData));
                    logWebhook("AiSensy API response: " . $apiResponse . " (HTTP $httpCode)");
                    
                    if ($httpCode >= 200 && $httpCode < 300) {
                        logWebhook("✅ Lead enrolled in AiSensy campaign: $campaignName");
                    } else {
                        logWebhook("⚠️ Failed to enroll in campaign. HTTP Code: $httpCode");
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
