<?php
/**
 * Discora - Secure Checkout Page
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/functions.php';

require_login('login.php');

if (is_admin()) {
    redirect(BASE_URL);
    exit;
}

$page_title = "Checkout";
$page_css = ['cart-checkout.css'];

// Fetch real cart data
$cart_data  = get_current_cart_items();
$cart_items = $cart_data['items'];
if (empty($cart_items)) {
    header('Location: cart.php');
    exit;
}
$subtotal   = $cart_data['subtotal'];
$total      = $subtotal;

// Pre-fill user shipping details if available
$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? '';
$shipping_name = $user_name;
$shipping_phone = '';
$shipping_address = '';
$shipping_city = '';
$shipping_postal = '';

$db = get_db_connection();
if ($user_id && $db) {
    $addr_stmt = $db->prepare("SELECT recipient_name, phone, address_line1, city, postal_code FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
    $addr_stmt->execute([$user_id]);
    $default_addr = $addr_stmt->fetch(PDO::FETCH_ASSOC);
    if ($default_addr) {
        if (!empty($default_addr['recipient_name'])) $shipping_name = $default_addr['recipient_name'];
        if (!empty($default_addr['phone'])) $shipping_phone = $default_addr['phone'];
        if (!empty($default_addr['address_line1'])) $shipping_address = $default_addr['address_line1'];
        if (!empty($default_addr['city'])) $shipping_city = $default_addr['city'];
        if (!empty($default_addr['postal_code'])) $shipping_postal = $default_addr['postal_code'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background-color: #f8fafc !important;
        color: #0f172a !important;
    }
    .checkout-step-card {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;
    }
    .payment-option-card {
        cursor: pointer;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 12px !important;
        background: #ffffff !important;
        transition: all 0.2s ease;
    }
    .payment-option-card:hover {
        border-color: #94a3b8 !important;
        transform: translateY(-1px);
    }
    .payment-option-card.selected {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.04) !important;
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.12) !important;
    }
    .modern-label {
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #475569;
        margin-bottom: 0.4rem;
        display: block;
    }
    .modern-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
    }
    .modern-input-wrapper:hover {
        border-color: #94a3b8;
    }
    .modern-input-wrapper:focus-within {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.12) !important;
    }
    .modern-input-wrapper .input-prefix-icon {
        padding-left: 1rem;
        padding-right: 0.6rem;
        color: #94a3b8;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        pointer-events: none;
    }
    .modern-input-wrapper .form-control {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        padding: 0.72rem 0.9rem 0.72rem 0;
        font-size: 0.95rem;
        color: #0f172a !important;
        font-weight: 500;
    }
    .modern-input-wrapper .form-control::placeholder {
        color: #94a3b8;
        font-weight: 400;
        font-size: 0.9rem;
    }
    .order-summary-card {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 16px !important;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05) !important;
    }
    .btn-place-order {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        border: none;
        color: #ffffff;
        font-weight: 600;
        border-radius: 12px;
        padding: 0.85rem;
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.25);
    }
    .btn-place-order:hover:not(:disabled) {
        background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.35);
        color: #ffffff;
    }
    .btn-place-order:disabled {
        background: #94a3b8;
        cursor: not-allowed;
        box-shadow: none;
    }
</style>

<div class="container my-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="text-dark fw-bold mb-1 font-heading">Secure Checkout</h2>
            <span class="text-muted small"><i class="bi bi-shield-check text-success me-1"></i>SSL Encrypted & Guaranteed Safe Checkout</span>
        </div>
        <a href="<?= BASE_URL ?>cart.php" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Cart
        </a>
    </div>

    <form action="<?= BASE_URL ?>actions/checkout-action.php" method="POST" id="checkoutForm">
        <div class="row g-4">
            <div class="col-lg-8">
                
                <!-- STEP 1: Payment Method -->
                <div class="card checkout-step-card p-4 p-sm-5 mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                        <i class="bi bi-credit-card-2-front-fill text-success fs-4"></i>
                        <div>
                            <h5 class="text-dark fw-bold mb-0">1. Select Payment Method</h5>
                            <span class="text-muted small">Choose your preferred payment method to proceed</span>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="card p-3 payment-option-card w-100" id="cardOptionLabel">
                                <div class="form-check d-flex align-items-center gap-2 mb-1">
                                    <input class="form-check-input payment-radio" type="radio" name="payment_method" value="card" id="payCard" required>
                                    <span class="form-check-label fw-bold text-dark fs-6" for="payCard">Credit / Debit Card</span>
                                </div>
                                <div class="text-muted small ps-4">Pay securely online with Visa, Mastercard, or Amex.</div>
                            </label>
                        </div>
                        <div class="col-md-6">
                            <label class="card p-3 payment-option-card w-100" id="codOptionLabel">
                                <div class="form-check d-flex align-items-center gap-2 mb-1">
                                    <input class="form-check-input payment-radio" type="radio" name="payment_method" value="cod" id="payCod" required>
                                    <span class="form-check-label fw-bold text-dark fs-6" for="payCod">Cash on Delivery (COD)</span>
                                </div>
                                <div class="text-muted small ps-4">Pay in cash when your physical discs arrive at your door.</div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Details (Hidden initially until selection is made) -->
                <div id="detailsSection" style="display: none;">
                    <div class="card checkout-step-card p-4 p-sm-5 mb-4">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                            <div>
                                <h5 class="text-dark fw-bold mb-0">2. Shipping Details</h5>
                                <span class="text-muted small">Where should we deliver your physical game discs & gear?</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="modern-label">Full Name</label>
                                <div class="modern-input-wrapper">
                                    <span class="input-prefix-icon"><i class="bi bi-person"></i></span>
                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($shipping_name) ?>" required placeholder="e.g. John Doe">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Phone Number</label>
                                <div class="modern-input-wrapper">
                                    <span class="input-prefix-icon"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($shipping_phone) ?>" required placeholder="e.g. 077 123 4567">
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="modern-label">Street Address</label>
                                <div class="modern-input-wrapper">
                                    <span class="input-prefix-icon"><i class="bi bi-geo-alt"></i></span>
                                    <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($shipping_address) ?>" required placeholder="House / Apartment number, Street name">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">City</label>
                                <div class="modern-input-wrapper">
                                    <span class="input-prefix-icon"><i class="bi bi-buildings"></i></span>
                                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($shipping_city) ?>" required placeholder="e.g. Colombo">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Postal / ZIP Code</label>
                                <div class="modern-input-wrapper">
                                    <span class="input-prefix-icon"><i class="bi bi-mailbox"></i></span>
                                    <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($shipping_postal) ?>" required placeholder="e.g. 00100">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Checkout Order Summary -->
            <div class="col-lg-4">
                <div class="order-summary-card p-4 sticky-top" style="top: 20px;">
                    <h5 class="text-dark fw-bold mb-3 border-bottom pb-2 font-heading">Order Total</h5>
                    <div class="d-flex justify-content-between text-muted mb-2">
                        <span>Items Total (<?= count($cart_items) ?>)</span>
                        <span class="text-dark fw-semibold"><?= format_price($subtotal) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted mb-3">
                        <span>Island-wide Express Shipping</span>
                        <span class="text-success fw-bold">FREE</span>
                    </div>
                    <hr class="border-secondary border-opacity-25">
                    <div class="d-flex justify-content-between text-dark fw-bold fs-5 mb-4">
                        <span>Total Due</span>
                        <span class="text-primary"><?= format_price($total) ?></span>
                    </div>
                    <button type="submit" class="btn btn-place-order btn-lg w-100" id="placeOrderBtn" disabled>
                        <i class="bi bi-shield-check me-1"></i> Select Payment Method
                    </button>
                    <div class="text-center text-muted small mt-3">
                        <i class="bi bi-lock-fill text-success me-1"></i> 256-bit Encrypted Checkout
                    </div>
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
                placeOrderBtn.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Pay Now & Place Order';
            } else {
                placeOrderBtn.innerHTML = '<i class="bi bi-truck me-1"></i> Place Order (Cash on Delivery)';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
