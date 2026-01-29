<?php
/**
 * Simple Meta API Connection Test
 * Tests if Page Access Token works with Meta Graph API
 */

// Get the access token from database
$db = new PDO('mysql:host=localhost;dbname=realvibe', 'root', '');
$stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key='meta_access_token'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$accessToken = $result['setting_value'] ?? '';

if (empty($accessToken)) {
    die("❌ Error: Access token not configured\n");
}

echo "=== Testing Meta API Connection ===\n\n";
echo "Access Token: " . substr($accessToken, 0, 20) . "..." . substr($accessToken, -10) . "\n\n";

// Test API call to /me endpoint
$url = "https://graph.facebook.com/v18.0/me?access_token=" . urlencode($accessToken);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

$data = json_decode($response, true);

if ($httpCode === 200 && isset($data['id'])) {
    echo "✅ SUCCESS! Meta API connection verified!\n";
    echo "Account ID: " . $data['id'] . "\n";
    if (isset($data['name'])) {
        echo "Account Name: " . $data['name'] . "\n";
    }
    echo "\n✅ Your Meta Lead Ads integration is ready!\n";
} else {
    echo "❌ FAILED!\n";
    if (isset($data['error'])) {
        echo "Error: " . $data['error']['message'] . "\n";
        echo "Type: " . $data['error']['type'] . "\n";
        echo "Code: " . $data['error']['code'] . "\n";
    }
}

echo "\n=== Configuration Summary ===\n";
$stmt = $db->query("SELECT setting_key, LEFT(setting_value, 30) as value FROM settings WHERE setting_key LIKE 'meta_%' ORDER BY setting_key");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo $row['setting_key'] . ": " . $row['value'] . "...\n";
}
