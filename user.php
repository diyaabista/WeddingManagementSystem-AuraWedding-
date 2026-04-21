<?php
// ============================================================
// AuraWedding - User Dashboard (user.php)
// ============================================================
session_start();

// Redirect to login if not logged in or if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? false)) {
    header('Location: index.html');
    exit();
}

require_once 'php/config.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$weddingId = $_SESSION['wedding_id'] ?? null;

$db = getDB();

// Get user email
$userStmt = $db->prepare("SELECT email FROM users WHERE id = ?");
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userEmailResult = $userStmt->get_result()->fetch_assoc();
$userEmail = $userEmailResult['email'] ?? 'user@example.com';

// Fetch wedding details
$weddingData = null;
$guests = [];
$totalGuests = 0;
$confirmedGuests = 0;
$pendingGuests = 0;

if ($weddingId) {
    // Get wedding details
    $wStmt = $db->prepare("SELECT * FROM weddings WHERE id = ? AND user_id = ?");
    $wStmt->bind_param('ii', $weddingId, $userId);
    $wStmt->execute();
    $weddingData = $wStmt->get_result()->fetch_assoc();

    // Get guests
    $gStmt = $db->prepare("SELECT * FROM guests WHERE wedding_id = ? ORDER BY name ASC");
    $gStmt->bind_param('i', $weddingId);
    $gStmt->execute();
    $gResult = $gStmt->get_result();
    while ($guest = $gResult->fetch_assoc()) {
        $guests[] = $guest;
        $totalGuests++;
        if ($guest['rsvp_status'] === 'confirmed') $confirmedGuests++;
        if ($guest['rsvp_status'] === 'pending') $pendingGuests++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wedding - AuraWedding</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { 
            background: linear-gradient(135deg, var(--pink-light) 0%, #fff0f5 100%);
            padding: 20px;
        }
        .user-header {
            background: white;
            padding: 30px 40px;
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .user-header h1 {
            margin: 0;
            color: var(--pink-main);
            font-size: 2rem;
        }
        .user-info {
            display: flex;
            gap: 30px;
            align-items: center;
        }
        .user-details {
            text-align: right;
        }
        .user-details p {
            margin: 5px 0;
            color: var(--text-light);
            font-size: 0.95rem;
        }
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            text-align: center;
            border-top: 4px solid var(--pink-main);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--pink-main);
            margin: 10px 0;
        }
        .stat-label {
            color: var(--text-light);
            font-weight: 600;
            font-size: 0.9rem;
        }
        .section-card {
            background: white;
            border-radius: var(--radius-md);
            padding: 30px;
            box-shadow: var(--shadow-sm);
            margin-bottom: 25px;
        }
        .section-card h2 {
            margin-top: 0;
            color: var(--pink-deep);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .nav-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .nav-buttons a, .nav-buttons button {
            text-decoration: none;
            padding: 10px 20px;
            border-radius: var(--radius-md);
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }
        .nav-buttons .btn-primary {
            background: linear-gradient(135deg, var(--pink-main), var(--pink-deep));
            color: white;
        }
        .nav-buttons .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .nav-buttons .btn-secondary {
            background: var(--pink-light);
            color: var(--pink-deep);
            border: 2px solid var(--pink-soft);
        }
        .nav-buttons .btn-secondary:hover {
            background: var(--pink-soft);
        }
        .guest-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .guest-table th {
            background: var(--pink-light);
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: var(--pink-deep);
            border-bottom: 2px solid var(--pink-soft);
        }
        .guest-table td {
            padding: 12px;
            border-bottom: 1px solid var(--pink-light);
        }
        .guest-table tr:hover {
            background: #f9f9f9;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-confirmed {
            background: #d4edda;
            color: #155724;
        }
        .badge-pending {
            background: #fff3cd;
            color: #856404;
        }
        .badge-declined {
            background: #f8d7da;
            color: #721c24;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--text-light);
        }
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="user-header">
        <div>
            <h1>💍 <?php echo htmlspecialchars($userName); ?>'s Wedding</h1>
            <p style="color:var(--text-light);margin:5px 0">Plan and manage your perfect day</p>
        </div>
        <div class="user-info">
            <div class="user-details">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($userEmail); ?></p>
                <p><strong>User ID:</strong> #<?php echo $userId; ?></p>
            </div>
            <button class="btn btn-danger btn-sm" onclick="logout()">Logout 🚪</button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-container">
        <div class="stat-card">
            <div style="font-size:2rem">👥</div>
            <div class="stat-number"><?php echo $totalGuests; ?></div>
            <div class="stat-label">Total Guests</div>
        </div>
        <div class="stat-card">
            <div style="font-size:2rem">✅</div>
            <div class="stat-number"><?php echo $confirmedGuests; ?></div>
            <div class="stat-label">Confirmed</div>
        </div>
        <div class="stat-card">
            <div style="font-size:2rem">⏳</div>
            <div class="stat-number"><?php echo $pendingGuests; ?></div>
            <div class="stat-label">Pending RSVP</div>
        </div>
        <div class="stat-card">
            <div style="font-size:2rem">🎊</div>
            <div class="stat-number"><?php echo $weddingData ? 1 : 0; ?></div>
            <div class="stat-label">Wedding Planned</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="section-card">
        <h2>🚀 Quick Actions</h2>
        <div class="nav-buttons">
            <button class="btn-primary" onclick="goToApp('dashboard')">📊 My Dashboard</button>
            <button class="btn-primary" onclick="goToApp('guests')">👥 Manage Guests</button>
            <button class="btn-secondary" onclick="goToApp('sangeet')">🎵 Sangeet Gallery</button>
            <button class="btn-secondary" onclick="goToApp('mehendi')">🌿 Mehendi Designs</button>
            <button class="btn-secondary" onclick="goToApp('haldi')">🌼 Haldi Ideas</button>
        </div>
    </div>

    <!-- Wedding Details -->
    <div class="section-card">
        <h2>💒 Wedding Details</h2>
        <?php if ($weddingData): ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-top:20px">
                <div style="background:var(--pink-light);padding:20px;border-radius:var(--radius-md)">
                    <p style="color:var(--text-light);font-size:0.9rem;margin:0 0 10px 0">Wedding Date</p>
                    <p style="font-size:1.3rem;font-weight:700;color:var(--pink-deep);margin:0">
                        <?php echo $weddingData['wedding_date'] ? date('d M Y', strtotime($weddingData['wedding_date'])) : 'Not Set'; ?>
                    </p>
                </div>
                <div style="background:var(--pink-light);padding:20px;border-radius:var(--radius-md)">
                    <p style="color:var(--text-light);font-size:0.9rem;margin:0 0 10px 0">Wedding ID</p>
                    <p style="font-size:1.3rem;font-weight:700;color:var(--pink-deep);margin:0">#<?php echo $weddingId; ?></p>
                </div>
                <div style="background:var(--pink-light);padding:20px;border-radius:var(--radius-md)">
                    <p style="color:var(--text-light);font-size:0.9rem;margin:0 0 10px 0">Status</p>
                    <p style="font-size:1.3rem;font-weight:700;color:#56ab2f;margin:0">🎊 Active</p>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">💒</div>
                <h3>No Wedding Planned Yet</h3>
                <p>Create your wedding profile in the dashboard to get started!</p>
                <button class="btn btn-marriage" onclick="goToApp('dashboard')">Start Planning →</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Guests List -->
    <div class="section-card">
        <h2>👥 Guest List (<?php echo count($guests); ?> Guests)</h2>
        <?php if (count($guests) > 0): ?>
            <table class="guest-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Relation</th>
                        <th>RSVP Status</th>
                        <th>Hotel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($guests as $guest): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($guest['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($guest['phone'] ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($guest['relation'] ?? '—'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $guest['rsvp_status']; ?>">
                                    <?php echo ucfirst($guest['rsvp_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                if ($guest['hotel_name']) {
                                    echo htmlspecialchars($guest['hotel_name']) . ' (Rm ' . htmlspecialchars($guest['room_number'] ?? '') . ')';
                                } else {
                                    echo '—';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">👥</div>
                <h3>No Guests Added Yet</h3>
                <p>Add guests to your wedding in the dashboard!</p>
                <button class="btn btn-marriage" onclick="goToApp('guests')">Add Guests →</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Help Section -->
    <div class="section-card" style="background:linear-gradient(135deg,var(--pink-light),var(--rose-light));border-top:4px solid var(--pink-main)">
        <h2 style="color:var(--pink-deep)">❓ Need Help?</h2>
        <p style="color:var(--text-mid)">
            Manage every detail of your wedding with AuraWedding's complete platform. 
            Explore galleries, manage guests, set up your ceremony, and track RSVPs all in one place.
        </p>
        <div style="display:flex;gap:15px;flex-wrap:wrap;margin-top:20px">
            <button class="btn btn-marriage" onclick="goToApp('dashboard')">Go to Dashboard</button>
            <button class="btn btn-outline" onclick="logout()">Logout</button>
        </div>
    </div>

</div>

<script>
function goToApp(page) {
    window.location.href = 'index.html?page=' + page;
}

function logout() {
    fetch('php/auth.php', {
        method: 'POST',
        body: new URLSearchParams({
            action: 'logout'
        })
    }).then(() => {
        window.location.href = 'index.html';
    });
}
</script>

</body>
</html>
