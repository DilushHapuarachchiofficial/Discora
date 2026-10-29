<?php
/**
 * Discora - Admin Reports & Analytics Dashboard
 * Features Dynamic Date Filtering (Presets & Custom Date Range),
 * Interactive Chart.js Revenue vs Orders Visualization,
 * Product Performance, Status Breakdown, and Export.
 */

require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/core/functions.php';
require_permission('reports');
$admin_title = "Reports & Analytics";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

// -------------------------------------------------------------
// 1. Process Date Filter Inputs & Presets
// -------------------------------------------------------------
$preset     = sanitize_input($_GET['preset'] ?? 'all');
$start_date = sanitize_input($_GET['start_date'] ?? '');
$end_date   = sanitize_input($_GET['end_date'] ?? '');
$status_flt = sanitize_input($_GET['status'] ?? 'all');

$today = date('Y-m-d');

// Resolve presets to concrete date ranges
if ($preset === 'today') {
    $start_date = $today;
    $end_date   = $today;
} elseif ($preset === 'yesterday') {
    $start_date = date('Y-m-d', strtotime('-1 day'));
    $end_date   = date('Y-m-d', strtotime('-1 day'));
} elseif ($preset === 'last_7_days') {
    $start_date = date('Y-m-d', strtotime('-6 days'));
    $end_date   = $today;
} elseif ($preset === 'last_30_days') {
    $start_date = date('Y-m-d', strtotime('-29 days'));
    $end_date   = $today;
} elseif ($preset === 'this_month') {
    $start_date = date('Y-m-01');
    $end_date   = $today;
} elseif ($preset === 'last_month') {
    $start_date = date('Y-m-01', strtotime('first day of last month'));
    $end_date   = date('Y-m-t', strtotime('last day of last month'));
} elseif ($preset === 'this_year') {
    $start_date = date('Y-01-01');
    $end_date   = $today;
} elseif ($preset === 'custom' || (!empty($start_date) || !empty($end_date))) {
    $preset = 'custom';
    if (!empty($start_date)) {
        $start_date = date('Y-m-d', strtotime($start_date));
    }
    if (!empty($end_date)) {
        $end_date = date('Y-m-d', strtotime($end_date));
    }
} else {
    $preset = 'all';
    $start_date = '';
    $end_date = '';
}

// -------------------------------------------------------------
// 2. Build Dynamic SQL Conditions & Parameters
// -------------------------------------------------------------
$where_clauses = [];
$params = [];

if (!empty($start_date)) {
    $where_clauses[] = "o.order_date >= :start_datetime";
    $params[':start_datetime'] = $start_date . ' 00:00:00';
}

if (!empty($end_date)) {
    $where_clauses[] = "o.order_date <= :end_datetime";
    $params[':end_datetime'] = $end_date . ' 23:59:59';
}

if (!empty($status_flt) && $status_flt !== 'all') {
    $where_clauses[] = "o.order_status = :status_filter";
    $params[':status_filter'] = $status_flt;
}

$where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// -------------------------------------------------------------
// 3. Query Period Aggregate Metrics
// -------------------------------------------------------------
$summary_stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as gross_amount,
        COALESCE(SUM(CASE WHEN o.order_status IN ('Completed', 'Delivered', 'Shipped', 'Processing') THEN o.total_amount ELSE 0 END), 0) as valid_revenue,
        COALESCE(SUM(CASE WHEN o.order_status IN ('Completed', 'Delivered', 'Shipped', 'Processing') THEN 1 ELSE 0 END), 0) as valid_orders_count,
        COALESCE(SUM(CASE WHEN o.order_status = 'Pending' THEN 1 ELSE 0 END), 0) as pending_count,
        COALESCE(SUM(CASE WHEN o.order_status = 'Pending' THEN o.total_amount ELSE 0 END), 0) as pending_revenue,
        COALESCE(SUM(CASE WHEN o.order_status = 'Delivered' THEN 1 ELSE 0 END), 0) as delivered_count,
        COALESCE(SUM(CASE WHEN o.order_status = 'Cancelled' THEN 1 ELSE 0 END), 0) as cancelled_count
    FROM orders o
    $where_sql
");
$summary_stmt->execute($params);
$period_summary = $summary_stmt->fetch(PDO::FETCH_ASSOC);

$total_orders        = (int)$period_summary['total_orders'];
$valid_revenue       = (float)$period_summary['valid_revenue'];
$gross_amount        = (float)$period_summary['gross_amount'];
$valid_orders_count  = (int)$period_summary['valid_orders_count'];
$pending_count       = (int)$period_summary['pending_count'];
$pending_revenue     = (float)$period_summary['pending_revenue'];
$aov                 = $valid_orders_count > 0 ? ($valid_revenue / $valid_orders_count) : 0.00;

// Total Units Sold in this period
$items_stmt = $db->prepare("
    SELECT COALESCE(SUM(oi.quantity), 0) as total_items_sold
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    $where_sql
");
$items_stmt->execute($params);
$total_items_sold = (int)$items_stmt->fetchColumn();

// Lifetime Total Revenue for benchmark context
$lifetime_stmt = $db->query("
    SELECT 
        COALESCE(SUM(total_amount), 0) as lifetime_revenue,
        COUNT(*) as lifetime_orders
    FROM orders
    WHERE order_status IN ('Completed', 'Delivered', 'Shipped', 'Processing')
");
$lifetime_data = $lifetime_stmt->fetch(PDO::FETCH_ASSOC);
$lifetime_revenue = (float)$lifetime_data['lifetime_revenue'];
$lifetime_orders  = (int)$lifetime_data['lifetime_orders'];

// -------------------------------------------------------------
// 4. Query Daily Breakdown for Chart & Trend
// -------------------------------------------------------------
$daily_stmt = $db->prepare("
    SELECT 
        DATE(o.order_date) as order_day, 
        COUNT(*) as order_count,
        COALESCE(SUM(CASE WHEN o.order_status IN ('Completed', 'Delivered', 'Shipped', 'Processing') THEN o.total_amount ELSE 0 END), 0) as day_revenue,
        COALESCE(SUM(o.total_amount), 0) as gross_revenue
    FROM orders o
    $where_sql
    GROUP BY DATE(o.order_date)
    ORDER BY order_day ASC
");
$daily_stmt->execute($params);
$daily_sales = $daily_stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare JSON data for Chart.js
$chart_labels  = [];
$chart_revenue = [];
$chart_orders  = [];

foreach ($daily_sales as $row) {
    $chart_labels[]  = date('M d', strtotime($row['order_day']));
    $chart_revenue[] = (float)$row['day_revenue'];
    $chart_orders[]  = (int)$row['order_count'];
}

// -------------------------------------------------------------
// 5. Query Sales by Status (Filtered by Period)
// -------------------------------------------------------------
$status_stmt = $db->prepare("
    SELECT 
        o.order_status, 
        COUNT(*) as count, 
        COALESCE(SUM(o.total_amount), 0) as total
    FROM orders o
    $where_sql
    GROUP BY o.order_status
    ORDER BY total DESC
");
$status_stmt->execute($params);
$sales_by_status = $status_stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------------------------------------------------
// 6. Query Top Selling Products (Filtered by Period)
// -------------------------------------------------------------
$top_stmt = $db->prepare("
    SELECT 
        p.product_id,
        p.product_name,
        pl.platform_name,
        pl.slug as platform_slug,
        SUM(oi.quantity) as qty_sold,
        SUM(oi.subtotal) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    JOIN platforms pl ON p.platform_id = pl.platform_id
    JOIN orders o ON oi.order_id = o.order_id
    $where_sql
    GROUP BY oi.product_id, p.product_name, pl.platform_name, pl.slug
    ORDER BY qty_sold DESC
    LIMIT 6
");
$top_stmt->execute($params);
$top_products = $top_stmt->fetchAll(PDO::FETCH_ASSOC);

// -------------------------------------------------------------
// 7. Query Orders List for Detailed Log (Filtered by Period)
// -------------------------------------------------------------
$orders_stmt = $db->prepare("
    SELECT 
        o.order_id, 
        o.order_number, 
        o.order_date, 
        o.shipping_name, 
        u.full_name as customer_name,
        o.total_amount, 
        o.order_status,
        COALESCE(p.payment_method, 'Cash on Delivery') as payment_method,
        COALESCE(p.payment_status, 'Pending') as payment_status,
        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) as items_count
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    LEFT JOIN payments p ON o.order_id = p.order_id
    $where_sql
    ORDER BY o.order_date DESC
    LIMIT 30
");
$orders_stmt->execute($params);
$recent_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Determine human-readable period description
if ($preset === 'today') {
    $period_label = "Today (" . date('M d, Y') . ")";
} elseif ($preset === 'yesterday') {
    $period_label = "Yesterday (" . date('M d, Y', strtotime('-1 day')) . ")";
} elseif ($preset === 'last_7_days') {
    $period_label = "Last 7 Days (" . date('M d', strtotime('-6 days')) . " - " . date('M d, Y') . ")";
} elseif ($preset === 'last_30_days') {
    $period_label = "Last 30 Days (" . date('M d', strtotime('-29 days')) . " - " . date('M d, Y') . ")";
} elseif ($preset === 'this_month') {
    $period_label = "This Month (" . date('F Y') . ")";
} elseif ($preset === 'last_month') {
    $period_label = "Last Month (" . date('F Y', strtotime('last month')) . ")";
} elseif ($preset === 'this_year') {
    $period_label = "This Year (" . date('Y') . ")";
} elseif (!empty($start_date) && !empty($end_date)) {
    $period_label = date('M d, Y', strtotime($start_date)) . " to " . date('M d, Y', strtotime($end_date));
} elseif (!empty($start_date)) {
    $period_label = "From " . date('M d, Y', strtotime($start_date));
} elseif (!empty($end_date)) {
    $period_label = "Until " . date('M d, Y', strtotime($end_date));
} else {
    $period_label = "All Time (Complete History)";
}
?>

<!-- Load Chart.js CDN for Interactive Graphs -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="reports-container pb-4">

    <!-- =========================================================
         PAGE TITLE & EXPORT HEADER
         ========================================================= -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1 font-heading text-dark d-flex align-items-center gap-2">
                <i class="bi bi-graph-up text-primary"></i> Reports & Analytics
            </h4>
            <p class="text-muted small mb-0">
                Track revenue, order metrics and game sales trends across custom date periods.
            </p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-printer"></i> Print Report
            </button>
            <a href="reports.php" class="btn btn-light btn-sm border d-flex align-items-center gap-1 shadow-sm text-dark">
                <i class="bi bi-arrow-clockwise"></i> Reset Filters
            </a>
        </div>
    </div>

    <!-- =========================================================
         DATE FILTER TOOLBAR CARD
         ========================================================= -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            
            <!-- Quick Preset Filter Buttons -->
            <div class="d-flex flex-wrap gap-2 mb-3 pb-3 border-bottom align-items-center">
                <span class="small fw-bold text-uppercase text-secondary me-2">Quick Presets:</span>
                <a href="reports.php?preset=all" 
                   class="btn btn-sm <?= ($preset === 'all') ? 'btn-primary shadow-sm' : 'btn-outline-secondary' ?>">
                    All Time
                </a>
                <a href="reports.php?preset=today" 
                   class="btn btn-sm <?= ($preset === 'today') ? 'btn-primary shadow-sm' : 'btn-outline-secondary' ?>">
                    Today
                </a>
                <a href="reports.php?preset=yesterday" 
                   class="btn btn-sm <?= ($preset === 'yesterday') ? 'btn-primary shadow-sm' : 'btn-outline-secondary' ?>">
                    Yesterday
                </a>
                <a href="reports.php?preset=last_7_days" 
                   class="btn btn-sm <?= ($preset === 'last_7_days') ? 'btn-primary shadow-sm' : 'btn-outline-secondary' ?>">
                    Last 7 Days
                </a>
                <a href="reports.php?preset=this_month" 
                   class="btn btn-sm <?= ($preset === 'this_month') ? 'btn-primary shadow-sm' : 'btn-outline-secondary' ?>">
                    This Month
                </a>
                <a href="reports.php?preset=last_month" 
                   class="btn btn-sm <?= ($preset === 'last_month') ? 'btn-primary shadow-sm' : 'btn-outline-secondary' ?>">
                    Last Month
                </a>
                <a href="reports.php?preset=last_30_days" 
                   class="btn btn-sm <?= ($preset === 'last_30_days') ? 'btn-primary shadow-sm' : 'btn-outline-secondary' ?>">
                    Last 30 Days
                </a>
            </div>

            <!-- Custom Date Range Form -->
            <form method="GET" action="reports.php" class="row g-2 align-items-end" id="dateFilterForm">
                <input type="hidden" name="preset" value="custom">

                <!-- Start Date -->
                <div class="col-sm-6 col-md-3">
                    <label for="startDateInput" class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-calendar-event me-1"></i> Start Date
                    </label>
                    <input type="date" 
                           id="startDateInput" 
                           name="start_date" 
                           class="form-control form-control-sm" 
                           value="<?= htmlspecialchars($start_date) ?>"
                           max="<?= $today ?>">
                </div>

                <!-- End Date -->
                <div class="col-sm-6 col-md-3">
                    <label for="endDateInput" class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-calendar-check me-1"></i> End Date
                    </label>
                    <input type="date" 
                           id="endDateInput" 
                           name="end_date" 
                           class="form-control form-control-sm" 
                           value="<?= htmlspecialchars($end_date) ?>"
                           max="<?= $today ?>">
                </div>

                <!-- Status Filter -->
                <div class="col-sm-6 col-md-3">
                    <label for="statusInput" class="form-label small fw-semibold text-secondary mb-1">
                        <i class="bi bi-funnel me-1"></i> Order Status
                    </label>
                    <select id="statusInput" name="status" class="form-select form-select-sm">
                        <option value="all" <?= ($status_flt === 'all') ? 'selected' : '' ?>>All Statuses</option>
                        <option value="Completed" <?= ($status_flt === 'Completed') ? 'selected' : '' ?>>Completed</option>
                        <option value="Delivered" <?= ($status_flt === 'Delivered') ? 'selected' : '' ?>>Delivered</option>
                        <option value="Shipped" <?= ($status_flt === 'Shipped') ? 'selected' : '' ?>>Shipped</option>
                        <option value="Processing" <?= ($status_flt === 'Processing') ? 'selected' : '' ?>>Processing</option>
                        <option value="Pending" <?= ($status_flt === 'Pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Cancelled" <?= ($status_flt === 'Cancelled') ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <!-- Submit / Clear Buttons -->
                <div class="col-sm-6 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold d-flex align-items-center justify-content-center gap-1 shadow-sm">
                        <i class="bi bi-filter"></i> Apply Filter
                    </button>
                    <a href="reports.php" class="btn btn-outline-secondary btn-sm" title="Clear Date Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                </div>
            </form>

            <!-- Active Period Indicator Badge -->
            <div class="mt-3 pt-2 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top border-light">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill font-monospace">
                        <i class="bi bi-clock-history me-1"></i> Period: <?= htmlspecialchars($period_label) ?>
                    </span>
                    <?php if ($status_flt !== 'all'): ?>
                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                        <i class="bi bi-tag me-1"></i> Status: <?= htmlspecialchars($status_flt) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="text-muted small">
                    Showing <strong><?= $total_orders ?></strong> orders matching filter
                </div>
            </div>

        </div>
    </div>

    <!-- =========================================================
         SUMMARY KPI CARDS (UPDATED FOR SELECTED PERIOD)
         ========================================================= -->
    <div class="row g-3 mb-4">
        
        <!-- Period Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase small fw-bold opacity-75 d-block mb-1">Period Revenue</span>
                        <h3 class="fw-extrabold mb-1 font-heading">Rs. <?= number_format($valid_revenue, 2) ?></h3>
                        <span class="small opacity-75">
                            Gross: Rs. <?= number_format($gross_amount, 2) ?>
                        </span>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-currency-dollar"></i></div>
                </div>
            </div>
        </div>

        <!-- Period Total Orders -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase small fw-bold opacity-75 d-block mb-1">Total Orders</span>
                        <h3 class="fw-extrabold mb-1 font-heading"><?= number_format($total_orders) ?></h3>
                        <span class="small opacity-75">
                            <?= $valid_orders_count ?> Valid &bull; <?= $pending_count ?> Pending
                        </span>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-cart-check"></i></div>
                </div>
            </div>
        </div>

        <!-- Average Order Value (AOV) -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase small fw-bold opacity-75 d-block mb-1">Average Order Value</span>
                        <h3 class="fw-extrabold mb-1 font-heading">Rs. <?= number_format($aov, 2) ?></h3>
                        <span class="small opacity-75">
                            Per processed customer order
                        </span>
                    </div>
                    <div class="fs-1 opacity-50"><i class="bi bi-calculator"></i></div>
                </div>
            </div>
        </div>

        <!-- Units / Discs Sold -->
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm bg-dark text-white h-100">
                <div class="card-body p-3 p-xl-4 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-uppercase small fw-bold opacity-75 d-block mb-1">Physical Units Sold</span>
                        <h3 class="fw-extrabold mb-1 font-heading"><?= number_format($total_items_sold) ?></h3>
                        <span class="small text-secondary">
                            Game discs & hardware
                        </span>
                    </div>
                    <div class="fs-1 opacity-50 text-secondary"><i class="bi bi-disc"></i></div>
                </div>
            </div>
        </div>

    </div>

    <!-- =========================================================
         INTERACTIVE REVENUE & ORDER COUNT TIMELINE (CHART.JS)
         ========================================================= -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <div>
                <h6 class="fw-bold mb-0 font-heading text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-bar-chart-line text-primary"></i> Daily Revenue & Order Volume
                </h6>
                <small class="text-muted">Displaying trend for <?= htmlspecialchars($period_label) ?></small>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary px-2 py-1"><i class="bi bi-square-fill me-1"></i> Revenue (Rs.)</span>
                <span class="badge bg-success px-2 py-1"><i class="bi bi-dash-lg me-1"></i> Orders Count</span>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            <?php if (empty($daily_sales)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-1 opacity-50 d-block mb-2"></i>
                    <h6 class="fw-bold">No order records found for this period</h6>
                    <p class="small mb-3">Try adjusting your date range or selecting "All Time" to view your historical sales.</p>
                    <a href="reports.php?preset=all" class="btn btn-outline-primary btn-sm">View All Time Data</a>
                </div>
            <?php else: ?>
                <div style="position: relative; height: 320px; width: 100%;">
                    <canvas id="revenueOrdersChart"></canvas>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- =========================================================
         BREAKDOWN TABLES: STATUS SUMMARY & TOP PRODUCTS
         ========================================================= -->
    <div class="row g-4 mb-4">
        
        <!-- Sales by Status -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 font-heading text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-pie-chart text-info"></i> Sales by Order Status
                    </h6>
                    <small class="text-muted">Volume and value breakdown for selected period</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Status</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-end">Total Value</th>
                                    <th class="text-end pe-3">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($sales_by_status)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No data available for this range</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($sales_by_status as $st): 
                                        $badge_color = match($st['order_status']) {
                                            'Delivered', 'Completed' => 'bg-success',
                                            'Processing' => 'bg-primary',
                                            'Shipped' => 'bg-info',
                                            'Pending' => 'bg-warning text-dark',
                                            'Cancelled' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                        $pct = ($gross_amount > 0) ? round(($st['total'] / $gross_amount) * 100, 1) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge <?= $badge_color ?> rounded-pill px-2-5 py-1">
                                                <?= htmlspecialchars($st['order_status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-semibold"><?= number_format($st['count']) ?></td>
                                        <td class="text-end fw-bold font-monospace">Rs. <?= number_format($st['total'], 2) ?></td>
                                        <td class="text-end pe-3 small text-muted"><?= $pct ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Selling Products in Period -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="fw-bold mb-0 font-heading text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-trophy text-warning"></i> Top Games & Products
                    </h6>
                    <small class="text-muted">Most purchased titles in selected period</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Game / Product</th>
                                    <th>Platform</th>
                                    <th class="text-center">Units</th>
                                    <th class="text-end pe-3">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($top_products)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted small">No product sales in this period</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($top_products as $idx => $tp): ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge bg-light text-dark border me-1">#<?= $idx + 1 ?></span>
                                            <span class="fw-semibold"><?= htmlspecialchars($tp['product_name']) ?></span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                                <?= htmlspecialchars($tp['platform_name']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center fw-bold"><?= number_format($tp['qty_sold']) ?></td>
                                        <td class="text-end pe-3 fw-bold text-success font-monospace">
                                            Rs. <?= number_format($tp['revenue'], 2) ?>
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

    </div>

    <!-- =========================================================
         DETAILED ORDERS LOG FOR FILTERED PERIOD
         ========================================================= -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold mb-0 font-heading text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-primary"></i> Orders Log for Filtered Period
                </h6>
                <small class="text-muted">Displaying up to 30 latest orders in this date range</small>
            </div>
            <a href="orders.php" class="btn btn-outline-primary btn-sm">
                View All Orders <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Order Number</th>
                            <th>Date &amp; Time</th>
                            <th>Customer</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 opacity-50 d-block mb-1"></i>
                                No orders matching the selected date filters.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $ord): 
                                $status_badge = match($ord['order_status']) {
                                    'Delivered', 'Completed' => 'bg-success',
                                    'Processing' => 'bg-primary',
                                    'Shipped' => 'bg-info',
                                    'Pending' => 'bg-warning text-dark',
                                    'Cancelled' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            ?>
                            <tr>
                                <td class="ps-3 fw-bold font-monospace text-primary">
                                    <a href="order-details.php?id=<?= $ord['order_id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($ord['order_number']) ?>
                                    </a>
                                </td>
                                <td class="small text-secondary">
                                    <?= date('M d, Y h:i A', strtotime($ord['order_date'])) ?>
                                </td>
                                <td>
                                    <span class="fw-semibold"><?= htmlspecialchars($ord['shipping_name'] ?: ($ord['customer_name'] ?? 'Customer')) ?></span>
                                </td>
                                <td class="small">
                                    <span class="d-block text-dark"><?= htmlspecialchars($ord['payment_method']) ?></span>
                                    <span class="badge bg-light text-secondary border"><?= htmlspecialchars($ord['payment_status']) ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $status_badge ?> rounded-pill px-2-5 py-1">
                                        <?= htmlspecialchars($ord['order_status']) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold font-monospace text-dark">
                                    Rs. <?= number_format($ord['total_amount'], 2) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="order-details.php?id=<?= $ord['order_id'] ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" title="View Order">
                                        <i class="bi bi-eye"></i>
                                    </a>
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

<!-- =========================================================
     CHART.JS SCRIPT INITIALIZATION
     ========================================================= -->
<?php if (!empty($daily_sales)): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('revenueOrdersChart');
    if (!ctx) return;

    const labels  = <?= json_encode($chart_labels) ?>;
    const revenue = <?= json_encode($chart_revenue) ?>;
    const orders  = <?= json_encode($chart_orders) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Revenue (Rs.)',
                    data: revenue,
                    backgroundColor: 'rgba(13, 110, 253, 0.75)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                    borderRadius: 6,
                    yAxisID: 'yRevenue',
                    order: 2
                },
                {
                    label: 'Orders Count',
                    data: orders,
                    type: 'line',
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#ffffff',
                    pointHoverRadius: 6,
                    pointRadius: 4,
                    borderWidth: 2.5,
                    tension: 0.3,
                    yAxisID: 'yOrders',
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false // Using custom badges in header
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.yAxisID === 'yRevenue') {
                                return ' Revenue: Rs. ' + context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            } else {
                                return ' Orders: ' + context.parsed.y + ' orders';
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'system-ui',
                            size: 11
                        }
                    }
                },
                yRevenue: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rs. ' + (value >= 1000 ? (value / 1000) + 'k' : value);
                        },
                        font: {
                            size: 11
                        }
                    }
                },
                yOrders: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    grid: {
                        drawOnChartArea: false
                    },
                    ticks: {
                        precision: 0,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
