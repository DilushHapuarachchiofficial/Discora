<?php
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/core/products.php';
$new_arrivals_query = get_filtered_products(['is_new_arrival' => 1], 'newest', 1, 8);
echo count($new_arrivals_query['products']);
