<?php
/**
 * Meta Leads Service
 * Handles Meta Lead Ads webhook processing and lead creation
 */

require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/MetaLeadForm.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/AiSensyService.php';
require_once __DIR__ . '/MetaWhatsAppService.php';

class MetaLeadsService {
    private $db;
    private $leadModel;
    private $metaLeadFormModel;
    private $projectModel;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
        $this->leadModel = new Lead();
        $this->metaLeadFormModel = new MetaLeadForm();
        $this->projectModel = new Project();
    }
    
    /**
     * Verify webhook signature from Meta
     */
    public function verifyWebhookSignature($payload, $signature, $appSecret) {
        $expectedSignature = 'sha256=' . hash_hmac('sha256', $payload, $appSecret);
        return hash_equals($expectedSignature, $signature);
    }
    
    /**
     * Process webhook data from Meta
     * Accepts both real webhooks and Testing Tool format
     */
    public function processWebhook($data) {
        try {
            // Accept both formats:
            // 1. Real webhook: {"object":"page","entry":[...]}
            // 2. Testing Tool: {"entry":[...]} (no object field)
            
            // If object field exists, validate it
            if (isset($data['object']) && $data['object'] !== 'page') {
                throw new Exception('Invalid webhook object type');
            }
            
            if (!isset($data['entry']) || !is_array($data['entry'])) {
                throw new Exception('Invalid webhook entry data');
            }
            
            $results = [];
            
            foreach ($data['entry'] as $entry) {
                if (!isset($entry['changes'])) continue;
                
                foreach ($entry['changes'] as $change) {
                    if ($change['field'] === 'leadgen') {
                        $result = $this->processLeadgenChange($change['value']);
                        $results[] = $result;
                    }
                }
            }
            
            return [
                'success' => true,
                'processed' => count($results),
                'results' => $results
            ];
            
        } catch (Exception $e) {
            error_log("Meta Webhook Processing Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Process individual leadgen change
     */
    private function processLeadgenChange($data) {
        try {
            $leadgenId = $data['leadgen_id'] ?? null;
            $formId = $data['form_id'] ?? null;
            $adId = $data['ad_id'] ?? null;
            $createdTime = $data['created_time'] ?? null;
            
            if (!$leadgenId || !$formId) {
                throw new Exception('Missing leadgen_id or form_id');
            }
            
            // Find the project mapped to this form
            $formMapping = $this->metaLeadFormModel->findProjectByFormId($formId);
            
            if (!$formMapping) {
                error_log("No project mapping found for form_id: $formId");
                return [
                    'success' => false,
                    'error' => 'No project mapping for form_id: ' . $formId,
                    'form_id' => $formId
                ];
            }
            
            // Fetch full lead data from Meta API
            $leadData = $this->fetchLeadDetails($leadgenId);
            
            if (!$leadData) {
                throw new Exception('Failed to fetch lead details from Meta API');
            }
            
            // Create lead in CRM
            $lead = $this->createLeadFromMeta($leadData, $formMapping, $adId);
            
            // Update form statistics
            $this->metaLeadFormModel->incrementLeadCount($formId);
            
            // Send WhatsApp welcome message
            $this->sendWelcomeMessage($lead, $formMapping);
            
            return [
                'success' => true,
                'lead_id' => $lead['id'],
                'project_id' => $formMapping['project_id'],
                'form_id' => $formId
            ];
            
        } catch (Exception $e) {
            error_log("Lead Processing Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Fetch full lead details from Meta API
     */
    public function fetchLeadDetails($leadgenId) {
        try {
            $accessToken = $this->getSetting('meta_access_token');
            
            if (!$accessToken) {
                throw new Exception('Meta access token not configured');
            }
            
            $url = "https://graph.facebook.com/v18.0/{$leadgenId}?access_token={$accessToken}";
            
            error_log("Fetching lead from Meta API: $url");
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            error_log("Meta API Response - HTTP $httpCode: $response");
            
            if ($httpCode !== 200) {
                error_log("Meta API Error (HTTP $httpCode): $response");
                return null;
            }
            
            $data = json_decode($response, true);
            
            // Log the extracted field data
            if (isset($data['field_data'])) {
                error_log("Field data found: " . json_encode($data['field_data']));
            }
            
            return $data;
            
        } catch (Exception $e) {
            error_log("Error fetching lead details: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create lead in CRM from Meta data
     */
    private function createLeadFromMeta($metaData, $formMapping, $adId = null) {
        // Extract field data
        $fieldData = [];
        if (isset($metaData['field_data']) && is_array($metaData['field_data'])) {
            foreach ($metaData['field_data'] as $field) {
                $fieldData[$field['name']] = $field['values'][0] ?? '';
            }
        }
        
        // Map common field names
        $name = $fieldData['full_name'] ?? $fieldData['name'] ?? $fieldData['first_name'] ?? 'Unknown';
        $email = $fieldData['email'] ?? null;
        $phone = $fieldData['phone_number'] ?? $fieldData['phone'] ?? null;
        
        // Log extracted data
        error_log("Extracted from Meta - Name: $name, Email: $email, Phone: $phone");
        
        // Clean phone number
        if ($phone) {
            $phone = preg_replace('/[^0-9+]/', '', $phone);
        }
        
        // Prepare lead data
        $leadData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'project_id' => $formMapping['project_id'],
            'source' => 'meta_lead_ads',
            'meta_form_id' => $metaData['form_id'] ?? $formMapping['form_id'],
            'meta_lead_id' => $metaData['id'] ?? null,
            'meta_ad_id' => $adId,
            'status' => 'new',
            'notes' => $this->buildLeadNotes($fieldData, $metaData)
        ];
        
        // Create the lead
        $leadId = $this->leadModel->create($leadData);
        
        if ($leadId) {
            $leadData['id'] = $leadId;
            error_log("Lead created successfully: ID $leadId - Name: $name, Email: $email, Phone: $phone");
        }
        
        return $leadData;
    }
    
    /**
     * Build notes from all field data
     */
    private function buildLeadNotes($fieldData, $metaData) {
        $notes = "Lead captured from Meta Lead Ads\n\n";
        $notes .= "Form ID: " . ($metaData['form_id'] ?? 'N/A') . "\n";
        $notes .= "Lead ID: " . ($metaData['id'] ?? 'N/A') . "\n";
        $notes .= "Created: " . ($metaData['created_time'] ?? 'N/A') . "\n\n";
        
        if (!empty($fieldData)) {
            $notes .= "Form Responses:\n";
            foreach ($fieldData as $key => $value) {
                if (!in_array($key, ['full_name', 'name', 'first_name', 'email', 'phone_number', 'phone'])) {
                    $notes .= "- " . ucfirst(str_replace('_', ' ', $key)) . ": $value\n";
                }
            }
        }
        
        return $notes;
    }
    
    /**
     * Send WhatsApp welcome message based on project configuration
     */
    private function sendWelcomeMessage($lead, $formMapping) {
        try {
            if (!$lead['phone']) {
                error_log("Cannot send WhatsApp: No phone number for lead {$lead['id']}");
                return false;
            }
            
            $provider = $formMapping['whatsapp_provider'] ?? 'default';
            $welcomeMessage = $formMapping['welcome_message'] ?? "Thank you for your interest! We'll contact you shortly.";
            
            if ($provider === 'aisensy') {
                return $this->sendViaAiSensy($lead, $formMapping, $welcomeMessage);
            } elseif ($provider === 'cloud_api') {
                return $this->sendViaCloudAPI($lead, $welcomeMessage);
            }
            
            error_log("Unknown WhatsApp provider: $provider for project {$formMapping['project_id']}");
            return false;
            
        } catch (Exception $e) {
            error_log("WhatsApp sending error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send via AiSensy
     */
    private function sendViaAiSensy($lead, $formMapping, $message) {
        $campaignName = $formMapping['aisensy_campaign_name'] ?? null;
        
        if (!$campaignName) {
            error_log("AiSensy campaign name not configured for project {$formMapping['project_id']}");
            return false;
        }
        
        $aisensy = new AiSensyService();
        return $aisensy->sendCampaignMessage($lead['phone'], $campaignName, [
            'name' => $lead['name'],
            'project' => $formMapping['project_name'] ?? ''
        ]);
    }
    
    /**
     * Send via WhatsApp Cloud API
     */
    private function sendViaCloudAPI($lead, $message) {
        $whatsapp = new MetaWhatsAppService();
        return $whatsapp->sendTextMessage($lead['phone'], $message);
    }
    
    /**
     * Get setting value
     */
    private function getSetting($key) {
        $stmt = $this->db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch();
        return $result ? $result['setting_value'] : null;
    }
    
    /**
     * Test Meta API connection
     */
    public function testConnection() {
        try {
            $accessToken = $this->getSetting('meta_access_token');
            
            if (!$accessToken) {
                return [
                    'success' => false,
                    'error' => 'Access token not configured'
                ];
            }
            
            // Test with a simple API call
            $url = "https://graph.facebook.com/v18.0/me?access_token={$accessToken}";
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return [
                    'success' => true,
                    'message' => 'Connection successful',
                    'account' => $data
                ];
            }
            
            return [
                'success' => false,
                'error' => "HTTP $httpCode: $response"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
