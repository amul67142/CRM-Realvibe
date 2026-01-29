<?php
/**
 * Lead Controller
 * Handles lead management, conversation view, and messaging
 */

require_once __DIR__ . '/../models/Lead.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/WhatsAppMessage.php';
require_once __DIR__ . '/../models/LeadReply.php';
require_once __DIR__ . '/../services/AiSensyService.php';

class LeadController {
    private $leadModel;
    private $projectModel;
    private $messageModel;
    private $replyModel;
    
    public function __construct() {
        $this->leadModel = new Lead();
        $this->projectModel = new Project();
        $this->messageModel = new WhatsAppMessage();
        $this->replyModel = new LeadReply();
    }
    
    /**
     * List all leads
     */
    public function list() {
        requireLogin();
        
        // Get filters from query string
        $filters = [];
        if (isset($_GET['project_id']) && !empty($_GET['project_id'])) {
            $filters['project_id'] = $_GET['project_id'];
        }
        if (isset($_GET['source']) && !empty($_GET['source'])) {
            $filters['source'] = $_GET['source'];
        }
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }
        
        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        // Get leads
        $leads = $this->leadModel->getAll($filters, $perPage, $offset);
        $totalLeads = $this->leadModel->count($filters);
        
        // Get projects for filter dropdown
        $projects = $this->projectModel->getForDropdown();
        
        $data = [
            'leads' => $leads,
            'projects' => $projects,
            'filters' => $filters,
            'currentPage' => $page,
            'totalLeads' => $totalLeads,
            'perPage' => $perPage
        ];
        
        include BASE_PATH . 'views/leads/list.php';
    }
    
    /**
     * Create new lead
     */
    public function create() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate CSRF
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('leads/create');
            }
            
            $data = [
                'project_id' => $_POST['project_id'],
                'name' => sanitizeInput($_POST['name']),
                'phone' => sanitizeInput($_POST['phone']),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'source' => 'manual',
                'status' => $_POST['status'] ?? 'new',
                'budget' => sanitizeInput($_POST['budget'] ?? ''),
                'notes' => sanitizeInput($_POST['notes'] ?? '')
            ];
            
            $result = $this->leadModel->create($data);
            
            if ($result['success']) {
                // Send welcome message
                $lead = $this->leadModel->getById($result['id']);
                $project = $this->projectModel->getById($data['project_id']);
                
                if ($project && $project['welcome_message']) {
                    $aisensy = new AiSensyService();
                    $aisensy->sendWelcomeMessage($lead, $project);
                }
                
                // Send lead alert notifications to client and admin
                try {
                    require_once __DIR__ . '/../services/LeadNotificationService.php';
                    $notificationService = new LeadNotificationService();
                    $notificationService->sendNewLeadAlert($lead, $project);
                } catch (Exception $e) {
                    error_log("Lead notification error: " . $e->getMessage());
                }
                
                // Create in-app notification
                require_once __DIR__ . '/../models/Notification.php';
                $notification = new Notification();
                $notification->create([
                    'user_id' => null, // Global notification for all users
                    'type' => 'new_lead',
                    'title' => 'New Lead',
                    'message' => "New lead from {$lead['name']} for {$project['project_name']}",
                    'link' => 'leads/' . $lead['id'],
                    'icon' => 'user-plus'
                ]);
                
                // Auto-enroll in campaigns
                require_once __DIR__ . '/../services/CampaignService.php';
                $campaignService = new CampaignService();
                $campaignService->autoEnrollLead($result['id']);
                
                setFlashMessage('Lead created successfully!', 'success');
                redirect('leads/' . $result['id']);
            } else {
                setFlashMessage($result['message'] ?? 'Failed to create lead', 'error');
                storeOldInput();
                redirect('leads/create');
            }
        }
        
        // Show form
        $projects = $this->projectModel->getForDropdown();
        include BASE_PATH . 'views/leads/create.php';
    }
    
    /**
     * View lead details
     */
    public function view() {
        requireLogin();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            setFlashMessage('Lead not found', 'error');
            redirect('leads');
        }
        
        include BASE_PATH . 'views/leads/view.php';
    }
    
    /**
     * View conversation
     */
    public function conversation() {
        requireLogin();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('leads');
        }
        
        $lead = $this->leadModel->getById($id);
        if (!$lead) {
            setFlashMessage('Lead not found', 'error');
            redirect('leads');
        }
        
        // Get conversation
        $conversation = $this->messageModel->getConversation($id);
        
        // Mark replies as read
        $this->replyModel->markAllAsRead($id);
        
        include BASE_PATH . 'views/leads/conversation.php';
    }
    
    /**
     * Send message (AJAX)
     */
    public function sendMessage() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json(['success' => false, 'error' => 'Invalid request'], 405);
        }
        
        $leadId = $_POST['lead_id'] ?? null;
        $message = $_POST['message'] ?? '';
        
        if (!$leadId || empty($message)) {
            json(['success' => false, 'error' => 'Missing required fields'], 400);
        }
        
        $lead = $this->leadModel->getById($leadId);
        if (!$lead) {
            json(['success' => false, 'error' => 'Lead not found'], 404);
        }
        
        // Send via AiSensy
        $aisensy = new AiSensyService();
        $result = $aisensy->sendTextMessage($lead['phone'], $message);
        
        if ($result['success']) {
            // Log message
            $this->messageModel->log([
                'lead_id' => $leadId,
                'message_content' => $message,
                'status' => 'sent',
                'direction' => 'outbound',
                'message_id' => $result['data']['messageId'] ?? null
            ]);
            
            json(['success' => true, 'message' => 'Message sent successfully']);
        } else {
            json(['success' => false, 'error' => $result['message'] ?? 'Failed to send message'], 500);
        }
    }
    
    /**
     * Update lead status (AJAX)
     */
    public function updateStatus() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json(['success' => false, 'error' => 'Invalid request'], 405);
        }
        
        $leadId = $_POST['lead_id'] ?? null;
        $status = $_POST['status'] ?? '';
        
        if (!$leadId || empty($status)) {
            json(['success' => false, 'error' => 'Missing required fields'], 400);
        }
        
        if ($this->leadModel->updateStatus($leadId, $status)) {
            json(['success' => true, 'message' => 'Status updated successfully']);
        } else {
            json(['success' => false, 'error' => 'Failed to update status'], 500);
        }
    }
    
    /**
     * Delete lead
     */
    public function delete() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('You do not have permission to delete leads', 'error');
            redirect('leads');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('leads');
        }
        
        if ($this->leadModel->delete($id)) {
            setFlashMessage('Lead deleted successfully', 'success');
        } else {
            setFlashMessage('Failed to delete lead', 'error');
        }
        
        redirect('leads');
    }
}
