<?php
/**
 * Discora - Customer Wishlist Management Service
 * Connects with MySQL wishlists & wishlist_items tables
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/functions.php';

/**
 * Get or create Wishlist ID for user
 */
function get_or_create_wishlist_id(int $userId): int {
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = :uid LIMIT 1");
    $stmt->execute([':uid' => $userId]);
    $wishlistId = $stmt->fetchColumn();

    if (!$wishlistId) {
        $insert = $pdo->prepare("INSERT INTO wishlists (user_id) VALUES (:uid)");
        $insert->execute([':uid' => $userId]);
        $wishlistId = (int)$pdo->lastInsertId();
    }
    return (int)$wishlistId;
}

/**
 * Toggle product in user's wishlist (Add if not present, Remove if present)
 */
function toggle_product_wishlist(int $productId, int $userId): array {
    $pdo = Database::getConnection();
    $wishlistId = get_or_create_wishlist_id($userId);

    // Check if product already in wishlist
    $stmt = $pdo->prepare("SELECT wishlist_item_id FROM wishlist_items WHERE wishlist_id = :wid AND product_id = :pid LIMIT 1");
    $stmt->execute([':wid' => $wishlistId, ':pid' => $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $delStmt = $pdo->prepare("DELETE FROM wishlist_items WHERE wishlist_item_id = :item_id");
        $delStmt->execute([':item_id' => $existing['wishlist_item_id']]);
        return [
            'success' => true,
            'action'  => 'removed',
            'message' => 'Removed from your wishlist.'
        ];
    } else {
        $addStmt = $pdo->prepare("INSERT INTO wishlist_items (wishlist_id, product_id) VALUES (:wid, :pid)");
        $addStmt->execute([':wid' => $wishlistId, ':pid' => $productId]);
        return [
            'success' => true,
            'action'  => 'added',
            'message' => 'Added to your wishlist!'
        ];
    }
}

/**
 * Check if a product is in user's wishlist
 */
function is_product_in_wishlist(int $productId, ?int $userId): bool {
    if (!$userId) return false;
    $pdo = Database::getConnection();
    $stmt = $pdo->prepare("
        SELECT wi.wishlist_item_id 
        FROM wishlist_items wi 
        JOIN wishlists w ON wi.wishlist_id = w.wishlist_id 
        WHERE w.user_id = :uid AND wi.product_id = :pid 
        LIMIT 1
    ");
    $stmt->execute([':uid' => $userId, ':pid' => $productId]);
    return (bool)$stmt->fetchColumn();
}
