<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

try {
    $db = getDatabaseConnection();
    $output = [];

    // 1. Check for specific problematic leads
    $names = ['Sharma ji', 'Sonam Bakshi', 'ewew', 'Divesh Malhotra'];
    $output['specific_leads'] = [];
    
    foreach ($names as $name) {
        $stmt = $db->prepare("SELECT id, name, phone, email, created_at, project_id FROM leads WHERE name LIKE ? ORDER BY id DESC LIMIT 20");
        $stmt->execute(["%$name%"]);
        $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $output['specific_leads'][$name] = $leads;
    }

    // 2. Count total duplicates by phone
    $stmt = $db->query("
        SELECT phone, COUNT(*) as count 
        FROM leads 
        GROUP BY phone 
        HAVING count > 1
        ORDER BY count DESC
        LIMIT 10
    ");
    $output['top_duplicates'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($output, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

