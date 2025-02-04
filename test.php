<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

echo json_encode([
    'status' => 'working',
    'message' => 'PHP server is running correctly',
    'php_version' => PHP_VERSION,
    'time' => date('Y-m-d H:i:s')
]);
?>
