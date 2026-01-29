<?php
/**
 * Auth Controller
 * Handles user authentication
 */

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Show login page
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        // Handle login form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processLogin();
            return;
        }
        
        // Show login form
        include BASE_PATH . 'views/auth/login.php';
    }
    
    /**
     * Process login
     */
    private function processLogin() {
        // Validate CSRF token
        if (!isset($_POST['csrf_token']) || !verifyToken($_POST['csrf_token'])) {
            setFlashMessage('Invalid request. Please try again.', 'error');
            redirect('login');
        }
        
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($password)) {
            setFlashMessage('Please enter both username and password.', 'error');
            redirect('login');
        }
        
        // Authenticate user
        $user = $this->userModel->authenticate($username, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            
            // Log activity
            logActivity('user_login', 'user', $user['id'], "User logged in");
            
            // Redirect to intended page or dashboard
            $redirectUrl = $_SESSION['redirect_after_login'] ?? 'dashboard';
            unset($_SESSION['redirect_after_login']);
            
            setFlashMessage('Welcome back, ' . $user['full_name'] . '!', 'success');
            redirect($redirectUrl);
        } else {
            setFlashMessage('Invalid username or password.', 'error');
            redirect('login');
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;
        
        if ($userId) {
            logActivity('user_logout', 'user', $userId, "User logged out");
        }
        
        // Destroy session
        session_unset();
        session_destroy();
        
        // Start new session for flash message
        session_start();
        setFlashMessage('You have been logged out successfully.', 'info');
        
        redirect('login');
    }
}
