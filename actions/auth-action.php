<?php
/**
 * Discora - Authentication Processing Endpoint (Login / Register / Logout)
 * Connects securely with discora_db MySQL Database via PDO
 * Supports both JSON AJAX Responses and Standard HTTP Redirects
 */

require_once dirname(__DIR__) . '/config/db.php';
require_once dirname(__DIR__) . '/config/session.php';
require_once dirname(__DIR__) . '/core/functions.php';

// Helper to check if request is AJAX
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') 
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

function send_auth_response(bool $success, string $message, ?string $redirect = null, bool $is_ajax = false) {
    if ($is_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'  => $success,
            'message'  => $message,
            'redirect' => $redirect
        ]);
        exit;
    } else {
        set_flash_message($success ? 'success' : 'danger', $message);
        if ($redirect) {
            redirect($redirect);
        } else {
            redirect(BASE_URL . 'index.php');
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $pdo = Database::getConnection();

    // =========================================================
    // 1. LOGIN PROCESSING
    // =========================================================
    if ($action === 'login') {
        $login_identity = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($login_identity) || empty($password)) {
            send_auth_response(false, 'Please provide both email/username and password.', null, $is_ajax);
        }

        try {
            // Find user by email or username
            $stmt = $pdo->prepare("
                SELECT u.user_id, u.role_id, u.full_name, u.username, u.email, u.password, u.status, r.role_name
                FROM users u
                JOIN roles r ON u.role_id = r.role_id
                WHERE u.email = :email OR u.username = :username
                LIMIT 1
            ");
            $stmt->execute([
                ':email' => $login_identity,
                ':username' => $login_identity
            ]);
            $user = $stmt->fetch();

            if (!$user) {
                send_auth_response(false, 'No account found with that email or username.', null, $is_ajax);
            }

            if ($user['status'] !== 'Active') {
                send_auth_response(false, 'Your account is currently inactive or suspended. Please contact support.', null, $is_ajax);
            }

            // Verify password hash
            if (!password_verify($password, $user['password'])) {
                send_auth_response(false, 'Incorrect password. Please try again.', null, $is_ajax);
            }

            // Set session variables
            $_SESSION['user_id']   = (int)$user['user_id'];
            $_SESSION['role_id']   = (int)$user['role_id'];
            $_SESSION['user_name'] = $user['full_name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = strtolower($user['role_name']);

            // If Remember Me selected, set secure cookie
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('discora_remember', $token, time() + (86400 * 30), "/"); // 30 days
            }

            // Determine redirect URL
            $redirect_url = strtolower($user['role_name']) === 'admin' ? ADMIN_URL : BASE_URL . 'index.php';
            
            // Check if there was an intended redirect saved in session
            if (!empty($_SESSION['redirect_after_login'])) {
                $redirect_url = $_SESSION['redirect_after_login'];
                unset($_SESSION['redirect_after_login']);
            }

            send_auth_response(true, 'Welcome back, ' . $user['full_name'] . '!', $redirect_url, $is_ajax);

        } catch (PDOException $e) {
            send_auth_response(false, 'A database error occurred during login. Please try again later.', null, $is_ajax);
        }
    }

    // =========================================================
    // 2. REGISTRATION PROCESSING
    // =========================================================
    elseif ($action === 'register') {
        $first_name       = sanitize_input($_POST['first_name'] ?? '');
        $last_name        = sanitize_input($_POST['last_name'] ?? '');
        $email            = sanitize_input($_POST['email'] ?? '');
        $phone            = sanitize_input($_POST['phone'] ?? '');
        $password         = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $terms            = isset($_POST['terms']);

        // Validation
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
            send_auth_response(false, 'All fields are required.', null, $is_ajax);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            send_auth_response(false, 'Please enter a valid email address.', null, $is_ajax);
        }

        if (strlen($password) < 6) {
            send_auth_response(false, 'Password must be at least 6 characters long.', null, $is_ajax);
        }

        if ($password !== $confirm_password) {
            send_auth_response(false, 'Passwords do not match.', null, $is_ajax);
        }

        if (!$terms) {
            send_auth_response(false, 'You must agree to the Terms of Service & Privacy Policy.', null, $is_ajax);
        }

        $full_name = trim($first_name . ' ' . $last_name);
        // Generate a base username from email or name
        $base_username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $email)[0]));
        if (empty($base_username)) {
            $base_username = 'gamer' . rand(100, 9999);
        }

        try {
            // Check if email already registered
            $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email LIMIT 1");
            $checkStmt->execute([':email' => $email]);
            if ($checkStmt->fetch()) {
                send_auth_response(false, 'An account is already registered with this email address.', null, $is_ajax);
            }

            // Ensure unique username
            $unique_username = $base_username;
            $userCheck = $pdo->prepare("SELECT user_id FROM users WHERE username = :uname LIMIT 1");
            $userCheck->execute([':uname' => $unique_username]);
            if ($userCheck->fetch()) {
                $unique_username = $base_username . rand(10, 999);
            }

            // Hash password securely with BCRYPT
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new customer account (role_id = 2 -> Customer)
            $insertStmt = $pdo->prepare("
                INSERT INTO users (role_id, full_name, username, email, password, phone, status)
                VALUES (2, :full_name, :username, :email, :password, :phone, 'Active')
            ");
            $insertStmt->execute([
                ':full_name' => $full_name,
                ':username'  => $unique_username,
                ':email'     => $email,
                ':password'  => $hashedPassword,
                ':phone'     => $phone
            ]);

            $new_user_id = (int)$pdo->lastInsertId();

            // Create initial shopping cart & wishlist for the user
            try {
                $cartStmt = $pdo->prepare("INSERT INTO carts (user_id) VALUES (:uid)");
                $cartStmt->execute([':uid' => $new_user_id]);

                $wishStmt = $pdo->prepare("INSERT INTO wishlists (user_id) VALUES (:uid)");
                $wishStmt->execute([':uid' => $new_user_id]);
            } catch (Exception $e) {
                // Non-fatal if tables handle on demand
            }

            // Auto-login newly registered user
            $_SESSION['user_id']    = $new_user_id;
            $_SESSION['role_id']    = 2;
            $_SESSION['user_name']  = $full_name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role']  = 'customer';

            send_auth_response(true, 'Account created successfully! Welcome to Discora, ' . $full_name . '.', BASE_URL . 'index.php', $is_ajax);

        } catch (PDOException $e) {
            send_auth_response(false, 'Registration error: ' . $e->getMessage(), null, $is_ajax);
        }
    }
}

// Redirect home on invalid direct GET access
redirect(BASE_URL . 'index.php');
