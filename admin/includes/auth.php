<?php
/**
 * Admin Authentication Middleware
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ob_start(); // Prevent headers already sent errors when redirecting after form submissions
require_once dirname(__DIR__, 2) . '/config/constants.php';

function is_admin() {
    return isset($_SESSION['user_id']) && (
        (isset($_SESSION['role_id']) && $_SESSION['role_id'] == 1) || 
        (isset($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'admin')
    );
}

if (!is_admin()) {
    header("Location: " . BASE_URL);
    exit();
}

function is_superadmin() {
    return is_admin() && isset($_SESSION['is_superadmin']) && $_SESSION['is_superadmin'] == 1;
}

function has_permission($module) {
    if (is_superadmin()) {
        return true;
    }
    $permissions = $_SESSION['admin_permissions'] ?? [];
    return in_array($module, $permissions);
}

function require_permission($module) {
    if (!has_permission($module)) {
        header("Location: " . BASE_URL . "admin/index.php");
        exit();
    }
}
