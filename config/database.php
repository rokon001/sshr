<?php
/**
 * Database Configuration
 * Update these values with your hosting credentials
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Site settings
define('SITE_URL', 'https://yourdomain.com');
define('SITE_NAME', 'Start Smart HR');
define('ADMIN_EMAIL', 'info@startsmarthr.eu');

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Europe/Zagreb');

/**
 * Database connection class
 */
class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // If database connection fails, site will work in static mode
            $this->pdo = null;
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function isConnected() {
        return $this->pdo !== null;
    }
}

/**
 * Helper function to get database connection
 */
function db() {
    return Database::getInstance()->getConnection();
}

/**
 * Check if database is available
 */
function dbAvailable() {
    return Database::getInstance()->isConnected();
}

