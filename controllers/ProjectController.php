<?php
/**
 * Project Controller
 * Manages real estate projects
 */

require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../models/Client.php';

class ProjectController {
    private $projectModel;
    private $clientModel;
    
    public function __construct() {
        $this->projectModel = new Project();
        $this->clientModel = new Client();
    }
    
    /**
     * List all projects
     */
    public function list() {
        requireLogin();
        
        $filters = [];
        if (isset($_GET['client_id']) && !empty($_GET['client_id'])) {
            $filters['client_id'] = $_GET['client_id'];
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        $projects = $this->projectModel->getAll($filters, $perPage, $offset);
        $totalProjects = $this->projectModel->count($filters);
        $clients = $this->clientModel->getAll();
        
        $data = [
            'projects' => $projects,
            'clients' => $clients,
            'filters' => $filters,
            'currentPage' => $page,
            'totalProjects' => $totalProjects,
            'perPage' => $perPage
        ];
        
        include BASE_PATH . 'views/projects/list.php';
    }
    
    /**
     * Create new project
     */
    public function create() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('Access denied', 'error');
            redirect('dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('projects/create');
            }
            
            $data = [
                'client_id' => $_POST['client_id'],
                'project_name' => sanitizeInput($_POST['project_name']),
                'project_type' => sanitizeInput($_POST['project_type'] ?? ''),
                'location' => sanitizeInput($_POST['location'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'welcome_message' => sanitizeInput($_POST['welcome_message'] ?? ''),
                'whatsapp_provider' => sanitizeInput($_POST['whatsapp_provider'] ?? 'default'),
                'aisensy_campaign_name' => sanitizeInput($_POST['aisensy_campaign_name'] ?? ''),
                'price_range' => sanitizeInput($_POST['price_range'] ?? ''),
                'brochure_url' => sanitizeInput($_POST['brochure_url'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            // Validate AiSensy campaign name if AiSensy is selected
            if ($data['whatsapp_provider'] === 'aisensy' && empty($data['aisensy_campaign_name'])) {
                setFlashMessage('AiSensy campaign name is required when using AiSensy provider', 'error');
                storeOldInput();
                redirect('projects/create');
            }
            
            $projectId = $this->projectModel->create($data);
            
            if ($projectId) {
                setFlashMessage('Project created successfully!', 'success');
                redirect('projects');
            } else {
                setFlashMessage('Failed to create project', 'error');
                storeOldInput();
                redirect('projects/create');
            }
        }
        
        $clients = $this->clientModel->getAll();
        include BASE_PATH . 'views/projects/create.php';
    }
    
    /**
     * Edit project
     */
    public function edit() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('Access denied', 'error');
            redirect('dashboard');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('projects');
        }
        
        $project = $this->projectModel->getById($id);
        if (!$project) {
            setFlashMessage('Project not found', 'error');
            redirect('projects');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('projects/edit?id=' . $id);
            }
            
            $data = [
                'client_id' => $_POST['client_id'],
                'project_name' => sanitizeInput($_POST['project_name']),
                'project_type' => sanitizeInput($_POST['project_type'] ?? ''),
                'location' => sanitizeInput($_POST['location'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'welcome_message' => sanitizeInput($_POST['welcome_message'] ?? ''),
                'whatsapp_provider' => sanitizeInput($_POST['whatsapp_provider'] ?? 'default'),
                'aisensy_campaign_name' => sanitizeInput($_POST['aisensy_campaign_name'] ?? ''),
                'price_range' => sanitizeInput($_POST['price_range'] ?? ''),
                'brochure_url' => sanitizeInput($_POST['brochure_url'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            // Validate AiSensy campaign name if AiSensy is selected
            if ($data['whatsapp_provider'] === 'aisensy' && empty($data['aisensy_campaign_name'])) {
                setFlashMessage('AiSensy campaign name is required when using AiSensy provider', 'error');
                redirect('projects/edit?id=' . $id);
            }
            
            if ($this->projectModel->update($id, $data)) {
                setFlashMessage('Project updated successfully!', 'success');
                redirect('projects');
            } else {
                setFlashMessage('Failed to update project', 'error');
                redirect('projects/edit?id=' . $id);
            }
        }
        
        $clients = $this->clientModel->getAll();
        include BASE_PATH . 'views/projects/edit.php';
    }
    
    /**
     * Delete project
     */
    public function delete() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            setFlashMessage('Access denied', 'error');
            redirect('projects');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('projects');
        }
        
        if ($this->projectModel->delete($id)) {
            setFlashMessage('Project deleted successfully', 'success');
        } else {
            setFlashMessage('Failed to delete project', 'error');
        }
        
        redirect('projects');
    }
}
