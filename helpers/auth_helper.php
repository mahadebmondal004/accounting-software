<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function hasPermission($permission)
{
    if (!isLoggedIn()) {
        return false;
    }

    // Lazy load permissions if not in session
    if (!isset($_SESSION['permissions'])) {
        // We'd ideally need a database connection here, or we fetch it at login time.
        // Assuming login sets $_SESSION['permissions'] as an array
        return false;
    }

    // Check for super admin/all access
    if (isset($_SESSION['permissions']['all']) && $_SESSION['permissions']['all'] === true) {
        return true;
    }

    return isset($_SESSION['permissions'][$permission]) && $_SESSION['permissions'][$permission] === true;
}

function requirePermission($permission)
{
    if (!hasPermission($permission)) {
        header('Location: ' . APP_URL . '/dashboard/index'); // Or unauthorized page
        exit;
    }
}
