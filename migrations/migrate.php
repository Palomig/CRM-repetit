<?php
/**
 * Database Migration Script
 * Run this script to apply database migrations
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $db = db()->getConnection();

    // Read and execute migration file
    $migrationFile = __DIR__ . '/001_create_boards_table.sql';

    if (!file_exists($migrationFile)) {
        throw new Exception('Migration file not found: ' . $migrationFile);
    }

    $sql = file_get_contents($migrationFile);

    // Split by semicolon and execute each statement
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            // Remove comments and empty statements
            $stmt = preg_replace('/--.*$/m', '', $stmt);
            $stmt = trim($stmt);
            return !empty($stmt);
        }
    );

    $db->beginTransaction();

    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            $db->exec($statement);
        }
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Migration completed successfully',
        'statements_executed' => count($statements)
    ]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
