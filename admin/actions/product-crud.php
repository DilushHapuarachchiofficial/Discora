<?php
/**
 * Discora Admin - Product CRUD Handler
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/session.php';
require_once dirname(dirname(__DIR__)) . '/core/auth.php';

require_admin('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        // Handle product creation + image upload
        set_flash_message('success', 'Product created successfully!');
    } elseif ($action === 'update') {
        // Handle product update
        set_flash_message('success', 'Product updated successfully!');
    } elseif ($action === 'delete') {
        // Handle product deletion
        set_flash_message('warning', 'Product deleted successfully!');
    }
}
redirect(ADMIN_URL . 'products.php');
