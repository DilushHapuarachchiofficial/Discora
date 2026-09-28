<?php
/**
 * Discora - Order Placement and Checkout Processing Endpoint
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/functions.php';
require_once dirname(__DIR__) . '/core/auth.php';

require_login('login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $shipping_name = sanitize_input($_POST['name'] ?? '');
    $shipping_phone = sanitize_input($_POST['phone'] ?? '');
    $shipping_address = sanitize_input($_POST['address'] ?? '');
    $shipping_city = sanitize_input($_POST['city'] ?? '');
    $shipping_postal_code = sanitize_input($_POST['postal_code'] ?? '');
    $raw_payment_method = sanitize_input($_POST['payment_method'] ?? 'card');
    
    $payment_method = ($raw_payment_method === 'cod') ? 'Cash on Delivery' : 'Credit/Debit Card';

    if (empty($shipping_address) || empty($shipping_city) || empty($shipping_name)) {
        set_flash_message('danger', 'Shipping address and name are required.');
        redirect(BASE_URL . 'checkout.php');
    }

    $db = Database::getConnection();

    try {
        $db->beginTransaction();

        // 1. Get Cart Items
        $cartStmt = $db->prepare("
            SELECT ci.cart_item_id, ci.product_id, ci.quantity, p.price, p.stock_quantity
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.cart_id
            JOIN products p ON ci.product_id = p.product_id
            WHERE c.user_id = ?
        ");
        $cartStmt->execute([$user_id]);
        $cart_items = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($cart_items)) {
            throw new Exception("Your cart is empty.");
        }

        // 2. Calculate Total & Check Stock
        $total_amount = 0;
        foreach ($cart_items as $item) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception("Insufficient stock for product ID: " . $item['product_id']);
            }
            $total_amount += ($item['price'] * $item['quantity']);
        }

        // 3. Create Order
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $orderStmt = $db->prepare("
            INSERT INTO orders (user_id, order_number, subtotal, total_amount, order_status, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_postal_code) 
            VALUES (?, ?, ?, ?, 'Pending', ?, ?, ?, ?, ?)
        ");
        $orderStmt->execute([$user_id, $order_number, $total_amount, $total_amount, $shipping_name, $shipping_phone, $shipping_address, $shipping_city, $shipping_postal_code]);
        $order_id = $db->lastInsertId();

        // 4. Insert Order Items & Reduce Stock
        $itemInsertStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stockUpdateStmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");

        foreach ($cart_items as $item) {
            $item_subtotal = $item['quantity'] * $item['price'];
            $itemInsertStmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price'], $item_subtotal]);
            $stockUpdateStmt->execute([$item['quantity'], $item['product_id']]);
        }

        // 5. Insert Payment Record
        $payStmt = $db->prepare("INSERT INTO payments (order_id, payment_method, amount, payment_status) VALUES (?, ?, ?, 'Pending')");
        $payStmt->execute([$order_id, $payment_method, $total_amount]);

        // 6. Clear Cart
        $clearCartStmt = $db->prepare("DELETE ci FROM cart_items ci JOIN carts c ON ci.cart_id = c.cart_id WHERE c.user_id = ?");
        $clearCartStmt->execute([$user_id]);

        // 7. Add Admin Notification
        $notifStmt = $db->prepare("INSERT INTO admin_notifications (type, reference_id, message) VALUES ('New Order', ?, ?)");
        $notifStmt->execute([$order_id, "New order $order_number placed by user."]);

        $db->commit();

        set_flash_message('success', 'Your order has been placed successfully!');
        redirect(BASE_URL . 'order-success.php?id=' . $order_id);

    } catch (Exception $e) {
        $db->rollBack();
        set_flash_message('danger', 'Order failed: ' . $e->getMessage());
        redirect(BASE_URL . 'checkout.php');
    }
} else {
    redirect(BASE_URL . 'checkout.php');
}
