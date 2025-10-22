<?php
header('Content-Type: text/plain; charset=utf-8');

echo "=== Path Diagnostics ===\n\n";

echo "1. Current directory (__DIR__):\n";
echo __DIR__ . "\n\n";

echo "2. Parent directory:\n";
echo dirname(__DIR__) . "\n\n";

echo "3. Checking if files exist with relative paths:\n";
$files = [
    '../includes/config.php',
    '../includes/db.php',
    '../includes/functions.php'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . '/' . $file;
    $exists = file_exists($fullPath);
    $readable = $exists ? is_readable($fullPath) : false;

    echo "  $file\n";
    echo "    Full path: $fullPath\n";
    echo "    Exists: " . ($exists ? "YES" : "NO") . "\n";
    echo "    Readable: " . ($readable ? "YES" : "NO") . "\n";

    if ($exists) {
        echo "    Real path: " . realpath($fullPath) . "\n";
    }
    echo "\n";
}

echo "4. Directory listing of parent:\n";
$parentDir = dirname(__DIR__);
if (is_dir($parentDir)) {
    $items = scandir($parentDir);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $itemPath = $parentDir . '/' . $item;
            $type = is_dir($itemPath) ? '[DIR]' : '[FILE]';
            echo "  $type $item\n";
        }
    }
} else {
    echo "  Parent directory not accessible!\n";
}

echo "\n5. Directory listing of parent/includes:\n";
$includesDir = dirname(__DIR__) . '/includes';
if (is_dir($includesDir)) {
    $items = scandir($includesDir);
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            echo "  $item\n";
        }
    }
} else {
    echo "  Includes directory not found at: $includesDir\n";
}

echo "\n6. PHP include_path:\n";
echo get_include_path() . "\n";

echo "\n7. Server document root:\n";
echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set' . "\n";

echo "\n8. Script filename:\n";
echo $_SERVER['SCRIPT_FILENAME'] ?? 'Not set' . "\n";
?>
