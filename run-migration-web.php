<?php
/**
 * Web-Based Database Migration Runner
 * Access at: http://localhost/Realvibe/run-migration-web.php
 */

// Handle AJAX migration request FIRST (before anything else)
if (isset($_GET['action']) && $_GET['action'] === 'migrate') {
    // Start output buffering to catch any errors
    ob_start();
    
    // Suppress PHP errors from displaying (we'll catch them)
    ini_set('display_errors', '0');
    error_reporting(0);
    
    header('Content-Type: application/json');
    
    $logs = [];
    $successful = 0;
    $errors = 0;
    $skipped = 0;
    
    try {
        // Load config
        require_once __DIR__ . '/config/config.php';
        
        $db = getDatabaseConnection();
        $logs[] = ['message' => 'Database connected', 'type' => 'success'];
        
        $sqlFile = __DIR__ . '/database/migrations/add_nurturing_whatsapp_fields.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception("Migration file not found: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        $logs[] = ['message' => 'Migration file loaded', 'type' => 'success'];
        
        // Split by semicolon and filter out comments
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($s) {
                $s = trim($s);
                return !empty($s) && substr($s, 0, 2) !== '--';
            }
        );
        
        $logs[] = ['message' => 'Found ' . count($statements) . ' SQL statements', 'type' => 'info'];
        
        foreach ($statements as $stmt) {
            try {
                $db->exec($stmt);
                $preview = substr(str_replace(["\r", "\n"], ' ', $stmt), 0, 70) . '...';
                $logs[] = ['message' => '✓ ' . $preview, 'type' => 'success'];
                $successful++;
            } catch (PDOException $e) {
                // Ignore "duplicate column" and "table exists" errors
                if (strpos($e->getMessage(), 'Duplicate column') !== false ||
                    strpos($e->getMessage(), 'already exists') !== false) {
                    $preview = substr(str_replace(["\r", "\n"], ' ', $stmt), 0, 50);
                    $logs[] = ['message' => '⚠ Skipped (exists): ' . $preview, 'type' => 'warning'];
                    $skipped++;
                } else {
                    $logs[] = ['message' => '✗ ERROR: ' . $e->getMessage(), 'type' => 'error'];
                    $errors++;
                }
            }
        }
        
        // Create completion marker
        file_put_contents(__DIR__ . '/.migration-complete', date('Y-m-d H:i:s'));
        
        // Clear any buffered output
        ob_end_clean();
        
        echo json_encode([
            'success' => true,
            'logs' => $logs,
            'successful' => $successful,
            'errors' => $errors,
            'skipped' => $skipped
        ]);
        
    } catch (Exception $e) {
        // Clear any buffered output
        ob_end_clean();
        
        echo json_encode([
            'success' => false,
            'logs' => array_merge($logs, [
                ['message' => 'FATAL ERROR: ' . $e->getMessage(), 'type' => 'error']
            ]),
            'successful' => $successful,
            'errors' => $errors + 1,
            'skipped' => $skipped
        ]);
    }
    
    exit; // Stop here - don't output HTML
}

// Load config for HTML page
require_once __DIR__ . '/config/config.php';

// Simple security - delete this file after running
if (file_exists(__DIR__ . '/.migration-complete')) {
    die('<h1>✅ Migration already completed!</h1><p>Delete <code>.migration-complete</code> file to run again.</p>');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration - Realvibe CRM</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        .log {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            max-height: 500px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
        }
        .log-line {
            margin: 5px 0;
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .info { color: #17a2b8; }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
        }
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .summary {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📦 Database Migration</h1>
        <p class="subtitle">Nurturing Campaign WhatsApp Web Integration</p>
        
        <button onclick="runMigration()" id="runBtn">▶ Run Migration</button>
        
        <div class="log" id="log" style="display: none;"></div>
        <div class="summary" id="summary" style="display: none;"></div>
    </div>

    <script>
        function addLog(message, type = 'info') {
            const log = document.getElementById('log');
            log.style.display = 'block';
            
            const line = document.createElement('div');
            line.className = 'log-line ' + type;
            line.textContent = message;
            log.appendChild(line);
            log.scrollTop = log.scrollHeight;
        }

        async function runMigration() {
            const btn = document.getElementById('runBtn');
            btn.disabled = true;
            btn.textContent = '⏳ Running...';
            
            addLog('Starting migration...', 'info');
            
            try {
                const response = await fetch('?action=migrate', {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                data.logs.forEach(log => {
                    addLog(log.message, log.type);
                });
                
                if (data.success) {
                    document.getElementById('summary').innerHTML = `
                        <strong>✅ Migration Complete!</strong><br>
                        Successful: ${data.successful}<br>
                        Errors: ${data.errors}<br>
                        Skipped: ${data.skipped}
                    `;
                    document.getElementById('summary').style.display = 'block';
                    btn.textContent = '✅ Migration Complete!';
                } else {
                    btn.textContent = '❌ Migration Failed';
                    btn.disabled = false;
                }
                
            } catch (error) {
                addLog('FATAL ERROR: ' + error.message, 'error');
                btn.textContent = '❌ Error - Try Again';
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
