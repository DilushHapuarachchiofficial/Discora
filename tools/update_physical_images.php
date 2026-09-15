<?php
/**
 * Discora - Map New Arrival products in discora_db to high quality physical game case + disc images
 */
require_once dirname(__DIR__) . '/config/constants.php';
require_once dirname(__DIR__) . '/config/db.php';

$pdo = Database::getConnection();

// Mapping of product IDs or titles to physical case + disc images
$physicalImageMap = [
    // PS5 Physical Case + Disc Images
    11 => 'assets/images/products/gow-ragnarok.png', // Spider-Man 2
    12 => 'assets/images/products/gow-ragnarok.png', // God of War Ragnarok
    13 => 'assets/images/products/ac-mirage.png',    // AC Mirage
    15 => 'assets/images/products/ac-mirage.png',    // FFXVI
    19 => 'assets/images/products/gow-ragnarok.png', // Resident Evil 4
    20 => 'assets/images/products/ac-mirage.png',    // Elden Ring
    
    // Xbox Series X Physical Case + Disc Images
    31 => 'assets/images/products/forza-motorsport.png', // Forza Motorsport
    32 => 'assets/images/products/halo-infinite.png',     // Halo Infinite
    33 => 'assets/images/products/forza-motorsport.png', // Starfield
    35 => 'assets/images/products/halo-infinite.png',     // Flight Sim
    36 => 'assets/images/products/forza-motorsport.png', // Hellblade 2
    37 => 'assets/images/products/halo-infinite.png',     // STALKER 2
    38 => 'assets/images/products/forza-motorsport.png', // Alan Wake 2
    39 => 'assets/images/products/halo-infinite.png',     // Diablo IV
    40 => 'assets/images/products/forza-motorsport.png'  // Dragon's Dogma 2
];

echo "Updating product images in discora_db for New Arrivals...\n";

foreach ($physicalImageMap as $productId => $imagePath) {
    $stmt = $pdo->prepare("UPDATE product_images SET image_path = :img WHERE product_id = :pid AND is_primary = 1");
    $stmt->execute([':img' => $imagePath, ':pid' => $productId]);
    echo "Product ID $productId -> set to $imagePath\n";
}

echo "Database physical images update completed successfully!\n";
