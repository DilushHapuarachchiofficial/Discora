<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('customers');
$admin_title = "Manage Customers";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$where_clauses = ["role_id = 2"]; // Only customers
$params = [];

if ($search) {
    $where_clauses[] = "(full_name LIKE :s OR email LIKE :s OR phone LIKE :s)";
    $params['s'] = "%$search%";
}

$where_sql = 'WHERE ' . implode(' AND ', $where_clauses);

$count_stmt = $db->prepare("SELECT COUNT(*) FROM users $where_sql");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$sql = "
    SELECT u.user_id, u.full_name, u.email, u.phone, u.status, u.created_at,
           (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id) as total_orders
    FROM users u
    $where_sql
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $u_id = (int)$_POST['user_id'];
        $new_status = $_POST['new_status'];
        if (in_array($new_status, ['Active', 'Inactive', 'Suspended'])) {
            $upd = $db->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role_id = 2");
            $upd->execute([$new_status, $u_id]);
            header("Location: customers.php?" . http_build_query($_GET));
            exit();
        }
    }
}
?>

<div class="card">
    <div class="card-header bg-white p-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <form method="GET" action="customers.php" class="d-flex gap-2 flex-grow-1" style="max-width: 400px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, phone..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="btn btn-sm btn-dark px-3">Search</button>
            <?php if ($search): ?>
                <a href="customers.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">Customer</th>
                    <th>Contact</th>
                    <th>Joined</th>
                    <th>Orders</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No customers found.</td></tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($c['full_name']) ?></div>
                        </td>
                        <td>
                            <div><a href="mailto:<?= htmlspecialchars($c['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($c['email']) ?></a></div>
                            <div class="small text-secondary"><?= htmlspecialchars($c['phone'] ?? '-') ?></div>
                        </td>
                        <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                        <td class="fw-bold"><?= $c['total_orders'] ?></td>
                        <td>
                            <?php
                            $badge = match($c['status']) {
                                'Active' => 'bg-success',
                                'Inactive' => 'bg-secondary',
                                'Suspended' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= $c['status'] ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="customer-details.php?id=<?= $c['user_id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Change account status?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?= $c['user_id'] ?>">
                                    <?php if ($c['status'] === 'Active'): ?>
                                        <input type="hidden" name="new_status" value="Suspended">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Suspend"><i class="bi bi-slash-circle"></i></button>
                                    <?php else: ?>
                                        <input type="hidden" name="new_status" value="Active">
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Activate"><i class="bi bi-check-circle"></i></button>
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
