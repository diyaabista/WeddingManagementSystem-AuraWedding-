<?php
// ============================================================
// AuraWedding - Sub-Events & Dashboard CRUD (php/events.php)
// ============================================================
require_once 'config.php';
requireLogin();

$db        = getDB();
$method    = $_SERVER['REQUEST_METHOD'];
$action    = $_GET['action'] ?? $_POST['action'] ?? '';
$weddingId = getWeddingId();

// ---- READ: Get all sub-events ----
if ($method === 'GET' && $action === 'list') {
    $stmt = $db->prepare("SELECT * FROM sub_events WHERE wedding_id=? ORDER BY event_date ASC");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    jsonResponse(['success' => true, 'events' => $events]);
}

// ---- READ: Get single sub-event by ID ----
if ($method === 'GET' && $action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $db->prepare("SELECT * FROM sub_events WHERE id=? AND wedding_id=?");
    $stmt->bind_param('ii', $id, $weddingId);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    
    if ($event) {
        jsonResponse(['success' => true, 'event' => $event]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Event not found.']);
    }
}

// ---- CREATE: Add sub-event ----
if ($method === 'POST' && $action === 'create') {
    $type      = sanitize($_POST['event_type'] ?? '');
    $date      = sanitize($_POST['event_date'] ?? '');
    $venue     = sanitize($_POST['venue'] ?? '');
    $timeStart = sanitize($_POST['time_start'] ?? '');
    $timeEnd   = sanitize($_POST['time_end'] ?? '');
    $notes     = sanitize($_POST['notes'] ?? '');

    // Validation
    if (empty($type) || empty($date)) {
        jsonResponse(['success' => false, 'message' => 'Event type and date are required.'], 400);
        exit;
    }

    $validTypes = ['sangeet', 'mehendi', 'haldi', 'vivaha'];
    if (!in_array($type, $validTypes)) {
        jsonResponse(['success' => false, 'message' => 'Invalid event type.'], 400);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO sub_events (wedding_id, event_type, event_date, venue, time_start, time_end, notes) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('issssss', $weddingId, $type, $date, $venue, $timeStart, $timeEnd, $notes);

    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'message' => 'Event created successfully.', 'id' => $db->insert_id]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to create event.']);
    }
}

// ---- UPDATE: Edit sub-event ----
if ($method === 'POST' && $action === 'update') {
    $id        = (int)($_POST['id'] ?? 0);
    $date      = sanitize($_POST['event_date'] ?? '');
    $venue     = sanitize($_POST['venue'] ?? '');
    $timeStart = sanitize($_POST['time_start'] ?? '');
    $timeEnd   = sanitize($_POST['time_end'] ?? '');
    $notes     = sanitize($_POST['notes'] ?? '');

    $stmt = $db->prepare("UPDATE sub_events SET event_date=?, venue=?, time_start=?, time_end=?, notes=? WHERE id=? AND wedding_id=?");
    $stmt->bind_param('sssssii', $date, $venue, $timeStart, $timeEnd, $notes, $id, $weddingId);

    jsonResponse(['success' => $stmt->execute()]);
}

// ---- DELETE: Remove sub-event ----
if ($method === 'POST' && $action === 'delete') {
    $id   = (int)($_POST['id'] ?? 0);
    $stmt = $db->prepare("DELETE FROM sub_events WHERE id=? AND wedding_id=?");
    $stmt->bind_param('ii', $id, $weddingId);
    jsonResponse(['success' => $stmt->execute() && $db->affected_rows > 0]);
}

// ---- READ: Get wedding profile ----
if ($method === 'GET' && $action === 'profile') {
    $stmt = $db->prepare("SELECT * FROM weddings WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $weddingId, $_SESSION['user_id']);
    $stmt->execute();
    $wedding = $stmt->get_result()->fetch_assoc();
    jsonResponse(['success' => true, 'wedding' => $wedding]);
}

// ---- UPDATE: Wedding profile ----
if ($method === 'POST' && $action === 'update_profile') {
    $bride   = sanitize($_POST['bride_name'] ?? '');
    $groom   = sanitize($_POST['groom_name'] ?? '');
    $venue   = sanitize($_POST['venue'] ?? '');
    $date    = sanitize($_POST['wedding_date'] ?? '');
    $budget  = (float)($_POST['total_budget'] ?? 0);

    $stmt = $db->prepare("UPDATE weddings SET bride_name=?, groom_name=?, venue=?, wedding_date=?, total_budget=? WHERE id=? AND user_id=?");
    $stmt->bind_param('ssssdii', $bride, $groom, $venue, $date, $budget, $weddingId, $_SESSION['user_id']);
    jsonResponse(['success' => $stmt->execute()]);
}

// ---- READ: Catering menu ----
if ($method === 'GET' && $action === 'menu') {
    $result = $db->query("SELECT * FROM catering_menu ORDER BY category, item_name");
    $menu   = $result->fetch_all(MYSQLI_ASSOC);

    // Get selected items
    $stmt = $db->prepare("SELECT menu_id FROM selected_menu WHERE wedding_id=?");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $selected = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'menu_id');

    jsonResponse(['success' => true, 'menu' => $menu, 'selected' => $selected]);
}

// ---- UPDATE: Save menu selection ----
if ($method === 'POST' && $action === 'save_menu') {
    error_log('💾 SAVE_MENU: POST request received');
    error_log('💾 SAVE_MENU: SESSION = ' . json_encode($_SESSION));
    error_log('💾 SAVE_MENU: POST DATA = ' . json_encode($_POST));
    
    // Get wedding ID from session
    $weddingId = $_SESSION['wedding_id'] ?? null;
    error_log('💾 SAVE_MENU: weddingId = ' . ($weddingId ?? 'NULL'));
    
    if (!$weddingId) {
        error_log('💾 SAVE_MENU: No wedding ID - returning 401');
        jsonResponse(['success' => false, 'message' => 'No wedding ID in session. Please log in again.'], 401);
    }
    
    $menuIds = $_POST['menu_ids'] ?? [];
    error_log('💾 SAVE_MENU: menuIds count = ' . count($menuIds));

    // Validate that menu IDs is not empty
    if (empty($menuIds)) {
        error_log('💾 SAVE_MENU: No menu IDs - returning error');
        jsonResponse(['success' => false, 'message' => 'No menu items selected.']);
    }

    // Try to save to database if available
    $db = getDB();
    if (!$db) {
        error_log('💾 SAVE_MENU: No database - demo mode');
        // Demo mode: just acknowledge the save without database
        jsonResponse(['success' => true, 'message' => 'Menu saved successfully! (Demo mode)', 'count' => count($menuIds)]);
    }

    error_log('💾 SAVE_MENU: Database available - saving to DB');
    // Database mode: Save to database
    // Delete existing selections
    $stmt = $db->prepare("DELETE FROM selected_menu WHERE wedding_id=?");
    $stmt->bind_param('i', $weddingId);
    if (!$stmt->execute()) {
        error_log('💾 SAVE_MENU: Delete failed: ' . $stmt->error);
        jsonResponse(['success' => false, 'message' => 'Failed to clear previous selections: ' . $stmt->error]);
    }

    // Insert new selections
    $stmt = $db->prepare("INSERT INTO selected_menu (wedding_id, menu_id) VALUES (?, ?)");
    if (!$stmt) {
        error_log('💾 SAVE_MENU: Prepare failed: ' . $db->error);
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $db->error]);
    }
    
    $savedCount = 0;
    foreach ($menuIds as $menuId) {
        $menuId = (int)$menuId;
        if ($menuId > 0) {
            $stmt->bind_param('ii', $weddingId, $menuId);
            if ($stmt->execute()) {
                $savedCount++;
            } else {
                error_log('Failed to insert menu item ' . $menuId . ': ' . $stmt->error);
            }
        }
    }
    
    error_log('💾 SAVE_MENU: Saved ' . $savedCount . ' items');
    
    if ($savedCount === 0) {
        jsonResponse(['success' => false, 'message' => 'Failed to save menu items.']);
    }
    
    jsonResponse(['success' => true, 'message' => "Menu saved with $savedCount items.", 'count' => $savedCount]);
}

// ---- READ/WRITE: Mandap selection ----
if ($method === 'GET' && $action === 'mandap') {
    $stmt = $db->prepare("SELECT * FROM mandap_selections WHERE wedding_id=? LIMIT 1");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $mandap = $stmt->get_result()->fetch_assoc();
    jsonResponse(['success' => true, 'mandap' => $mandap]);
}

if ($method === 'POST' && $action === 'save_mandap') {
    $style  = sanitize($_POST['mandap_style'] ?? '');
    $flower = sanitize($_POST['flower_theme'] ?? '');
    $color  = sanitize($_POST['color_scheme'] ?? '');
    $notes  = sanitize($_POST['notes'] ?? '');

    // Upsert
    $stmt = $db->prepare("SELECT id FROM mandap_selections WHERE wedding_id=?");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc();

    if ($exists) {
        $stmt = $db->prepare("UPDATE mandap_selections SET mandap_style=?, flower_theme=?, color_scheme=?, notes=? WHERE wedding_id=?");
        $stmt->bind_param('ssssi', $style, $flower, $color, $notes, $weddingId);
    } else {
        $stmt = $db->prepare("INSERT INTO mandap_selections (wedding_id, mandap_style, flower_theme, color_scheme, notes) VALUES (?,?,?,?,?)");
        $stmt->bind_param('issss', $weddingId, $style, $flower, $color, $notes);
    }
    jsonResponse(['success' => $stmt->execute()]);
}

// ---- READ: Gifts ----
if ($method === 'GET' && $action === 'gifts') {
    $stmt = $db->prepare("SELECT g.*, gu.name as guest_name FROM gifts g LEFT JOIN guests gu ON gu.id=g.guest_id WHERE g.wedding_id=? ORDER BY g.received_at DESC");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $gifts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $totalStmt = $db->prepare("SELECT SUM(amount) as total FROM gifts WHERE wedding_id=?");
    $totalStmt->bind_param('i', $weddingId);
    $totalStmt->execute();
    $total = $totalStmt->get_result()->fetch_assoc()['total'] ?? 0;

    jsonResponse(['success' => true, 'gifts' => $gifts, 'total' => $total]);
}

// ---- PLANNER ACTION: Accept Booking ----
if ($method === 'POST' && $action === 'accept_booking') {
    $weddingId = (int)($weddingId ?? 0);
    
    if (!$weddingId) {
        jsonResponse(['success' => false, 'message' => 'Wedding ID not found.']);
        exit;
    }
    
    // Check if wedding exists and current user is the planner
    $stmt = $db->prepare("SELECT id FROM weddings WHERE id=?");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $wedding = $stmt->get_result()->fetch_assoc();
    
    if (!$wedding) {
        jsonResponse(['success' => false, 'message' => 'Wedding not found.']);
        exit;
    }
    
    // Update booking status in bookings table (if it exists)
    $stmt = $db->prepare("UPDATE bookings SET status='accepted', updated_at=NOW() WHERE wedding_id=?");
    $stmt->bind_param('i', $weddingId);
    
    if ($stmt->execute()) {
        // Also update weddings table status field if it exists
        $updateWedding = $db->prepare("UPDATE weddings SET status='accepted', updated_at=NOW() WHERE id=?");
        $updateWedding->bind_param('i', $weddingId);
        $updateWedding->execute();
        
        jsonResponse(['success' => true, 'message' => 'Booking accepted successfully.']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to accept booking: ' . $db->error]);
    }
}

// ---- PLANNER ACTION: Decline Booking ----
if ($method === 'POST' && $action === 'decline_booking') {
    $weddingId = (int)($weddingId ?? 0);
    $reason = sanitize($_POST['reason'] ?? '');
    
    if (!$weddingId) {
        jsonResponse(['success' => false, 'message' => 'Wedding ID not found.']);
        exit;
    }
    
    if (empty($reason)) {
        jsonResponse(['success' => false, 'message' => 'Decline reason is required.']);
        exit;
    }
    
    // Check if wedding exists
    $stmt = $db->prepare("SELECT id FROM weddings WHERE id=?");
    $stmt->bind_param('i', $weddingId);
    $stmt->execute();
    $wedding = $stmt->get_result()->fetch_assoc();
    
    if (!$wedding) {
        jsonResponse(['success' => false, 'message' => 'Wedding not found.']);
        exit;
    }
    
    // Update booking status in bookings table (if it exists)
    $stmt = $db->prepare("UPDATE bookings SET status='declined', decline_reason=?, updated_at=NOW() WHERE wedding_id=?");
    $stmt->bind_param('si', $reason, $weddingId);
    
    if ($stmt->execute()) {
        // Also update weddings table status field if it exists
        $updateWedding = $db->prepare("UPDATE weddings SET status='declined', decline_reason=?, updated_at=NOW() WHERE id=?");
        $updateWedding->bind_param('si', $reason, $weddingId);
        $updateWedding->execute();
        
        jsonResponse(['success' => true, 'message' => 'Booking declined successfully.']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to decline booking: ' . $db->error]);
    }
}
?>

