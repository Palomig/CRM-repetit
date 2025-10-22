<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['stats'])) {
                getFinanceStats();
            } else {
                getTransactions();
            }
            break;
            
        case 'POST':
            createTransaction($input);
            break;
            
        case 'DELETE':
            deleteTransaction($input['id']);
            break;
            
        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    jsonResponse(['error' => 'Произошла ошибка: ' . $e->getMessage()], 500);
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
    $errors = validate($data, [
        'type' => ['required'],
        'amount' => ['required', 'numeric'],
        'category' => ['required'],
        'transaction_date' => ['required']
    ]);

    if (!empty($errors)) {
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }

    // Convert empty strings to null for foreign keys
    $student_id = !empty($data['student_id']) ? $data['student_id'] : null;
    $teacher_id = !empty($data['teacher_id']) ? $data['teacher_id'] : null;

    $sql = "INSERT INTO finance (student_id, teacher_id, type, amount, category, description, transaction_date)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    db()->query($sql, [
        $student_id,
        $teacher_id,
        $data['type'],
        $data['amount'],
        $data['category'],
        $data['description'] ?? null,
        $data['transaction_date']
    ]);
    
    $id = db()->lastInsertId();
    
    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Транзакция успешно добавлена'], 201);
}

function deleteTransaction($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    db()->query("DELETE FROM finance WHERE id = ?", [$id]);
    
    jsonResponse(['success' => true, 'message' => 'Транзакция успешно удалена']);
}