<?php
/**
 * Debug Script - Show Exact Request/Response
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/aisensy.php';

echo "<pre style='background: #000; color: #0f0; padding: 20px; font-family: monospace;'>";
echo str_repeat("=", 70) . "\n";
echo "AISENSY API DEBUG - DETAILED\n";
echo str_repeat("=", 70) . "\n\n";

// Configuration
$apiKey = AISENSY_API_KEY;
$apiUrl = AISENSY_API_BASE_URL;

echo "API URL: $apiUrl\n";
echo "API Key: " . substr($apiKey, 0, 20) . "...\n\n";

// Test Data
$testData = [
    'apiKey' => $apiKey,
    'campaignName' => 'CRM_Template',
    'destination' => '+919355614889',
    'userName' => 'Rahul Kumar',
    'templateParams' => [
        'Rahul Kumar',
        'Thank you for your interest in Pyramid Alban! We are excited to help you find your dream home.'
    ],
    'source' => 'api-test-debug',
    'media' => new stdClass(),  // Empty object
    'buttons' => [],
    'carouselCards' => [],
    'location' => new stdClass()
];

echo "REQUEST PAYLOAD:\n";
echo str_repeat("-", 70) . "\n";
$jsonPayload = json_encode($testData, JSON_PRETTY_PRINT);
echo $jsonPayload . "\n";
echo str_repeat("-", 70) . "\n\n";

echo "Sending request to AiSensy...\n\n";

// Send request with verbose output
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => $jsonPayload,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_VERBOSE => true
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlInfo = curl_getinfo($ch);
$curlError = curl_error($ch);
curl_close($ch);

echo "RESPONSE:\n";
echo str_repeat("=", 70) . "\n";
echo "HTTP Code: $httpCode\n\n";

if ($curlError) {
    echo "CURL Error: $curlError\n\n";
}

echo "Response Body:\n";
echo str_repeat("-", 70) . "\n";
$responseData = json_decode($response);
if ($responseData) {
    echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
} else {
    echo $response . "\n";
}
echo str_repeat("-", 70) . "\n\n";

// Status
if ($httpCode == 200 || $httpCode == 201) {
    echo "✅ ✅ ✅ SUCCESS! ✅ ✅ ✅\n\n";
    echo "Check WhatsApp on +919355614889\n";
} else {
    echo "❌ FAILED\n\n";
    
    // Common issues
    echo "TROUBLESHOOTING:\n";
    echo str_repeat("-", 70) . "\n";
    
    if ($httpCode == 401) {
        echo "• 401 Unauthorized - API key issue\n";
        echo "  → Check if API key is correct in config/aisensy.php\n";
    } elseif ($httpCode == 404) {
        echo "• 404 Not Found - Campaign issue\n";
        echo "  → Check if campaign 'CRM_Template' exists and is LIVE\n";
        echo "  → Verify campaign name spelling\n";
    } elseif ($httpCode == 400) {
        echo "• 400 Bad Request - Payload issue\n";
        echo "  → Check template parameters\n";
        echo "  → Verify phone number format\n";
    } else {
        echo "• Unexpected error code: $httpCode\n";
    }
}

echo "\n" . str_repeat("=", 70) . "\n";
echo "DEBUG INFO:\n";
echo str_repeat("-", 70) . "\n";
echo "Total Time: " . $curlInfo['total_time'] . "s\n";
echo "Content Type: " . ($curlInfo['content_type'] ?? 'N/A') . "\n";
echo str_repeat("=", 70) . "\n";

echo "</pre>";
