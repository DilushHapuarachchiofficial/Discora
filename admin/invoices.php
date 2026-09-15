<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('invoices');
$admin_title = "Manage Invoices";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');

$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(inv.invoice_number LIKE :search OR u.full_name LIKE :search_name OR o.order_number LIKE :search_order)";
    $params['search'] = "%$search%";
    $params['search_name'] = "%$search%";
    $params['search_order'] = "%$search%";
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$count_stmt = $db->prepare("SELECT COUNT(*) FROM invoices inv JOIN orders o ON inv.order_id = o.order_id JOIN users u ON o.user_id = u.user_id $where_sql");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$sql = "
    SELECT inv.invoice_id, inv.invoice_number, inv.generated_at,
           o.order_id, o.order_number, o.total_amount, o.order_status,
           u.full_name as customer_name
    FROM invoices inv
    JOIN orders o ON inv.order_id = o.order_id
    JOIN users u ON o.user_id = u.user_id
    $where_sql
    ORDER BY inv.generated_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header bg-white p-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <form method="GET" action="invoices.php" class="d-flex gap-2 flex-grow-1" style="max-width: 600px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search invoice, order # or customer..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-sm btn-dark px-3">Search</button>
            <?php if ($search): ?>
                <a href="invoices.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">Invoice #</th>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Generated At</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No invoices found.</td></tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?= htmlspecialchars($inv['invoice_number']) ?></td>
                        <td><a href="order-details.php?id=<?= $inv['order_id'] ?>">#<?= htmlspecialchars($inv['order_number']) ?></a></td>
                        <td><?= htmlspecialchars($inv['customer_name']) ?></td>
                        <td class="fw-bold">Rs. <?= number_format($inv['total_amount'], 2) ?></td>
                        <td><?= date('M d, Y h:i A', strtotime($inv['generated_at'])) ?></td>
                        <td class="text-end pe-4">
                            <a href="invoice.php?order_id=<?= $inv['order_id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary border">
                                <i class="bi bi-file-earmark-pdf me-1"></i> View PDF
                            </a>
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
