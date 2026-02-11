<?php
/**
 * Front Controller & Router
 * Handles all incoming requests and routes to appropriate controllers
 */

// Start output buffering
ob_start();

// Configure session settings BEFORE starting session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.gc_maxlifetime', 7200); // 2 hours
ini_set('session.cookie_lifetime', 0); // Until browser closes

// Start session
session_start();

// Load configuration
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/aisensy.php';

// Load helper functions
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

// Error handling for production
if (ENVIRONMENT === 'production') {
    set_error_handler('customErrorHandler');
    set_exception_handler('customExceptionHandler');
}

// Get the requested URL
$requestUri = '';

if (isset($_GET['url'])) {
    $requestUri = $_GET['url'];
} else {
    $requestUri = $_SERVER['REQUEST_URI'];
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $basePath = str_replace('\\', '/', dirname($scriptName));

    // Remove base path from request URI
    if ($basePath !== '/' && strpos($requestUri, $basePath) === 0) {
        $requestUri = substr($requestUri, strlen($basePath));
    }
}

// Remove query string
$requestUri = strtok($requestUri, '?');

// Remove leading and trailing slashes
$requestUri = trim($requestUri, '/');

// Handle error pages
if (isset($_GET['error'])) {
    $errorCode = $_GET['error'];
    include __DIR__ . '/views/errors/' . $errorCode . '.php';
    exit;
}

// Define routes
$routes = [
    '' => ['controller' => 'DashboardController', 'action' => 'index', 'auth' => true],
    'login' => ['controller' => 'AuthController', 'action' => 'login', 'auth' => false],
    'logout' => ['controller' => 'AuthController', 'action' => 'logout', 'auth' => true],
    
    // Dashboard
    'dashboard' => ['controller' => 'DashboardController', 'action' => 'index', 'auth' => true],
    
    // Clients
    'clients' => ['controller' => 'ClientController', 'action' => 'list', 'auth' => true],
    'clients/create' => ['controller' => 'ClientController', 'action' => 'create', 'auth' => true],
    'clients/edit' => ['controller' => 'ClientController', 'action' => 'edit', 'auth' => true],
    'clients/delete' => ['controller' => 'ClientController', 'action' => 'delete', 'auth' => true],
    'clients/view' => ['controller' => 'ClientController', 'action' => 'view', 'auth' => true],
    
    // Projects
    'projects' => ['controller' => 'ProjectController', 'action' => 'list', 'auth' => true],
    'projects/create' => ['controller' => 'ProjectController', 'action' => 'create', 'auth' => true],
    'projects/edit' => ['controller' => 'ProjectController', 'action' => 'edit', 'auth' => true],
    'projects/delete' => ['controller' => 'ProjectController', 'action' => 'delete', 'auth' => true],
    'projects/view' => ['controller' => 'ProjectController', 'action' => 'view', 'auth' => true],
    
    // Leads
    'leads' => ['controller' => 'LeadController', 'action' => 'list', 'auth' => true],
    'leads/create' => ['controller' => 'LeadController', 'action' => 'create', 'auth' => true],
    'leads/export' => ['controller' => 'LeadController', 'action' => 'export', 'auth' => true],
    'leads/import' => ['controller' => 'LeadController', 'action' => 'import', 'auth' => true],
    'leads/process-import' => ['controller' => 'LeadController', 'action' => 'processImport', 'auth' => true],
    'leads/edit' => ['controller' => 'LeadController', 'action' => 'edit', 'auth' => true],
    'leads/delete' => ['controller' => 'LeadController', 'action' => 'delete', 'auth' => true],
    'leads/view' => ['controller' => 'LeadController', 'action' => 'view', 'auth' => true],
    'leads/conversation' => ['controller' => 'LeadController', 'action' => 'conversation', 'auth' => true],
    'leads/send-message' => ['controller' => 'LeadController', 'action' => 'sendMessage', 'auth' => true],
    'leads/update-status' => ['controller' => 'LeadController', 'action' => 'updateStatus', 'auth' => true],
    'leads/send-custom-message' => ['controller' => 'LeadController', 'action' => 'sendCustomMessage', 'auth' => true],
    
    // Campaigns
    'campaigns' => ['controller' => 'CampaignController', 'action' => 'list', 'auth' => true],
    'campaigns/create' => ['controller' => 'CampaignController', 'action' => 'create', 'auth' => true],
    'campaigns/edit' => ['controller' => 'CampaignController', 'action' => 'edit', 'auth' => true],
    'campaigns/delete' => ['controller' => 'CampaignController', 'action' => 'delete', 'auth' => true],
    'campaigns/builder' => ['controller' => 'CampaignController', 'action' => 'builder', 'auth' => true],
    'campaigns/analytics' => ['controller' => 'CampaignController', 'action' => 'analytics', 'auth' => true],
    'campaigns/toggle-status' => ['controller' => 'CampaignController', 'action' => 'toggleStatus', 'auth' => true],
    'campaigns/start' => ['controller' => 'CampaignController', 'action' => 'start', 'auth' => true],
    'campaigns/pause' => ['controller' => 'CampaignController', 'action' => 'pause', 'auth' => true],
    'campaigns/resume' => ['controller' => 'CampaignController', 'action' => 'resume', 'auth' => true],
    'campaigns/manage-leads' => ['controller' => 'CampaignController', 'action' => 'manageLeads', 'auth' => true],
    'campaigns/add-lead' => ['controller' => 'CampaignController', 'action' => 'addLead', 'auth' => true],
    'campaigns/remove-lead' => ['controller' => 'CampaignController', 'action' => 'removeLead', 'auth' => true],
    'campaigns/pause-lead' => ['controller' => 'CampaignController', 'action' => 'pauseLead', 'auth' => true],
    'campaigns/resume-lead' => ['controller' => 'CampaignController', 'action' => 'resumeLead', 'auth' => true],
    
    // Messages
    'messages/templates' => ['controller' => 'MessageController', 'action' => 'templates', 'auth' => true],
    'messages/template/create' => ['controller' => 'MessageController', 'action' => 'createTemplate', 'auth' => true],
    'messages/template/edit' => ['controller' => 'MessageController', 'action' => 'editTemplate', 'auth' => true],
    'messages/template/delete' => ['controller' => 'MessageController', 'action' => 'deleteTemplate', 'auth' => true],
    
    // Settings & Integrations
    'settings/integrations' => ['controller' => 'SettingsController', 'action' => 'integrations', 'auth' => true],
    'settings/test-integration' => ['controller' => 'SettingsController', 'action' => 'testIntegration', 'auth' => true],
    'settings/profile' => ['controller' => 'SettingsController', 'action' => 'profile', 'auth' => true],
    'settings/updateProfile' => ['controller' => 'SettingsController', 'action' => 'updateProfile', 'auth' => true],
    'settings/updatePassword' => ['controller' => 'SettingsController', 'action' => 'updatePassword', 'auth' => true],
    
    // Notifications
    'notifications/unread' => ['controller' => 'NotificationController', 'action' => 'getUnread', 'auth' => true],
    'notifications/mark-read' => ['controller' => 'NotificationController', 'action' => 'markAsRead', 'auth' => true],
    'notifications/mark-all-read' => ['controller' => 'NotificationController', 'action' => 'markAllAsRead', 'auth' => true],
    'notifications/delete' => ['controller' => 'NotificationController', 'action' => 'delete', 'auth' => true],
];

// Try to match route
$routeMatched = false;
$controllerName = null;
$actionName = null;
$requiresAuth = true;

if (isset($routes[$requestUri])) {
    $route = $routes[$requestUri];
    $controllerName = $route['controller'];
    $actionName = $route['action'];
    $requiresAuth = $route['auth'];
    $routeMatched = true;
} else {
    // Check for dynamic routes (e.g., leads/123)
    $parts = explode('/', $requestUri);
    
    if (count($parts) == 2 && is_numeric($parts[1])) {
        $resource = $parts[0];
        $id = $parts[1];
        
        switch ($resource) {
            case 'clients':
                $controllerName = 'ClientController';
                $actionName = 'view';
                $_GET['id'] = $id;
                $requiresAuth = true;
                $routeMatched = true;
                break;
            case 'projects':
                $controllerName = 'ProjectController';
                $actionName = 'view';
                $_GET['id'] = $id;
                $requiresAuth = true;
                $routeMatched = true;
                break;
            case 'leads':
                $controllerName = 'LeadController';
                $actionName = 'view';
                $_GET['id'] = $id;
                $requiresAuth = true;
                $routeMatched = true;
                break;
            case 'campaigns':
                $controllerName = 'CampaignController';
                $actionName = 'view';
                $_GET['id'] = $id;
                $requiresAuth = true;
                $routeMatched = true;
                break;
        }
    } elseif (count($parts) == 3 && is_numeric($parts[1])) {
        // Handle routes like leads/123/conversation
        $resource = $parts[0];
        $id = $parts[1];
        $subAction = $parts[2];
        
        if ($resource === 'leads' && $subAction === 'conversation') {
            $controllerName = 'LeadController';
            $actionName = 'conversation';
            $_GET['id'] = $id;
            $requiresAuth = true;
            $routeMatched = true;
        }
    }
}

// Check authentication
if ($requiresAuth && !isLoggedIn()) {
    if ($requestUri !== 'login') {
        $_SESSION['redirect_after_login'] = $requestUri;
        redirect('login');
    }
}

// Route to controller
if ($routeMatched) {
    $controllerFile = __DIR__ . '/controllers/' . $controllerName . '.php';
    
    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            
            if (method_exists($controller, $actionName)) {
                $controller->$actionName();
            } else {
                // Method not found
                header("HTTP/1.0 404 Not Found");
                include __DIR__ . '/views/errors/404.php';
            }
        } else {
            // Class not found
            header("HTTP/1.0 404 Not Found");
            include __DIR__ . '/views/errors/404.php';
        }
    } else {
        // Controller file not found
        header("HTTP/1.0 404 Not Found");
        include __DIR__ . '/views/errors/404.php';
    }
} else {
    // No route matched - 404
    header("HTTP/1.0 404 Not Found");
    include __DIR__ . '/views/errors/404.php';
}

// Flush output buffer
ob_end_flush();
