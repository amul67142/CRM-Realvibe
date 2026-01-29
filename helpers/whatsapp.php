<?php
/**
 * WhatsApp Helper Functions
 * Centralized WhatsApp messaging with provider routing
 */

require_once __DIR__ . '/../services/AiSensyService.php';
require_once __DIR__ . '/../services/MetaWhatsAppService.php';

/**
 * Send WhatsApp message based on project configuration
 * 
 * @param string $phone Phone number
 * @param string $message Message text
 * @param int|null $projectId Project ID (null = use global settings)
 * @return array Result array with success status
 */
function sendWhatsAppMessage($phone, $message, $projectId = null) {
    $db = getDatabaseConnection();
    
    // Get provider configuration
    $provider = 'default';
    $campaignName = null;
    
    if ($projectId) {
        // Get project-specific provider
        $stmt = $db->prepare("SELECT whatsapp_provider, aisensy_campaign_name FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch();
        
        if ($project) {
            $provider = $project['whatsapp_provider'] ?? 'default';
            $campaignName = $project['aisensy_campaign_name'];
        }
    }
    
    // If provider is 'default', use global setting
    if ($provider === 'default') {
        $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = 'whatsapp_provider'");
        $stmt->execute();
        $result = $stmt->fetch();
        $provider = $result ? $result['setting_value'] : 'aisensy';
    }
    
    // Route to appropriate service based on provider
    try {
        switch ($provider) {
            case 'aisensy':
                $service = new AiSensyService();
                // Use project campaign name if available, otherwise use default
                $campaign = $campaignName ?: 'api_campaign';
                return $service->sendTextMessage($phone, $message, $campaign);
                
            case 'whatsapp_api':
                $service = new MetaWhatsAppService();
                return $service->sendTextMessage($phone, $message);
                
            case 'twilio':
                // TODO: Implement Twilio service
                return [
                    'success' => false,
                    'error' => 'Twilio provider not yet implemented'
                ];
                
            default:
                return [
                    'success' => false,
                    'error' => 'Unknown WhatsApp provider: ' . $provider
                ];
        }
    } catch (Exception $e) {
        error_log("WhatsApp send error (Provider: $provider): " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to send message: ' . $e->getMessage()
        ];
    }
}

/**
 * Get WhatsApp provider for a project
 * 
 * @param int|null $projectId Project ID
 * @return string Provider name
 */
function getProjectWhatsAppProvider($projectId = null) {
    if (!$projectId) {
        return 'default';
    }
    
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT whatsapp_provider FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();
    
    return $project ? ($project['whatsapp_provider'] ?? 'default') : 'default';
}

/**
 * Get AiSensy campaign name for a project
 * 
 * @param int $projectId Project ID
 * @return string|null Campaign name
 */
function getProjectAiSensyCampaign($projectId) {
    $db = getDatabaseConnection();
    $stmt = $db->prepare("SELECT aisensy_campaign_name FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch();
    
    return $project ? $project['aisensy_campaign_name'] : null;
}
