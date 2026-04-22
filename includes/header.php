<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraWedding — The Complete Wedding Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php
// Only show navbar for non-admin pages and non-auth pages
if (!in_array($page, ['login','register'])):
    // Check if admin page
    $currentPage = $page ?? 'home';
?>
<nav class="navbar">
    <a href="index.php" class="navbar-brand">
        <span style="font-size:2rem">💍</span>
        <span class="brand-name">AuraWedding</span>
    </a>

    <div class="nav-links">
        <a href="index.php" class="<?= $currentPage==='home'?'active':'' ?>">🏠 Home</a>
        <a href="index.php?page=sangeet" class="<?= $currentPage==='sangeet'?'active':'' ?>">🎵 Sangeet</a>
        <a href="index.php?page=mehendi" class="<?= $currentPage==='mehendi'?'active':'' ?>">🌿 Mehendi</a>
        <a href="index.php?page=haldi" class="<?= $currentPage==='haldi'?'active':'' ?>">🌻 Haldi</a>
        <a href="index.php?page=vivaha" class="<?= $currentPage==='vivaha'?'active':'' ?>">👰 Vivaha</a>
        <a href="index.php?page=wedding-planner" class="<?= $currentPage==='wedding-planner'?'active':'' ?>">📋 Wedding Planner</a>
    </div>

    <div class="nav-right">
        <?php if (isLoggedIn()): ?>
            <span class="nav-user">👤 <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
            <?php if (isAdmin()): ?>
                <a href="admin/index.php" class="btn btn-gold btn-sm">⚙️ Admin Panel</a>
            <?php elseif (isPlannerOnly()): ?>
                <a href="admin/planner-dashboard.php" class="btn btn-gold btn-sm">👰 Planner Dashboard</a>
            <?php else: ?>
                <a href="index.php?page=my-bookings" class="btn btn-outline btn-sm">My Bookings</a>
            <?php endif; ?>
            <a href="index.php?page=logout" class="btn btn-primary">🔓 Logout</a>
        <?php else: ?>
            <a href="index.php?page=login&tab=planner-login" class="btn btn-outline btn-sm">👰 Planner</a>
            <a href="index.php?page=login&tab=user-login" class="btn btn-primary">🔓 Login</a>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>

<?php
// Flash messages
$flash = getFlash();
if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>" style="margin:16px 40px">
    <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>
