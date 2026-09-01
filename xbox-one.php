<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';

$page_title = "Xbox One Games & Accessories";
$page_css   = ['products.css'];
$page_js    = ['products.js'];
$body_class = 'page-xbox-one';

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
    'platform'  => ['xbox-one'],
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
$paginationUrl = 'xbox-one.php?' . ($baseQuery ? $baseQuery . '&' : '') . 'page=';

$page_heading      = 'XBOX ONE GAMES';
$page_subheading   = 'START YOUR ADVENTURE';
$page_description  = 'Discover our huge library of physical Xbox One games. From blockbuster exclusives to amazing indie titles.';
$badge_text        = 'XBOX ONE';
$badge_icon        = 'bi-xbox';
$empty_state_title = 'NO XBOX ONE PRODUCTS FOUND';
$empty_state_desc  = 'No Xbox One products match your active filter combination.';
$empty_state_btn   = 'Reset Filters';
$empty_state_url   = 'xbox-one.php';
$page_url          = 'xbox-one.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product-listing-template.php';
require_once __DIR__ . '/includes/footer.php';
