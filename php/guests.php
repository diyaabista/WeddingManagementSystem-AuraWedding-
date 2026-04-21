<?php
// ============================================================
// AuraWedding - Guest Management CRUD (php/guests.php)
// ============================================================
require_once 'config.php';
requireLogin();

$db        = getDB();
$method    = $_SERVER['REQUEST_METHOD'];
$action    = $_GET['action'] ?? $_POST['action'] ?? '';
$weddingId = getWeddingId();

// Check database connection
if (!$db) {
    jsonResponse(['success' => false, 'message' => 'Database connection failed.']);
}

// ---- READ: Get all guests ----
if ($method === 'GET' && $action === 'list') {
    $stmt = $db->prepare("
        SELECT g.*, hb.hotel_name, hb.room_number, hb.check_in, hb.check_out
        FROM guests g
        LEFT JOIN hotel_bookings hb ON hb.guest_id = g.id
        WHERE g.wedding_id = ?
        ORDER BY g.name ASC
    ");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $result = $stmt->get_result();
    $guests = $result->fetch_all(MYSQLI_ASSOC);
    jsonResponse(['success' => true, 'guests' => $guests]);
}

// ---- CREATE: Add a guest ----
if ($method === 'POST' && $action === 'create') {
    $name     = sanitize($_POST['name'] ?? '');
    $phone    = sanitize($_POST['phone'] ?? '');
    $email    = sanitize($_POST['email'] ?? '');
    $side     = sanitize($_POST['side'] ?? 'both');
    $relation = sanitize($_POST['relation'] ?? '');

    if (empty($name)) jsonResponse(['success' => false, 'message' => 'Name is required.']);

    $stmt = $db->prepare("INSERT INTO guests (wedding_id, name, phone, email, side, relation) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('isssss', $weddingId, $name, $phone, $email, $side, $relation);

    if ($stmt->execute()) {
        $guestId = $db->insert_id;

        // Optional hotel booking
        $hotel  = sanitize($_POST['hotel_name'] ?? '');
        $room   = sanitize($_POST['room_number'] ?? '');
        $checkIn  = sanitize($_POST['check_in'] ?? '');
        $checkOut = sanitize($_POST['check_out'] ?? '');

        if (!empty($hotel) && !empty($room)) {
            $hStmt = $db->prepare("INSERT INTO hotel_bookings (guest_id, hotel_name, room_number, check_in, check_out) VALUES (?, ?, ?, ?, ?)");
            $hStmt->bind_param('issss', $guestId, $hotel, $room, $checkIn, $checkOut);
            $hStmt->execute();
        }

        jsonResponse(['success' => true, 'id' => $guestId]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to add guest.']);
    }
}

// ---- UPDATE: Edit a guest ----
if ($method === 'POST' && $action === 'update') {
    $id          = (int)($_POST['id'] ?? 0);
    $name        = sanitize($_POST['name'] ?? '');
    $phone       = sanitize($_POST['phone'] ?? '');
    $email       = sanitize($_POST['email'] ?? '');
    $rsvpStatus  = sanitize($_POST['rsvp_status'] ?? 'pending');
    $side        = sanitize($_POST['side'] ?? 'both');
    $relation    = sanitize($_POST['relation'] ?? '');

    $stmt = $db->prepare("UPDATE guests SET name=?, phone=?, email=?, rsvp_status=?, side=?, relation=? WHERE id=? AND wedding_id=?");
    $stmt->bind_param('ssssssii', $name, $phone, $email, $rsvpStatus, $side, $relation, $id, $weddingId);

    if ($stmt->execute()) {
        // Update hotel booking
        $hotel    = sanitize($_POST['hotel_name'] ?? '');
        $room     = sanitize($_POST['room_number'] ?? '');
        $checkIn  = sanitize($_POST['check_in'] ?? '');
        $checkOut = sanitize($_POST['check_out'] ?? '');

        // Check if booking exists
        $chk = $db->prepare("SELECT id FROM hotel_bookings WHERE guest_id=?");
        $chk->bind_param('i', $id);
        $chk->execute();
        $exists = $chk->get_result()->fetch_assoc();

        if (!empty($hotel)) {
            if ($exists) {
                $hStmt = $db->prepare("UPDATE hotel_bookings SET hotel_name=?, room_number=?, check_in=?, check_out=? WHERE guest_id=?");
                $hStmt->bind_param('ssssi', $hotel, $room, $checkIn, $checkOut, $id);
            } else {
                $hStmt = $db->prepare("INSERT INTO hotel_bookings (guest_id, hotel_name, room_number, check_in, check_out) VALUES (?, ?, ?, ?, ?)");
                $hStmt->bind_param('issss', $id, $hotel, $room, $checkIn, $checkOut);
            }
            $hStmt->execute();
        }

        jsonResponse(['success' => true]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Update failed.']);
    }
}

// ---- DELETE: Remove a guest ----
if ($method === 'POST' && $action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    
    if (!$id) {
        jsonResponse(['success' => false, 'message' => 'Invalid guest ID.']);
    }
    
    if (!$weddingId) {
        jsonResponse(['success' => false, 'message' => 'Wedding ID not found in session.']);
    }
    
    $stmt = $db->prepare("DELETE FROM guests WHERE id=? AND wedding_id=?");
    $stmt->bind_param('ii', $id, $weddingId);

    if ($stmt->execute()) {
        if ($db->affected_rows > 0) {
            jsonResponse(['success' => true, 'message' => 'Guest deleted successfully.']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Guest not found or unauthorized to delete.']);
        }
    } else {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
}

// ---- READ: Get summary stats ----
if ($method === 'GET' && $action === 'stats') {
    $stmt = $db->prepare("SELECT rsvp_status, COUNT(*) as count FROM guests WHERE wedding_id=? GROUP BY rsvp_status");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $rows  = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stats = ['total' => 0, 'confirmed' => 0, 'pending' => 0, 'declined' => 0];
    foreach ($rows as $r) {
        $stats[$r['rsvp_status']] = (int)$r['count'];
        $stats['total'] += (int)$r['count'];
    }
    jsonResponse(['success' => true, 'stats' => $stats]);
}
?>
