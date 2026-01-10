<?php
/**
 * Session Security Middleware
 * Checks session timeout and validates session integrity
 */

function check_session_timeout()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Skip for login page
    if (strpos($_SERVER['REQUEST_URI'], '/auth/login') !== false) {
        return;
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    // Session timeout: 30 minutes (1800 seconds)
    $timeout_duration = 1800;

    // Check last activity
    if (isset($_SESSION['last_activity'])) {
        $elapsed_time = time() - $_SESSION['last_activity'];

        if ($elapsed_time > $timeout_duration) {
            // Session expired
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['timeout_message'] = 'Your session has expired due to inactivity. Please login again.';
            header('Location: ' . APP_URL . '/auth/login');
            exit();
        }
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();

    // Optional: Check if IP changed (security measure)
    if (isset($_SESSION['user_ip'])) {
        if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
            // IP changed - possible session hijacking
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['security_alert'] = 'Security alert: Session terminated due to IP change.';
            header('Location: ' . APP_URL . '/auth/login');
            exit();
        }
    }
}

// Call this function at the start of each request
check_session_timeout();
?>