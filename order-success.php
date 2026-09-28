<?php
/**
 * Discora - Order Success Confirmation Page
 */
$page_title = "Order Placed Successfully";
require_once __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background-color: #ffffff !important;
        color: #111827 !important;
    }
</style>
<div class="container my-5 text-center">
    <div class="card bg-light border-light shadow-sm p-5 rounded-4 col-lg-6 mx-auto">
        <div class="text-success mb-3" style="font-size: 4rem;">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h2 class="text-dark fw-bold mb-2">Order Confirmed!</h2>
        <p class="text-muted mb-4">Thank you for your purchase. We are preparing your physical game order for safe dispatch.</p>
        
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= BASE_URL ?>orders.php" class="btn btn-outline-dark rounded-pill px-4">View Orders</a>
            <a href="<?= BASE_URL ?>products.php" class="btn btn-primary rounded-pill px-4">Continue Shopping</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
