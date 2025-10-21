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
            getTeachers();
            break;
            
        case 'POST':
            createTeacher($input);
            break;
            
        case 'PUT':
            updateTeacher($input);
            break;
            
        case 'DELETE':
            deleteTeacher($input['id']);
            break;
            
        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    jsonResponse(['error' => 'Произошла ошибка: ' . $e->getMessage()], 500);
}

function getTeachers() {
    $teachers = db()->fetchAll("
        SELECT 
            t.*,
            (SELECT COUNT(*) FROM students WHERE teacher_id = t.id AND status = 'active' AND type = 'individual') as student_count,
            (SELECT COUNT(*) FROM `groups` WHERE teacher_id = t.id AND status = 'active') as group_count
        FROM teachers t
        ORDER BY t.name
    ");
    
    foreach ($teachers as &$teacher) {
        $teacher['subjects'] = json_decode($teacher['subjects'], true);
    }
    
    jsonResponse(['success' => true, 'data' => $teachers]);
}

function createTeacher($data) {
    $errors = validate($data, [
        'name' => ['required'],
        'subjects' => ['required']
    ]);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }
    
    $subjects = is_array($data['subjects']) ? json_encode($data['subjects']) : json_encode([$data['subjects']]);
    
    $sql = "INSERT INTO teachers (name, subjects, phone, salary_rate, status) VALUES (?, ?, ?, ?, ?)";
    
    db()->query($sql, [
        $data['name'],
        $subjects,
        $data['phone'] ?? null,
        $data['salary_rate'] ?? 0,
        $data['status'] ?? 'active'
    ]);
    
    $id = db()->lastInsertId();
    
    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Преподаватель успешно добавлен'], 201);
}

function updateTeacher($data) {
    if (!isset($data['id'])) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    $fields = [];
    $params = [];
    
    $allowedFields = ['name', 'subjects', 'phone', 'salary_rate', 'status'];
    
    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            if ($field === 'subjects') {
                $subjects = is_array($data[$field]) ? json_encode($data[$field]) : json_encode([$data[$field]]);
                $fields[] = "$field = ?";
                $params[] = $subjects;
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
    $sql = "UPDATE teachers SET " . implode(', ', $fields) . " WHERE id = ?";
    
    db()->query($sql, $params);
    
    jsonResponse(['success' => true, 'message' => 'Преподаватель успешно обновлен']);
}

function deleteTeacher($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    db()->query("DELETE FROM teachers WHERE id = ?", [$id]);
    
    jsonResponse(['success' => true, 'message' => 'Преподаватель успешно удален']);
}