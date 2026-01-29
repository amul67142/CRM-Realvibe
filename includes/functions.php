<?php
/**
 * Core Helper Functions
 * Utility functions used throughout the application
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT id, username, email, full_name, role FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Require login - redirect to login page if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $currentUri = $_SERVER['REQUEST_URI'];
        $_SESSION['redirect_after_login'] = $currentUri;
        redirect('login');
    }
}

/**
 * Check if user has required permission/role
 */
function hasPermission($requiredRole) {
    $user = getCurrentUser();
    if (!$user) {
        return false;
    }
    
    $roleHierarchy = ['agent' => 1, 'manager' => 2, 'admin' => 3];
    $userRole = $roleHierarchy[$user['role']] ?? 0;
    $required = $roleHierarchy[$requiredRole] ?? 0;
    
    return $userRole >= $required;
}

/**
 * Sanitize input data
 */
function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Format phone number to AiSensy format (919876543210)
 */
function formatPhoneNumber($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // If starts with +, remove it (already removed above)
    // If starts with 0, remove it
    if (substr($phone, 0, 1) === '0') {
        $phone = substr($phone, 1);
    }
    
    // If doesn't start with country code, add default
    if (strlen($phone) == 10) {
        $phone = DEFAULT_COUNTRY_CODE . $phone;
    }
    
    return $phone;
}

/**
 * Format date for display
 */
function formatDate($date, $format = 'M d, Y h:i A') {
    if (empty($date) || $date === '0000-00-00 00:00:00') {
        return '-';
    }
    return date($format, strtotime($date));
}

/**
 * Upload file with security checks
 */
function uploadFile($file, $destination = 'media', $allowedTypes = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'No file uploaded or upload error'];
    }
    
    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds maximum limit'];
    }
    
    // Set allowed types if not provided
    if ($allowedTypes === null) {
        $allowedTypes = array_merge(ALLOWED_IMAGE_TYPES, ALLOWED_DOCUMENT_TYPES, ALLOWED_VIDEO_TYPES);
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    // Determine destination path
    $destinationPath = ($destination === 'media') ? MEDIA_PATH : DOCUMENT_PATH;
    $fullPath = $destinationPath . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $fullPath)) {
        $url = ($destination === 'media') ? MEDIA_URL : DOCUMENT_URL;
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $fullPath,
            'url' => $url . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Generate CSRF token
 */
function generateToken() {
    // Reuse existing token if it's still valid
    if (isset($_SESSION['csrf_token']) && isset($_SESSION['csrf_token_time'])) {
        // Check if token hasn't expired
        if (time() - $_SESSION['csrf_token_time'] <= CSRF_TOKEN_EXPIRY) {
            return $_SESSION['csrf_token'];
        }
    }
    
    // Generate new token if none exists or expired
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

/**
 * Verify CSRF token
 */
function verifyToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check token expiry
    if (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRY) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect with flash message
 */
function redirect($url, $message = null, $type = 'info') {
    if ($message !== null) {
        setFlashMessage($message, $type);
    }
    
    // Check if URL already includes base URL
    if (strpos($url, 'http') === 0) {
        header('Location: ' . $url);
    } else {
        header('Location: ' . url($url));
    }
    exit;
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = [
        'message' => $message,
        'type' => $type // success, error, warning, info
    ];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $flash = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $flash;
    }
    return null;
}

/**
 * Log user activity
 */
function logActivity($action, $entityType = null, $entityId = null, $description = null) {
    $db = getDatabaseConnection();
    $userId = $_SESSION['user_id'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $db->prepare("
        INSERT INTO activity_log (user_id, action, entity_type, entity_id, description, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([$userId, $action, $entityType, $entityId, $description, $ipAddress, $userAgent]);
}

/**
 * Create slug from string
 */
function slugify($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Truncate text with ellipsis
 */
function truncate($text, $length = 100, $ellipsis = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $ellipsis;
}

/**
 * Time ago format
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($datetime);
    }
}

/**
 * Export array to CSV
 */
function arrayToCsv($data, $filename = 'export.csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (count($data) > 0) {
        // Headers
        fputcsv($output, array_keys($data[0]));
        
        // Data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

/**
 * Validate email address
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number
 */
function validatePhone($phone) {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it's a valid length (10-15 digits)
    return strlen($phone) >= 10 && strlen($phone) <= 15;
}

/**
 * Replace merge tags in template with actual data
 */
function replacePlaceholders($template, $data) {
    foreach ($data as $key => $value) {
        $template = str_replace('{{' . $key . '}}', $value, $template);
    }
    return $template;
}

/**
 * Custom error handler
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    error_log("Error [$errno]: $errstr in $errfile on line $errline");
    
    if (ENVIRONMENT === 'production') {
        // Show generic error page
        include __DIR__ . '/../views/errors/500.php';
        exit;
    }
}

/**
 * Custom exception handler
 */
function customExceptionHandler($exception) {
    error_log("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    if (ENVIRONMENT === 'production') {
        // Show generic error page
        include __DIR__ . '/../views/errors/500.php';
        exit;
    } else {
        echo "<h1>Exception</h1>";
        echo "<p><strong>Message:</strong> " . $exception->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $exception->getFile() . "</p>";
        echo "<p><strong>Line:</strong> " . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    }
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isLoggedIn()) {
        $lastActivity = $_SESSION['last_activity'] ?? time();
        
        if (time() - $lastActivity > SESSION_TIMEOUT) {
            session_unset();
            session_destroy();
            session_start();
            setFlashMessage('Your session has expired. Please login again.', 'warning');
            redirect('login');
        }
        
        $_SESSION['last_activity'] = time();
    }
}
