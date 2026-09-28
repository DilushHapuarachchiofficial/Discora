<?php
require_once __DIR__ . '/includes/auth.php';
require_permission('admins'); // Superadmin only since 'admins' module requires superadmin

require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';

$admin_title = "Manage Admins";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

// Handle Delete (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_admin') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $admin_id = (int)$_POST['admin_id'];
        
        // Prevent deleting self or other superadmins just in case
        if ($admin_id !== $_SESSION['user_id']) {
            $check = $db->prepare("SELECT is_superadmin FROM users WHERE user_id = ?");
            $check->execute([$admin_id]);
            $is_super = $check->fetchColumn();
            
            if (!$is_super) {
                $del = $db->prepare("DELETE FROM users WHERE user_id = ?");
                $del->execute([$admin_id]);
            }
        }
        header("Location: admins.php");
        exit();
    }
}

// Fetch all admins
$stmt = $db->query("
    SELECT user_id, full_name, username, email, is_superadmin, status, created_at, avatar, admin_permissions
    FROM users 
    WHERE role_id = 1 
    ORDER BY is_superadmin DESC, created_at DESC
");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Admin Accounts</h6>
        <a href="admin-add.php" class="btn btn-sm btn-primary">
            <i class="bi bi-person-plus me-1"></i> Add Admin
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-secondary small">
                <tr>
                    <th class="ps-4">Admin</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center gap-3">
                            <img src="<?= BASE_URL . htmlspecialchars($admin['avatar']) ?>" alt="Avatar" class="rounded-circle" style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($admin['full_name']) ?></div>
                                <div class="small text-muted">@<?= htmlspecialchars($admin['username']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($admin['email']) ?></td>
                    <td>
                        <?php if ($admin['is_superadmin']): ?>
                            <span class="badge bg-danger">Super Admin</span>
                        <?php else: ?>
                            <span class="badge bg-primary">Sub Admin</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge <?= $admin['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"><?= $admin['status'] ?></span>
                    </td>
                    <td class="text-end pe-4">
                        <?php if (!$admin['is_superadmin']): ?>
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="admin-edit.php?id=<?= $admin['user_id'] ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this admin?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_admin">
                                    <input type="hidden" name="admin_id" value="<?= $admin['user_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
