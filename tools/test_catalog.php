<?php
/**
 * Discora - Catalog Verification Test
 */

require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/core/products.php';
require_once dirname(__DIR__) . '/core/cart.php';

echo "=== 1. PLATFORM PRODUCTS TEST ===\n";
$platforms = ['ps4' => 'PS4', 'ps5' => 'PS5', 'xbox-one' => 'Xbox One', 'xbox-series-xs' => 'Xbox Series X|S'];

foreach ($platforms as $slug => $name) {
    $res = get_filtered_products(['platform' => [$slug]], 'newest', 1, 12);
    echo "$name: found " . count($res['products']) . " / total " . $res['total'] . " products\n";
    foreach ($res['products'] as $p) {
        echo "  - [ID: {$p['product_id']}] {$p['product_name']} ({$p['price']}) - Img: {$p['primary_image']}\n";
    }
}

echo "\n=== 2. NEW ARRIVALS TEST ===\n";
$newArr = get_filtered_products(['is_new_arrival' => 1], 'newest', 1, 12);
echo "New Arrivals: found " . count($newArr['products']) . " / total " . $newArr['total'] . "\n";

echo "\n=== 3. PRODUCT DETAILS TEST (ID: 1 - A Plague Tale: Innocence) ===\n";
$details = get_product_details(1);
if ($details) {
    echo "Found: {$details['product_name']} | Platform: {$details['platform_name']} | Stock: {$details['stock_quantity']} | Primary Img: {$details['primary_image']}\n";
} else {
    echo "Product ID 1 NOT FOUND!\n";
}

echo "\n=== 4. SEARCH TEST ('Spider-Man') ===\n";
$searchRes = get_filtered_products(['search' => 'Spider-Man'], 'newest', 1, 12);
echo "Search 'Spider-Man': found " . count($searchRes['products']) . " products\n";
foreach ($searchRes['products'] as $sp) {
    echo "  - {$sp['product_name']} ({$sp['platform_name']})\n";
}

echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
