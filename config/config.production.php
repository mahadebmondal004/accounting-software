<?php
// Production Configuration for Hostinger
// Copy this to config/config.php after deployment

// Database Configuration
define('DB_HOST', 'localhost'); // Hostinger MySQL host
define('DB_USER', 'your_database_username'); // Replace with your Hostinger DB username
define('DB_PASS', 'your_database_password'); // Replace with your Hostinger DB password
define('DB_NAME', 'your_database_name'); // Replace with your Hostinger DB name

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