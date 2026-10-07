<?php
// ULTRA SIMPLE - no config, no database
session_start();
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'message' => 'DEBUG SIMPLE WORKING',
    'session_id' => session_id(),
    'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
    'timestamp' => time()
]);
?>