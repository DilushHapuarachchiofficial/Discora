<?php
/**
 * Discora - New Arrivals & Hot Releases Page
 * Premium White / Light Theme Physical Gaming Store Catalog
 * Dynamic database-driven page displaying physical products with is_new_arrival = 1
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';

$page_title = "New Arrivals & Hot Releases";
$page_css   = ['products.css'];
$page_js    = ['products.js'];
$body_class = 'page-new-arrivals';

// Read query parameters
$search     = sanitize_input($_GET['search'] ?? '');
$platforms  = isset($_GET['platform']) ? (array)$_GET['platform'] : [];
$categories = isset($_GET['category']) ? (array)$_GET['category'] : [];
$min_price  = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? $_GET['min_price'] : null;
$max_price  = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? $_GET['max_price'] : null;
$in_stock   = !empty($_GET['in_stock']);
$sort       = sanitize_input($_GET['sort'] ?? 'newest');
$page       = max(1, (int)($_GET['page'] ?? 1));
$per_page   = 12;

// Filter explicitly for is_new_arrival = 1
$filters = [
    'search'         => $search,
    'platform'       => $platforms,
    'category'       => $categories,
    'min_price'      => $min_price,
    'max_price'      => $max_price,
    'in_stock'       => $in_stock,
    'is_new_arrival' => 1
];

$result = get_filtered_products($filters, $sort, $page, $per_page);
$catalog_products = $result['products'];
$total_products   = $result['total'];
$total_pages      = $result['total_pages'];
$current_page     = $result['current_page'];

$queryParams = $_GET;
unset($queryParams['page']);
$baseQuery = http_build_query($queryParams);
$paginationUrl = 'new-arrivals.php?' . ($baseQuery ? $baseQuery . '&' : '') . 'page=';

// Template Configuration Variables
$page_heading      = 'NEW ARRIVALS';
$page_subheading   = 'HOT RELEASES';
$page_description  = 'Discover the latest physical game releases and bring your next adventure home. Genuine factory-sealed physical discs, steelbook day-one launch editions, and exclusive console releases.';
$badge_text        = 'JUST DROPPED & PRE-ORDERS';
$badge_icon        = 'bi-fire';
$empty_state_title = 'NO NEW ARRIVALS FOUND';
$empty_state_desc  = 'No new arrivals match your active filter combination.';
$empty_state_btn   = 'Reset Filters';
$empty_state_url   = 'new-arrivals.php';
$page_url          = 'new-arrivals.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product-listing-template.php';
require_once __DIR__ . '/includes/footer.php';
