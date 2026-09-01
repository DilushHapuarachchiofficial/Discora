<?php
/**
 * Discora - Reusable Sidebar & Offcanvas Product Filter Component
 * Dynamically populated from MySQL platforms & categories
 */

require_once dirname(__DIR__) . '/core/products.php';

$all_categories = get_categories_list();
$all_platforms  = get_platforms_list();

// Extract current filter state from GET
$selected_platforms  = isset($_GET['platform']) ? (array)$_GET['platform'] : [];
$selected_categories = isset($_GET['category']) ? (array)$_GET['category'] : [];
$current_search      = sanitize_input($_GET['search'] ?? '');
$current_min_price   = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? $_GET['min_price'] : '';
$current_max_price   = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? $_GET['max_price'] : '';
$current_sort        = sanitize_input($_GET['sort'] ?? 'newest');
$current_in_stock    = !empty($_GET['in_stock']);
$form_action         = htmlspecialchars($_SERVER['PHP_SELF']);
?>
<div class="filter-sidebar bg-dark p-3 p-xl-4 rounded-4 border border-secondary border-opacity-25 mb-4 shadow-sm">
    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom border-secondary border-opacity-25">
        <h5 class="text-light fw-bold mb-0 font-heading fs-5">
            <i class="bi bi-funnel text-primary me-2"></i>Filter Games
        </h5>
        <a href="<?= $form_action ?>" class="small text-secondary text-decoration-none hover-primary">Reset All</a>
    </div>

    <form action="<?= $form_action ?>" method="GET" id="catalogFilterForm">
        <!-- Preserve Search and Sort -->
        <?php if (!empty($current_search)): ?>
            <input type="hidden" name="search" value="<?= htmlspecialchars($current_search) ?>">
        <?php endif; ?>
        <?php if (!empty($current_sort)): ?>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($current_sort) ?>">
        <?php endif; ?>

        <!-- Platform Filter -->
        <div class="mb-4">
            <h6 class="text-light text-uppercase small fw-bold mb-2 font-heading tracking-wider">Gaming Platform</h6>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($all_platforms as $plat): ?>
                    <?php 
                    $is_checked = in_array($plat['slug'], $selected_platforms) || in_array($plat['platform_name'], $selected_platforms);
                    $icon = strpos(strtolower($plat['slug']), 'xbox') !== false ? 'bi-xbox text-success' : 'bi-playstation text-primary';
                    ?>
                    <div class="form-check auth-checkbox mb-0">
                        <input class="form-check-input filter-checkbox" 
                               type="checkbox" 
                               name="platform[]" 
                               value="<?= htmlspecialchars($plat['slug']) ?>" 
                               id="plat_<?= htmlspecialchars($plat['slug']) ?>"
                               <?= $is_checked ? 'checked' : '' ?>>
                        <label class="form-check-label text-secondary small d-flex justify-content-between align-items-center w-100" for="plat_<?= htmlspecialchars($plat['slug']) ?>">
                            <span><i class="bi <?= $icon ?> me-1"></i> <?= htmlspecialchars($plat['platform_name']) ?></span>
                            <span class="badge bg-secondary bg-opacity-25 text-light small"><?= $plat['product_count'] ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Category Filter -->
        <div class="mb-4">
            <h6 class="text-light text-uppercase small fw-bold mb-2 font-heading tracking-wider">Category & Genre</h6>
            <div class="d-flex flex-column gap-2">
                <?php foreach ($all_categories as $cat): ?>
                    <?php 
                    if (empty($cat['product_count'])) continue;
                    $is_cat_checked = in_array($cat['slug'], $selected_categories) || in_array($cat['category_name'], $selected_categories);
                    ?>
                    <div class="form-check auth-checkbox mb-0">
                        <input class="form-check-input filter-checkbox" 
                               type="checkbox" 
                               name="category[]" 
                               value="<?= htmlspecialchars($cat['slug']) ?>" 
                               id="cat_<?= htmlspecialchars($cat['slug']) ?>"
                               <?= $is_cat_checked ? 'checked' : '' ?>>
                        <label class="form-check-label text-secondary small d-flex justify-content-between align-items-center w-100" for="cat_<?= htmlspecialchars($cat['slug']) ?>">
                            <span><?= htmlspecialchars($cat['category_name']) ?></span>
                            <span class="badge bg-secondary bg-opacity-25 text-light small"><?= $cat['product_count'] ?></span>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Price Range Filter -->
        <div class="mb-4">
            <h6 class="text-light text-uppercase small fw-bold mb-2 font-heading tracking-wider">Price Range ($)</h6>
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-black border-secondary text-secondary">$</span>
                    <input type="number" 
                           class="form-control form-control-sm bg-black border-secondary text-light" 
                           name="min_price" 
                           placeholder="Min" 
                           min="0" 
                           value="<?= htmlspecialchars($current_min_price) ?>">
                </div>
                <span class="text-secondary">-</span>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-black border-secondary text-secondary">$</span>
                    <input type="number" 
                           class="form-control form-control-sm bg-black border-secondary text-light" 
                           name="max_price" 
                           placeholder="Max" 
                           min="0" 
                           value="<?= htmlspecialchars($current_max_price) ?>">
                </div>
            </div>
        </div>

        <!-- In Stock Only -->
        <div class="mb-4">
            <div class="form-check auth-checkbox">
                <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="filterInStock" <?= $current_in_stock ? 'checked' : '' ?>>
                <label class="form-check-label text-secondary small" for="filterInStock">
                    In Stock Copies Only
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-sm w-100 py-2 rounded-pill fw-semibold font-heading tracking-wider">
            <i class="bi bi-funnel-fill me-1"></i> Apply Filters
        </button>
    </form>
</div>
