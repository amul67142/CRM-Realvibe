<?php
/**
 * Client Controller
 * Manages real estate clients/developers
 */

require_once __DIR__ . '/../models/Client.php';

class ClientController {
    private $clientModel;
    
    public function __construct() {
        $this->clientModel = new Client();
    }
    
    /**
     * List all clients
     */
    public function list() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('Access denied', 'error');
            redirect('dashboard');
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $perPage;
        
        $clients = $this->clientModel->getAll($perPage, $offset);
        $totalClients = $this->clientModel->count();
        
        $data = [
            'clients' => $clients,
            'currentPage' => $page,
            'totalClients' => $totalClients,
            'perPage' => $perPage
        ];
        
        include BASE_PATH . 'views/clients/list.php';
    }
    
    /**
     * Create new client
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
                redirect('clients/create');
            }
            
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'company_name' => sanitizeInput($_POST['company_name'] ?? ''),
                'address' => sanitizeInput($_POST['address'] ?? ''),
                'notes' => sanitizeInput($_POST['notes'] ?? '')
            ];
            
            $clientId = $this->clientModel->create($data);
            
            if ($clientId) {
                setFlashMessage('Client created successfully!', 'success');
                redirect('clients');
            } else {
                setFlashMessage('Failed to create client', 'error');
                storeOldInput();
                redirect('clients/create');
            }
        }
        
        include BASE_PATH . 'views/clients/create.php';
    }
    
    /**
     * Edit client
     */
    public function edit() {
        requireLogin();
        
        if (!hasPermission('manager')) {
            setFlashMessage('Access denied', 'error');
            redirect('dashboard');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('clients');
        }
        
        $client = $this->clientModel->getById($id);
        if (!$client) {
            setFlashMessage('Client not found', 'error');
            redirect('clients');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!verifyToken($_POST['csrf_token'] ?? '')) {
                setFlashMessage('Invalid request', 'error');
                redirect('clients/edit?id=' . $id);
            }
            
            $data = [
                'name' => sanitizeInput($_POST['name']),
                'email' => sanitizeInput($_POST['email'] ?? ''),
                'phone' => sanitizeInput($_POST['phone'] ?? ''),
                'company_name' => sanitizeInput($_POST['company_name'] ?? ''),
                'address' => sanitizeInput($_POST['address'] ?? ''),
                'notes' => sanitizeInput($_POST['notes'] ?? '')
            ];
            
            if ($this->clientModel->update($id, $data)) {
                setFlashMessage('Client updated successfully!', 'success');
                redirect('clients');
            } else {
                setFlashMessage('Failed to update client', 'error');
                redirect('clients/edit?id=' . $id);
            }
        }
        
        include BASE_PATH . 'views/clients/edit.php';
    }
    
    /**
     * Delete client
     */
    public function delete() {
        requireLogin();
        
        if (!hasPermission('admin')) {
            setFlashMessage('Access denied', 'error');
            redirect('clients');
        }
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            redirect('clients');
        }
        
        if ($this->clientModel->delete($id)) {
            setFlashMessage('Client deleted successfully', 'success');
        } else {
            setFlashMessage('Failed to delete client', 'error');
        }
        
        redirect('clients');
    }
}
