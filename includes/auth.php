<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isPlanner() {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin','planner']);
}

function isPlannerOnly() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'planner';
}

function isUser() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'user';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: index.php?page=login");
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php?page=login");
        exit;
    }
}

function requirePlanner() {
    if (!isPlannerOnly()) {
        header("Location: index.php?page=login");
        exit;
    }
}

function requireUser() {
    if (!isUser()) {
        header("Location: index.php?error=Only regular users can access this page");
        exit;
    }
}

function sanitize($conn, $str) {
    return $conn->real_escape_string(htmlspecialchars(trim($str)));
}

function generateBookingCode() {
    return 'AW' . strtoupper(substr(md5(uniqid()), 0, 8));
}

function flashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
