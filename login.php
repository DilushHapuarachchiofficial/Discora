<?php
/**
 * Discora - Customer Login Page
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/auth.php';

if (is_logged_in()) {
    if (is_admin()) {
        redirect(ADMIN_URL);
    } else {
        redirect(BASE_URL . 'index.php');
    }
}

$page_title = "Customer Login";
$page_css = ['auth.css'];
$page_js = ['auth.js'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-disc text-primary fs-1"></i>
                    <h3 class="text-light fw-bold mt-2">Welcome Back</h3>
                    <p class="text-secondary small">Login to your Discora physical gaming account</p>
                </div>

                <form action="<?= BASE_URL ?>actions/auth-action.php" method="POST">
                    <input type="hidden" name="action" value="login">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Email Address</label>
                        <input type="email" name="email" class="form-control bg-dark border-secondary text-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Password</label>
                        <input type="password" name="password" class="form-control bg-dark border-secondary text-light" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold mt-3">Log In</button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <span class="text-secondary small">Don't have an account?</span>
                    <a href="<?= BASE_URL ?>register.php" class="text-primary text-decoration-none small ms-1">Register now</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof openDiscoraAuthModal === 'function') {
        openDiscoraAuthModal('login');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
