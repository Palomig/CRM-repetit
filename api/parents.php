<?php
require_once '../includes/config_api.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

try {
    switch ($method) {
        case 'POST':
            createParent($input);
            break;
            
        default:
            jsonResponse(['error' => 'Метод не поддерживается'], 405);
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    jsonResponse(['error' => 'Произошла ошибка: ' . $e->getMessage()], 500);
}

function createParent($data) {
    $errors = validate($data, [
        'name' => ['required'],
        'phone' => ['required']
    ]);
    
    if (!empty($errors)) {
        jsonResponse(['error' => 'Ошибка валидации', 'errors' => $errors], 400);
    }
    
    $sql = "INSERT INTO parents (name, phone, whatsapp, telegram) VALUES (?, ?, ?, ?)";
    
    db()->query($sql, [
        $data['name'],
        $data['phone'],
        $data['whatsapp'] ?? null,
        $data['telegram'] ?? null
    ]);
    
    $id = db()->lastInsertId();
    
    jsonResponse(['success' => true, 'id' => $id, 'message' => 'Родитель успешно добавлен'], 201);
}