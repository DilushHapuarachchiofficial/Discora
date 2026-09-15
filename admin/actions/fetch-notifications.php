<?php
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/config/db.php';

header('Content-Type: application/json');

try {
    $db = get_db_connection();
    
    // Check if the request is to mark notifications as read
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_read') {
        $stmt = $db->prepare("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit;
    }

    // Fetch unread notifications
    $stmt = $db->prepare("SELECT notification_id, type, reference_id, message, created_at 
                          FROM admin_notifications 
                          WHERE is_read = 0 
                          ORDER BY created_at DESC 
                          LIMIT 10");
    $stmt->execute();
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($notifications),
        'notifications' => $notifications
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
