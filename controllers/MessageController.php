<?php
/**
 * Message Controller
 * Manages message templates
 */

require_once __DIR__ . '/../models/MessageTemplate.php';

class MessageController {
    private $templateModel;
    
    public function __construct() {
        $this->templateModel = new MessageTemplate();
    }
    
    /**
     * List all templates
     */
    public function templates() {
        requireLogin();
        
        $type = $_GET['type'] ?? null;
        $templates = $this->templateModel->getAll($type);
        
        $data = [
            'templates' => $templates,
            'current_type' => $type
        ];
        
        include BASE_PATH . 'views/messages/templates.php';
    }
    
    /**
     * Create new template
     */
    public function createTemplate() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('Access denied', 'error');
            redirect('messages/templates');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('messages/template/create');
            }
            
            $data = [
                'template_name' => sanitizeInput($_POST['template_name']),
                'template_content' => sanitizeInput($_POST['template_content']),
                'template_type' => sanitizeInput($_POST['template_type'] ?? 'other'),
                'variables' => $_POST['variables'] ?? []
            ];
            
            $templateId = $this->templateModel->create($data);
            
            if ($templateId) {
                setFlashMessage('Template created successfully!', 'success');
                redirect('messages/templates');
            } else {
                setFlashMessage('Failed to create template', 'error');
                storeOldInput();
                redirect('messages/template/create');
            }
        }
        
        include BASE_PATH . 'views/messages/create_template.php';
    }
    
    /**
     * Edit template
     */
    public function editTemplate() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('Access denied', 'error');
            redirect('messages/templates');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('messages/templates');
        }
        
        $template = $this->templateModel->getById($id);
        if (!$template) {
            setFlashMessage('Template not found', 'error');
            redirect('messages/templates');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('messages/template/edit?id=' . $id);
            }
            
            $data = [
                'template_name' => sanitizeInput($_POST['template_name']),
                'template_content' => sanitizeInput($_POST['template_content']),
                'template_type' => sanitizeInput($_POST['template_type'] ?? 'other'),
                'variables' => $_POST['variables'] ?? []
            ];
            
            if ($this->templateModel->update($id, $data)) {
                setFlashMessage('Template updated successfully!', 'success');
                redirect('messages/templates');
            } else {
                setFlashMessage('Failed to update template', 'error');
                redirect('messages/template/edit?id=' . $id);
            }
        }
        
        include BASE_PATH . 'views/messages/edit_template.php';
    }
    
    /**
     * Delete template
     */
    public function deleteTemplate() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            setFlashMessage('Access denied', 'error');
            redirect('messages/templates');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('messages/templates');
        }
        
        if ($this->templateModel->delete($id)) {
            setFlashMessage('Template deleted successfully', 'success');
        } else {
            setFlashMessage('Failed to delete template', 'error');
        }
        
        redirect('messages/templates');
    }
}
