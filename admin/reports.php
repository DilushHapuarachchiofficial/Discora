<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('reports');
$admin_title = "Reports & Analytics";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

// Basic Sales Report Data
$sales_by_status = $db->query("SELECT order_status, COUNT(*) as count, SUM(total_amount) as total FROM orders GROUP BY order_status")->fetchAll(PDO::FETCH_ASSOC);

$top_products = $db->query("
    SELECT p.product_name, SUM(oi.quantity) as qty_sold, SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    GROUP BY oi.product_id
    ORDER BY qty_sold DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$recent_sales = $db->query("
    SELECT DATE(order_date) as date, SUM(total_amount) as total_sales, COUNT(*) as order_count
    FROM orders
    WHERE order_status IN ('Completed', 'Delivered', 'Shipped', 'Processing')
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date) DESC
    LIMIT 30
")->fetchAll(PDO::FETCH_ASSOC);

$total_revenue = array_sum(array_column(array_filter($sales_by_status, fn($s) => in_array($s['order_status'], ['Completed', 'Delivered', 'Shipped', 'Processing'])), 'total'));
?>

<div class="row g-4">
    <!-- Summary Cards -->
    <div class="col-12">
        <div class="card bg-primary text-white border-0">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase fw-bold opacity-75 mb-2">Lifetime Valid Revenue</h6>
                    <h2 class="fw-extrabold mb-0">Rs. <?= number_format($total_revenue, 2) ?></h2>
                </div>
                <i class="bi bi-graph-up-arrow opacity-50" style="font-size: 3rem;"></i>
            </div>
        </div>
    </div>

    <!-- Sales by Status -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Sales by Status</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Status</th><th class="text-end">Orders</th><th class="text-end">Value</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales_by_status as $s): ?>
                        <tr>
                            <td><?= $s['order_status'] ?></td>
                            <td class="text-end"><?= $s['count'] ?></td>
                            <td class="text-end fw-bold">Rs. <?= number_format($s['total'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Products -->
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Top Selling Products</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr><th>Product</th><th class="text-end">Qty Sold</th><th class="text-end">Revenue</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $tp): ?>
                        <tr>
                            <td><?= htmlspecialchars($tp['product_name']) ?></td>
                            <td class="text-end"><?= $tp['qty_sold'] ?></td>
                            <td class="text-end fw-bold">Rs. <?= number_format($tp['revenue'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Daily Sales (Last 30 Days) -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Daily Sales (Last 30 Active Days)</h6></div>
            <div class="card-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light position-sticky top-0">
                            <tr>
                                <th>Date</th>
                                <th class="text-end">Orders</th>
                                <th class="text-end pe-4">Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_sales as $rs): ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($rs['date'])) ?></td>
                                <td class="text-end"><?= $rs['order_count'] ?></td>
                                <td class="text-end pe-4 fw-bold text-success">Rs. <?= number_format($rs['total_sales'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
