<?php
// Temporary error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to catch any unexpected output
ob_start();

try {
    // Use API-specific config without sessions and headers
    require_once '../includes/config_api.php';
    require_once '../includes/db.php';
    require_once '../includes/functions.php';

    // Clear any buffered output from includes
    ob_clean();

    // Set JSON header
    header('Content-Type: application/json; charset=utf-8');

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    // Log request details for debugging
    error_log("API Request: Method=$method, Input=" . json_encode($input));

    switch ($method) {
        case 'GET':
            if (isset($_GET['stats'])) {
                getFinanceStats();
            } else {
                getTransactions();
            }
            break;

        case 'POST':
            if (!$input) {
                jsonResponse(['error' => 'Некорректные данные запроса'], 400);
            }
            createTransaction($input);
            break;

        case 'DELETE':
            if (!isset($input['id'])) {
                jsonResponse(['error' => 'ID не указан'], 400);
            }
            deleteTransaction($input['id']);
            break;

        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    ob_clean(); // Clear any buffered output
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    ob_clean(); // Clear any buffered output
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Произошла ошибка: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    // Catch any fatal errors
    error_log("Fatal Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    ob_clean(); // Clear any buffered output
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Критическая ошибка: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

function getTransactions() {
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
    $transactions = db()->fetchAll("
        SELECT 
            f.*,
            s.name as student_name,
            t.name as teacher_name
        FROM finance f
        LEFT JOIN students s ON f.student_id = s.id
        LEFT JOIN teachers t ON f.teacher_id = t.id
        WHERE MONTH(f.transaction_date) = ? AND YEAR(f.transaction_date) = ?
        ORDER BY f.transaction_date DESC, f.created_at DESC
    ", [$month, $year]);
    
    jsonResponse(['success' => true, 'data' => $transactions]);
}

function getFinanceStats() {
    $month = $_GET['month'] ?? date('m');
    $year = $_GET['year'] ?? date('Y');
    
    // Общая статистика
    $summary = db()->fetchOne("
        SELECT 
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as total_income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as total_expense
        FROM finance
        WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
    ", [$month, $year]);
    
    $summary['profit'] = ($summary['total_income'] ?? 0) - ($summary['total_expense'] ?? 0);
    
    // По категориям
    $byCategory = db()->fetchAll("
        SELECT 
            category,
            type,
            SUM(amount) as total
        FROM finance
        WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
        GROUP BY category, type
        ORDER BY total DESC
    ", [$month, $year]);
    
    // По предметам
    $bySubject = db()->fetchAll("
        SELECT 
            s.subject,
            SUM(f.amount) as total
        FROM finance f
        JOIN students s ON f.student_id = s.id
        WHERE f.type = 'income' 
        AND MONTH(f.transaction_date) = ? 
        AND YEAR(f.transaction_date) = ?
        GROUP BY s.subject
        ORDER BY total DESC
    ", [$month, $year]);
    
    // По преподавателям
    $byTeacher = db()->fetchAll("
        SELECT 
            t.name,
            SUM(f.amount) as total
        FROM finance f
        JOIN teachers t ON f.teacher_id = t.id
        WHERE f.type = 'expense' 
        AND f.category = 'Зарплата'
        AND MONTH(f.transaction_date) = ? 
        AND YEAR(f.transaction_date) = ?
        GROUP BY t.id, t.name
        ORDER BY total DESC
    ", [$month, $year]);
    
    // Динамика по месяцам (последние 6 месяцев)
    $monthlyTrend = db()->fetchAll("
        SELECT 
            DATE_FORMAT(transaction_date, '%Y-%m') as month,
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
        FROM finance
        WHERE transaction_date >= DATE_SUB(CONCAT(?, '-', ?, '-01'), INTERVAL 5 MONTH)
        GROUP BY DATE_FORMAT(transaction_date, '%Y-%m')
        ORDER BY month
    ", [$year, $month]);
    
    jsonResponse([
        'success' => true,
        'data' => [
            'summary' => $summary,
            'byCategory' => $byCategory,
            'bySubject' => $bySubject,
            'byTeacher' => $byTeacher,
            'monthlyTrend' => $monthlyTrend
        ]
    ]);
}

function createTransaction($data) {
    // Log incoming data for debugging
    error_log("Create transaction data: " . json_encode($data));

    $errors = validate($data, [
        'type' => ['required'],
        'amount' => ['required', 'numeric'],
        'category' => ['required'],
        'transaction_date' => ['required']
    ]);

    if (!empty($errors)) {
        error_log("Validation errors: " . json_encode($errors));
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }

    // Convert empty strings to null for foreign keys
    $student_id = !empty($data['student_id']) ? $data['student_id'] : null;
    $teacher_id = !empty($data['teacher_id']) ? $data['teacher_id'] : null;

    error_log("Processed IDs - student_id: " . var_export($student_id, true) . ", teacher_id: " . var_export($teacher_id, true));

    try {
        $sql = "INSERT INTO finance (student_id, teacher_id, type, amount, category, description, transaction_date)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $params = [
            $student_id,
            $teacher_id,
            $data['type'],
            $data['amount'],
            $data['category'],
            $data['description'] ?? null,
            $data['transaction_date']
        ];

        error_log("SQL params: " . json_encode($params));

        db()->query($sql, $params);

        $id = db()->lastInsertId();

        jsonResponse(['success' => true, 'id' => $id, 'message' => 'Транзакция успешно добавлена'], 201);
    } catch (Exception $e) {
        error_log("Transaction insert error: " . $e->getMessage());
        jsonResponse(['error' => 'Ошибка базы данных: ' . $e->getMessage()], 500);
    }
}

function deleteTransaction($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    db()->query("DELETE FROM finance WHERE id = ?", [$id]);
    
    jsonResponse(['success' => true, 'message' => 'Транзакция успешно удалена']);
}