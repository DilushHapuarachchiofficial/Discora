<?php
$admin_title = "Dashboard";
require_once __DIR__ . '/includes/header.php';

try {
    $db = get_db_connection();
    
    // Total Products
    $stmt = $db->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Total Customers (Role ID 2)
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role_id = 2");
    $total_customers = $stmt->fetchColumn();

    // Total Orders
    $stmt = $db->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn();

    // Total Sales (Completed or Delivered orders)
    $stmt = $db->query("SELECT SUM(total_amount) FROM orders WHERE order_status IN ('Completed', 'Delivered', 'Shipped', 'Processing')");
    $total_sales = $stmt->fetchColumn() ?: 0.00;

    // Pending Orders
    $stmt = $db->query("SELECT COUNT(*) FROM orders WHERE order_status = 'Pending'");
    $pending_orders = $stmt->fetchColumn();

    // Low Stock Products (<= 5)
    $stmt = $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 5 AND status = 'Active'");
    $low_stock = $stmt->fetchColumn();

    // Recent Orders
    $stmt = $db->query("
        SELECT o.order_id, o.order_number, u.full_name as customer, o.total_amount, o.order_status, o.order_date 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        ORDER BY o.order_date DESC 
        LIMIT 5
    ");
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Sales by Date (Last 7 Days) for Chart
    $stmt = $db->query("
        SELECT DATE(order_date) as date, SUM(total_amount) as daily_sales
        FROM orders
        WHERE order_date >= DATE(NOW()) - INTERVAL 7 DAY
        GROUP BY DATE(order_date)
        ORDER BY DATE(order_date) ASC
    ");
    $sales_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<div class="row g-4 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-primary text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1">Total Sales</h6>
                    <h3 class="fw-extrabold mb-0">Rs. <?= number_format($total_sales, 2) ?></h3>
                </div>
                <div class="fs-1 opacity-50"><i class="bi bi-currency-dollar"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-success text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1">Total Orders</h6>
                    <h3 class="fw-extrabold mb-0"><?= number_format($total_orders) ?></h3>
                </div>
                <div class="fs-1 opacity-50"><i class="bi bi-cart-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-info text-white h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1">Customers</h6>
                    <h3 class="fw-extrabold mb-0"><?= number_format($total_customers) ?></h3>
                </div>
                <div class="fs-1 opacity-50"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm bg-warning text-dark h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <h6 class="text-uppercase fw-bold opacity-75 mb-1">Total Products</h6>
                    <h3 class="fw-extrabold mb-0"><?= number_format($total_products) ?></h3>
                </div>
                <div class="fs-1 opacity-50"><i class="bi bi-controller"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Chart -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="fw-bold mb-0">Sales Overview (Last 7 Days)</h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-end" style="min-height: 300px;">
                <?php if (empty($sales_data)): ?>
                    <div class="text-center text-muted my-auto">No sales data in the last 7 days.</div>
                <?php else: ?>
                    <div class="d-flex align-items-end justify-content-between h-100 gap-2 px-2 pb-2">
                        <?php 
                        $max_sales = max(array_column($sales_data, 'daily_sales'));
                        foreach ($sales_data as $data): 
                            $height = ($data['daily_sales'] / $max_sales) * 100;
                        ?>
                            <div class="d-flex flex-column align-items-center flex-grow-1" style="height: 100%;">
                                <div class="bg-primary rounded-top w-100 opacity-75" style="height: <?= $height ?>%; max-width: 40px; transition: height 1s ease;" title="Rs. <?= number_format($data['daily_sales'], 2) ?>"></div>
                                <small class="text-muted mt-2 font-monospace" style="font-size: 0.7rem;"><?= date('M d', strtotime($data['date'])) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h6 class="fw-bold mb-0">Action Needed</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center p-3 rounded bg-light mb-3">
                    <div class="fs-2 text-warning me-3"><i class="bi bi-box-seam"></i></div>
                    <div>
                        <h4 class="mb-0 fw-bold"><?= $pending_orders ?></h4>
                        <span class="text-secondary small">Pending Orders</span>
                    </div>
                    <a href="orders.php?status=Pending" class="btn btn-sm btn-outline-dark ms-auto">View</a>
                </div>
                <div class="d-flex align-items-center p-3 rounded bg-light">
                    <div class="fs-2 text-danger me-3"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <h4 class="mb-0 fw-bold"><?= $low_stock ?></h4>
                        <span class="text-secondary small">Low Stock Products</span>
                    </div>
                    <a href="products.php?filter=low_stock" class="btn btn-sm btn-outline-dark ms-auto">View</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders Table -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h6 class="fw-bold mb-0">Recent Orders</h6>
        <a href="orders.php" class="btn btn-sm btn-light">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_orders)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No recent orders found.</td></tr>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td class="ps-4 fw-bold">#<?= htmlspecialchars($order['order_number']) ?></td>
                        <td><?= htmlspecialchars($order['customer']) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($order['order_date'])) ?></td>
                        <td>
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
                            <span class="badge <?= $badge ?>"><?= $order['order_status'] ?></span>
                        </td>
                        <td class="text-end pe-4 fw-bold">Rs. <?= number_format($order['total_amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
