<?php
/**
 * Discora - Admin Navigation Sidebar
 */
?>
<div class="bg-black border-end border-secondary border-opacity-25 min-vh-100 p-3" id="sidebar-wrapper" style="width: 260px;">
    <div class="sidebar-heading text-center py-3 primary-text fs-4 fw-bold text-uppercase border-bottom border-secondary border-opacity-25 mb-3">
        <i class="bi bi-disc text-primary me-2"></i><?= APP_NAME ?>
        <span class="badge bg-warning text-dark d-block fs-6 mt-1">ADMIN PANEL</span>
    </div>
    <div class="list-group list-group-flush gap-1">
        <a href="<?= ADMIN_URL ?>index.php" class="list-group-item list-group-item-action bg-transparent text-light border-0 rounded-2 py-2">
            <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard
        </a>
        <a href="<?= ADMIN_URL ?>products.php" class="list-group-item list-group-item-action bg-transparent text-light border-0 rounded-2 py-2">
            <i class="bi bi-controller me-2 text-info"></i> Products
        </a>
        <a href="<?= ADMIN_URL ?>product-add.php" class="list-group-item list-group-item-action bg-transparent text-light border-0 rounded-2 py-2 ps-4 small">
            <i class="bi bi-plus-circle me-2"></i> Add Product
        </a>
        <a href="<?= ADMIN_URL ?>categories.php" class="list-group-item list-group-item-action bg-transparent text-light border-0 rounded-2 py-2">
            <i class="bi bi-tags me-2 text-success"></i> Categories & Genres
        </a>
        <a href="<?= ADMIN_URL ?>orders.php" class="list-group-item list-group-item-action bg-transparent text-light border-0 rounded-2 py-2">
            <i class="bi bi-bag-check me-2 text-warning"></i> Orders
        </a>
        <a href="<?= ADMIN_URL ?>customers.php" class="list-group-item list-group-item-action bg-transparent text-light border-0 rounded-2 py-2">
            <i class="bi bi-people me-2 text-danger"></i> Customers
        </a>
    </div>
</div>
