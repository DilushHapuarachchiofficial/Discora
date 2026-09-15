<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('categories');
$admin_title = "Manage Categories";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();
$error = '';
$success = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add') {
            $name = trim($_POST['category_name'] ?? '');
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'] ?? '')));
            if ($name && $slug) {
                try {
                    $stmt = $db->prepare("INSERT INTO categories (category_name, slug) VALUES (?, ?)");
                    $stmt->execute([$name, $slug]);
                    $success = 'Category added successfully.';
                } catch (PDOException $e) {
                    $error = 'Error adding category (Slug must be unique).';
                }
            }
        } elseif ($action === 'toggle_status') {
            $id = (int)$_POST['category_id'];
            $status = $_POST['new_status'];
            if (in_array($status, ['Active', 'Inactive'])) {
                $stmt = $db->prepare("UPDATE categories SET status = ? WHERE category_id = ?");
                $stmt->execute([$status, $id]);
                $success = 'Status updated.';
            }
        }
    }
}

$categories = $db->query("
    SELECT c.*, COUNT(p.product_id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.category_id = p.category_id 
    GROUP BY c.category_id 
    ORDER BY c.category_name ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Categories</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Slug</th>
                            <th>Products</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td class="ps-4 fw-bold"><?= htmlspecialchars($cat['category_name']) ?></td>
                            <td><span class="text-secondary">/category/</span><?= htmlspecialchars($cat['slug']) ?></td>
                            <td><?= $cat['product_count'] ?></td>
                            <td>
                                <span class="badge <?= $cat['status'] === 'Active' ? 'bg-success' : 'bg-secondary' ?>"><?= $cat['status'] ?></span>
                            </td>
                            <td class="text-end pe-4">
                                <form method="POST" style="display:inline;">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                    <input type="hidden" name="new_status" value="<?= $cat['status'] === 'Active' ? 'Inactive' : 'Active' ?>">
                                    <button type="submit" class="btn btn-sm <?= $cat['status'] === 'Active' ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                        <?= $cat['status'] === 'Active' ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Add Category</h6></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Name</label>
                        <input type="text" name="category_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Slug</label>
                        <input type="text" name="slug" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Add Category</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('input[name="category_name"]').addEventListener('input', function(e) {
        let slug = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        document.querySelector('input[name="slug"]').value = slug;
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
