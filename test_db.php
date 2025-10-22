<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'includes/config.php';
require_once 'includes/db.php';

echo "<h1>Database Connection Test</h1>";

try {
    echo "<h2>1. Testing database connection...</h2>";
    $conn = db()->getConnection();
    echo "<p style='color: green;'>✓ Database connection successful!</p>";

    echo "<h2>2. Checking if 'finance' table exists...</h2>";
    $stmt = $conn->prepare("SHOW TABLES LIKE 'finance'");
    $stmt->execute();
    $result = $stmt->fetch();

    if ($result) {
        echo "<p style='color: green;'>✓ Table 'finance' exists!</p>";

        echo "<h2>3. Checking table structure...</h2>";
        $stmt = $conn->prepare("DESCRIBE finance");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        foreach ($columns as $col) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($col['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<h2>4. Testing INSERT query...</h2>";
        $testData = [
            'student_id' => null,
            'teacher_id' => null,
            'type' => 'income',
            'amount' => 1000,
            'category' => 'Test',
            'description' => 'Test transaction',
            'transaction_date' => date('Y-m-d')
        ];

        $sql = "INSERT INTO finance (student_id, teacher_id, type, amount, category, description, transaction_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            $testData['student_id'],
            $testData['teacher_id'],
            $testData['type'],
            $testData['amount'],
            $testData['category'],
            $testData['description'],
            $testData['transaction_date']
        ]);

        if ($result) {
            $lastId = $conn->lastInsertId();
            echo "<p style='color: green;'>✓ Test INSERT successful! Last insert ID: $lastId</p>";

            // Clean up test data
            echo "<h2>5. Cleaning up test data...</h2>";
            $stmt = $conn->prepare("DELETE FROM finance WHERE id = ?");
            $stmt->execute([$lastId]);
            echo "<p style='color: green;'>✓ Test data cleaned up!</p>";
        } else {
            echo "<p style='color: red;'>✗ Test INSERT failed!</p>";
        }

    } else {
        echo "<p style='color: red;'>✗ Table 'finance' does not exist!</p>";
        echo "<h2>Creating 'finance' table...</h2>";

        $createTableSQL = "CREATE TABLE IF NOT EXISTS finance (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NULL,
            teacher_id INT NULL,
            type ENUM('income', 'expense') NOT NULL,
            amount DECIMAL(10, 2) NOT NULL,
            category VARCHAR(100) NOT NULL,
            description TEXT NULL,
            transaction_date DATE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL,
            FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $conn->exec($createTableSQL);
        echo "<p style='color: green;'>✓ Table 'finance' created successfully!</p>";
    }

    echo "<h2>All tests completed!</h2>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
