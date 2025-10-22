<?php
// Ultra simple test to see if PHP works at all in the api directory
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Step 1: PHP is working in api directory\n";

// Test paths
echo "Step 2: Current directory: " . __DIR__ . "\n";
echo "Step 3: Parent directory: " . dirname(__DIR__) . "\n";

// Check if files exist
$configPath = dirname(__DIR__) . '/includes/config.php';
$dbPath = dirname(__DIR__) . '/includes/db.php';
$functionsPath = dirname(__DIR__) . '/includes/functions.php';

echo "Step 4: Checking file existence...\n";
echo "  config.php: " . (file_exists($configPath) ? "EXISTS" : "NOT FOUND") . " at $configPath\n";
echo "  db.php: " . (file_exists($dbPath) ? "EXISTS" : "NOT FOUND") . " at $dbPath\n";
echo "  functions.php: " . (file_exists($functionsPath) ? "EXISTS" : "NOT FOUND") . " at $functionsPath\n";

// Try to include config
echo "\nStep 5: Including config.php...\n";
try {
    require_once '../includes/config.php';
    echo "  ✓ config.php loaded\n";
} catch (Throwable $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Try to include db
echo "\nStep 6: Including db.php...\n";
try {
    require_once '../includes/db.php';
    echo "  ✓ db.php loaded\n";
} catch (Throwable $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Try to include functions
echo "\nStep 7: Including functions.php...\n";
try {
    require_once '../includes/functions.php';
    echo "  ✓ functions.php loaded\n";
} catch (Throwable $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Try to connect to database
echo "\nStep 8: Testing database connection...\n";
try {
    $db = db();
    echo "  ✓ Database instance created\n";

    $conn = $db->getConnection();
    echo "  ✓ Database connection successful\n";

    // Test a simple query
    $stmt = $conn->query("SELECT 1");
    echo "  ✓ Test query executed\n";
} catch (Throwable $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test jsonResponse function
echo "\nStep 9: Testing jsonResponse function...\n";
try {
    if (function_exists('jsonResponse')) {
        echo "  ✓ jsonResponse function exists\n";
    } else {
        echo "  ✗ jsonResponse function NOT FOUND\n";
    }
} catch (Throwable $e) {
    echo "  ✗ Error: " . $e->getMessage() . "\n";
}

echo "\n✓✓✓ All basic tests passed! ✓✓✓\n";
?>
