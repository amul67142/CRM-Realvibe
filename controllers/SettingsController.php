<?php
/**
 * Settings Controller
 * Manages application settings and integrations
 */

class SettingsController {
    private $db;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
    }
    
    /**
     * Show integrations page
     */
    public function integrations() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            setFlashMessage('Access denied', 'error');
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveIntegrations();
            return;
        }
        
        // Get current settings
        $settings = $this->getAllSettings();
        
        include BASE_PATH . 'views/settings/integrations.php';
    }
    
    /**
     * Save integrations
     */
    private function saveIntegrations() {
        if (!verifyToken($_POST['csrf_token'] ?? '')) {
            setFlashMessage('Invalid request', 'error');
            redirect('settings/integrations');
        }
        
        // WhatsApp Integration Settings
        $settings = [
            // Primary WhatsApp Provider
            'whatsapp_provider' => $_POST['whatsapp_provider'] ?? 'aisensy',
            
            // AiSensy
            'aisensy_api_key' => $_POST['aisensy_api_key'] ?? '',
            'aisensy_base_url' => $_POST['aisensy_base_url'] ?? 'https://backend.aisensy.com/campaign/t1/api/v2',
            'aisensy_enabled' => isset($_POST['aisensy_enabled']) ? 1 : 0,
            
            // WhatsApp Business API
            'whatsapp_api_key' => $_POST['whatsapp_api_key'] ?? '',
            'whatsapp_api_url' => $_POST['whatsapp_api_url'] ?? '',
            'whatsapp_phone_number_id' => $_POST['whatsapp_phone_number_id'] ?? '',
            'whatsapp_business_account_id' => $_POST['whatsapp_business_account_id'] ?? '',
            'whatsapp_api_enabled' => isset($_POST['whatsapp_api_enabled']) ? 1 : 0,
            
            // Meta Lead Ads
            'meta_app_id' => $_POST['meta_app_id'] ?? '',
            'meta_app_secret' => $_POST['meta_app_secret'] ?? '',
            'meta_access_token' => $_POST['meta_access_token'] ?? '',
            'meta_verify_token' => $_POST['meta_verify_token'] ?? '',
            
            // Twilio WhatsApp
            'twilio_account_sid' => $_POST['twilio_account_sid'] ?? '',
            'twilio_auth_token' => $_POST['twilio_auth_token'] ?? '',
            'twilio_whatsapp_number' => $_POST['twilio_whatsapp_number'] ?? '',
            'twilio_enabled' => isset($_POST['twilio_enabled']) ? 1 : 0,
            
            // SMTP Email Settings
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? '587',
            'smtp_username' => $_POST['smtp_username'] ?? '',
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'smtp_encryption' => $_POST['smtp_encryption'] ?? 'tls',
            'smtp_from_email' => $_POST['smtp_from_email'] ?? '',
            'smtp_from_name' => $_POST['smtp_from_name'] ?? 'RealVibe CRM',
            'smtp_enabled' => isset($_POST['smtp_enabled']) ? 1 : 0,
            
            // SMS Gateway
            'sms_provider' => $_POST['sms_provider'] ?? 'none',
            'sms_api_key' => $_POST['sms_api_key'] ?? '',
            'sms_sender_id' => $_POST['sms_sender_id'] ?? '',
            'sms_enabled' => isset($_POST['sms_enabled']) ? 1 : 0,
        ];
        
        foreach ($settings as $key => $value) {
            $this->saveSetting($key, $value);
        }
        
        // Also update the config file for AiSensy if needed
        if (!empty($settings['aisensy_api_key'])) {
            $this->updateAiSensyConfig($settings['aisensy_api_key'], $settings['aisensy_base_url']);
        }
        
        setFlashMessage('Integration settings saved successfully!', 'success');
        redirect('settings/integrations');
    }
    
    /**
     * Save individual setting
     */
    private function saveSetting($key, $value) {
        $stmt = $this->db->prepare("
            INSERT INTO settings (setting_key, setting_value, setting_type)
            VALUES (?, ?, 'string')
            ON DUPLICATE KEY UPDATE setting_value = ?
        ");
        
        $stmt->execute([$key, $value, $value]);
    }
    
    /**
     * Get all settings
     */
    private function getAllSettings() {
        $stmt = $this->db->query("SELECT setting_key, setting_value FROM settings");
        $results = $stmt->fetchAll();
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        return $settings;
    }
    
    /**
     * Get single setting
     */
    public static function getSetting($key, $default = '') {
        $db = getDatabaseConnection();
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        
        return $result ? $result['setting_value'] : $default;
    }
    
    /**
     * Update AiSensy config file
     */
    private function updateAiSensyConfig($apiKey, $baseUrl) {
        $configFile = BASE_PATH . 'config/aisensy.php';
        $content = file_get_contents($configFile);
        
        // Replace API key
        $content = preg_replace(
            "/define\('AISENSY_API_KEY',\s*'[^']*'\);/",
            "define('AISENSY_API_KEY', '$apiKey');",
            $content
        );
        
        // Replace base URL
        $content = preg_replace(
            "/define\('AISENSY_API_BASE_URL',\s*'[^']*'\);/",
            "define('AISENSY_API_BASE_URL', '$baseUrl');",
            $content
        );
        
        file_put_contents($configFile, $content);
    }
    
    /**
     * Test integration
     */
    public function testIntegration() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            json(['success' => false, 'error' => 'Access denied'], 403);
        }
        
        $provider = $_POST['provider'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        switch ($provider) {
            case 'aisensy':
                $this->testAiSensy($phone);
                break;
            case 'whatsapp_api':
                $this->testWhatsAppAPI($phone);
                break;
            case 'twilio':
                $this->testTwilio($phone);
                break;
            case 'smtp':
                $this->testSMTP($_POST['email'] ?? '');
                break;
            default:
                json(['success' => false, 'error' => 'Unknown provider'], 400);
        }
    }
    
    /**
     * Test AiSensy integration
     */
    private function testAiSensy($phone) {
        require_once BASE_PATH . 'services/AiSensyService.php';
        
        $campaign = $_POST['campaign'] ?? 'test_campaign';
        $template = $_POST['template'] ?? null;
        
        $aisensy = new AiSensyService();
        
        // If template provided, use template message, otherwise use simple text
        if ($template) {
            $result = $aisensy->sendTemplateMessage($phone, $template, ['Test from RealVibe CRM']);
        } else {
            $result = $aisensy->sendTextMessage($phone, 'Test message from RealVibe CRM - Connection successful! 🎉');
        }
        
        json($result);
    }
    
    /**
     * Test WhatsApp Business API
     */
    private function testWhatsAppAPI($phone) {
        require_once BASE_PATH . 'services/MetaWhatsAppService.php';
        
        $meta = new MetaWhatsAppService();
        
        // Try to send a simple template (hello_world is pre-approved by Meta)
        $result = $meta->sendTemplateMessage($phone, 'hello_world', 'en_US');
        
        json($result);
    }
    
    /**
     * Test Twilio
     */
    private function testTwilio($phone) {
        // Implement Twilio test
        json(['success' => false, 'error' => 'Twilio test not implemented yet'], 501);
    }
    
    /**
     * Test SMTP
     */
    private function testSMTP($email) {
        // Implement SMTP test
        json(['success' => false, 'error' => 'SMTP test not implemented yet'], 501);
    }
    
    /**
     * Show admin profile settings
     */
    public function profile() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            setFlashMessage('Access denied', 'error');
            redirect('dashboard');
        }
        
        // Get current admin profile
        $settings = $this->getAdminProfile();
        
        include BASE_PATH . 'views/settings/profile.php';
    }
    
    /**
     * Update admin profile
     */
    public function updateProfile() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            json(['success' => false, 'error' => 'Access denied'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json(['success' => false, 'error' => 'Invalid request method'], 405);
        }
        
        // Debug CSRF token
        $submittedToken = $_POST['csrf_token'] ?? '';
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        error_log("CSRF Debug - Submitted: " . substr($submittedToken, 0, 10) . "... Session: " . substr($sessionToken, 0, 10) . "...");
        
        if (!verifyToken($submittedToken)) {
            error_log("CSRF verification failed");
            json(['success' => false, 'error' => 'Invalid CSRF token. Please refresh the page and try again.'], 403);
        }
        
        $settings = [
            'admin_name' => sanitizeInput($_POST['admin_name'] ?? ''),
            'admin_email' => sanitizeInput($_POST['admin_email'] ?? ''),
            'admin_phone' => sanitizeInput($_POST['admin_phone'] ?? ''),
            'admin_notification_enabled' => isset($_POST['admin_notification_enabled']) ? '1' : '0'
        ];
        
        // Validate
        if (empty($settings['admin_name'])) {
            json(['success' => false, 'error' => 'Name is required'], 400);
        }
        
        if (!empty($settings['admin_email']) && !filter_var($settings['admin_email'], FILTER_VALIDATE_EMAIL)) {
            json(['success' => false, 'error' => 'Invalid email address'], 400);
        }
        
        // Save settings
        foreach ($settings as $key => $value) {
            $stmt = $this->db->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type)
                VALUES (?, ?, 'string')
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([$key, $value]);
        }
        
        json(['success' => true, 'message' => 'Profile updated successfully']);
    }
    
    /**
     * Update admin password
     */
    public function updatePassword() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            json(['success' => false, 'error' => 'Access denied'], 403);
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json(['success' => false, 'error' => 'Invalid request method'], 405);
        }
        
        if (!verifyToken($_POST['csrf_token'] ?? '')) {
            json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            json(['success' => false, 'error' => 'All password fields are required'], 400);
        }
        
        if ($newPassword !== $confirmPassword) {
            json(['success' => false, 'error' => 'New passwords do not match'], 400);
        }
        
        if (strlen($newPassword) < 6) {
            json(['success' => false, 'error' => 'Password must be at least 6 characters'], 400);
        }
        
        // Verify current password
        $userId = $_SESSION['user_id'];
        $stmt = $this->db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            json(['success' => false, 'error' => 'Current password is incorrect'], 400);
        }
        
        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE  id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        json(['success' => true, 'message' => 'Password updated successfully']);
    }
    
    /**
     * Get admin profile settings
     */
    private function getAdminProfile() {
        $stmt = $this->db->prepare("
            SELECT setting_key, setting_value 
            FROM settings 
            WHERE setting_key IN ('admin_name', 'admin_email', 'admin_phone', 'admin_notification_enabled')
        ");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return [
            'admin_name' => $settings['admin_name'] ?? 'Admin',
            'admin_email' => $settings['admin_email'] ?? '',
            'admin_phone' => $settings['admin_phone'] ?? '',
            'admin_notification_enabled' => ($settings['admin_notification_enabled'] ?? '1') == '1'
        ];
    }
}
