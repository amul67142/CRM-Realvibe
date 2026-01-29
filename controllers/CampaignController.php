<?php
/**
 * Campaign Controller
 * Manages nurturing campaigns
 */

require_once __DIR__ . '/../models/Campaign.php';
require_once __DIR__ . '/../models/CampaignMessage.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/LeadCampaign.php';
require_once __DIR__ . '/../services/CampaignService.php';

class CampaignController {
    private $campaignModel;
    private $messageModel;
    private $projectModel;
    private $leadCampaignModel;
    
    public function __construct() {
        $this->campaignModel = new Campaign();
        $this->messageModel = new CampaignMessage();
        $this->projectModel = new Project();
        $this->leadCampaignModel = new LeadCampaign();
    }
    
    /**
     * List all campaigns
     */
    public function list() {
        requireLogin();
        
        $filters = [];
        if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
            $filters['project_id'] = $_GET['project_id'];
        }
        
        $campaigns = $this->campaignModel->getAll($filters);
        $projects = $this->projectModel->getForDropdown();
        
        $data = [
            'campaigns' => $campaigns,
            'projects' => $projects,
            'filters' => $filters
        ];
        
        include BASE_PATH . 'views/campaigns/list.php';
    }
    
    /**
     * Create new campaign
     */
    public function create() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('Access denied', 'error');
            redirect('campaigns');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('campaigns/create');
            }
            
            $data = [
                'project_id' => $_POST['project_id'],
                'campaign_name' => sanitizeInput($_POST['campaign_name']),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'duration_days' => (int)$_POST['duration_days'] ?? 5,
                'is_active' => 0, // Start as inactive
                'auto_enroll' => isset($_POST['auto_enroll']) ? 1 : 0
            ];
            
            $campaignId = $this->campaignModel->create($data);
            
            if ($campaignId) {
                setFlashMessage('Campaign created successfully! Now add messages to it.', 'success');
                redirect('campaigns/builder?id=' . $campaignId);
            } else {
                setFlashMessage('Failed to create campaign', 'error');
                storeOldInput();
                redirect('campaigns/create');
            }
        }
        
        $projects = $this->projectModel->getForDropdown();
        include BASE_PATH . 'views/campaigns/create.php';
    }
    
    /**
     * Campaign builder (add/edit messages)
     */
    public function builder() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('Access denied', 'error');
            redirect('campaigns');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('campaigns');
        }
        
        $campaign = $this->campaignModel->getById($id);
        if (!$campaign) {
            setFlashMessage('Campaign not found', 'error');
            redirect('campaigns');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('campaigns/builder?id=' . $id);
            }
            
            // Get messages data from form
            $messages = [];
            $dayCount = (int)$campaign['duration_days'];
            
            for ($day = 1; $day <= $dayCount; $day++) {
                if (!empty($_POST["message_content_$day"])) {
                    $messages[] = [
                        'day_number' => $day,
                        'message_type' => $_POST["message_type_$day"] ?? 'text',
                        'message_content' => $_POST["message_content_$day"],
                        'media_url' => $_POST["media_url_$day"] ?? null,
                        'send_time' => $_POST["send_time_$day"] ?? '10:00:00'
                    ];
                }
            }
            
            if ($this->messageModel->bulkCreate($id, $messages)) {
                setFlashMessage('Campaign messages saved successfully!', 'success');
                redirect('campaigns');
            } else {
                setFlashMessage('Failed to save campaign messages', 'error');
                redirect('campaigns/builder?id=' . $id);
            }
        }
        
        // Get existing messages
        $messages = $this->messageModel->getByCampaign($id);
        
        $data = [
            'campaign' => $campaign,
            'messages' => $messages
        ];
        
        include BASE_PATH . 'views/campaigns/builder.php';
    }
    
    /**
     * Campaign analytics
     */
    public function analytics() {
        requireLogin();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('campaigns');
        }
        
        $campaign = $this->campaignModel->getById($id);
        if (!$campaign) {
            setFlashMessage('Campaign not found', 'error');
            redirect('campaigns');
        }
        
        $analytics = $this->campaignModel->getAnalytics($id);
        $enrollments = $this->leadCampaignModel->getByCampaign($id);
        
        $data = [
            'campaign' => $campaign,
            'analytics' => $analytics,
            'enrollments' => $enrollments
        ];
        
        include BASE_PATH . 'views/campaigns/analytics.php';
    }
    
    /**
     * Toggle campaign status (activate/deactivate)
     */
    public function toggleStatus() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            json(['success' => false, 'error' => 'Access denied'], 403);
        }
        
        $id = $_POST['campaign_id'] ?? null;
        if (!$id) {
            json(['success' => false, 'error' => 'Missing campaign ID'], 400);
        }
        
        if ($this->campaignModel->toggleStatus($id)) {
            json(['success' => true, 'message' => 'Campaign status updated']);
        } else {
            json(['success' => false, 'error' => 'Failed to update status'], 500);
        }
    }
    
    /**
     * Delete campaign
     */
    public function delete() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            setFlashMessage('Access denied', 'error');
            redirect('campaigns');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('campaigns');
        }
        
        if ($this->campaignModel->delete($id)) {
            setFlashMessage('Campaign deleted successfully', 'success');
        } else {
            setFlashMessage('Failed to delete campaign', 'error');
        }
        
        redirect('campaigns');
    }
}
