<?php
/**
 * AiSensy Service
 * WhatsApp Business API integration via AiSensy
 */

class AiSensyService {
    private $apiKey;
    private $baseUrl;
    private $timeout;
    
    public function __construct() {
        $this->apiKey = AISENSY_API_KEY;
        $this->baseUrl = AISENSY_API_BASE_URL;
        $this->timeout = AISENSY_TIMEOUT;
    }
    
    /**
     * Send plain text WhatsApp message
     * Note: Requires an API campaign to be created in AiSensy dashboard
     */
    public function sendTextMessage($phoneNumber, $message, $campaignName = 'api_campaign') {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $data = [
            'apiKey' => $this->apiKey,
            'campaignName' => $campaignName,  // Must be created in AiSensy dashboard
            'destination' => $phoneNumber,
            'userName' => 'Customer',
            'templateParams' => [$message],  // Parameters for template
            'source' => 'realvibe-crm'
        ];
        
        return $this->makeRequest('', $data);  // Base URL already includes endpoint
    }
    
    /**
     * Send media message (image, video, document)
     */
    public function sendMediaMessage($phoneNumber, $mediaUrl, $caption = '', $mediaType = 'image') {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $data = [
            'apiKey' => $this->apiKey,
            'campaignName' => $mediaType . '_message',
            'destination' => $phoneNumber,
            'userName' => 'RealVibe CRM',
            'media' => [
                'url' => $mediaUrl,
                'filename' => basename($mediaUrl)
            ]
        ];
        
        if ($caption) {
            $data['message'] = $caption;
        }
        
        $endpoint = '/sendMediaMessage';
        
        return $this->makeRequest($endpoint, $data);
    }
    
    /**
     * Send template message with buttons
     */
    public function sendTemplateMessage($phoneNumber, $templateId, $params = []) {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $data = [
            'apiKey' => $this->apiKey,
            'campaignName' => 'template_message',
            'destination' => $phoneNumber,
            'userName' => 'RealVibe CRM',
            'templateParams' => $params,
            'source' => 'realvibe-crm'
        ];
        
        return $this->makeRequest('/sendTemplateMessage?id=' . $templateId, $data);
    }
    
    /**
     * Send button message
     */
    public function sendButtonMessage($phoneNumber, $message, $buttons) {
        $phoneNumber = $this->formatPhoneNumber($phoneNumber);
        
        $data = [
            'apiKey' => $this->apiKey,
            'campaignName' => 'button_message',
            'destination' => $phoneNumber,
            'userName' => 'RealVibe CRM',
            'message' => $message,
            'buttonDetails' => $buttons
        ];
        
        return $this->makeRequest('/sendButtonMessage', $data);
    }
    
    /**
     * Get message delivery status
     */
    public function getMessageStatus($messageId) {
        $data = [
            'apiKey' => $this->apiKey,
            'messageId' => $messageId
        ];
        
        return $this->makeRequest('/getMessageStatus', $data);
    }
    
    /**
     * Format phone number to AiSensy format
     * Example: +91 98765 43210 -> 919876543210
     */
    public function formatPhoneNumber($phone) {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Remove leading zeros
        $phone = ltrim($phone, '0');
        
        // If doesn't start with country code, add default
        if (strlen($phone) == 10) {
            $phone = DEFAULT_COUNTRY_CODE . $phone;
        }
        
        // Remove + if present (already removed above)
        return $phone;
    }
    
    /**
     * Make cURL request to AiSensy API
     */
private function makeRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Log request and response if enabled
        if (AISENSY_LOG_ALL_REQUESTS) {
            $this->logRequest($endpoint, $data, $response, $httpCode);
        }
        
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
                'http_code' => $httpCode
            ];
        } else {
            return [
                'success' => false,
                'error' => 'api_error',
                'message' => $responseData['message'] ?? 'Unknown API error',
                'http_code' => $httpCode,
                'data' => $responseData
            ];
        }
    }
    
    /**
     * Log API request and response
     */
    private function logRequest($endpoint, $requestData, $response, $httpCode) {
        // Remove API key from logged data for security
        $requestDataCopy = $requestData;
        if (isset($requestDataCopy['apiKey'])) {
            $requestDataCopy['apiKey'] = '***REDACTED***';
        }
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'endpoint' => $endpoint,
            'request' => $requestDataCopy,
            'response' => $response,
            'http_code' => $httpCode
        ];
        
        $logFile = AISENSY_LOG_FILE;
        $logDir = dirname($logFile);
        
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents(
            $logFile,
            json_encode($logEntry) . PHP_EOL,
            FILE_APPEND
        );
    }
    
    /**
     * Validate API credentials
     */
    public function validateCredentials() {
        if (empty($this->apiKey) || $this->apiKey === 'YOUR_AISENSY_API_KEY_HERE') {
            return [
                'success' => false,
                'error' => 'invalid_credentials',
                'message' => 'AiSensy API key not configured'
            ];
        }
        
        // Try a simple API call to verify credentials
        // You might need to adjust based on AiSensy's actual validation endpoint
        return [
            'success' => true,
            'message' => 'API key configured'
        ];
    }
    
    /**
     * Send welcome message to lead
     */
    public function sendWelcomeMessage($lead, $project) {
        $message = $project['welcome_message'] ?? "Hello {{name}}! Thank you for your interest in {{project_name}}. We will get back to you shortly.";
        
        // Replace merge tags
        $mergeData = [
            'name' => $lead['name'],
            'first_name' => explode(' ', $lead['name'])[0],
            'phone' => $lead['phone'],
            'email' => $lead['email'] ?? '',
            'project_name' => $project['project_name'],
            'project_location' => $project['location'] ?? '',
            'project_type' => $project['project_type'] ?? '',
            'price_range' => $project['price_range'] ?? '',
            'current_date' => date('F j, Y'),
            'brochure_link' => $project['brochure_url'] ?? ''
        ];
        
        $message = replacePlaceholders($message, $mergeData);
        
        return $this->sendTextMessage($lead['phone'], $message);
    }
    
    /**
     * Send unsubscribe confirmation
     */
    public function sendUnsubscribeConfirmation($phoneNumber, $name) {
        $message = "Hi $name, you have been unsubscribed from our messages. Reply START to subscribe again.";
        return $this->sendTextMessage($phoneNumber, $message);
    }
    
    /**
     * Send resubscribe confirmation
     */
    public function sendResubscribeConfirmation($phoneNumber, $name) {
        $message = "Welcome back $name! You have been resubscribed to our messages.";
        return $this->sendTextMessage($phoneNumber, $message);
    }
}
