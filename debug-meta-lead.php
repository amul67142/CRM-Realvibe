<?php
/**
 * Debug Script: Check what happened with the last Meta test lead
 * This will show us exactly what error occurred
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "=== Meta Lead Capture Diagnostic ===\n\n";

// 1. Check Meta credentials
$db = getDatabaseConnection();

$stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'meta_%'");
$stmt->execute();
$settings = $stmt->fetchAll();

echo "1. Meta Credentials:\n";
foreach ($settings as $setting) {
    $value = $setting['setting_value'];
    if (strlen($value) > 20) {
        $value = substr($value, 0, 20) . '... (' . strlen($value) . ' chars)';
    }
    echo "   {$setting['setting_key']}: $value\n";
}

// 2. Check form mapping
$stmt = $db->prepare("SELECT * FROM meta_lead_forms WHERE form_id = '980881823939901'");
$stmt->execute();
$mapping = $stmt->fetch();

echo "\n2. Form Mapping:\n";
if ($mapping) {
    echo "   ✅ Form mapped to Project ID: {$mapping['project_id']}\n";
    echo "   Form Name: {$mapping['form_name']}\n";
    echo "   Total Leads: {$mapping['total_leads']}\n";
} else {
    echo "   ❌ No mapping found!\n";
}

// 3. Try to fetch a test lead from Meta API
echo "\n3. Testing Meta API Access:\n";

$stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'meta_access_token'");
$stmt->execute();
$result = $stmt->fetch();
$accessToken = $result ? $result['setting_value'] : null;

if (!$accessToken) {
    echo "   ❌ No access token configured!\n";
} else {
    echo "   ✅ Access token exists\n";
    
    // Try to fetch the test lead ID from Meta
    $testLeadId = '3366158526681825'; // From the Meta testing tool
    $url = "https://graph.facebook.com/v18.0/{$testLeadId}?access_token={$accessToken}";
    
    echo "   Fetching lead ID: $testLeadId\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "   HTTP Status: $httpCode\n";
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        echo "   ✅ Successfully fetched lead data!\n";
        echo "   Lead Data:\n";
        print_r($data);
    } else {
        echo "   ❌ Failed to fetch lead!\n";
        echo "   Response: $response\n";
    }
}

// 4. Check recent leads in database
echo "\n4. Recent Leads in Database:\n";
$stmt = $db->prepare("SELECT COUNT(*) as total FROM leads");
$stmt->execute();
$count = $stmt->fetch();
echo "   Total leads: {$count['total']}\n";

$stmt = $db->prepare("SELECT id, name, source, created_at FROM leads ORDER BY created_at DESC LIMIT 3");
$stmt->execute();
$leads = $stmt->fetchAll();
echo "   Last 3 leads:\n";
foreach ($leads as $lead) {
    echo "     - {$lead['name']} ({$lead['source']}) - {$lead['created_at']}\n";
}

echo "\n=== End of Diagnostic ===\n";
