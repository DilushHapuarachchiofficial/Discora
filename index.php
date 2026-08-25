<?php
/**
 * Discora - Physical Video Game Store Homepage
 * High-performance, fully responsive native stack implementation
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/auth.php';

$page_title = "Level Up Your Collection | PlayStation & Xbox Store";
$page_css = ['home.css'];
$page_js = ['home.js'];

require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';
require_once __DIR__ . '/includes/header.php';

// Dynamically retrieve new arrivals from MySQL discora_db
$new_arrivals_query = get_filtered_products(['is_new_arrival' => 1], 'newest', 1, 8);
$new_arrivals_products = $new_arrivals_query['products'];
?>

<!-- =========================================================
     1. HERO BANNER SECTION (#hero)
     ========================================================= -->
<section class="hero-section" id="hero">
    <div class="hero-background-wrapper">
        <img src="<?= ASSETS_PATH ?>images/banners/hero-banner.png" alt="Discora Hero Banner" class="hero-bg-img">
        <div class="hero-gradient-overlay"></div>
    </div>

    <div class="container position-relative z-3">
        <div class="hero-content">
            <h1 class="hero-title mb-3">
                LEVEL UP YOUR<br>COLLECTION
            </h1>
            <p class="hero-subtitle mb-4">
                Physical Games for<br>PlayStations &amp; Xbox
            </p>
            <div class="d-flex flex-column flex-sm-row gap-3">
                <a href="#playstation" class="btn-playstation hero-cta-btn nav-scroll-link">
                    <i class="bi bi-playstation me-2 fs-5"></i> Shop For PlayStation
                </a>
                <a href="#xbox" class="btn-xbox hero-cta-btn nav-scroll-link">
                    <i class="bi bi-xbox me-2 fs-5"></i> Shop For Xbox
                </a>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     2. NEW ARRIVALS HORIZONTAL CAROUSEL SECTION (#new-arrivals)
     ========================================================= -->
<section class="new-arrivals-section section-light" id="new-arrivals">
    <div class="container">
        <!-- Section Header Row -->
        <div class="row align-items-center mb-4 mb-lg-5 g-4">
            <div class="col-lg-4 text-center text-lg-start">
                <h2 class="section-headline mb-0 text-black">New Arrivals</h2>
            </div>
            <div class="col-lg-8 text-center text-lg-start">
                <p class="section-desc-light mb-3">
                    Discover our latest collection of exciting new game releases, carefully selected for gamers looking for fresh adventures and unforgettable experiences. Our New Arrivals section brings you the newest PS5 titles, including highly anticipated releases, action-packed adventures, immersive open-world games, and popular gaming experiences. Whether you are a fan of thrilling combat, exploration, racing, sports, or story-driven adventures, you can find something exciting to add to your collection.
                </p>
                <a href="<?= BASE_URL ?>products.php" class="btn-dark-pill">
                    Shop Now <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>

        <!-- Horizontal Multi-Item Interactive Carousel -->
        <div class="carousel-outer-wrapper">
            <!-- Navigation Arrow Buttons -->
            <button class="carousel-btn-prev" id="carouselPrevBtn" aria-label="Previous Product">
                <i class="bi bi-chevron-left fs-5"></i>
            </button>
            <button class="carousel-btn-next" id="carouselNextBtn" aria-label="Next Product">
                <i class="bi bi-chevron-right fs-5"></i>
            </button>

            <!-- Carousel Viewport & Track -->
            <div class="carousel-viewport">
                <div class="carousel-track" id="newArrivalsTrack">
                    <?php 
                    $carousel_mode = true;
                    foreach ($new_arrivals_products as $item): 
                        $product = $item;
                        require __DIR__ . '/includes/product-card.php';
                    endforeach; 
                    $carousel_mode = false;
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     3. PLAYSTATION SECTION (#playstation)
     ========================================================= -->
<section class="playstation-section section-dark" id="playstation">
    <div class="ps-ambient-glow"></div>
    <div class="container position-relative z-2">
        <div class="row align-items-center g-4 g-lg-5">
            <!-- Left: Console Showcase Image -->
            <div class="col-lg-6 text-center">
                <img src="<?= ASSETS_PATH ?>images/banners/ps.png" alt="PlayStation 5 Console & DualSense Controllers" class="img-fluid ps-showcase-img">
            </div>

            <!-- Right: Description & CTA -->
            <div class="col-lg-6 ps-content-col text-center text-lg-start">
                <h2 class="section-headline text-light mb-3 mb-lg-4">PlayStation</h2>
                <p class="section-desc-dark mb-4">
                    Discover and download your favorite PlayStation games easily from our website. Browse a wide collection of popular titles and enjoy a simple, convenient, and user-friendly gaming experience.
                </p>
                <div>
                    <a href="<?= BASE_URL ?>playstation.php" class="btn-light-pill">
                        PlayStation games <i class="bi bi-playstation ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     4. XBOX SECTION (#xbox)
     ========================================================= -->
<section class="xbox-section section-light" id="xbox">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <!-- Left on Desktop / Bottom on Mobile: Description & CTA -->
            <div class="col-lg-6 xbox-content-col text-center text-lg-start order-2 order-lg-1">
                <h2 class="section-headline text-black mb-3 mb-lg-4">Xbox one &amp; series X|S</h2>
                <p class="section-desc-light mb-4">
                    Discover the ultimate destination for Xbox gaming. Our website offers a huge collection of Xbox One and Xbox Series X|S games, from the latest releases to all-time favorites. Browse, choose, and get your games quickly with a smooth, secure, and user-friendly experience designed for every Xbox gamer.
                </p>
                <div>
                    <a href="<?= BASE_URL ?>xbox.php" class="btn-dark-pill">
                        Xbox games <i class="bi bi-xbox ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Right on Desktop / Top on Mobile: Console Showcase Image -->
            <div class="col-lg-6 text-center order-1 order-lg-2">
                <img src="<?= ASSETS_PATH ?>images/banners/xbox.png" alt="Xbox Series X and Series S Consoles" class="img-fluid xbox-showcase-img">
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     5. ABOUT US SECTION (#about-us)
     ========================================================= -->
<section class="about-section section-dark" id="about-us">
    <div class="container">
        <!-- Section Top Headline -->
        <div class="text-center mb-4 mb-lg-5">
            <h2 class="section-headline text-light about-header-badge">About us</h2>
        </div>

        <!-- Main Content Row -->
        <div class="row align-items-center g-4 g-lg-5 mb-5">
            <div class="col-lg-5 text-center text-lg-start">
                <h3 class="about-punchline text-light mb-0">
                    Your Game.<br>
                    <span class="accent-underline">Our</span> Passion.
                </h3>
            </div>
            <div class="col-lg-7 text-center text-lg-start">
                <p class="section-desc-dark mb-3">
                    Discora is your ultimate destination for PlayStation and Xbox physical games. We bring you the best titles, genuine products, and a smooth shopping experience.
                </p>
                <p class="section-desc-dark mb-0">
                    Our mission is simple - to connect gamers with the games they love, with great prices, fast delivery, and trusted services.
                </p>
            </div>
        </div>

        <!-- 3 Feature Outline Cards Row -->
        <div class="row g-3 g-md-4 features-row">
            <div class="col-12 col-md-4 feature-col">
                <div class="feature-outline-card">
                    <div class="mb-3 text-light fs-1">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold text-light mb-2 font-heading">100% Genuine Products</h5>
                    <p class="text-secondary small mb-0">Guaranteed authentic physical discs and official accessories straight from publishers.</p>
                </div>
            </div>

            <div class="col-12 col-md-4 feature-col">
                <div class="feature-outline-card">
                    <div class="mb-3 text-light fs-1">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h5 class="fw-bold text-light mb-2 font-heading">Fast &amp; Secure Delivery</h5>
                    <p class="text-secondary small mb-0">Carefully packaged physical media delivered swiftly right to your doorstep.</p>
                </div>
            </div>

            <div class="col-12 col-md-4 feature-col">
                <div class="feature-outline-card">
                    <div class="mb-3 text-light fs-1">
                        <i class="bi bi-hand-thumbs-up"></i>
                    </div>
                    <h5 class="fw-bold text-light mb-2 font-heading">Customer Satisfaction</h5>
                    <p class="text-secondary small mb-0">Dedicated gaming customer support and flexible warranty on hardware and discs.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================================
     6. CONTACT US SECTION (#contact-us)
     ========================================================= -->
<section class="contact-section section-light" id="contact-us">
    <div class="container">
        <!-- Section Top Headline -->
        <div class="text-center mb-4 mb-lg-5">
            <h2 class="section-headline text-black contact-header-badge">Contact us</h2>
        </div>

        <!-- Main Content Row -->
        <div class="row align-items-center g-4 g-lg-5 mb-5">
            <div class="col-lg-5 text-center text-lg-start">
                <h3 class="contact-punchline text-black mb-0">
                    We're Here<br>
                    <span class="accent-underline">To</span> Help!
                </h3>
            </div>
            <div class="col-lg-7 text-center text-lg-start">
                <p class="section-desc-light mb-0">
                    Have questions about our games, services, or your order? The Discora team is always ready to help. Whether you need assistance choosing a game, have a technical issue, or simply want more information, feel free to reach out to us. Send us a message through our contact form, and we'll do our best to get back to you as soon as possible. Your gaming experience matters to us!
                </p>
            </div>
        </div>

        <!-- 3 Contact Cards Row -->
        <div class="row g-3 g-md-4 contact-info-row">
            <div class="col-12 col-md-4 contact-info-col">
                <div class="contact-info-card">
                    <div class="mb-3 text-dark fs-1">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1 font-heading">Our Location</h5>
                    <p class="text-muted small mb-0">No.123 Gamer Street, Colombo 07</p>
                </div>
            </div>

            <div class="col-12 col-md-4 contact-info-col">
                <div class="contact-info-card">
                    <div class="mb-3 text-dark fs-1">
                        <i class="bi bi-telephone"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1 font-heading">Phone Number</h5>
                    <p class="text-muted small mb-0">+94 71 234 5678</p>
                </div>
            </div>

            <div class="col-12 col-md-4 contact-info-col">
                <div class="contact-info-card">
                    <div class="mb-3 text-dark fs-1">
                        <i class="bi bi-envelope"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1 font-heading">Email Address</h5>
                    <p class="text-muted small mb-0">support@discora.lk</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
