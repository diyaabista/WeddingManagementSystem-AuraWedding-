<?php
// ============================================================
// AuraWedding - Booking Handler (php/book.php)
// ============================================================
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['success' => false, 'message' => 'Invalid request method.']);
}

$client_name = sanitize($_POST['client_name'] ?? '');
$client_email = sanitize($_POST['client_email'] ?? '');
$client_phone = sanitize($_POST['client_phone'] ?? '');
$selected_items = $_POST['selected_items'] ?? '[]'; // Do not sanitize JSON broadly
$terms_accepted = isset($_POST['terms_accepted']) && $_POST['terms_accepted'] === 'on' ? 1 : 0;

if (empty($client_name) || empty($client_email) || empty($client_phone)) {
    jsonResponse(['success' => false, 'message' => 'All fields are required.']);
}

if (!$terms_accepted) {
    jsonResponse(['success' => false, 'message' => 'You must accept the terms and conditions.']);
}

$db = getDB();

// If database connection fails, use demo mode
if (!$db) {
    jsonResponse(['success' => true, 'message' => 'Booking submitted successfully! (Demo Mode)']);
}

// Get user's wedding_id from session if available
$wedding_id = $_SESSION['wedding_id'] ?? null;

$stmt = $db->prepare("INSERT INTO bookings (client_name, client_email, client_phone, selected_items, terms_accepted, wedding_id) VALUES (?, ?, ?, ?, ?, ?)");

if ($stmt) {
    $stmt->bind_param('ssssii', $client_name, $client_email, $client_phone, $selected_items, $terms_accepted, $wedding_id);

    if ($stmt->execute()) {
        $booking_id = $db->insert_id;
        jsonResponse([
            'success' => true, 
            'message' => 'Booking submitted successfully!',
            'booking_id' => $booking_id,
            'wedding_id' => $wedding_id
        ]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
} else {
    // Fallback if the database schema hasn't been updated with wedding_id column yet
    $stmt = $db->prepare("INSERT INTO bookings (client_name, client_email, client_phone, selected_items, terms_accepted) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param('ssssi', $client_name, $client_email, $client_phone, $selected_items, $terms_accepted);
        if ($stmt->execute()) {
            jsonResponse(['success' => true, 'message' => 'Booking submitted successfully!']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
        }
    } else {
        jsonResponse(['success' => false, 'message' => 'Database error: ' . $db->error]);
    }
}
?>
