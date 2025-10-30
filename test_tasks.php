<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Tasks API Test</h1>";
echo "<p>Testing /api/tasks.php</p>";

// Test GET request
echo "<h2>1. Testing GET /api/tasks.php</h2>";
try {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = [];

    ob_start();
    include 'api/tasks.php';
    $output = ob_get_clean();

    echo "<p style='color: green;'>✓ File loaded successfully!</p>";
    echo "<p>Output:</p>";
    echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc; max-height: 400px; overflow: auto;'>";
    echo htmlspecialchars($output);
    echo "</pre>";

    // Try to parse as JSON
    $json = json_decode($output, true);
    if ($json !== null) {
        echo "<p style='color: green;'>✓ Valid JSON!</p>";
        echo "<pre style='background: #e0ffe0; padding: 10px; border: 1px solid #0a0;'>";
        print_r($json);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Invalid JSON! Error: " . json_last_error_msg() . "</p>";
    }

} catch (Throwable $e) {
    $output = ob_get_clean();
    echo "<p style='color: red;'>✗ Error loading file!</p>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<p><strong>Stack Trace:</strong></p>";
    echo "<pre style='background: #ffe0e0; padding: 10px; border: 1px solid #a00;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";

    if ($output) {
        echo "<p><strong>Output before error:</strong></p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; border: 1px solid #ccc;'>";
        echo htmlspecialchars($output);
        echo "</pre>";
    }
}

// Test checking if file exists
echo "<h2>2. File system check</h2>";
echo "<p>api/tasks.php exists: " . (file_exists('api/tasks.php') ? '✓ Yes' : '✗ No') . "</p>";
echo "<p>api/tasks.php readable: " . (is_readable('api/tasks.php') ? '✓ Yes' : '✗ No') . "</p>";

// Test includes
echo "<h2>3. Testing includes</h2>";
try {
    echo "<p>Testing api_config.php...</p>";
    ob_start();
    require_once 'includes/api_config.php';
    ob_end_clean();
    echo "<p style='color: green;'>✓ api_config.php loaded</p>";
} catch (Throwable $e) {
    ob_end_clean();
    echo "<p style='color: red;'>✗ api_config.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

try {
    echo "<p>Testing db.php...</p>";
    ob_start();
    require_once 'includes/db.php';
    ob_end_clean();
    echo "<p style='color: green;'>✓ db.php loaded</p>";

    // Test DB connection
    if (function_exists('db')) {
        $testQuery = db()->fetchAll("SELECT 1 as test");
        echo "<p style='color: green;'>✓ Database connection works!</p>";
        echo "<pre>Result: ";
        print_r($testQuery);
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠ db() function not found</p>";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "<p style='color: red;'>✗ db.php error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<h2>4. PHP Info</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Output Buffering: " . (ob_get_level() > 0 ? 'Active (level ' . ob_get_level() . ')' : 'Inactive') . "</p>";
?>
