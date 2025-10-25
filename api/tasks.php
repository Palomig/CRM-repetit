<?php
// Start output buffering to prevent any stray output
ob_start();

// API endpoint - set JSON header before any output
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/api_config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Clean any output from includes
ob_clean();

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'GET':
            getTasks();
            break;
            
        case 'POST':
            createTask($input);
            break;
            
        case 'PUT':
            updateTask($input);
            break;
            
        case 'DELETE':
            deleteTask($input['id']);
            break;
            
        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Return more detailed error in development (remove in production)
    jsonResponse([
        'error' => 'Произошла ошибка: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}

function getTasks() {
    $status = $_GET['status'] ?? null;
    $boardId = $_GET['board_id'] ?? null;

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
    $conditions = [];

    if ($status) {
        $conditions[] = "t.status = ?";
        $params[] = $status;
    }

    if ($boardId) {
        $conditions[] = "t.board_id = ?";
        $params[] = $boardId;
    }

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(' AND ', $conditions);
    }

    $sql .= " ORDER BY
        t.position ASC,
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

function createTask($data) {
    $errors = validate($data, [
        'title' => ['required'],
        'due_date' => ['required']
    ]);

    if (!empty($errors)) {
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }

    // Convert empty strings to null for foreign keys
    $boardId = !empty($data['board_id']) ? $data['board_id'] : null;
    $studentId = !empty($data['student_id']) ? $data['student_id'] : null;
    $teacherId = !empty($data['teacher_id']) ? $data['teacher_id'] : null;

    // Get max position for the board
    if ($boardId !== null) {
        $maxPosition = db()->fetchOne(
            "SELECT MAX(position) as max_pos FROM tasks WHERE board_id = ?",
            [$boardId]
        );
    } else {
        $maxPosition = db()->fetchOne(
            "SELECT MAX(position) as max_pos FROM tasks WHERE board_id IS NULL"
        );
    }
    $position = ($maxPosition['max_pos'] ?? -1) + 1;

    $sql = "INSERT INTO tasks (board_id, student_id, teacher_id, title, description, due_date, priority, status, position)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    db()->query($sql, [
        $boardId,
        $studentId,
        $teacherId,
        $data['title'],
        $data['description'] ?? null,
        $data['due_date'],
        $data['priority'] ?? 'medium',
        $data['status'] ?? 'pending',
        $position
    ]);

    $id = db()->lastInsertId();

    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Задача успешно создана'], 201);
}

function updateTask($data) {
    if (!isset($data['id'])) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }

    $fields = [];
    $params = [];

    $allowedFields = ['board_id', 'student_id', 'teacher_id', 'title', 'description', 'due_date', 'priority', 'status', 'position'];

    foreach ($allowedFields as $field) {
        if (array_key_exists($field, $data)) {
            $fields[] = "$field = ?";
            // Convert empty strings to null for foreign key fields
            if (in_array($field, ['board_id', 'student_id', 'teacher_id'])) {
                $params[] = !empty($data[$field]) ? $data[$field] : null;
            } else {
                $params[] = $data[$field];
            }
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

function deleteTask($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    db()->query("DELETE FROM tasks WHERE id = ?", [$id]);
    
    jsonResponse(['success' => true, 'message' => 'Задача успешно удалена']);
}