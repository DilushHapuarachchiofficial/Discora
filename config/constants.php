<?php
/**
 * Discora - Global Configuration Constants
 */

// Application Info
define('APP_NAME', 'Discora');
define('APP_TAGLINE', 'Your Ultimate PlayStation & Xbox Store');
define('APP_VERSION', '1.0.0');

// Base URL (Auto-detected reliably)
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$app_root = str_replace('\\', '/', dirname(__DIR__));
$base_dir = str_replace($doc_root, '', $app_root);
$base_url = rtrim($base_dir, '/') . '/';
define('BASE_URL', $base_url);
define('ADMIN_URL', BASE_URL . 'admin/');

// Absolute Directory Paths
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', ROOT_PATH . 'config' . DIRECTORY_SEPARATOR);
define('CORE_PATH', ROOT_PATH . 'core' . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', ROOT_PATH . 'includes' . DIRECTORY_SEPARATOR);
define('ASSETS_PATH', BASE_URL . 'assets/');
define('UPLOADS_PATH', ROOT_PATH . 'uploads' . DIRECTORY_SEPARATOR);
define('UPLOADS_URL', BASE_URL . 'uploads/');

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'discora_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Store Settings
define('CURRENCY_SYMBOL', 'Rs. ');
define('CURRENCY_CODE', 'USD');
define('TAX_RATE', 0.08); // 8% sales tax
define('SHIPPING_FEE_STANDARD', 4.99);
define('FREE_SHIPPING_THRESHOLD', 50.00);
