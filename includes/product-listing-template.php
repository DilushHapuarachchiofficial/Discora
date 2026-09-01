<?php
/**
 * Reusable Product Listing Template
 * Expects the following variables to be set before inclusion:
 * - $catalog_products: array of product records
 * - $total_products: int total number of products matched
 * - $total_pages: int
 * - $current_page: int
 * - $paginationUrl: string URL base for pagination links
 * - $page_heading: string (e.g., "NEW ARRIVALS")
 * - $page_subheading: string (e.g., "HOT RELEASES")
 * - $page_description: string
 * - $badge_text: string (e.g., "JUST DROPPED & PRE-ORDERS")
 * - $badge_icon: string (e.g., "bi-fire")
 * - $empty_state_title: string (e.g., "NO NEW ARRIVALS FOUND")
 * - $empty_state_desc: string
 * - $empty_state_btn: string
 * - $empty_state_url: string
 * - $page_url: string (for sort links)
 * - $sort: string (current sort)
 */
?>
<!-- Entire Page Wrapper with White Light Theme -->
<div class="new-arrivals-page-wrapper py-4 py-lg-5">
    <div class="container-fluid px-3 px-lg-4 px-xl-5">
        
        <!-- HERO / HEADER SECTION -->
        <div class="new-arrivals-hero p-4 p-md-5 mb-4 mb-lg-5 position-relative overflow-hidden shadow-sm">
            <!-- GSAP Background Geometric Shapes & Game Disc Motifs -->
            <div class="hero-bg-shapes">
                <div class="disc-shape disc-shape-1"></div>
                <div class="disc-shape disc-shape-2"></div>
                <div class="disc-shape disc-shape-3"></div>
                <div class="hero-grid-overlay"></div>
            </div>

            <div class="row align-items-center position-relative z-2">
                <div class="col-12 hero-text-col">
                    <?php if (!empty($badge_text)): ?>
                    <div class="d-inline-flex align-items-center gap-2 px-3 py-1-5 rounded-pill bg-danger bg-opacity-25 border border-danger border-opacity-50 text-white fw-bold small mb-3 hero-badge shadow-sm">
                        <i class="bi <?= htmlspecialchars($badge_icon ?? 'bi-fire') ?> text-danger"></i> <?= htmlspecialchars($badge_text) ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Main & Secondary Headings -->
                    <h1 class="display-4 fw-extrabold text-white font-heading mb-1 hero-title tracking-tight">
                        <?= htmlspecialchars($page_heading) ?>
                    </h1>
                    <h2 class="display-6 fw-bold text-danger font-heading mb-3 hero-subtitle">
                        <?= htmlspecialchars($page_subheading) ?>
                    </h2>
                    
                    <p class="text-light lead fs-5 mb-4 hero-desc max-w-2xl">
                        <?= htmlspecialchars($page_description) ?>
                    </p>

                    <!-- Key Physical Game Store Badges -->
                    <div class="d-flex align-items-center gap-3 flex-wrap hero-stats">
                        <div class="d-flex align-items-center gap-2 px-3 py-2 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-25 shadow-sm text-white">
                            <i class="bi bi-disc text-danger fs-5"></i>
                            <span class="fw-semibold fs-6">
                                <strong class="text-danger"><?= $total_products ?></strong> Physical Drops
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2 px-3 py-2 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-25 shadow-sm text-white">
                            <i class="bi bi-shield-check text-primary fs-5"></i>
                            <span class="fw-semibold fs-6">100% Factory Sealed</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 px-3 py-2 bg-dark bg-opacity-50 rounded-3 border border-secondary border-opacity-25 shadow-sm text-white">
                            <i class="bi bi-truck text-success fs-5"></i>
                            <span class="fw-semibold fs-6">Fast Delivery</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Filters & Toolbar -->
        <div class="row g-4">
            
            <!-- Sidebar Filters Column -->
            <div class="col-lg-3 d-none d-lg-block">
                <?php require INCLUDES_PATH . 'sidebar-filters.php'; ?>
            </div>

            <!-- Product Grid & Toolbar Column -->
            <div class="col-lg-9">
                
                <!-- Toolbar -->
                <div class="catalog-toolbar d-flex flex-wrap justify-content-between align-items-center gap-3 p-3 bg-white rounded-3 border border-gray-200 mb-4 shadow-sm">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-outline-dark btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileFilterOffcanvas">
                            <i class="bi bi-funnel me-1"></i> Filters
                        </button>
                        <span class="text-secondary small fw-medium">
                            Showing <strong class="text-dark"><?= count($catalog_products) ?></strong> physical game releases
                        </span>
                    </div>

                    <!-- Sorting Dropdown -->
                    <div class="d-flex align-items-center gap-2">
                        <label for="catalogSortSelect" class="text-secondary small mb-0 font-heading fw-bold">SORT BY:</label>
                        <select id="catalogSortSelect" class="form-select form-select-sm bg-light border-gray-300 text-dark w-auto fw-medium" onchange="location = this.value;">
                            <?php
                            $sorts = [
                                'newest'     => 'Newest First',
                                'featured'   => 'Featured First',
                                'price_low'  => 'Price: Low to High',
                                'price_high' => 'Price: High to Low',
                                'name_asc'   => 'Name: A to Z',
                                'name_desc'  => 'Name: Z to A'
                            ];
                            foreach ($sorts as $k => $label) {
                                $query = $_GET;
                                $query['sort'] = $k;
                                $query['page'] = 1;
                                $url = ($page_url ?? 'products.php') . '?' . http_build_query($query);
                                $selected = ($sort === $k) ? 'selected' : '';
                                echo "<option value=\"$url\" $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (!empty($catalog_products)): ?>
                    <div class="row g-4 product-grid" id="productGridContainer">
                        <?php foreach ($catalog_products as $product): ?>
                            <?php require INCLUDES_PATH . 'product-card.php'; ?>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Catalog Pagination" class="mt-5">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link bg-white border-gray-300 text-dark shadow-sm" href="<?= $paginationUrl . ($current_page - 1) ?>">
                                        <i class="bi bi-chevron-left"></i> Prev
                                    </a>
                                </li>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?= ($i === $current_page) ? 'active' : '' ?>">
                                        <a class="page-link <?= ($i === $current_page) ? 'bg-danger border-danger text-white' : 'bg-white border-gray-300 text-dark shadow-sm' ?>" href="<?= $paginationUrl . $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                                    <a class="page-link bg-white border-gray-300 text-dark shadow-sm" href="<?= $paginationUrl . ($current_page + 1) ?>">
                                        Next <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>

                <?php else: ?>
                    <div class="text-center py-5 my-5 bg-white rounded-4 border border-gray-200 p-4 shadow-sm">
                        <i class="bi bi-disc fs-1 text-danger mb-3 d-block"></i>
                        <h3 class="text-dark fw-bold font-heading"><?= htmlspecialchars($empty_state_title) ?></h3>
                        <p class="text-secondary mb-4"><?= htmlspecialchars($empty_state_desc) ?></p>
                        <a href="<?= htmlspecialchars($empty_state_url) ?>" class="btn btn-danger rounded-pill px-4">
                            <i class="bi bi-arrow-clockwise me-1"></i> <?= htmlspecialchars($empty_state_btn) ?>
                        </a>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Mobile Filter Offcanvas Drawer -->
<div class="offcanvas offcanvas-start bg-white text-dark" tabindex="-1" id="mobileFilterOffcanvas" aria-labelledby="mobileFilterOffcanvasLabel">
    <div class="offcanvas-header border-bottom border-gray-200">
        <h5 class="offcanvas-title font-heading fs-5 text-dark" id="mobileFilterOffcanvasLabel"><i class="bi bi-funnel text-danger me-2"></i>Filter Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <?php require INCLUDES_PATH . 'sidebar-filters.php'; ?>
    </div>
</div>
