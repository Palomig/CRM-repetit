<?php
// Конфигурация базы данных (только если еще не определены)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'cw95865_rmtutori');
if (!defined('DB_USER')) define('DB_USER', 'cw95865_rmtutori');
if (!defined('DB_PASS')) define('DB_PASS', '123456789');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Настройки приложения
if (!defined('SITE_URL')) define('SITE_URL', 'https://cw95865.tmweb.ru');
if (!defined('SITE_NAME')) define('SITE_NAME', 'CRM Репетиторский Центр');

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Настройки сессии
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Установить в 1 для HTTPS

// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Обработка ошибок
error_reporting(E_ALL);
ini_set('display_errors', 0); // Отключить на продакшене
ini_set('log_errors', 1);
ini_set('error_log', '/home/c/cw95865/error.log');

// Заголовки безопасности
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');