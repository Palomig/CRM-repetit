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
    error_log("API Tasks Request: Method=$method, Input=" . json_encode($input));

    switch ($method) {
        case 'GET':
            api_getTasks();
            break;

        case 'POST':
            if (!$input) {
                jsonResponse(['error' => 'Некорректные данные запроса'], 400);
            }
            api_createTask($input);
            break;

        case 'PUT':
            if (!$input) {
                jsonResponse(['error' => 'Некорректные данные запроса'], 400);
            }
            api_updateTask($input);
            break;

        case 'DELETE':
            if (!isset($input['id'])) {
                jsonResponse(['error' => 'ID не указан'], 400);
            }
            api_deleteTask($input['id']);
            break;

        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Ошибка базы данных: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Произошла ошибка: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    error_log("Fatal Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Критическая ошибка: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

function api_getTasks() {
    $status = $_GET['status'] ?? null;

    $sql = "
        SELECT
            t.*,
            s.name as student_name,
            te.name as teacher_name
        FROM tasks t
        LEFT JOIN students s ON t.student_id = s.id
        LEFT JOIN teachers te ON t.teacher_id = te.id
    ";

    $params = [];

    if ($status) {
        $sql .= " WHERE t.status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY
        CASE
            WHEN t.priority = 'high' THEN 1
            WHEN t.priority = 'medium' THEN 2
            WHEN t.priority = 'low' THEN 3
        END,
        t.due_date ASC
    ";

    $tasks = db()->fetchAll($sql, $params);

    jsonResponse(['success' => true, 'data' => $tasks]);
}

function api_createTask($data) {
    error_log("Create task data: " . json_encode($data));

    $errors = validate($data, [
        'title' => ['required'],
        'due_date' => ['required']
    ]);

    if (!empty($errors)) {
        error_log("Validation errors: " . json_encode($errors));
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }

    try {
        $sql = "INSERT INTO tasks (student_id, teacher_id, title, description, due_date, priority, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $params = [
            !empty($data['student_id']) ? $data['student_id'] : null,
            !empty($data['teacher_id']) ? $data['teacher_id'] : null,
            $data['title'],
            $data['description'] ?? null,
            $data['due_date'],
            $data['priority'] ?? 'medium',
            $data['status'] ?? 'pending'
        ];

        error_log("SQL params: " . json_encode($params));

        db()->query($sql, $params);

        $id = db()->lastInsertId();

        jsonResponse(['success' => true, 'id' => $id, 'message' => 'Задача успешно создана'], 201);
    } catch (Exception $e) {
        error_log("Task insert error: " . $e->getMessage());
        jsonResponse(['error' => 'Ошибка базы данных: ' . $e->getMessage()], 500);
    }
}

function api_updateTask($data) {
    if (!isset($data['id'])) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }

    $fields = [];
    $params = [];

    $allowedFields = ['student_id', 'teacher_id', 'title', 'description', 'due_date', 'priority', 'status'];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    if (empty($fields)) {
        jsonResponse(['error' => 'Нет данных для обновления'], 400);
    }

    $params[] = $data['id'];
    $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = ?";

    db()->query($sql, $params);

    jsonResponse(['success' => true, 'message' => 'Задача успешно обновлена']);
}

function api_deleteTask($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }

    db()->query("DELETE FROM tasks WHERE id = ?", [$id]);

    jsonResponse(['success' => true, 'message' => 'Задача успешно удалена']);
}
