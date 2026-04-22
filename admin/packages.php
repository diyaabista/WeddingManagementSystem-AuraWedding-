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

    if ($action === 'add' || $action === 'edit') {
        $name     = sanitize($conn, $_POST['name']);
        $type     = sanitize($conn, $_POST['ceremony_type']);
        $desc     = sanitize($conn, $_POST['description']);
        $price    = floatval($_POST['price']);
        $duration = sanitize($conn, $_POST['duration']);
        $features = sanitize($conn, $_POST['features']);
        $active   = intval($_POST['is_active'] ?? 1);

        if ($action === 'add') {
            $stmt = $conn->prepare("INSERT INTO packages (name,ceremony_type,description,price,duration,features,is_active) VALUES (?,?,?,?,?,?,?)");
            $stmt->bind_param("sssdssi", $name, $type, $desc, $price, $duration, $features, $active);
            $stmt->execute();
            flashMessage('success', 'Package added!');
        } else {
            $pid = intval($_POST['package_id']);
            $stmt = $conn->prepare("UPDATE packages SET name=?,ceremony_type=?,description=?,price=?,duration=?,features=?,is_active=? WHERE id=?");
            $stmt->bind_param("sssdssi i", $name, $type, $desc, $price, $duration, $features, $active, $pid);
            $stmt->bind_param("sssdssii", $name, $type, $desc, $price, $duration, $features, $active, $pid);
            $stmt->execute();
            flashMessage('success', 'Package updated!');
        }
        header("Location: packages.php"); exit;
    }

    if ($action === 'delete') {
        $pid = intval($_POST['package_id']);
        $conn->query("DELETE FROM packages WHERE id=$pid");
        flashMessage('success', 'Package deleted.');
        header("Location: packages.php"); exit;
    }

    if ($action === 'toggle') {
        $pid = intval($_POST['package_id']);
        $conn->query("UPDATE packages SET is_active = NOT is_active WHERE id=$pid");
        header("Location: packages.php"); exit;
    }
}

$packages = $conn->query("SELECT * FROM packages ORDER BY price ASC");
$edit = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $edit = $conn->query("SELECT * FROM packages WHERE id=$eid")->fetch_assoc();
}
?>

<div class="admin-header">
    <div class="admin-title">📦 Packages Management</div>
    <button onclick="document.getElementById('addPkgModal').classList.add('open')" class="btn btn-primary">+ Add Package</button>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<?php if ($edit): ?>
<!-- Edit Form -->
<div style="background:#fff; border-radius:18px; padding:28px; box-shadow:0 4px 20px rgba(0,0,0,0.08); margin-bottom:24px">
    <h3 style="margin-bottom:20px; color:var(--dark)">✏️ Edit Package: <?= htmlspecialchars($edit['name']) ?></h3>
    <form method="POST">
        <input type="hidden" name="form_action" value="edit">
        <input type="hidden" name="package_id" value="<?= $edit['id'] ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Package Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($edit['name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Ceremony Type *</label>
                <select name="ceremony_type">
                    <?php foreach (['sangeet','mehendi','haldi','vivaha','full'] as $t): ?>
                    <option value="<?= $t ?>" <?= $edit['ceremony_type']===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Price (₹) *</label>
                <input type="number" name="price" value="<?= $edit['price'] ?>" required>
            </div>
            <div class="form-group">
                <label>Duration</label>
                <input type="text" name="duration" value="<?= htmlspecialchars($edit['duration']) ?>" placeholder="e.g. 1 Day">
            </div>
            <div class="form-group full">
                <label>Description</label>
                <textarea name="description" rows="2"><?= htmlspecialchars($edit['description']) ?></textarea>
            </div>
            <div class="form-group full">
                <label>Features (comma-separated)</label>
                <input type="text" name="features" value="<?= htmlspecialchars($edit['features']) ?>" placeholder="Feature 1,Feature 2,Feature 3">
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="is_active">
                    <option value="1" <?= $edit['is_active']?'selected':'' ?>>Active</option>
                    <option value="0" <?= !$edit['is_active']?'selected':'' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div style="margin-top:16px; display:flex; gap:10px">
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="packages.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>
<?php endif; ?>

<div class="data-table">
    <table>
        <thead>
            <tr>
                <th>#</th><th>Name</th><th>Type</th><th>Price</th><th>Duration</th><th>Status</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($packages && $packages->num_rows > 0): ?>
            <?php while ($p = $packages->fetch_assoc()): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                <td><span style="background:var(--pink-light); color:var(--pink-main); padding:3px 10px; border-radius:10px; font-size:0.85rem"><?= ucfirst($p['ceremony_type']) ?></span></td>
                <td style="font-weight:700; color:var(--pink-main)">₹<?= number_format($p['price']) ?></td>
                <td><?= htmlspecialchars($p['duration']) ?></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="form_action" value="toggle">
                        <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
                        <button type="submit" style="background:<?= $p['is_active'] ? '#d1e7dd' : '#f8d7da' ?>; color:<?= $p['is_active'] ? '#0f5132' : '#842029' ?>; border:none; padding:4px 12px; border-radius:10px; cursor:pointer; font-size:0.85rem">
                            <?= $p['is_active'] ? '✅ Active' : '❌ Inactive' ?>
                        </button>
                    </form>
                </td>
                <td style="display:flex; gap:6px">
                    <a href="packages.php?edit=<?= $p['id'] ?>" class="btn btn-sm btn-outline">✏️ Edit</a>
                    <form method="POST" onsubmit="return confirm('Delete this package?')">
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#f8d7da; color:#842029; border:none; cursor:pointer">🗑</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="7" style="text-align:center; padding:30px; color:#888">No packages found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Modal -->
<div class="modal-overlay" id="addPkgModal">
    <div class="modal" style="max-width:640px">
        <div class="modal-header">
            <h3 style="font-weight:700">➕ Add New Package</h3>
            <button class="modal-close" onclick="document.getElementById('addPkgModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="form_action" value="add">
            <div class="form-grid">
                <div class="form-group">
                    <label>Package Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Royal Vivaha">
                </div>
                <div class="form-group">
                    <label>Ceremony Type *</label>
                    <select name="ceremony_type">
                        <?php foreach (['sangeet','mehendi','haldi','vivaha','full'] as $t): ?>
                        <option value="<?= $t ?>"><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (₹) *</label>
                    <input type="number" name="price" required placeholder="e.g. 50000">
                </div>
                <div class="form-group">
                    <label>Duration</label>
                    <input type="text" name="duration" placeholder="e.g. 1 Day">
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" rows="2" placeholder="Package description..."></textarea>
                </div>
                <div class="form-group full">
                    <label>Features (comma-separated)</label>
                    <input type="text" name="features" placeholder="DJ,Decor,Catering,Photography">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; margin-top:16px">Add Package</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
