<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Boards API Test</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .output { background: #f0f0f0; padding: 10px; border: 1px solid #ccc; overflow-x: auto; }
</style>";

// Test 1: Check if boards table exists
echo "<h2>1. Check if 'boards' table exists</h2>";
try {
    require_once 'includes/db.php';
    $db = db();

    $result = $db->fetchOne("SHOW TABLES LIKE 'boards'");

    if ($result) {
        echo "<p class='success'>✓ Table 'boards' exists</p>";

        // Get table structure
        $columns = $db->fetchAll("DESCRIBE boards");
        echo "<p>Table structure:</p>";
        echo "<pre class='output'>";
        print_r($columns);
        echo "</pre>";

        // Count rows
        $count = $db->fetchOne("SELECT COUNT(*) as count FROM boards");
        echo "<p>Rows in table: <strong>" . $count['count'] . "</strong></p>";

        // Get all boards
        if ($count['count'] > 0) {
            $boards = $db->fetchAll("SELECT * FROM boards");
            echo "<p>Boards data:</p>";
            echo "<pre class='output'>";
            print_r($boards);
            echo "</pre>";
        }
    } else {
        echo "<p class='error'>✗ Table 'boards' does NOT exist!</p>";
        echo "<p>Please run the migration first.</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre class='output'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// Test 2: Direct include test (GET)
echo "<h2>2. Test GET /api/boards.php (Direct include)</h2>";
try {
    ob_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Reset superglobals
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_GET = [];
    $_POST = [];

    // Try to include the file
    include 'api/boards.php';

    $output = ob_get_clean();

    echo "<p class='success'>✓ File loaded successfully!</p>";
    echo "<p>Output:</p>";
    echo "<pre class='output'>";
    echo htmlspecialchars($output);
    echo "</pre>";

    // Try to parse as JSON
    $json = json_decode($output, true);
    if ($json) {
        echo "<p class='success'>✓ Valid JSON response</p>";
        echo "<pre class='output'>";
        print_r($json);
        echo "</pre>";
    } else {
        echo "<p class='error'>✗ Invalid JSON response</p>";
        echo "<p>JSON error: " . json_last_error_msg() . "</p>";
    }

} catch (Throwable $e) {
    $output = ob_get_clean();
    echo "<p class='error'>✗ Error loading file!</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre class='output'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";

    if ($output) {
        echo "<p>Output before error:</p>";
        echo "<pre class='output'>";
        echo htmlspecialchars($output);
        echo "</pre>";
    }
}

// Test 3: Test database connection
echo "<h2>3. Test Database Connection</h2>";
try {
    require_once 'includes/api_config.php';
    require_once 'includes/db.php';

    $db = db();
    $conn = $db->getConnection();

    echo "<p class='success'>✓ Database connection successful</p>";

    // Test a simple query
    $test = $db->fetchOne("SELECT 1 as test");
    echo "<p class='success'>✓ Simple query works</p>";

} catch (Exception $e) {
    echo "<p class='error'>✗ Database error!</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// Test 4: Test creating a board manually
echo "<h2>4. Test Manual Board Creation</h2>";
try {
    require_once 'includes/db.php';
    $db = db();

    // Get max position
    $maxPosition = $db->fetchOne('SELECT MAX(position) as max_pos FROM boards');
    $position = ($maxPosition['max_pos'] ?? -1) + 1;

    echo "<p>Next position: <strong>$position</strong></p>";

    // Try to insert
    $testName = 'Test Board ' . date('H:i:s');
    $db->query(
        'INSERT INTO boards (name, description, position) VALUES (?, ?, ?)',
        [$testName, 'Test description', $position]
    );

    $boardId = $db->lastInsertId();

    echo "<p class='success'>✓ Board created successfully!</p>";
    echo "<p>New board ID: <strong>$boardId</strong></p>";

    // Fetch it back
    $newBoard = $db->fetchOne('SELECT * FROM boards WHERE id = ?', [$boardId]);
    echo "<p>Created board:</p>";
    echo "<pre class='output'>";
    print_r($newBoard);
    echo "</pre>";

    // Clean up
    $db->query('DELETE FROM boards WHERE id = ?', [$boardId]);
    echo "<p class='success'>✓ Test board deleted</p>";

} catch (Exception $e) {
    echo "<p class='error'>✗ Error creating board!</p>";
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre class='output'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p>Test completed at " . date('Y-m-d H:i:s') . "</p>";
?>
