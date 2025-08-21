<?php
/**
 * Te Quiero Verde POS - Database Configuration
 * File: config/database.php
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'tqv';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    public $pdo;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset={$this->charset}";
        
        try {
            $this->pdo = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}

// Session management functions
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['session_token']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /tqvpos/login.php');
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = new Database();
    $stmt = $db->pdo->prepare("
        SELECT u.*, s.session_token 
        FROM users u 
        JOIN user_sessions s ON u.id = s.user_id 
        WHERE u.id = ? AND s.session_token = ? AND s.is_active = 1
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['session_token']]);
    return $stmt->fetch();
}

function logout() {
    startSession();
    if (isset($_SESSION['session_token'])) {
        $db = new Database();
        $stmt = $db->pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE session_token = ?");
        $stmt->execute([$_SESSION['session_token']]);
    }
    session_destroy();
    header('Location: /tqvpos/login.php');
    exit();
}
?>
