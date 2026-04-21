<?php
// ============================================================
// AuraWedding - User Dashboard (php/user_dashboard.php)
// ============================================================
session_start();

// Redirect to login if not logged in or if admin
if (!isset($_SESSION['user_id']) || ($_SESSION['is_admin'] ?? false)) {
    header('Location: ../index.html');
    exit();
}

require_once 'config.php';

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'] ?? 'User';
$weddingId = $_SESSION['wedding_id'] ?? null;

$db = getDB();

// Get user email
$userStmt = $db->prepare("SELECT email FROM users WHERE id = ?");
$userStmt->bind_param('i', $userId);
$userStmt->execute();
$userEmailResult = $userStmt->get_result()->fetch_assoc();
$userEmail = $userEmailResult['email'] ?? null;

// Fetch wedding details
$weddingData = null;
$subEvents = [];
$guests = [];
$userBookings = [];
$bookingStatus = 'none';

if ($weddingId) {
    // Get wedding details
    $wStmt = $db->prepare("SELECT * FROM weddings WHERE id = ? AND user_id = ?");
    $wStmt->bind_param('ii', $weddingId, $userId);
    $wStmt->execute();
    $weddingData = $wStmt->get_result()->fetch_assoc();

    // Get sub-events
    $eStmt = $db->prepare("SELECT * FROM sub_events WHERE wedding_id = ? ORDER BY event_date ASC");
    $eStmt->bind_param('i', $weddingId);
    $eStmt->execute();
    $eResult = $eStmt->get_result();
    while ($event = $eResult->fetch_assoc()) {
        $subEvents[] = $event;
    }

    // Get guest count
    $gStmt = $db->prepare("SELECT COUNT(*) as count FROM guests WHERE wedding_id = ?");
    $gStmt->bind_param('i', $weddingId);
    $gStmt->execute();
    $gResult = $gStmt->get_result()->fetch_assoc();
    $guestCount = $gResult['count'] ?? 0;

    // Get user's bookings from bookings table
    if ($userEmail) {
        $bStmt = $db->prepare("SELECT * FROM bookings WHERE client_email = ? ORDER BY created_at DESC");
        $bStmt->bind_param('s', $userEmail);
        $bStmt->execute();
        $bResult = $bStmt->get_result();
        while ($booking = $bResult->fetch_assoc()) {
            $userBookings[] = $booking;
        }
        
        // Get the latest booking status
        if (!empty($userBookings)) {
            $bookingStatus = $userBookings[0]['status'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wedding Dashboard - AuraWedding</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, var(--bg-light) 0%, #f5e6f5 100%);
            padding: 20px;
        }
        .dashboard-container {
            max-width: 1100px;
            margin: 0 auto;
        }
        .dashboard-header {
            background: white;
            padding: 30px;
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .header-content h1 {
            margin: 0;
            color: var(--pink-main);
            font-size: 2rem;
        }
        .header-content p {
            margin: 5px 0 0 0;
            color: var(--text-mid);
            font-size: 0.95rem;
        }
        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        .dashboard-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-top: 4px solid var(--pink-main);
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-content {
            color: var(--text-mid);
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .card-content strong {
            color: var(--text-dark);
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-top: 10px;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }
        .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        .events-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .events-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .events-list li:last-child {
            border-bottom: none;
        }
        .event-type {
            font-weight: 700;
            color: var(--pink-main);
            text-transform: capitalize;
        }
        .event-date {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .full-width {
            grid-column: 1 / -1;
        }
        .btn-back {
            background: var(--text-light);
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .btn-back:hover {
            background: var(--text-dark);
        }
        .quick-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 10px;
        }
        .quick-link-btn {
            padding: 12px;
            text-align: center;
            background: var(--pink-light);
            color: var(--pink-main);
            border: 2px solid var(--pink-main);
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
        }
        .quick-link-btn:hover {
            background: var(--pink-main);
            color: white;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1>💍 My Wedding Dashboard</h1>
                <p>Welcome, <strong><?= htmlspecialchars($userName) ?></strong>!</p>
            </div>
            <div class="header-actions">
                <a href="../index.html" class="btn-back">← Back to Site</a>
                <form style="display:inline" method="POST" action="auth.php">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn-back" style="background: #dc3545">Logout</button>
                </form>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Wedding Info Card -->
            <div class="dashboard-card">
                <h3 class="card-title">💒 Wedding Details</h3>
                <div class="card-content">
                    <?php if ($weddingData): ?>
                        <p><strong>Bride:</strong><br><?= htmlspecialchars($weddingData['bride_name'] ?? 'Not Set') ?></p>
                        <p><strong>Groom:</strong><br><?= htmlspecialchars($weddingData['groom_name'] ?? 'Not Set') ?></p>
                        <p><strong>Venue:</strong><br><?= htmlspecialchars($weddingData['venue'] ?? 'Not Set') ?></p>
                        <p><strong>Wedding Date:</strong><br><?= $weddingData['wedding_date'] ? date('M d, Y', strtotime($weddingData['wedding_date'])) : 'Not Set' ?></p>
                        <p><strong>Budget:</strong><br>₹<?= number_format($weddingData['total_budget'] ?? 0) ?></p>
                    <?php else: ?>
                        <p style="color: var(--text-light)">No wedding details found. Please set up your wedding information.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Booking Status Card -->
            <div class="dashboard-card">
                <h3 class="card-title">📋 Booking Status</h3>
                <div class="card-content">
                    <?php if ($bookingStatus === 'none'): ?>
                        <p style="color: var(--text-light);">No bookings submitted yet.</p>
                        <a href="../index.html" style="color: var(--pink-main); font-weight: 700; text-decoration: none;">← Go back and submit a booking</a>
                    <?php else: ?>
                        <p>Latest booking status:</p>
                        <span class="status-badge status-<?= htmlspecialchars($bookingStatus) ?>">
                            <?= ucfirst(htmlspecialchars($bookingStatus)) ?>
                        </span>
                        <?php if ($bookingStatus === 'pending'): ?>
                            <p style="margin-top: 15px; font-size: 0.9rem; color: var(--text-light);">
                                ⏳ Your booking is pending admin review. You'll be notified once it's accepted or rejected.
                            </p>
                        <?php elseif ($bookingStatus === 'confirmed' || $bookingStatus === 'accepted'): ?>
                            <p style="margin-top: 15px; font-size: 0.9rem; color: #155724;">
                                ✅ Your booking has been confirmed! Admin will proceed with the arrangement.
                            </p>
                        <?php elseif ($bookingStatus === 'rejected'): ?>
                            <p style="margin-top: 15px; font-size: 0.9rem; color: #721c24;">
                                ❌ Your booking was rejected. Please contact admin for more details.
                            </p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- All Bookings Card (Full Width) -->
            <?php if (!empty($userBookings)): ?>
            <div class="dashboard-card full-width">
                <h3 class="card-title">📦 Your Bookings</h3>
                <div class="card-content">
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--pink-main);">
                                    <th style="padding: 12px; text-align: left; color: var(--pink-main); font-weight: 700;">Date</th>
                                    <th style="padding: 12px; text-align: left; color: var(--pink-main); font-weight: 700;">Items</th>
                                    <th style="padding: 12px; text-align: left; color: var(--pink-main); font-weight: 700;">Status</th>
                                    <th style="padding: 12px; text-align: left; color: var(--pink-main); font-weight: 700;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userBookings as $booking): 
                                    $items = json_decode($booking['selected_items'], true);
                                    if (!is_array($items)) $items = [];
                                    
                                    // Fetch booking events
                                    $eventStmt = $db->prepare("SELECT * FROM booking_events WHERE booking_id = ? ORDER BY event_date ASC");
                                    $eventStmt->bind_param('i', $booking['id']);
                                    $eventStmt->execute();
                                    $bookingEvents = $eventStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                                ?>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <td style="padding: 12px;"><?= date('M d, Y', strtotime($booking['created_at'])) ?></td>
                                    <td style="padding: 12px;">
                                        <ul style="margin: 0; padding-left: 20px; font-size: 0.9rem;">
                                            <?php if (empty($items)): ?>
                                                <li style="color:var(--text-light)">No items</li>
                                            <?php else: ?>
                                                <?php foreach (array_slice($items, 0, 2) as $item): ?>
                                                    <li><?= htmlspecialchars($item) ?></li>
                                                <?php endforeach; ?>
                                                <?php if (count($items) > 2): ?>
                                                    <li style="color: var(--text-light);">+<?= count($items) - 2 ?> more items</li>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                    <td style="padding: 12px;">
                                        <span style="padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 700;
                                            <?php
                                                if ($booking['status'] === 'pending') echo 'background: #fff3cd; color: #856404;';
                                                elseif ($booking['status'] === 'confirmed' || $booking['status'] === 'accepted') echo 'background: #d4edda; color: #155724;';
                                                elseif ($booking['status'] === 'rejected') echo 'background: #f8d7da; color: #721c24;';
                                            ?>
                                        ">
                                            <?= ucfirst(htmlspecialchars($booking['status'])) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php if ($booking['status'] === 'pending'): ?>
                                            <button onclick="DashboardManager.openEditModal(<?= htmlspecialchars(json_encode($booking)) ?>, <?= htmlspecialchars(json_encode($bookingEvents)) ?>)" 
                                                    style="background: var(--pink-main); color: white; padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem;">
                                                ✏️ Edit
                                            </button>
                                        <?php else: ?>
                                            <span style="color: var(--text-light); font-size: 0.9rem;">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Guests Card -->
            <div class="dashboard-card">
                <h3 class="card-title">👥 Guests</h3>
                <div class="card-content">
                    <p><strong><?= $guestCount ?></strong> guests added to your list</p>
                    <p style="margin-top: 15px; color: var(--text-light); font-size: 0.9rem;">
                        Manage your guest list from the main site dashboard.
                    </p>
                </div>
            </div>

            <!-- Events Card (Full Width) -->
            <?php if (!empty($subEvents)): ?>
            <div class="dashboard-card full-width">
                <h3 class="card-title">📅 Wedding Events</h3>
                <div class="card-content">
                    <ul class="events-list">
                        <?php foreach ($subEvents as $event): ?>
                        <li>
                            <div>
                                <span class="event-type"><?= ucfirst($event['event_type']) ?></span><br>
                                <span class="event-date"><?= date('M d, Y | H:i', strtotime($event['event_date'] . ' ' . ($event['time_start'] ?? '00:00:00'))) ?></span><br>
                                <small style="color: var(--text-light);"><?= htmlspecialchars($event['venue'] ?? '') ?></small>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Links Card (Full Width) -->
            <div class="dashboard-card full-width">
                <h3 class="card-title">🎯 Quick Links</h3>
                <div class="quick-links">
                    <a href="../index.html#page-dashboard" class="quick-link-btn">📊 Main Dashboard</a>
                    <a href="../index.html#page-catering" class="quick-link-btn">🍽️ Catering Menu</a>
                    <a href="../index.html#page-guests" class="quick-link-btn">👥 Manage Guests</a>
                    <a href="../index.html#page-gifts" class="quick-link-btn">🎁 Gifts & Registry</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Booking Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; overflow-y: auto; padding: 20px 0;">
        <div style="background: white; max-width: 600px; margin: 40px auto; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); padding: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 style="margin: 0; color: var(--pink-main); font-size: 1.5rem;">✏️ Edit Booking</h2>
                <button onclick="DashboardManager.closeEditModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">✕</button>
            </div>

            <form id="editForm" style="display: flex; flex-direction: column; gap: 15px;">
                <input type="hidden" id="editBookingId" name="booking_id">
                
                <div>
                    <label style="font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 5px;">Bride's Name</label>
                    <input type="text" id="editBrideName" name="bride_name" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Quicksand', sans-serif;">
                </div>

                <div>
                    <label style="font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 5px;">Groom's Name</label>
                    <input type="text" id="editGroomName" name="groom_name" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Quicksand', sans-serif;">
                </div>

                <div>
                    <label style="font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 5px;">Your Name (Client)</label>
                    <input type="text" id="editClientName" name="client_name" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Quicksand', sans-serif;">
                </div>

                <div>
                    <label style="font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 5px;">Email</label>
                    <input type="email" id="editClientEmail" name="client_email" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Quicksand', sans-serif;">
                </div>

                <div>
                    <label style="font-weight: 700; color: var(--text-dark); display: block; margin-bottom: 5px;">Phone</label>
                    <input type="tel" id="editClientPhone" name="client_phone" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Quicksand', sans-serif;">
                </div>

                <div id="editEventsContainer" style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 10px;"></div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="DashboardManager.closeEditModal()" style="flex: 1; padding: 12px; background: #ccc; color: #333; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">Cancel</button>
                    <button type="button" onclick="DashboardManager.submitEdit()" style="flex: 1; padding: 12px; background: var(--pink-main); color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 700;">💾 Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const DashboardManager = {
            currentBooking: null,
            currentEvents: null,

            openEditModal(booking, events) {
                this.currentBooking = booking;
                this.currentEvents = events || [];

                // Populate form fields
                document.getElementById('editBookingId').value = booking.id;
                document.getElementById('editBrideName').value = booking.bride_name || '';
                document.getElementById('editGroomName').value = booking.groom_name || '';
                document.getElementById('editClientName').value = booking.client_name || '';
                document.getElementById('editClientEmail').value = booking.client_email || '';
                document.getElementById('editClientPhone').value = booking.client_phone || '';

                // Populate event fields
                const eventsContainer = document.getElementById('editEventsContainer');
                eventsContainer.innerHTML = '';

                if (this.currentEvents && this.currentEvents.length > 0) {
                    const eventsLabel = document.createElement('h3');
                    eventsLabel.style.cssText = 'margin: 0 0 15px 0; color: var(--pink-main); font-size: 1.1rem;';
                    eventsLabel.textContent = '📅 Event Details';
                    eventsContainer.appendChild(eventsLabel);

                    this.currentEvents.forEach((event, index) => {
                        const eventDiv = document.createElement('div');
                        eventDiv.style.cssText = 'background: #f9f9f9; padding: 12px; border-radius: 6px; margin-bottom: 12px;';
                        
                        const eventType = document.createElement('p');
                        eventType.style.cssText = 'margin: 0 0 10px 0; font-weight: 700; color: var(--pink-main); text-transform: capitalize;';
                        eventType.textContent = event.event_type;
                        eventDiv.appendChild(eventType);

                        const eventDate = document.createElement('input');
                        eventDate.type = 'date';
                        eventDate.value = event.event_date;
                        eventDate.style.cssText = 'width: 100%; padding: 8px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-family: "Quicksand", sans-serif;';
                        eventDate.className = 'edit-event-date';
                        eventDate.dataset.eventId = event.id;
                        eventDiv.appendChild(eventDate);

                        const venueInput = document.createElement('input');
                        venueInput.type = 'text';
                        venueInput.placeholder = 'Venue';
                        venueInput.value = event.venue || '';
                        venueInput.style.cssText = 'width: 100%; padding: 8px; margin-bottom: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-family: "Quicksand", sans-serif;';
                        venueInput.className = 'edit-event-venue';
                        venueInput.dataset.eventId = event.id;
                        eventDiv.appendChild(venueInput);

                        const timeInput = document.createElement('input');
                        timeInput.type = 'time';
                        timeInput.value = event.event_time || '00:00';
                        timeInput.style.cssText = 'width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-family: "Quicksand", sans-serif;';
                        timeInput.className = 'edit-event-time';
                        timeInput.dataset.eventId = event.id;
                        eventDiv.appendChild(timeInput);

                        eventsContainer.appendChild(eventDiv);
                    });
                }

                document.getElementById('editModal').style.display = 'block';
                document.body.style.overflow = 'hidden';
            },

            closeEditModal() {
                document.getElementById('editModal').style.display = 'none';
                document.body.style.overflow = 'auto';
                this.currentBooking = null;
                this.currentEvents = null;
            },

            async submitEdit() {
                const bookingId = document.getElementById('editBookingId').value;
                const formData = new FormData(document.getElementById('editForm'));
                formData.append('action', 'update_booking');

                // Collect event updates
                const eventUpdates = [];
                if (this.currentEvents && this.currentEvents.length > 0) {
                    this.currentEvents.forEach((event) => {
                        const dateInput = document.querySelector(`.edit-event-date[data-event-id="${event.id}"]`);
                        const venueInput = document.querySelector(`.edit-event-venue[data-event-id="${event.id}"]`);
                        const timeInput = document.querySelector(`.edit-event-time[data-event-id="${event.id}"]`);

                        if (dateInput && venueInput) {
                            eventUpdates.push({
                                event_id: event.id,
                                event_date: dateInput.value,
                                event_time: timeInput.value,
                                venue: venueInput.value,
                                notes: event.notes || ''
                            });
                        }
                    });
                }

                formData.append('event_updates', JSON.stringify(eventUpdates));

                try {
                    const response = await fetch('../php/booking_planner.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ Booking updated successfully! Refreshing...');
                        window.location.reload();
                    } else {
                        if (data.conflict) {
                            alert('❌ Venue conflict detected! This venue is already booked on this date.');
                        } else {
                            alert('❌ Error: ' + data.message);
                        }
                    }
                } catch (error) {
                    alert('❌ Error updating booking: ' + error.message);
                }
            }
        };

        // Close modal when clicking outside
        document.getElementById('editModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'editModal') {
                DashboardManager.closeEditModal();
            }
        });
    </script>
</body>
</html>
