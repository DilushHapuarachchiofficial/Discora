<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';

$page_title = "PS5 Games & Consoles";
$page_css   = ['products.css'];
$page_js    = ['products.js'];
$body_class = 'page-ps5';

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
    'platform'  => ['ps5'],
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
$paginationUrl = 'ps5.php?' . ($baseQuery ? $baseQuery . '&' : '') . 'page=';

$page_heading      = 'PS5 GAMES';
$page_subheading   = 'NEXT-GEN ADVENTURES';
$page_description  = 'Experience lightning-fast loading, deeper immersion with haptic feedback, and an all-new generation of incredible PlayStation games.';
$badge_text        = 'PLAYSTATION 5';
$badge_icon        = 'bi-playstation';
$empty_state_title = 'NO PS5 PRODUCTS FOUND';
$empty_state_desc  = 'No PlayStation 5 products match your active filter combination.';
$empty_state_btn   = 'Reset Filters';
$empty_state_url   = 'ps5.php';
$page_url          = 'ps5.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product-listing-template.php';
require_once __DIR__ . '/includes/footer.php';
