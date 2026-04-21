<?php
// ============================================================
// AuraWedding - Initialize Playlists (php/init_playlists.php)
// ============================================================
header('Content-Type: application/json');

require_once 'config.php';

$db = getDB();

// Check if playlists already exist
$result = $db->query("SELECT COUNT(*) as count FROM playlist_songs WHERE 1");
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    // Playlists already loaded
    jsonResponse(['success' => true, 'message' => 'Playlists already loaded', 'songs_count' => $row['count']]);
    exit;
}

// Create playlists
$stmt = $db->prepare("INSERT INTO playlists (wedding_id, playlist_name, description, event_type, mood) VALUES (?, ?, ?, ?, ?)");
$wedding_id = 1;
$name1 = 'Classical Sangeet Collection';
$desc1 = 'Traditional classical songs perfect for sangeet ceremony';
$type = 'sangeet';
$mood1 = 'Classical';

$stmt->bind_param('issss', $wedding_id, $name1, $desc1, $type, $mood1);
$stmt->execute();
$pl1_id = $db->insert_id;

$name2 = 'Bollywood Sangeet Hits';
$desc2 = 'Modern Bollywood romantic songs for sangeet celebration';
$mood2 = 'Romantic';
$stmt->bind_param('issss', $wedding_id, $name2, $desc2, $type, $mood2);
$stmt->execute();
$pl2_id = $db->insert_id;

// Classical songs
$classical_songs = [
    ['Bole Chudiyan', 'Shreya Ghoshal', 'Classical Fusion', 244],
    ['Dola Re Dola', 'Anushka Manchanda', 'Classical Fusion', 220],
    ['Morni Banke', 'Shreya Ghoshal', 'Classical Fusion', 210],
    ['Jiya Jale', 'Lata Mangeshkar', 'Classical Romance', 264],
    ['Chandni O Chandni', 'Lata Mangeshkar', 'Classical Romance', 238],
    ['Saathiya', 'Mera Naam Chin Chin Chu', 'Classical Fusion', 198],
    ['Aati Ho Ati Ho', 'Shreya Ghoshal', 'Devotional Classical', 216],
    ['Raina Beeti Jaye', 'Shreya Ghoshal', 'Classical Romance', 252],
    ['Teri Aankhon Ke Samne', 'Mohammed Rafi & Lata Mangeshkar', 'Classical Duet', 226],
    ['Hazaron Khwahishen Aisi', 'Jagjit Singh', 'Classical Ghazal', 244],
    ['Shringaar Karo Dulhan Meri', 'Asha Parekh', 'Classical Sangeet', 278],
    ['Suhaag Raat Mein Jao Re', 'Suman Kalyanpur', 'Classical Wedding', 254],
    ['Mhara Maher Aayo Bapu', 'Traditional', 'Rajasthani Folk', 268],
    ['Raat Bhar Jaagey Hain Saath', 'Alka Yagnik & Udit Narayan', 'Classical Duet', 242],
    ['Dheere Dheere Se Bheegy', 'Shreya Ghoshal', 'Classical Romantic', 238],
    ['Choli Ke Peeche', 'Asha Bhosle & Mohammed Rafi', 'Retro Sangeet', 248],
    ['Mehndi Hai Rachai', 'Shreya Ghoshal', 'Classical Mehndi', 222],
    ['Badhai Ho Badhai', 'Lata Mangeshkar', 'Traditional Sangeet', 256],
    ['Sukhmani Sahib', 'Jagjit Singh', 'Devotional', 294],
    ['Ae Mere Pyare Watan', 'Mohammed Rafi', 'Patriotic Sangeet', 212],
    ['Radhey Radhey Naam', 'Shreya Ghoshal', 'Devotional Classical', 245],
    ['Priya Priya Teri', 'K.S. Chithra', 'South Indian Classical', 238],
    ['Lavni Tani Baaje', 'Shreya Ghoshal', 'Marathi Traditional', 234],
    ['Bhangde Di Reet', 'Nooran Sisters', 'Punjabi Folk', 256],
    ['Dulari Teri Suhani Aankh', 'Sonu Nigam', 'Classical Sangeet', 250]
];

// Bollywood songs
$bollywood_songs = [
    ['Gale Lagaa Le', 'Udit Narayan & Alka Yagnik', 'Bollywood Romance', 246],
    ['Tum Tak', 'Rahat Fateh Ali Khan', 'Bollywood Sufi', 296],
    ['Humdumm', 'Shreya Ghoshal & Rahat Fateh Ali Khan', 'Bollywood Romantic', 218],
    ['Tenu Leke Main Jaaunga', 'Rahat Fateh Ali Khan', 'Bollywood Romance', 268],
    ['Pyaar Ki Ek Moti Khushbu', 'Sonu Nigam & Shreya Ghoshal', 'Bollywood Romantic', 242],
    ['Kabhii Mayne Kaha Tha', 'Arijit Singh', 'Bollywood Romantic', 258],
    ['Pal Pal Dil Ke Paas', 'Arijit Singh', 'Bollywood Romance', 274],
    ['Raabta', 'Arijit Singh & Nidhhi Agerwal', 'Bollywood Modern', 224],
    ['Teri Khair Mangdi', 'Akhil Sachdeva', 'Bollywood Romantic', 252],
    ['Main Tera Ban Jaunga', 'Arjun Kanungo & Carla Dennis', 'Bollywood Modern', 240],
    ['Ae Dil Hai Mushkil', 'Anushka Manchanda', 'Bollywood Ballad', 278],
    ['Kabhi Kabhi Aditi Zindagi', 'Arijit Singh', 'Bollywood Melancholy', 286],
    ['Thaane Ke Liye', 'Shreya Ghoshal', 'Bollywood Dance', 232],
    ['Saiyaan', 'Kailash Kher', 'Bollywood Sufi', 242],
    ['Bheegi Bheegi Raaton Mein', 'Anupam Roy', 'Bollywood Rain Song', 254],
    ['Kahin Door', 'Mohammed Rafi', 'Evergreen Romance', 268],
    ['Kitni Haseen Hogi', 'Sonu Nigam', 'Modern Romantic', 246],
    ['Toh Phir Aao', 'Arjun Kanungo', 'Contemporary Sangeet', 238],
    ['Ishq Mein Marjawan', 'Neha Kakkar', 'Modern Romantic', 224],
    ['O Sanam', 'Arjun Kanungo & Carla Dennis', 'Duet Love Song', 252],
    ['Khuda Aur Mohabbat', 'Amjad Sabri', 'Qawwali Sufi', 296],
    ['Mere Haath Mein', 'Prem Joshua', 'Sufi Instrumental', 286],
    ['Tere Bina', 'Shreya Ghoshal', 'Wedding Song', 242],
    ['Jab Se Tum Ho Paas Mere', 'Sonu Nigam', 'Romantic Duet', 268],
    ['Main Tenu Samjheya Kare', 'Rahat Fateh Ali Khan', 'Punjabi Love', 260]
];

$stmt = $db->prepare("INSERT INTO playlist_songs (playlist_id, song_name, artist_name, genre, duration_seconds, song_order) VALUES (?, ?, ?, ?, ?, ?)");

// Insert classical songs
$count = 0;
foreach ($classical_songs as $i => $song) {
    $order = $i + 1;
    $stmt->bind_param('isssii', $pl1_id, $song[0], $song[1], $song[2], $song[3], $order);
    $stmt->execute();
    $count++;
}

// Insert bollywood songs
foreach ($bollywood_songs as $i => $song) {
    $order = $i + 1;
    $stmt->bind_param('isssii', $pl2_id, $song[0], $song[1], $song[2], $song[3], $order);
    $stmt->execute();
    $count++;
}

jsonResponse(['success' => true, 'message' => 'Playlists loaded successfully', 'songs_count' => $count, 'playlists' => 2]);
?>
