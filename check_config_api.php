<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== Checking config_api.php deployment ===\n\n";

$configApiPath = __DIR__ . '/includes/config_api.php';

echo "1. Checking if config_api.php exists:\n";
echo "   Path: $configApiPath\n";
echo "   Exists: " . (file_exists($configApiPath) ? "YES ✓" : "NO ✗") . "\n";

if (file_exists($configApiPath)) {
    echo "   Readable: " . (is_readable($configApiPath) ? "YES ✓" : "NO ✗") . "\n";
    echo "   Size: " . filesize($configApiPath) . " bytes\n\n";

    echo "2. Contents of config_api.php:\n";
    echo "---START---\n";
    echo file_get_contents($configApiPath);
    echo "\n---END---\n\n";
} else {
    echo "\n   ✗ File NOT FOUND! Deployment may not have completed.\n\n";
}

echo "3. Checking api/finance.php:\n";
$financeApiPath = __DIR__ . '/api/finance.php';
if (file_exists($financeApiPath)) {
    $content = file_get_contents($financeApiPath);
    echo "   Uses config_api.php: " . (strpos($content, 'config_api.php') !== false ? "YES ✓" : "NO ✗") . "\n";
    echo "   Uses old config.php: " . (strpos($content, "require_once '../includes/config.php'") !== false ? "YES (PROBLEM!)" : "NO ✓") . "\n";

    echo "\n   First 15 lines of api/finance.php:\n";
    echo "   ---START---\n";
    $lines = explode("\n", $content);
    for ($i = 0; $i < min(15, count($lines)); $i++) {
        echo "   " . ($i + 1) . ": " . $lines[$i] . "\n";
    }
    echo "   ---END---\n";
}

echo "\n4. Trying to load config_api.php:\n";
if (file_exists($configApiPath)) {
    try {
        ob_start();
        require_once $configApiPath;
        ob_end_clean();
        echo "   ✓ config_api.php loaded successfully!\n";
        echo "   DB_HOST defined: " . (defined('DB_HOST') ? "YES ✓" : "NO ✗") . "\n";
        echo "   DB_NAME defined: " . (defined('DB_NAME') ? "YES ✓" : "NO ✗") . "\n";
    } catch (Throwable $e) {
        ob_end_clean();
        echo "   ✗ Error loading config_api.php:\n";
        echo "   " . $e->getMessage() . "\n";
        echo "   " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}
?>
