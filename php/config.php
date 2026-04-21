<?php
// ============================================================
// AuraWedding - Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // Change to your MySQL username
define('DB_PASS', '');           // Change to your MySQL password
define('DB_NAME', 'aurawedding');

function getDB() {
    static $conn = null;
    static $attempted = false;
    
    if ($attempted) {
        return $conn;
    }
    
    $attempted = true;
    
    // Set connection timeout to 2 seconds
    $conn = @new mysqli(
        DB_HOST,
        DB_USER,
        DB_PASS,
        DB_NAME,
        ini_get("mysqli.default_port"),
        "/tmp/mysql.sock"
    );
    
    // Use procedural interface for better timeout control
    if (!$conn || $conn->connect_error) {
        error_log('Database connection error: ' . ($conn ? $conn->connect_error : 'Unknown error'));
        return null;
    }
    
    $conn->set_charset('utf8mb4');
    return $conn;
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper: Check if logged in
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?page=login');
        exit;
    }
}

// Helper: Get current wedding ID
function getWeddingId() {
    return $_SESSION['wedding_id'] ?? null;
}

// Helper: Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// Helper: JSON response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
