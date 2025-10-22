<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== Testing API Finance ===\n\n";

// Test 1: Check if api/finance.php file exists and is readable
echo "1. Checking api/finance.php file:\n";
$apiFile = __DIR__ . '/api/finance.php';
echo "   Path: $apiFile\n";
echo "   Exists: " . (file_exists($apiFile) ? "YES" : "NO") . "\n";
echo "   Readable: " . (is_readable($apiFile) ? "YES" : "NO") . "\n";

if (file_exists($apiFile)) {
    echo "\n2. First 30 lines of api/finance.php:\n";
    echo "---START---\n";
    $lines = file($apiFile);
    for ($i = 0; $i < min(30, count($lines)); $i++) {
        echo ($i + 1) . ": " . $lines[$i];
    }
    echo "---END---\n";

    // Check for ob_start
    $content = file_get_contents($apiFile);
    echo "\n3. Checking for output buffering:\n";
    echo "   Contains 'ob_start()': " . (strpos($content, 'ob_start()') !== false ? "YES ✓" : "NO ✗") . "\n";
    echo "   Contains 'ob_clean()': " . (strpos($content, 'ob_clean()') !== false ? "YES ✓" : "NO ✗") . "\n";
}

echo "\n4. Making a test GET request to api/finance.php:\n";
$url = 'https://cw95865.tmweb.ru/api/finance.php?month=10&year=2025';
echo "   URL: $url\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

echo "   HTTP Status: $httpCode\n";
echo "   Response Headers:\n";
foreach (explode("\n", $headers) as $header) {
    if (trim($header)) {
        echo "     " . trim($header) . "\n";
    }
}
echo "   Response Body (first 500 chars):\n";
echo "     " . substr($body, 0, 500) . "\n";

if ($httpCode == 500) {
    echo "\n   ⚠️ ERROR 500 detected!\n";
    echo "   Full response body:\n";
    echo "---START---\n";
    echo $body;
    echo "\n---END---\n";
}
?>
