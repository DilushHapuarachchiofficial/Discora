<?php
/**
 * Discora - Dynamic Product Details Page
 * Loads product details, gallery images, stock status, and reviews from MySQL
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';
require_once __DIR__ . '/core/auth.php';

$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$productSlug = sanitize_input($_GET['slug'] ?? '');

$product = null;
if ($productId > 0) {
    $product = get_product_details($productId);
} elseif (!empty($productSlug)) {
    $product = get_product_details($productSlug);
}

if (!$product) {
    // Product not found error page
    $page_title = "Product Not Found";
    $page_css   = ['products.css'];
    require_once __DIR__ . '/includes/header.php';
    ?>
    <div class="container my-5 py-5 text-center">
        <div class="p-5 bg-dark rounded-4 border border-secondary border-opacity-25 shadow">
            <i class="bi bi-exclamation-octagon text-danger fs-1 mb-3 d-block"></i>
            <h2 class="text-light fw-bold font-heading">GAME OR PRODUCT NOT FOUND</h2>
            <p class="text-secondary mb-4">The game edition you requested could not be located in our inventory.</p>
            <a href="<?= BASE_URL ?>products.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Browse Products Catalog
            </a>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$page_title = $product['product_name'] . " - " . $product['platform_name'];
$page_css   = ['product-details.css', 'products.css'];
$page_js    = ['product-details.js'];

$is_playstation = (strpos(strtolower($product['platform_slug'] ?? ''), 'ps') !== false || strpos(strtolower($product['platform_brand'] ?? ''), 'playstation') !== false);
$badge_class = $is_playstation ? 'badge-playstation' : 'badge-xbox';
$platform_icon = $is_playstation ? 'bi-playstation' : 'bi-xbox';
$platform_class = $is_playstation ? 'platform-playstation' : 'platform-xbox';

$price = (float)$product['price'];
$discount_price = !empty($product['discount_price']) ? (float)$product['discount_price'] : null;
$effective_price = $discount_price ?: $price;

$discount_percent = ($discount_price && $discount_price < $price) 
    ? round((($price - $discount_price) / $price) * 100) 
    : 0;

$stock_qty = (int)$product['stock_quantity'];
$is_out_of_stock = ($stock_qty <= 0 || (isset($product['status']) && $product['status'] === 'Out of Stock'));

$related_products = get_related_products($product['product_id'], $product['category_id'], $product['platform_id'], 4);

$in_wishlist = false;
if (is_logged_in()) {
    $db = Database::getConnection();
    $wStmt = $db->prepare("SELECT 1 FROM wishlist_items wi JOIN wishlists w ON wi.wishlist_id = w.wishlist_id WHERE w.user_id = ? AND wi.product_id = ?");
    $wStmt->execute([$_SESSION['user_id'], $product['product_id']]);
    $in_wishlist = (bool)$wStmt->fetch();
}

$body_class = 'bg-white text-dark';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-4 my-lg-5">
    
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>" class="text-secondary text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>products.php" class="text-secondary text-decoration-none">Products</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?><?= $is_playstation ? ($product['platform_slug'] === 'ps4' ? 'ps4.php' : 'ps5.php') : ($product['platform_slug'] === 'xbox-one' ? 'xbox-one.php' : 'xbox-series.php') ?>" class="text-secondary text-decoration-none"><?= htmlspecialchars($product['platform_name']) ?></a></li>
            <li class="breadcrumb-item active text-dark text-truncate" style="max-width: 300px;" aria-current="page"><?= htmlspecialchars($product['product_name']) ?></li>
        </ol>
    </nav>

    <!-- Product Showcase Row -->
    <div class="row g-5 <?= $platform_class ?>">
        
        <!-- Left: Image Gallery Showcase -->
        <div class="col-lg-5">
            <div class="product-gallery-sticky">
                <!-- Main Preview Showcase -->
                <div class="main-preview-box p-4 bg-white rounded-4 border border-opacity-25 text-center mb-3 position-relative overflow-hidden shadow-sm">
                    <!-- Platform Ribbon -->
                    <span class="badge position-absolute top-0 start-0 m-3 <?= $badge_class ?> z-2">
                        <i class="bi <?= $platform_icon ?> me-1"></i> <?= htmlspecialchars($product['platform_name']) ?>
                    </span>

                    <?php if (!empty($product['is_new_arrival'])): ?>
                        <span class="badge badge-new-arrival position-absolute top-0 end-0 m-3 z-2">NEW ARRIVAL</span>
                    <?php endif; ?>

                    <img id="mainProductImg" 
                         src="<?= BASE_URL . $product['primary_image'] ?>" 
                         class="img-fluid main-product-image rounded-3" 
                         alt="<?= htmlspecialchars($product['product_name']) ?>">
                </div>

                <!-- Gallery Thumbnails -->
                <?php if (count($product['images']) > 1): ?>
                    <div class="d-flex gap-2 thumb-gallery justify-content-center">
                        <?php foreach ($product['images'] as $idx => $img): ?>
                            <div class="thumb-item <?= ($idx === 0) ? 'active' : '' ?>" data-src="<?= BASE_URL . $img['image_path'] ?>">
                                <img src="<?= BASE_URL . $img['image_path'] ?>" 
                                     alt="<?= htmlspecialchars($img['alt_text'] ?? $product['product_name']) ?>" 
                                     class="img-fluid rounded border border-secondary">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Product Information & Purchase Area -->
        <div class="col-lg-7">
            <div class="product-details-content">
                
                <!-- Category & Edition -->
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-secondary bg-opacity-25 text-light text-uppercase font-heading tracking-wider">
                        <?= htmlspecialchars($product['genre'] ?? 'Physical Game Disc') ?>
                    </span>
                    <span class="text-secondary small">• <?= htmlspecialchars($product['edition'] ?? 'Standard Edition') ?></span>
                </div>

                <!-- Product Name -->
                <h1 class="text-dark fw-bold font-heading mb-2 display-6"><?= htmlspecialchars($product['product_name']) ?></h1>

                <!-- Rating and Reviews Count -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="text-warning fs-6">
                        <?php 
                        $stars = round($product['avg_rating']);
                        for ($s = 1; $s <= 5; $s++) {
                            echo ($s <= $stars) ? '<i class="bi bi-star-fill"></i> ' : '<i class="bi bi-star"></i> ';
                        }
                        ?>
                    </div>
                    <span class="text-dark fw-bold small"><?= number_format($product['avg_rating'], 1) ?></span>
                    <span class="text-secondary small">(<?= $product['review_count'] ?> customer reviews)</span>
                </div>

                <!-- Price Block -->
                <div class="product-price-block d-flex align-items-baseline gap-3 mb-4 p-3 bg-light bg-opacity-75 rounded-3 border">
                    <?php if ($discount_price && $discount_price < $price): ?>
                        <span class="fs-2 fw-bold text-dark"><?= format_price($discount_price) ?></span>
                        <span class="fs-5 text-muted text-decoration-line-through"><?= format_price($price) ?></span>
                        <span class="badge bg-danger fs-6">-<?= $discount_percent ?>% OFF</span>
                        <span class="text-success small ms-auto fw-semibold">You Save <?= format_price($price - $discount_price) ?></span>
                    <?php else: ?>
                        <span class="fs-2 fw-bold text-dark"><?= format_price($price) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="product-description text-secondary mb-4 lead fs-6">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>

                <!-- Key Specs Grid -->
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row g-3 small">
                        <div class="col-6 col-sm-4">
                            <span class="text-muted d-block">Platform</span>
                            <strong class="text-dark"><?= htmlspecialchars($product['platform_name']) ?></strong>
                        </div>
                        <div class="col-6 col-sm-4">
                            <span class="text-muted d-block">Publisher</span>
                            <strong class="text-dark"><?= htmlspecialchars($product['publisher'] ?? 'Discora Gaming') ?></strong>
                        </div>
                        <div class="col-6 col-sm-4">
                            <span class="text-muted d-block">Release Date</span>
                            <strong class="text-dark"><?= htmlspecialchars($product['release_date'] ?? 'Available Now') ?></strong>
                        </div>
                        <div class="col-6 col-sm-4">
                            <span class="text-muted d-block">Product Type</span>
                            <strong class="text-dark"><?= htmlspecialchars($product['product_type'] ?? 'Factory Sealed Physical Disc') ?></strong>
                        </div>
                        <div class="col-6 col-sm-4">
                            <span class="text-secondary d-block">Availability</span>
                            <?php if ($is_out_of_stock): ?>
                                <span class="badge bg-danger">Out of Stock</span>
                            <?php else: ?>
                                <span class="badge bg-success">In Stock (<?= $stock_qty ?> copies)</span>
                            <?php endif; ?>
                        </div>
                        <div class="col-6 col-sm-4">
                            <span class="text-secondary d-block">Delivery</span>
                            <strong class="text-info">Express 1-2 Days</strong>
                        </div>
                    </div>
                </div>

                <!-- Purchase Controls -->
                <div class="purchase-controls-box mb-4">
                    <?php if ($is_out_of_stock): ?>
                        <div class="alert alert-danger bg-dark border-danger text-light mb-0">
                            <i class="bi bi-slash-circle me-2"></i> This item is currently out of stock. Check back soon for restocks!
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <!-- Quantity Selector -->
                            <div class="quantity-input-group d-flex align-items-center bg-white border rounded-pill p-1">
                                <button type="button" class="btn btn-sm btn-link text-dark text-decoration-none px-3" id="qtyMinusBtn"><i class="bi bi-dash"></i></button>
                                <input type="number" id="detailProductQty" class="form-control form-control-sm bg-transparent border-0 text-dark text-center fw-bold p-0" value="1" min="1" max="<?= $stock_qty ?>" style="width: 45px;">
                                <button type="button" class="btn btn-sm btn-link text-dark text-decoration-none px-3" id="qtyPlusBtn"><i class="bi bi-plus"></i></button>
                            </div>

                            <!-- Add to Cart -->
                            <button type="button" 
                                    class="btn btn-lg rounded-pill px-4 btn-add-cart-detail flex-grow-1" 
                                    data-product-id="<?= $product['product_id'] ?>">
                                <i class="bi bi-cart-plus me-2"></i> Add to Cart
                            </button>

                            <!-- Wishlist -->
                            <button type="button" 
                                    class="btn btn-outline-dark btn-lg rounded-circle p-3 btn-wishlist-toggle" 
                                    data-product-id="<?= $product['product_id'] ?>" 
                                    title="<?= $in_wishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
                                <i class="bi <?= $in_wishlist ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Authenticity Trust Badges -->
                <div class="row g-2 text-secondary small pt-3 border-top border-secondary border-opacity-25">
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-primary fs-5"></i>
                        <span>100% Factory Sealed</span>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-truck text-success fs-5"></i>
                        <span>Island-wide Express</span>
                    </div>
                    <div class="col-sm-4 d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-counterclockwise text-info fs-5"></i>
                        <span>7 Days Return Guarantee</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Product Reviews & Ratings Section -->
    <div class="product-reviews-section mt-5 pt-5 border-top border-secondary border-opacity-25">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h3 class="text-dark fw-bold font-heading mb-1">VERIFIED CUSTOMER REVIEWS</h3>
                <p class="text-muted small mb-0">Read genuine feedback from players who purchased this physical edition.</p>
            </div>
            <div>
                <?php if (is_logged_in()): ?>
                    <button class="btn btn-outline-dark rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                        <i class="bi bi-pencil-square me-1"></i> Write a Review
                    </button>
                <?php else: ?>
                    <button class="btn btn-outline-dark rounded-pill px-3" onclick="openDiscoraAuthModal('login');">
                        <i class="bi bi-lock me-1"></i> Sign in to Review
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($product['reviews'])): ?>
                <?php foreach ($product['reviews'] as $rev): ?>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border h-100 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong class="text-dark"><?= htmlspecialchars($rev['author_name'] ?? 'Player') ?></strong>
                                <div class="text-warning small">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="bi bi-star-fill <?= ($s <= $rev['rating']) ? '' : 'text-secondary' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-secondary small mb-1"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>
                            <small class="text-muted d-block"><?= date('M d, Y', strtotime($rev['created_at'])) ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="p-4 bg-light rounded-3 border text-center">
                        <p class="text-muted mb-0">No customer reviews yet. Be the first player to review this game!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Related Games Section -->
    <?php if (!empty($related_products)): ?>
        <div class="related-games-section mt-5 pt-5 border-top border-secondary border-opacity-25">
            <h3 class="text-dark fw-bold font-heading mb-4">YOU MAY ALSO LIKE</h3>
            <div class="row g-4">
                <?php foreach ($related_products as $rel_prod): ?>
                    <?php 
                    $product = $rel_prod;
                    require INCLUDES_PATH . 'product-card.php'; 
                    ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- Write Review Modal -->
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-labelledby="writeReviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white text-dark border shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-heading fs-5" id="writeReviewModalLabel">Write a Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="productReviewForm" action="<?= BASE_URL ?>actions/review-action.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small">Your Star Rating</label>
                        <select name="rating" class="form-select bg-light border text-dark" required>
                            <option value="5">★★★★★ - 5 Stars (Masterpiece)</option>
                            <option value="4">★★★★☆ - 4 Stars (Great Game)</option>
                            <option value="3">★★★☆☆ - 3 Stars (Good)</option>
                            <option value="2">★★☆☆☆ - 2 Stars (Average)</option>
                            <option value="1">★☆☆☆☆ - 1 Star (Disappointed)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Review Comments</label>
                        <textarea name="review_text" rows="4" class="form-control bg-light border text-dark" placeholder="Share your experience with this game disc..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-dark rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
