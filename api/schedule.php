<?php
// Start output buffering to prevent any stray output
ob_start();

// Set JSON header before any output
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../includes/api_config.php';
    require_once '../includes/db.php';
    require_once '../includes/functions.php';

    // Clean any output from includes
    ob_clean();

    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);

    switch ($method) {
        case 'GET':
            getLessons();
            break;

        case 'POST':
            createLesson($input);
            break;

        case 'PUT':
            updateLesson($input);
            break;

        case 'DELETE':
            deleteLesson($input['id']);
            break;

        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (Throwable $e) {
    error_log("Schedule API Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    ob_clean();
    jsonResponse([
        'error' => 'Произошла ошибка: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], 500);
}

function getLessons() {
    $start = $_GET['start'] ?? date('Y-m-d', strtotime('-1 month'));
    $end = $_GET['end'] ?? date('Y-m-d', strtotime('+2 months'));
    $teacherId = $_GET['teacher_id'] ?? null;
    
    $sql = "
        SELECT 
            l.*,
            COALESCE(s.name, g.name) as title,
            s.name as student_name,
            g.name as group_name,
            t.name as teacher_name,
            r.name as room_name,
            CONCAT(l.lesson_date, ' ', l.lesson_time) as start,
            DATE_ADD(CONCAT(l.lesson_date, ' ', l.lesson_time), INTERVAL l.duration MINUTE) as end
        FROM lessons l
        LEFT JOIN students s ON l.student_id = s.id
        LEFT JOIN `groups` g ON l.group_id = g.id
        LEFT JOIN teachers t ON l.teacher_id = t.id
        LEFT JOIN rooms r ON l.room_id = r.id
        WHERE l.lesson_date BETWEEN ? AND ?
    ";
    
    $params = [$start, $end];
    
    if ($teacherId) {
        $sql .= " AND l.teacher_id = ?";
        $params[] = $teacherId;
    }
    
    $sql .= " ORDER BY l.lesson_date, l.lesson_time";
    
    $lessons = db()->fetchAll($sql, $params);
    
    // Форматируем для FullCalendar
    $events = [];
    foreach ($lessons as $lesson) {
        $color = '#3B82F6'; // blue
        if ($lesson['status'] === 'completed') $color = '#10B981'; // green
        if ($lesson['status'] === 'cancelled') $color = '#EF4444'; // red

        // Получаем список студентов для групповых занятий
        $students = [];
        if ($lesson['group_id']) {
            $students = db()->fetchAll(
                "SELECT name FROM students WHERE group_id = ? AND status = 'active' ORDER BY name",
                [$lesson['group_id']]
            );
            $students = array_column($students, 'name');
        } elseif ($lesson['student_id']) {
            $students = [$lesson['student_name']];
        }

        $events[] = [
            'id' => $lesson['id'],
            'title' => $lesson['title'],
            'start' => $lesson['start'],
            'end' => $lesson['end'],
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'student_id' => $lesson['student_id'],
                'group_id' => $lesson['group_id'],
                'teacher_id' => $lesson['teacher_id'],
                'room_id' => $lesson['room_id'],
                'teacher_name' => $lesson['teacher_name'],
                'room_name' => $lesson['room_name'],
                'status' => $lesson['status'],
                'duration' => $lesson['duration'],
                'notes' => $lesson['notes'],
                'students' => $students
            ]
        ];
    }
    
    jsonResponse(['success' => true, 'data' => $events]);
}

function createLesson($data) {
    $errors = validate($data, [
        'teacher_id' => ['required', 'numeric'],
        'room_id' => ['required', 'numeric'],
        'lesson_date' => ['required'],
        'lesson_time' => ['required']
    ]);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }
    
    if (empty($data['student_id']) && empty($data['group_id'])) {
        jsonResponse(['error' => 'Укажите ученика или группу'], 400);
    }
    
    $sql = "INSERT INTO lessons (student_id, group_id, teacher_id, room_id, lesson_date, lesson_time, duration, status, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    db()->query($sql, [
        $data['student_id'] ?? null,
        $data['group_id'] ?? null,
        $data['teacher_id'],
        $data['room_id'],
        $data['lesson_date'],
        $data['lesson_time'],
        $data['duration'] ?? 60,
        $data['status'] ?? 'scheduled',
        $data['notes'] ?? null
    ]);
    
    $id = db()->lastInsertId();
    
    // Обновить дату последнего урока для ученика
    if (!empty($data['student_id'])) {
        db()->query("UPDATE students SET last_lesson_date = ? WHERE id = ?", [
            $data['lesson_date'],
            $data['student_id']
        ]);
    }
    
    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Урок успешно создан'], 201);
}

function updateLesson($data) {
    if (!isset($data['id'])) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    $fields = [];
    $params = [];
    
    $allowedFields = ['student_id', 'group_id', 'teacher_id', 'room_id', 'lesson_date', 'lesson_time', 'duration', 'status', 'notes'];
    
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
    $sql = "UPDATE lessons SET " . implode(', ', $fields) . " WHERE id = ?";
    
    db()->query($sql, $params);
    
    jsonResponse(['success' => true, 'message' => 'Урок успешно обновлен']);
}

function deleteLesson($id) {
    if (!$id) {
        jsonResponse(['error' => 'ID не указан'], 400);
    }
    
    db()->query("DELETE FROM lessons WHERE id = ?", [$id]);
    
    jsonResponse(['success' => true, 'message' => 'Урок успешно удален']);
}