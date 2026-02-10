<?php
/**
 * Run Database Migration
 * Execute: php database/run-migration.php
 */

require_once __DIR__ . '/../config/config.php';

try {
    $db = getDatabaseConnection();
    echo "📦 Running nurturing campaign WhatsApp Web migration...\n\n";
    
    $sql = file_get_contents(__DIR__ . '/migrations/add_nurturing_whatsapp_fields.sql');
    
    // Split by semicolon and filter out comments
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($s) {
            $s = trim($s);
            return !empty($s) && substr($s, 0, 2) !== '--';
        }
    );
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $stmt) {
        try {
            $db->exec($stmt);
            $preview = substr($stmt, 0, 60) . '...';
            echo "✓ OK: $preview\n";
            $success++;
        } catch (PDOException $e) {
            // Ignore "duplicate column" errors
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "⚠ Skipped (already exists): " . substr($stmt, 0, 40) . "...\n";
            } else {
                echo "✗ ERROR: " . $e->getMessage() . "\n";
                $errors++;
            }
        }
    }
    
    echo "\n" . str_repeat('=', 60) . "\n";
    echo "✅ Migration complete!\n";
    echo "   Successful: $success\n";
    echo "   Errors: $errors\n";
    echo str_repeat('=', 60) . "\n";
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
