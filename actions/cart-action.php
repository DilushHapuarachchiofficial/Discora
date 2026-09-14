<?php
/**
 * Discora - Shopping Cart AJAX Action Endpoint
 */

header('Content-Type: application/json; charset=utf-8');

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/cart.php';
require_once dirname(__DIR__) . '/core/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = sanitize_input($_POST['action'] ?? '');
    $productId = (int)($_POST['product_id'] ?? 0);
    $quantity  = max(1, (int)($_POST['quantity'] ?? 1));
    $itemId    = (int)($_POST['cart_item_id'] ?? 0);

    if ($action === 'add') {
        if ($productId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product selected.']);
            exit;
        }
        $result = add_product_to_cart($productId, $quantity);
        echo json_encode($result);
        exit;
    }

    if ($action === 'update') {
        if ($itemId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
            exit;
        }
        $result = update_cart_item_quantity($itemId, $quantity);
        $cart = get_current_cart_items();
        echo json_encode(array_merge($result, [
            'subtotal'       => format_price($cart['subtotal']),
            'total_quantity' => $cart['total_quantity']
        ]));
        exit;
    }

    if ($action === 'remove') {
        if ($itemId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
            exit;
        }
        remove_cart_item($itemId);
        $cart = get_current_cart_items();
        echo json_encode([
            'success'        => true,
            'message'        => 'Item removed from your cart.',
            'subtotal'       => format_price($cart['subtotal']),
            'total_quantity' => $cart['total_quantity']
        ]);
        exit;
    }
}

// GET cart count / data
$cart = get_current_cart_items();
echo json_encode([
    'success'        => true,
    'total_quantity' => $cart['total_quantity'],
    'subtotal'       => format_price($cart['subtotal']),
    'items'          => $cart['items']
]);
