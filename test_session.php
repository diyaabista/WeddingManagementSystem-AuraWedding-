<?php
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo json_encode([
    'session_started' => true,
    'session_id' => session_id(),
    'user_id' => $_SESSION['user_id'] ?? null,
    'user_name' => $_SESSION['user_name'] ?? null,
    'wedding_id' => $_SESSION['wedding_id'] ?? null,
    'login_type' => $_SESSION['login_type'] ?? null,
    'all_session_data' => $_SESSION
], JSON_PRETTY_PRINT);
?>
