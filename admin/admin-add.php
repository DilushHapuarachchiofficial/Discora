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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'] ?? '';
        $permissions = $_POST['permissions'] ?? [];
        
        if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
            $error = "All fields are required.";
        } else {
            // Check uniqueness
            $check = $db->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
            $check->execute([$email, $username]);
            if ($check->rowCount() > 0) {
                $error = "Email or username is already taken.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $perms_json = json_encode(array_intersect($permissions, $modules));
                
                $insert = $db->prepare("
                    INSERT INTO users (role_id, full_name, username, email, password, admin_permissions, status)
                    VALUES (1, ?, ?, ?, ?, ?, 'Active')
                ");
                if ($insert->execute([$full_name, $username, $email, $hashed_password, $perms_json])) {
                    $success = "Admin created successfully!";
                    // Redirect to admins list
                    header("Location: admins.php");
                    exit();
                } else {
                    $error = "Failed to create admin.";
                }
            }
        }
    } else {
        $error = "Invalid form submission.";
    }
}

$admin_title = "Add Sub-Admin";
require_once __DIR__ . '/includes/header.php';
?>

<div class="card max-w-2xl mx-auto">
    <div class="card-header bg-white p-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Add New Admin</h6>
        <a href="admins.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
    
    <div class="card-body p-4">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <?= csrf_field() ?>
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Full Name</label>
                <input type="text" name="full_name" class="form-control" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Username</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <h6 class="fw-bold border-bottom pb-2 mb-3">Module Permissions</h6>
            <p class="text-muted small mb-3">Select which modules this sub-admin can access.</p>
            
            <div class="row g-3 mb-4">
                <?php foreach ($modules as $mod): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $mod ?>" id="perm_<?= $mod ?>"
                            <?= isset($_POST['permissions']) && in_array($mod, $_POST['permissions']) ? 'checked' : '' ?>>
                        <label class="form-check-label text-capitalize" for="perm_<?= $mod ?>">
                            <?= htmlspecialchars($mod) ?>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i> Create Admin
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
