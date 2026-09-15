<?php
$admin_title = "Admin Profile";
require_once __DIR__ . '/includes/header.php';

$db = get_db_connection();
$user_id = $_SESSION['user_id'];

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verify_csrf_token($_POST['csrf_token'])) {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['password'] ?? '';
        
        if (empty($full_name) || empty($email)) {
            $error = 'Name and Email are required.';
        } else {
            // If user wants to change password, verify current password first
            if (!empty($new_password)) {
                $stmt = $db->prepare("SELECT password FROM users WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $db_pass = $stmt->fetchColumn();
                
                if (empty($current_password)) {
                    $error = 'You must enter your current password to set a new password.';
                } elseif (!password_verify($current_password, $db_pass)) {
                    $error = 'The current password you entered is incorrect.';
                }
            }
            
            $avatar_path = null;
            
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = dirname(__DIR__) . '/assets/images/users/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_extension;
                    $destination = $upload_dir . $new_filename;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destination)) {
                        $avatar_path = 'assets/images/users/' . $new_filename;
                    } else {
                        $error = 'Failed to move uploaded file.';
                    }
                } else {
                    $error = 'Invalid image format. Allowed formats: JPG, JPEG, PNG, GIF.';
                }
            }

            if (empty($error)) {
                if ($avatar_path) {
                    if (!empty($new_password)) {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $upd = $db->prepare("UPDATE users SET full_name = ?, email = ?, avatar = ?, password = ? WHERE user_id = ?");
                        $res = $upd->execute([$full_name, $email, $avatar_path, $hashed, $user_id]);
                    } else {
                        $upd = $db->prepare("UPDATE users SET full_name = ?, email = ?, avatar = ? WHERE user_id = ?");
                        $res = $upd->execute([$full_name, $email, $avatar_path, $user_id]);
                    }
                    if ($res) {
                        $_SESSION['user_avatar'] = $avatar_path;
                    }
                } else {
                    if (!empty($new_password)) {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $upd = $db->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?");
                        $res = $upd->execute([$full_name, $email, $hashed, $user_id]);
                    } else {
                        $upd = $db->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
                        $res = $upd->execute([$full_name, $email, $user_id]);
                    }
                }
                
                if ($res) {
                    $_SESSION['user_name'] = $full_name;
                    $success = 'Profile updated successfully.';
                } else {
                    $error = 'Failed to update profile.';
                }
            }
        }
    }
}

$stmt = $db->prepare("SELECT full_name, email, avatar FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white p-4 border-bottom">
                <h5 class="mb-0 fw-bold">My Profile</h5>
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <img src="<?= BASE_URL . htmlspecialchars($admin['avatar'] ?? 'assets/images/users/default-avatar.png') ?>" alt="Admin Avatar" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover;">
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Profile Image</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($admin['full_name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($admin['email'] ?? '') ?>" required>
                    </div>
                    
                    <h6 class="fw-bold border-bottom pb-2 mb-3">Change Password</h6>
                    
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Required only if changing password">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Leave blank to keep current password">
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
