<?php
/**
 * Discora - Customer Order Invoice & Details Page
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/functions.php';

require_login('login.php');

$order_id = intval($_GET['id'] ?? 0);
$user_id = $_SESSION['user_id'];
$db = get_db_connection();

// Fetch order
$stmt = $db->prepare("SELECT order_id, order_status, total_amount, order_date FROM orders WHERE order_id = ? AND user_id = ? LIMIT 1");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found or access denied.");
}

// Fetch order items
$item_stmt = $db->prepare("
    SELECT oi.quantity, oi.unit_price, oi.subtotal, p.product_name, pl.platform_name, pl.brand 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN platforms pl ON p.platform_id = pl.platform_id
    WHERE oi.order_id = ?
");
$item_stmt->execute([$order_id]);
$items = $item_stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Order Invoice #" . htmlspecialchars($order['order_id']);
require_once __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background-color: #ffffff !important;
        color: #111827 !important;
    }
</style>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="text-dark fw-bold mb-0">Order Invoice #ORD-<?= htmlspecialchars($order['order_id']) ?></h2>
            <span class="text-muted small">Placed on <?= date('F j, Y', strtotime($order['order_date'])) ?></span>
            <span class="badge rounded-pill bg-secondary ms-2"><?= htmlspecialchars($order['order_status']) ?></span>
        </div>
        <a href="<?= BASE_URL ?>orders.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3"><i class="bi bi-arrow-left me-1"></i> Back to Orders</a>
    </div>

    <div class="card bg-white shadow-sm border-0 p-4 rounded-3 mb-4">
        <h5 class="text-dark mb-4">Items in this Order</h5>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Item</th>
                        <th>Platform</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                            $badgeClass = match($item['brand']) {
                                'PlayStation' => 'bg-primary',
                                'Xbox' => 'bg-success',
                                'Nintendo' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($item['product_name']) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($item['platform_name']) ?></span></td>
                            <td><?= format_price($item['unit_price']) ?></td>
                            <td><?= intval($item['quantity']) ?></td>
                            <td class="fw-bold"><?= format_price($item['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-end mt-4">
            <div class="text-end">
                <p class="text-muted mb-1">Total Amount</p>
                <h3 class="fw-bold text-dark mb-0"><?= format_price($order['total_amount']) ?></h3>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
