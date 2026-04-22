<?php include 'header.php'; ?>

<?php
// Redirect planners to their dashboard
if ($_SESSION['role'] === 'planner') {
    header("Location: planner-dashboard.php");
    exit;
}

// Stats
$totalBookings  = $conn->query("SELECT COUNT(*) as c FROM bookings")->fetch_assoc()['c'];
$pendingCount   = $conn->query("SELECT COUNT(*) as c FROM bookings WHERE status='pending'")->fetch_assoc()['c'];
$confirmedCount = $conn->query("SELECT COUNT(*) as c FROM bookings WHERE status='confirmed'")->fetch_assoc()['c'];
$totalUsers     = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='user'")->fetch_assoc()['c'];
$totalMessages  = $conn->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];
$recentBookings = $conn->query("SELECT b.*, u.name as user_name FROM bookings b LEFT JOIN users u ON b.user_id=u.id ORDER BY b.created_at DESC LIMIT 8");
?>

<div class="admin-header">
    <div>
        <div class="admin-title">Dashboard Overview</div>
        <div style="color:#888; font-size:0.9rem">Welcome back, <?= htmlspecialchars($_SESSION['name']) ?> 👋</div>
    </div>
    <div style="font-size:0.85rem; color:#888"><?= date('l, d F Y') ?></div>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">📋</div>
        <div class="stat-info">
            <h3><?= $totalBookings ?></h3>
            <p>Total Bookings</p>
        </div>
    </div>
    <div class="stat-card" style="border-left:4px solid #ffc107">
        <div class="stat-icon">⏳</div>
        <div class="stat-info">
            <h3><?= $pendingCount ?></h3>
            <p>Pending</p>
        </div>
    </div>
    <div class="stat-card" style="border-left:4px solid #28a745">
        <div class="stat-icon">✅</div>
        <div class="stat-info">
            <h3><?= $confirmedCount ?></h3>
            <p>Confirmed</p>
        </div>
    </div>
    <div class="stat-card" style="border-left:4px solid #17a2b8">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
            <h3><?= $totalUsers ?></h3>
            <p>Registered Users</p>
        </div>
    </div>
    <div class="stat-card" style="border-left:4px solid #dc3545">
        <div class="stat-icon">✉️</div>
        <div class="stat-info">
            <h3><?= $totalMessages ?></h3>
            <p>Unread Messages</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div style="display:flex; gap:12px; margin-bottom:28px; flex-wrap:wrap">
    <a href="bookings.php?filter=pending" class="btn btn-primary btn-sm">⏳ Review Pending (<?= $pendingCount ?>)</a>
    <a href="messages.php" class="btn btn-gold btn-sm">✉️ View Messages (<?= $totalMessages ?>)</a>
    <a href="packages.php" class="btn btn-outline btn-sm">📦 Manage Packages</a>
    <a href="gallery.php" class="btn btn-outline btn-sm">🖼️ Gallery</a>
</div>

<!-- Recent Bookings -->
<div class="data-table">
    <div style="padding:18px 20px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #f0f0f0; flex-wrap:wrap; gap:10px">
        <h3 style="font-size:1rem; font-weight:700; color:var(--dark)">Recent Bookings</h3>
        <a href="bookings.php" style="color:var(--pink-main); font-size:0.9rem; text-decoration:none">View All →</a>
    </div>
    <table id="recentTable">
        <thead>
            <tr>
                <th>Code</th>
                <th>Couple</th>
                <th>Wedding Date</th>
                <th>Ceremonies</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($recentBookings && $recentBookings->num_rows > 0): ?>
            <?php while ($b = $recentBookings->fetch_assoc()): ?>
            <tr>
                <td><strong style="color:var(--pink-main)"><?= $b['booking_code'] ?></strong></td>
                <td><?= htmlspecialchars($b['bride_name']) ?> & <?= htmlspecialchars($b['groom_name']) ?></td>
                <td><?= date('d M Y', strtotime($b['wedding_date'])) ?></td>
                <td><?= htmlspecialchars($b['ceremony_types']) ?></td>
                <td><span class="badge badge-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span></td>
                <td><a href="bookings.php?action=view&id=<?= $b['id'] ?>" class="btn btn-primary btn-sm">Manage</a></td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="6" style="text-align:center; padding:30px; color:#888">No bookings yet</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Ceremony Stats -->
<?php
$cerStats = [];
$cerRes = $conn->query("SELECT ceremony_types FROM bookings");
if ($cerRes) {
    while ($row = $cerRes->fetch_assoc()) {
        foreach (explode(',', $row['ceremony_types']) as $c) {
            $c = trim($c);
            $cerStats[$c] = ($cerStats[$c] ?? 0) + 1;
        }
    }
}
?>
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-top:24px">
    <?php
    $cerIcons = ['sangeet'=>'🎵','mehendi'=>'🌿','haldi'=>'🌻','vivaha'=>'👰'];
    foreach ($cerIcons as $cer => $icon):
        $count = $cerStats[$cer] ?? 0;
    ?>
    <div style="background:#fff; border-radius:14px; padding:20px; text-align:center; box-shadow:0 4px 16px rgba(0,0,0,0.06)">
        <div style="font-size:2rem; margin-bottom:8px"><?= $icon ?></div>
        <div style="font-size:1.6rem; font-weight:800; color:var(--dark)"><?= $count ?></div>
        <div style="font-size:0.85rem; color:#888; text-transform:capitalize"><?= ucfirst($cer) ?> Bookings</div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>
