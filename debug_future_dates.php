<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDatabaseConnection();

// Find leads created in the future
$stmt = $db->query("SELECT id, name, phone, created_at FROM leads WHERE created_at > NOW() ORDER BY created_at DESC");
$futureLeads = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($futureLeads) . " leads with future dates:\n";
foreach ($futureLeads as $lead) {
    echo "[{$lead['id']}] {$lead['name']} ({$lead['phone']}) - {$lead['created_at']}\n";
}
