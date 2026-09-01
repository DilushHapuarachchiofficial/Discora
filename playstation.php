<?php
/**
 * Discora - PlayStation Hub Landing Page
 * Dynamically queries PS5 and PS4 games from MySQL discora_db
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';

$page_title = "PlayStation Games & Discs";
$page_css   = ['products.css'];
$page_js    = ['products.js'];
$body_class = 'page-playstation';

$search     = sanitize_input($_GET['search'] ?? '');
$categories = isset($_GET['category']) ? (array)$_GET['category'] : [];
$min_price  = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? $_GET['min_price'] : null;
$max_price  = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? $_GET['max_price'] : null;
$in_stock   = !empty($_GET['in_stock']);
$sort       = sanitize_input($_GET['sort'] ?? 'newest');
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = 12;

$filters = [
    'search'    => $search,
    'platform'  => ['ps5', 'ps4'],
    'category'  => $categories,
    'min_price' => $min_price,
    'max_price' => $max_price,
    'in_stock'  => $in_stock
];

$result = get_filtered_products($filters, $sort, $page, $per_page);
$catalog_products = $result['products'];
$total_products   = $result['total'];
$total_pages      = $result['total_pages'];
$current_page     = $result['current_page'];

$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$paginationUrl = 'playstation.php?' . ($baseQuery ? $baseQuery . '&' : '') . 'page=';

$page_heading      = 'PLAYSTATION HUB';
$page_subheading   = 'PLAY HAS NO LIMITS';
$page_description  = 'Explore authentic PS5 and PS4 physical game discs, DualSense controllers, and collector box editions.';
$badge_text        = 'PLAYSTATION HUB';
$badge_icon        = 'bi-playstation';
$empty_state_title = 'NO PLAYSTATION PRODUCTS FOUND';
$empty_state_desc  = 'No PlayStation products match your active filter combination.';
$empty_state_btn   = 'Reset Filters';
$empty_state_url   = 'playstation.php';
$page_url          = 'playstation.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product-listing-template.php';
require_once __DIR__ . '/includes/footer.php';
