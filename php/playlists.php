<?php
// ============================================================
// AuraWedding - Playlist Management API (php/playlists.php)
// ============================================================
require_once 'config.php';
requireLogin();

$db        = getDB();
$method    = $_SERVER['REQUEST_METHOD'];
$action    = $_GET['action'] ?? $_POST['action'] ?? '';
$weddingId = getWeddingId();

// ---- CREATE: Add new playlist ----
if ($method === 'POST' && $action === 'create') {
    $playlistName = sanitize($_POST['playlist_name'] ?? '');
    $description  = sanitize($_POST['description'] ?? '');
    $eventId      = (int)($_POST['event_id'] ?? 0) ?: null;
    $eventType    = sanitize($_POST['event_type'] ?? 'sangeet');
    $mood         = sanitize($_POST['mood'] ?? '');

    if (empty($playlistName)) {
        jsonResponse(['success' => false, 'message' => 'Playlist name is required.'], 400);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO playlists (wedding_id, event_id, playlist_name, description, event_type, mood) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('iissss', $weddingId, $eventId, $playlistName, $description, $eventType, $mood);

    if ($stmt->execute()) {
        $playlistId = $db->insert_id;
        jsonResponse(['success' => true, 'message' => 'Playlist created successfully.', 'playlist_id' => $playlistId]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to create playlist.']);
    }
}

// ---- READ: Get all playlists ----
if ($method === 'GET' && $action === 'list') {
    $eventType = sanitize($_GET['event_type'] ?? '');
    
    if ($eventType) {
        $stmt = $db->prepare("SELECT * FROM playlists WHERE wedding_id=? AND event_type=? ORDER BY created_at DESC");
        $stmt->bind_param('is', $weddingId, $eventType);
    } else {
        $stmt = $db->prepare("SELECT * FROM playlists WHERE wedding_id=? ORDER BY created_at DESC");
        $stmt->bind_param('i', $weddingId);
    }
    
    $stmt->execute();
    $playlists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get song count for each playlist
    foreach ($playlists as &$playlist) {
        $songStmt = $db->prepare("SELECT COUNT(*) as song_count FROM playlist_songs WHERE playlist_id=?");
        $songStmt->bind_param('i', $playlist['id']);
        $songStmt->execute();
        $songResult = $songStmt->get_result()->fetch_assoc();
        $playlist['song_count'] = $songResult['song_count'];
    }
    
    jsonResponse(['success' => true, 'playlists' => $playlists]);
}

// ---- READ: Get playlist details with songs ----
if ($method === 'GET' && $action === 'get') {
    $playlistId = (int)($_GET['id'] ?? 0);
    
    if ($playlistId == 0) {
        jsonResponse(['success' => false, 'message' => 'Playlist ID is required.'], 400);
        exit;
    }
    
    $stmt = $db->prepare("SELECT * FROM playlists WHERE id=? AND wedding_id=?");
    $stmt->bind_param('ii', $playlistId, $weddingId);
    $stmt->execute();
    $playlist = $stmt->get_result()->fetch_assoc();
    
    if (!$playlist) {
        jsonResponse(['success' => false, 'message' => 'Playlist not found.'], 404);
        exit;
    }
    
    // Get all songs in this playlist
    $songStmt = $db->prepare("SELECT * FROM playlist_songs WHERE playlist_id=? ORDER BY song_order ASC");
    $songStmt->bind_param('i', $playlistId);
    $songStmt->execute();
    $songs = $songStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $playlist['songs'] = $songs;
    jsonResponse(['success' => true, 'playlist' => $playlist]);
}

// ---- UPDATE: Edit playlist ----
if ($method === 'POST' && $action === 'update') {
    $playlistId   = (int)($_POST['id'] ?? 0);
    $playlistName = sanitize($_POST['playlist_name'] ?? '');
    $description  = sanitize($_POST['description'] ?? '');
    $mood         = sanitize($_POST['mood'] ?? '');

    if ($playlistId == 0) {
        jsonResponse(['success' => false, 'message' => 'Playlist ID is required.'], 400);
        exit;
    }

    $stmt = $db->prepare("UPDATE playlists SET playlist_name=?, description=?, mood=? WHERE id=? AND wedding_id=?");
    $stmt->bind_param('sssii', $playlistName, $description, $mood, $playlistId, $weddingId);

    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'message' => 'Playlist updated successfully.']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to update playlist.']);
    }
}

// ---- DELETE: Remove playlist ----
if ($method === 'POST' && $action === 'delete') {
    $playlistId = (int)($_POST['id'] ?? 0);
    
    if ($playlistId == 0) {
        jsonResponse(['success' => false, 'message' => 'Playlist ID is required.'], 400);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM playlists WHERE id=? AND wedding_id=?");
    $stmt->bind_param('ii', $playlistId, $weddingId);
    
    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'message' => 'Playlist deleted successfully.']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to delete playlist.']);
    }
}

// ---- CREATE: Add song to playlist ----
if ($method === 'POST' && $action === 'add_song') {
    $playlistId  = (int)($_POST['playlist_id'] ?? 0);
    $songName    = sanitize($_POST['song_name'] ?? '');
    $artistName  = sanitize($_POST['artist_name'] ?? '');
    $genre       = sanitize($_POST['genre'] ?? '');
    $duration    = (int)($_POST['duration_seconds'] ?? 0);
    $notes       = sanitize($_POST['notes'] ?? '');

    if ($playlistId == 0 || empty($songName)) {
        jsonResponse(['success' => false, 'message' => 'Playlist ID and song name are required.'], 400);
        exit;
    }

    // Verify playlist belongs to this wedding
    $verifyStmt = $db->prepare("SELECT id FROM playlists WHERE id=? AND wedding_id=?");
    $verifyStmt->bind_param('ii', $playlistId, $weddingId);
    $verifyStmt->execute();
    
    if ($verifyStmt->get_result()->num_rows == 0) {
        jsonResponse(['success' => false, 'message' => 'Playlist not found.'], 404);
        exit;
    }

    // Get next song order
    $orderStmt = $db->prepare("SELECT MAX(song_order) as max_order FROM playlist_songs WHERE playlist_id=?");
    $orderStmt->bind_param('i', $playlistId);
    $orderStmt->execute();
    $orderResult = $orderStmt->get_result()->fetch_assoc();
    $nextOrder = ($orderResult['max_order'] ?? 0) + 1;

    $stmt = $db->prepare("INSERT INTO playlist_songs (playlist_id, song_name, artist_name, genre, duration_seconds, song_order, notes) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('issssss', $playlistId, $songName, $artistName, $genre, $duration, $nextOrder, $notes);

    if ($stmt->execute()) {
        $songId = $db->insert_id;
        jsonResponse(['success' => true, 'message' => 'Song added to playlist.', 'song_id' => $songId]);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to add song.']);
    }
}

// ---- DELETE: Remove song from playlist ----
if ($method === 'POST' && $action === 'remove_song') {
    $songId = (int)($_POST['song_id'] ?? 0);

    if ($songId == 0) {
        jsonResponse(['success' => false, 'message' => 'Song ID is required.'], 400);
        exit;
    }

    // Verify song belongs to a playlist in this wedding
    $verifyStmt = $db->prepare("SELECT ps.id FROM playlist_songs ps JOIN playlists p ON ps.playlist_id = p.id WHERE ps.id=? AND p.wedding_id=?");
    $verifyStmt->bind_param('ii', $songId, $weddingId);
    $verifyStmt->execute();
    
    if ($verifyStmt->get_result()->num_rows == 0) {
        jsonResponse(['success' => false, 'message' => 'Song not found.'], 404);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM playlist_songs WHERE id=?");
    $stmt->bind_param('i', $songId);
    
    if ($stmt->execute()) {
        jsonResponse(['success' => true, 'message' => 'Song removed from playlist.']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to remove song.']);
    }
}

// ---- UPDATE: Reorder songs in playlist ----
if ($method === 'POST' && $action === 'reorder_songs') {
    $playlistId = (int)($_POST['playlist_id'] ?? 0);
    $songOrder = json_decode($_POST['song_order'] ?? '[]', true);

    if ($playlistId == 0 || empty($songOrder)) {
        jsonResponse(['success' => false, 'message' => 'Playlist ID and song order are required.'], 400);
        exit;
    }

    $success = true;
    foreach ($songOrder as $order => $songId) {
        $position = $order + 1;
        $stmt = $db->prepare("UPDATE playlist_songs SET song_order=? WHERE id=? AND playlist_id=?");
        $stmt->bind_param('iii', $position, $songId, $playlistId);
        
        if (!$stmt->execute()) {
            $success = false;
            break;
        }
    }

    if ($success) {
        jsonResponse(['success' => true, 'message' => 'Songs reordered successfully.']);
    } else {
        jsonResponse(['success' => false, 'message' => 'Failed to reorder songs.']);
    }
}

// Default response
jsonResponse(['success' => false, 'message' => 'Invalid action.'], 400);
