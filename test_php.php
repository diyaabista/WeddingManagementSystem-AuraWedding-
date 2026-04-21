<?php
// Simple test endpoint to verify PHP is working
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = [
    'status' => 'ok',
    'message' => 'PHP is working',
    'post_data' => $_POST,
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion()
];

echo json_encode($response, JSON_PRETTY_PRINT);
?>
