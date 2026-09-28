<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo = Database::getConnection();
    
    // Fetch all products
    $stmt = $pdo->query("SELECT product_id, discount_price FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $pdo->prepare("UPDATE products SET price = :price, discount_price = :discount_price WHERE product_id = :id");
    
    // Possible base prices for realistic numbers
    $priceBases = [7990, 8990, 9990, 10990, 12990, 14990, 16990, 18990, 19990];
    
    foreach ($products as $product) {
        $newPrice = $priceBases[array_rand($priceBases)];
        
        $newDiscount = null;
        if ($product['discount_price']) {
            // Generate a realistic discount (10% to 30% off)
            $discountPct = rand(1, 3) * 10; // 10, 20, 30
            $discounted = $newPrice * (1 - ($discountPct / 100));
            // Round to nearest 10
            $newDiscount = round($discounted / 10) * 10;
        }
        
        $updateStmt->execute([
            ':price' => $newPrice,
            ':discount_price' => $newDiscount,
            ':id' => $product['product_id']
        ]);
    }
    
    echo "Prices updated to 7000 - 20000 range successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
