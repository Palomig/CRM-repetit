<?php
// Debug index.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Dashboard Debug</h1>";

try {
    echo "<p>1. Loading header...</p>";
    require_once 'includes/header.php';

    echo "<p>2. Getting students stats...</p>";
    $studentsStats = getStudentsStats();
    echo "<p>✓ Students stats: " . print_r($studentsStats, true) . "</p>";

    echo "<p>3. Getting finance stats...</p>";
    $financeStats = getFinanceStats();
    echo "<p>✓ Finance stats: " . print_r($financeStats, true) . "</p>";

    echo "<p>4. Getting tasks...</p>";
    $tasks = getTasksForDashboard('pending', 5);
    echo "<p>✓ Tasks count: " . count($tasks) . "</p>";

    echo "<h2 style='color: green;'>All tests passed!</h2>";

} catch (Throwable $e) {
    echo "<h2 style='color: red;'>ERROR:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
