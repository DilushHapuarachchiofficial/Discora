<?php
/**
 * Discora - Authentication Guards and User Session Management
 */

require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/functions.php';

/**
 * Check if a user is currently logged in
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the currently logged in user is an Admin
 */
function is_admin(): bool {
    return is_logged_in() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current authenticated user details from session
 */
function current_user(): ?array {
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'] ?? null,
        'name'  => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'role'  => $_SESSION['user_role'] ?? 'customer'
    ];
}

/**
 * Guard: Require logged in user for protected pages
 */
function require_login(string $redirect_to = 'login.php'): void {
    if (!is_logged_in()) {
        set_flash_message('warning', 'Please login to access that page.');
        redirect(BASE_URL . $redirect_to);
    }
}

/**
 * Guard: Require admin role for admin pages
 */
function require_admin(string $redirect_to = 'login.php'): void {
    if (!is_admin()) {
        set_flash_message('danger', 'Access denied. Administrator privileges required.');
        redirect(BASE_URL . $redirect_to);
    }
}
