<?php
/**
 * Dashboard Controller
 * Main dashboard with analytics
 */

require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Campaign.php';
require_once __DIR__ . '/../models/LeadReply.php';
require_once __DIR__ . '/../models/WhatsAppMessage.php';

class DashboardController {
    private $leadModel;
    private $campaignModel;
    private $replyModel;
    private $messageModel;
    
    public function __construct() {
        $this->leadModel = new Lead();
        $this->campaignModel = new Campaign();
        $this->replyModel = new LeadReply();
        $this->messageModel = new WhatsAppMessage();
    }
    
    /**
     * Show dashboard
     */
    public function index() {
        requireLogin();
        
        // Get statistics
        $stats = [
            'total_leads' => $this->leadModel->count(),
            'leads_today' => $this->leadModel->getCountToday(),
            'active_campaigns' => $this->campaignModel->countActive(),
            'unread_replies' => $this->replyModel->countUnread()
        ];
        
        // Get leads by source
        $leadsBySource = $this->leadModel->getCountBySource();
        
        // Get leads by status
        $leadsByStatus = $this->leadModel->getCountByStatus();
        
        // Get recent leads
        $recentLeads = $this->leadModel->getRecent(10);
        
        // Get unread replies
        $unreadReplies = $this->replyModel->getRecentUnread(5);
        
        // Get message statistics
        $messageStats = [
            'sent_today' => $this->messageModel->countToday()
        ];
        
        // Get leads trend (last 30 days)
        $leadsTrend = $this->getLeadsTrend(30);
        
        $data = [
            'stats' => $stats,
            'leadsBySource' => $leadsBySource,
            'leadsByStatus' => $leadsByStatus,
            'recentLeads' => $recentLeads,
            'unreadReplies' => $unreadReplies,
            'messageStats' => $messageStats,
            'leadsTrend' => $leadsTrend
        ];
        
        include BASE_PATH . 'views/dashboard/index.php';
    }
    
    /**
     * Get leads trend data for chart
     */
    private function getLeadsTrend($days = 30) {
        $db = getDatabaseConnection();
        
        $stmt = $db->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM leads
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
        
        $stmt->execute([$days]);
        $results = $stmt->fetchAll();
        
        // Format for chart
        $trend = [];
        foreach ($results as $row) {
            $trend[] = [
                'date' => date('M d', strtotime($row['date'])),
                'count' => (int)$row['count']
            ];
        }
        
        return $trend;
    }
}
