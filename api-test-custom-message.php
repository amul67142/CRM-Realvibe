<?php
/**
 * Test API Endpoint - Send Custom WhatsApp Message
 * Compatible with test-custom-ui.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/aisensy.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

$phone = $input['phone'] ?? null;
$name = $input['name'] ?? null;
$message = $input['message'] ?? null;

if (!$phone || !$name || !$message) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Phone, name, and message are required'
    ]);
    exit;
}

try {
    //Format phone
    $formattedPhone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($formattedPhone) == 10) {
        $formattedPhone = '91' . $formattedPhone;
    }
    if (!str_starts_with($formattedPhone, '+')) {
        $formattedPhone = '+' . $formattedPhone;
    }
    
    // Prepare payload - matching working Python script
    $payload = [
        'apiKey' => AISENSY_API_KEY,
        'campaignName' => 'CRM_Template',
        'destination' => $formattedPhone,
        'userName' => $name,
        'templateParams' => [$name, $message],
        'source' => 'crm_test_ui',
        'media' => (object)[],
        'buttons' => [],
        'carouselCards' => [],
        'location' => (object)[]
    ];
    
    // Send request
    $ch = curl_init(AISENSY_API_BASE_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 30
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception("CURL Error: $curlError");
    }
    
    if ($httpCode == 200 || $httpCode == 201) {
        echo json_encode([
            'success' => true,
            'message' => 'Message sent successfully!',
            'response' => json_decode($response, true)
        ]);
    } else {
        http_response_code($httpCode);
        echo json_encode([
            'success' => false,
            'error' => "HTTP Error: $httpCode",
            'response' => $response
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
