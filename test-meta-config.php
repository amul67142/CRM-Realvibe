<?php
/**
 * Test Meta Lead Ads Configuration
 * Quick test script to verify Meta API credentials
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/services/MetaLeadsService.php';

echo "=== Meta Lead Ads Configuration Test ===\n\n";

// Get configured settings
$db = getDatabaseConnection();
$stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'meta_%' ORDER BY setting_key");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

echo "Current Configuration:\n";
echo "- App ID: " . ($settings['meta_app_id'] ?? 'NOT SET') . "\n";
echo "- App Secret: " . (isset($settings['meta_app_secret']) && !empty($settings['meta_app_secret']) ? '***' . substr($settings['meta_app_secret'], -4) : 'NOT SET') . "\n";
echo "- Access Token: " . (isset($settings['meta_access_token']) && !empty($settings['meta_access_token']) ? '***' . substr($settings['meta_access_token'], -10) : 'NOT SET') . "\n";
echo "- Verify Token: " . ($settings['meta_verify_token'] ?? 'NOT SET') . "\n\n";

// Webhook URL
echo "Webhook Configuration:\n";
echo "- Webhook URL: " . rtrim(BASE_URL, '/') . "/api/webhooks/meta-leads\n";
echo "- Verify Token: " . ($settings['meta_verify_token'] ?? 'NOT SET') . "\n\n";

// Test connection if access token is configured
if (!empty($settings['meta_access_token'])) {
    echo "Testing Meta API Connection...\n";
    
    $metaService = new MetaLeadsService();
    $result = $metaService->testConnection();
    
    if ($result['success']) {
        echo "✓ SUCCESS! Connection verified.\n";
        if (isset($result['account'])) {
            echo "  Account ID: " . ($result['account']['id'] ?? 'N/A') . "\n";
            echo "  Account Name: " . ($result['account']['name'] ?? 'N/A') . "\n";
        }
    } else {
        echo "✗ FAILED! " . $result['error'] . "\n";
    }
} else {
    echo "⚠ Access Token not configured. Cannot test connection.\n";
    echo "  Please configure a Page Access Token in Settings → Integrations\n";
}

echo "\n=== Next Steps ===\n";
echo "1. Get a Page Access Token from Graph API Explorer\n";
echo "2. Configure it in Settings → Integrations\n";
echo "3. Set up webhook in Meta Developer Console:\n";
echo "   - URL: " . rtrim(BASE_URL, '/') . "/api/webhooks/meta-leads\n";
echo "   - Verify Token: " . ($settings['meta_verify_token'] ?? 'NOT SET') . "\n";
echo "   - Subscribe to: leadgen events\n";
echo "4. Map Lead Forms to Projects\n";
echo "5. Start capturing leads!\n";
