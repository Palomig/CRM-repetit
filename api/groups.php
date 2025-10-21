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
            getGroups();
            break;
            
        case 'POST':
            createGroup($input);
            break;
            
        case 'PUT':
            updateGroup($input);
            break;
            
        case 'DELETE':
            deleteGroup($input['id']);
            break;
            
        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    jsonResponse(['error' => 'Произошла ошибка: ' . $e->getMessage()], 500);
}

function getGroups() {
    $groups = db()->fetchAll("
        SELECT 
            g.*,
            t.name as teacher_name,
            r.name as room_name,
            (SELECT COUNT(*) FROM students WHERE group_id = g.id AND status = 'active') as current_students
        FROM `groups` g
        LEFT JOIN teachers t ON g.teacher_id = t.id
        LEFT JOIN rooms r ON g.room_id = r.id
        ORDER BY g.name
    ");
    
    foreach ($groups as &$group) {
        $group['schedule'] = $group['schedule'] ? json_decode($group['schedule'], true) : [];
        
        // Получить список учеников группы
        $group['students'] = db()->fetchAll("
            SELECT id, name, class FROM students 
            WHERE group_id = ? AND status = 'active'
            ORDER BY name
        ", [$group['id']]);
    }
    
    jsonResponse(['success' => true, 'data' => $groups]);
}

function createGroup($data) {
    $errors = validate($data, [
        'name' => ['required'],
        'subject' => ['required'],
        'teacher_id' => ['required', 'numeric'],
        'room_id' => ['required', 'numeric']
    ]);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }
    
    $schedule = isset($data['schedule']) && is_array($data['schedule']) ? json_encode($data['schedule']) : null;
    
    $sql = "INSERT INTO `groups` (name, subject, teacher_id, room_id, schedule, max_students, price, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    db()->query($sql, [
        $data['name'],
        $data['subject'],
        $data['teacher_id'],
        $data['room_id'],
        $schedule,
        $data['max_students'] ?? 6,
        $data['price'] ?? 0,
        $data['status'] ?? 'active'
    ]);
    
    $id = db()->lastInsertId();
    
    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Группа успешно создана'], 201);
}

function updateGroup($data) {
    if (!isset($data['id'])) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    $fields = [];
    $params = [];
    
    $allowedFields = ['name', 'subject', 'teacher_id', 'room_id', 'schedule', 'max_students', 'price', 'status'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            if ($field === 'schedule' && is_array($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = json_encode($data[$field]);
            } else {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
    }
    
    if (empty($fields)) {
        jsonResponse(['error' => 'Нет данных для обновления'], 400);
    }
    
    $params[] = $data['id'];
    $sql = "UPDATE `groups` SET " . implode(', ', $fields) . " WHERE id = ?";
    
    db()->query($sql, $params);
    
    jsonResponse(['success' => true, 'message' => 'Группа успешно обновлена']);
}

function deleteGroup($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    // Проверить, есть ли ученики в группе
    $count = db()->fetchOne("SELECT COUNT(*) as count FROM students WHERE group_id = ?", [$id]);
    
    if ($count['count'] > 0) {
        jsonResponse(['error' => 'Невозможно удалить группу с учениками'], 400);
    }
    
    db()->query("DELETE FROM `groups` WHERE id = ?", [$id]);
    
    jsonResponse(['success' => true, 'message' => 'Группа успешно удалена']);
}