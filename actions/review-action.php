<?php
/**
 * Discora - Handle Review Submissions
 */
require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/functions.php';
require_once dirname(__DIR__) . '/core/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'require_auth' => true, 'message' => 'Please log in to submit a review.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $rating = (int)($_POST['rating'] ?? 0);
    $review_text = sanitize_input($_POST['review_text'] ?? '');

    if ($product_id <= 0 || $rating < 1 || $rating > 5) {
        echo json_encode(['success' => false, 'message' => 'Invalid rating or product.']);
        exit;
    }

    $db = Database::getConnection();

    try {
        // Check if review already exists
        $stmt = $db->prepare("SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Update existing review
            $update = $db->prepare("UPDATE reviews SET rating = ?, review_text = ?, review_status = 'Approved', updated_at = NOW() WHERE review_id = ?");
            $update->execute([$rating, $review_text, $existing['review_id']]);
            echo json_encode(['success' => true, 'message' => 'Your review has been updated!']);
        } else {
            // Insert new review
            $insert = $db->prepare("INSERT INTO reviews (user_id, product_id, rating, review_text, review_status) VALUES (?, ?, ?, ?, 'Approved')");
            $insert->execute([$user_id, $product_id, $rating, $review_text]);
            echo json_encode(['success' => true, 'message' => 'Thank you! Your review has been submitted.']);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error submitting review: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
