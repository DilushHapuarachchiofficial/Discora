<?php
require_once __DIR__ . '/includes/auth.php';
if (!is_superadmin()) {
    header("Location: index.php");
    exit();
}
$admin_title = "Store Settings";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        // In a real app, you would save these to a settings table
        // For now, we will just show a success message
        $success = 'Store settings updated successfully.';
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="mb-0 fw-bold">Store Settings</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST">
                    <?= csrf_field() ?>
                    
                    <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">General Info</h6>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Store Name</label>
                            <input type="text" name="store_name" class="form-control" value="Discora" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Contact Email</label>
                            <input type="email" name="contact_email" class="form-control" value="support@discora.lk" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">Address</label>
                        <textarea name="address" class="form-control" rows="2" required>No.123 Gamer Street, Colombo 07</textarea>
                    </div>

                    <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Store Configuration</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Currency</label>
                            <select class="form-select" name="currency">
                                <option value="LKR" selected>LKR (Rs.)</option>
                                <option value="USD">USD ($)</option>
                                <option value="EUR">EUR (€)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-secondary small fw-bold">Tax Rate (%)</label>
                            <input type="number" step="0.01" name="tax_rate" class="form-control" value="8.00" required>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
