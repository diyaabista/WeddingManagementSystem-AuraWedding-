<?php
require_once 'php/config.php';

$db = getDB();

// First, check if playlists exist
$result = $db->query("SELECT COUNT(*) as count FROM playlists");
$row = $result->fetch_assoc();
echo "Current playlists: " . $row['count'] . "\n";

// Check songs count
$result = $db->query("SELECT COUNT(*) as count FROM playlist_songs");
$row = $result->fetch_assoc();
echo "Current songs: " . $row['count'] . "\n";

// If no playlists, create them
if ($row['count'] == 0) {
    echo "\nCreating playlists and songs...\n";
    
    // Create playlists
    $db->query("INSERT INTO playlists (wedding_id, playlist_name, description, event_type, mood) VALUES
    (1, 'Classical Sangeet Collection', 'Traditional classical songs perfect for sangeet ceremony', 'sangeet', 'Classical'),
    (1, 'Bollywood Sangeet Hits', 'Modern Bollywood romantic songs for sangeet celebration', 'sangeet', 'Romantic')");
    
    echo "Playlists created.\n";
    
    // Get playlist IDs
    $result = $db->query("SELECT id FROM playlists ORDER BY id");
    $playlists = [];
    while ($row = $result->fetch_assoc()) {
        $playlists[] = $row['id'];
    }
    
    if (count($playlists) >= 2) {
        $pl1_id = $playlists[0];
        $pl2_id = $playlists[1];
        
        // Classical songs (25)
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
        
        // Bollywood songs (25)
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
        
        // Insert classical songs
        foreach ($classical_songs as $i => $song) {
            $stmt = $db->prepare("INSERT INTO playlist_songs (playlist_id, song_name, artist_name, genre, duration_seconds, song_order) VALUES (?, ?, ?, ?, ?, ?)");
            $order = $i + 1;
            $stmt->bind_param('isssii', $pl1_id, $song[0], $song[1], $song[2], $song[3], $order);
            $stmt->execute();
        }
        echo "Inserted 25 classical songs.\n";
        
        // Insert bollywood songs
        foreach ($bollywood_songs as $i => $song) {
            $stmt = $db->prepare("INSERT INTO playlist_songs (playlist_id, song_name, artist_name, genre, duration_seconds, song_order) VALUES (?, ?, ?, ?, ?, ?)");
            $order = $i + 1;
            $stmt->bind_param('isssii', $pl2_id, $song[0], $song[1], $song[2], $song[3], $order);
            $stmt->execute();
        }
        echo "Inserted 25 bollywood songs.\n";
    }
}

// Verify final counts
$result = $db->query("SELECT COUNT(*) as count FROM playlists");
$row = $result->fetch_assoc();
echo "\nFinal playlists: " . $row['count'] . "\n";

$result = $db->query("SELECT COUNT(*) as count FROM playlist_songs");
$row = $result->fetch_assoc();
echo "Final songs: " . $row['count'] . "\n";

echo "\n✅ Database loaded successfully!\n";
?>
