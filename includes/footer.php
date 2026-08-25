<?php
/**
 * Discora - Shared Frontend Footer Component
 * Expects optional variable: $page_js (array of custom page JS files)
 */
?>
<footer class="site-footer bg-black text-secondary border-top border-secondary border-opacity-25 pt-5 pb-4" id="siteFooter">
    <div class="container">
        <div class="row g-4 mb-5">
            <!-- Brand Column -->
            <div class="col-lg-3 col-md-6">
                <a class="d-flex align-items-center gap-2 text-decoration-none text-light mb-3" href="<?= BASE_URL ?>">
                    <img src="<?= ASSETS_PATH ?>images/logos/discora-logo.png" alt="Discora" height="38">
                    <span class="fw-bold text-uppercase tracking-wider fs-4 brand-text"><?= APP_NAME ?></span>
                </a>
                <p class="small text-secondary mb-4 leading-relaxed">
                    Discora is your dedicated destination for physical PlayStation & Xbox games, next-gen consoles, and premium gaming gear. Level up your physical collection today.
                </p>
                <div class="d-flex gap-3 social-links">
                    <a href="https://facebook.com" target="_blank" rel="noopener" class="text-secondary hover-light" aria-label="Facebook"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="https://instagram.com" target="_blank" rel="noopener" class="text-secondary hover-light" aria-label="Instagram"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="https://twitter.com" target="_blank" rel="noopener" class="text-secondary hover-light" aria-label="Twitter"><i class="bi bi-twitter-x fs-5"></i></a>
                    <a href="https://discord.com" target="_blank" rel="noopener" class="text-secondary hover-light" aria-label="Discord"><i class="bi bi-discord fs-5"></i></a>
                    <a href="https://youtube.com" target="_blank" rel="noopener" class="text-secondary hover-light" aria-label="YouTube"><i class="bi bi-youtube fs-5"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-light text-uppercase fw-bold mb-3 font-heading tracking-wider">Quick Links</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>#hero" class="text-secondary text-decoration-none hover-light nav-scroll-link">Home</a></li>
                    <li><a href="<?= BASE_URL ?>#new-arrivals" class="text-secondary text-decoration-none hover-light nav-scroll-link">New Arrivals</a></li>
                    <li><a href="<?= BASE_URL ?>products.php" class="text-secondary text-decoration-none hover-light">Products</a></li>
                    <li><a href="<?= BASE_URL ?>#about-us" class="text-secondary text-decoration-none hover-light nav-scroll-link">About Us</a></li>
                    <li><a href="<?= BASE_URL ?>#contact-us" class="text-secondary text-decoration-none hover-light nav-scroll-link">Contact Us</a></li>
                </ul>
            </div>

            <!-- PlayStation Category -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-light text-uppercase fw-bold mb-3 font-heading tracking-wider">PlayStation</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>playstation.php?platform=ps4" class="text-secondary text-decoration-none hover-light">PS4 Games & Discs</a></li>
                    <li><a href="<?= BASE_URL ?>playstation.php?platform=ps5" class="text-secondary text-decoration-none hover-light">PS5 Games & Discs</a></li>
                    <li><a href="<?= BASE_URL ?>#playstation" class="text-secondary text-decoration-none hover-light nav-scroll-link">PS5 Hardware Showcase</a></li>
                </ul>
            </div>

            <!-- Xbox Category -->
            <div class="col-lg-2 col-md-6 col-6">
                <h6 class="text-light text-uppercase fw-bold mb-3 font-heading tracking-wider">Xbox</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>xbox.php?platform=xbox-one" class="text-secondary text-decoration-none hover-light">Xbox One Games</a></li>
                    <li><a href="<?= BASE_URL ?>xbox.php?platform=xbox-series" class="text-secondary text-decoration-none hover-light">Xbox Series X|S Games</a></li>
                    <li><a href="<?= BASE_URL ?>#xbox" class="text-secondary text-decoration-none hover-light nav-scroll-link">Xbox Hardware Showcase</a></li>
                </ul>
            </div>

            <!-- Customer Support -->
            <div class="col-lg-3 col-md-6 col-6">
                <h6 class="text-light text-uppercase fw-bold mb-3 font-heading tracking-wider">Customer Support</h6>
                <ul class="list-unstyled small d-flex flex-column gap-2">
                    <li><a href="<?= BASE_URL ?>#contact-us" class="text-secondary text-decoration-none hover-light nav-scroll-link">Contact Support</a></li>
                    <li><a href="<?= BASE_URL ?>contact.php#faq" class="text-secondary text-decoration-none hover-light">FAQ</a></li>
                    <li><a href="<?= BASE_URL ?>about.php#shipping" class="text-secondary text-decoration-none hover-light">Shipping Information</a></li>
                    <li><a href="<?= BASE_URL ?>about.php#returns" class="text-secondary text-decoration-none hover-light">Returns & Refunds</a></li>
                </ul>
            </div>
        </div>

        <hr class="border-secondary border-opacity-25 my-4">

        <!-- Bottom Copyright & Badges -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary gap-3">
            <p class="mb-0">&copy; 2026 <?= APP_NAME ?>. All Rights Reserved.</p>
            <div class="d-flex gap-4">
                <span><i class="bi bi-shield-check text-primary me-1"></i> Authentic Physical Discs</span>
                <span><i class="bi bi-truck text-success me-1"></i> Fast Island-wide Delivery</span>
            </div>
        </div>
    </div>
</footer>

<!-- Discora Global Authentication Popup Modal -->
<?php require_once INCLUDES_PATH . 'auth-modal.php'; ?>

<!-- Bootstrap 5.3.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- GSAP 3.12.5 & ScrollTrigger Plugins -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>

<!-- Universal Site JavaScript -->
<script src="<?= ASSETS_PATH ?>js/main.js?v=<?= time() ?>"></script>
<script src="<?= ASSETS_PATH ?>js/animations.js?v=<?= time() ?>"></script>
<script src="<?= ASSETS_PATH ?>js/cart.js?v=<?= time() ?>"></script>
<script src="<?= ASSETS_PATH ?>js/auth.js?v=<?= time() ?>"></script>

<!-- Page Specific JavaScript Files -->
<?php if (isset($page_js) && is_array($page_js)): ?>
    <?php foreach ($page_js as $js): ?>
        <script src="<?= ASSETS_PATH ?>js/pages/<?= htmlspecialchars($js) ?>?v=<?= time() ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
