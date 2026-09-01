<?php
/**
 * Discora - All Products Catalog Page
 * Fully dynamic database-driven product catalog with search, multi-filters, sorting, and pagination
 */

require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
require_once __DIR__ . '/core/cart.php';
require_once __DIR__ . '/core/wishlist.php';

$page_title = "All Games & Hardware Catalog";
$page_css   = ['products.css'];
$page_js    = ['products.js'];
$body_class = 'page-products';

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

$filters = [
    'search'    => $search,
    'platform'  => $platforms,
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
$paginationUrl = 'products.php?' . ($baseQuery ? $baseQuery . '&' : '') . 'page=';

// Template Configuration Variables
$page_heading      = 'ALL PRODUCTS';
$page_subheading   = 'EXPLORE OUR COLLECTION';
$page_description  = 'Browse our entire physical catalogue of genuine factory-sealed games, consoles, and accessories across all platforms.';
$badge_text        = 'COMPLETE CATALOGUE';
$badge_icon        = 'bi-collection';
$empty_state_title = 'NO PRODUCTS FOUND';
$empty_state_desc  = 'No products match your active filter combination.';
$empty_state_btn   = 'Reset Filters';
$empty_state_url   = 'products.php';
$page_url          = 'products.php';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/product-listing-template.php';
require_once __DIR__ . '/includes/footer.php';
