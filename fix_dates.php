<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDatabaseConnection();

// Set target date to yesterday (Feb 10, 2026) at 10 AM
$targetDate = '2026-02-10 10:00:00';

// Current time for reference
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
echo "Target Date: $targetDate\n\n";

// Update all leads created in the future
$sql = "UPDATE leads SET created_at = :target_date WHERE created_at > NOW()";
$stmt = $db->prepare($sql);
$stmt->execute(['target_date' => $targetDate]);

echo "Updated " . $stmt->rowCount() . " leads to $targetDate.\n";
