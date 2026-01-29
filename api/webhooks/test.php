<?php
// Simple test webhook to verify URL routing works
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Webhook test - " . date('Y-m-d H:i:s') . "\n";
echo "Request method: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "Query params: " . print_r($_GET, true) . "\n";

// Test verification request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mode = $_GET['hub_mode'] ?? '';
    $token = $_GET['hub_verify_token'] ?? '';
    $challenge = $_GET['hub_challenge'] ?? '';
    
    echo "Mode: $mode\n";
    echo "Token: $token\n";
    echo "Challenge: $challenge\n";
    
    if ($mode === 'subscribe' && $token === 'realvibe_webhook_2026') {
        echo "\n\n";
        echo $challenge;
    } else {
        echo "Verification failed";
    }
}
