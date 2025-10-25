<?php
/**
 * Database Migration Script
 * Run this script to apply database migrations
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/api_config.php';
require_once __DIR__ . '/../includes/db.php';

try {
    $db = db()->getConnection();

    // Read migration file
    $migrationFile = __DIR__ . '/001_create_boards_table.sql';

    if (!file_exists($migrationFile)) {
        throw new Exception('Migration file not found: ' . $migrationFile);
    }

    $sql = file_get_contents($migrationFile);

    // Remove comments
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Execute the entire SQL as one statement
    // (MySQL supports multiple statements in one exec call)
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 0);

    // Split by delimiter, handling prepared statements
    $statements = [];
    $buffer = '';
    $inPreparedStatement = false;

    $lines = explode("\n", $sql);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // Track if we're inside a prepared statement block
        if (stripos($line, 'PREPARE') !== false) {
            $inPreparedStatement = true;
        }

        $buffer .= $line . ' ';

        if (stripos($line, 'DEALLOCATE PREPARE') !== false) {
            $inPreparedStatement = false;
            $statements[] = trim($buffer);
            $buffer = '';
        } elseif (!$inPreparedStatement && substr(rtrim($line), -1) === ';') {
            $statements[] = trim($buffer);
            $buffer = '';
        }
    }

    if (!empty(trim($buffer))) {
        $statements[] = trim($buffer);
    }

    $executedCount = 0;
    $errors = [];

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (empty($statement)) continue;

        try {
            $db->exec($statement);
            $executedCount++;
        } catch (PDOException $e) {
            // Log error but continue (some errors are expected if columns already exist)
            $errors[] = [
                'statement' => substr($statement, 0, 100) . '...',
                'error' => $e->getMessage()
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Migration completed successfully',
        'statements_executed' => $executedCount,
        'errors' => $errors,
        'note' => 'Some errors are expected if migration was already partially applied'
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
