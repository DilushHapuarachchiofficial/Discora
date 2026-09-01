<?php
/**
 * Discora - Xbox Hub Landing Page
 * Dynamically queries Xbox Series X|S and Xbox One games from MySQL discora_db
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';

$page_title = "Xbox Games & Hardware";
$page_css   = ['products.css'];
$page_js    = ['products.js'];
$body_class = 'page-xbox';

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
    'platform'  => ['xbox-series-x-s', 'xbox-one'],
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
$paginationUrl = 'xbox.php?' . ($baseQuery ? $baseQuery . '&' : '') . 'page=';

$page_heading      = 'XBOX HUB';
$page_subheading   = 'POWER YOUR DREAMS';
$page_description  = 'Genuine physical game discs for Xbox Series X|S and Xbox One with Smart Delivery upgrades.';
$badge_text        = 'XBOX HUB';
$badge_icon        = 'bi-xbox';
$empty_state_title = 'NO XBOX PRODUCTS FOUND';
$empty_state_desc  = 'No Xbox products match your active filter combination.';
$empty_state_btn   = 'Reset Filters';
$empty_state_url   = 'xbox.php';
$page_url          = 'xbox.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product-listing-template.php';
require_once __DIR__ . '/includes/footer.php';
