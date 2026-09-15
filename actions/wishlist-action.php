<?php
/**
 * Discora - Handle Wishlist Toggling (AJAX)
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to manage your wishlist.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? 'toggle'; // 'toggle', 'add', 'remove'

    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product.']);
        exit;
    }

    $db = Database::getConnection();

    try {
        // Get or create wishlist for user
        $wStmt = $db->prepare("SELECT wishlist_id FROM wishlists WHERE user_id = ?");
        $wStmt->execute([$user_id]);
        $wishlist = $wStmt->fetch(PDO::FETCH_ASSOC);

        if (!$wishlist) {
            $db->prepare("INSERT INTO wishlists (user_id) VALUES (?)")->execute([$user_id]);
            $wishlist_id = $db->lastInsertId();
        } else {
            $wishlist_id = $wishlist['wishlist_id'];
        }

        // Check if item already in wishlist
        $cStmt = $db->prepare("SELECT wishlist_item_id FROM wishlist_items WHERE wishlist_id = ? AND product_id = ?");
        $cStmt->execute([$wishlist_id, $product_id]);
        $item = $cStmt->fetch(PDO::FETCH_ASSOC);

        if ($action === 'toggle') {
            $action = $item ? 'remove' : 'add';
        }

        if ($action === 'add' && !$item) {
            $db->prepare("INSERT INTO wishlist_items (wishlist_id, product_id) VALUES (?, ?)")->execute([$wishlist_id, $product_id]);
            echo json_encode(['success' => true, 'action' => 'added', 'message' => 'Added to wishlist!']);
        } elseif ($action === 'remove' && $item) {
            $db->prepare("DELETE FROM wishlist_items WHERE wishlist_item_id = ?")->execute([$item['wishlist_item_id']]);
            echo json_encode(['success' => true, 'action' => 'removed', 'message' => 'Removed from wishlist!']);
        } else {
            echo json_encode(['success' => true, 'action' => 'none', 'message' => 'No changes made.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
