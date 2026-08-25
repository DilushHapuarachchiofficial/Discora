<?php
/**
 * Discora - Main Responsive Navigation Bar & GSAP Mobile Side Navigation Drawer
 */
require_once dirname(__DIR__) . '/core/cart.php';
$cart_data = get_current_cart_items();
$cart_count = $cart_data['total_quantity'];
$user = current_user();
?>
<!-- Main Top Header Navigation -->
<header class="site-header sticky-top" id="siteHeader">
    <nav class="navbar navbar-expand-lg navbar-dark main-navbar py-2 py-lg-3">
        <div class="container d-flex align-items-center justify-content-between">
            
            <!-- Brand Logo -->
            <a class="navbar-brand d-flex align-items-center brand-logo py-0" href="<?= BASE_URL ?>">
                <img src="<?= ASSETS_PATH ?>images/logos/discora-logo.png" alt="Discora Logo" class="brand-img">
            </a>

            <!-- Desktop Nav Links (Hidden on Mobile/Tablet) -->
            <div class="d-none d-lg-flex align-items-center justify-content-center flex-grow-1 mx-3 mx-xl-4">
                <ul class="navbar-nav flex-row gap-xl-4 gap-lg-3 align-items-center desktop-nav-list mb-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>new-arrivals.php">New Arrivals</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>products.php">Products</a>
                    </li>

                    <!-- PlayStation Dropdown -->
                    <li class="nav-item dropdown dropdown-hover">
                        <a class="nav-link d-flex align-items-center" href="<?= BASE_URL ?>playstation.php" id="psDropdown" role="button" aria-expanded="false">
                            PlayStation
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark discora-dropdown-menu shadow-lg" aria-labelledby="psDropdown">
                            <li><a class="dropdown-item d-flex align-items-center justify-content-between" href="<?= BASE_URL ?>ps5.php"><span>PlayStation 5 (PS5)</span> <i class="bi bi-playstation text-primary"></i></a></li>
                            <li><a class="dropdown-item d-flex align-items-center justify-content-between" href="<?= BASE_URL ?>ps4.php"><span>PlayStation 4 (PS4)</span> <i class="bi bi-playstation text-secondary"></i></a></li>
                        </ul>
                    </li>

                    <!-- Xbox Dropdown -->
                    <li class="nav-item dropdown dropdown-hover">
                        <a class="nav-link d-flex align-items-center" href="<?= BASE_URL ?>xbox.php" id="xboxDropdown" role="button" aria-expanded="false">
                            Xbox
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark discora-dropdown-menu shadow-lg" aria-labelledby="xboxDropdown">
                            <li><a class="dropdown-item d-flex align-items-center justify-content-between" href="<?= BASE_URL ?>xbox-series.php"><span>Xbox Series X|S</span> <i class="bi bi-xbox text-success"></i></a></li>
                            <li><a class="dropdown-item d-flex align-items-center justify-content-between" href="<?= BASE_URL ?>xbox-one.php"><span>Xbox One</span> <i class="bi bi-xbox text-secondary"></i></a></li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link nav-scroll-link" href="<?= BASE_URL ?>#about-us">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-scroll-link" href="<?= BASE_URL ?>#contact-us">Contact Us</a>
                    </li>
                </ul>
            </div>

            <!-- Right Action Icons (Search, Cart, User, Mobile Hamburger) -->
            <div class="d-flex align-items-center gap-3 header-actions">
                <!-- Search Button / Modal Trigger -->
                <button type="button" class="btn btn-icon-link text-light p-1" data-bs-toggle="modal" data-bs-target="#searchModal" title="Search Games" aria-label="Search">
                    <i class="bi bi-search fs-5"></i>
                </button>

                <!-- Cart Button -->
                <a href="<?= BASE_URL ?>cart.php" class="btn btn-icon-link text-light p-1 position-relative" title="Shopping Cart" aria-label="Cart">
                    <i class="bi bi-cart3 fs-5"></i>
                    <span id="cart-badge-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-dark">
                        <?= $cart_count ?>
                    </span>
                </a>

                <!-- User Profile / Auth Button -->
                <?php if (is_logged_in()): ?>
                    <div class="dropdown d-none d-sm-block">
                        <button class="btn btn-icon-link text-light p-1 dropdown-toggle dropdown-no-arrow" type="button" id="userMenuBtn" data-bs-toggle="dropdown" aria-expanded="false" title="<?= htmlspecialchars($user['name']) ?>">
                            <i class="bi bi-person-circle fs-5 text-primary"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark discora-dropdown-menu shadow" aria-labelledby="userMenuBtn">
                            <li class="px-3 py-2 border-bottom border-secondary border-opacity-25">
                                <span class="small text-muted d-block">Signed in as</span>
                                <strong class="text-light"><?= htmlspecialchars($user['name']) ?></strong>
                            </li>
                            <?php if (is_admin()): ?>
                                <li><a class="dropdown-item text-warning" href="<?= ADMIN_URL ?>"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>account.php"><i class="bi bi-person me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?= BASE_URL ?>orders.php"><i class="bi bi-bag-check me-2"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <button type="button" class="btn btn-icon-link text-light p-1 d-none d-sm-inline-block" title="Login / Register" aria-label="Account" data-auth-modal data-auth-tab="login">
                        <i class="bi bi-person fs-5"></i>
                    </button>
                <?php endif; ?>

                <!-- Mobile Hamburger Button -->
                <button class="btn btn-hamburger d-lg-none text-light p-1 ms-1" id="mobileMenuToggle" type="button" aria-label="Open Navigation Menu">
                    <i class="bi bi-list fs-1"></i>
                </button>
            </div>
        </div>
    </nav>
</header>

<!-- Mobile Side Navigation Drawer (GSAP Animated) -->
<div class="mobile-side-nav" id="mobileSideNav" aria-hidden="true">
    <!-- Side Nav Header -->
    <div class="side-nav-header d-flex align-items-center justify-content-between p-3 p-sm-4 border-bottom border-secondary border-opacity-25">
        <a class="d-flex align-items-center text-decoration-none" href="<?= BASE_URL ?>">
            <img src="<?= ASSETS_PATH ?>images/logos/discora-logo.png" alt="Discora" class="brand-img-mobile">
        </a>
        <button class="btn btn-close-side-nav text-light p-2" id="sideNavCloseBtn" aria-label="Close Navigation Menu">
            <i class="bi bi-x-lg fs-4"></i>
        </button>
    </div>

    <!-- Side Nav Content -->
    <div class="side-nav-body p-3 p-sm-4 d-flex flex-column justify-content-between">
        <ul class="nav flex-column side-nav-links gap-2">
            <li class="side-nav-item">
                <a class="side-nav-link" href="<?= BASE_URL ?>">
                    <i class="bi bi-house-door me-2 text-primary"></i> Home
                </a>
            </li>
            <li class="side-nav-item">
                <a class="side-nav-link" href="<?= BASE_URL ?>new-arrivals.php">
                    <i class="bi bi-stars me-2 text-danger"></i> New Arrivals
                </a>
            </li>
            <li class="side-nav-item">
                <a class="side-nav-link" href="<?= BASE_URL ?>products.php">
                    <i class="bi bi-grid me-2 text-info"></i> All Products
                </a>
            </li>

            <!-- Mobile PlayStation Accordion -->
            <li class="side-nav-item side-nav-accordion">
                <button class="side-nav-link side-accordion-toggle d-flex align-items-center justify-content-between w-100" type="button" data-bs-toggle="collapse" data-bs-target="#mobilePsMenu" aria-expanded="false">
                    <span><i class="bi bi-playstation me-2 text-primary"></i> PlayStation</span>
                    <i class="bi bi-chevron-down accordion-arrow transition-icon"></i>
                </button>
                <div class="collapse side-sub-menu" id="mobilePsMenu">
                    <ul class="list-unstyled ps-4 pt-2 mb-0 d-flex flex-column gap-2">
                        <li><a href="<?= BASE_URL ?>ps5.php" class="side-sub-link"><i class="bi bi-disc me-2 text-primary"></i> PS5 Games & Discs</a></li>
                        <li><a href="<?= BASE_URL ?>ps4.php" class="side-sub-link"><i class="bi bi-disc me-2 text-secondary"></i> PS4 Games & Discs</a></li>
                        <li><a href="<?= BASE_URL ?>playstation.php" class="side-sub-link"><i class="bi bi-arrow-right-short me-1"></i> PlayStation Hub</a></li>
                    </ul>
                </div>
            </li>

            <!-- Mobile Xbox Accordion -->
            <li class="side-nav-item side-nav-accordion">
                <button class="side-nav-link side-accordion-toggle d-flex align-items-center justify-content-between w-100" type="button" data-bs-toggle="collapse" data-bs-target="#mobileXboxMenu" aria-expanded="false">
                    <span><i class="bi bi-xbox me-2 text-success"></i> Xbox</span>
                    <i class="bi bi-chevron-down accordion-arrow transition-icon"></i>
                </button>
                <div class="collapse side-sub-menu" id="mobileXboxMenu">
                    <ul class="list-unstyled ps-4 pt-2 mb-0 d-flex flex-column gap-2">
                        <li><a href="<?= BASE_URL ?>xbox-series.php" class="side-sub-link"><i class="bi bi-disc me-2 text-success"></i> Xbox Series X|S Games</a></li>
                        <li><a href="<?= BASE_URL ?>xbox-one.php" class="side-sub-link"><i class="bi bi-disc me-2 text-secondary"></i> Xbox One Games</a></li>
                        <li><a href="<?= BASE_URL ?>xbox.php" class="side-sub-link"><i class="bi bi-arrow-right-short me-1"></i> Xbox Hub</a></li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a class="side-nav-link nav-scroll-link" href="<?= BASE_URL ?>#about-us">
                    <i class="bi bi-info-circle me-2 text-light"></i> About Us
                </a>
            </li>
            <li class="side-nav-item">
                <a class="side-nav-link nav-scroll-link" href="<?= BASE_URL ?>#contact-us">
                    <i class="bi bi-envelope me-2 text-light"></i> Contact Us
                </a>
            </li>
        </ul>

        <!-- Mobile User & Cart Footers -->
        <div class="side-nav-footer pt-3 border-top border-secondary border-opacity-25 mt-3">
            <div class="d-grid gap-2">
                <a href="<?= BASE_URL ?>cart.php" class="btn btn-outline-light d-flex align-items-center justify-content-between py-2">
                    <span><i class="bi bi-cart3 me-2"></i> Shopping Cart</span>
                    <span class="badge bg-danger rounded-pill"><?= $cart_count ?></span>
                </a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= BASE_URL ?>account.php" class="btn btn-primary d-flex align-items-center justify-content-center gap-2 py-2">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['name']) ?>
                    </a>
                    <a href="<?= BASE_URL ?>logout.php" class="btn btn-sm btn-outline-danger py-1 mt-1">Logout</a>
                <?php else: ?>
                    <button type="button" class="btn btn-primary py-2 fw-semibold" data-auth-modal data-auth-tab="login">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login / Register
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Backdrop Overlay for Mobile Side Nav -->
<div class="side-nav-overlay" id="sideNavOverlay"></div>

<!-- Global Quick Search Modal -->
<div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered px-3">
        <div class="modal-content bg-dark text-light border border-secondary border-opacity-50 shadow-lg rounded-4">
            <div class="modal-header border-bottom border-secondary border-opacity-25 py-3">
                <h5 class="modal-title fs-6 text-uppercase fw-semibold" id="searchModalLabel"><i class="bi bi-search me-2 text-primary"></i>Search Physical Games & Consoles</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-sm-4">
                <form action="<?= BASE_URL ?>products.php" method="GET">
                    <div class="input-group input-group-lg">
                        <input type="search" name="search" class="form-control bg-black border-secondary text-light" placeholder="Search PS5, Xbox, Spider-Man..." autofocus required>
                        <button class="btn btn-primary px-3 px-sm-4" type="submit"><i class="bi bi-arrow-right fs-5"></i></button>
                    </div>
                </form>
                <div class="quick-tags mt-3 d-flex flex-wrap gap-2 align-items-center">
                    <span class="small text-muted">Popular:</span>
                    <a href="<?= BASE_URL ?>products.php?search=Spider-Man" class="badge bg-secondary text-decoration-none bg-opacity-25 text-light hover-primary">Spider-Man</a>
                    <a href="<?= BASE_URL ?>products.php?search=Forza" class="badge bg-secondary text-decoration-none bg-opacity-25 text-light hover-primary">Forza</a>
                    <a href="<?= BASE_URL ?>products.php?search=God+of+War" class="badge bg-secondary text-decoration-none bg-opacity-25 text-light hover-primary">God of War</a>
                    <a href="<?= BASE_URL ?>products.php?platform[]=ps5" class="badge bg-secondary text-decoration-none bg-opacity-25 text-light hover-primary">PS5 Games</a>
                    <a href="<?= BASE_URL ?>products.php?platform[]=xbox-series" class="badge bg-secondary text-decoration-none bg-opacity-25 text-light hover-primary">Xbox Series X</a>
                </div>
            </div>
        </div>
    </div>
</div>
