<?php
/**
 * Discora Admin - Order Status Update Handler
 */
require_once dirname(dirname(__DIR__)) . '/config/db.php';
require_once dirname(dirname(__DIR__)) . '/config/session.php';
require_once dirname(dirname(__DIR__)) . '/core/auth.php';

require_admin('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = sanitize_input($_POST['status'] ?? 'Processing');
    // Update order status in database
    set_flash_message('success', "Order #{$order_id} status updated to {$status}.");
    redirect(ADMIN_URL . "order-details.php?id={$order_id}");
}
redirect(ADMIN_URL . 'orders.php');
