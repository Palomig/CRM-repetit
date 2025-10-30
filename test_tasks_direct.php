<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Direct Tasks API HTTP Test</h1>";

// Test 1: Direct file check
echo "<h2>1. File Existence Check</h2>";
$apiFile = __DIR__ . '/api/tasks.php';
$configFile = __DIR__ . '/includes/api_config.php';
$dbFile = __DIR__ . '/includes/db.php';

echo "<p>api/tasks.php exists: " . (file_exists($apiFile) ? '✓ Yes' : '✗ No') . "</p>";
echo "<p>includes/api_config.php exists: " . (file_exists($configFile) ? '✓ Yes' : '✗ No') . "</p>";
echo "<p>includes/db.php exists: " . (file_exists($dbFile) ? '✓ Yes' : '✗ No') . "</p>";

// Test 2: Parse check (syntax errors)
echo "<h2>2. Syntax Check</h2>";
$result = shell_exec("php -l api/tasks.php 2>&1");
echo "<pre>" . htmlspecialchars($result) . "</pre>";

// Test 3: Actual HTTP request
echo "<h2>3. HTTP Request to /api/tasks.php</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://cw95865.tmweb.ru/api/tasks.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "<p><strong>HTTP Status:</strong> " . $httpCode . "</p>";
echo "<h3>Response Headers:</h3>";
echo "<pre style='background: #f0f0f0; padding: 10px;'>" . htmlspecialchars($header) . "</pre>";

echo "<h3>Response Body:</h3>";
echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 400px; overflow: auto;'>" . htmlspecialchars($body) . "</pre>";

if ($body) {
    $json = json_decode($body, true);
    if ($json !== null) {
        echo "<p style='color: green;'>✓ Valid JSON</p>";
        echo "<pre style='background: #e0ffe0; padding: 10px;'>";
        print_r($json);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Invalid JSON: " . json_last_error_msg() . "</p>";
    }
}

// Test 4: Check error log
echo "<h2>4. Recent Error Log</h2>";
$errorLog = '/home/c/cw95865/error.log';
if (file_exists($errorLog)) {
    $lines = file($errorLog);
    $recent = array_slice($lines, -20); // Last 20 lines
    echo "<pre style='background: #ffe0e0; padding: 10px; max-height: 300px; overflow: auto;'>";
    echo htmlspecialchars(implode('', $recent));
    echo "</pre>";
} else {
    echo "<p>Error log not found at: $errorLog</p>";
}
?>
