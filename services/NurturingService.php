<?php
/**
 * Nurturing Service
 * Handles automated WhatsApp nurturing via WhatsApp Web
 */

require_once __DIR__  . '/../config/config.php';
require_once __DIR__ . '/WhatsAppWebService.php';

class NurturingService {
    private $db;
    private $whatsapp;
    private $logFile;
    
    // Timing configuration
    private $startHour = 10; // 10 AM
    private $endHour = 18;   // 6 PM
    private $minDelayHours = 2; // Minimum 2 hours between messages to same lead
    private $defaultDailyLimit = 30;
    
    public function __construct() {
        $this->db = getDatabaseConnection();
        $this->whatsapp = new WhatsAppWebService();
        $this->logFile = BASE_PATH . 'logs/nurturing.log';
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    /**
     * Process all active campaigns
     * Called by cron job
     */
    public function processNurturing() {
        $this->log("========== Starting nurturing process ==========");
        
        // Check if WhatsApp is available
        if (!$this->whatsapp->isAvailable()) {
            $this->log("ERROR: WhatsApp Web not available. Skipping.");
            return false;
        }
        
        // Check if we're within sending hours
        $currentHour = (int)date('H');
        if ($currentHour < $this->startHour || $currentHour >= $this->endHour) {
            $this->log("Outside sending hours ($this->startHour-$this->endHour). Current: $currentHour. Skipping.");
            return false;
        }
        
        // Get all active campaigns
        $campaigns = $this->getActiveCampaigns();
        $this->log("Found " . count($campaigns) . " active campaigns");
        
        $totalSent = 0;
        
        foreach ($campaigns as $campaign) {
            $sent = $this->processCampaign($campaign);
            $totalSent += $sent;
            
            // Check if we've hit daily limit across all campaigns
            if ($totalSent >= $this->defaultDailyLimit) {
                $this->log("Daily limit reached ($this->defaultDailyLimit). Stopping.");
                break;
            }
        }
        
        $this->log("========== Nurturing process complete. Sent: $totalSent ==========");
        return $totalSent;
    }
    
    /**
     * Process a single campaign
     */
    private function processCampaign($campaign) {
        $campaignId = $campaign['id'];
        $campaignName = $campaign['campaign_name'];
        $dailyLimit = $campaign['daily_message_limit'] ?? $this->defaultDailyLimit;
        
        $this->log("Processing campaign: $campaignName (ID: $campaignId)");
        
        // Get leads ready for next message
        $leads = $this->getLeadsForNextMessage($campaignId);
        $this->log("  Found " . count($leads) . " leads ready for messages");
        
        $sent = 0;
        
        foreach ($leads as $lead) {
            // Check campaign daily limit
            if ($this->getCampaignMessagesToday($campaignId) >= $dailyLimit) {
                $this->log("  Campaign daily limit reached ($dailyLimit). Moving to next campaign.");
                break;
            }
            
            // Send next message
            if ($this->sendNextMessage($campaignId, $lead)) {
                $sent++;
                
                // Add delay between messages (3-7 seconds as configured in WhatsApp service)
                sleep(1);
            }
        }
        
        $this->log("  Campaign complete. Sent: $sent messages");
        return $sent;
    }
    
    /**
     * Send next message in sequence to a lead
     */
    public function sendNextMessage($campaignId, $lead) {
        $leadId = is_array($lead) ? $lead['lead_id'] : $lead;
        $currentIndex = is_array($lead) ? ($lead['current_message_index'] ?? 0) : 0;
        
        $this->log("  Sending message to lead ID: $leadId (index: $currentIndex)");
        
        try {
            // Get next message
            $message = $this->getMessageAtIndex($campaignId, $currentIndex);
            
            if (!$message) {
                // No more messages - mark as completed
                $this->completeLeadNurturing($campaignId, $leadId);
                $this->log("    No more messages. Lead completed.");
                return false;
            }
            
            // Get lead details
            $leadInfo = $this->getLeadInfo($leadId);
            if (!$leadInfo) {
                $this->log("    ERROR: Lead not found");
                return false;
            }
            
            // Prepare message content (replace variables)
            $content = $this->prepareMessageContent($message['message_content'], $leadInfo);
            
            // Send via WhatsApp Web
            $result = $this->whatsapp->sendMessage($leadInfo['phone'], $content, true);
            
            if ($result['success'] || isset($result['queued'])) {
                // Update lead progress
                $this->updateLeadProgress($campaignId, $leadId, $currentIndex + 1);
                
                // Log to nurturing_log
                $this->logNurturingAction($campaignId, $leadId, 'message_sent', $currentIndex, 
                    'Message sent: ' . substr($content, 0, 50) . '...');
                
                $this->log("    ✓ Message sent successfully" . (isset($result['queued']) ? ' (queued)' : ''));
                return true;
            } else {
                $this->log("    ✗ Failed: " . ($result['error'] ?? 'Unknown error'));
                return false;
            }
            
        } catch (Exception $e) {
            $this->log("    ERROR: " . $e->getMessage());
            $this->logNurturingAction($campaignId, $leadId, 'error', $currentIndex, $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send welcome message immediately
     */
    public function sendWelcomeMessage($leadId, $campaignId) {
        $this->log("Sending welcome message to lead $leadId for campaign $campaignId");
        
        try {
            // Get campaign details
            $campaign = $this->getCampaign($campaignId);
            if (!$campaign || !$campaign['auto_send_welcome']) {
                return false;
            }
            
            // Get lead info
            $leadInfo = $this->getLeadInfo($leadId);
            if (!$leadInfo) {
                return false;
            }
            
            // Get welcome message
            $welcomeMsg = $campaign['welcome_message'];
            if (empty($welcomeMsg)) {
                // Default welcome message
                $welcomeMsg = "Hi {name}! 👋\n\n" .
                             "Thanks for connecting with us. We've received your details and our team will update you shortly.\n\n" .
                             "Looking forward to helping you find your dream home!\n\n" .
                             "Best regards,\nRealvibe Team";
            }
            
            // Replace variables
            $content = $this->prepareMessageContent($welcomeMsg, $leadInfo);
            
            // Send via WhatsApp Web
            $result = $this->whatsapp->sendMessage($leadInfo['phone'], $content, false); // Send immediately, don't queue
            
            if ($result['success']) {
                // Update welcome_sent_at
                $this->markWelcomeSent($campaignId, $leadId);
                
                // Log action
                $this->logNurturingAction($campaignId, $leadId, 'welcome_sent', null, 'Welcome message sent');
                
                $this->log("  ✓ Welcome message sent to {$leadInfo['name']}");
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            $this->log("ERROR sending welcome: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if lead can receive message today
     */
    public function canSendToday($campaignId, $leadId) {
        $stmt = $this->db->prepare("
            SELECT last_message_sent_at 
            FROM campaign_leads 
            WHERE campaign_id = ? AND lead_id = ?
        ");
        $stmt->execute([$campaignId, $leadId]);
        $result = $stmt->fetch();
        
        if (!$result || !$result['last_message_sent_at']) {
            return true; // Never sent before
        }
        
        $lastSent = strtotime($result['last_message_sent_at']);
        $minWait = $this->minDelayHours * 3600; // Convert to seconds
        
        return (time() - $lastSent) >= $minWait;
    }
    
    /**
     * Get leads ready for next message
     */
    public function getLeadsForNextMessage($campaignId) {
        $minWaitTime = date('Y-m-d H:i:s', time() - ($this->minDelayHours * 3600));
        
        $stmt = $this->db->prepare("
            SELECT cl.*, l.name, l.phone, l.email
            FROM campaign_leads cl
            JOIN leads l ON cl.lead_id = l.id
            JOIN campaign_messages cm ON cm.campaign_id = cl.campaign_id 
                AND cm.day_number = (cl.current_message_index + 1)
            WHERE cl.campaign_id = ?
                AND cl.status = 'active'
                AND cl.welcome_sent_at IS NOT NULL
                AND (cl.last_message_sent_at IS NULL OR cl.last_message_sent_at < ?)
            ORDER BY cl.last_message_sent_at ASC
            LIMIT 100
        ");
        
        $stmt->execute([$campaignId, $minWaitTime]);
        return $stmt->fetchAll();
    }
    
    /**
     * Helper: Get active campaigns
     */
    private function getActiveCampaigns() {
        $stmt = $this->db->query("
            SELECT * FROM campaigns 
            WHERE status = 'active' 
                AND whatsapp_method IN ('whatsapp_web', 'both')
            ORDER BY id ASC
        ");
        return $stmt->fetchAll();
    }
    
    /**
     * Helper: Get message at specific index
     */
    private function getMessageAtIndex($campaignId, $index) {
        $stmt = $this->db->prepare("
            SELECT * FROM campaign_messages 
            WHERE campaign_id = ? AND day_number = ?
        ");
        $stmt->execute([$campaignId, $index + 1]); // day_number is 1-indexed
        return $stmt->fetch();
    }
    
    /**
     * Helper: Get lead info
     */
    private function getLeadInfo($leadId) {
        $stmt = $this->db->prepare("SELECT * FROM leads WHERE id = ?");
        $stmt->execute([$leadId]);
        return $stmt->fetch();
    }
    
    /**
     * Helper: Get campaign
     */
    private function getCampaign($campaignId) {
        $stmt = $this->db->prepare("SELECT * FROM campaigns WHERE id = ?");
        $stmt->execute([$campaignId]);
        return $stmt->fetch();
    }
    
    /**
     * Helper: Prepare message content with variable replacement
     */
    private function prepareMessageContent($template, $leadInfo) {
        $replacements = [
            '{name}' => $leadInfo['name'],
            '{phone}' => $leadInfo['phone'],
            '{email}' => $leadInfo['email'] ?? '',
            '{first_name}' => explode(' ', $leadInfo['name'])[0]
        ];
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    /**
     * Helper: Update lead progress
     */
    private function updateLeadProgress($campaignId, $leadId, $newIndex) {
        $stmt = $this->db->prepare("
            UPDATE campaign_leads 
            SET current_message_index = ?,
                last_message_sent_at = NOW(),
                updated_at = NOW()
            WHERE campaign_id = ? AND lead_id = ?
        ");
        return $stmt->execute([$newIndex, $campaignId, $leadId]);
    }
    
    /**
     * Helper: Complete lead nurturing
     */
    private function completeLeadNurturing($campaignId, $leadId) {
        $stmt = $this->db->prepare("
            UPDATE campaign_leads 
            SET status = 'completed',
                completed_at = NOW(),
                updated_at = NOW()
            WHERE campaign_id = ? AND lead_id = ?
        ");
        
        $result = $stmt->execute([$campaignId, $leadId]);
        
        if ($result) {
            $this->logNurturingAction($campaignId, $leadId, 'completed', null, 'Nurturing sequence completed');
        }
        
        return $result;
    }
    
    /**
     * Helper: Mark welcome as sent
     */
    private function markWelcomeSent($campaignId, $leadId) {
        $stmt = $this->db->prepare("
            UPDATE campaign_leads 
            SET welcome_sent_at = NOW(),
                started_at = NOW(),
                status = 'active',
                updated_at = NOW()
            WHERE campaign_id = ? AND lead_id = ?
        ");
        return $stmt->execute([$campaignId, $leadId]);
    }
    
    /**
     * Helper: Get messages sent today for campaign
     */
    private function getCampaignMessagesToday($campaignId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM nurturing_log 
            WHERE campaign_id = ? 
                AND action_type = 'message_sent'
                AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$campaignId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
    
    /**
     * Helper: Log nurturing action
     */
    private function logNurturingAction($campaignId, $leadId, $actionType, $messageIndex = null, $details = null) {
        $stmt = $this->db->prepare("
            INSERT INTO nurturing_log (campaign_id, lead_id, action_type, message_index, details)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$campaignId, $leadId, $actionType, $messageIndex, $details]);
    }
    
    /**
     * Helper: Log to file
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
