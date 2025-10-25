<?php
// Ultra minimal test - just to see if file loads at all
ob_start();
header('Content-Type: text/plain');

echo "Tasks API Debug Test\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

echo "1. Testing file write...\n";
$logFile = __DIR__ . '/../simple_test.log';
$result = @file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Test write\n");
echo "Write result: " . ($result !== false ? "SUCCESS ($result bytes)" : "FAILED") . "\n\n";

echo "2. Loading api_config.php...\n";
try {
    require_once '../includes/api_config.php';
    echo "SUCCESS\n\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n\n";
    exit;
}

echo "3. Loading db.php...\n";
try {
    require_once '../includes/db.php';
    echo "SUCCESS\n\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n\n";
    exit;
}

echo "4. Loading functions.php...\n";
try {
    require_once '../includes/functions.php';
    echo "SUCCESS\n\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n\n";
    exit;
}

echo "5. Testing database connection...\n";
try {
    $db = db();
    echo "SUCCESS\n\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n\n";
    exit;
}

echo "6. Testing query (SELECT 1)...\n";
try {
    $test = $db->fetchOne("SELECT 1 as test");
    echo "SUCCESS: " . print_r($test, true) . "\n\n";
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n\n";
    exit;
}

echo "7. Testing tasks query (with board_id)...\n";
try {
    $boardId = $_GET['board_id'] ?? 1;
    echo "Board ID: $boardId\n";

    $sql = "SELECT * FROM tasks WHERE board_id = ? LIMIT 1";
    $task = $db->fetchOne($sql, [$boardId]);

    if ($task) {
        echo "SUCCESS: Found task\n";
        echo print_r($task, true) . "\n";
    } else {
        echo "SUCCESS: No tasks found (table is empty)\n";
    }
} catch (Throwable $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit;
}

echo "\n8. All tests PASSED!\n";
