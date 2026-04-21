<?php
// ============================================================
// AuraWedding - Database Setup Script (php/setup_db.php)
// ============================================================

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'aurawedding';

// Connect to MySQL (without database)
$conn = new mysqli($dbHost, $dbUser, $dbPass);
if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

// Read the SQL file
$sqlFile = dirname(__FILE__) . '/../sql/aurawedding.sql';
if (!file_exists($sqlFile)) {
    die(json_encode(['error' => 'SQL file not found at ' . $sqlFile]));
}

$sql = file_get_contents($sqlFile);

// Execute multiple queries
if ($conn->multi_query($sql)) {
    // Process all results
    do {
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->next_result());
    
    echo json_encode([
        'success' => true,
        'message' => 'Database setup completed successfully!',
        'db' => $dbName,
        'demo_user_email' => 'user@aurawedding.com',
        'demo_admin_email' => 'admin@aurawedding.com',
        'demo_password' => 'wedding123'
    ]);
} else {
    echo json_encode([
        'error' => 'Error executing SQL: ' . $conn->error
    ]);
}

$conn->close();
?>

