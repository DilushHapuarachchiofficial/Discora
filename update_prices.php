<?php
require_once __DIR__ . '/config/db.php';

try {
    $pdo = Database::getConnection();
    
    // Fetch all products
    $stmt = $pdo->query("SELECT product_id, price, discount_price FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $pdo->prepare("UPDATE products SET price = :price, discount_price = :discount_price WHERE product_id = :id");
    
    foreach ($products as $product) {
        $oldPrice = (float)$product['price'];
        $oldDiscount = $product['discount_price'] ? (float)$product['discount_price'] : null;
        
        // If price is less than 1000, it's likely USD, so let's convert it.
        // Assuming conversion rate of 320
        if ($oldPrice < 1000) {
            $newPrice = round(($oldPrice * 320) / 10) * 10; // Round to nearest 10
            
            $newDiscount = null;
            if ($oldDiscount) {
                $newDiscount = round(($oldDiscount * 320) / 10) * 10;
            }
            
            $updateStmt->execute([
                ':price' => $newPrice,
                ':discount_price' => $newDiscount,
                ':id' => $product['product_id']
            ]);
        }
    }
    
    echo "Prices updated successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
