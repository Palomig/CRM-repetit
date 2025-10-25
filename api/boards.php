<?php
/**
 * Boards API
 * Handles CRUD operations for kanban boards
 */

// API endpoint - set JSON header before any output
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../includes/api_config.php';
require_once __DIR__ . '/../includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = db();

try {
    switch ($method) {
        case 'GET':
            // Get all boards or a specific board
            if (isset($_GET['id'])) {
                $board = $db->fetchOne(
                    'SELECT * FROM boards WHERE id = ? ORDER BY position',
                    [$_GET['id']]
                );

                if (!$board) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Доска не найдена']);
                    exit;
                }

                echo json_encode(['success' => true, 'data' => $board]);
            } else {
                $boards = $db->fetchAll('SELECT * FROM boards ORDER BY position, id');
                echo json_encode(['success' => true, 'data' => $boards]);
            }
            break;

        case 'POST':
            // Create new board
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['name'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Название доски обязательно']);
                exit;
            }

            // Get max position
            $maxPosition = $db->fetchOne('SELECT MAX(position) as max_pos FROM boards');
            $position = ($maxPosition['max_pos'] ?? -1) + 1;

            $db->query(
                'INSERT INTO boards (name, description, position) VALUES (?, ?, ?)',
                [
                    $data['name'],
                    $data['description'] ?? null,
                    $position
                ]
            );

            $boardId = $db->lastInsertId();

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'id' => $boardId,
                'message' => 'Доска успешно создана'
            ]);
            break;

        case 'PUT':
            // Update board
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID доски обязателен']);
                exit;
            }

            // Build update query dynamically
            $updates = [];
            $params = [];

            if (isset($data['name'])) {
                $updates[] = 'name = ?';
                $params[] = $data['name'];
            }
            if (isset($data['description'])) {
                $updates[] = 'description = ?';
                $params[] = $data['description'];
            }
            if (isset($data['position'])) {
                $updates[] = 'position = ?';
                $params[] = $data['position'];
            }

            if (empty($updates)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Нет данных для обновления']);
                exit;
            }

            $params[] = $data['id'];
            $sql = 'UPDATE boards SET ' . implode(', ', $updates) . ' WHERE id = ?';

            $db->query($sql, $params);

            echo json_encode(['success' => true, 'message' => 'Доска успешно обновлена']);
            break;

        case 'DELETE':
            // Delete board
            $data = json_decode(file_get_contents('php://input'), true);

            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID доски обязателен']);
                exit;
            }

            // Check if there are tasks on this board
            $taskCount = $db->fetchOne(
                'SELECT COUNT(*) as count FROM tasks WHERE board_id = ?',
                [$data['id']]
            );

            if ($taskCount['count'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Невозможно удалить доску с задачами. Сначала переместите или удалите все задачи.'
                ]);
                exit;
            }

            $db->query('DELETE FROM boards WHERE id = ?', [$data['id']]);

            echo json_encode(['success' => true, 'message' => 'Доска успешно удалена']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Метод не поддерживается']);
            break;
    }
} catch (Exception $e) {
    error_log("Boards API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Произошла ошибка при выполнении операции'
    ]);
}
