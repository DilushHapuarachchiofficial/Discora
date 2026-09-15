<?php
/**
 * Discora - Customer Wishlist Page
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/functions.php';

require_login('login.php');

$page_title = "My Wishlist";
$user = current_user();
$db = get_db_connection();
$user_id = $_SESSION['user_id'];

// Fetch wishlist items
$stmt = $db->prepare("
    SELECT wi.wishlist_item_id, p.product_id, p.product_name, p.price, p.discount_price, p.stock_quantity,
           (SELECT image_path FROM product_images WHERE product_id = p.product_id ORDER BY is_primary DESC LIMIT 1) as image_path
    FROM wishlist_items wi
    JOIN wishlists w ON wi.wishlist_id = w.wishlist_id
    JOIN products p ON wi.product_id = p.product_id
    WHERE w.user_id = ?
    ORDER BY wi.added_at DESC
");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background-color: #ffffff !important;
        color: #111827 !important;
    }
</style>

<div class="container my-5">
    <div class="row g-4">
        <!-- Account Sidebar -->
        <div class="col-md-4">
            <div class="card bg-white shadow-sm border-0 p-4 rounded-3 text-center">
                <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 mx-auto" style="width: 80px; height: 80px; overflow: hidden;">
                    <?php if (!empty($user['avatar'])): ?>
                        <img src="<?= BASE_URL . htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="bi bi-person fs-1"></i>
                    <?php endif; ?>
                </div>
                <h5 class="text-dark fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h5>
                <p class="text-muted small mb-3"><?= htmlspecialchars($user['email']) ?></p>
                <div class="list-group list-group-flush text-start">
                    <a href="<?= BASE_URL ?>account.php" class="list-group-item list-group-item-action bg-transparent text-dark border-bottom"><i class="bi bi-person me-2"></i>Profile Info</a>
                    <a href="<?= BASE_URL ?>orders.php" class="list-group-item list-group-item-action bg-transparent text-dark border-bottom"><i class="bi bi-bag-check me-2"></i>My Orders</a>
                    <a href="<?= BASE_URL ?>wishlist.php" class="list-group-item list-group-item-action bg-transparent text-primary fw-bold border-bottom"><i class="bi bi-heart me-2"></i>My Wishlist</a>
                    <a href="<?= BASE_URL ?>logout.php" class="list-group-item list-group-item-action bg-transparent text-danger border-0"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                </div>
            </div>
        </div>

        <!-- Wishlist -->
        <div class="col-md-8">
            <div class="card bg-white shadow-sm border-0 p-4 rounded-3">
                <h5 class="text-dark fw-bold mb-4">My Wishlist</h5>
                
                <?php if (empty($wishlist_items)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-heart text-muted mb-3 d-block" style="font-size: 3rem;"></i>
                        <h4 class="text-dark">Your wishlist is empty</h4>
                        <p class="text-muted mb-4">Save your favorite games here to buy them later.</p>
                        <a href="<?= BASE_URL ?>products.php" class="btn btn-outline-dark rounded-pill px-4">Browse Games</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($wishlist_items as $item): 
                            $price = $item['price'];
                            $discount = $item['discount_price'];
                            $eff_price = ($discount > 0 && $discount < $price) ? $discount : $price;
                        ?>
                        <div class="col-md-6" id="wishlist-item-<?= $item['wishlist_item_id'] ?>">
                            <div class="card border h-100 rounded-3 p-3">
                                <div class="d-flex gap-3">
                                    <div class="flex-shrink-0">
                                        <a href="<?= BASE_URL ?>product-details.php?id=<?= $item['product_id'] ?>">
                                            <img src="<?= BASE_URL . $item['image_path'] ?>" class="rounded bg-light p-1 object-fit-contain" style="width: 80px; height: 100px;">
                                        </a>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-bold text-dark text-truncate" style="max-width: 150px;">
                                            <a href="<?= BASE_URL ?>product-details.php?id=<?= $item['product_id'] ?>" class="text-dark text-decoration-none hover-primary">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                            </a>
                                        </h6>
                                        <div class="mb-2">
                                            <?php if ($discount > 0 && $discount < $price): ?>
                                                <span class="text-dark fw-bold"><?= format_price($discount) ?></span>
                                                <span class="text-muted small text-decoration-line-through ms-1"><?= format_price($price) ?></span>
                                            <?php else: ?>
                                                <span class="text-dark fw-bold"><?= format_price($price) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <?php if ($item['stock_quantity'] > 0): ?>
                                                <button type="button" class="btn btn-sm btn-dark btn-add-cart-wishlist" data-product-id="<?= $item['product_id'] ?>">
                                                    <i class="bi bi-cart-plus"></i> Add
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-remove-wishlist" data-product-id="<?= $item['product_id'] ?>" data-item-id="<?= $item['wishlist_item_id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Add to Cart from Wishlist
    document.querySelectorAll('.btn-add-cart-wishlist').forEach(btn => {
        btn.addEventListener('click', async () => {
            const formData = new FormData();
            formData.append('product_id', btn.dataset.productId);
            formData.append('quantity', 1);
            
            try {
                const res = await fetch('actions/cart-action.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    alert('Added to cart!');
                } else {
                    alert(data.message || 'Error adding to cart');
                }
            } catch (err) {
                console.error(err);
            }
        });
    });

    // Remove from Wishlist
    document.querySelectorAll('.btn-remove-wishlist').forEach(btn => {
        btn.addEventListener('click', async () => {
            if (!confirm('Remove this item from your wishlist?')) return;
            
            const formData = new FormData();
            formData.append('product_id', btn.dataset.productId);
            formData.append('action', 'remove');
            
            try {
                const res = await fetch('actions/wishlist-action.php', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    const el = document.getElementById('wishlist-item-' + btn.dataset.itemId);
                    if (el) el.remove();
                    // if none left, might want to reload to show empty state
                    if (document.querySelectorAll('[id^="wishlist-item-"]').length === 0) {
                        location.reload();
                    }
                } else {
                    alert(data.message || 'Error removing from wishlist');
                }
            } catch (err) {
                console.error(err);
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
