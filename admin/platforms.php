<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('platforms');
$admin_title = "Manage Platforms";
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
            $name = trim($_POST['platform_name'] ?? '');
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'] ?? '')));
            $brand = $_POST['brand'] ?? 'Multiplatform';
            if ($name && $slug) {
                try {
                    $stmt = $db->prepare("INSERT INTO platforms (platform_name, slug, brand) VALUES (?, ?, ?)");
                    $stmt->execute([$name, $slug, $brand]);
                    $success = 'Platform added successfully.';
                } catch (PDOException $e) {
                    $error = 'Error adding platform (Slug must be unique).';
                }
            }
        }
        // Note: Platforms usually don't have a status field in the schema, just displaying and adding is enough.
    }
}

$platforms = $db->query("
    SELECT p.*, COUNT(prod.product_id) as product_count 
    FROM platforms p 
    LEFT JOIN products prod ON p.platform_id = prod.platform_id 
    GROUP BY p.platform_id 
    ORDER BY p.platform_name ASC
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
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Platforms</h6></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th class="ps-4">Name</th>
                            <th>Brand</th>
                            <th>Slug</th>
                            <th class="text-end pe-4">Products</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($platforms as $plat): ?>
                        <tr>
                            <td class="ps-4 fw-bold">
                                <?= htmlspecialchars($plat['platform_name']) ?>
                            </td>
                            <td><?= htmlspecialchars($plat['brand']) ?></td>
                            <td><span class="text-secondary">/platform/</span><?= htmlspecialchars($plat['slug']) ?></td>
                            <td class="text-end pe-4 fw-bold"><?= $plat['product_count'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white py-3"><h6 class="fw-bold mb-0">Add Platform</h6></div>
            <div class="card-body">
                <form method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Name</label>
                        <input type="text" name="platform_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Slug</label>
                        <input type="text" name="slug" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Brand</label>
                        <select name="brand" class="form-select">
                            <option value="PlayStation">PlayStation</option>
                            <option value="Xbox">Xbox</option>
                            <option value="Nintendo">Nintendo</option>
                            <option value="Multiplatform">Multiplatform</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Add Platform</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('input[name="platform_name"]').addEventListener('input', function(e) {
        let slug = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        document.querySelector('input[name="slug"]').value = slug;
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
