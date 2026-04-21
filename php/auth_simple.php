<?php
// Minimal auth endpoint - no database
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $email      = $_POST['email'] ?? '';
    $password   = $_POST['password'] ?? '';
    $login_type = $_POST['login_type'] ?? 'user';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit;
    }

    // DEMO MODE: Accept password 'wedding123' for any email
    if ($password === 'wedding123') {
        $is_admin = ($login_type === 'admin');
        $username = explode('@', $email)[0];
        
        // Set session
        $_SESSION['user_id']   = rand(1000, 9999);
        $_SESSION['user_name'] = $username;
        $_SESSION['is_admin']  = $is_admin;
        $_SESSION['is_planner'] = ($login_type === 'planner');
        $_SESSION['login_type'] = $login_type;
        $_SESSION['wedding_id'] = rand(1000, 9999);  // Generate demo wedding ID for menu saving
        
        echo json_encode([
            'success' => true,
            'name' => $username,
            'is_admin' => $is_admin,
            'is_planner' => ($login_type === 'planner'),
            'wedding_id' => $_SESSION['wedding_id']  // Include wedding_id in response
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid password. Use: wedding123']);
    }
}
elseif ($action === 'check') {
    $logged_in = isset($_SESSION['user_id']);
    echo json_encode([
        'logged_in' => $logged_in,
        'user_name' => $_SESSION['user_name'] ?? '',
        'login_type' => $_SESSION['login_type'] ?? '',
        'is_admin' => $_SESSION['is_admin'] ?? false,
        'wedding_id' => $_SESSION['wedding_id'] ?? null
    ]);
}
elseif ($action === 'logout') {
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logged out']);
}
else {
    echo json_encode(['error' => 'Invalid action']);
}
?>
