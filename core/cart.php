<?php
/**
 * Discora - Shopping Cart Management Service
 * Handles session-based and MySQL-based cart operations with stock validation
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/functions.php';

/**
 * Get or create active cart ID for user/session
 */
function get_or_create_cart_id(): int {
    $pdo = Database::getConnection();
    $userId = $_SESSION['user_id'] ?? null;
    $sessionToken = session_id();

    if ($userId) {
        // Registered User Cart
        $stmt = $pdo->prepare("SELECT cart_id FROM carts WHERE user_id = :uid LIMIT 1");
        $stmt->execute([':uid' => $userId]);
        $cartId = $stmt->fetchColumn();

        if (!$cartId) {
            $insert = $pdo->prepare("INSERT INTO carts (user_id, session_token) VALUES (:uid, :token)");
            $insert->execute([':uid' => $userId, ':token' => $sessionToken]);
            $cartId = (int)$pdo->lastInsertId();
        }
        return (int)$cartId;
    } else {
        // Guest Cart by Session Token
        $stmt = $pdo->prepare("SELECT cart_id FROM carts WHERE session_token = :token AND user_id IS NULL LIMIT 1");
        $stmt->execute([':token' => $sessionToken]);
        $cartId = $stmt->fetchColumn();

        if (!$cartId) {
            $insert = $pdo->prepare("INSERT INTO carts (user_id, session_token) VALUES (NULL, :token)");
            $insert->execute([':token' => $sessionToken]);
            $cartId = (int)$pdo->lastInsertId();
        }
        return (int)$cartId;
    }
}

/**
 * Add product to cart with strict stock validation
 */
function add_product_to_cart(int $productId, int $quantity = 1): array {
    $pdo = Database::getConnection();

    // 1. Verify Product & Stock
    $stmt = $pdo->prepare("SELECT product_id, product_name, price, discount_price, stock_quantity, status FROM products WHERE product_id = :pid LIMIT 1");
    $stmt->execute([':pid' => $productId]);
    $product = $stmt->fetch();

    if (!$product || $product['status'] !== 'Active') {
        return ['success' => false, 'message' => 'Product is currently unavailable.'];
    }

    if ($product['stock_quantity'] <= 0) {
        return ['success' => false, 'message' => 'This product is currently out of stock.'];
    }

    $cartId = get_or_create_cart_id();

    // 2. Check if product already in cart
    $itemStmt = $pdo->prepare("SELECT cart_item_id, quantity FROM cart_items WHERE cart_id = :cid AND product_id = :pid LIMIT 1");
    $itemStmt->execute([':cid' => $cartId, ':pid' => $productId]);
    $existing = $itemStmt->fetch();

    $newQty = $existing ? ($existing['quantity'] + $quantity) : $quantity;

    if ($newQty > $product['stock_quantity']) {
        return [
            'success' => false, 
            'message' => 'Cannot add more than ' . $product['stock_quantity'] . ' items available in stock.'
        ];
    }

    if ($existing) {
        $updateStmt = $pdo->prepare("UPDATE cart_items SET quantity = :qty, updated_at = CURRENT_TIMESTAMP WHERE cart_item_id = :item_id");
        $updateStmt->execute([':qty' => $newQty, ':item_id' => $existing['cart_item_id']]);
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (:cid, :pid, :qty)");
        $insertStmt->execute([':cid' => $cartId, ':pid' => $productId, ':qty' => $newQty]);
    }

    // Return updated cart count
    $countStmt = $pdo->prepare("SELECT SUM(quantity) FROM cart_items WHERE cart_id = :cid");
    $countStmt->execute([':cid' => $cartId]);
    $totalCount = (int)$countStmt->fetchColumn();

    // Keep session mirror updated
    $_SESSION['cart_count'] = $totalCount;

    return [
        'success'    => true,
        'message'    => $product['product_name'] . ' added to your shopping cart!',
        'cart_count' => $totalCount,
        'product'    => $product
    ];
}

/**
 * Fetch all items in the current active cart
 */
function get_current_cart_items(): array {
    $pdo = Database::getConnection();
    $cartId = get_or_create_cart_id();

    $sql = "
        SELECT 
            ci.cart_item_id,
            ci.cart_id,
            ci.product_id,
            ci.quantity,
            pr.product_name,
            pr.slug,
            pr.price,
            pr.discount_price,
            COALESCE(pr.discount_price, pr.price) as effective_price,
            pr.stock_quantity,
            pl.platform_name,
            COALESCE(
                (SELECT img.image_path FROM product_images img WHERE img.product_id = pr.product_id ORDER BY img.is_primary DESC, img.display_order ASC LIMIT 1),
                'assets/images/products/placeholder.jpg'
            ) as image_path
        FROM cart_items ci
        JOIN products pr ON ci.product_id = pr.product_id
        JOIN platforms pl ON pr.platform_id = pl.platform_id
        WHERE ci.cart_id = :cid
        ORDER BY ci.created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':cid' => $cartId]);
    $items = $stmt->fetchAll();

    $subtotal = 0.00;
    $totalQuantity = 0;
    foreach ($items as &$item) {
        $item['subtotal'] = (float)$item['effective_price'] * (int)$item['quantity'];
        $subtotal += $item['subtotal'];
        $totalQuantity += (int)$item['quantity'];
    }

    $_SESSION['cart_count'] = $totalQuantity;

    return [
        'cart_id'        => $cartId,
        'items'          => $items,
        'subtotal'       => $subtotal,
        'total_quantity' => $totalQuantity
    ];
}

/**
 * Remove an item from cart
 */
function remove_cart_item(int $cartItemId): bool {
    $pdo = Database::getConnection();
    $cartId = get_or_create_cart_id();

    $stmt = $pdo->prepare("DELETE FROM cart_items WHERE cart_item_id = :item_id AND cart_id = :cid");
    return $stmt->execute([':item_id' => $cartItemId, ':cid' => $cartId]);
}

/**
 * Update quantity of item in cart
 */
function update_cart_item_quantity(int $cartItemId, int $newQuantity): array {
    $pdo = Database::getConnection();
    $cartId = get_or_create_cart_id();

    if ($newQuantity <= 0) {
        remove_cart_item($cartItemId);
        return ['success' => true, 'message' => 'Item removed from cart.'];
    }

    // Verify stock
    $stmt = $pdo->prepare("
        SELECT ci.cart_item_id, pr.stock_quantity, pr.product_name 
        FROM cart_items ci 
        JOIN products pr ON ci.product_id = pr.product_id 
        WHERE ci.cart_item_id = :item_id AND ci.cart_id = :cid 
        LIMIT 1
    ");
    $stmt->execute([':item_id' => $cartItemId, ':cid' => $cartId]);
    $row = $stmt->fetch();

    if (!$row) {
        return ['success' => false, 'message' => 'Item not found in your cart.'];
    }

    if ($newQuantity > $row['stock_quantity']) {
        return ['success' => false, 'message' => 'Maximum stock available is ' . $row['stock_quantity']];
    }

    $update = $pdo->prepare("UPDATE cart_items SET quantity = :qty, updated_at = CURRENT_TIMESTAMP WHERE cart_item_id = :item_id");
    $update->execute([':qty' => $newQuantity, ':item_id' => $cartItemId]);

    return ['success' => true, 'message' => 'Cart quantity updated.'];
}
