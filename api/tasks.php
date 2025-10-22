<?php
require_once '../includes/config_api.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

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
    jsonResponse(['error' => 'Произошла ошибка: ' . $e->getMessage()], 500);
}

function getTasks() {
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

function createTask($data) {
    $errors = validate($data, [
        'title' => ['required'],
        'due_date' => ['required']
    ]);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }
    
    $sql = "INSERT INTO tasks (student_id, teacher_id, title, description, due_date, priority, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    db()->query($sql, [
        $data['student_id'] ?? null,
        $data['teacher_id'] ?? null,
        $data['title'],
        $data['description'] ?? null,
        $data['due_date'],
        $data['priority'] ?? 'medium',
        $data['status'] ?? 'pending'
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

function deleteTask($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    db()->query("DELETE FROM tasks WHERE id = ?", [$id]);
    
    jsonResponse(['success' => true, 'message' => 'Задача успешно удалена']);
}