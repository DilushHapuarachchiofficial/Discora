<?php
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/core/products.php';

$res = get_filtered_products(['is_new_arrival' => 1], 'newest', 1, 50);
echo "TOTAL NEW ARRIVALS: " . count($res['products']) . "\n\n";

foreach ($res['products'] as $p) {
    echo "ID: {$p['product_id']}\n";
    echo "Name: {$p['product_name']}\n";
    echo "Platform: {$p['platform_name']}\n";
    echo "Image: {$p['primary_image']}\n";
    echo "Price: {$p['price']} | Discount: {$p['discount_price']}\n";
    echo "----------------------------------------\n";
}
