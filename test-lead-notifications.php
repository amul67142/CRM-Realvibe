<?php
/**
 * Test Lead Notification System
 * Creates a test lead and triggers notifications
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/models/Lead.php';
require_once __DIR__ . '/models/Project.php';
require_once __DIR__ . '/services/LeadNotificationService.php';

echo "===========================================\n";
echo "Lead Notification Test\n";
echo "===========================================\n\n";

// Get database connection
$db = getDatabaseConnection();

// Check admin settings
echo "1. Checking Admin Settings...\n";
$stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_name', 'admin_phone', 'admin_notification_enabled')");
$stmt->execute();
$adminSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

echo "   Admin Name: " . ($adminSettings['admin_name'] ?? 'Not set') . "\n";
echo "   Admin Phone: " . ($adminSettings['admin_phone'] ?? 'Not set') . "\n";
echo "   Notifications Enabled: " . ($adminSettings['admin_notification_enabled'] ?? 'Not set') . "\n\n";

if (empty($adminSettings['admin_phone'])) {
    echo "   ⚠ WARNING: Admin phone not configured!\n";
    echo "   Go to Settings → Admin Profile to add your phone number.\n\n";
}

// Get first project with client
echo "2. Finding Project with Client...\n";
$stmt = $db->prepare("
    SELECT p.*, c.name as client_name, c.phone as client_phone
    FROM projects p
    LEFT JOIN clients c ON p.client_id = c.id
    WHERE p.is_active = 1
    LIMIT 1
");
$stmt->execute();
$project = $stmt->fetch();

if (!$project) {
    echo "   ❌ No active projects found!\n";
    exit;
}

echo "   Project: {$project['project_name']}\n";
echo "   Client: {$project['client_name']}\n";
echo "   Client Phone: " . ($project['client_phone'] ?? 'Not set') . "\n\n";

if (empty($project['client_phone'])) {
    echo "   ⚠ WARNING: Client phone not configured!\n\n";
}

// Create test lead
echo "3. Creating Test Lead...\n";
$leadModel = new Lead();

$testLeadData = [
    'project_id' => $project['id'],
    'name' => 'Test Lead ' . date('H:i:s'),
    'phone' => '9876543210',
    'email' => 'testlead@example.com',
    'source' => 'manual_test',
    'status' => 'new',
    'is_subscribed' => 1,
    'notes' => 'This is a test lead created to verify notification system'
];

$result = $leadModel->create($testLeadData);

if (!$result['success']) {
    echo "   ❌ Failed to create lead: " . ($result['message'] ?? 'Unknown error') . "\n";
    exit;
}

$leadId = $result['id'];
echo "   ✅ Lead created with ID: $leadId\n\n";

// Get the created lead
$lead = $leadModel->getById($leadId);

// Send notifications
echo "4. Sending Notifications...\n";
$notificationService = new LeadNotificationService();
$notificationResults = $notificationService->sendNewLeadAlert($lead, $project);

if ($notificationResults['success']) {
    echo "   ✅ Notification service executed\n\n";
    
    echo "5. Notification Results:\n";
    foreach ($notificationResults['results'] as $result) {
        $status = $result['success'] ? '✅' : '❌';
        echo "   $status {$result['recipient']} ({$result['phone']})\n";
        if (!empty($result['message'])) {
            echo "      Message: {$result['message']}\n";
        }
    }
} else {
    echo "   ❌ Notification service failed: " . ($notificationResults['message'] ?? 'Unknown error') . "\n";
}

echo "\n===========================================\n";
echo "Test Complete!\n";
echo "===========================================\n\n";

echo "Check your WhatsApp on:\n";
if (!empty($adminSettings['admin_phone'])) {
    echo "  - Admin: {$adminSettings['admin_phone']}\n";
}
if (!empty($project['client_phone'])) {
    echo "  - Client: {$project['client_phone']}\n";
}

echo "\nCheck notification logs at:\n";
echo "  logs/lead-notifications.log\n";
