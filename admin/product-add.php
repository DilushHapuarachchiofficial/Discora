<?php

require_once __DIR__ . '/includes/auth.php';
require_permission('products');
$admin_title = "Add Product";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

// Fetch platforms and categories for dropdowns
$platforms = $db->query("SELECT * FROM platforms ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);
$categories = $db->query("SELECT * FROM categories WHERE status = 'Active' ORDER BY display_order ASC")->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request (CSRF token missing or incorrect).';
    } else {
        // Collect & sanitize inputs
        $product_name   = trim($_POST['product_name'] ?? '');
        $slug           = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['slug'] ?? '')));
        $platform_id    = (int)($_POST['platform_id'] ?? 0);
        $category_id    = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $price          = (float)($_POST['price'] ?? 0);
        $discount_price = !empty($_POST['discount_price']) ? (float)$_POST['discount_price'] : null;
        $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);
        $description    = trim($_POST['description'] ?? '');
        $status         = $_POST['status'] ?? 'Active';
        $is_new_arrival = isset($_POST['is_new_arrival']) ? 1 : 0;
        $is_featured    = isset($_POST['is_featured']) ? 1 : 0;

        // Validation
        if (empty($product_name) || empty($slug) || !$platform_id || $price <= 0) {
            $error = 'Please fill in all required fields (Name, Slug, Platform, Price).';
        } else {
            try {
                $db->beginTransaction();

                // 1. Insert Product
                $stmt = $db->prepare("
                    INSERT INTO products (
                        category_id, platform_id, product_name, slug, description, price, discount_price, 
                        stock_quantity, is_new_arrival, is_featured, status
                    ) VALUES (
                        :cat, :plat, :name, :slug, :desc, :price, :discount, :stock, :new, :feat, :status
                    )
                ");
                $stmt->execute([
                    'cat'      => $category_id,
                    'plat'     => $platform_id,
                    'name'     => $product_name,
                    'slug'     => $slug,
                    'desc'     => $description,
                    'price'    => $price,
                    'discount' => $discount_price,
                    'stock'    => $stock_quantity,
                    'new'      => $is_new_arrival,
                    'feat'     => $is_featured,
                    'status'   => $status
                ]);

                $product_id = $db->lastInsertId();

                // 2. Handle Image Uploads
                if (!empty($_FILES['images']['name'][0])) {
                    $upload_dir = __DIR__ . '/../assets/images/products/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                    $is_primary = 1;

                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                        $file_error = $_FILES['images']['error'][$key];
                        if ($file_error === UPLOAD_ERR_OK) {
                            $file_type = mime_content_type($tmp_name);
                            if (in_array($file_type, $allowed_types)) {
                                $ext = pathinfo($_FILES['images']['name'][$key], PATHINFO_EXTENSION);
                                $new_filename = $slug . '-' . time() . '-' . $key . '.' . $ext;
                                $dest = $upload_dir . $new_filename;
                                
                                if (move_uploaded_file($tmp_name, $dest)) {
                                    $db_path = 'assets/images/products/' . $new_filename;
                                    
                                    $img_stmt = $db->prepare("INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)");
                                    $img_stmt->execute([$product_id, $db_path, $is_primary]);
                                    
                                    $is_primary = 0; // Only the first image is primary
                                }
                            }
                        }
                    }
                }

                $db->commit();
                $success = "Product '$product_name' added successfully.";
                // Clear post data to prevent resubmission
                $_POST = [];

            } catch (PDOException $e) {
                $db->rollBack();
                if ($e->getCode() == 23000) { // Integrity constraint violation (Duplicate slug)
                    $error = 'The SEO Slug must be unique. A product with this slug already exists.';
                } else {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}
?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0">Add New Product</h6>
    </div>
    <div class="card-body p-4">
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="row g-4">
                <!-- Main Info -->
                <div class="col-lg-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Product Name *</label>
                        <input type="text" name="product_name" class="form-control" value="<?= htmlspecialchars($_POST['product_name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">SEO Slug *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-gray-200">discora.com/</span>
                            <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Description</label>
                        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">Product Images</label>
                        <input type="file" name="images[]" class="form-control" accept="image/jpeg, image/png, image/webp" multiple>
                        <div class="form-text">Select multiple files. The first file will be the primary image. (Max 2MB per image)</div>
                    </div>
                </div>

                <!-- Sidebar Settings -->
                <div class="col-lg-4">
                    <div class="bg-light rounded p-3 mb-4 border border-gray-200">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Organization</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Platform *</label>
                            <select name="platform_id" class="form-select" required>
                                <option value="">Select Platform</option>
                                <?php foreach ($platforms as $plat): ?>
                                    <option value="<?= $plat['platform_id'] ?>" <?= ($_POST['platform_id'] ?? '') == $plat['platform_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($plat['platform_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">None</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= ($_POST['category_id'] ?? '') == $cat['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="bg-light rounded p-3 mb-4 border border-gray-200">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Pricing & Inventory</h6>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-secondary">Price (Rs.) *</label>
                                <input type="number" step="0.01" name="price" class="form-control" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-secondary">Discount Price</label>
                                <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= htmlspecialchars($_POST['discount_price'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Stock Quantity *</label>
                            <input type="number" name="stock_quantity" class="form-control" value="<?= htmlspecialchars($_POST['stock_quantity'] ?? '10') ?>" required>
                        </div>
                    </div>

                    <div class="bg-light rounded p-3 mb-4 border border-gray-200">
                        <h6 class="fw-bold mb-3 border-bottom pb-2">Visibility</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">Status</label>
                            <select name="status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_new_arrival" name="is_new_arrival" value="1" <?= isset($_POST['is_new_arrival']) ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-bold" for="is_new_arrival">Mark as New Arrival</label>
                        </div>
                        
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                            <label class="form-check-label small fw-bold" for="is_featured">Featured Product</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2">
                        <i class="bi bi-save me-1"></i> Save Product
                    </button>
                    <a href="products.php" class="btn btn-light border w-100 mt-2 fw-bold">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-generate slug from product name
    document.querySelector('input[name="product_name"]').addEventListener('input', function(e) {
        let slug = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        document.querySelector('input[name="slug"]').value = slug;
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
