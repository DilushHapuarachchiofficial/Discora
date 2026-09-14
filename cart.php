<?php
/**
 * Discora - Shopping Cart Page
 * Dynamically renders items stored in MySQL carts & cart_items tables
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/functions.php';

$page_title = "Your Shopping Cart";
$page_css   = ['cart-checkout.css'];
$page_js    = ['cart.js'];

$cart_data  = get_current_cart_items();
$cart_items = $cart_data['items'];
$subtotal   = $cart_data['subtotal'];
$total      = $subtotal;

require_once __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background-color: #ffffff !important;
        color: #111827 !important;
    }
</style>

<div class="container my-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="text-dark fw-bold font-heading mb-0">YOUR SHOPPING CART</h2>
        <a href="<?= BASE_URL ?>products.php" class="btn btn-outline-dark btn-sm rounded-pill">
            <i class="bi bi-arrow-left me-1"></i> Continue Shopping
        </a>
    </div>

    <?php if (!empty($cart_items)): ?>
        <div class="row g-4" id="cartContentRow">
            <!-- Cart Items Table -->
            <div class="col-lg-8">
                <div class="card bg-white shadow-sm border-0 p-3 p-md-4 rounded-4">
                    <div class="table-responsive">
                        <table class="table table-hover table-cart align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small font-heading">
                                    <th>GAME / ITEM</th>
                                    <th>PRICE</th>
                                    <th>QTY</th>
                                    <th>SUBTOTAL</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr id="cart-item-row-<?= $item['cart_item_id'] ?>">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="<?= BASE_URL . $item['image_path'] ?>" width="54" height="68" class="rounded object-fit-contain bg-light p-1" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                <div>
                                                    <h6 class="mb-0 text-dark fw-bold font-heading">
                                                        <a href="<?= BASE_URL ?>product-details.php?id=<?= $item['product_id'] ?>" class="text-dark text-decoration-none hover-primary">
                                                            <?= htmlspecialchars($item['product_name']) ?>
                                                        </a>
                                                    </h6>
                                                    <span class="badge bg-secondary bg-opacity-50 mt-1 small text-dark"><?= htmlspecialchars($item['platform_name']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= format_price($item['effective_price']) ?></td>
                                        <td>
                                            <input type="number" 
                                                   value="<?= $item['quantity'] ?>" 
                                                   min="1" 
                                                   max="<?= $item['stock_quantity'] ?>" 
                                                   class="form-control form-control-sm bg-light border-light text-dark text-center cart-qty-input" 
                                                   style="width: 70px;"
                                                   data-item-id="<?= $item['cart_item_id'] ?>">
                                        </td>
                                        <td class="fw-bold text-dark"><?= format_price($item['subtotal']) ?></td>
                                        <td>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger btn-remove-cart-item" 
                                                    data-item-id="<?= $item['cart_item_id'] ?>" 
                                                    title="Remove Item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Order Summary Card -->
            <div class="col-lg-4">
                <div class="summary-card p-4 bg-white rounded-4 border-0 shadow-sm">
                    <h5 class="text-dark fw-bold font-heading mb-3 border-bottom border-light pb-2">ORDER SUMMARY</h5>
                    <div class="d-flex justify-content-between text-muted mb-2">
                        <span>Subtotal</span>
                        <span class="text-dark fw-semibold" id="cartSubtotalText"><?= format_price($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted mb-3">
                        <span>Island-wide Delivery</span>
                        <span class="text-success fw-bold">FREE</span>
                    </div>
                    <hr class="border-light">
                    <div class="d-flex justify-content-between text-dark fw-bold fs-5 mb-4">
                        <span>Estimated Total</span>
                        <span class="text-primary font-heading fs-4" id="cartTotalText"><?= format_price($total) ?></span>
                    </div>
                    <a href="<?= BASE_URL ?>checkout.php" class="btn btn-primary btn-lg w-100 rounded-pill font-heading tracking-wider py-3 shadow">
                        PROCEED TO CHECKOUT <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5 bg-white rounded-4 border-0 p-5 shadow-sm">
            <i class="bi bi-cart-x fs-1 text-muted mb-3 d-block"></i>
            <h3 class="text-dark fw-bold font-heading">YOUR CART IS CURRENTLY EMPTY</h3>
            <p class="text-muted mb-4">Explore our physical PlayStation & Xbox games catalog to add games to your collection.</p>
            <a href="<?= BASE_URL ?>products.php" class="btn btn-primary rounded-pill px-4 py-2 font-heading tracking-wider">
                <i class="bi bi-controller me-1"></i> Browse All Games
            </a>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Live quantity update
    document.querySelectorAll('.cart-qty-input').forEach(input => {
        input.addEventListener('change', async function() {
            const itemId = this.dataset.itemId;
            const newQty = parseInt(this.value, 10);
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('cart_item_id', itemId);
            formData.append('quantity', newQty);

            const res = await fetch('actions/cart-action.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Could not update quantity');
            }
        });
    });

    // Item removal
    document.querySelectorAll('.btn-remove-cart-item').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Remove this game from your cart?')) return;
            const itemId = this.dataset.itemId;
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('cart_item_id', itemId);

            const res = await fetch('actions/cart-action.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                location.reload();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
