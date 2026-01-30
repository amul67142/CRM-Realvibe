<?php
/**
 * Database Configuration
 * PDO-based MySQL connection with singleton pattern
 */

class Database {
    private static $instance = null;
    private $connection;
    
    // Environment detection
    private function isProduction() {
        // Check if running on production server
        // Method 1: Check server name
        if (isset($_SERVER['SERVER_NAME']) && 
            (strpos($_SERVER['SERVER_NAME'], 'localhost') === false && 
             strpos($_SERVER['SERVER_NAME'], '127.0.0.1') === false)) {
            return true;
        }
        
        // Method 2: Check if production file marker exists
        if (file_exists(dirname(__DIR__) . '/.production')) {
            return true;
        }
        
        // Method 3: Check environment variable
        if (getenv('APP_ENV') === 'production') {
            return true;
        }
        
        return false;
    }
    
    // Get database credentials based on environment
    private function getCredentials() {
        if ($this->isProduction()) {
            // Production (Hostinger) credentials
            return [
                'host' => 'localhost',
                'dbname' => 'u650869678_crm',
                'username' => 'u650869678_crm',
                'password' => 'Amul@123#',
                'charset' => 'utf8mb4'
            ];
        } else {
            // Local development credentials
            return [
                'host' => 'localhost',
                'dbname' => 'realvibe',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4'
            ];
        }
    }
    
    private function __construct() {
        $creds = $this->getCredentials();
        
        $dsn = "mysql:host={$creds['host']};dbname={$creds['dbname']};charset={$creds['charset']}";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$creds['charset']}"
        ];
        
        try {
            $this->connection = new PDO($dsn, $creds['username'], $creds['password'], $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Prevent cloning
    private function __clone() {}
    
    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function to get database connection
 */
function getDatabaseConnection() {
    return Database::getInstance()->getConnection();
}
