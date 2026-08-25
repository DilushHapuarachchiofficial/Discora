<?php
/**
 * Discora - Responsive Login & Registration Popup Modal Component
 * Matches Discora Gaming Theme, Colors, Typography & GSAP Animations
 */
?>
<!-- Discora Authentication Modal Overlay -->
<div class="discora-auth-overlay" id="discoraAuthOverlay" aria-hidden="true">
    <div class="discora-auth-modal" id="discoraAuthModal" role="dialog" aria-modal="true" aria-labelledby="authModalHeading">
        
        <!-- Modal Close Button -->
        <button type="button" class="auth-modal-close" id="authModalCloseBtn" aria-label="Close Authentication Dialog">
            <i class="bi bi-x-lg"></i>
        </button>

        <div class="auth-modal-grid">
            
            <!-- Left Column: Gaming Visual & Brand Story -->
            <div class="auth-visual-pane">
                <div class="auth-visual-overlay"></div>
                <div class="auth-visual-content">
                    
                    <!-- Discora Logo (Centered & Enlarged) -->
                    <div class="auth-brand-badge text-center mb-4">
                        <img src="<?= ASSETS_PATH ?>images/logos/discora-logo.png" alt="Discora Logo" class="auth-brand-logo">
                    </div>

                    <!-- Visual Headline & Badge -->
                    <div class="auth-gaming-badge">
                        <i class="bi bi-controller text-primary me-2"></i>
                        <span>Physical Gaming Hub</span>
                    </div>

                    <h3 class="auth-visual-title font-heading">
                        UNLOCK THE <span class="text-gradient-ps">ULTIMATE</span> GAMING VAULT
                    </h3>

                    <p class="auth-visual-desc">
                        Your destination for authentic PlayStation & Xbox physical discs, next-gen hardware bundles, and collector editions.
                    </p>

                    <!-- Features Checklist -->
                    <div class="auth-features-list">
                        <div class="auth-feature-item">
                            <i class="bi bi-disc-fill text-primary"></i>
                            <span>100% Genuine Factory-Sealed Discs</span>
                        </div>
                        <div class="auth-feature-item">
                            <i class="bi bi-truck text-success"></i>
                            <span>Fast Island-wide Express Delivery</span>
                        </div>
                        <div class="auth-feature-item">
                            <i class="bi bi-shield-check text-info"></i>
                            <span>Exclusive Member Drops & Pre-Orders</span>
                        </div>
                    </div>

                    <!-- Platform Badges Footer -->
                    <div class="auth-visual-footer">
                        <span class="platform-badge ps-badge"><i class="bi bi-playstation me-1"></i> PS5 / PS4</span>
                        <span class="platform-badge xbox-badge"><i class="bi bi-xbox me-1"></i> Xbox Series X|S</span>
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
                     1. LOGIN FORM VIEW
                     ============================================== -->
                <div class="auth-form-view active" id="loginView">
                    <div class="auth-header mb-4">
                        <span class="auth-subtitle text-uppercase tracking-wider">Welcome Back</span>
                        <h2 class="auth-title font-heading" id="authModalHeading">ACCOUNT LOGIN</h2>
                        <p class="auth-desc text-muted">Enter your registered email and password to access your account.</p>
                    </div>

                    <form id="discoraLoginForm" action="<?= BASE_URL ?>actions/auth-action.php" method="POST" novalidate>
                        <input type="hidden" name="action" value="login">
                        
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
                                <a href="javascript:void(0)" class="auth-link-subtle" id="forgotPasswordTrigger">Forgot password?</a>
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
                    <div class="auth-switch-prompt mt-4 pt-3 text-center border-top border-secondary border-opacity-25">
                        <span class="text-muted small">Don't have a Discora account?</span>
                        <button type="button" class="btn-switch-tab text-primary ms-1 fw-bold" id="goToRegisterBtn">
                            Create an Account
                        </button>
                    </div>

                    <!-- Quick Demo Credentials Hint (Ideal for Testing) -->
                    <div class="demo-creds-box mt-3 p-2 rounded bg-black bg-opacity-50 border border-secondary border-opacity-25 text-center">
                        <span class="small text-secondary d-block">Demo Customer: <code>alex@example.com</code> / <code>customer123</code></span>
                        <span class="small text-secondary d-block">Demo Admin: <code>admin@discora.com</code> / <code>admin123</code></span>
                    </div>
                </div>

                <!-- ==============================================
                     2. REGISTER FORM VIEW
                     ============================================== -->
                <div class="auth-form-view d-none" id="registerView">
                    <div class="auth-header mb-4">
                        <span class="auth-subtitle text-uppercase tracking-wider">New to Discora</span>
                        <h2 class="auth-title font-heading">CREATE ACCOUNT</h2>
                        <p class="auth-desc text-muted">Join Discora to track your disc orders, save wishlists, and receive member perks.</p>
                    </div>

                    <form id="discoraRegisterForm" action="<?= BASE_URL ?>actions/auth-action.php" method="POST" novalidate>
                        <input type="hidden" name="action" value="register">

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
                                    I agree to the <a href="javascript:void(0)" class="text-primary text-decoration-none">Terms of Service</a> & <a href="javascript:void(0)" class="text-primary text-decoration-none">Privacy Policy</a>
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
                    <div class="auth-switch-prompt mt-4 pt-3 text-center border-top border-secondary border-opacity-25">
                        <span class="text-muted small">Already have a Discora account?</span>
                        <button type="button" class="btn-switch-tab text-primary ms-1 fw-bold" id="goToLoginBtn">
                            Log In Here
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
