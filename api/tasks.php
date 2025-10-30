<?php
// Tasks API without functions.php dependency
ob_start();
header('Content-Type: application/json; charset=utf-8');

try {
    error_log("Tasks API: Starting request");
    require_once '../includes/api_config.php';
    error_log("Tasks API: api_config loaded");
    require_once '../includes/db.php';
    error_log("Tasks API: db.php loaded");

    ob_clean();

    $method = $_SERVER['REQUEST_METHOD'];
    error_log("Tasks API: Method = " . $method);
    $input = json_decode(file_get_contents('php://input'), true);

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
            sendJson(['error' => 'Метод не поддерживается'], 405);
    }

} catch (Throwable $e) {
    error_log("Tasks API Error: " . $e->getMessage());
    error_log("Tasks API Stack: " . $e->getTraceAsString());
    ob_clean();
    sendJson([
        'error' => 'Произошла ошибка: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}

function sendJson($data, $code = 200) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function getTasks() {
    $status = $_GET['status'] ?? null;
    $boardId = $_GET['board_id'] ?? null;

    $sql = "SELECT t.*, s.name as student_name, te.name as teacher_name
            FROM tasks t
            LEFT JOIN students s ON t.student_id = s.id
            LEFT JOIN teachers te ON t.teacher_id = te.id";

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

    $sql .= " ORDER BY t.position ASC, t.created_at DESC";

    $tasks = db()->fetchAll($sql, $params);

    sendJson(['success' => true, 'data' => $tasks]);
}

function createTask($data) {
    // Validate
    if (empty($data['title'])) {
        sendJson(['error' => 'Название обязательно'], 400);
    }
    if (empty($data['due_date'])) {
        sendJson(['error' => 'Дата обязательна'], 400);
    }

    $boardId = !empty($data['board_id']) ? $data['board_id'] : null;
    $studentId = !empty($data['student_id']) ? $data['student_id'] : null;
    $teacherId = !empty($data['teacher_id']) ? $data['teacher_id'] : null;

    // Get max position
    if ($boardId !== null) {
        $maxPos = db()->fetchOne("SELECT MAX(position) as max_pos FROM tasks WHERE board_id = ?", [$boardId]);
    } else {
        $maxPos = db()->fetchOne("SELECT MAX(position) as max_pos FROM tasks WHERE board_id IS NULL");
    }
    $position = ($maxPos['max_pos'] ?? -1) + 1;

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
    sendJson(['success' => true, 'id' => $id, 'message' => 'Задача создана'], 201);
}

function updateTask($data) {
    if (!isset($data['id'])) {
        sendJson(['error' => 'ID не указан'], 400);
    }

    $fields = [];
    $params = [];

    $allowedFields = ['board_id', 'student_id', 'teacher_id', 'title', 'description', 'due_date', 'priority', 'status', 'position'];

    foreach ($allowedFields as $field) {
        if (array_key_exists($field, $data)) {
            $fields[] = "$field = ?";
            if (in_array($field, ['board_id', 'student_id', 'teacher_id'])) {
                $params[] = !empty($data[$field]) ? $data[$field] : null;
            } else {
                $params[] = $data[$field];
            }
        }
    }

    if (empty($fields)) {
        sendJson(['error' => 'Нет данных для обновления'], 400);
    }

    $params[] = $data['id'];
    $sql = "UPDATE tasks SET " . implode(', ', $fields) . " WHERE id = ?";

    db()->query($sql, $params);
    sendJson(['success' => true, 'message' => 'Задача обновлена']);
}

function deleteTask($id) {
    if (!$id) {
        sendJson(['error' => 'ID не указан'], 400);
    }

    db()->query("DELETE FROM tasks WHERE id = ?", [$id]);
    sendJson(['success' => true, 'message' => 'Задача удалена']);
}
