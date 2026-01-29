<?php
/**
 * Test script to send a welcome message via Meta WhatsApp
 */

require_once __DIR__ . '/config/init.php';
require_once __DIR__ . '/services/MetaWhatsAppService.php';

// Phone number to send to
$phone = '919355614889';

// Welcome message
$message = "Hello! 👋\n\n" .
           "Welcome to *RealVibe*!\n\n" .
           "Thank you for your interest in our premium properties. " .
           "We're excited to help you find your dream home! 🏡\n\n" .
           "Our team will get back to you shortly with more details about our exclusive projects.\n\n" .
           "Feel free to ask any questions!\n\n" .
           "Best regards,\n" .
           "_RealVibe Team_";

// Initialize Meta WhatsApp service
$meta = new MetaWhatsAppService();

// Send the message
echo "Sending welcome message to: $phone\n\n";
$result = $meta->sendTextMessage($phone, $message);

// Display result
echo "Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT);
echo "\n\n";

if ($result['success']) {
    echo "✅ SUCCESS! Welcome message sent!\n";
    echo "Message ID: " . ($result['data']['messages'][0]['id'] ?? 'N/A') . "\n";
} else {
    echo "❌ FAILED!\n";
    echo "Error: " . ($result['error'] ?? 'Unknown error') . "\n";
    if (isset($result['message'])) {
        echo "Message: " . $result['message'] . "\n";
    }
}
