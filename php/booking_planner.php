<?php
// ============================================================
// AuraWedding - Multi-Step Booking Handler (php/booking_planner.php)
// ============================================================
require_once 'config.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db = getDB();

switch ($action) {

    // ---- LIST ALL BOOKINGS (for Planner Review) ----
    case 'list':
        if (!$db) {
            jsonResponse(['success' => false, 'message' => 'Database connection failed'], 500);
            exit;
        }

        $stmt = $db->prepare(
            "SELECT id, client_name, client_email, client_phone, bride_name, groom_name, status, created_at 
             FROM bookings 
             ORDER BY created_at DESC 
             LIMIT 50"
        );
        
        if (!$stmt) {
            jsonResponse(['success' => false, 'message' => 'Query prepare failed: ' . $db->error], 500);
            exit;
        }

        if (!$stmt->execute()) {
            jsonResponse(['success' => false, 'message' => 'Query execute failed: ' . $stmt->error], 500);
            exit;
        }

        $result = $stmt->get_result();
        $bookings = $result->fetch_all(MYSQLI_ASSOC);

        jsonResponse([
            'success' => true,
            'bookings' => $bookings,
            'count' => count($bookings)
        ]);
        break;

    // ---- STEP 1: Validate Event Selection ----
    case 'validate_events':
        $selectedEvents = $_POST['events'] ?? [];
        
        if (empty($selectedEvents)) {
            jsonResponse(['success' => false, 'message' => 'Please select at least one event.'], 400);
            exit;
        }

        $validEvents = ['haldi', 'mehendi', 'sangeet', 'ring_ceremony', 'vivah', 'reception'];
        foreach ($selectedEvents as $event) {
            if (!in_array($event, $validEvents)) {
                jsonResponse(['success' => false, 'message' => 'Invalid event selected.'], 400);
                exit;
            }
        }

        jsonResponse(['success' => true, 'message' => 'Events validated.']);
        break;

    // ---- STEP 2: Save Event Details ----
    case 'save_event_details':
        $bookingId = intval($_POST['booking_id'] ?? 0);
        $eventType = sanitize($_POST['event_type'] ?? '');
        $eventDate = sanitize($_POST['event_date'] ?? '');
        $eventTime = sanitize($_POST['event_time'] ?? '');
        $venue = sanitize($_POST['venue'] ?? '');
        $guestCount = intval($_POST['guest_count'] ?? 0);
        $roomCount = intval($_POST['room_count'] ?? 0);
        $notes = sanitize($_POST['notes'] ?? '');

        if (!$eventDate || !$venue) {
            jsonResponse(['success' => false, 'message' => 'Date and venue are required.'], 400);
            exit;
        }

        // Check for venue conflicts
        $conflictStmt = $db->prepare(
            "SELECT COUNT(*) as count FROM booking_events be
             JOIN bookings b ON be.booking_id = b.id
             WHERE be.venue = ? AND be.event_date = ? AND b.status IN ('pending', 'accepted')"
        );
        $conflictStmt->bind_param('ss', $venue, $eventDate);
        $conflictStmt->execute();
        $conflictResult = $conflictStmt->get_result()->fetch_assoc();

        if ($conflictResult['count'] > 0) {
            jsonResponse([
                'success' => false, 
                'message' => 'Venue conflict detected! This venue is already booked on this date.',
                'conflict' => true
            ], 409);
            exit;
        }

        // Save event details
        if ($bookingId > 0) {
            // Create new event for existing booking
            $stmt = $db->prepare(
                "INSERT INTO booking_events (booking_id, event_type, event_date, event_time, venue, guest_count, room_count, notes)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('isssii ss', $bookingId, $eventType, $eventDate, $eventTime, $venue, $guestCount, $roomCount, $notes);

            if ($stmt->execute()) {
                $eventId = $db->insert_id;
                jsonResponse([
                    'success' => true,
                    'message' => 'Event details saved.',
                    'event_id' => $eventId
                ]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Database error: ' . $db->error], 500);
            }
        } else {
            jsonResponse(['success' => false, 'message' => 'Booking ID is required.'], 400);
        }
        break;

    // ---- Step 2b: Save Guest List ----
    case 'save_guests':
        $eventId = intval($_POST['event_id'] ?? 0);
        $bookingId = intval($_POST['booking_id'] ?? 0);
        $guests = json_decode($_POST['guests'] ?? '[]', true);

        if (!$eventId || !is_array($guests)) {
            jsonResponse(['success' => false, 'message' => 'Invalid data provided.'], 400);
            exit;
        }

        // Delete existing guests for this event
        $deleteStmt = $db->prepare("DELETE FROM booking_guests WHERE event_id = ?");
        $deleteStmt->bind_param('i', $eventId);
        $deleteStmt->execute();

        // Insert new guests
        $insertStmt = $db->prepare(
            "INSERT INTO booking_guests (booking_id, event_id, guest_name, guest_phone, guest_email, dietary_preference, rsvp_status)
             VALUES (?, ?, ?, ?, ?, ?, 'pending')"
        );

        $successCount = 0;
        foreach ($guests as $guest) {
            $name = sanitize($guest['name'] ?? '');
            $phone = sanitize($guest['phone'] ?? '');
            $email = sanitize($guest['email'] ?? '');
            $diet = sanitize($guest['dietary'] ?? '');

            if (empty($name)) continue;

            $insertStmt->bind_param('iisss', $bookingId, $eventId, $name, $phone, $email, $diet);
            if ($insertStmt->execute()) $successCount++;
        }

        // Update event guest count
        $updateStmt = $db->prepare(
            "UPDATE booking_events SET guest_count = ? WHERE id = ?"
        );
        $count = count($guests);
        $updateStmt->bind_param('ii', $count, $eventId);
        $updateStmt->execute();

        jsonResponse([
            'success' => true,
            'message' => "Added $successCount guests.",
            'guest_count' => $count
        ]);
        break;

    // ---- Step 2c: Save Room Bookings ----
    case 'save_rooms':
        $eventId = intval($_POST['event_id'] ?? 0);
        $rooms = json_decode($_POST['rooms'] ?? '[]', true);

        if (!$eventId || !is_array($rooms)) {
            jsonResponse(['success' => false, 'message' => 'Invalid data provided.'], 400);
            exit;
        }

        // Delete existing rooms
        $deleteStmt = $db->prepare("DELETE FROM booking_rooms WHERE booking_event_id = ?");
        $deleteStmt->bind_param('i', $eventId);
        $deleteStmt->execute();

        // Insert new rooms
        $insertStmt = $db->prepare(
            "INSERT INTO booking_rooms (booking_event_id, hotel_name, room_count, room_type, check_in, check_out, price_per_room, notes)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $successCount = 0;
        foreach ($rooms as $room) {
            $hotelName = sanitize($room['hotel_name'] ?? '');
            $roomCount = intval($room['room_count'] ?? 1);
            $roomType = sanitize($room['room_type'] ?? '');
            $checkIn = sanitize($room['check_in'] ?? '');
            $checkOut = sanitize($room['check_out'] ?? '');
            $price = floatval($room['price'] ?? 0);
            $notes = sanitize($room['notes'] ?? '');

            if (empty($hotelName) || empty($checkIn) || empty($checkOut)) continue;

            $insertStmt->bind_param('isssssds', $eventId, $hotelName, $roomCount, $roomType, $checkIn, $checkOut, $price, $notes);
            if ($insertStmt->execute()) $successCount++;
        }

        // Update event room count
        $totalRooms = array_sum(array_map(function($r) { return intval($r['room_count'] ?? 1); }, $rooms));
        $updateStmt = $db->prepare(
            "UPDATE booking_events SET room_count = ? WHERE id = ?"
        );
        $updateStmt->bind_param('ii', $totalRooms, $eventId);
        $updateStmt->execute();

        jsonResponse([
            'success' => true,
            'message' => "Saved $successCount hotel bookings.",
            'room_count' => $totalRooms
        ]);
        break;

    // ---- STEP 3: Get Booking Summary ----
    case 'get_summary':
        $bookingId = intval($_GET['id'] ?? 0);

        if ($bookingId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid booking ID.'], 400);
            exit;
        }

        // Get booking details
        $bookingStmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
        $bookingStmt->bind_param('i', $bookingId);
        $bookingStmt->execute();
        $booking = $bookingStmt->get_result()->fetch_assoc();

        if (!$booking) {
            jsonResponse(['success' => false, 'message' => 'Booking not found.'], 404);
            exit;
        }

        // Get events
        $eventsStmt = $db->prepare(
            "SELECT be.*, COUNT(DISTINCT bg.id) as guest_count, COUNT(DISTINCT br.id) as room_count
             FROM booking_events be
             LEFT JOIN booking_guests bg ON be.id = bg.event_id
             LEFT JOIN booking_rooms br ON be.id = br.booking_event_id
             WHERE be.booking_id = ?
             GROUP BY be.id"
        );
        $eventsStmt->bind_param('i', $bookingId);
        $eventsStmt->execute();
        $events = $eventsStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get guests for each event
        $guests = [];
        foreach ($events as $event) {
            $guestStmt = $db->prepare(
                "SELECT * FROM booking_guests WHERE event_id = ?"
            );
            $guestStmt->bind_param('i', $event['id']);
            $guestStmt->execute();
            $guests[$event['id']] = $guestStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        // Get rooms for each event
        $rooms = [];
        foreach ($events as $event) {
            $roomStmt = $db->prepare(
                "SELECT * FROM booking_rooms WHERE booking_event_id = ?"
            );
            $roomStmt->bind_param('i', $event['id']);
            $roomStmt->execute();
            $rooms[$event['id']] = $roomStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        jsonResponse([
            'success' => true,
            'booking' => $booking,
            'events' => $events,
            'guests' => $guests,
            'rooms' => $rooms
        ]);
        break;

    // ---- STEP 4: Submit Booking ----
    case 'submit_booking':
        $bookingId = intval($_POST['booking_id'] ?? 0);
        $clientName = sanitize($_POST['client_name'] ?? '');
        $clientEmail = sanitize($_POST['client_email'] ?? '');
        $clientPhone = sanitize($_POST['client_phone'] ?? '');
        $brideName = sanitize($_POST['bride_name'] ?? '');
        $groomName = sanitize($_POST['groom_name'] ?? '');
        $termsAccepted = isset($_POST['terms_accepted']) ? 1 : 0;

        if (!$clientName || !$clientEmail) {
            jsonResponse(['success' => false, 'message' => 'Name and email are required.'], 400);
            exit;
        }

        if (!$termsAccepted) {
            jsonResponse(['success' => false, 'message' => 'Please accept terms and conditions.'], 400);
            exit;
        }

        // Update booking with final details
        $updateStmt = $db->prepare(
            "UPDATE bookings 
             SET client_name = ?, client_email = ?, client_phone = ?, bride_name = ?, groom_name = ?, 
                 terms_accepted = ?, status = 'pending', updated_at = NOW()
             WHERE id = ?"
        );
        $updateStmt->bind_param('sssssi', $clientName, $clientEmail, $clientPhone, $brideName, $groomName, $termsAccepted, $bookingId);

        if ($updateStmt->execute()) {
            jsonResponse([
                'success' => true,
                'message' => 'Booking submitted successfully! Admin will review shortly.',
                'booking_id' => $bookingId
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Submission failed.'], 500);
        }
        break;

    // ---- CANCEL BOOKING (User) ----
    case 'cancel_booking':
        $bookingId = intval($_POST['booking_id'] ?? 0);

        if ($bookingId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid booking ID.'], 400);
            exit;
        }

        // Check if booking is pending
        $checkStmt = $db->prepare("SELECT status FROM bookings WHERE id = ?");
        $checkStmt->bind_param('i', $bookingId);
        $checkStmt->execute();
        $booking = $checkStmt->get_result()->fetch_assoc();

        if (!$booking) {
            jsonResponse(['success' => false, 'message' => 'Booking not found.'], 404);
            exit;
        }

        if ($booking['status'] !== 'pending') {
            jsonResponse([
                'success' => false,
                'message' => "Cannot cancel {$booking['status']} bookings. Only pending bookings can be cancelled."
            ], 400);
            exit;
        }

        // Cancel the booking
        $cancelStmt = $db->prepare(
            "UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = ?"
        );
        $cancelStmt->bind_param('i', $bookingId);

        if ($cancelStmt->execute()) {
            jsonResponse(['success' => true, 'message' => 'Booking cancelled successfully.']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Cancellation failed.'], 500);
        }
        break;

    // ---- CREATE NEW BOOKING ----
    case 'create_booking':
        $userId = $_SESSION['user_id'] ?? null;

        $createStmt = $db->prepare(
            "INSERT INTO bookings (user_id, status, created_at, updated_at) VALUES (?, 'pending', NOW(), NOW())"
        );
        $createStmt->bind_param('i', $userId);

        if ($createStmt->execute()) {
            jsonResponse([
                'success' => true,
                'message' => 'Booking created. Start adding events.',
                'booking_id' => $db->insert_id
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create booking.'], 500);
        }
        break;

    // ---- UPDATE BOOKING (Edit Pending Bookings) ----
    case 'update_booking':
        $bookingId = intval($_POST['booking_id'] ?? 0);
        $brideName = sanitize($_POST['bride_name'] ?? '');
        $groomName = sanitize($_POST['groom_name'] ?? '');
        $clientName = sanitize($_POST['client_name'] ?? '');
        $clientEmail = sanitize($_POST['client_email'] ?? '');
        $clientPhone = sanitize($_POST['client_phone'] ?? '');

        if ($bookingId <= 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid booking ID.'], 400);
            exit;
        }

        // Check if booking exists and is pending
        $checkStmt = $db->prepare("SELECT status FROM bookings WHERE id = ?");
        $checkStmt->bind_param('i', $bookingId);
        $checkStmt->execute();
        $booking = $checkStmt->get_result()->fetch_assoc();

        if (!$booking) {
            jsonResponse(['success' => false, 'message' => 'Booking not found.'], 404);
            exit;
        }

        if ($booking['status'] !== 'pending') {
            jsonResponse([
                'success' => false,
                'message' => "Cannot edit {$booking['status']} bookings. Only pending bookings can be edited."
            ], 400);
            exit;
        }

        // Update booking details
        $updateStmt = $db->prepare(
            "UPDATE bookings 
             SET bride_name = ?, groom_name = ?, client_name = ?, 
                 client_email = ?, client_phone = ?, updated_at = NOW()
             WHERE id = ?"
        );
        $updateStmt->bind_param('sssssi', $brideName, $groomName, $clientName, $clientEmail, $clientPhone, $bookingId);

        if ($updateStmt->execute()) {
            // Also handle event updates if provided
            $eventUpdates = json_decode($_POST['event_updates'] ?? '[]', true);
            
            if (is_array($eventUpdates) && !empty($eventUpdates)) {
                foreach ($eventUpdates as $eventUpdate) {
                    $eventId = intval($eventUpdate['event_id'] ?? 0);
                    $eventDate = sanitize($eventUpdate['event_date'] ?? '');
                    $eventTime = sanitize($eventUpdate['event_time'] ?? '');
                    $venue = sanitize($eventUpdate['venue'] ?? '');
                    $notes = sanitize($eventUpdate['notes'] ?? '');

                    if ($eventId > 0 && $eventDate && $venue) {
                        // Check for venue conflicts (excluding current booking)
                        $conflictStmt = $db->prepare(
                            "SELECT COUNT(*) as count FROM booking_events be
                             JOIN bookings b ON be.booking_id = b.id
                             WHERE be.venue = ? AND be.event_date = ? 
                             AND b.status IN ('pending', 'accepted')
                             AND be.id != ?"
                        );
                        $conflictStmt->bind_param('ssi', $venue, $eventDate, $eventId);
                        $conflictStmt->execute();
                        $conflictResult = $conflictStmt->get_result()->fetch_assoc();

                        if ($conflictResult['count'] > 0) {
                            jsonResponse([
                                'success' => false, 
                                'message' => 'Venue conflict detected! This venue is already booked on this date.',
                                'conflict' => true
                            ], 409);
                            exit;
                        }

                        // Update event
                        $eventUpdateStmt = $db->prepare(
                            "UPDATE booking_events 
                             SET event_date = ?, event_time = ?, venue = ?, notes = ?, updated_at = NOW()
                             WHERE id = ? AND booking_id = ?"
                        );
                        $eventUpdateStmt->bind_param('ssissi', $eventDate, $eventTime, $venue, $notes, $eventId, $bookingId);
                        $eventUpdateStmt->execute();
                    }
                }
            }

            jsonResponse([
                'success' => true,
                'message' => 'Booking updated successfully!',
                'booking_id' => $bookingId
            ]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Update failed: ' . $db->error], 500);
        }
        break;

    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);
        break;
}
?>
