<?php
/**
 * Campaign Service
 * Business logic for campaign management and lead enrollment
 */

require_once __DIR__ . '/../models/Campaign.php';
require_once __DIR__ . '/../models/CampaignMessage.php';
require_once __DIR__ . '/../models/LeadCampaign.php';
require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/WhatsAppMessage.php';
require_once __DIR__ . '/AiSensyService.php';

class CampaignService {
    private $campaignModel;
    private $messageModel;
    private $leadCampaignModel;
    private $leadModel;
    private $whatsappModel;
    private $aisensy;
    
    public function __construct() {
        $this->campaignModel = new Campaign();
        $this->messageModel = new CampaignMessage();
        $this->leadCampaignModel = new LeadCampaign();
        $this->leadModel = new Lead();
        $this->whatsappModel = new WhatsAppMessage();
        $this->aisensy = new AiSensyService();
    }
    
    /**
     * Enroll lead in campaign
     */
    public function enrollLead($leadId, $campaignId) {
        // Get lead and campaign details
        $lead = $this->leadModel->getById($leadId);
        $campaign = $this->campaignModel->getById($campaignId);
        
        if (!$lead || !$campaign) {
            return ['success' => false, 'error' => 'Lead or campaign not found'];
        }
        
        // Check if lead is subscribed
        if (!$lead['is_subscribed']) {
            return ['success' => false, 'error' => 'Lead is unsubscribed'];
        }
        
        // Enroll lead
        $result = $this->leadCampaignModel->enroll($leadId, $campaignId);
        
        if ($result['success']) {
            logActivity('campaign_enrollment', 'lead_campaign', $result['id'], "Lead {$lead['name']} enrolled in campaign {$campaign['campaign_name']}");
        }
        
        return $result;
    }
    
    /**
     * Auto-enroll lead in active campaigns for their project
     */
    public function autoEnrollLead($leadId) {
        $lead = $this->leadModel->getById($leadId);
        
        if (!$lead || !$lead['is_subscribed']) {
            return ['success' => false, 'error' => 'Lead not found or unsubscribed'];
        }
        
        // Get active campaigns with auto-enroll enabled for this project
        $campaigns = $this->campaignModel->getActiveByProject($lead['project_id']);
        
        $enrolled = 0;
        foreach ($campaigns as $campaign) {
            $result = $this->enrollLead($leadId, $campaign['id']);
            if ($result['success']) {
                $enrolled++;
            }
        }
        
        return ['success' => true, 'enrolled_count' => $enrolled];
    }
    
    /**
     * Process due campaign messages (called by cron)
     */
    public function processDueMessages() {
        $dueMessages = $this->leadCampaignModel->getDueMessages();
        
        $sent = 0;
        $failed = 0;
        $completed = 0;
        
        foreach ($dueMessages as $message) {
            try {
                // Prepare merge data
                $mergeData = [
                    'name' => $message['name'],
                    'first_name' => explode(' ', $message['name'])[0],
                    'phone' => $message['phone'],
                    'email' => $message['email'] ?? '',
                    'project_name' => $message['project_name'],
                    'project_location' => $message['project_location'] ?? '',
                    'project_type' => $message['project_type'] ?? '',
                    'price_range' => $message['price_range'] ?? '',
                    'client_name' => $message['client_name'] ?? '',
                    'current_date' => date('F j, Y'),
                    'brochure_link' => $message['brochure_url'] ?? ''
                ];
                
                // Replace merge tags in message content
                $messageContent = replacePlaceholders($message['message_content'], $mergeData);
                
                // Add unsubscribe notice
                $messageContent .= "\n\nReply STOP to unsubscribe.";
                
                // Send message based on type
                $sendResult = null;
                
                switch ($message['message_type']) {
                    case 'image':
                    case 'video':
                    case 'document':
                        if ($message['media_url']) {
                            $sendResult = $this->aisensy->sendMediaMessage(
                                $message['phone'],
                                $message['media_url'],
                                $messageContent,
                                $message['message_type']
                            );
                        } else {
                            // Fallback to text if no media
                            $sendResult = $this->aisensy->sendTextMessage($message['phone'], $messageContent);
                        }
                        break;
                        
                    case 'template':
                        if ($message['template_id']) {
                            // Parse template params from merge data
                            $sendResult = $this->aisensy->sendTemplateMessage(
                                $message['phone'],
                                $message['template_id'],
                                array_values($mergeData)
                            );
                        } else {
                            // Fallback to text
                            $sendResult = $this->aisensy->sendTextMessage($message['phone'], $messageContent);
                        }
                        break;
                        
                    default: // text
                        $sendResult = $this->aisensy->sendTextMessage($message['phone'], $messageContent);
                        break;
                }
                
                if ($sendResult && $sendResult['success']) {
                    // Log message in database
                    $this->whatsappModel->log([
                        'lead_id' => $message['lead_id'],
                        'campaign_id' => $message['campaign_id'],
                        'message_id' => $sendResult['data']['messageId'] ?? null,
                        'message_type' => $message['message_type'],
                        'message_content' => $messageContent,
                        'media_url' => $message['media_url'] ?? null,
                        'status' => 'sent',
                        'direction' => 'outbound'
                    ]);
                    
                    // Update campaign progress
                    $newDay = $message['current_day'] + 1;
                    $this->leadCampaignModel->updateProgress($message['lead_id'], $message['campaign_id'], $newDay);
                    
                    // Check if campaign is complete
                    if ($newDay >= $message['duration_days']) {
                        $this->leadCampaignModel->markCompleted($message['lead_id'], $message['campaign_id']);
                        $completed++;
                    }
                    
                    $sent++;
                } else {
                    // Log failed message
                    $this->whatsappModel->log([
                        'lead_id' => $message['lead_id'],
                        'campaign_id' => $message['campaign_id'],
                        'message_type' => $message['message_type'],
                        'message_content' => $messageContent,
                        'status' => 'failed',
                        'direction' => 'outbound'
                    ]);
                    
                    $failed++;
                }
                
                // Small delay to avoid rate limiting
                usleep(200000); // 200ms delay
                
            } catch (Exception $e) {
                error_log("Campaign message processing error: " . $e->getMessage());
                $failed++;
            }
        }
        
        return [
            'success' => true,
            'messages_sent' => $sent,
            'messages_failed' => $failed,
            'campaigns_completed' => $completed
        ];
    }
    
    /**
     * Unsubscribe lead from all campaigns
     */
    public function unsubscribeLead($leadId) {
        // Update lead subscription status
        $this->leadModel->updateSubscription($leadId, 0);
        
        // Mark all active campaigns as unsubscribed
        $this->leadCampaignModel->unsubscribeAll($leadId);
        
        logActivity('lead_unsubscribed', 'lead', $leadId, "Lead unsubscribed from all campaigns");
        
        return ['success' => true];
    }
    
    /**
     * Resubscribe lead
     */
    public function resubscribeLead($leadId) {
        $this->leadModel->updateSubscription($leadId, 1);
        
        logActivity('lead_resubscribed', 'lead', $leadId, "Lead resubscribed");
        
        return ['success' => true];
    }
}
