<?php
/**
 * Test updated webhook with Testing Tool format
 * This simulates what Meta Testing Tool sends
 */

$webhookUrl = 'http://localhost/Realvibe/api/webhooks/meta-leads.php';

// Testing Tool format (no "object" field)
$testingToolData = [
    'entry' => [[
        'id' => '23072081346897',
        'time' => time(),
        'changes' => [[
            'field' => 'leadgen',
            'value' => [
                'ad_id' => null,
                'form_id' => '980881823939901',
                'leadgen_id' => '3366158526681825',
                'created_time' => time(),
                'page_id' => '23072081346897',
                'adgroup_id' => null
            ]
        ]]
    ]]
];

echo "Testing Updated Webhook with Testing Tool Format...\n";
echo "Form ID: 980881823939901\n";
echo "Lead ID: 3366158526681825\n\n";

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testingToolData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "Response: $response\n\n";

if ($httpCode === 200) {
    echo "✅ Webhook accepted Testing Tool format!\n";
    echo "Now it will try to fetch lead data from Meta API...\n";
    echo "Check: C:\\xampp\\htdocs\\Realvibe\\logs\\meta-webhook.log\n";
} else {
    echo "❌ Webhook failed!\n";
}
