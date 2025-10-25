<?php
// Check what version of tasks.php is on the server
echo "File check: " . __FILE__ . "\n\n";

$content = file_get_contents(__FILE__);

if (strpos($content, 'About to ob_clean') !== false) {
    echo "✓ NEW VERSION with detailed logging\n";
} else {
    echo "✗ OLD VERSION - GitHub Actions hasn't deployed yet!\n";
}

echo "\n\nFirst 50 lines of tasks.php:\n";
echo "================\n";

$tasksFile = __DIR__ . '/tasks.php';
if (file_exists($tasksFile)) {
    $lines = file($tasksFile);
    echo implode('', array_slice($lines, 0, 50));
} else {
    echo "tasks.php not found!\n";
}
