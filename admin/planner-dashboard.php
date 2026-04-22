<?php include 'header.php'; ?>

<?php
// Only planners can access this page
if ($_SESSION['role'] !== 'planner') {
    header("Location: index.php");
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bid = intval($_POST['booking_id']);
    $status = sanitize($conn, $_POST['status']);
    $notes = sanitize($conn, $_POST['admin_notes'] ?? '');
    $allowed_statuses = ['pending','confirmed','rejected','completed'];
    if (in_array($status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE bookings SET status=?, admin_notes=? WHERE id=?");
        $stmt->bind_param("ssi", $status, $notes, $bid);
        $stmt->execute();
        flashMessage('success', "Booking #{$bid} updated to " . ucfirst($status));
        header("Location: planner-dashboard.php");
        exit;
    }
}

// Build query with filter
$where = '';
if ($filter === 'pending') $where = "WHERE b.status='pending'";
elseif ($filter === 'confirmed') $where = "WHERE b.status='confirmed'";
elseif ($filter === 'rejected') $where = "WHERE b.status='rejected'";
elseif ($filter === 'completed') $where = "WHERE b.status='completed'";

$bookings = $conn->query("SELECT b.*, u.name as user_name, p.name as pkg_name FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN packages p ON b.package_id=p.id $where ORDER BY b.created_at DESC");

// Count per status
$counts = [];
foreach (['all','pending','confirmed','rejected','completed'] as $s) {
    $w = ($s !== 'all') ? "WHERE status='$s'" : '';
    $counts[$s] = $conn->query("SELECT COUNT(*) as c FROM bookings $w")->fetch_assoc()['c'];
}

// Single booking view
$booking = null;
if ($action === 'view' && $id) {
    $stmt = $conn->prepare("SELECT b.*, u.name as user_name, p.name as pkg_name FROM bookings b LEFT JOIN users u ON b.user_id=u.id LEFT JOIN packages p ON b.package_id=p.id WHERE b.id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
}

// View all customer designs
$all_designs = null;
if ($action === 'view-designs') {
    $all_designs = $conn->query("SELECT d.*, b.booking_code, b.bride_name, b.groom_name FROM user_designs d LEFT JOIN bookings b ON d.booking_id=b.id ORDER BY d.created_at DESC");
}

// Flash
$flash = getFlash();
?>

<div class="admin-header">
    <div class="admin-title">👰 Wedding Planner Dashboard</div>
    <div style="font-size:0.9rem; color:#888">Welcome, <?= htmlspecialchars($_SESSION['name']) ?> | <?= $counts['all'] ?> total bookings</div>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- ALL DESIGNS VIEW -->
<?php if ($action === 'view-designs' && $all_designs): ?>
<div style="background:#fff; border-radius:18px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,0.08); margin-bottom:24px">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px">
        <h3 style="font-size:1.3rem; font-weight:800; color:var(--pink-main)">🎨 Customer Design Gallery</h3>
        <a href="planner-dashboard.php" class="btn btn-outline" style="padding:8px 16px">← Back to Bookings</a>
    </div>

    <?php if ($all_designs->num_rows > 0): ?>
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px">
        <?php while ($design = $all_designs->fetch_assoc()): ?>
        <?php 
            $imagePath = $design['image_url'];
            if (strpos($imagePath, 'images/') === 0) {
                $imagePath = '../' . $imagePath;
            }
        ?>
        <div style="background:#f9f9f9; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1); transition:transform 0.3s, box-shadow 0.3s" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 6px 16px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'">
            <a href="<?= htmlspecialchars($imagePath) ?>" target="_blank" style="display:block">
                <img src="<?= htmlspecialchars($imagePath) ?>" alt="Design" style="width:100%; height:180px; object-fit:cover; cursor:pointer; transition:opacity 0.3s">
            </a>
            <div style="padding:14px">
                <div style="font-size:0.75rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:4px">
                    📌 <?= htmlspecialchars($design['booking_code'] ?? 'N/A') ?>
                </div>
                <div style="font-size:0.9rem; color:#333; font-weight:600; margin-bottom:6px">
                    <?= htmlspecialchars($design['bride_name'] ?? 'Unknown') ?> & <?= htmlspecialchars($design['groom_name'] ?? '') ?>
                </div>
                <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px">
                    <span style="background:var(--pink-light); color:var(--pink-main); padding:3px 10px; border-radius:8px; font-size:0.75rem; font-weight:600; text-transform:capitalize">
                        🎨 <?= $design['design_type'] ?>
                    </span>
                    <span style="background:#e8f4f8; color:#0066cc; padding:3px 10px; border-radius:8px; font-size:0.75rem; font-weight:600; text-transform:capitalize">
                        🎊 <?= $design['ceremony_type'] ?>
                    </span>
                </div>
                <?php if ($design['description']): ?>
                <div style="font-size:0.8rem; color:#666; line-height:1.4; max-height:60px; overflow:hidden">
                    <?= htmlspecialchars($design['description']) ?>
                </div>
                <?php endif; ?>
                <div style="font-size:0.75rem; color:#999; margin-top:8px; font-style:italic">
                    📅 <?= date('d M Y', strtotime($design['created_at'])) ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center; padding:48px; color:#888">
        <div style="font-size:2rem; margin-bottom:12px">🎨</div>
        <div style="font-size:1.1rem; font-weight:600">No customer designs yet</div>
        <div style="font-size:0.9rem; margin-top:8px">When customers upload designs during booking, they'll appear here</div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>


<?php if ($action !== 'view-designs'): ?>
<div style="display:flex; gap:8px; margin-bottom:22px; flex-wrap:wrap">
    <?php foreach (['all','pending','confirmed','rejected','completed'] as $s):
        $colors = ['all'=>'#6c757d','pending'=>'#ffc107','confirmed'=>'#28a745','rejected'=>'#dc3545','completed'=>'#17a2b8'];
    ?>
    <a href="planner-dashboard.php?filter=<?= $s ?>"
       style="padding:8px 18px; border-radius:20px; font-size:0.88rem; font-weight:600; text-decoration:none; background:<?= $filter===$s ? $colors[$s] : '#fff' ?>; color:<?= $filter===$s ? '#fff' : '#555' ?>; border:2px solid <?= $colors[$s] ?>">
        <?= ucfirst($s) ?> (<?= $counts[$s] ?>)
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Single Booking View -->
<?php if ($booking): ?>
<div style="background:#fff; border-radius:18px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,0.08); margin-bottom:24px">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px">
        <div>
            <h3 style="font-size:1.3rem; font-weight:800; color:var(--pink-main)"><?= $booking['booking_code'] ?></h3>
            <div style="font-size:0.9rem; color:#888">Submitted: <?= date('d M Y H:i', strtotime($booking['created_at'])) ?></div>
        </div>
        <span class="badge badge-<?= $booking['status'] ?>" style="font-size:0.9rem; padding:8px 20px"><?= ucfirst($booking['status']) ?></span>
    </div>

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:20px; margin-bottom:24px">
        <?php
        $details = [
            ['👰 Bride', $booking['bride_name']],
            ['🤵 Groom', $booking['groom_name']],
            ['✉️ Email', $booking['email']],
            ['📞 Phone', $booking['phone']],
            ['📅 Wedding Date', date('d M Y', strtotime($booking['wedding_date']))],
            ['👥 Guests', $booking['guest_count'] ?: 'Not specified'],
            ['📍 Venue', $booking['venue'] ?: 'Not specified'],
            ['📦 Package', $booking['pkg_name'] ?: 'No package'],
            ['💰 Budget', $booking['budget'] ? '₹'.number_format($booking['budget']) : 'Not specified'],
            ['👤 User', $booking['user_name'] ?: 'Guest'],
        ];
        foreach ($details as [$label, $val]):
        ?>
        <div>
            <div style="font-size:0.78rem; color:#888; font-weight:600; text-transform:uppercase"><?= $label ?></div>
            <div style="font-weight:600; color:var(--dark); margin-top:4px"><?= htmlspecialchars($val) ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-bottom:20px">
        <div style="font-size:0.78rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:8px">🎊 Ceremonies Requested</div>
        <div style="display:flex; gap:8px; flex-wrap:wrap">
            <?php
            $cerIcons = ['sangeet'=>'🎵','mehendi'=>'🌿','haldi'=>'🌻','vivaha'=>'👰'];
            foreach (explode(',', $booking['ceremony_types']) as $cer):
                $cer = trim($cer);
            ?>
            <span style="background:var(--pink-light); color:var(--pink-main); padding:6px 16px; border-radius:20px; font-size:0.9rem; font-weight:600">
                <?= ($cerIcons[$cer] ?? '💍') . ' ' . ucfirst($cer) ?>
            </span>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($booking['special_requests']): ?>
    <div style="background:#f9f9f9; border-radius:12px; padding:16px; margin-bottom:20px">
        <div style="font-size:0.78rem; color:#888; font-weight:600; text-transform:uppercase; margin-bottom:6px">📝 Special Requests</div>
        <p style="font-size:0.9rem; color:var(--text)"><?= nl2br(htmlspecialchars($booking['special_requests'])) ?></p>
    </div>
    <?php endif; ?>

    <?php
    // Fetch user designs for this booking
    $designs = null;
    try {
        $designsQuery = $conn->prepare("SELECT * FROM user_designs WHERE booking_id=? ORDER BY created_at DESC");
        if ($designsQuery) {
            $designsQuery->bind_param("i", $booking['id']);
            $designsQuery->execute();
            $designs = $designsQuery->get_result();
        }
    } catch (Exception $e) {
        $designs = null;
    }
    ?>

    <?php if ($designs && $designs->num_rows > 0): ?>
    <div style="background:#f0f8ff; border-radius:12px; padding:16px; margin-bottom:20px">
        <div style="font-size:0.78rem; color:#084298; font-weight:600; text-transform:uppercase; margin-bottom:12px">🎨 Customer's Custom Designs</div>
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px">
            <?php while ($design = $designs->fetch_assoc()): ?>
            <?php 
                // Fix image path for admin folder
                $imagePath = $design['image_url'];
                if (strpos($imagePath, 'images/') === 0) {
                    $imagePath = '../' . $imagePath;
                }
            ?>
            <div style="background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1)">
                <a href="<?= htmlspecialchars($imagePath) ?>" target="_blank" style="display:block">
                    <img src="<?= htmlspecialchars($imagePath) ?>" alt="Design" style="width:100%; height:130px; object-fit:cover; cursor:pointer; transition:opacity 0.3s">
                </a>
                <div style="padding:10px">
                    <div style="font-size:0.75rem; color:#084298; font-weight:600; text-transform:capitalize"><?= $design['design_type'] ?></div>
                    <div style="font-size:0.75rem; color:#666; margin:4px 0"><?= ucfirst($design['ceremony_type']) ?></div>
                    <?php if ($design['description']): ?>
                    <div style="font-size:0.7rem; color:#999; max-height:40px; overflow:hidden; line-height:1.3"><?= htmlspecialchars($design['description']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Update Status Form - PLANNER ONLY -->
    <div style="background:var(--pink-light); border-radius:14px; padding:22px">
        <h4 style="margin-bottom:16px; color:var(--dark)">⚙️ Confirm or Reject Booking</h4>
        <form method="POST">
            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
            <input type="hidden" name="update_status" value="1">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px">
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="<?= $booking['status'] ?>" selected><?= ucfirst($booking['status']) ?></option>
                        <option value="confirmed">✅ Confirm Booking</option>
                        <option value="rejected">❌ Reject Booking</option>
                        <option value="completed">🎊 Mark as Completed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Planner's Note to Customer</label>
                    <input type="text" name="admin_notes" value="<?= htmlspecialchars($booking['admin_notes'] ?? '') ?>" placeholder="Message for the customer...">
                </div>
            </div>
            <div style="margin-top:14px; display:flex; gap:10px">
                <button type="submit" class="btn btn-primary">✅ Update Booking</button>
                <a href="planner-dashboard.php" class="btn btn-outline">← Back to List</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Bookings Table -->
<?php if ($action !== 'view-designs'): ?>
<div style="background:#fff; border-radius:12px; padding:12px 16px; margin-bottom:14px; display:flex; gap:10px; align-items:center">
    <input type="text" id="searchInput" placeholder="🔍 Search bookings..." style="padding:10px 14px; border:2px solid #e8e8e8; border-radius:10px; font-size:0.9rem; outline:none; flex:1" oninput="searchTable('searchInput','bookingTable')">
</div>

<div class="data-table" style="overflow-x:auto; border-radius:12px">
    <table id="bookingTable" style="width:100%; border-collapse:collapse">
        <thead>
            <tr style="background:#f8f9fa">
                <th style="padding:14px; text-align:left; font-weight:700; color:#333; white-space:nowrap; border-bottom:2px solid #e8e8e8">Code</th>
                <th style="padding:14px; text-align:left; font-weight:700; color:#333; white-space:nowrap; border-bottom:2px solid #e8e8e8">Couple Names</th>
                <th style="padding:14px; text-align:left; font-weight:700; color:#333; white-space:nowrap; border-bottom:2px solid #e8e8e8">Email</th>
                <th style="padding:14px; text-align:left; font-weight:700; color:#333; white-space:nowrap; border-bottom:2px solid #e8e8e8">Phone</th>
                <th style="padding:14px; text-align:left; font-weight:700; color:#333; white-space:nowrap; border-bottom:2px solid #e8e8e8">Wedding Date</th>
                <th style="padding:14px; text-align:left; font-weight:700; color:#333; white-space:nowrap; border-bottom:2px solid #e8e8e8">Package</th>
                <th style="padding:14px; text-align:left; font-weight:700; color:#333; white-space:nowrap; border-bottom:2px solid #e8e8e8">Status</th>
                <th style="padding:14px; text-align:left; font-weight:700; color:#333; white-space:nowrap; border-bottom:2px solid #e8e8e8">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($bookings && $bookings->num_rows > 0): ?>
            <?php while ($b = $bookings->fetch_assoc()): ?>
            <tr style="border-bottom:1px solid #e8e8e8; transition:background 0.3s">
                <td style="padding:14px; font-weight:700; color:var(--pink-main)"><?= $b['booking_code'] ?></td>
                <td style="padding:14px">
                    <div style="font-weight:600; color:#333"><?= htmlspecialchars($b['bride_name']) ?> & <?= htmlspecialchars($b['groom_name']) ?></div>
                </td>
                <td style="padding:14px; font-size:0.9rem; color:#666"><?= htmlspecialchars($b['email']) ?></td>
                <td style="padding:14px; font-size:0.9rem"><?= htmlspecialchars($b['phone']) ?></td>
                <td style="padding:14px; font-size:0.9rem; white-space:nowrap"><?= date('d M Y', strtotime($b['wedding_date'])) ?></td>
                <td style="padding:14px; font-size:0.9rem"><?= htmlspecialchars($b['pkg_name'] ?? '—') ?></td>
                <td style="padding:14px">
                    <span class="badge badge-<?= $b['status'] ?>" style="padding:6px 14px; border-radius:12px; font-weight:600; display:inline-block"><?= ucfirst($b['status']) ?></span>
                </td>
                <td style="padding:14px">
                    <a href="planner-dashboard.php?action=view&id=<?= $b['id'] ?>" class="btn btn-primary btn-sm" style="padding:8px 14px; font-size:0.85rem">Manage</a>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="8" style="text-align:center; padding:36px; color:#888">No bookings found for this filter.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
