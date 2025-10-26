<?php
// Note: db.php should be loaded before this file
// Functions assume database connection is available via db()

// Функция для безопасного вывода данных
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Функция для форматирования даты
function formatDate($date, $format = 'd.m.Y') {
    if (!$date) return '';
    return date($format, strtotime($date));
}

// Функция для форматирования времени
function formatTime($time, $format = 'H:i') {
    if (!$time) return '';
    return date($format, strtotime($time));
}

// Функция для форматирования суммы
function formatMoney($amount) {
    return number_format($amount, 0, '.', ' ') . ' ₽';
}

// Функция для получения текущей даты
function getCurrentDate() {
    return date('Y-m-d');
}

// Функция для получения названия дня недели на русском
function getRussianDayName($englishDay) {
    $days = [
        'Monday' => 'Понедельник',
        'Tuesday' => 'Вторник',
        'Wednesday' => 'Среда',
        'Thursday' => 'Четверг',
        'Friday' => 'Пятница',
        'Saturday' => 'Суббота',
        'Sunday' => 'Воскресенье'
    ];
    return $days[$englishDay] ?? $englishDay;
}

// Функция для отправки JSON ответа
function jsonResponse($data, $statusCode = 200) {
    // Clear any output buffer
    if (ob_get_level() > 0) {
        ob_clean();
    }

    http_response_code($statusCode);

    // Only set header if not already set
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Функция для валидации данных
function validate($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? null;
        
        foreach ($fieldRules as $rule) {
            if ($rule === 'required' && empty($value)) {
                $errors[$field] = "Поле обязательно для заполнения";
                break;
            }

            if ($rule === 'email' && !empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Некорректный email";
                break;
            }

            if ($rule === 'numeric' && !empty($value) && !is_numeric($value)) {
                $errors[$field] = "Значение должно быть числом";
                break;
            }
        }
    }
    
    return $errors;
}

// Функция для получения количества учеников в группе
function getGroupStudentsCount($groupId) {
    $count = db()->fetchOne(
        "SELECT COUNT(*) as count FROM students WHERE group_id = ? AND status = 'active'",
        [$groupId]
    );
    return $count['count'] ?? 0;
}

// Функция для получения статистики по ученикам
function getStudentsStats() {
    $stats = db()->fetchOne("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'paused' THEN 1 ELSE 0 END) as paused,
            SUM(CASE WHEN type = 'individual' THEN 1 ELSE 0 END) as individual,
            SUM(CASE WHEN type = 'group' THEN 1 ELSE 0 END) as `group`
        FROM students
    ");
    return $stats;
}

// Функция для получения финансовой статистики за месяц
function getFinanceStats($month = null, $year = null) {
    if (!$month) $month = date('m');
    if (!$year) $year = date('Y');
    
    $stats = db()->fetchOne("
        SELECT 
            SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
            SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
        FROM finance
        WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?
    ", [$month, $year]);
    
    $stats['profit'] = ($stats['income'] ?? 0) - ($stats['expense'] ?? 0);
    return $stats;
}

// Функция для получения задач
function getTasks($status = null, $limit = null) {
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
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    return db()->fetchAll($sql, $params);
}

// Функция для генерации ссылки WhatsApp
function getWhatsAppLink($phone) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    return "https://wa.me/" . $cleanPhone;
}

// Функция для генерации ссылки Telegram
function getTelegramLink($username) {
    $cleanUsername = ltrim($username, '@');
    return "https://t.me/" . $cleanUsername;
}