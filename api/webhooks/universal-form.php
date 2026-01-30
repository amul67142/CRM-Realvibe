<?php
/**
 * Universal Form Webhook - Simplified Version
 * Basic lead capture without extra dependencies
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Suppress errors to prevent JSON corruption
ini_set('display_errors', 0);
error_reporting(0);

try {
    // Simple require - no complex dependencies
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../config/database.php';
    
    // Get POST data
    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    
    if (!$data) {
        parse_str($rawData, $data);
    }
    
    if (!empty($_POST)) {
        $data = array_merge($data, $_POST);
    }
    
    if (!empty($_GET)) {
        $data = array_merge($data, $_GET);
    }
    
    if (empty($data)) {
        throw new Exception('No data received');
    }
    
    // Extract fields (handle all variations)
    $name = $data['name'] ?? $data['your-name'] ?? $data['full-name'] ?? 'Unknown';
    $email = $data['email'] ?? $data['your-email'] ?? null;
    $phone = $data['phone'] ?? $data['your-phone'] ?? $data['mobile'] ?? null;
    $message = $data['message'] ?? $data['your-message'] ?? '';
    $projectId = $data['project_id'] ?? $_GET['project_id'] ?? 1;
    $source = $data['source'] ?? $_GET['source'] ?? 'website_form';
    
    // Validate
    if (!$email && !$phone) {
        throw new Exception('Either email or phone is required');
    }
    
    // Clean phone
    if ($phone) {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (!str_starts_with($phone, '+') && !str_starts_with($phone, '91')) {
            $phone = '91' . $phone;
        }
    }
    
    // Build notes
    $notes = "Source: $source\nReceived: " . date('Y-m-d H:i:s') . "\n";
    if ($message) {
        $notes .= "\nMessage: $message";
    }
    
    // Direct database insert - no model dependencies
    $db = getDatabaseConnection();
    
    $stmt = $db->prepare("
        INSERT INTO leads (project_id, name, phone, email, source, status, is_subscribed, notes, created_at)
        VALUES (?, ?, ?, ?, ?, 'new', 1, ?, NOW())
    ");
    
    $result = $stmt->execute([
        $projectId,
        trim($name),
        $phone,
        $email,
        $source,
        trim($notes)
    ]);
    
    if ($result) {
        $leadId = $db->lastInsertId();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Thank you! We will contact you soon.',
            'lead_id' => $leadId
        ]);
    } else {
        throw new Exception('Failed to create lead');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
