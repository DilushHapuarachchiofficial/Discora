<?php
/**
 * Discora - Reusable Gaming Product Card Component
 * Expects $product associative array populated from MySQL discora_db
 */
if (!isset($product)) return;

$platform_name = $product['platform_name'] ?? 'Console';
$platform_slug = strtolower($product['platform_slug'] ?? $product['platform'] ?? '');

$is_playstation = (strpos($platform_slug, 'ps') !== false || strpos($platform_slug, 'playstation') !== false);
$badge_class = $is_playstation ? 'badge-playstation' : 'badge-xbox';
$platform_icon = $is_playstation ? 'bi-playstation' : 'bi-xbox';

$price = (float)($product['price'] ?? 0.00);
$discount_price = !empty($product['discount_price']) ? (float)$product['discount_price'] : null;
$effective_price = $discount_price ?: $price;

$discount_percent = ($discount_price && $discount_price < $price) 
    ? round((($price - $discount_price) / $price) * 100) 
    : 0;

$stock_qty = (int)($product['stock_quantity'] ?? 0);
$is_out_of_stock = ($stock_qty <= 0);

$image_src = !empty($product['primary_image']) ? BASE_URL . $product['primary_image'] : (!empty($product['image']) ? $product['image'] : ASSETS_PATH . 'images/products/placeholder.jpg');
$detail_url = BASE_URL . 'product-details.php?id=' . urlencode($product['product_id'] ?? $product['id'] ?? 1);
$product_id = (int)($product['product_id'] ?? $product['id'] ?? 0);

$wrapper_class = !empty($carousel_mode) ? 'carousel-item-wrapper' : 'col-6 col-md-6 col-lg-4 col-xl-3 product-card-col';
?>
<div class="<?= $wrapper_class ?>">
    <div class="card h-100 product-card <?= $is_playstation ? 'card-playstation' : 'card-xbox' ?> bg-white border rounded-4 overflow-hidden position-relative shadow-sm transition-all">
        
        <!-- Platform Badge -->
        <span class="badge position-absolute top-0 start-0 m-3 <?= $badge_class ?> z-2 shadow-sm">
            <i class="bi <?= $platform_icon ?> me-1"></i> <?= htmlspecialchars($platform_name) ?>
        </span>

        <!-- Wishlist Button -->
        <button type="button" 
                class="btn btn-wishlist-toggle position-absolute top-0 end-0 m-3 z-2" 
                data-product-id="<?= $product_id ?>" 
                title="Add to Wishlist"
                aria-label="Wishlist">
            <i class="bi bi-heart"></i>
        </button>

        <!-- Product Image Showcase -->
        <div class="product-img-wrapper position-relative overflow-hidden">
            <a href="<?= $detail_url ?>" class="d-block text-center p-3">
                <img src="<?= htmlspecialchars($image_src) ?>" 
                     class="card-img-top product-img" 
                     alt="<?= htmlspecialchars($product['product_name'] ?? $product['title'] ?? 'Game Cover') ?>"
                     loading="lazy">
            </a>

            <!-- Status Badges Overlay -->
            <div class="product-badges-corner position-absolute bottom-0 start-0 m-3 d-flex flex-column gap-1">
                <?php if (!empty($product['is_new_arrival']) || !empty($product['is_new'])): ?>
                    <span class="badge badge-new-arrival text-uppercase">NEW ARRIVAL</span>
                <?php endif; ?>
                <?php if ($discount_percent > 0): ?>
                    <span class="badge bg-danger text-uppercase">-<?= $discount_percent ?>% OFF</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Details Body -->
        <div class="card-body d-flex flex-column justify-content-between p-3 p-xl-4 bg-white">
            <div>
                <!-- Category / Genre -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted text-uppercase fw-semibold tracking-wider font-heading small">
                        <?= htmlspecialchars($product['genre'] ?? $product['category_name'] ?? 'Action Game') ?>
                    </small>
                    <?php if ($is_out_of_stock): ?>
                        <span class="badge bg-secondary bg-opacity-50 text-light small">Out of Stock</span>
                    <?php else: ?>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-25 small">In Stock</span>
                    <?php endif; ?>
                </div>

                <!-- Title -->
                <h6 class="card-title text-dark fw-bold mb-2 font-heading fs-5 text-truncate-2">
                    <a href="<?= $detail_url ?>" class="text-decoration-none text-dark hover-primary">
                        <?= htmlspecialchars($product['product_name'] ?? $product['title'] ?? 'Untitled Game') ?>
                    </a>
                </h6>
            </div>
            
            <!-- Price and Cart Action Footer -->
            <div class="d-flex flex-column align-items-start gap-3 mt-3 pt-3 border-top">
                <div>
                    <?php if ($discount_price && $discount_price < $price): ?>
                        <div class="d-flex flex-column">
                            <span class="fs-5 fw-bold text-dark"><?= format_price($discount_price) ?></span>
                            <small class="text-muted text-decoration-line-through"><?= format_price($price) ?></small>
                        </div>
                    <?php else: ?>
                        <span class="fs-5 fw-bold text-dark"><?= format_price($price) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart Action -->
                <?php if ($is_out_of_stock): ?>
                    <button class="btn btn-sm btn-secondary rounded-3 px-3 py-2 disabled w-100" disabled title="Out of Stock">
                        <i class="bi bi-slash-circle me-1"></i> Sold Out
                    </button>
                <?php else: ?>
                    <button type="button" 
                            class="btn btn-add-cart rounded-3 px-3 px-xl-4 py-2 w-100 fw-bold" 
                            data-product-id="<?= $product_id ?>" 
                            title="Add to Cart">
                        <i class="bi bi-cart-plus me-1"></i> Add
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
