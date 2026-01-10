<?php
// public/index.php

// Error Reporting (Keep enabled for now, can be disabled in production later)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log errors to file
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

// Custom Session Handling to prevent session loss
$sessionPath = __DIR__ . '/../storage/sessions';
if (!file_exists($sessionPath)) {
    @mkdir($sessionPath, 0777, true);
}
session_save_path($sessionPath);
ini_set('session.gc_maxlifetime', 86400);

// Cookie Params - Simplified for Localhost
session_set_cookie_params(86400, '/');
session_name('ACCU_SESSION_ID');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Load Config
require_once '../config/config.php';

// Load Helpers
require_once '../helpers/csrf_helper.php';

// Session Security Middleware
require_once '../app/middleware/SessionSecurity.php';

// Autoload Core Libraries
spl_autoload_register(function ($className) {
    if (file_exists('../core/' . $className . '.php')) {
        require_once '../core/' . $className . '.php';
    }
});

// Init Core Library
try {
    $init = new Router();
} catch (Throwable $e) {
    // Graceful error page
    error_log("FATAL: " . $e->getMessage());
    echo "<div style='text-align:center; padding:50px; font-family:sans-serif;'>";
    echo "<h1>Temporarily Unavailable</h1>";
    echo "<p>The application encountered a critical error. Please check the logs.</p>";
    echo "<div style='background:#f8d7da; color:#721c24; padding:20px; margin:20px auto; max-width:800px; text-align:left; border-radius:5px;'>";
    echo "<strong>Error Details:</strong><br>";
    echo "<pre style='white-space:pre-wrap;'>" . htmlspecialchars($e->getMessage()) . "</pre>";
    echo "<pre style='white-space:pre-wrap; font-size:11px;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    echo "</div>";
}
