<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== Path Diagnostics ===\n\n";

echo "1. Current directory (__DIR__):\n";
echo __DIR__ . "\n\n";

echo "2. Document Root:\n";
echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set' . "\n\n";

echo "3. Script Filename:\n";
echo $_SERVER['SCRIPT_FILENAME'] ?? 'Not set' . "\n\n";

echo "4. Checking if includes files exist:\n";
$basePath = __DIR__;
$includesPath = $basePath . '/includes';

echo "  Base path: $basePath\n";
echo "  Includes path: $includesPath\n\n";

$files = [
    'includes/config.php',
    'includes/db.php',
    'includes/functions.php',
    'api/finance.php'
];

foreach ($files as $file) {
    $fullPath = $basePath . '/' . $file;
    $exists = file_exists($fullPath);
    $readable = $exists ? is_readable($fullPath) : false;

    echo "  $file\n";
    echo "    Exists: " . ($exists ? "YES ✓" : "NO ✗") . "\n";
    echo "    Readable: " . ($readable ? "YES ✓" : "NO ✗") . "\n";
    if ($exists) {
        echo "    Real path: " . realpath($fullPath) . "\n";
        echo "    Size: " . filesize($fullPath) . " bytes\n";
    }
    echo "\n";
}

echo "5. Directory listing of root:\n";
if (is_dir($basePath)) {
    $items = scandir($basePath);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $itemPath = $basePath . '/' . $item;
            $type = is_dir($itemPath) ? '[DIR]' : '[FILE]';
            echo "  $type $item\n";
        }
    }
}

echo "\n6. Directory listing of includes/:\n";
if (is_dir($includesPath)) {
    $items = scandir($includesPath);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $itemPath = $includesPath . '/' . $item;
            $size = is_file($itemPath) ? filesize($itemPath) : 0;
            echo "  $item ($size bytes)\n";
        }
    }
} else {
    echo "  ✗ Includes directory not found!\n";
}

echo "\n7. Directory listing of api/:\n";
$apiPath = $basePath . '/api';
if (is_dir($apiPath)) {
    $items = scandir($apiPath);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            echo "  $item\n";
        }
    }
} else {
    echo "  ✗ API directory not found!\n";
}

echo "\n8. Testing require_once from api/ context:\n";
echo "  If we are in api/ directory, path '../includes/config.php' would resolve to:\n";
$apiConfigPath = $basePath . '/api/../includes/config.php';
echo "    $apiConfigPath\n";
echo "    Exists: " . (file_exists($apiConfigPath) ? "YES ✓" : "NO ✗") . "\n";
echo "    Real path: " . (file_exists($apiConfigPath) ? realpath($apiConfigPath) : "N/A") . "\n";
?>
