<?php
/**
 * API Configuration
 * Lightweight config for API endpoints without session and HTML headers
 */

// Database configuration (only define if not already defined)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'cw95865_rmtutori');
if (!defined('DB_USER')) define('DB_USER', 'cw95865_rmtutori');
if (!defined('DB_PASS')) define('DB_PASS', '123456789');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

// Application settings
if (!defined('SITE_URL')) define('SITE_URL', 'https://cw95865.tmweb.ru');
if (!defined('SITE_NAME')) define('SITE_NAME', 'CRM Репетиторский Центр');

// Timezone
date_default_timezone_set('Europe/Moscow');

// Error handling - log errors but don't display them as HTML (breaks JSON)
error_reporting(E_ALL);
ini_set('display_errors', 1); // TEMPORARY: Enable to see errors for debugging
ini_set('log_errors', 1);
ini_set('error_log', '/home/c/cw95865/error.log');

// TEMPORARILY DISABLED - this was converting warnings to exceptions
// Convert PHP errors to exceptions so we can catch them
/*set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});*/

// Don't start session for API endpoints
// Don't set HTML security headers for API endpoints
