<?php
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';
$activeTab = $_GET['tab'] ?? 'user-login';

// Handle Login (both user and planner use same endpoint)
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = sanitize($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $login_type = sanitize($conn, $_POST['login_type'] ?? 'user');

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin/index.php");
            } elseif ($user['role'] === 'planner') {
                header("Location: admin/planner-dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

// Handle Register (only for users)
if (isset($_POST['action']) && $_POST['action'] === 'register') {
    $activeTab = 'register-form';
    $name = sanitize($conn, $_POST['name'] ?? '');
    $email = sanitize($conn, $_POST['email'] ?? '');
    $phone = sanitize($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $cpassword = $_POST['confirm_password'] ?? '';

    if ($name && $email && $password && $cpassword) {
        if ($password !== $cpassword) {
            $error = "Passwords do not match.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } else {
            // Check existing
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $error = "Email already registered. Please login.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'user')");
                $stmt->bind_param("ssss", $name, $email, $phone, $hashed);
                if ($stmt->execute()) {
                    $success = "Account created! You can now login.";
                    $activeTab = 'user-login';
                } else {
                    $error = "Registration failed. Please try again.";
                }
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <div style="font-size:2.5rem">💍</div>
            <h2>AuraWedding</h2>
            <p>Your dream wedding starts here</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="auth-tabs">
            <div class="auth-tab <?= $activeTab==='planner-login'?'active':'' ?>" data-tab="planner-login">👰 Planner Login</div>
            <div class="auth-tab <?= $activeTab==='user-login'?'active':'' ?>" data-tab="user-login">👥 User Login</div>
            <div class="auth-tab <?= $activeTab==='register-form'?'active':'' ?>" data-tab="register-form">📝 Register</div>
        </div>

        <!-- PLANNER LOGIN FORM -->
        <div class="auth-form" id="planner-login" style="display:<?= $activeTab==='planner-login'?'block':'none' ?>">
            <h3 style="text-align:center; font-size:1.1rem; margin-bottom:20px; color:var(--pink-main)">Wedding Planner Portal</h3>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="login_type" value="planner">
                <div class="form-group" style="margin-bottom:16px">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="planner@email.com" required>
                </div>
                <div class="form-group" style="margin-bottom:24px">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; font-size:1rem; padding:14px">Sign In →</button>
            </form>

            <div style="text-align:center; margin-top:20px; padding:16px; background:var(--gray); border-radius:12px; font-size:0.85rem; color:#666">
                <strong>📌 Welcome Back Boss...g</strong><br>
            </div>
        </div>

        <!-- USER LOGIN FORM -->
        <div class="auth-form" id="user-login" style="display:<?= $activeTab==='user-login'?'block':'none' ?>">
            <h3 style="text-align:center; font-size:1.1rem; margin-bottom:20px; color:var(--dark)">Customer Portal</h3>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="login_type" value="user">
                <div class="form-group" style="margin-bottom:16px">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group" style="margin-bottom:24px">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; font-size:1rem; padding:14px">Sign In →</button>
            </form>

            <div style="text-align:center; margin-top:20px; padding:16px; background:var(--gray); border-radius:12px; font-size:0.85rem; color:#666">
                <strong>💡 Don't have an account?</strong><br>
                <a href="index.php?page=login&tab=register-form" style="color:var(--pink-main); font-weight:600">Register here →</a>
            </div>
        </div>

        <!-- REGISTER FORM -->
        <div class="auth-form" id="register-form" style="display:<?= $activeTab==='register-form'?'block':'none' ?>">
            <h3 style="text-align:center; font-size:1.1rem; margin-bottom:20px; color:var(--dark)">Create Your Account</h3>
            <form method="POST">
                <input type="hidden" name="action" value="register">
                <div class="form-group" style="margin-bottom:14px">
                    <label>Full Name *</label>
                    <input type="text" name="name" placeholder="Your Full Name" required>
                </div>
                <div class="form-group" style="margin-bottom:14px">
                    <label>Email Address *</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>
                <div class="form-group" style="margin-bottom:14px">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" placeholder="+91 98765 43210">
                </div>
                <div class="form-group" style="margin-bottom:14px">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required>
                </div>
                <div class="form-group" style="margin-bottom:24px">
                    <label>Confirm Password *</label>
                    <input type="password" name="confirm_password" placeholder="Repeat password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; font-size:1rem; padding:14px">Create Account →</button>
            </form>
        </div>

        <div style="text-align:center; margin-top:20px">
            <a href="index.php" style="color:#888; font-size:0.9rem; text-decoration:none">← Back to Home</a>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.auth-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        const tabName = this.dataset.tab;
        document.querySelectorAll('.auth-form').forEach(form => form.style.display = 'none');
        document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
        document.getElementById(tabName).style.display = 'block';
        this.classList.add('active');
    });
});
</script>
