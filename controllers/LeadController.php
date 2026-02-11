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
        
        $currentPage = $page;
        
        include BASE_PATH . 'views/leads/list.php';
    }
    
    /**
     * Import leads view
     */
    public function import() {
        requireLogin();
        $projects = $this->projectModel->getForDropdown();
        include BASE_PATH . 'views/leads/import.php';
    }

    /**
     * Process CSV import
     */
    public function processImport() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('leads/import');
            }

            if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
                setFlashMessage('Please upload a valid CSV file', 'error');
                redirect('leads/import');
            }

            $projectId = $_POST['project_id'];
            if (!$projectId) {
                setFlashMessage('Please select a project', 'error');
                redirect('leads/import');
            }

            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, "r");
            
            if ($handle === FALSE) {
                setFlashMessage('Could not read file', 'error');
                redirect('leads/import');
            }

            // Get headers
            $headers = fgetcsv($handle);
            if (!$headers) {
                setFlashMessage('Empty file', 'error');
                redirect('leads/import');
            }
            
            // Normalize headers
            $headers = array_map(function($h) {
                return strtolower(trim($h));
            }, $headers);

            $successCount = 0;
            $errorCount = 0;
            $rowNum = 1; // Start after header

            while (($data = fgetcsv($handle)) !== FALSE) {
                $rowNum++;
                // Pad data if row is shorter than headers
                if (count($data) < count($headers)) {
                    $data = array_pad($data, count($headers), '');
                }
                
                $row = array_combine($headers, $data);
                
                // Map fields checking for various common names
                $name = $row['name'] ?? null;
                
                // Prioritize Whatsapp Number, then Phone Number, then Phone
                $whatsapp = $row['whatsapp number'] ?? $row['whatsapp_number'] ?? null;
                $mobile = $row['phone number'] ?? $row['phone_number'] ?? $row['mobile'] ?? $row['phone'] ?? null;
                $phone = $whatsapp ?: $mobile;

                if (!$name || !$phone) {
                    $errorCount++;
                    continue;
                }

                // Clean and format phone number for consistent duplicate checking
                $phone = formatPhoneNumber($phone);
                
                // Duplicate check
                if ($this->leadModel->checkDuplicate($phone, $projectId)) {
                    $errorCount++; 
                    continue;
                }

                // Handle Date (MM/DD/YYYY or DD/MM/YYYY or similar)
                $createdAt = date('Y-m-d H:i:s'); // Default to now
                $dateStr = $row['date'] ?? null;
                if ($dateStr) {
                    $timestamp = strtotime($dateStr);
                    if ($timestamp) {
                        // If date is in the future (e.g., "30 Nov" parsed as current year 2026 but meant for 2025),
                        // subtract 1 year
                        if ($timestamp > time()) {
                            $timestamp = strtotime('-1 year', $timestamp);
                        }
                        $createdAt = date('Y-m-d H:i:s', $timestamp);
                    }
                }

                // Handle Status
                // "Lead Status 2" seems to contain the system status (Call Back, Qualified, etc)
                // "Lead Status 1" is more descriptive
                $statusRaw = $row['lead status 2'] ?? $row['lead_status_2'] ?? 
                             $row['lead status 1'] ?? $row['lead_status_1'] ?? 
                             $row['status'] ?? 'new';
                             
                $statusMap = [
                    'call back' => 'contacted',
                    'qualified' => 'qualified',
                    'disqualified' => 'disqualified',
                    'not responding' => 'contacted',
                    'interested' => 'interested',
                    'converted' => 'won',
                    'lost' => 'lost'
                ];
                
                $status = $statusMap[strtolower(trim($statusRaw))] ?? 'new';

                // Notes - Combine extra info
                $notesParts = [];
                if (!empty($row['notes'])) $notesParts[] = $row['notes'];
                if (!empty($row['lead status 1'])) $notesParts[] = "Status Detail: " . $row['lead status 1'];
                if (!empty($row['feedback 1'])) $notesParts[] = "Feedback 1: " . $row['feedback 1'];
                if (!empty($row['feedback 2'])) $notesParts[] = "Feedback 2: " . $row['feedback 2'];
                if (!empty($row['remarks'])) $notesParts[] = "Remarks: " . $row['remarks'];
                
                $notes = implode("\n", $notesParts);

                // Email
                $email = $row['email id'] ?? $row['email'] ?? '';

                $leadData = [
                    'project_id' => $projectId,
                    'name' => sanitizeInput($name),
                    'phone' => sanitizeInput($phone),
                    'email' => sanitizeInput($email),
                    'budget' => sanitizeInput($row['budget'] ?? ''),
                    'status' => $status,
                    'notes' => sanitizeInput($notes),
                    'source' => 'import',
                    'created_at' => $createdAt
                ];

                $result = $this->leadModel->create($leadData);
                if ($result['success']) {
                    $successCount++;
                } else {
                    $errorCount++;
                }
            }
            
            fclose($handle);
            
            $msg = "Import completed: $successCount leads imported successfully.";
            if ($errorCount > 0) {
                $msg .= " $errorCount rows skipped (duplicates or missing data).";
            }
            
            setFlashMessage($msg, $successCount > 0 ? 'success' : 'warning');
            redirect('leads');
        }
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
                'source' => !empty($_POST['source']) ? sanitizeInput($_POST['source']) : 'manual',
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
    
    /**
     * Send custom WhatsApp message to a lead
     * AJAX endpoint
     * Supports both WhatsApp Web and AiSensy (with fallback)
     */
    public function sendCustomMessage() {
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json(['success' => false, 'error' => 'Invalid request method'], 405);
        }
        
        // Get JSON payload
        $input = json_decode(file_get_contents('php://input'), true);
        
        $leadId = $input['lead_id'] ?? null;
        $message = $input['message'] ?? null;
        $useWhatsAppWeb = $input['use_whatsapp_web'] ?? true; // Default to WhatsApp Web
        
        if (!$leadId || !$message) {
            json(['success' => false, 'error' => 'Lead ID and message are required'], 400);
        }
        
        // Get lead details
        $lead = $this->leadModel->getById($leadId);
        if (!$lead) {
            json(['success' => false, 'error' => 'Lead not found'], 404);
        }
        
        $result = [];
        
        // Try WhatsApp Web first (if enabled)
        if ($useWhatsAppWeb) {
            require_once __DIR__ . '/../services/WhatsAppWebService.php';
            $whatsappWebService = new WhatsAppWebService();
            
            // Check if WhatsApp Web service is available
            if ($whatsappWebService->isAvailable()) {
                $result = $whatsappWebService->sendMessage(
                    $lead['phone'],
                    $message,
                    true // Queue if busy
                );
                
                if ($result['success'] || isset($result['queued'])) {
                    // Log the message
                    $this->messageModel->create([
                        'lead_id' => $leadId,
                        'message_type' => 'custom_whatsapp_web',
                        'content' => $message,
                        'sent_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    json([
                        'success' => true,
                        'method' => 'WhatsApp Web',
                        'queued' => isset($result['queued']) ? $result['queued'] : false,
                        'message' => isset($result['queued']) && $result['queued'] 
                            ? 'Message queued for delivery'
                            : 'Message sent successfully via WhatsApp Web!'
                    ]);
                }
            }
        }
        
        // Fallback to AiSensy if WhatsApp Web failed or not enabled
        require_once __DIR__ . '/../services/LeadNotificationService.php';
        $notificationService = new LeadNotificationService();
        
        $result = $notificationService->sendCustomMessage(
            $lead['phone'],
            $lead['name'],
            $message,
            'crm_custom_nurture'
        );
        
        if ($result['success']) {
            // Log the message
            $this->messageModel->create([
                'lead_id' => $leadId,
                'message_type' => 'custom_aisensy',
                'content' => $message,
                'sent_at' => date('Y-m-d H:i:s')
            ]);
            
            json([
                'success' => true,
                'method' => 'AiSensy',
                'message' => 'Message sent successfully via AiSensy!'
            ]);
        } else {
            json([
                'success' => false,
                'error' => $result['error'] ?? 'Failed to send message via any method'
            ], 500);
        }
    }
}

