<?php
/**
 * AI Sensy API Test - Based on Working Python Script
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/aisensy.php';

echo str_repeat("=", 70) . "\n";
echo "AI SENSY API TEST - PHP Version\n";
echo str_repeat("=", 70) . "\n\n";

// Configuration
$apiKey = AISENSY_API_KEY;
$apiUrl = AISENSY_API_BASE_URL;  // Base URL without /campaign-requests

// Test Data - Exact same structure as Python script
$testData = [
    'apiKey' => $apiKey,
    'campaignName' => 'CRM_Template',
    'destination' => '+919355614889',  // With + prefix
    'userName' => 'Rahul Kumar',
    'templateParams' => [
        'Rahul Kumar',  // {{1}} - Name
        'Thank you for your interest in Pyramid Alban! We\'re excited to help you find your dream home.'  // {{2}} - Message
    ],
    'source' => 'api-test-php',
    'media' => (object)[],  // Empty object
    'buttons' => [],
    'carouselCards' => [],
    'location' => (object)[]
];

echo "Endpoint: $apiUrl\n";
echo "Campaign: {$testData['campaignName']}\n";
echo "Destination: {$testData['destination']}\n";
echo "Name: {$testData['userName']}\n\n";
echo "Sending request...\n\n";

// Send request
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ],
    CURLOPT_POSTFIELDS => json_encode($testData),
    CURLOPT_TIMEOUT => 30
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// Display results
echo "Status Code: $httpCode\n\n";
echo "Response Body:\n";
echo str_repeat("=", 70) . "\n";

if ($curlError) {
    echo "CURL Error: $curlError\n";
} else {
    // Try to pretty print JSON
    $responseData = json_decode($response);
    if ($responseData) {
        echo json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    } else {
        echo $response . "\n";
    }
}

echo str_repeat("=", 70) . "\n\n";

if ($httpCode == 200 || $httpCode == 201) {
    echo "✅ SUCCESS! Message sent successfully!\n";
} else {
    echo "❌ FAILED! Status code: $httpCode\n";
}

echo "\n" . str_repeat("=", 70) . "\n";
