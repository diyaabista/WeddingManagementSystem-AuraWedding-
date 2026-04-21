<?php
// ============================================================
// AuraWedding - Wedding Planner Dashboard (planner.php)
// ============================================================
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? false)) {
    header('Location: index.html');
    exit();
}

require_once 'php/config.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? 'user@example.com';

$db = getDB();

// Fetch user's bookings
$bookingStmt = $db->prepare("
    SELECT b.*, 
           COUNT(DISTINCT be.id) as event_count,
           COUNT(DISTINCT bg.id) as guest_count,
           COUNT(DISTINCT br.id) as room_count
    FROM bookings b
    LEFT JOIN booking_events be ON b.id = be.booking_id
    LEFT JOIN booking_guests bg ON be.id = bg.event_id
    LEFT JOIN booking_rooms br ON be.id = br.event_id
    WHERE b.user_id = ?
    GROUP BY b.id
    ORDER BY b.created_at DESC
");
$bookingStmt->bind_param('i', $userId);
$bookingStmt->execute();
$bookingResult = $bookingStmt->get_result();
$bookings = [];
while ($booking = $bookingResult->fetch_assoc()) {
    $bookings[] = $booking;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Wedding Planner - AuraWedding</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<nav class="navbar">
  <div class="container">
    <div class="navbar-inner">
      <a href="index.html" class="logo">
        <span style="font-size:1.8rem">💍</span>
        <span class="logo-text">AuraWedding</span>
      </a>

      <button type="button" class="nav-toggle" onclick="toggleMenu()">☰</button>

      <ul class="nav-links" id="navLinks">
        <li><a href="index.html">Home</a></li>
        <li><a href="user.php">Dashboard</a></li>
        <li><a href="planner.php" class="active">📋 Wedding Planner</a></li>
      </ul>

      <div style="display:flex;align-items:center;gap:10px">
        <span id="userName" style="color:var(--pink-main);font-weight:700"><?php echo htmlspecialchars($userName); ?></span>
        <button type="button" class="btn btn-danger btn-sm" onclick="logout()">Logout</button>
      </div>
    </div>
  </div>
</nav>

<!-- ============================================================
     TOAST CONTAINER
     ============================================================ -->
<div class="toast-container" id="toastContainer"></div>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<main class="container" style="padding: 60px 0">
  <section class="section-page">
    <div class="section-header">
      <h2>💍 Wedding Planner</h2>
      <p>Create and manage your perfect wedding celebration</p>
    </div>

    <!-- New Booking Button -->
    <div style="margin-bottom: 30px; text-align: center;">
      <button type="button" class="btn btn-marriage" onclick="goToPlanner()">
        ➕ Create New Wedding Plan
      </button>
    </div>

    <!-- Existing Bookings -->
    <div style="margin-top: 40px;">
      <h3 style="margin-bottom: 20px;">Your Wedding Plans</h3>
      
      <?php if (empty($bookings)): ?>
        <div style="text-align: center; padding: 40px; background: var(--bg-light); border-radius: 12px;">
          <p style="color: var(--text-light); margin-bottom: 20px;">No wedding plans yet. Create your first plan to get started!</p>
          <button type="button" class="btn btn-marriage" onclick="goToPlanner()">
            Start Planning
          </button>
        </div>
      <?php else: ?>
        <div style="display: grid; gap: 20px;">
          <?php foreach ($bookings as $booking): ?>
            <div style="border: 1px solid var(--pink-light); border-radius: 12px; padding: 20px; background: white;">
              <div style="display: flex; justify-content: space-between; align-items: start; gap: 20px;">
                <div>
                  <h4 style="margin: 0 0 10px 0; color: var(--pink-main);">
                    👰 <?php echo htmlspecialchars($booking['bride_name'] ?? 'Wedding Plan'); ?>
                    <?php if ($booking['groom_name']): ?>
                      & 🤵 <?php echo htmlspecialchars($booking['groom_name']); ?>
                    <?php endif; ?>
                  </h4>
                  <p style="margin: 5px 0; color: var(--text-mid);">
                    📅 <?php echo $booking['event_count']; ?> Events | 
                    👥 <?php echo $booking['guest_count']; ?> Guests | 
                    🏨 <?php echo $booking['room_count']; ?> Rooms
                  </p>
                  <p style="margin: 10px 0 0 0; color: var(--text-light); font-size: 0.9rem;">
                    Status: <strong><?php echo ucfirst(htmlspecialchars($booking['status'])); ?></strong>
                  </p>
                </div>
                <div style="display: flex; gap: 10px;">
                  <?php if ($booking['status'] === 'pending'): ?>
                    <button type="button" class="btn btn-outline" onclick="editPlan(<?php echo $booking['id']; ?>)">
                      Edit
                    </button>
                  <?php endif; ?>
                  <button type="button" class="btn btn-outline" onclick="viewPlan(<?php echo $booking['id']; ?>)">
                    View
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Info Section -->
    <div style="margin-top: 60px; padding: 30px; background: var(--pink-light); border-radius: 12px; text-align: center;">
      <h3 style="margin-top: 0; color: var(--pink-main);">Need Help?</h3>
      <p>Our wedding planning team is here to assist you at every step.</p>
      <p style="font-size: 0.9rem; color: var(--text-light);">
        <strong>Contact us:</strong> support@aurawedding.com | +91 XXXX-XXXX-XXXX
      </p>
    </div>
  </section>
</main>

<script>
  // Navigation
  function goToPlanner() {
    window.location.href = 'index.html?page=wedding-planner';
  }

  function editPlan(bookingId) {
    // Navigate to wedding planner with edit mode
    window.location.href = 'index.html?page=wedding-planner&edit=' + bookingId;
  }

  function viewPlan(bookingId) {
    // Show booking details
    alert('Booking ID: ' + bookingId);
  }

  function toggleMenu() {
    const navLinks = document.getElementById('navLinks');
    if (navLinks) navLinks.classList.toggle('active');
  }

  function logout() {
    if (confirm('Are you sure you want to logout?')) {
      fetch('php/auth.php', {
        method: 'POST',
        body: new URLSearchParams({ action: 'logout' })
      }).then(() => {
        window.location.href = 'index.html';
      });
    }
  }

  // Load user info on page load
  window.addEventListener('DOMContentLoaded', () => {
    console.log('✨ Wedding Planner loaded!');
  });
</script>

</body>
</html>
