<?php
/**
 * Meta WhatsApp Cloud API Service
 * Official WhatsApp Business API from Meta/Facebook
 * 
 * Features:
 * - 1,000 free conversations per month
 * - Most reliable (official API)
 * - Template-based messaging
 */

class MetaWhatsAppService {
    private $accessToken;
    private $phoneNumberId;
    private $apiVersion;
    private $baseUrl;
    
    public function __construct() {
        // Get from settings or config
        $this->accessToken = $this->getSetting('whatsapp_api_key');
        $this->phoneNumberId = $this->getSetting('whatsapp_phone_number_id');
        $this->apiVersion = 'v18.0';  // Latest stable version
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
    }
    
    /**
     * Send template message
     * Templates must be pre-approved in Meta Business Manager
     */
    public function sendTemplateMessage($phoneNumber, $templateName, $languageCode = 'en_US', $parameters = []) {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $components = [];
        
        // Add body parameters if provided
        if (!empty($parameters)) {
            $bodyParams = [];
            foreach ($parameters as $param) {
                $bodyParams[] = [
                    'type' => 'text',
                    'text' => $param
                ];
            }
            
            $components[] = [
                'type' => 'body',
                'parameters' => $bodyParams
            ];
        }
        
        $data = [
            'messaging_product' => 'whatsapp',
            'to' => $phoneNumber,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode
                ]
            ]
        ];
        
        if (!empty($components)) {
            $data['template']['components'] = $components;
        }
        
        return $this->makeRequest('/messages', $data);
    }
    
    /**
     * Send text message (within 24-hour window)
     * Can only send if customer has messaged you first within 24 hours
     */
    public function sendTextMessage($phoneNumber, $message) {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phoneNumber,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message
            ]
        ];
        
        return $this->makeRequest('/messages', $data);
    }
    
    /**
     * Send media message (image, video, document)
     */
    public function sendMediaMessage($phoneNumber, $mediaUrl, $mediaType = 'image', $caption = '') {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $mediaData = [
            'link' => $mediaUrl
        ];
        
        if ($caption && in_array($mediaType, ['image', 'video'])) {
            $mediaData['caption'] = $caption;
        }
        
        $data = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phoneNumber,
            'type' => $mediaType,
            $mediaType => $mediaData
        ];
        
        return $this->makeRequest('/messages', $data);
    }
    
    /**
     * Send welcome message to new lead
     */
    public function sendWelcomeMessage($lead, $project) {
        // Use template for welcome message
        $templateName = 'lead_welcome';  // Must be created in Meta Business Manager
        
        $parameters = [
            $lead['name'],                    // {{1}} - Customer name
            $project['project_name'],         // {{2}} - Project name
        ];
        
        return $this->sendTemplateMessage($lead['phone'], $templateName, 'en_US', $parameters);
    }
    
    /**
     * Format phone number to Meta format
     * Example: +91 98765 43210 -> 919876543210
     */
    private function formatPhoneNumber($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading zeros
        $phone = ltrim($phone, '0');
        
        // If doesn't start with country code, add default (India = 91)
        if (strlen($phone) == 10) {
            $phone = '91' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Make cURL request to Meta API
     */
    private function makeRequest($endpoint, $data) {
        $url = $this->baseUrl . '/' . $this->phoneNumberId . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Log request if enabled
        $this->logRequest($endpoint, $data, $response, $httpCode);
        
        if ($error) {
            return [
                'success' => false,
                'error' => 'curl_error',
                'message' => $error
            ];
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $responseData,
                'message_id' => $responseData['messages'][0]['id'] ?? null,
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => 'api_error',
                'message' => $responseData['error']['message'] ?? 'Unknown API error',
                'error_code' => $responseData['error']['code'] ?? null,
                'http_code' => $httpCode,
                'data' => $responseData
            ];
        }
    }
    
    /**
     * Get setting from database
     */
    private function getSetting($key, $default = '') {
        try {
            require_once __DIR__ . '/../controllers/SettingsController.php';
            return SettingsController::getSetting($key, $default);
        } catch (Exception $e) {
            return $default;
        }
    }
    
    /**
     * Log API request and response
     */
    private function logRequest($endpoint, $requestData, $response, $httpCode) {
        $logFile = BASE_PATH . 'logs/meta-whatsapp.log';
        $logDir = dirname($logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Redact access token for security
        $requestDataCopy = $requestData;
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'request' => $requestDataCopy,
            'response' => $response,
            'http_code' => $httpCode
        ];
        
        file_put_contents(
            $logFile,
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * Validate credentials
     */
    public function validateCredentials() {
        if (empty($this->accessToken) || empty($this->phoneNumberId)) {
            return [
                'success' => false,
                'error' => 'invalid_credentials',
                'message' => 'Meta WhatsApp API credentials not configured'
            ];
        }
        
        return [
            'success' => true,
            'message' => 'Credentials configured'
        ];
    }
}
