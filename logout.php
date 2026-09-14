<?php
/**
 * Discora - Logout Handler
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/core/functions.php';

// Unset all session variables
$_SESSION = [];

// Destroy session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Start a fresh session for redirect flash message
session_start();
set_flash_message('info', 'You have been logged out successfully.');
redirect(BASE_URL . 'index.php');
