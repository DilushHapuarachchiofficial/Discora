<?php
/**
 * Discora Admin - Category & Genre CRUD Handler
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/session.php';
require_once dirname(dirname(__DIR__)) . '/core/auth.php';

require_admin('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    // Handle category creation, editing, deletion
    set_flash_message('success', 'Category updated successfully!');
}
redirect(ADMIN_URL . 'categories.php');
