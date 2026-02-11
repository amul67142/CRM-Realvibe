<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDatabaseConnection();

$stmt = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('admin_notification_enabled', 'admin_phone')");
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

echo "Admin Notification Enabled: " . (($settings['admin_notification_enabled'] ?? '0') == '1' ? "YES" : "NO") . "\n";
echo "Admin Phone: " . ($settings['admin_phone'] ?? "NOT SET") . "\n";
