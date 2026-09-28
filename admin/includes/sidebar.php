<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="admin-sidebar bg-black text-white p-3 d-flex flex-column" id="adminSidebar">
    <div class="sidebar-header position-relative d-flex align-items-center justify-content-center mb-4 mt-2">
        <a href="index.php" class="text-white text-decoration-none d-flex align-items-center justify-content-center gap-2 w-100">
            <img src="<?= ASSETS_PATH ?>images/logos/discora-logo.png" alt="Discora Icon" style="height: 30px; width: 30px; object-fit: cover; object-position: left;" class="d-none logo-icon">
            <img src="<?= ASSETS_PATH ?>images/logos/discora-logo.png" alt="Discora Logo" style="height: 30px;" class="logo-img">
        </a>
        <button class="btn btn-sm btn-link text-white d-lg-none position-absolute end-0" id="closeSidebar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <ul class="nav flex-column flex-nowrap gap-2 mb-auto sidebar-nav">
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="index.php" class="nav-link text-white <?= $current_page == 'index.php' ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> <span>Dashboard</span>
            </a>
        </li>
        
        <?php if (has_permission('products') || has_permission('categories') || has_permission('platforms')): ?>
        <li class="nav-item mt-3 mb-1 text-secondary small fw-bold text-uppercase px-3 sidebar-section-title">Catalogue</li>
        <?php endif; ?>

        <?php if (has_permission('products')): ?>
        <!-- Products -->
        <li class="nav-item">
            <a href="products.php" class="nav-link text-white <?= in_array($current_page, ['products.php', 'product-add.php', 'product-edit.php']) ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-controller me-2"></i> <span>Products</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('categories')): ?>
        <!-- Categories -->
        <li class="nav-item">
            <a href="categories.php" class="nav-link text-white <?= $current_page == 'categories.php' ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-tags me-2"></i> <span>Categories</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('platforms')): ?>
        <!-- Platforms -->
        <li class="nav-item">
            <a href="platforms.php" class="nav-link text-white <?= $current_page == 'platforms.php' ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-pc-display me-2"></i> <span>Platforms</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('orders') || has_permission('invoices') || has_permission('reports')): ?>
        <li class="nav-item mt-3 mb-1 text-secondary small fw-bold text-uppercase px-3 sidebar-section-title">Sales</li>
        <?php endif; ?>

        <?php if (has_permission('orders')): ?>
        <!-- Orders -->
        <li class="nav-item">
            <a href="orders.php" class="nav-link text-white <?= in_array($current_page, ['orders.php', 'order-details.php']) ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-cart3 me-2"></i> <span>Orders</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('invoices')): ?>
        <!-- Invoices -->
        <li class="nav-item">
            <a href="invoices.php" class="nav-link text-white <?= in_array($current_page, ['invoices.php', 'invoice.php']) ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-receipt me-2"></i> <span>Invoices</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('reports')): ?>
        <!-- Reports -->
        <li class="nav-item">
            <a href="reports.php" class="nav-link text-white <?= $current_page == 'reports.php' ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-graph-up me-2"></i> <span>Reports</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('customers') || has_permission('reviews')): ?>
        <li class="nav-item mt-3 mb-1 text-secondary small fw-bold text-uppercase px-3 sidebar-section-title">Customers</li>
        <?php endif; ?>

        <?php if (has_permission('customers')): ?>
        <!-- Customers -->
        <li class="nav-item">
            <a href="customers.php" class="nav-link text-white <?= in_array($current_page, ['customers.php', 'customer-details.php']) ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-people me-2"></i> <span>Customers</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('reviews')): ?>
        <!-- Reviews -->
        <li class="nav-item">
            <a href="reviews.php" class="nav-link text-white <?= $current_page == 'reviews.php' ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-star me-2"></i> <span>Reviews</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (has_permission('messages') || is_superadmin()): ?>
        <li class="nav-item mt-3 mb-1 text-secondary small fw-bold text-uppercase px-3 sidebar-section-title">System</li>
        <?php endif; ?>

        <?php if (has_permission('messages')): ?>
        <!-- Messages -->
        <li class="nav-item">
            <a href="messages.php" class="nav-link text-white <?= $current_page == 'messages.php' ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-envelope me-2"></i> <span>Messages</span>
            </a>
        </li>
        <?php endif; ?>

        <?php if (is_superadmin()): ?>
        <!-- Admins -->
        <li class="nav-item">
            <a href="admins.php" class="nav-link text-white <?= in_array($current_page, ['admins.php', 'admin-add.php', 'admin-edit.php']) ? 'active bg-primary bg-opacity-25 rounded' : '' ?>">
                <i class="bi bi-shield-lock me-2"></i> <span>Admins</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <hr class="text-secondary">
    <div class="d-flex align-items-center gap-2 admin-profile-sidebar">
        <?php if (!empty($_SESSION['user_avatar'])): ?>
            <img src="<?= BASE_URL . htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Admin Avatar" class="rounded-circle border border-secondary" style="width: 36px; height: 36px; object-fit: cover;">
        <?php else: ?>
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                <i class="bi bi-person-fill"></i>
            </div>
        <?php endif; ?>
        <div class="user-info">
            <div class="fw-bold fs-6 lh-1"><?= htmlspecialchars($_SESSION['user_name'] ?? $_SESSION['full_name'] ?? 'Admin') ?></div>
            <a href="logout.php" class="small text-danger text-decoration-none">Logout</a>
        </div>
    </div>
</div>
