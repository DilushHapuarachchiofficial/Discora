<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('products');
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$db = get_db_connection();

// Handle Status Toggle (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $product_id = (int)$_POST['product_id'];
        $new_status = $_POST['new_status'];
        if (in_array($new_status, ['Active', 'Inactive', 'Out of Stock'])) {
            $upd = $db->prepare("UPDATE products SET status = :st WHERE product_id = :id");
            $upd->execute(['st' => $new_status, 'id' => $product_id]);
            // Redirect to refresh
            header("Location: products.php?" . http_build_query($_GET));
            exit();
        }
    }
}

$admin_title = "Manage Products";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

// Search & Filter
$search = trim($_GET['search'] ?? '');
$filter = $_GET['filter'] ?? '';

$where_clauses = [];
$params = [];

if ($search) {
    $where_clauses[] = "(p.product_name LIKE :search OR p.product_id = :search_id)";
    $params['search'] = "%$search%";
    $params['search_id'] = is_numeric($search) ? $search : 0;
}

if ($filter === 'low_stock') {
    $where_clauses[] = "p.stock_quantity <= 5 AND p.status = 'Active'";
} elseif ($filter === 'inactive') {
    $where_clauses[] = "p.status = 'Inactive'";
} elseif ($filter === 'new_arrival') {
    $where_clauses[] = "p.is_new_arrival = 1";
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

// Count total for pagination
$count_stmt = $db->prepare("SELECT COUNT(*) FROM products p $where_sql");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Fetch products
$sql = "
    SELECT p.product_id, p.product_name, p.price, p.stock_quantity, p.status, p.is_new_arrival,
           pl.platform_name, c.category_name,
           (SELECT image_path FROM product_images WHERE product_id = p.product_id ORDER BY is_primary DESC, display_order ASC LIMIT 1) as main_image
    FROM products p
    JOIN platforms pl ON p.platform_id = pl.platform_id
    LEFT JOIN categories c ON p.category_id = c.category_id
    $where_sql
    ORDER BY p.product_id DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header bg-white p-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <form method="GET" action="products.php" class="d-flex gap-2 flex-grow-1" style="max-width: 600px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            <select name="filter" class="form-select form-select-sm w-auto">
                <option value="">All Products</option>
                <option value="low_stock" <?= $filter === 'low_stock' ? 'selected' : '' ?>>Low Stock</option>
                <option value="inactive" <?= $filter === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="new_arrival" <?= $filter === 'new_arrival' ? 'selected' : '' ?>>New Arrivals</option>
            </select>
            <button type="submit" class="btn btn-sm btn-dark px-3">Filter</button>
            <?php if ($search || $filter): ?>
                <a href="products.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
        
        <a href="product-add.php" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Product
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">Product</th>
                    <th>Platform</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No products found.</td></tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($p['main_image']): ?>
                                    <img src="../<?= htmlspecialchars($p['main_image']) ?>" alt="" style="width: 40px; height: 50px; object-fit: contain;" class="bg-light rounded">
                                <?php else: ?>
                                    <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 50px;">
                                        <i class="bi bi-image"></i>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($p['product_name']) ?></div>
                                    <?php if ($p['is_new_arrival']): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25" style="font-size: 0.65rem;">NEW ARRIVAL</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($p['platform_name']) ?></td>
                        <td class="fw-bold">Rs. <?= number_format($p['price'], 2) ?></td>
                        <td>
                            <?php if ($p['stock_quantity'] <= 5): ?>
                                <span class="text-danger fw-bold"><i class="bi bi-exclamation-circle me-1"></i><?= $p['stock_quantity'] ?></span>
                            <?php else: ?>
                                <?= $p['stock_quantity'] ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $badge = match($p['status']) {
                                'Active' => 'bg-success',
                                'Inactive' => 'bg-secondary',
                                'Out of Stock' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= $p['status'] ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="product-edit.php?id=<?= $p['product_id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Change status?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                    <?php if ($p['status'] === 'Active'): ?>
                                        <input type="hidden" name="new_status" value="Inactive">
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Deactivate"><i class="bi bi-pause-circle"></i></button>
                                    <?php else: ?>
                                        <input type="hidden" name="new_status" value="Active">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Activate"><i class="bi bi-play-circle"></i></button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
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
