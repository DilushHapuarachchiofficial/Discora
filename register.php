<?php
/**
 * Discora - Customer Registration Page
 */
$page_title = "Create an Account";
$page_css = ['auth.css'];
$page_js = ['auth.js'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="auth-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="bi bi-controller text-primary fs-1"></i>
                    <h3 class="text-light fw-bold mt-2">Join Discora</h3>
                    <p class="text-secondary small">Create an account to track orders & pre-order titles</p>
                </div>

                <form id="registerForm" action="<?= BASE_URL ?>actions/auth-action.php" method="POST">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Full Name</label>
                        <input type="text" name="name" class="form-control bg-dark border-secondary text-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Email Address</label>
                        <input type="email" name="email" class="form-control bg-dark border-secondary text-light" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Password</label>
                        <input type="password" id="password" name="password" class="form-control bg-dark border-secondary text-light" required minlength="6">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-secondary small">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control bg-dark border-secondary text-light" required minlength="6">
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-semibold mt-3">Register Account</button>
                </form>

                <div class="text-center mt-4 pt-3 border-top border-secondary border-opacity-25">
                    <span class="text-secondary small">Already have an account?</span>
                    <a href="<?= BASE_URL ?>login.php" class="text-primary text-decoration-none small ms-1">Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof openDiscoraAuthModal === 'function') {
        openDiscoraAuthModal('register');
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
