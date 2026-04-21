<?php
// ============================================================
// AuraWedding - Authentication Handler (php/auth.php)
// ============================================================
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Debug: Log all POST data
error_log('AUTH.PHP CALLED with POST: ' . json_encode($_POST));

require_once 'config.php';

$action = $_POST['action'] ?? '';
error_log('Action: ' . $action);

switch ($action) {

    // ---- LOGIN ----
    case 'login':
        $email      = sanitize($_POST['email'] ?? '');
        $password   = $_POST['password'] ?? '';
        $login_type = sanitize($_POST['login_type'] ?? 'user');

        if (empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'Email and password are required.']);
        }

        $db = getDB();
        
        // If database is not available, allow demo login with password 'wedding123'
        if (!$db) {
            error_log('Database unavailable, using demo mode');
            if ($password !== 'wedding123') {
                jsonResponse(['success' => false, 'message' => 'Invalid email or password. (Try password: wedding123)']);
            }
            
            // Demo mode success
            $is_admin = ($login_type === 'admin');
            $_SESSION['user_id']   = rand(1000, 9999);
            $_SESSION['user_name'] = explode('@', $email)[0];
            $_SESSION['is_admin']  = $is_admin;
            $_SESSION['is_planner'] = ($login_type === 'planner');
            $_SESSION['login_type'] = $login_type;
            
            jsonResponse([
                'success' => true, 
                'name' => explode('@', $email)[0],
                'is_admin' => $is_admin,
                'is_planner' => ($login_type === 'planner')
            ]);
        }
        
        $stmt = $db->prepare("SELECT id, name, password, is_admin FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();

        // For demo: accept plain "wedding123" as password or any hashed password
        $valid = false;
        $is_admin = false;
        
        if ($user) {
            $valid = password_verify($password, $user['password']) || $password === 'wedding123';
            $is_admin = (bool)$user['is_admin'];
        } else {
            // Demo mode: allow login with password 'wedding123' for any email
            $valid = ($password === 'wedding123');
            // Determine if admin based on email
            if ($login_type === 'admin' && $valid) {
                $is_admin = true;
            }
        }

        // Check if login type matches user role
        if ($login_type === 'admin' && $user && !$user['is_admin']) {
            jsonResponse(['success' => false, 'message' => 'Invalid admin credentials.']);
        }
        if ($login_type === 'user' && $user && $user['is_admin']) {
            jsonResponse(['success' => false, 'message' => 'Admins must use Admin Login.']);
        }
        // Planner login is same as user for now
        if ($login_type === 'planner' && $user && $user['is_admin']) {
            jsonResponse(['success' => false, 'message' => 'Admins must use Admin Login.']);
        }

        if ($valid) {
            $_SESSION['user_id']   = $user['id'] ?? rand(1000, 9999);
            $_SESSION['user_name'] = $user['name'] ?? explode('@', $email)[0];
            $_SESSION['is_admin']  = $is_admin;
            $_SESSION['is_planner'] = ($login_type === 'planner');
            $_SESSION['login_type'] = $login_type;

            // Fetch wedding ID for non-admin users
            if (!$is_admin && $user) {
                $wStmt = $db->prepare("SELECT id FROM weddings WHERE user_id = ? LIMIT 1");
                $wStmt->bind_param('i', $user['id']);
                $wStmt->execute();
                $wResult = $wStmt->get_result()->fetch_assoc();
                $_SESSION['wedding_id'] = $wResult['id'] ?? null;
            }

            $username = $user['name'] ?? explode('@', $email)[0];
            jsonResponse(['success' => true, 'name' => $username, 'is_admin' => $is_admin, 'is_planner' => ($login_type === 'planner')]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Invalid email or password. (Try password: wedding123)']);
        }
        break;

    // ---- REGISTER ----
    case 'register':
        $name     = sanitize($_POST['name'] ?? '');
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $date     = sanitize($_POST['wedding_date'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            jsonResponse(['success' => false, 'message' => 'All fields are required.']);
        }

        $db   = getDB();
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO users (name, email, password, wedding_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $name, $email, $hash, $date);

        if ($stmt->execute()) {
            $userId = $db->insert_id;
            // Create a wedding record
            $wStmt = $db->prepare("INSERT INTO weddings (user_id, wedding_date) VALUES (?, ?)");
            $wStmt->bind_param('is', $userId, $date);
            $wStmt->execute();
            $weddingId = $db->insert_id;

            $_SESSION['user_id']    = $userId;
            $_SESSION['user_name']  = $name;
            $_SESSION['wedding_id'] = $weddingId;
            jsonResponse(['success' => true]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Email already exists.']);
        }
        break;

    // ---- LOGOUT ----
    case 'logout':
        // Clear session variables
        $_SESSION = [];
        // Destroy the session
        session_destroy();
        // Clear the session cookie
        setcookie(session_name(), '', time() - 3600, '/');
        jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
        break;

    // ---- CHECK SESSION ----
    case 'check':
        jsonResponse([
            'logged_in'  => isset($_SESSION['user_id']),
            'user_name'  => $_SESSION['user_name'] ?? null,
            'wedding_id' => $_SESSION['wedding_id'] ?? null,
            'is_admin'   => $_SESSION['is_admin'] ?? false,
            'login_type' => $_SESSION['login_type'] ?? 'user',
        ]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
?>
