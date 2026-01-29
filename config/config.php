<?php
/**
 * Application Configuration
 */

// Environment detection
define('ENVIRONMENT', 'development'); // Change to 'production' for live server

// Base URL configuration (auto-detect)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = ($scriptPath === '/' || $scriptPath === '\\') ? '' : $scriptPath;

define('BASE_URL', $protocol . $host . $basePath);
define('BASE_PATH', __DIR__ . '/../');

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . 'logs/error.log');
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// File upload configuration
define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
define('ALLOWED_VIDEO_TYPES', ['video/mp4', 'video/mpeg', 'video/quicktime']);

// Pagination
define('ITEMS_PER_PAGE', 25);

// Application settings
define('APP_NAME', 'RealVibe CRM');
define('APP_VERSION', '1.0.0');

// Security
define('CSRF_TOKEN_EXPIRY', 3600); // 1 hour
define('SESSION_TIMEOUT', 7200); // 2 hours

// Paths
define('UPLOAD_PATH', BASE_PATH . 'uploads/');
define('MEDIA_PATH', UPLOAD_PATH . 'media/');
define('DOCUMENT_PATH', UPLOAD_PATH . 'documents/');
define('UPLOAD_URL', BASE_URL . '/uploads/');
define('MEDIA_URL', UPLOAD_URL . 'media/');
define('DOCUMENT_URL', UPLOAD_URL . 'documents/');

// Create upload directories if they don't exist
if (!file_exists(MEDIA_PATH)) {
    mkdir(MEDIA_PATH, 0755, true);
}
if (!file_exists(DOCUMENT_PATH)) {
    mkdir(DOCUMENT_PATH, 0755, true);
}
