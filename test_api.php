<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>API Test</h1>";

// Test GET request
echo "<h2>1. Testing GET /api/finance.php?month=10&year=2025</h2>";
try {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET['month'] = '10';
    $_GET['year'] = '2025';

    ob_start();
    include 'api/finance.php';
    $output = ob_get_clean();

    echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($output);
    echo "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Test POST request
echo "<h2>2. Testing POST /api/finance.php (Create Transaction)</h2>";
$testTransaction = [
    'type' => 'income',
    'amount' => 1000,
    'category' => 'Тест',
    'description' => 'Тестовая транзакция',
    'transaction_date' => date('Y-m-d'),
    'student_id' => '',
    'teacher_id' => ''
];

echo "<p>Request data:</p>";
echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
echo htmlspecialchars(json_encode($testTransaction, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "</pre>";

try {
    // Simulate POST request
    $_SERVER['REQUEST_METHOD'] = 'POST';

    // Create a temporary file to simulate php://input
    $tempInput = tmpfile();
    fwrite($tempInput, json_encode($testTransaction));
    rewind($tempInput);

    ob_start();

    // We can't easily test this without modifying the API file
    // Let's make a real HTTP request instead

    ob_end_clean();

    echo "<p>Making real HTTP request...</p>";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/finance.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testTransaction));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);

    $stderr = fopen('php://temp', 'rw+');
    curl_setopt($ch, CURLOPT_STDERR, $stderr);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    rewind($stderr);
    $verboseLog = stream_get_contents($stderr);

    curl_close($ch);

    echo "<p>HTTP Status Code: <strong>$httpCode</strong></p>";
    echo "<p>Response:</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($response);
    echo "</pre>";

    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "<p>Parsed JSON:</p>";
            echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
            print_r($data);
            echo "</pre>";
        }
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>3. Direct include test</h2>";
echo "<p>Testing if api/finance.php can be loaded...</p>";

try {
    ob_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Reset superglobals
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = ['month' => '10', 'year' => '2025'];
    $_POST = [];

    // Try to include the file
    include 'api/finance.php';

    $output = ob_get_clean();

    echo "<p style='color: green;'>✓ File loaded successfully!</p>";
    echo "<p>Output:</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
    echo htmlspecialchars($output);
    echo "</pre>";

} catch (Throwable $e) {
    $output = ob_get_clean();
    echo "<p style='color: red;'>✗ Error loading file!</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";

    if ($output) {
        echo "<p>Output before error:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo htmlspecialchars($output);
        echo "</pre>";
    }
}
?>
