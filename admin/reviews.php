<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('reviews');
$admin_title = "Manage Reviews";
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
    $where_clauses[] = "(u.full_name LIKE :s OR p.product_name LIKE :s OR r.review_text LIKE :s)";
    $params['s'] = "%$search%";
}
if ($status) {
    $where_clauses[] = "r.review_status = :status";
    $params['status'] = $status;
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$count_stmt = $db->prepare("SELECT COUNT(*) FROM reviews r JOIN users u ON r.user_id = u.user_id JOIN products p ON r.product_id = p.product_id $where_sql");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$sql = "
    SELECT r.*, u.full_name, p.product_name 
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    JOIN products p ON r.product_id = p.product_id
    $where_sql
    ORDER BY r.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $r_id = (int)$_POST['review_id'];
        if ($_POST['action'] === 'approve') {
            $upd = $db->prepare("UPDATE reviews SET review_status = 'Approved' WHERE review_id = ?");
            $upd->execute([$r_id]);
        } elseif ($_POST['action'] === 'hide') {
            $upd = $db->prepare("UPDATE reviews SET review_status = 'Rejected' WHERE review_id = ?");
            $upd->execute([$r_id]);
        } elseif ($_POST['action'] === 'delete') {
            $del = $db->prepare("DELETE FROM reviews WHERE review_id = ?");
            $del->execute([$r_id]);
        }
        header("Location: reviews.php?" . http_build_query($_GET));
        exit();
    }
}
?>

<div class="card">
    <div class="card-header bg-white p-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <form method="GET" action="reviews.php" class="d-flex gap-2 flex-grow-1" style="max-width: 600px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search reviews..." value="<?= htmlspecialchars($search) ?>">
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">All Statuses</option>
                <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Approved" <?= $status === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= $status === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" class="btn btn-sm btn-dark px-3">Filter</button>
            <?php if ($search || $status): ?>
                <a href="reviews.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">Product / Customer</th>
                    <th>Rating</th>
                    <th style="width: 35%;">Review</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reviews)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No reviews found.</td></tr>
                <?php else: ?>
                    <?php foreach ($reviews as $r): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($r['product_name']) ?></div>
                            <div class="small text-secondary">by <?= htmlspecialchars($r['full_name']) ?></div>
                        </td>
                        <td>
                            <div class="text-warning">
                                <?php for ($i=1; $i<=5; $i++): ?>
                                    <i class="bi <?= $i <= $r['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </td>
                        <td>
                            <p class="mb-0 small text-wrap"><?= nl2br(htmlspecialchars($r['review_text'] ?? '')) ?></p>
                        </td>
                        <td class="small"><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
                        <td>
                            <?php
                            $badge = match($r['review_status']) {
                                'Approved' => 'bg-success',
                                'Pending' => 'bg-warning text-dark',
                                'Rejected' => 'bg-secondary',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= $r['review_status'] ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <form method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="review_id" value="<?= $r['review_id'] ?>">
                                
                                <?php if ($r['review_status'] !== 'Approved'): ?>
                                    <button type="submit" name="action" value="approve" class="btn btn-sm btn-outline-success border-0" title="Approve"><i class="bi bi-check-circle"></i></button>
                                <?php endif; ?>
                                
                                <?php if ($r['review_status'] !== 'Rejected'): ?>
                                    <button type="submit" name="action" value="hide" class="btn btn-sm btn-outline-warning border-0" title="Hide (Reject)"><i class="bi bi-eye-slash"></i></button>
                                <?php endif; ?>
                                
                                <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger border-0" title="Delete" onclick="return confirm('Permanently delete review?');"><i class="bi bi-trash"></i></button>
                            </form>
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
