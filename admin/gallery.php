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

    if ($action === 'add') {
        $title    = sanitize($conn, $_POST['title'] ?? '');
        $type     = sanitize($conn, $_POST['ceremony_type']);
        $sub      = sanitize($conn, $_POST['sub_category'] ?? '');
        $img_url  = sanitize($conn, $_POST['image_url']);
        if ($img_url) {
            $stmt = $conn->prepare("INSERT INTO gallery (title,ceremony_type,sub_category,image_url) VALUES (?,?,?,?)");
            $stmt->bind_param("ssss", $title, $type, $sub, $img_url);
            $stmt->execute();
            flashMessage('success', 'Gallery image added!');
        }
        header("Location: gallery.php"); exit;
    }

    if ($action === 'delete') {
        $gid = intval($_POST['gallery_id']);
        $conn->query("DELETE FROM gallery WHERE id=$gid");
        flashMessage('success', 'Image deleted.');
        header("Location: gallery.php"); exit;
    }

    if ($action === 'toggle') {
        $gid = intval($_POST['gallery_id']);
        $conn->query("UPDATE gallery SET is_active = NOT is_active WHERE id=$gid");
        header("Location: gallery.php"); exit;
    }
}

$filter = $_GET['type'] ?? 'all';
$where = $filter !== 'all' ? "WHERE ceremony_type='$filter'" : '';
$gallery = $conn->query("SELECT * FROM gallery $where ORDER BY id DESC");
$counts = [];
foreach (['all','sangeet','mehendi','haldi','vivaha'] as $t) {
    $w = ($t !== 'all') ? "WHERE ceremony_type='$t'" : '';
    $counts[$t] = $conn->query("SELECT COUNT(*) as c FROM gallery $w")->fetch_assoc()['c'];
}
?>

<div class="admin-header">
    <div class="admin-title">🖼️ Gallery Management</div>
    <button onclick="document.getElementById('addGalleryModal').classList.add('open')" class="btn btn-primary">+ Add Image</button>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<!-- Filter Tabs -->
<div style="display:flex; gap:8px; margin-bottom:22px; flex-wrap:wrap">
    <?php foreach (['all','sangeet','mehendi','haldi','vivaha'] as $t):
        $icons = ['all'=>'🌸','sangeet'=>'🎵','mehendi'=>'🌿','haldi'=>'🌻','vivaha'=>'👰'];
    ?>
    <a href="gallery.php?type=<?= $t ?>"
       style="padding:8px 18px; border-radius:20px; font-size:0.88rem; font-weight:600; text-decoration:none; background:<?= $filter===$t?'var(--pink-main)':'#fff' ?>; color:<?= $filter===$t?'#fff':'#555' ?>; border:2px solid var(--pink-soft)">
        <?= $icons[$t] ?> <?= ucfirst($t) ?> (<?= $counts[$t] ?>)
    </a>
    <?php endforeach; ?>
</div>

<!-- Gallery Grid in admin -->
<div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:16px">
    <?php if ($gallery && $gallery->num_rows > 0): ?>
    <?php while ($g = $gallery->fetch_assoc()): ?>
    <div style="background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(0,0,0,0.08); position:relative">
        <img src="<?= htmlspecialchars($g['image_url']) ?>" alt="" style="width:100%; height:180px; object-fit:cover">
        <div style="padding:10px 12px">
            <div style="font-size:0.8rem; font-weight:600; color:var(--dark); margin-bottom:4px"><?= htmlspecialchars($g['title'] ?: 'Untitled') ?></div>
            <div style="font-size:0.75rem; color:#888; margin-bottom:10px">
                <span style="background:var(--pink-light); color:var(--pink-main); padding:2px 8px; border-radius:6px"><?= ucfirst($g['ceremony_type']) ?></span>
                <?php if ($g['sub_category']): ?>
                <span style="background:#f0f0f0; padding:2px 8px; border-radius:6px; margin-left:4px"><?= htmlspecialchars($g['sub_category']) ?></span>
                <?php endif; ?>
            </div>
            <div style="display:flex; gap:6px">
                <form method="POST" style="flex:1">
                    <input type="hidden" name="form_action" value="toggle">
                    <input type="hidden" name="gallery_id" value="<?= $g['id'] ?>">
                    <button type="submit" style="width:100%; background:<?= $g['is_active']?'#d1e7dd':'#f8d7da' ?>; color:<?= $g['is_active']?'#0f5132':'#842029' ?>; border:none; padding:5px; border-radius:6px; cursor:pointer; font-size:0.75rem">
                        <?= $g['is_active']?'✅ Visible':'❌ Hidden' ?>
                    </button>
                </form>
                <form method="POST" onsubmit="return confirm('Delete this image?')">
                    <input type="hidden" name="form_action" value="delete">
                    <input type="hidden" name="gallery_id" value="<?= $g['id'] ?>">
                    <button type="submit" style="background:#f8d7da; color:#842029; border:none; padding:5px 8px; border-radius:6px; cursor:pointer">🗑</button>
                </form>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
    <?php else: ?>
    <div style="grid-column:1/-1; text-align:center; padding:50px; color:#888; background:#fff; border-radius:14px">
        No gallery images found. Add some!
    </div>
    <?php endif; ?>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addGalleryModal">
    <div class="modal">
        <div class="modal-header">
            <h3 style="font-weight:700">🖼️ Add Gallery Image</h3>
            <button class="modal-close" onclick="document.getElementById('addGalleryModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="form_action" value="add">
            <div class="form-group" style="margin-bottom:14px">
                <label>Title (optional)</label>
                <input type="text" name="title" placeholder="Image title">
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label>Ceremony Type *</label>
                <select name="ceremony_type" required>
                    <?php foreach (['sangeet','mehendi','haldi','vivaha'] as $t): ?>
                    <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label>Sub-Category</label>
                <input type="text" name="sub_category" placeholder="e.g. Mehendi Venue, Front-side Mehendi">
            </div>
            <div class="form-group" style="margin-bottom:22px">
                <label>Image URL *</label>
                <input type="url" name="image_url" required placeholder="https://example.com/image.jpg">
                <small style="color:#888; font-size:0.8rem">Use direct image URL (Unsplash, etc.)</small>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Add Image</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
