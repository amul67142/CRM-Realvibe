<?php
/**
 * Meta Lead Forms Controller
 * Handles CRUD operations for Meta Lead Form mappings
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/MetaLeadForm.php';
require_once __DIR__ . '/../models/Project.php';
require_once __DIR__ . '/../services/MetaLeadsService.php';

class MetaLeadFormsController {
    private $metaLeadFormModel;
    private $projectModel;
    private $metaService;
    
    public function __construct() {
        $this->metaLeadFormModel = new MetaLeadForm();
        $this->projectModel = new Project();
        $this->metaService = new MetaLeadsService();
    }
    
    /**
     * Add new form mapping to project
     */
    public function addFormToProject() {
        header('Content-Type: application/json');
        
        try {
            $projectId = $_POST['project_id'] ?? null;
            $formId = $_POST['form_id'] ?? null;
            $formName = $_POST['form_name'] ?? null;
            
            if (!$projectId || !$formId) {
                throw new Exception('Project ID and Form ID are required');
            }
            
            // Check if form ID already exists
            if ($this->metaLeadFormModel->formIdExists($formId)) {
                throw new Exception('This Form ID is already mapped to another project');
            }
            
            // Create the mapping
            $result = $this->metaLeadFormModel->create($projectId, $formId, $formName);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Form added successfully'
                ]);
            } else {
                throw new Exception('Failed to add form mapping');
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Remove form mapping from project
     */
    public function removeFormFromProject() {
        header('Content-Type: application/json');
        
        try {
            $formMappingId = $_POST['form_mapping_id'] ?? null;
            
            if (!$formMappingId) {
                throw new Exception('Form mapping ID is required');
            }
            
            $result = $this->metaLeadFormModel->delete($formMappingId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Form removed successfully'
                ]);
            } else {
                throw new Exception('Failed to remove form mapping');
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Toggle form active status
     */
    public function toggleFormStatus() {
        header('Content-Type: application/json');
        
        try {
            $formMappingId = $_POST['form_mapping_id'] ?? null;
            $isActive = $_POST['is_active'] ?? null;
            
            if ($formMappingId === null || $isActive === null) {
                throw new Exception('Form mapping ID and status are required');
            }
            
            $result = $this->metaLeadFormModel->toggleActive($formMappingId, $isActive);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Status updated successfully'
                ]);
            } else {
                throw new Exception('Failed to update status');
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get forms for a project
     */
    public function getProjectForms() {
        header('Content-Type: application/json');
        
        try {
            $projectId = $_GET['project_id'] ?? null;
            
            if (!$projectId) {
                throw new Exception('Project ID is required');
            }
            
            $forms = $this->metaLeadFormModel->getByProject($projectId);
            
            echo json_encode([
                'success' => true,
                'forms' => $forms
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get all active form mappings
     */
    public function getAllActiveForms() {
        header('Content-Type: application/json');
        
        try {
            $forms = $this->metaLeadFormModel->getAllActive();
            
            echo json_encode([
                'success' => true,
                'forms' => $forms
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get form statistics
     */
    public function getFormStats() {
        header('Content-Type: application/json');
        
        try {
            $formId = $_GET['form_id'] ?? null;
            
            if (!$formId) {
                throw new Exception('Form ID is required');
            }
            
            $stats = $this->metaLeadFormModel->getFormStats($formId);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Test Meta API connection
     */
    public function testConnection() {
        header('Content-Type: application/json');
        
        try {
            $result = $this->metaService->testConnection();
            echo json_encode($result);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

// Handle AJAX requests
if (isset($_GET['action']) || isset($_POST['action'])) {
    $controller = new MetaLeadFormsController();
    $action = $_GET['action'] ?? $_POST['action'];
    
    switch ($action) {
        case 'add_form':
            $controller->addFormToProject();
            break;
        case 'remove_form':
            $controller->removeFormFromProject();
            break;
        case 'toggle_status':
            $controller->toggleFormStatus();
            break;
        case 'get_project_forms':
            $controller->getProjectForms();
            break;
        case 'get_all_active':
            $controller->getAllActiveForms();
            break;
        case 'get_form_stats':
            $controller->getFormStats();
            break;
        case 'test_connection':
            $controller->testConnection();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}
