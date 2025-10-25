<?php
/**
 * Simple test to check if APIs are working and if log files are being created
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>API Status Check</h1>";
echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } .success { color: green; } .error { color: red; } .output { background: #f0f0f0; padding: 10px; border: 1px solid #ccc; margin: 10px 0; }</style>";

// Test 1: Check if we can write to log files
echo "<h2>1. File Write Permission Test</h2>";
$testFile = __DIR__ . '/test_write.log';
$testContent = "[" . date('Y-m-d H:i:s') . "] Test write\n";

try {
    $result = file_put_contents($testFile, $testContent);
    if ($result !== false) {
        echo "<p class='success'>✓ Can write to root directory (wrote $result bytes)</p>";
        echo "<p>File: <a href='/test_write.log'>test_write.log</a></p>";

        if (file_exists($testFile)) {
            echo "<p class='success'>✓ File exists and can be read</p>";
            echo "<pre class='output'>" . htmlspecialchars(file_get_contents($testFile)) . "</pre>";
        }
    } else {
        echo "<p class='error'>✗ Cannot write to root directory</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 2: Check log files
echo "<h2>2. Check Log Files</h2>";
$logFiles = ['tasks_debug.log', 'boards_debug.log', 'test_debug.log'];
foreach ($logFiles as $logFile) {
    $path = __DIR__ . '/' . $logFile;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p class='success'>✓ $logFile exists ($size bytes)</p>";
        echo "<p><a href='/$logFile' target='_blank'>View $logFile</a></p>";
        echo "<pre class='output'>" . htmlspecialchars(file_get_contents($path)) . "</pre>";
    } else {
        echo "<p>⚠ $logFile does not exist yet (will be created when API is called)</p>";
    }
}

// Test 3: Make actual API calls
echo "<h2>3. API Call Tests</h2>";

echo "<h3>A. Test Boards API (GET)</h3>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/api/boards.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "<p>HTTP Status: <strong>$httpCode</strong></p>";
if ($httpCode == 200) {
    echo "<p class='success'>✓ Boards API works!</p>";
} else {
    echo "<p class='error'>✗ Boards API returned error code</p>";
}
echo "<p>Response headers:</p><pre class='output'>" . htmlspecialchars($headers) . "</pre>";
echo "<p>Response body:</p><pre class='output'>" . htmlspecialchars($body) . "</pre>";

echo "<h3>B. Test Tasks API (GET)</h3>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . '/api/tasks.php?board_id=1');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

echo "<p>HTTP Status: <strong>$httpCode</strong></p>";
if ($httpCode == 200) {
    echo "<p class='success'>✓ Tasks API works!</p>";
} else {
    echo "<p class='error'>✗ Tasks API returned error code</p>";
}
echo "<p>Response headers:</p><pre class='output'>" . htmlspecialchars($headers) . "</pre>";
echo "<p>Response body:</p><pre class='output'>" . htmlspecialchars($body) . "</pre>";

// Test 4: Check if logs were created after API calls
echo "<h2>4. Check Logs After API Calls</h2>";
foreach ($logFiles as $logFile) {
    $path = __DIR__ . '/' . $logFile;
    if (file_exists($path)) {
        echo "<p class='success'>✓ $logFile exists</p>";
        echo "<p><a href='/$logFile' target='_blank'>View $logFile</a></p>";
    } else {
        echo "<p class='error'>✗ $logFile was NOT created (API might not be running or can't write)</p>";
    }
}

echo "<hr><p>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>
