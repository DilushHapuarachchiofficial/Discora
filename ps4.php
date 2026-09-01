<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';

$page_title = "PS4 Games & Accessories";
$page_css   = ['products.css'];
$page_js    = ['products.js'];
$body_class = 'page-ps4';

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
    'platform'  => ['ps4'],
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
$paginationUrl = 'ps4.php?' . ($baseQuery ? $baseQuery . '&' : '') . 'page=';

$page_heading      = 'PS4 GAMES';
$page_subheading   = 'PLAY YOUR FAVORITES';
$page_description  = 'Explore our massive collection of physical PlayStation 4 games. From greatest hits to hidden gems, find the perfect addition to your PS4 library.';
$badge_text        = 'PLAYSTATION 4';
$badge_icon        = 'bi-playstation';
$empty_state_title = 'NO PS4 PRODUCTS FOUND';
$empty_state_desc  = 'No PlayStation 4 products match your active filter combination.';
$empty_state_btn   = 'Reset Filters';
$empty_state_url   = 'ps4.php';
$page_url          = 'ps4.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product-listing-template.php';
require_once __DIR__ . '/includes/footer.php';
