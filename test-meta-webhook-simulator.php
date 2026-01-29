<?php
/**
 * Simulate Meta sending lead data to webhook
 * This tests if your webhook processing works correctly
 */

$webhookUrl = 'http://localhost/Realvibe/api/webhooks/meta-leads.php';

// Sample webhook payload from Meta
$sampleData = [
    'entry' => [[
        'id' => '123456789',
        'time' => time(),
        'changes' => [[
            'field' => 'leadgen',
            'value' => [
                'ad_id' => '123456',
                'form_id' => '980881823939901',  // Your actual form ID
                'leadgen_id' => 'TEST_LEAD_' . time(),
                'created_time' => time(),
                'page_id' => '123456789',
                'adgroup_id' => '123456'
            ]
        ]]
    ]]
];

echo "Simulating Meta webhook call...\n";
echo "Form ID: 980881823939901\n";
echo "Target: $webhookUrl\n\n";

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sampleData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode === 200) {
    echo "✅ Webhook processed successfully!\n";
    echo "Check database for test lead with leadgen_id starting with 'TEST_LEAD_'\n";
} else {
    echo "❌ Webhook failed!\n";
    echo "Check logs: C:\\xampp\\htdocs\\Realvibe\\logs\\meta-webhook.log\n";
}
