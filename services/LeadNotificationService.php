<?php
/**
 * Lead Notification Service
 * Sends automated WhatsApp notifications when new leads are captured
 * Uses AiSensy "newleadsreminder" template
 */

require_once __DIR__ . '/../config/aisensy.php';

class LeadNotificationService {
    private $apiKey;
    private $apiUrl;
    private $db;
    private $logFile;
    
    public function __construct() {
        $this->apiKey = AISENSY_API_KEY;
        $this->apiUrl = AISENSY_API_BASE_URL;
        $this->db = getDatabaseConnection();
        $this->logFile = BASE_PATH . 'logs/lead-notifications.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Send new lead alert notifications to client and admin
     * 
     * @param array $lead Lead data
     * @param array $project Project data
     * @return array Results for each recipient
     */
    public function sendNewLeadAlert($lead, $project) {
        $this->log("Starting new lead alert for Lead ID: {$lead['id']}, Project: {$project['project_name']}");
        
        // Get notification recipients
        $recipients = $this->getNotificationRecipients($project);
        
        if (empty($recipients)) {
            $this->log("No recipients found for notifications");
            return [
                'success' => false,
                'message' => 'No recipients configured'
            ];
        }
        
        $results = [];
        
        // Send notification to each recipient
        foreach ($recipients as $recipient) {
            $result = $this->sendNotificationToRecipient($recipient, $lead, $project);
            $results[] = [
                'recipient' => $recipient['name'],
                'phone' => $recipient['phone'],
                'success' => $result['success'],
                'message' => $result['message'] ?? $result['error'] ?? ''
            ];
        }
        
        return [
            'success' => true,
            'results' => $results
        ];
    }
    
    /**
     * Get list of recipients (client + admin) who should receive notifications
     * 
     * @param array $project Project data
     * @return array Array of recipients with name and phone
     */
    private function getNotificationRecipients($project) {
        $recipients = [];
        
        // Get client phone from clients table
        if (!empty($project['client_id'])) {
            $stmt = $this->db->prepare("
                SELECT name, phone, email 
                FROM clients 
                WHERE id = ? AND phone IS NOT NULL AND phone != ''
            ");
            $stmt->execute([$project['client_id']]);
            $client = $stmt->fetch();
            
            if ($client && !empty($client['phone'])) {
                $recipients[] = [
                    'type' => 'client',
                    'name' => $client['name'],
                    'phone' => $client['phone'],
                    'email' => $client['email']
                ];
                $this->log("Added client recipient: {$client['name']} - {$client['phone']}");
            } else {
                $this->log("Client phone not found for client ID: {$project['client_id']}");
            }
        }
        
        // Get admin phone from settings
        $stmt = $this->db->prepare("
            SELECT setting_value 
            FROM settings 
            WHERE setting_key = 'admin_notification_enabled'
        ");
        $stmt->execute();
        $notifEnabled = $stmt->fetchColumn();
        
        if ($notifEnabled == '1') {
            $adminData = $this->getAdminProfile();
            
            if (!empty($adminData['phone'])) {
                $recipients[] = [
                    'type' => 'admin',
                    'name' => $adminData['name'],
                    'phone' => $adminData['phone'],
                    'email' => $adminData['email']
                ];
                $this->log("Added admin recipient: {$adminData['name']} - {$adminData['phone']}");
            } else {
                $this->log("Admin phone not configured");
            }
        } else {
            $this->log("Admin notifications disabled");
        }
        
        return $recipients;
    }
    
    /**
     * Get admin profile from settings
     * 
     * @return array Admin profile data
     */
    private function getAdminProfile() {
        $stmt = $this->db->prepare("
            SELECT setting_key, setting_value 
            FROM settings 
            WHERE setting_key IN ('admin_name', 'admin_email', 'admin_phone')
        ");
        $stmt->execute();
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        return [
            'name' => $settings['admin_name'] ?? 'Admin',
            'email' => $settings['admin_email'] ?? '',
            'phone' => $settings['admin_phone'] ?? ''
        ];
    }
    
    /**
     * Send notification to a single recipient
     * 
     * @param array $recipient Recipient data
     * @param array $lead Lead data
     * @param array $project Project data
     * @return array Result
     */
    private function sendNotificationToRecipient($recipient, $lead, $project) {
        // Prepare template parameters for AiSensy
        $params = [
            $recipient['name'],                    // {{1}} - Recipient name
            $project['project_name'],              // {{2}} - Project name
            $lead['name'],                         // {{3}} - Lead name
            $lead['email'] ?: 'Not provided',      // {{4}} - Lead email
            $lead['phone'],                        // {{5}} - Lead phone
            $lead['phone']                         // {{6}} - Lead WhatsApp (same as phone)
        ];
        
        // Send via AiSensy
        return $this->sendAiSensyTemplate(
            $recipient['phone'],
            'New_Leads_Reminder',      // Campaign name
            'newleadsreminder',         // Template name
            $params
        );
    }
    
    /**
     * Send WhatsApp message using AiSensy template
     * 
     * @param string $phone Phone number
     * @param string $campaignName AiSensy campaign name
     * @param string $templateName Template name
     * @param array $params Template parameters
     * @return array Result
     */
    private function sendAiSensyTemplate($phone, $campaignName, $templateName, $params) {
        // Format phone number
        $phone = $this->formatPhoneNumber($phone);
        
        $data = [
            'apiKey' => $this->apiKey,
            'campaignName' => $campaignName,
            'destination' => $phone,
            'userName' => $params[0], // Recipient name
            'templateParams' => $params,
            'source' => 'realvibe-crm-notifications'
        ];
        
        $this->log("Sending to $phone via campaign: $campaignName, template: $templateName");
        $this->log("Template params: " . json_encode($params));
        
        try {
            $ch = curl_init($this->apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json'
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            $this->log("Response code: $httpCode, Response: $response");
            
            if ($error) {
                $this->log("cURL Error: $error");
                return [
                    'success' => false,
                    'error' => 'Connection error: ' . $error
                ];
            }
            
            $responseData = json_decode($response, true);
            
            if ($httpCode == 200) {
                return [
                    'success' => true,
                    'message' => 'Notification sent successfully',
                    'data' => $responseData
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'API error: ' . ($responseData['message'] ?? 'Unknown error'),
                    'data' => $responseData
                ];
            }
            
        } catch (Exception $e) {
            $this->log("Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send custom WhatsApp message to a lead
     * Uses CRM_Template campaign with custom message content
     * 
     * @param string $phone Lead phone number
     * @param string $name Lead name
     * @param string $customMessage Your custom message content
     * @param string $source Source identifier (default: 'crm_custom')
     * @return array Response with success status
     */
    public function sendCustomMessage($phone, $name, $customMessage, $source = 'crm_custom') {
        $this->log("Sending custom message to: $name ($phone)");
        
        try {
            // Format phone number
            $formattedPhone = $this->formatPhoneNumber($phone);
            
            // Ensure phone has + prefix for AiSensy
            if (!str_starts_with($formattedPhone, '+')) {
                $formattedPhone = '+' . $formattedPhone;
            }
            
            // Prepare payload - matching working Python script format
            $payload = [
                'apiKey' => $this->apiKey,
                'campaignName' => 'CRM_Template',  // Use existing campaign
                'destination' => $formattedPhone,
                'userName' => $name,
                'templateParams' => [
                    $name,           // {{1}} - Name
                    $customMessage   // {{2}} - Custom Message
                ],
                'source' => $source,
                'media' => (object)[],
                'buttons' => [],
                'carouselCards' => [],
                'location' => (object)[]
            ];
            
            $this->log("Payload: " . json_encode($payload));
            
            // Send request
            $ch = curl_init($this->apiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 30
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            $this->log("Response Code: $httpCode");
            $this->log("Response: $response");
            
            if ($curlError) {
                throw new Exception("CURL Error: $curlError");
            }
            
            if ($httpCode == 200 || $httpCode == 201) {
                $this->log("Custom message sent successfully");
                return [
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'response' => json_decode($response, true)
                ];
            } else {
                $this->log("Failed to send custom message. HTTP Code: $httpCode");
                return [
                    'success' => false,
                    'error' => "HTTP Error: $httpCode",
                    'response' => $response
                ];
            }
            
        } catch (Exception $e) {
            $this->log("Exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Format phone number for India (prepend 91 if needed)
     * 
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    private function formatPhoneNumber($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // If 10 digits (India), prepend 91
        if (strlen($phone) == 10) {
            $phone = '91' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Log message to file
     * 
     * @param string $message Message to log
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents(
            $this->logFile,
            "[{$timestamp}] {$message}" . PHP_EOL,
            FILE_APPEND
        );
    }
}
