<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requirePlanner(); // Allow both admin and planner

$adminPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraWedding Admin — <?= ucfirst($adminPage) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .sidebar-nav a.active { background:rgba(255,255,255,0.12); color:#fff; border-left:3px solid var(--gold); }
        body { background: #f3f4f6; }
    </style>
</head>
<body>

<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div style="font-size:1.5rem; margin-bottom:4px">💍 AuraWedding</div>
            <div style="font-size:0.75rem; color:rgba(255,255,255,0.5); font-weight:400"><?= isAdmin() ? 'Admin Dashboard' : 'Planner Dashboard' ?></div>
        </div>
        <nav class="sidebar-nav">
            <?php if (isAdmin()): ?>
            <a href="index.php" class="<?= $adminPage==='index'?'active':'' ?>">📊 Dashboard</a>
            <a href="bookings.php" class="<?= $adminPage==='bookings'?'active':'' ?>">📋 Bookings</a>
            <a href="users.php" class="<?= $adminPage==='users'?'active':'' ?>">👥 Users</a>
            <a href="packages.php" class="<?= $adminPage==='packages'?'active':'' ?>">📦 Packages</a>
            <a href="gallery.php" class="<?= $adminPage==='gallery'?'active':'' ?>">🖼️ Gallery</a>
            <a href="messages.php" class="<?= $adminPage==='messages'?'active':'' ?>">✉️ Messages</a>
            <?php else: ?>
            <a href="planner-dashboard.php" class="<?= $adminPage==='planner-dashboard'?'active':'' ?>">📋 Bookings</a>
            <a href="planner-dashboard.php?action=view-designs" class="<?= ($adminPage==='planner-dashboard' && $action==='view-designs')?'active':'' ?>">🎨 Designs</a>
            <?php endif; ?>
            <hr style="border-color:rgba(255,255,255,0.1); margin:12px 0">
            <a href="../index.php" target="_blank">🌐 View Website</a>
            <a href="../index.php?page=logout">🔓 Logout</a>
        </nav>
        <div style="padding:20px 24px; border-top:1px solid rgba(255,255,255,0.1); margin-top:auto; font-size:0.82rem; color:rgba(255,255,255,0.5)">
            Logged in as:<br>
            <strong style="color:rgba(255,255,255,0.8)"><?= htmlspecialchars($_SESSION['name']) ?></strong>
            <span style="background:rgba(255,255,255,0.15); border-radius:4px; padding:1px 6px; font-size:0.75rem; margin-left:6px"><?= $_SESSION['role'] ?></span>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="admin-content">
