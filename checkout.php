<?php
/**
 * Discora - Secure Checkout Page
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/functions.php';

require_login('login.php');

$page_title = "Checkout";
$page_css = ['cart-checkout.css'];
// Add checkout.js if necessary or just write JS inline for form toggle

// Fetch real cart data
$cart_data  = get_current_cart_items();
$cart_items = $cart_data['items'];
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}
$subtotal   = $cart_data['subtotal'];
$total      = $subtotal;

require_once __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background-color: #ffffff !important;
        color: #111827 !important;
    }
    .payment-option-card {
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }
    .payment-option-card.selected {
        border-color: #0d6efd;
        background-color: #f8f9fa;
    }
</style>

<div class="container my-5">
    <h2 class="text-dark fw-bold mb-4">Secure Checkout</h2>

    <form action="<?= BASE_URL ?>actions/checkout-action.php" method="POST" id="checkoutForm">
        <div class="row g-4">
            <div class="col-lg-8">
                
                <!-- STEP 1: Payment Method -->
                <div class="card bg-white border-light shadow-sm p-4 rounded-3 mb-4">
                    <h5 class="text-dark fw-bold mb-3"><i class="bi bi-credit-card me-2 text-success"></i>1. Select Payment Method</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="card bg-light p-3 payment-option-card w-100" id="cardOptionLabel">
                                <div class="form-check">
                                    <input class="form-check-input payment-radio" type="radio" name="payment_method" value="card" id="payCard" required>
                                    <label class="form-check-label fw-bold text-dark" for="payCard">Credit / Debit Card</label>
                                </div>
                                <div class="text-muted small mt-2">Pay securely with Visa, Mastercard, or Amex.</div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="card bg-light p-3 payment-option-card w-100" id="codOptionLabel">
                                <div class="form-check">
                                    <input class="form-check-input payment-radio" type="radio" name="payment_method" value="cod" id="payCod" required>
                                    <label class="form-check-label fw-bold text-dark" for="payCod">Cash on Delivery (COD)</label>
                                </div>
                                <div class="text-muted small mt-2">Pay with cash when your physical discs arrive.</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Details (Hidden initially until selection is made) -->
                <div id="detailsSection" style="display: none;">
                    <div class="card bg-white border-light shadow-sm p-4 rounded-3 mb-4">
                        <h5 class="text-dark fw-bold mb-3"><i class="bi bi-geo-alt me-2 text-primary"></i>2. Shipping Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Full Name</label>
                                <input type="text" name="name" class="form-control bg-light border-light text-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Phone Number</label>
                                <input type="text" name="phone" class="form-control bg-light border-light text-dark" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label text-muted small">Street Address</label>
                                <input type="text" name="address" class="form-control bg-light border-light text-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">City</label>
                                <input type="text" name="city" class="form-control bg-light border-light text-dark" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Postal / ZIP Code</label>
                                <input type="text" name="postal_code" class="form-control bg-light border-light text-dark" required>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Checkout Order Summary -->
            <div class="col-lg-4">
                <div class="summary-card p-4 bg-white shadow-sm border-0 rounded-4 sticky-top" style="top: 20px;">
                    <h5 class="text-dark fw-bold mb-3 border-bottom border-light pb-2">Order Total</h5>
                    <div class="d-flex justify-content-between text-muted mb-2">
                        <span>Items Total</span>
                        <span class="text-dark fw-semibold"><?= format_price($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted mb-3">
                        <span>Shipping</span>
                        <span class="text-success fw-bold">FREE</span>
                    </div>
                    <hr class="border-light">
                    <div class="d-flex justify-content-between text-dark fw-bold fs-5 mb-4">
                        <span>Total Due</span>
                        <span class="text-primary"><?= format_price($total) ?></span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill" id="placeOrderBtn" disabled>Select Payment Method</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const radios = document.querySelectorAll('.payment-radio');
    const detailsSection = document.getElementById('detailsSection');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    radios.forEach(radio => {
        radio.addEventListener('change', (e) => {
            // Update styles
            document.querySelectorAll('.payment-option-card').forEach(el => el.classList.remove('selected'));
            e.target.closest('.payment-option-card').classList.add('selected');
            
            // Show forms
            detailsSection.style.display = 'block';
            placeOrderBtn.disabled = false;
            
            if (e.target.value === 'card') {
                placeOrderBtn.textContent = 'Pay Now & Place Order';
            } else {
                placeOrderBtn.textContent = 'Place Order (COD)';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
