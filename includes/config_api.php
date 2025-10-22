<?php
// API Configuration - без сессий и HTTP заголовков
// Используется только для API endpoints

// Конфигурация базы данных
define('DB_HOST', 'localhost');
define('DB_NAME', 'cw95865_rmtutori');
define('DB_USER', 'cw95865_rmtutori');
define('DB_PASS', '123456789');
define('DB_CHARSET', 'utf8mb4');

// Настройки приложения
define('SITE_URL', 'https://cw95865.tmweb.ru');
define('SITE_NAME', 'CRM Репетиторский Центр');

// Часовой пояс
date_default_timezone_set('Europe/Moscow');

// Обработка ошибок (временно включен вывод для отладки)
error_reporting(E_ALL);
ini_set('display_errors', 1); // Временно включено для отладки
ini_set('log_errors', 1);
ini_set('error_log', '/home/c/cw95865/error.log');

// НЕ запускаем сессию для API
// НЕ устанавливаем заголовки безопасности (API сам установит нужные)
