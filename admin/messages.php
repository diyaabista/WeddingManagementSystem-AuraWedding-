<?php include 'header.php'; ?>

<?php
// Admin only
if (!isAdmin()) {
    header("Location: planner-dashboard.php");
    exit;
}

$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';
    if ($action === 'mark_read') {
        $mid = intval($_POST['msg_id']);
        $conn->query("UPDATE messages SET is_read=1 WHERE id=$mid");
        header("Location: messages.php"); exit;
    }
    if ($action === 'delete') {
        $mid = intval($_POST['msg_id']);
        $conn->query("DELETE FROM messages WHERE id=$mid");
        flashMessage('success', 'Message deleted.');
        header("Location: messages.php"); exit;
    }
    if ($action === 'mark_all_read') {
        $conn->query("UPDATE messages SET is_read=1");
        flashMessage('success', 'All messages marked as read.');
        header("Location: messages.php"); exit;
    }
}

$filter = $_GET['filter'] ?? 'all';
$where = '';
if ($filter === 'unread') $where = "WHERE is_read=0";
if ($filter === 'read')   $where = "WHERE is_read=1";
$messages = $conn->query("SELECT * FROM messages $where ORDER BY created_at DESC");
$unreadCount = $conn->query("SELECT COUNT(*) as c FROM messages WHERE is_read=0")->fetch_assoc()['c'];
$totalCount  = $conn->query("SELECT COUNT(*) as c FROM messages")->fetch_assoc()['c'];

// View single
$viewMsg = null;
if (isset($_GET['view'])) {
    $mid = intval($_GET['view']);
    $viewMsg = $conn->query("SELECT * FROM messages WHERE id=$mid")->fetch_assoc();
    if ($viewMsg && !$viewMsg['is_read']) {
        $conn->query("UPDATE messages SET is_read=1 WHERE id=$mid");
    }
}
?>

<div class="admin-header">
    <div class="admin-title">✉️ Messages (<?= $unreadCount ?> unread)</div>
    <?php if ($unreadCount > 0): ?>
    <form method="POST" style="display:inline">
        <input type="hidden" name="form_action" value="mark_all_read">
        <button type="submit" class="btn btn-outline btn-sm">✓ Mark All Read</button>
    </form>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Filter -->
<div style="display:flex; gap:8px; margin-bottom:20px">
    <a href="messages.php" style="padding:8px 18px; border-radius:20px; font-size:0.88rem; font-weight:600; text-decoration:none; background:<?= $filter==='all'?'var(--pink-main)':'#fff' ?>; color:<?= $filter==='all'?'#fff':'#555' ?>; border:2px solid var(--pink-soft)">All (<?= $totalCount ?>)</a>
    <a href="messages.php?filter=unread" style="padding:8px 18px; border-radius:20px; font-size:0.88rem; font-weight:600; text-decoration:none; background:<?= $filter==='unread'?'var(--pink-main)':'#fff' ?>; color:<?= $filter==='unread'?'#fff':'#555' ?>; border:2px solid var(--pink-soft)">Unread (<?= $unreadCount ?>)</a>
    <a href="messages.php?filter=read" style="padding:8px 18px; border-radius:20px; font-size:0.88rem; font-weight:600; text-decoration:none; background:<?= $filter==='read'?'var(--pink-main)':'#fff' ?>; color:<?= $filter==='read'?'#fff':'#555' ?>; border:2px solid var(--pink-soft)">Read</a>
</div>

<?php if ($viewMsg): ?>
<div style="background:#fff; border-radius:16px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,0.08); margin-bottom:24px">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px">
        <h3 style="color:var(--dark)">📩 <?= htmlspecialchars($viewMsg['subject'] ?: 'No Subject') ?></h3>
        <a href="messages.php" style="color:#888; text-decoration:none; font-size:0.9rem">← Back</a>
    </div>
    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:20px">
        <div><div style="font-size:0.78rem; color:#888; font-weight:600">FROM</div><div style="font-weight:600"><?= htmlspecialchars($viewMsg['name']) ?></div></div>
        <div><div style="font-size:0.78rem; color:#888; font-weight:600">EMAIL</div><div><?= htmlspecialchars($viewMsg['email']) ?></div></div>
        <div><div style="font-size:0.78rem; color:#888; font-weight:600">PHONE</div><div><?= htmlspecialchars($viewMsg['phone'] ?: '—') ?></div></div>
        <div><div style="font-size:0.78rem; color:#888; font-weight:600">DATE</div><div><?= date('d M Y H:i', strtotime($viewMsg['created_at'])) ?></div></div>
    </div>
    <div style="background:#f9f9f9; border-radius:12px; padding:20px; line-height:1.8; color:var(--text)">
        <?= nl2br(htmlspecialchars($viewMsg['message'])) ?>
    </div>
    <div style="margin-top:16px; display:flex; gap:10px">
        <a href="mailto:<?= htmlspecialchars($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['subject']) ?>" class="btn btn-primary btn-sm">📧 Reply via Email</a>
        <form method="POST">
            <input type="hidden" name="form_action" value="delete">
            <input type="hidden" name="msg_id" value="<?= $viewMsg['id'] ?>">
            <button type="submit" class="btn btn-sm" style="background:#f8d7da; color:#842029; border:none; cursor:pointer" onclick="return confirm('Delete message?')">🗑 Delete</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="data-table">
    <table>
        <thead>
            <tr><th>Status</th><th>Name</th><th>Email</th><th>Subject</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php if ($messages && $messages->num_rows > 0): ?>
            <?php while ($m = $messages->fetch_assoc()): ?>
            <tr style="<?= !$m['is_read'] ? 'background:#fff8f0' : '' ?>">
                <td>
                    <?php if (!$m['is_read']): ?>
                    <span style="width:10px; height:10px; background:var(--pink-main); border-radius:50%; display:inline-block"></span>
                    <?php else: ?>
                    <span style="width:10px; height:10px; background:#ccc; border-radius:50%; display:inline-block"></span>
                    <?php endif; ?>
                </td>
                <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                <td><?= htmlspecialchars($m['email']) ?></td>
                <td><?= htmlspecialchars($m['subject'] ?: '(No subject)') ?></td>
                <td style="font-size:0.82rem; color:#888"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                <td style="display:flex; gap:6px">
                    <a href="messages.php?view=<?= $m['id'] ?>" class="btn btn-primary btn-sm">View</a>
                    <?php if (!$m['is_read']): ?>
                    <form method="POST">
                        <input type="hidden" name="form_action" value="mark_read">
                        <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline">✓ Read</button>
                    </form>
                    <?php endif; ?>
                    <form method="POST" onsubmit="return confirm('Delete?')">
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="msg_id" value="<?= $m['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#f8d7da; color:#842029; border:none; cursor:pointer">🗑</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="6" style="text-align:center; padding:30px; color:#888">No messages found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
