<?php include 'header.php'; ?>

<?php
// Admin only
if (!isAdmin()) {
    header("Location: planner-dashboard.php");
    exit;
}

$flash = getFlash();

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';

    if ($action === 'add') {
        $name  = sanitize($conn, $_POST['name'] ?? '');
        $email = sanitize($conn, $_POST['email'] ?? '');
        $phone = sanitize($conn, $_POST['phone'] ?? '');
        $role  = sanitize($conn, $_POST['role'] ?? 'user');
        $pass  = $_POST['password'] ?? '';
        if ($name && $email && $pass) {
            $hashed = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?,?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $hashed, $role);
            $stmt->execute() ? flashMessage('success','User added!') : flashMessage('error','Email already exists.');
        }
        header("Location: users.php"); exit;
    }

    if ($action === 'delete') {
        $uid = intval($_POST['user_id']);
        $conn->query("DELETE FROM users WHERE id=$uid AND role='user'");
        flashMessage('success', 'User deleted.');
        header("Location: users.php"); exit;
    }

    if ($action === 'change_role') {
        $uid  = intval($_POST['user_id']);
        $role = sanitize($conn, $_POST['role']);
        $stmt = $conn->prepare("UPDATE users SET role=? WHERE id=?");
        $stmt->bind_param("si", $role, $uid);
        $stmt->execute();
        flashMessage('success', 'Role updated.');
        header("Location: users.php"); exit;
    }
}

$users = $conn->query("SELECT u.*, COUNT(b.id) as booking_count FROM users u LEFT JOIN bookings b ON u.id=b.user_id GROUP BY u.id ORDER BY u.created_at DESC");
?>

<div class="admin-header">
    <div class="admin-title">👥 Users Management</div>
    <button onclick="document.getElementById('addUserModal').classList.add('open')" class="btn btn-primary">+ Add User</button>
</div>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>"><?= htmlspecialchars($flash['message']) ?></div>
<?php endif; ?>

<div style="background:#fff; border-radius:12px; padding:12px 16px; margin-bottom:14px">
    <input type="text" id="searchInput" placeholder="🔍 Search users..." style="padding:10px 14px; border:2px solid #e8e8e8; border-radius:10px; font-size:0.9rem; outline:none; width:100%; max-width:400px" oninput="searchTable('searchInput','usersTable')">
</div>

<div class="data-table">
    <table id="usersTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Bookings</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users && $users->num_rows > 0): ?>
            <?php while ($u = $users->fetch_assoc()): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="form_action" value="change_role">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <select name="role" onchange="this.form.submit()" style="padding:4px 8px; border-radius:6px; border:1px solid #ddd; font-size:0.85rem">
                            <option value="user" <?= $u['role']==='user'?'selected':'' ?>>User</option>
                            <option value="planner" <?= $u['role']==='planner'?'selected':'' ?>>Planner</option>
                            <option value="admin" <?= $u['role']==='admin'?'selected':'' ?>>Admin</option>
                        </select>
                    </form>
                </td>
                <td><span style="background:var(--pink-light); color:var(--pink-main); padding:3px 10px; border-radius:10px; font-size:0.85rem; font-weight:600"><?= $u['booking_count'] ?></span></td>
                <td style="font-size:0.82rem; color:#888"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <?php if ($u['role'] === 'user'): ?>
                    <form method="POST" onsubmit="return confirm('Delete this user?')">
                        <input type="hidden" name="form_action" value="delete">
                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#f8d7da; color:#842029; border:none; cursor:pointer; padding:5px 12px; border-radius:8px">🗑 Delete</button>
                    </form>
                    <?php else: ?>
                    <span style="font-size:0.8rem; color:#888">Protected</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php else: ?>
            <tr><td colspan="8" style="text-align:center; padding:30px; color:#888">No users found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3 style="font-size:1.2rem; font-weight:700">➕ Add New User</h3>
            <button class="modal-close" onclick="document.getElementById('addUserModal').classList.remove('open')">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="form_action" value="add">
            <div class="form-group" style="margin-bottom:14px">
                <label>Full Name *</label>
                <input type="text" name="name" required placeholder="Full name">
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label>Email *</label>
                <input type="email" name="email" required placeholder="email@example.com">
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label>Phone</label>
                <input type="tel" name="phone" placeholder="+91 ...">
            </div>
            <div class="form-group" style="margin-bottom:14px">
                <label>Password *</label>
                <input type="password" name="password" required placeholder="Min. 6 characters">
            </div>
            <div class="form-group" style="margin-bottom:22px">
                <label>Role</label>
                <select name="role">
                    <option value="user">User</option>
                    <option value="planner">Planner</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Add User</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
