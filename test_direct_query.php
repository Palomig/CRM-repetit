<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Direct Finance Query Test</h1>";

try {
    echo "<h2>Step 1: Loading config.php</h2>";
    require_once 'includes/config.php';
    echo "<p style='color: green;'>✓ Config loaded</p>";

    echo "<h2>Step 2: Loading db.php</h2>";
    require_once 'includes/db.php';
    echo "<p style='color: green;'>✓ DB class loaded</p>";

    echo "<h2>Step 3: Loading functions.php</h2>";
    require_once 'includes/functions.php';
    echo "<p style='color: green;'>✓ Functions loaded</p>";

    echo "<h2>Step 4: Creating database connection</h2>";
    $db = db();
    echo "<p style='color: green;'>✓ DB instance created</p>";

    echo "<h2>Step 5: Testing simple query</h2>";
    $result = $db->fetchOne("SELECT 1 as test");
    echo "<p style='color: green;'>✓ Simple query works: " . print_r($result, true) . "</p>";

    echo "<h2>Step 6: Checking if finance table exists</h2>";
    $tables = $db->fetchAll("SHOW TABLES LIKE 'finance'");
    if (count($tables) > 0) {
        echo "<p style='color: green;'>✓ Finance table exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Finance table NOT found!</p>";
        die();
    }

    echo "<h2>Step 7: Describing finance table</h2>";
    $columns = $db->fetchAll("DESCRIBE finance");
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>Step 8: Executing getTransactions query</h2>";
    $month = 10;
    $year = 2025;

    echo "<p>Query parameters: month=$month, year=$year</p>";

    $sql = "
        SELECT
            f.*,
            s.name as student_name,
            t.name as teacher_name
        FROM finance f
        LEFT JOIN students s ON f.student_id = s.id
        LEFT JOIN teachers t ON f.teacher_id = t.id
        WHERE MONTH(f.transaction_date) = ? AND YEAR(f.transaction_date) = ?
        ORDER BY f.transaction_date DESC, f.created_at DESC
    ";

    echo "<p>SQL Query:</p>";
    echo "<pre>" . htmlspecialchars($sql) . "</pre>";

    echo "<p>Executing query...</p>";
    $transactions = $db->fetchAll($sql, [$month, $year]);

    echo "<p style='color: green;'>✓ Query executed successfully!</p>";
    echo "<p>Number of transactions found: " . count($transactions) . "</p>";

    if (count($transactions) > 0) {
        echo "<h3>First transaction:</h3>";
        echo "<pre>" . print_r($transactions[0], true) . "</pre>";
    }

    echo "<h2>Step 9: Testing getFinanceStats query</h2>";

    $sql = "
        SELECT
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM finance
        WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
    ";

    echo "<p>Executing stats query...</p>";
    $summary = $db->fetchOne($sql, [$month, $year]);

    echo "<p style='color: green;'>✓ Stats query executed successfully!</p>";
    echo "<pre>" . print_r($summary, true) . "</pre>";

    echo "<h2 style='color: green;'>✓✓✓ All tests passed! ✓✓✓</h2>";
    echo "<p>The database queries work fine. The problem must be in the API request handling or headers.</p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>✗ Database Error!</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Code:</strong> " . $e->getCode() . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>✗ Error!</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Throwable $e) {
    echo "<h2 style='color: red;'>✗ Fatal Error!</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
