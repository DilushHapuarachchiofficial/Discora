<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('customers');
$admin_title = "Customer Details";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Customer ID.");
}
$user_id = (int)$_GET['id'];

// Fetch User
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND role_id = 2");
$stmt->execute([$user_id]);
$customer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$customer) {
    die("Customer not found.");
}

// Stats
$orders_stmt = $db->prepare("SELECT COUNT(*) as count, SUM(total_amount) as total_spent FROM orders WHERE user_id = ?");
$orders_stmt->execute([$user_id]);
$stats = $orders_stmt->fetch(PDO::FETCH_ASSOC);

// Recent Orders
$rec_stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC LIMIT 10");
$rec_stmt->execute([$user_id]);
$orders = $rec_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Customer Profile</h6></div>
            <div class="card-body text-center pt-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                    <?= strtoupper(substr($customer['full_name'], 0, 1)) ?>
                </div>
                <h5 class="fw-bold mb-1"><?= htmlspecialchars($customer['full_name']) ?></h5>
                <p class="text-secondary mb-3"><?= htmlspecialchars($customer['email']) ?></p>
                
                <?php
                $badge = match($customer['status']) {
                    'Active' => 'bg-success',
                    'Inactive' => 'bg-secondary',
                    'Suspended' => 'bg-danger',
                    default => 'bg-secondary'
                };
                ?>
                <span class="badge <?= $badge ?> px-3 py-2 mb-4"><?= $customer['status'] ?> Account</span>
                
                <hr>
                
                <div class="d-flex justify-content-between mb-2 text-start">
                    <span class="text-secondary fw-bold small">Phone</span>
                    <span><?= htmlspecialchars($customer['phone'] ?? '-') ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-start">
                    <span class="text-secondary fw-bold small">Joined</span>
                    <span><?= date('M d, Y', strtotime($customer['created_at'])) ?></span>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-6">
                <div class="card bg-light border-0">
                    <div class="card-body text-center p-3">
                        <h3 class="fw-bold text-primary mb-1"><?= $stats['count'] ?></h3>
                        <div class="small text-secondary fw-bold text-uppercase">Total Orders</div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="card bg-light border-0">
                    <div class="card-body text-center p-3">
                        <h3 class="fw-bold text-success mb-1">Rs.<?= number_format($stats['total_spent'] ?: 0, 0) ?></h3>
                        <div class="small text-secondary fw-bold text-uppercase">Total Spent</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Order History</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th class="ps-4">Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No orders placed yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($orders as $o): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?= htmlspecialchars($o['order_number']) ?></td>
                                <td><?= date('M d, Y', strtotime($o['order_date'])) ?></td>
                                <td class="fw-bold">Rs. <?= number_format($o['total_amount'], 2) ?></td>
                                <td>
                                    <?php
                                    $bdg = match($o['order_status']) {
                                        'Pending' => 'bg-warning text-dark',
                                        'Processing' => 'bg-info text-dark',
                                        'Shipped' => 'bg-primary',
                                        'Delivered' => 'bg-success',
                                        'Cancelled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?= $bdg ?>"><?= $o['order_status'] ?></span>
                                </td>
                                <td class="text-end pe-4">
                                    <a href="order-details.php?id=<?= $o['order_id'] ?>" class="btn btn-sm btn-light border">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
