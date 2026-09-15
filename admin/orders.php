<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('orders');
$admin_title = "Manage Orders";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';

$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(o.order_number LIKE :search OR u.full_name LIKE :search_name)";
    $params['search'] = "%$search%";
    $params['search_name'] = "%$search%";
}
if ($status) {
    $where_clauses[] = "o.order_status = :status";
    $params['status'] = $status;
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$count_stmt = $db->prepare("SELECT COUNT(*) FROM orders o JOIN users u ON o.user_id = u.user_id $where_sql");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$sql = "
    SELECT o.order_id, o.order_number, o.order_date, o.total_amount, o.order_status, 
           u.full_name as customer_name,
           (SELECT payment_status FROM payments WHERE order_id = o.order_id LIMIT 1) as payment_status,
           (SELECT payment_method FROM payments WHERE order_id = o.order_id LIMIT 1) as payment_method
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    $where_sql
    ORDER BY o.order_date DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header bg-white p-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <form method="GET" action="orders.php" class="d-flex gap-2 flex-grow-1" style="max-width: 600px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search order # or customer..." value="<?= htmlspecialchars($search) ?>">
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">All Statuses</option>
                <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Processing" <?= $status === 'Processing' ? 'selected' : '' ?>>Processing</option>
                <option value="Shipped" <?= $status === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="Delivered" <?= $status === 'Delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn btn-sm btn-dark px-3">Filter</button>
            <?php if ($search || $status): ?>
                <a href="orders.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">Order #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Order Status</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                <tr><td colspan="7" class="text-center py-5 text-muted">No orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td class="ps-4 fw-bold">#<?= htmlspecialchars($o['order_number']) ?></td>
                        <td><?= htmlspecialchars($o['customer_name']) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($o['order_date'])) ?></td>
                        <td class="fw-bold">Rs. <?= number_format($o['total_amount'], 2) ?></td>
                        <td>
                            <?php 
                            $p_stat = $o['payment_status'] ?? 'Pending';
                            $p_meth = $o['payment_method'] ?? 'Unknown';
                            
                            $p_badge = match($p_stat) {
                                'Completed' => 'text-success',
                                'Pending' => 'text-warning',
                                'Failed' => 'text-danger',
                                'Refunded' => 'text-secondary',
                                default => 'text-secondary'
                            };
                            
                            // Format payment method text
                            $meth_display = ($p_meth === 'cod') ? 'COD' : (($p_meth === 'card') ? 'CARD' : strtoupper($p_meth));
                            ?>
                            <div class="d-flex flex-column">
                                <span class="small fw-bold <?= $p_badge ?>"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem"></i><?= $p_stat ?></span>
                                <span class="badge bg-light text-dark border mt-1" style="width: fit-content;"><i class="bi <?= $p_meth === 'cod' ? 'bi-cash' : 'bi-credit-card' ?> me-1"></i><?= htmlspecialchars($meth_display) ?></span>
                            </div>
                        </td>
                        <td>
                            <?php
                            $badge = match($o['order_status']) {
                                'Pending' => 'bg-warning text-dark',
                                'Processing' => 'bg-info text-dark',
                                'Shipped' => 'bg-primary',
                                'Delivered' => 'bg-success',
                                'Cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= $o['order_status'] ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="order-details.php?id=<?= $o['order_id'] ?>" class="btn btn-sm btn-light border">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($total_pages > 1): ?>
    <div class="card-footer bg-white p-3 d-flex justify-content-center">
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <?php
                $q = $_GET;
                for ($i = 1; $i <= $total_pages; $i++) {
                    $q['page'] = $i;
                    $url = '?' . http_build_query($q);
                    $active = $i === $page ? 'active' : '';
                    echo "<li class='page-item $active'><a class='page-link' href='$url'>$i</a></li>";
                }
                ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
