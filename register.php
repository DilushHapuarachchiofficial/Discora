<?php
/**
 * Discora - Customer Registration Page
 * Matches Discora Gaming Theme, Two-Column Visual Showcase & AJAX / Standard Flow
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/auth.php';

// Store target redirect if provided in URL parameter
if (!empty($_GET['redirect'])) {
    $_SESSION['redirect_after_login'] = sanitize_input($_GET['redirect']);
}

// If already logged in, redirect
if (is_logged_in()) {
    $destination = !empty($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : (is_admin() ? ADMIN_URL : BASE_URL . 'index.php');
    unset($_SESSION['redirect_after_login']);
    redirect($destination);
    exit;
}

$page_title = "Create an Account";
$body_class = "auth-page";
$hide_auth_modal = true; // Use page-embedded auth card without duplicate footer modal

$redirect_target = !empty($_SESSION['redirect_after_login']) 
    ? $_SESSION['redirect_after_login'] 
    : (!empty($_GET['redirect']) ? sanitize_input($_GET['redirect']) : BASE_URL . 'checkout.php');

$return_url = !empty($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : BASE_URL . 'index.php';

require_once __DIR__ . '/includes/header.php';
?>

<main class="discora-auth-page-wrapper">
    <div class="container d-flex justify-content-center align-items-center">
        <div class="discora-auth-card" role="region" aria-label="Account Authentication">
            
            <!-- Close / Return Button -->
            <a href="<?= htmlspecialchars($return_url) ?>" class="auth-modal-close text-decoration-none" aria-label="Close and return" title="Return to store">
                <i class="bi bi-x-lg"></i>
            </a>

            <div class="auth-modal-grid">
                
                <!-- Left Column: Cinematic Gaming Showcase -->
                <div class="auth-visual-pane">
                    <div class="auth-visual-overlay"></div>
                    <div class="auth-visual-content d-flex flex-column justify-content-between h-100">
                        
                        <!-- Discora Logo (Top) -->
                        <div class="auth-brand-badge text-start mb-auto">
                            <a href="<?= BASE_URL ?>" title="Discora Home">
                                <img src="<?= ASSETS_PATH ?>images/logos/discora-logo.png" alt="Discora Logo" class="auth-brand-logo">
                            </a>
                        </div>

                        <!-- Modern Glassmorphism Caption (Bottom) -->
                        <div class="auth-visual-glass-card p-3 rounded-4 mt-auto">
                            <span class="badge bg-primary bg-opacity-75 text-white rounded-pill px-3 py-1 mb-2">
                                <i class="bi bi-controller me-1"></i> Physical Gaming Vault
                            </span>
                            <h4 class="text-white fw-bold font-heading mb-1">Your Next-Gen Game Hub</h4>
                            <p class="text-light text-opacity-75 small mb-0">Original physical discs, consoles & exclusive gamer perks.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Authentication Forms -->
                <div class="auth-form-pane">
                    
                    <!-- Dynamic Alert Box -->
                    <div id="authAlertBox" class="auth-alert d-none" role="alert">
                        <i class="auth-alert-icon bi bi-info-circle-fill me-2"></i>
                        <span class="auth-alert-text"></span>
                    </div>

                    <!-- ==============================================
                         1. REGISTER FORM VIEW (ACTIVE BY DEFAULT)
                         ============================================== -->
                    <div class="auth-form-view active" id="registerView">
                        <div class="auth-header mb-4">
                            <span class="auth-subtitle text-uppercase tracking-wider">New to Discora</span>
                            <h2 class="auth-title font-heading">CREATE ACCOUNT</h2>
                            <p class="auth-desc text-muted">Join Discora to track your disc orders, save wishlists, and receive member perks.</p>
                        </div>

                        <form id="discoraRegisterForm" action="<?= BASE_URL ?>actions/auth-action.php" method="POST" novalidate>
                            <input type="hidden" name="action" value="register">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect_target) ?>">

                            <!-- Name Row -->
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <div class="auth-field-group">
                                        <label for="regFirstName" class="auth-label">First Name</label>
                                        <div class="auth-input-wrapper">
                                            <span class="input-icon"><i class="bi bi-person"></i></span>
                                            <input type="text" 
                                                   id="regFirstName" 
                                                   name="first_name" 
                                                   class="auth-input" 
                                                   placeholder="Alex" 
                                                   required>
                                        </div>
                                        <div class="auth-field-error" id="regFirstNameError"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="auth-field-group">
                                        <label for="regLastName" class="auth-label">Last Name</label>
                                        <div class="auth-input-wrapper">
                                            <span class="input-icon"><i class="bi bi-person"></i></span>
                                            <input type="text" 
                                                   id="regLastName" 
                                                   name="last_name" 
                                                   class="auth-input" 
                                                   placeholder="Rivera" 
                                                   required>
                                        </div>
                                        <div class="auth-field-error" id="regLastNameError"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Input -->
                            <div class="auth-field-group mb-3">
                                <label for="regEmail" class="auth-label">Email Address</label>
                                <div class="auth-input-wrapper">
                                    <span class="input-icon"><i class="bi bi-envelope"></i></span>
                                    <input type="email" 
                                           id="regEmail" 
                                           name="email" 
                                           class="auth-input" 
                                           placeholder="name@example.com" 
                                           autocomplete="email" 
                                           required>
                                </div>
                                <div class="auth-field-error" id="regEmailError"></div>
                            </div>

                            <!-- Phone Number -->
                            <div class="auth-field-group mb-3">
                                <label for="regPhone" class="auth-label">Phone Number</label>
                                <div class="auth-input-wrapper">
                                    <span class="input-icon"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" 
                                           id="regPhone" 
                                           name="phone" 
                                           class="auth-input" 
                                           placeholder="+1 (555) 000-0000" 
                                           required>
                                </div>
                                <div class="auth-field-error" id="regPhoneError"></div>
                            </div>

                            <!-- Password Row -->
                            <div class="row g-2 mb-3">
                                <div class="col-sm-6">
                                    <div class="auth-field-group">
                                        <label for="regPassword" class="auth-label">Password</label>
                                        <div class="auth-input-wrapper">
                                            <span class="input-icon"><i class="bi bi-lock"></i></span>
                                            <input type="password" 
                                                   id="regPassword" 
                                                   name="password" 
                                                   class="auth-input auth-password-field" 
                                                   placeholder="Min 6 characters" 
                                                   autocomplete="new-password" 
                                                   required>
                                            <button type="button" class="btn-password-toggle" data-target="regPassword" aria-label="Toggle password visibility">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="auth-field-error" id="regPasswordError"></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="auth-field-group">
                                        <label for="regConfirmPassword" class="auth-label">Confirm Password</label>
                                        <div class="auth-input-wrapper">
                                            <span class="input-icon"><i class="bi bi-shield-lock"></i></span>
                                            <input type="password" 
                                                   id="regConfirmPassword" 
                                                   name="confirm_password" 
                                                   class="auth-input auth-password-field" 
                                                   placeholder="Re-enter password" 
                                                   autocomplete="new-password" 
                                                   required>
                                            <button type="button" class="btn-password-toggle" data-target="regConfirmPassword" aria-label="Toggle confirm password visibility">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="auth-field-error" id="regConfirmPasswordError"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms Checkbox -->
                            <div class="auth-field-group mb-4">
                                <div class="form-check auth-checkbox">
                                    <input class="form-check-input" type="checkbox" name="terms" id="regTerms" required>
                                    <label class="form-check-label" for="regTerms">
                                        I agree to the <a href="<?= BASE_URL ?>about.php#terms" class="text-primary text-decoration-none">Terms of Service</a> & <a href="<?= BASE_URL ?>about.php#privacy" class="text-primary text-decoration-none">Privacy Policy</a>
                                    </label>
                                </div>
                                <div class="auth-field-error" id="regTermsError"></div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-auth-primary w-100" id="registerSubmitBtn">
                                <span class="btn-text">CREATE ACCOUNT</span>
                                <i class="bi bi-person-plus ms-1 btn-icon"></i>
                                <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                            </button>
                        </form>

                        <!-- Switch to Login -->
                        <div class="auth-switch-prompt mt-4 pt-3 text-center">
                            <span class="text-muted small">Already have a Discora account?</span>
                            <button type="button" class="btn-switch-tab text-primary ms-1 fw-bold" id="goToLoginBtn">
                                Log In Here
                            </button>
                        </div>
                    </div>

                    <!-- ==============================================
                         2. LOGIN FORM VIEW
                         ============================================== -->
                    <div class="auth-form-view d-none" id="loginView">
                        <div class="auth-header mb-4">
                            <span class="auth-subtitle text-uppercase tracking-wider">Welcome Back</span>
                            <h2 class="auth-title font-heading" id="authModalHeading">ACCOUNT LOGIN</h2>
                            <p class="auth-desc text-muted">Enter your registered email and password to access your account.</p>
                        </div>

                        <form id="discoraLoginForm" action="<?= BASE_URL ?>actions/auth-action.php" method="POST" novalidate>
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect_target) ?>">
                            
                            <!-- Email Input -->
                            <div class="auth-field-group mb-3">
                                <label for="loginEmail" class="auth-label">Email Address or Username</label>
                                <div class="auth-input-wrapper">
                                    <span class="input-icon"><i class="bi bi-envelope"></i></span>
                                    <input type="text" 
                                           id="loginEmail" 
                                           name="email" 
                                           class="auth-input" 
                                           placeholder="e.g. alex@example.com or admin" 
                                           autocomplete="username" 
                                           required>
                                </div>
                                <div class="auth-field-error" id="loginEmailError"></div>
                            </div>

                            <!-- Password Input -->
                            <div class="auth-field-group mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="loginPassword" class="auth-label mb-0">Password</label>
                                    <a href="<?= BASE_URL ?>contact.php#support" class="auth-link-subtle" id="forgotPasswordTrigger">Forgot password?</a>
                                </div>
                                <div class="auth-input-wrapper">
                                    <span class="input-icon"><i class="bi bi-lock"></i></span>
                                    <input type="password" 
                                           id="loginPassword" 
                                           name="password" 
                                           class="auth-input auth-password-field" 
                                           placeholder="Enter your password" 
                                           autocomplete="current-password" 
                                           required>
                                    <button type="button" class="btn-password-toggle" data-target="loginPassword" aria-label="Toggle password visibility">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="auth-field-error" id="loginPasswordError"></div>
                            </div>

                            <!-- Remember Me -->
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div class="form-check auth-checkbox">
                                    <input class="form-check-input" type="checkbox" name="remember" id="loginRemember">
                                    <label class="form-check-label" for="loginRemember">
                                        Remember me on this device
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn-auth-primary w-100" id="loginSubmitBtn">
                                <span class="btn-text">SIGN IN</span>
                                <i class="bi bi-arrow-right-short fs-4 ms-1 btn-icon"></i>
                                <span class="spinner-border spinner-border-sm d-none ms-2" role="status" aria-hidden="true"></span>
                            </button>
                        </form>

                        <!-- Switch to Register -->
                        <div class="auth-switch-prompt mt-4 pt-3 text-center">
                            <span class="text-muted small">Don't have a Discora account?</span>
                            <button type="button" class="btn-switch-tab text-primary ms-1 fw-bold" id="goToRegisterBtn">
                                Create an Account
                            </button>
                        </div>

                        <!-- Quick Demo Credentials Hint -->
                        <div class="demo-creds-box mt-3 p-2 text-center" title="Click any credential to autofill">
                            <span class="small d-block">Demo Customer: <code class="demo-cred-fill" data-email="alex@example.com" data-pass="customer123">alex@example.com</code> / <code class="demo-cred-fill" data-email="alex@example.com" data-pass="customer123">customer123</code></span>
                            <span class="small d-block">Demo Admin: <code class="demo-cred-fill" data-email="admin@discora.com" data-pass="admin123">admin@discora.com</code> / <code class="demo-cred-fill" data-email="admin@discora.com" data-pass="admin123">admin123</code></span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
