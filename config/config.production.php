<?php
// Production Configuration for Hostinger
// Copy this to config/config.php after deployment

// Simple .env parser
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0)
            continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            putenv(trim($name) . '=' . trim($value));
            $_ENV[trim($name)] = trim($value);
        }
    }
}

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'u313315785_account');
define('DB_PASS', getenv('DB_PASS') ?: 'Mahadev@9870');
define('DB_NAME', getenv('DB_NAME') ?: 'u313315785_account');

// App Root
define('APP_ROOT', dirname(dirname(__FILE__)));

// URL Root - IMPORTANT: Update this to your domain
define('APP_URL', 'https://kamglobalai.com');

// Site Name
define('SITE_NAME', 'AccuBooks - Accounting Software');

// App Version
define('APP_VERSION', '1.0.0');

// Error Reporting - PRODUCTION MODE
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Log errors to file instead
ini_set('log_errors', 1);
ini_set('error_log', APP_ROOT . '/storage/logs/error.log');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // HTTPS only
ini_set('session.use_strict_mode', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>