<?php
/**
 * API Configuration
 * Lightweight config for API endpoints without session and HTML headers
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cw95865_rmtutori');
define('DB_USER', 'cw95865_rmtutori');
define('DB_PASS', '123456789');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('SITE_URL', 'https://cw95865.tmweb.ru');
define('SITE_NAME', 'CRM Репетиторский Центр');

// Timezone
date_default_timezone_set('Europe/Moscow');

// Error handling - log errors but don't display them as HTML (breaks JSON)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Never display errors in API (breaks JSON response)
ini_set('log_errors', 1);
ini_set('error_log', '/home/c/cw95865/error.log');

// Convert PHP errors to exceptions so we can catch them
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Don't start session for API endpoints
// Don't set HTML security headers for API endpoints
