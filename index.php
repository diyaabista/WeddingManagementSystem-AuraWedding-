<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$page = $_GET['page'] ?? 'home';

// Handle logout
if ($page === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Determine which page to load
$allowed = ['home','sangeet','mehendi','haldi','vivaha','login','register','booking','my-bookings','wedding-planner','contact'];
if (!in_array($page, $allowed)) $page = 'home';

// Include header
include 'includes/header.php';

// Load page
include "pages/{$page}.php";

// Include footer
include 'includes/footer.php';
?>
