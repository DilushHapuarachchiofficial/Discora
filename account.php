<?php
/**
 * Discora - Customer Profile & Account Details Page
 */
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/functions.php';

require_login('login.php');

$page_title = "My Account";
$user = current_user();
$db = get_db_connection();
$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''), ENT_QUOTES, 'UTF-8');
            $address_line1 = htmlspecialchars(trim($_POST['address_line1'] ?? ''), ENT_QUOTES, 'UTF-8');
            $city = htmlspecialchars(trim($_POST['city'] ?? ''), ENT_QUOTES, 'UTF-8');
            $postal_code = htmlspecialchars(trim($_POST['postal_code'] ?? ''), ENT_QUOTES, 'UTF-8');
            
            $current_password = $_POST['current_password'] ?? '';
            $new_password     = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($full_name)) {
                $error = 'Full Name is required.';
            } else {
                // If user wants to change password, verify current password and confirm password
                if (!empty($new_password) || !empty($confirm_password) || !empty($current_password)) {
                    if (empty($current_password)) {
                        $error = 'You must enter your current password to set a new password.';
                    } elseif (empty($new_password)) {
                        $error = 'Please enter a new password.';
                    } elseif (empty($confirm_password)) {
                        $error = 'Please confirm your new password.';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'New password and Confirm password do not match.';
                    } elseif (strlen($new_password) < 6) {
                        $error = 'New password must be at least 6 characters long.';
                    } else {
                        $stmt = $db->prepare("SELECT password FROM users WHERE user_id = ?");
                        $stmt->execute([$user_id]);
                        $db_pass = $stmt->fetchColumn();

                        if (!password_verify($current_password, $db_pass)) {
                            $error = 'The current password you entered is incorrect.';
                        }
                    }
                }
            }

            if (empty($error)) {
                $avatar_path = null;
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/assets/images/users/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                    $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                    if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                        $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
                        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $new_filename)) {
                            $avatar_path = 'assets/images/users/' . $new_filename;
                        }
                    }
                }
                
                // Update profile with or without password
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    if ($avatar_path) {
                        $upd = $db->prepare("UPDATE users SET full_name = ?, avatar = ?, password = ? WHERE user_id = ?");
                        $res = $upd->execute([$full_name, $avatar_path, $hashed_password, $user_id]);
                    } else {
                        $upd = $db->prepare("UPDATE users SET full_name = ?, password = ? WHERE user_id = ?");
                        $res = $upd->execute([$full_name, $hashed_password, $user_id]);
                    }
                } else {
                    if ($avatar_path) {
                        $upd = $db->prepare("UPDATE users SET full_name = ?, avatar = ? WHERE user_id = ?");
                        $res = $upd->execute([$full_name, $avatar_path, $user_id]);
                    } else {
                        $upd = $db->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
                        $res = $upd->execute([$full_name, $user_id]);
                    }
                }
                
                if ($res) {
                    $_SESSION['user_name'] = $full_name;
                    $user['name'] = $full_name; // update local var for rendering
                    if ($avatar_path) {
                        $_SESSION['user_avatar'] = $avatar_path;
                        $user['avatar'] = $avatar_path;
                    }
                    
                    // Update or Insert Address
                    if (!empty($address_line1) && !empty($city) && !empty($postal_code)) {
                        $stmt = $db->prepare("SELECT address_id FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
                        $stmt->execute([$user_id]);
                        $address_id = $stmt->fetchColumn();
                        
                        if ($address_id) {
                            $upd_addr = $db->prepare("UPDATE addresses SET address_line1 = ?, city = ?, postal_code = ? WHERE address_id = ?");
                            $upd_addr->execute([$address_line1, $city, $postal_code, $address_id]);
                        } else {
                            $ins_addr = $db->prepare("INSERT INTO addresses (user_id, recipient_name, phone, address_line1, city, postal_code, is_default) VALUES (?, ?, '000000000', ?, ?, ?, 1)");
                            $ins_addr->execute([$user_id, $full_name, $address_line1, $city, $postal_code]);
                        }
                    }
                    
                    $success = 'Profile updated successfully.';
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    } else {
        $error = 'Invalid security token.';
    }
}

// Fetch default address
$stmt = $db->prepare("SELECT address_line1, city, postal_code FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1");
$stmt->execute([$user_id]);
$address = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['address_line1' => '', 'city' => '', 'postal_code' => ''];

require_once __DIR__ . '/includes/header.php';
?>
<style>
    body {
        background-color: #f8fafc !important;
        color: #0f172a !important;
    }
    .profile-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
    }
    .profile-sidebar {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
    }
    .profile-nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        border-radius: 10px;
        font-weight: 500;
        color: #475569;
        text-decoration: none;
        transition: all 0.2s ease;
        margin-bottom: 0.35rem;
    }
    .profile-nav-link:hover {
        background: #f1f5f9;
        color: #0f172a;
    }
    .profile-nav-link.active {
        background: rgba(13, 110, 253, 0.08);
        color: #0d6efd;
        font-weight: 600;
    }
    .profile-section-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid #f1f5f9;
    }
    .profile-section-header:first-of-type {
        margin-top: 0;
        padding-top: 0;
        border-top: none;
    }
    .modern-form-group {
        margin-bottom: 0.25rem;
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
    .modern-input-wrapper.is-disabled {
        background: #f1f5f9;
        border-color: #e2e8f0;
    }
    .modern-input-wrapper.is-disabled .form-control {
        color: #64748b !important;
        cursor: not-allowed;
    }
    .modern-input-wrapper.is-file {
        padding: 0.25rem;
    }
    .modern-input-wrapper.is-file .form-control {
        padding: 0.45rem 0.75rem;
    }
    .btn-toggle-eye {
        border: none;
        background: transparent;
        color: #94a3b8;
        padding: 0 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        transition: color 0.15s ease;
    }
    .btn-toggle-eye:hover {
        color: #0f172a;
    }
    .btn-modern-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: #ffffff;
        font-weight: 600;
        padding: 0.75rem 2rem;
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 14px rgba(13, 110, 253, 0.25);
        transition: all 0.2s ease;
    }
    .btn-modern-primary:hover {
        background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(13, 110, 253, 0.35);
        color: #ffffff;
    }
</style>

<div class="container my-5">
    <div class="row g-4">
        <!-- Account Sidebar -->
        <div class="col-lg-4 col-md-5">
            <div class="profile-sidebar p-4 text-center">
                <div class="position-relative d-inline-block mb-3">
                    <div class="rounded-circle p-1 bg-white shadow-sm border" style="width: 104px; height: 104px; overflow: hidden; margin: 0 auto;">
                        <?php if (!empty($user['avatar'])): ?>
                            <img src="<?= BASE_URL . htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;" class="rounded-circle">
                        <?php else: ?>
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle">
                                <i class="bi bi-person fs-1"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <h5 class="text-dark fw-bold mb-1 font-heading"><?= htmlspecialchars($user['name']) ?></h5>
                <p class="text-muted small mb-4"><?= htmlspecialchars($user['email']) ?></p>

                <div class="d-flex flex-column gap-1 text-start">
                    <a href="<?= BASE_URL ?>account.php" class="profile-nav-link active">
                        <i class="bi bi-person-fill me-2 fs-5"></i> Profile Details
                    </a>
                    <a href="<?= BASE_URL ?>orders.php" class="profile-nav-link">
                        <i class="bi bi-bag-check me-2 fs-5"></i> My Orders
                    </a>
                    <a href="<?= BASE_URL ?>wishlist.php" class="profile-nav-link">
                        <i class="bi bi-heart me-2 fs-5"></i> My Wishlist
                    </a>
                    <hr class="my-2 border-secondary border-opacity-25">
                    <a href="<?= BASE_URL ?>logout.php" class="profile-nav-link text-danger">
                        <i class="bi bi-box-arrow-right me-2 fs-5"></i> Logout
                    </a>
                </div>
            </div>
        </div>

        <!-- Account Info Form -->
        <div class="col-lg-8 col-md-7">
            <div class="profile-card p-4 p-sm-5">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div>
                        <h4 class="text-dark fw-bold mb-1 font-heading">Account Information</h4>
                        <span class="text-muted small">Update your personal profile, shipping address, and security credentials</span>
                    </div>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger rounded-3 d-flex align-items-center gap-2 mb-4 shadow-sm">
                        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success rounded-3 d-flex align-items-center gap-2 mb-4 shadow-sm">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div><?= $success ?></div>
                    </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <!-- Section 1: Personal Details -->
                    <div class="profile-section-header">
                        <i class="bi bi-person-vcard text-primary fs-5"></i>
                        <span>Personal Details</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 modern-form-group">
                            <label class="modern-label">Profile Image</label>
                            <div class="modern-input-wrapper is-file">
                                <span class="input-prefix-icon"><i class="bi bi-image"></i></span>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6 modern-form-group">
                            <label class="modern-label">Full Name</label>
                            <div class="modern-input-wrapper">
                                <span class="input-prefix-icon"><i class="bi bi-person"></i></span>
                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required placeholder="Your full name">
                            </div>
                        </div>
                        <div class="col-md-6 modern-form-group">
                            <label class="modern-label">Email Address</label>
                            <div class="modern-input-wrapper is-disabled" title="Email address cannot be changed">
                                <span class="input-prefix-icon"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Shipping Address -->
                    <div class="profile-section-header">
                        <i class="bi bi-truck text-success fs-5"></i>
                        <span>Default Shipping Address</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 modern-form-group">
                            <label class="modern-label">Street Address</label>
                            <div class="modern-input-wrapper">
                                <span class="input-prefix-icon"><i class="bi bi-geo-alt"></i></span>
                                <input type="text" name="address_line1" class="form-control" value="<?= htmlspecialchars($address['address_line1']) ?>" placeholder="House / Building No, Street address">
                            </div>
                        </div>
                        <div class="col-md-6 modern-form-group">
                            <label class="modern-label">City</label>
                            <div class="modern-input-wrapper">
                                <span class="input-prefix-icon"><i class="bi bi-buildings"></i></span>
                                <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($address['city']) ?>" placeholder="City / Town">
                            </div>
                        </div>
                        <div class="col-md-6 modern-form-group">
                            <label class="modern-label">Postal Code</label>
                            <div class="modern-input-wrapper">
                                <span class="input-prefix-icon"><i class="bi bi-mailbox"></i></span>
                                <input type="text" name="postal_code" class="form-control" value="<?= htmlspecialchars($address['postal_code']) ?>" placeholder="Postal Code (e.g. 10100)">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Change Password -->
                    <div class="profile-section-header">
                        <i class="bi bi-shield-lock-fill text-warning fs-5"></i>
                        <span>Security & Password</span>
                    </div>
                    <p class="text-muted small mb-3">Leave these fields blank if you do not wish to change your password.</p>

                    <div class="row g-3">
                        <div class="col-12 modern-form-group">
                            <label class="modern-label">Current Password</label>
                            <div class="modern-input-wrapper">
                                <span class="input-prefix-icon"><i class="bi bi-lock"></i></span>
                                <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Required only if changing password">
                                <button type="button" class="btn-toggle-eye" data-target="current_password" aria-label="Toggle password visibility"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6 modern-form-group">
                            <label class="modern-label">New Password</label>
                            <div class="modern-input-wrapper">
                                <span class="input-prefix-icon"><i class="bi bi-key"></i></span>
                                <input type="password" name="new_password" id="new_password" class="form-control" placeholder="New Password (min 6 characters)">
                                <button type="button" class="btn-toggle-eye" data-target="new_password" aria-label="Toggle password visibility"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <div class="col-md-6 modern-form-group">
                            <label class="modern-label">Confirm New Password</label>
                            <div class="modern-input-wrapper" id="confirmPassWrapper">
                                <span class="input-prefix-icon"><i class="bi bi-shield-check"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Re-enter new password">
                                <button type="button" class="btn-toggle-eye" data-target="confirm_password" aria-label="Toggle password visibility"><i class="bi bi-eye"></i></button>
                            </div>
                            <div id="passwordMatchMsg" class="small mt-1 ps-1"></div>
                        </div>

                        <div class="col-12 mt-4 pt-2">
                            <button type="submit" class="btn btn-modern-primary">
                                <i class="bi bi-check2-circle me-1"></i> Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const newPass = document.getElementById('new_password');
    const confirmPass = document.getElementById('confirm_password');
    const confirmWrapper = document.getElementById('confirmPassWrapper');
    const matchMsg = document.getElementById('passwordMatchMsg');

    function checkMatch() {
        if (!confirmPass.value && !newPass.value) {
            matchMsg.innerHTML = '';
            confirmWrapper.style.borderColor = '#cbd5e1';
            return;
        }
        if (!confirmPass.value) {
            matchMsg.innerHTML = '';
            confirmWrapper.style.borderColor = '#cbd5e1';
            return;
        }
        if (newPass.value === confirmPass.value) {
            matchMsg.innerHTML = '<span class="text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Passwords match</span>';
            confirmWrapper.style.borderColor = '#198754';
        } else {
            matchMsg.innerHTML = '<span class="text-danger fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Passwords do not match</span>';
            confirmWrapper.style.borderColor = '#dc3545';
        }
    }

    if (newPass && confirmPass) {
        newPass.addEventListener('input', checkMatch);
        confirmPass.addEventListener('input', checkMatch);
    }

    // Toggle Eye buttons for Password fields
    document.querySelectorAll('.btn-toggle-eye').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
