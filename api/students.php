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
            if (isset($_GET['id'])) {
                getStudent($_GET['id']);
            } else {
                getStudents();
            }
            break;
            
        case 'POST':
            createStudent($input);
            break;
            
        case 'PUT':
            updateStudent($input);
            break;
            
        case 'DELETE':
            deleteStudent($input['id']);
            break;
            
        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    jsonResponse(['error' => 'Произошла ошибка: ' . $e->getMessage()], 500);
}

function getStudents() {
    $status = $_GET['status'] ?? null;
    $type = $_GET['type'] ?? null;
    
    $sql = "
        SELECT 
            s.*,
            p.name as parent_name,
            p.phone as parent_phone,
            p.whatsapp as parent_whatsapp,
            p.telegram as parent_telegram,
            t.name as teacher_name,
            g.name as group_name
        FROM students s
        LEFT JOIN parents p ON s.parent_id = p.id
        LEFT JOIN teachers t ON s.teacher_id = t.id
        LEFT JOIN `groups` g ON s.group_id = g.id
        WHERE 1=1
    ";
    
    $params = [];
    
    if ($status) {
        $sql .= " AND s.status = ?";
        $params[] = $status;
    }
    
    if ($type) {
        $sql .= " AND s.type = ?";
        $params[] = $type;
    }
    
    $sql .= " ORDER BY s.name";
    
    $students = db()->fetchAll($sql, $params);
    
    jsonResponse(['success' => true, 'data' => $students]);
}

function getStudent($id) {
    $student = db()->fetchOne("
        SELECT 
            s.*,
            p.name as parent_name,
            p.phone as parent_phone,
            p.whatsapp as parent_whatsapp,
            p.telegram as parent_telegram,
            t.name as teacher_name,
            g.name as group_name,
            g.subject as group_subject
        FROM students s
        LEFT JOIN parents p ON s.parent_id = p.id
        LEFT JOIN teachers t ON s.teacher_id = t.id
        LEFT JOIN `groups` g ON s.group_id = g.id
        WHERE s.id = ?
    ", [$id]);
    
    if (!$student) {
        jsonResponse(['error' => 'Ученик не найден'], 404);
    }
    
    // Получить историю уроков
    $lessons = db()->fetchAll("
        SELECT 
            l.*,
            t.name as teacher_name,
            r.name as room_name
        FROM lessons l
        LEFT JOIN teachers t ON l.teacher_id = t.id
        LEFT JOIN rooms r ON l.room_id = r.id
        WHERE l.student_id = ?
        ORDER BY l.lesson_date DESC, l.lesson_time DESC
        LIMIT 10
    ", [$id]);
    
    $student['lessons'] = $lessons;
    
    // Получить историю оплат
    $payments = db()->fetchAll("
        SELECT * FROM finance
        WHERE student_id = ? AND type = 'income'
        ORDER BY transaction_date DESC
        LIMIT 10
    ", [$id]);
    
    $student['payments'] = $payments;
    
    jsonResponse(['success' => true, 'data' => $student]);
}

function createStudent($data) {
    // Валидация
    $errors = validate($data, [
        'name' => ['required'],
        'class' => ['required'],
        'parent_id' => ['required', 'numeric'],
        'subject' => ['required'],
        'type' => ['required'],
        'price' => ['numeric']
    ]);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }
    
    // Если тип "group", проверить заполненность группы
    if ($data['type'] === 'group' && !empty($data['group_id'])) {
        $group = db()->fetchOne("SELECT max_students FROM `groups` WHERE id = ?", [$data['group_id']]);
        $currentCount = getGroupStudentsCount($data['group_id']);
        
        if ($currentCount >= $group['max_students']) {
            jsonResponse(['error' => 'Группа заполнена'], 400);
        }
    }
    
    $sql = "INSERT INTO students (name, class, parent_id, subject, type, group_id, teacher_id, schedule, price, status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    db()->query($sql, [
        $data['name'],
        $data['class'],
        $data['parent_id'],
        $data['subject'],
        $data['type'],
        $data['group_id'] ?? null,
        $data['teacher_id'] ?? null,
        isset($data['schedule']) ? json_encode($data['schedule']) : null,
        $data['price'] ?? 0,
        $data['status'] ?? 'active',
        $data['notes'] ?? null
    ]);
    
    $id = db()->lastInsertId();
    
    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Ученик успешно добавлен'], 201);
}

function updateStudent($data) {
    if (!isset($data['id'])) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    $fields = [];
    $params = [];
    
    $allowedFields = ['name', 'class', 'parent_id', 'subject', 'type', 'group_id', 'teacher_id', 'schedule', 'last_lesson_date', 'price', 'status', 'notes'];
    
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
    $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?";
    
    db()->query($sql, $params);
    
    jsonResponse(['success' => true, 'message' => 'Ученик успешно обновлен']);
}

function deleteStudent($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    db()->query("DELETE FROM students WHERE id = ?", [$id]);
    
    jsonResponse(['success' => true, 'message' => 'Ученик успешно удален']);
}