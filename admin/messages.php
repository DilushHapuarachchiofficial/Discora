<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('messages');
$admin_title = "Contact Messages";
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
    $where_clauses[] = "(name LIKE :s OR email LIKE :s OR subject LIKE :s)";
    $params['s'] = "%$search%";
}
if ($status) {
    $where_clauses[] = "status = :status";
    $params['status'] = $status;
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$count_stmt = $db->prepare("SELECT COUNT(*) FROM contact_messages $where_sql");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

$sql = "
    SELECT * FROM contact_messages 
    $where_sql
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_status') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $msg_id = (int)$_POST['message_id'];
        $new_status = $_POST['new_status'];
        if (in_array($new_status, ['New', 'Read', 'Resolved'])) {
            $upd = $db->prepare("UPDATE contact_messages SET status = ? WHERE message_id = ?");
            $upd->execute([$new_status, $msg_id]);
            header("Location: messages.php?" . http_build_query($_GET));
            exit();
        }
    }
}
?>

<div class="card">
    <div class="card-header bg-white p-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <form method="GET" action="messages.php" class="d-flex gap-2 flex-grow-1" style="max-width: 600px;">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search sender or subject..." value="<?= htmlspecialchars($search) ?>">
            <select name="status" class="form-select form-select-sm w-auto">
                <option value="">All Statuses</option>
                <option value="New" <?= $status === 'New' ? 'selected' : '' ?>>New</option>
                <option value="Read" <?= $status === 'Read' ? 'selected' : '' ?>>Read</option>
                <option value="Resolved" <?= $status === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
            </select>
            <button type="submit" class="btn btn-sm btn-dark px-3">Filter</button>
            <?php if ($search || $status): ?>
                <a href="messages.php" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">Sender</th>
                    <th>Subject</th>
                    <th style="width: 35%;">Message</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No messages found.</td></tr>
                <?php else: ?>
                    <?php foreach ($messages as $m): ?>
                    <tr class="<?= $m['status'] === 'New' ? 'fw-bold bg-light bg-opacity-50' : '' ?>">
                        <td class="ps-4">
                            <div class="text-dark"><?= htmlspecialchars($m['name']) ?></div>
                            <div class="small"><a href="mailto:<?= htmlspecialchars($m['email']) ?>" class="text-decoration-none"><?= htmlspecialchars($m['email']) ?></a></div>
                        </td>
                        <td><?= htmlspecialchars($m['subject']) ?></td>
                        <td>
                            <div class="small text-wrap text-truncate" style="max-height: 3rem; overflow: hidden;" title="<?= htmlspecialchars($m['message']) ?>">
                                <?= nl2br(htmlspecialchars($m['message'])) ?>
                            </div>
                        </td>
                        <td class="small"><?= date('M d, Y h:i A', strtotime($m['created_at'])) ?></td>
                        <td>
                            <?php
                            $badge = match($m['status']) {
                                'New' => 'bg-danger',
                                'Read' => 'bg-info text-dark',
                                'Resolved' => 'bg-success',
                                default => 'bg-secondary'
                            };
                            ?>
                            <span class="badge <?= $badge ?>"><?= $m['status'] ?></span>
                        </td>
                        <td class="text-end pe-4">
                            <form method="POST" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="message_id" value="<?= $m['message_id'] ?>">
                                
                                <div class="dropdown d-inline">
                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        Mark As
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <button type="submit" name="new_status" value="Read" class="dropdown-item py-2">
                                                <i class="bi bi-envelope-open me-2 text-info"></i>Read
                                            </button>
                                        </li>
                                        <li>
                                            <button type="submit" name="new_status" value="Resolved" class="dropdown-item py-2">
                                                <i class="bi bi-check-circle me-2 text-success"></i>Resolved
                                            </button>
                                        </li>
                                        <li>
                                            <button type="submit" name="new_status" value="New" class="dropdown-item py-2">
                                                <i class="bi bi-envelope me-2 text-danger"></i>New
                                            </button>
                                        </li>
                                    </ul>
                                </div>
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
