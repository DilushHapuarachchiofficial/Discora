<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('orders');
$admin_title = "Order Details";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Order ID.");
}
$order_id = (int)$_GET['id'];

$error = '';
$success = '';

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $new_status = $_POST['order_status'];
        $valid_statuses = ['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'];
        
        if (in_array($new_status, $valid_statuses)) {
            try {
                $db->beginTransaction();
                
                // Get current status
                $curr_stmt = $db->prepare("SELECT order_status FROM orders WHERE order_id = ?");
                $curr_stmt->execute([$order_id]);
                $current_status = $curr_stmt->fetchColumn();

                // Update status
                $upd = $db->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
                $upd->execute([$new_status, $order_id]);

                // Handle Stock on Cancellation (If cancelled now, and wasn't cancelled before)
                if ($new_status === 'Cancelled' && $current_status !== 'Cancelled') {
                    $items_stmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    $items_stmt->execute([$order_id]);
                    $items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $restore_stmt = $db->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                    foreach ($items as $item) {
                        $restore_stmt->execute([$item['quantity'], $item['product_id']]);
                    }
                }

                $db->commit();
                $success = "Order status updated to $new_status.";
            } catch (PDOException $e) {
                $db->rollBack();
                $error = "Error updating order: " . $e->getMessage();
            }
        }
    }
}

// Fetch Order Info
$stmt = $db->prepare("
    SELECT o.*, u.full_name, u.email, u.phone as user_phone
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Order not found.");
}

// Fetch Order Items
$items_stmt = $db->prepare("
    SELECT oi.*, p.product_name, p.slug,
           (SELECT image_path FROM product_images WHERE product_id = p.product_id ORDER BY is_primary DESC LIMIT 1) as image_path
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$order_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch Payment Info
$pay_stmt = $db->prepare("SELECT * FROM payments WHERE order_id = ?");
$pay_stmt->execute([$order_id]);
$payment = $pay_stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Main Order Details -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header bg-white p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1">Order #<?= htmlspecialchars($order['order_number']) ?></h5>
                    <span class="text-secondary small"><?= date('F j, Y, g:i a', strtotime($order['order_date'])) ?></span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <?php
                    $badge = match($order['order_status']) {
                        'Pending' => 'bg-warning text-dark',
                        'Processing' => 'bg-info text-dark',
                        'Shipped' => 'bg-primary',
                        'Delivered' => 'bg-success',
                        'Cancelled' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?= $badge ?> fs-6 px-3 py-2"><?= $order['order_status'] ?></span>
                    
                    <?php if (in_array($order['order_status'], ['Processing', 'Shipped', 'Delivered'])): ?>
                    <a href="invoice.php?order_id=<?= $order['order_id'] ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Invoice
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card-body p-0">
                <table class="table mb-0 align-middle">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th class="ps-4">Product</th>
                            <th class="text-center">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end pe-4">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $item): ?>
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center gap-3">
                                    <?php if ($item['image_path']): ?>
                                        <img src="../<?= htmlspecialchars($item['image_path']) ?>" alt="" style="width: 50px; height: 60px; object-fit: contain;" class="bg-light rounded p-1">
                                    <?php else: ?>
                                        <div class="bg-light rounded p-3 text-muted"><i class="bi bi-image"></i></div>
                                    <?php endif; ?>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($item['product_name']) ?></div>
                                </div>
                            </td>
                            <td class="text-center">Rs. <?= number_format($item['unit_price'], 2) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td class="text-end pe-4 fw-bold">Rs. <?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white p-4">
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="d-flex justify-content-between mb-2 text-secondary">
                            <span>Subtotal</span>
                            <span>Rs. <?= number_format($order['subtotal'], 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-secondary">
                            <span>Shipping</span>
                            <span>Rs. <?= number_format($order['shipping_fee'], 2) ?></span>
                        </div>
                        <?php if ($order['discount'] > 0): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Discount</span>
                            <span>-Rs. <?= number_format($order['discount'], 2) ?></span>
                        </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total</span>
                            <span class="text-primary">Rs. <?= number_format($order['total_amount'], 2) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-lg-4">
        <!-- Status Update -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Update Status</h6></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="update_status">
                    <div class="input-group">
                        <select name="order_status" class="form-select">
                            <?php foreach (['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st): ?>
                                <option value="<?= $st ?>" <?= $order['order_status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-primary" type="submit">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Customer & Shipping -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Customer & Delivery</h6></div>
            <div class="card-body">
                <h6 class="fw-bold small text-secondary mb-2">Customer</h6>
                <div class="mb-4">
                    <div><?= htmlspecialchars($order['full_name']) ?></div>
                    <div><a href="mailto:<?= htmlspecialchars($order['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($order['email']) ?></a></div>
                    <div><?= htmlspecialchars($order['user_phone']) ?></div>
                </div>

                <h6 class="fw-bold small text-secondary mb-2">Shipping Address</h6>
                <div class="mb-4">
                    <div><strong><?= htmlspecialchars($order['shipping_name']) ?></strong></div>
                    <div><?= htmlspecialchars($order['shipping_address']) ?></div>
                    <div><?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_postal_code']) ?></div>
                    <div><?= htmlspecialchars($order['shipping_country']) ?></div>
                    <div>Phone: <?= htmlspecialchars($order['shipping_phone']) ?></div>
                </div>
                
                <?php if ($order['notes']): ?>
                <h6 class="fw-bold small text-secondary mb-2">Order Notes</h6>
                <p class="mb-0 bg-light p-2 rounded small text-muted"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Info -->
        <?php if ($payment): ?>
        <div class="card mb-4">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Payment Details</h6></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small fw-bold">Method:</span>
                    <?php 
                        $p_meth = $payment['payment_method'];
                        $meth_display = ($p_meth === 'cod') ? 'COD' : (($p_meth === 'card') ? 'CARD' : strtoupper($p_meth));
                    ?>
                    <span class="badge bg-light text-dark border"><i class="bi <?= $p_meth === 'cod' ? 'bi-cash' : 'bi-credit-card' ?> me-1"></i><?= htmlspecialchars($meth_display) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-secondary small fw-bold">Status:</span>
                    <?php 
                    $p_stat = $payment['payment_status'];
                    $p_badge = match($p_stat) {
                        'Completed' => 'text-success',
                        'Pending' => 'text-warning',
                        'Failed' => 'text-danger',
                        'Refunded' => 'text-secondary',
                        default => 'text-secondary'
                    };
                    ?>
                    <span class="<?= $p_badge ?> fw-bold"><?= $p_stat ?></span>
                </div>
                <?php if ($payment['transaction_reference']): ?>
                <div class="d-flex justify-content-between">
                    <span class="text-secondary small fw-bold">Ref:</span>
                    <span class="font-monospace small"><?= htmlspecialchars($payment['transaction_reference']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
