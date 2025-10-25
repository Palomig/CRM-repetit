<?php
// Minimal test endpoint to debug the issue
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    // Log the request
    $log = [
        'time' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
        'query' => $_GET ?? [],
        'server' => [
            'script_filename' => $_SERVER['SCRIPT_FILENAME'] ?? 'UNKNOWN',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'UNKNOWN',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'UNKNOWN',
        ]
    ];

    file_put_contents(__DIR__ . '/../test_debug.log', print_r($log, true) . "\n\n", FILE_APPEND);

    require_once __DIR__ . '/../includes/api_config.php';
    require_once __DIR__ . '/../includes/db.php';

    ob_clean();

    $db = db();
    $boards = $db->fetchAll('SELECT * FROM boards ORDER BY position, id');

    echo json_encode([
        'success' => true,
        'data' => $boards,
        'debug' => $log
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_clean();

    $error = [
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString())
    ];

    file_put_contents(__DIR__ . '/../test_debug.log', "ERROR:\n" . print_r($error, true) . "\n\n", FILE_APPEND);

    echo json_encode($error, JSON_UNESCAPED_UNICODE);
}
