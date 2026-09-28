<?php
require_once __DIR__ . '/includes/auth.php';
require_permission('admins');

require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/db.php';
require_once __DIR__ . '/includes/csrf.php';

$db = get_db_connection();
$modules = ['products', 'categories', 'platforms', 'orders', 'invoices', 'reports', 'customers', 'reviews', 'messages'];
$error = '';
$success = '';

$admin_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch admin
$stmt = $db->prepare("SELECT * FROM users WHERE user_id = ? AND role_id = 1 AND is_superadmin = 0");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$admin) {
    header("Location: admins.php");
    exit();
}

$current_perms = json_decode($admin['admin_permissions'] ?? '[]', true) ?: [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'] ?? '';
        $permissions = $_POST['permissions'] ?? [];
        $status = $_POST['status'] ?? 'Active';
        
        if (empty($full_name) || empty($username) || empty($email)) {
            $error = "Name, Username and Email are required.";
        } else {
            // Check uniqueness excluding current user
            $check = $db->prepare("SELECT user_id FROM users WHERE (email = ? OR username = ?) AND user_id != ?");
            $check->execute([$email, $username, $admin_id]);
            if ($check->rowCount() > 0) {
                $error = "Email or username is already taken by another user.";
            } else {
                $perms_json = json_encode(array_intersect($permissions, $modules));
                
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update = $db->prepare("
                        UPDATE users 
                        SET full_name = ?, username = ?, email = ?, password = ?, admin_permissions = ?, status = ?
                        WHERE user_id = ?
                    ");
                    $res = $update->execute([$full_name, $username, $email, $hashed_password, $perms_json, $status, $admin_id]);
                } else {
                    $update = $db->prepare("
                        UPDATE users 
                        SET full_name = ?, username = ?, email = ?, admin_permissions = ?, status = ?
                        WHERE user_id = ?
                    ");
                    $res = $update->execute([$full_name, $username, $email, $perms_json, $status, $admin_id]);
                }
                
                if ($res) {
                    $success = "Admin updated successfully!";
                    // Refresh data
                    $stmt->execute([$admin_id]);
                    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                    $current_perms = json_decode($admin['admin_permissions'] ?? '[]', true) ?: [];
                } else {
                    $error = "Failed to update admin.";
                }
            }
        }
    } else {
        $error = "Invalid form submission.";
    }
}

$admin_title = "Edit Sub-Admin";
require_once __DIR__ . '/includes/header.php';
?>

<div class="card max-w-2xl mx-auto">
    <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Edit Admin: <?= htmlspecialchars($admin['full_name']) ?></h6>
        <a href="admins.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
    
    <div class="card-body p-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <?= csrf_field() ?>
            
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-semibold">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($_POST['full_name'] ?? $admin['full_name']) ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="Active" <?= ($admin['status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                        <option value="Inactive" <?= ($admin['status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                        <option value="Suspended" <?= ($admin['status'] == 'Suspended') ? 'selected' : '' ?>>Suspended</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? $admin['username']) ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? $admin['email']) ?>">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-semibold">Reset Password</label>
                <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
            </div>
            
            <h6 class="fw-bold border-bottom pb-2 mb-3">Module Permissions</h6>
            <p class="text-muted small mb-3">Select which modules this sub-admin can access.</p>
            
            <div class="row g-3 mb-4">
                <?php foreach ($modules as $mod): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $mod ?>" id="perm_<?= $mod ?>"
                            <?= in_array($mod, $current_perms) ? 'checked' : '' ?>>
                        <label class="form-check-label text-capitalize" for="perm_<?= $mod ?>">
                            <?= htmlspecialchars($mod) ?>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
