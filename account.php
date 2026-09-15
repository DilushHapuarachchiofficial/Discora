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
            
            if (empty($full_name)) {
                $error = 'Full Name is required.';
            } else {
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
                
                if ($avatar_path) {
                    $upd = $db->prepare("UPDATE users SET full_name = ?, avatar = ? WHERE user_id = ?");
                    $res = $upd->execute([$full_name, $avatar_path, $user_id]);
                    if ($res) {
                        $_SESSION['user_avatar'] = $avatar_path;
                        $user['avatar'] = $avatar_path;
                    }
                } else {
                    // Update Name Only
                    $upd = $db->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
                    $res = $upd->execute([$full_name, $user_id]);
                }
                
                if ($res) {
                    $_SESSION['user_name'] = $full_name;
                    $user['name'] = $full_name; // update local var for rendering
                    
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
                    <a href="<?= BASE_URL ?>account.php" class="list-group-item list-group-item-action bg-transparent text-primary fw-bold border-bottom"><i class="bi bi-person me-2"></i>Profile Info</a>
                    <a href="<?= BASE_URL ?>orders.php" class="list-group-item list-group-item-action bg-transparent text-dark border-bottom"><i class="bi bi-bag-check me-2"></i>My Orders</a>
                    <a href="<?= BASE_URL ?>wishlist.php" class="list-group-item list-group-item-action bg-transparent text-dark border-bottom"><i class="bi bi-heart me-2"></i>My Wishlist</a>
                    <a href="<?= BASE_URL ?>logout.php" class="list-group-item list-group-item-action bg-transparent text-danger border-0"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                </div>
            </div>
        </div>

        <!-- Account Info Form -->
        <div class="col-md-8">
            <div class="card bg-white shadow-sm border-0 p-4 rounded-3">
                <h5 class="text-dark fw-bold mb-4">Account Information</h5>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= $success ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted small">Profile Image</label>
                            <input type="file" name="avatar" class="form-control bg-light border-light text-dark" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Full Name</label>
                            <input type="text" name="full_name" class="form-control bg-light border-light text-dark" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Email</label>
                            <input type="email" class="form-control bg-light border-light text-dark" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                        </div>
                        <div class="col-12 mt-4">
                            <h6 class="text-dark fw-bold mb-3">Shipping Address</h6>
                        </div>
                        <div class="col-12 mt-0">
                            <label class="form-label text-muted small">Street Address</label>
                            <input type="text" name="address_line1" class="form-control bg-light border-light text-dark" value="<?= htmlspecialchars($address['address_line1']) ?>" placeholder="Street Address">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">City</label>
                            <input type="text" name="city" class="form-control bg-light border-light text-dark" value="<?= htmlspecialchars($address['city']) ?>" placeholder="City">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Postal Code</label>
                            <input type="text" name="postal_code" class="form-control bg-light border-light text-dark" value="<?= htmlspecialchars($address['postal_code']) ?>" placeholder="Postal Code">
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4">Update Profile</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
